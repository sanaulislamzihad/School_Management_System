<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use App\Services\ResultService;
use App\Support\Lookups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class StudentController extends Controller
{
    public function index(Request $request): View
    {
        $fees = DB::table('invoices')->select('student_id', DB::raw('sum(amount) as billed'), DB::raw('sum(paid) as paid'))->groupBy('student_id');

        $students = DB::table('students as s')
            ->leftJoin('classes as c', 's.class_id', '=', 'c.id')
            ->leftJoin('sections as se', 's.section_id', '=', 'se.id')
            ->leftJoinSub($fees, 'f', 'f.student_id', '=', 's.id')
            ->select('s.*', 'c.name as class_name', 'se.name as section_name', 'f.billed', 'f.paid')
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->query('q')).'%';
                $query->where(fn ($q) => $q->where('s.name', 'like', $term)->orWhere('s.student_no', 'like', $term)->orWhere('s.phone', 'like', $term)->orWhere('s.guardian_phone', 'like', $term));
            })
            ->when($request->filled('session_id'), fn ($q) => $q->where('s.session_id', $request->integer('session_id')))
            ->when($request->filled('class_id'), fn ($q) => $q->where('s.class_id', $request->integer('class_id')))
            ->when($request->filled('section_id'), fn ($q) => $q->where('s.section_id', $request->integer('section_id')))
            ->when($request->filled('status'), fn ($q) => $q->where('s.status', $request->query('status')))
            ->orderBy('c.position')->orderBy('se.name')->orderBy('s.roll')
            ->paginate(25)->withQueryString();

        return view('admin.students.index', ['students' => $students] + $this->options());
    }

    public function create(): View
    {
        return view('admin.students.form', ['student' => new Student(['status' => 'Active', 'gender' => 'Male', 'admission_date' => today()])] + $this->options());
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);
        $password = Str::password(10, symbols: false);

        $student = DB::transaction(function () use ($data, $password) {
            $number = Student::nextNumber(DB::table('academic_sessions')->where('id', $data['session_id'])->value('name'));
            $user = User::create(['name' => $data['name'], 'login' => $number, 'password' => $password, 'role' => 'student', 'active' => true]);

            return Student::create($data + ['student_no' => $number, 'user_id' => $user->id]);
        });

        return redirect()->route('admin.students.show', $student)->with('credentials', ['login' => $student->student_no, 'password' => $password]);
    }

    public function show(Student $student): View
    {
        $student->load('schoolClass', 'section', 'shift', 'session');
        $attendance = DB::table('attendance')->where('student_id', $student->id)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $results = DB::table('exams')->where('session_id', $student->session_id)->orderBy('start_date')->get()
            ->map(fn ($exam) => ['exam' => $exam, 'result' => ResultService::forStudent($exam->id, $student)])->filter(fn ($r) => $r['result']);

        return view('admin.students.show', [
            'student' => $student, 'attendance' => $attendance, 'results' => $results,
            'invoices' => $student->invoices()->orderByDesc('id')->limit(12)->get(),
        ]);
    }

    public function edit(Student $student): View
    {
        return view('admin.students.form', ['student' => $student] + $this->options());
    }

    public function update(Request $request, Student $student): RedirectResponse
    {
        $student->update($this->validated($request, $student));
        $student->user?->update(['name' => $student->name, 'active' => $student->status === 'Active']);

        return redirect()->route('admin.students.show', $student)->with('ok', 'Student updated.');
    }

    public function destroy(Student $student): RedirectResponse
    {
        DB::transaction(function () use ($student) {
            $user = $student->user;
            $student->delete();
            $user?->delete();
        });

        return redirect()->route('admin.students.index')->with('ok', 'Student deleted.');
    }

    public function resetPassword(Student $student): RedirectResponse
    {
        abort_unless($student->user, 404);
        $password = Str::password(10, symbols: false);
        $student->user->update(['password' => $password]);

        return back()->with('credentials', ['login' => $student->student_no, 'password' => $password]);
    }

    private function options(): array
    {
        return ['sessions' => Lookups::get('sessions'), 'classes' => Lookups::get('classes'), 'sections' => Lookups::get('sections'), 'shifts' => Lookups::get('shifts')];
    }

    private function validated(Request $request, ?Student $student = null): array
    {
        $text = ['nullable', 'string', 'max:255'];
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gender' => ['required', Rule::in(['Male', 'Female', 'Other'])],
            'dob' => ['required', 'date', 'before:today'],
            'blood_group' => ['nullable', Rule::in(['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'])],
            'religion' => $text, 'birth_reg' => $text, 'phone' => $text, 'email' => ['nullable', 'email', 'max:120'],
            'present_address' => $text, 'permanent_address' => $text, 'medical_notes' => ['nullable', 'string', 'max:2000'],
            'session_id' => ['required', 'exists:academic_sessions,id'],
            'class_id' => ['required', 'exists:classes,id'],
            'section_id' => ['required', Rule::exists('sections', 'id')->where('class_id', $request->integer('class_id'))],
            'shift_id' => ['nullable', 'exists:shifts,id'],
            'roll' => ['nullable', 'integer', 'min:1', 'max:9999'],
            'admission_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(['Active', 'Inactive', 'Transferred', 'Passed Out'])],
            'father_name' => $text, 'father_phone' => $text, 'father_occupation' => $text,
            'mother_name' => $text, 'mother_phone' => $text, 'mother_occupation' => $text,
            'guardian_name' => $text, 'guardian_relation' => $text, 'guardian_phone' => ['required', 'string', 'max:30'],
            'previous_school' => $text, 'notes' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ], [], ['section_id' => 'section', 'class_id' => 'class', 'session_id' => 'session', 'dob' => 'date of birth', 'guardian_phone' => 'guardian phone']);

        unset($data['photo']);
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $name = Str::random(32).'.'.$file->guessExtension();
            $file->move(public_path('uploads/students'), $name);
            $data['photo'] = 'uploads/students/'.$name;
        }

        return $data;
    }
}
