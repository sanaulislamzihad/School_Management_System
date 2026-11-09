<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Student;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdmissionController extends Controller
{
    public function index(Request $request): View
    {
        $status = $request->query('status', 'Pending');
        $counts = DB::table('admissions')->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');

        $applications = DB::table('admissions as a')->join('classes as c', 'a.class_id', '=', 'c.id')->select('a.*', 'c.name as class_name')
            ->when($status !== 'All', fn ($q) => $q->where('a.status', $status))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->query('q')).'%';
                $query->where(fn ($q) => $q->where('a.name', 'like', $term)->orWhere('a.reference', 'like', $term)->orWhere('a.phone', 'like', $term));
            })
            ->orderByDesc('a.created_at')->paginate(20)->withQueryString();

        return view('admin.admissions', [
            'applications' => $applications, 'status' => $status, 'counts' => $counts,
            'sections' => DB::table('sections')->orderBy('name')->get()->groupBy('class_id'),
        ]);
    }

    public function approve(Request $request, int $admission): RedirectResponse
    {
        $application = DB::table('admissions')->where(['id' => $admission, 'status' => 'Pending'])->first();
        abort_unless($application, 404);

        $data = $request->validate(['section_id' => ['required', 'exists:sections,id']]);
        abort_unless(DB::table('sections')->where(['id' => $data['section_id'], 'class_id' => $application->class_id])->exists(), 422);

        $session = DB::table('academic_sessions')->where('is_current', true)->first();
        abort_unless($session, 422, 'Set a current academic session first.');
        $password = Str::password(10, symbols: false);

        $student = DB::transaction(function () use ($application, $data, $session, $password) {
            $number = Student::nextNumber($session->name);
            $user = User::create(['name' => $application->name, 'login' => $number, 'password' => $password, 'role' => 'student', 'active' => true]);
            $student = Student::create([
                'user_id' => $user->id, 'student_no' => $number, 'name' => $application->name, 'gender' => $application->gender, 'dob' => $application->dob,
                'email' => $application->email, 'present_address' => $application->address, 'previous_school' => $application->previous_school,
                'session_id' => $session->id, 'class_id' => $application->class_id, 'section_id' => $data['section_id'], 'admission_date' => today(), 'status' => 'Active',
                'guardian_name' => $application->guardian_name, 'guardian_phone' => $application->phone, 'phone' => $application->phone,
                'roll' => (int) DB::table('students')->where('section_id', $data['section_id'])->max('roll') + 1,
            ]);
            DB::table('admissions')->where('id', $application->id)->update(['status' => 'Approved', 'student_id' => $student->id, 'updated_at' => now()]);

            return $student;
        });

        return redirect()->route('admin.students.show', $student)->with('credentials', ['login' => $student->student_no, 'password' => $password]);
    }

    public function reject(int $admission): RedirectResponse
    {
        abort_unless(DB::table('admissions')->where(['id' => $admission, 'status' => 'Pending'])->update(['status' => 'Rejected', 'updated_at' => now()]), 404);

        return back()->with('ok', 'Application rejected.');
    }
}
