<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TeacherController extends Controller
{
    private function me(Request $request): Employee
    {
        return $request->user()->employee ?? abort(403, 'No employee record is linked to this account.');
    }

    private function links(Employee $me)
    {
        return DB::table('teacher_subjects as t')->join('classes as c', 't.class_id', '=', 'c.id')->join('sections as s', 't.section_id', '=', 's.id')->join('subjects as su', 't.subject_id', '=', 'su.id')
            ->select('t.*', 'c.name as class_name', 's.name as section_name', 'su.name as subject_name')->where('t.employee_id', $me->id)->orderBy('c.position')->orderBy('s.name')->get();
    }

    public function dashboard(Request $request): View
    {
        $me = $this->me($request);
        $links = $this->links($me);
        $sectionIds = $links->pluck('section_id')->unique();
        $today = TimetableController::teacherPeriods($me->id)->get(now()->format('l'), collect());
        $marked = DB::table('attendance as a')->join('students as s', 'a.student_id', '=', 's.id')->where('a.date', today()->toDateString())->whereIn('s.section_id', $sectionIds)->distinct()->pluck('s.section_id');

        return view('teacher.dashboard', [
            'me' => $me, 'links' => $links, 'today' => $today,
            'studentCount' => DB::table('students')->whereIn('section_id', $sectionIds)->where('status', 'Active')->count(),
            'sectionCount' => $sectionIds->count(),
            'unmarked' => $links->unique('section_id')->reject(fn ($l) => $marked->contains($l->section_id))->values(),
            'homeworkDue' => DB::table('homework')->where('employee_id', $me->id)->where('due_date', '>=', today()->toDateString())->count(),
            'homework' => DB::table('homework as h')->join('classes as c', 'h.class_id', '=', 'c.id')->join('sections as s', 'h.section_id', '=', 's.id')->select('h.*', 'c.name as class_name', 's.name as section_name')->where('h.employee_id', $me->id)->orderByDesc('h.due_date')->limit(5)->get(),
            'notices' => $this->noticeQuery()->limit(5)->get(),
        ]);
    }

    public function students(Request $request): View
    {
        $me = $this->me($request);
        $links = $this->links($me);

        $students = DB::table('students as st')->join('classes as c', 'st.class_id', '=', 'c.id')->join('sections as se', 'st.section_id', '=', 'se.id')
            ->select('st.id', 'st.name', 'st.student_no', 'st.roll', 'st.guardian_name', 'st.guardian_phone', 'c.name as class_name', 'se.name as section_name')
            ->whereIn('st.section_id', $links->pluck('section_id')->unique())->where('st.status', 'Active')
            ->when($request->filled('section_id'), fn ($q) => $q->where('st.section_id', $request->integer('section_id')))
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->query('q')).'%';
                $query->where(fn ($q) => $q->where('st.name', 'like', $term)->orWhere('st.student_no', 'like', $term)->orWhere('st.guardian_phone', 'like', $term));
            })
            ->orderBy('c.position')->orderBy('se.name')->orderBy('st.roll')->paginate(25)->withQueryString();

        return view('teacher.students', ['students' => $students, 'sections' => $links->unique('section_id')->values()]);
    }

    public function notices(): View
    {
        return view('teacher.notices', ['notices' => $this->noticeQuery()->paginate(15)]);
    }

    public function leave(Request $request): View
    {
        return view('teacher.leave', ['leaves' => DB::table('staff_leaves')->where('employee_id', $this->me($request)->id)->orderByDesc('created_at')->get()]);
    }

    public function leaveStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:Casual,Sick,Other'], 'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'], 'reason' => ['required', 'string', 'max:255'],
        ]);
        DB::table('staff_leaves')->insert($data + ['employee_id' => $this->me($request)->id, 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('ok', 'Leave request sent.');
    }

    public function profile(Request $request): View
    {
        return view('teacher.profile', ['me' => $this->me($request), 'links' => $this->links($this->me($request))]);
    }

    private function noticeQuery()
    {
        return DB::table('notices')->whereIn('audience', ['Everyone', 'Staff'])->orderByDesc('pinned')->orderByDesc('date');
    }
}
