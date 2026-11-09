<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class EmployeeController extends Controller
{
    public function index(Request $request): View
    {
        $employees = Employee::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->query('q')).'%';
                $query->where(fn ($q) => $q->where('name', 'like', $term)->orWhere('employee_no', 'like', $term)->orWhere('phone', 'like', $term));
            })
            ->when($request->filled('kind'), fn ($q) => $q->where('kind', $request->query('kind')))
            ->orderBy('employee_no')->paginate(20)->withQueryString();

        return view('admin.employees', compact('employees'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $password = Str::password(10, symbols: false);

        $employee = DB::transaction(function () use ($data, $password) {
            $number = Employee::nextNumber();
            $userId = $data['kind'] === 'Teacher'
                ? User::create(['name' => $data['name'], 'login' => $number, 'password' => $password, 'role' => 'teacher', 'active' => true])->id
                : null;

            return Employee::create($data + ['employee_no' => $number, 'user_id' => $userId]);
        });

        return back()->with($employee->user_id ? ['credentials' => ['login' => $employee->employee_no, 'password' => $password]] : ['ok' => 'Employee added.']);
    }

    public function update(Request $request, Employee $employee): RedirectResponse
    {
        $employee->update($this->validated($request));
        $employee->user?->update(['name' => $employee->name, 'active' => $employee->status === 'Active']);

        return back()->with('ok', 'Employee updated.');
    }

    public function destroy(Employee $employee): RedirectResponse
    {
        DB::transaction(function () use ($employee) {
            $user = $employee->user;
            $employee->delete();
            $user?->delete();
        });

        return back()->with('ok', 'Employee deleted.');
    }

    public function resetPassword(Employee $employee): RedirectResponse
    {
        abort_unless($employee->user, 404);
        $password = Str::password(10, symbols: false);
        $employee->user->update(['password' => $password]);

        return back()->with('credentials', ['login' => $employee->employee_no, 'password' => $password]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'designation' => ['nullable', 'string', 'max:120'],
            'department' => ['nullable', 'string', 'max:120'],
            'kind' => ['required', Rule::in(['Teacher', 'Staff'])],
            'gender' => ['nullable', Rule::in(['Male', 'Female', 'Other'])],
            'phone' => ['nullable', 'string', 'max:30'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'joining_date' => ['nullable', 'date'],
            'basic_salary' => ['required', 'numeric', 'min:0'],
            'status' => ['required', Rule::in(['Active', 'Inactive'])],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ]);
        $data['show_public'] = $request->boolean('show_public');

        unset($data['photo']);
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = Str::random(32).'.'.$file->guessExtension();
            $file->move(public_path('uploads/employees'), $name);
            $data['photo'] = 'uploads/employees/'.$name;
        }

        return $data;
    }
}
