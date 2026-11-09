<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\Lookups;
use App\Support\ResourceRegistry;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

/** Config-driven list, add, edit and delete pages. See ResourceRegistry. */
class ResourceController extends Controller
{
    public function index(Request $request, string $resource): View
    {
        $def = $this->definition($request, $resource);
        $teacher = $this->teacher($request);

        $query = DB::table($def['table'].' as t')->select(['t.*', ...($def['select'] ?? [])]);
        foreach ($def['joins'] ?? [] as [$table, $left, $right]) {
            $query->leftJoin($table, $left, '=', $right);
        }
        if ($teacher && ! empty($def['owned'])) {
            $query->where('t.employee_id', $teacher->id);
        }
        if (($term = trim((string) $request->query('q'))) !== '' && ! empty($def['search'])) {
            $query->where(fn ($q) => collect($def['search'])->each(fn ($col) => $q->orWhere($col, 'like', "%$term%")));
        }
        foreach ($def['filters'] ?? [] as $filter) {
            if ($request->filled($filter['name'])) {
                $query->where('t.'.$filter['name'], $request->query($filter['name']));
            }
        }
        foreach ($def['order'] as [$column, $direction]) {
            $query->orderBy($column, $direction);
        }

        $filters = array_map(fn ($f) => $f + ['choices' => $this->choices($f, $teacher)], $def['filters'] ?? []);
        $options = [];
        foreach ($def['fields'] as $field) {
            if ($field['type'] === 'select') {
                $options[$field['name']] = $this->choices($field, $teacher);
            }
        }

        return view('resource.index', [
            'name' => $resource,
            'def' => $def,
            'rows' => $query->paginate(20)->withQueryString(),
            'filters' => $filters,
            'options' => $options,
            'baseUrl' => staff_route('resource.index', ['resource' => $resource]),
            'blank' => collect($def['fields'])->mapWithKeys(fn ($f) => [$f['name'] => $f['type'] === 'checkbox' ? false : ''])->all(),
        ]);
    }

    public function store(Request $request, string $resource): RedirectResponse
    {
        $def = $this->definition($request, $resource);
        abort_if(! empty($def['noCreate']), 403);
        $data = $this->validated($request, $def);
        $this->runChecks($request, $def, $data);

        if ($this->teacher($request) && ! empty($def['owned'])) {
            $data['employee_id'] = $this->teacher($request)->id;
        }

        $id = DB::table($def['table'])->insertGetId($data);
        if (isset($def['after'])) {
            $def['after']($data, $id);
        }

        return back()->with('ok', ucfirst($def['singular']).' added.');
    }

    public function update(Request $request, string $resource, int $id): RedirectResponse
    {
        $def = $this->definition($request, $resource);
        $this->findRow($request, $def, $id);
        $data = $this->validated($request, $def, $id);
        $this->runChecks($request, $def, $data, $id);

        DB::table($def['table'])->where('id', $id)->update($data);
        if (isset($def['after'])) {
            $def['after']($data, $id);
        }

        return back()->with('ok', 'Changes saved.');
    }

    public function destroy(Request $request, string $resource, int $id): RedirectResponse
    {
        $def = $this->definition($request, $resource);
        $this->findRow($request, $def, $id);

        try {
            DB::table($def['table'])->where('id', $id)->delete();
        } catch (QueryException) {
            return back()->with('error', 'This '.$def['singular'].' is in use by other records, so it cannot be deleted.');
        }

        return back()->with('ok', ucfirst($def['singular']).' deleted.');
    }

    private function definition(Request $request, string $resource): array
    {
        $def = ResourceRegistry::get($resource);
        abort_unless($def && in_array($request->user()->role, $def['roles'] ?? ['admin'], true), 404);

        return $def;
    }

    private function teacher(Request $request): ?Employee
    {
        return $request->user()->role === 'teacher' ? $request->user()->employee : null;
    }

    private function findRow(Request $request, array $def, int $id): object
    {
        $row = DB::table($def['table'])->find($id);
        abort_unless($row, 404);
        if (($teacher = $this->teacher($request)) && ! empty($def['owned'])) {
            abort_unless((int) $row->employee_id === $teacher->id, 403);
        }

        return $row;
    }

    private function choices(array $field, ?Employee $teacher): array
    {
        if (isset($field['lookup'])) {
            return Lookups::get($field['lookup'], $teacher);
        }

        return array_map(fn ($o) => ['value' => $o, 'label' => Str::headline($o)], $field['options']);
    }

    private function validated(Request $request, array $def, ?int $id = null): array
    {
        $rules = [];
        foreach ($def['fields'] as $f) {
            $name = $f['name'];
            if ($f['type'] === 'checkbox') {
                continue;
            }
            $required = ! empty($f['required']) && ! ($f['type'] === 'image' && $id);
            $rule = [$required ? 'required' : 'nullable'];

            match ($f['type']) {
                'number' => array_push($rule, 'numeric', 'min:0'),
                'date' => $rule[] = 'date',
                'time' => $rule[] = 'date_format:H:i',
                'url' => array_push($rule, 'string', 'max:500', function ($attribute, $value, $fail) {
                    if ($value !== '#' && ! preg_match('#^https?://#i', (string) $value)) {
                        $fail('Enter a link that starts with http:// or https://.');
                    }
                }),
                'image' => array_push($rule, 'image', 'mimes:jpg,jpeg,png,webp', 'max:4096'),
                'select' => $rule[] = isset($f['lookup'])
                    ? Rule::exists(Lookups::table($f['lookup']), 'id')
                    : Rule::in($f['options']),
                'textarea' => array_push($rule, 'string', 'max:20000'),
                default => array_push($rule, 'string', 'max:255'),
            };

            if (! empty($f['unique'])) {
                $rule[] = Rule::unique($def['table'], $name)->ignore($id);
            }
            $rules[$name] = $rule;
        }

        $labels = collect($def['fields'])->pluck('label', 'name')->all();
        $data = $request->validate($rules, [], $labels);

        foreach ($def['fields'] as $f) {
            $name = $f['name'];
            if ($f['type'] === 'checkbox') {
                $data[$name] = $request->boolean($name);
            } elseif ($f['type'] === 'image') {
                if ($request->hasFile($name)) {
                    $file = $request->file($name);
                    $fileName = Str::random(32).'.'.$file->guessExtension();
                    $file->move(public_path('uploads/'.$def['table']), $fileName);
                    $data[$name] = 'uploads/'.$def['table'].'/'.$fileName;
                } else {
                    unset($data[$name]);
                }
            }
        }

        return $data;
    }

    private function runChecks(Request $request, array $def, array $data, ?int $id = null): void
    {
        $teacher = $this->teacher($request);
        if ($teacher && ! empty($def['owned']) && ! $teacher->teaches((int) $data['class_id'], (int) $data['section_id'], (int) $data['subject_id'])) {
            throw ValidationException::withMessages(['subject_id' => 'You are not assigned to this class, section and subject.']);
        }

        // Sections and subjects must belong to the selected class.
        foreach (['section_id' => 'sections', 'subject_id' => 'subjects'] as $column => $table) {
            if (isset($data['class_id'], $data[$column]) && ! DB::table($table)->where(['id' => $data[$column], 'class_id' => $data['class_id']])->exists()) {
                throw ValidationException::withMessages([$column => 'This choice does not belong to the selected class.']);
            }
        }

        if (isset($def['check']) && ($message = $def['check']($data, $id))) {
            throw ValidationException::withMessages([array_key_first($data) => $message]);
        }
    }
}
