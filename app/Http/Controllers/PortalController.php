<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Services\ResultService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PortalController extends Controller
{
    private function me(Request $request): Student
    {
        $student = $request->user()->student ?? abort(403, 'No student record is linked to this account.');

        return $student->load('schoolClass', 'section', 'shift', 'session');
    }

    private function classWork(string $table, Student $me)
    {
        return DB::table("$table as w")->join('subjects as su', 'w.subject_id', '=', 'su.id')->leftJoin('employees as e', 'w.employee_id', '=', 'e.id')
            ->select('w.*', 'su.name as subject_name', 'e.name as teacher_name')->where(['w.class_id' => $me->class_id, 'w.section_id' => $me->section_id]);
    }

    private function noticeQuery()
    {
        return DB::table('notices')->whereIn('audience', ['Everyone', 'Students'])->orderByDesc('pinned')->orderByDesc('date');
    }

    public function dashboard(Request $request): View
    {
        $me = $this->me($request);
        $counts = DB::table('attendance')->where('student_id', $me->id)->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status');
        $days = $counts->sum();
        $latestExam = DB::table('exams')->where(['session_id' => $me->session_id, 'is_published' => true])->orderByDesc('start_date')->first();
        $today = now()->format('l');

        return view('portal.dashboard', [
            'me' => $me,
            'attendanceRate' => $days ? round((($counts['Present'] ?? 0) + ($counts['Late'] ?? 0)) / $days * 100) : null,
            'latest' => $latestExam ? ResultService::forStudent($latestExam->id, $me) : null,
            'latestExam' => $latestExam,
            'pendingWork' => (clone $this->classWork('homework', $me))->where('w.due_date', '>=', today()->toDateString())->count(),
            'due' => (float) DB::table('invoices')->where('student_id', $me->id)->where('status', '!=', 'Paid')->selectRaw('coalesce(sum(amount - paid), 0) as due')->value('due'),
            'todayName' => $today,
            'routine' => TimetableController::sectionPeriods((int) $me->section_id)->get($today, collect()),
            'homework' => $this->classWork('homework', $me)->where('w.due_date', '>=', today()->toDateString())->orderBy('w.due_date')->limit(5)->get(),
            'diary' => $this->classWork('diaries', $me)->orderByDesc('w.date')->limit(4)->get(),
            'notices' => $this->noticeQuery()->limit(5)->get(),
        ]);
    }

    public function profile(Request $request): View
    {
        return view('portal.profile', ['me' => $this->me($request)]);
    }

    public function routine(Request $request): View
    {
        $me = $this->me($request);

        return view('portal.routine', ['me' => $me, 'periods' => TimetableController::sectionPeriods((int) $me->section_id)]);
    }

    public function attendance(Request $request): View
    {
        $me = $this->me($request);
        $months = DB::table('attendance')->where('student_id', $me->id)->selectRaw('substr(date, 1, 7) as month')->distinct()->orderByDesc('month')->pluck('month');
        $month = $months->contains($request->query('month')) ? $request->query('month') : $months->first();
        $records = $month ? DB::table('attendance')->where('student_id', $me->id)->where('date', 'like', "$month%")->orderByDesc('date')->get() : collect();

        return view('portal.attendance', ['me' => $me, 'months' => $months, 'month' => $month, 'records' => $records, 'counts' => $records->countBy('status')]);
    }

    public function assignments(Request $request): View
    {
        return view('portal.assignments', ['items' => $this->classWork('homework', $this->me($request))->orderByDesc('w.due_date')->paginate(15)]);
    }

    public function diary(Request $request): View
    {
        return view('portal.diary', ['items' => $this->classWork('diaries', $this->me($request))->orderByDesc('w.date')->paginate(15)]);
    }

    public function classTests(Request $request): View
    {
        $me = $this->me($request);
        $tests = DB::table('class_tests as t')->join('subjects as su', 't.subject_id', '=', 'su.id')
            ->leftJoin('class_test_marks as m', function ($j) use ($me) {
                $j->on('m.class_test_id', '=', 't.id')->where('m.student_id', $me->id);
            })
            ->select('t.*', 'su.name as subject_name', 'm.marks')->where(['t.class_id' => $me->class_id, 't.section_id' => $me->section_id])->orderByDesc('t.date')->get();

        return view('portal.class-tests', compact('tests'));
    }

    public function results(Request $request): View
    {
        $me = $this->me($request);
        $results = DB::table('exams')->where('is_published', true)->orderByDesc('start_date')->get()
            ->map(fn ($exam) => ['exam' => $exam, 'result' => ResultService::forStudent($exam->id, $me)])->filter(fn ($r) => $r['result'])->values();

        return view('portal.results', compact('results'));
    }

    public function accounts(Request $request): View
    {
        $me = $this->me($request);
        $invoices = DB::table('invoices')->where('student_id', $me->id)->orderByRaw("status = 'Paid'")->orderByDesc('id')->get();

        return view('portal.accounts', ['invoices' => $invoices, 'billed' => $invoices->sum('amount'), 'paid' => $invoices->sum('paid')]);
    }

    public function notices(): View
    {
        return view('portal.notices', ['notices' => $this->noticeQuery()->paginate(15)]);
    }

    public function notice(int $id): View
    {
        $notice = $this->noticeQuery()->where('id', $id)->first();
        abort_unless($notice, 404);

        return view('portal.notice', compact('notice'));
    }

    public function leave(Request $request): View
    {
        return view('portal.leave', ['leaves' => DB::table('student_leaves')->where('student_id', $this->me($request)->id)->orderByDesc('created_at')->get()]);
    }

    public function leaveStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', 'in:Sick,Family,Other'], 'from_date' => ['required', 'date', 'after_or_equal:today'],
            'to_date' => ['required', 'date', 'after_or_equal:from_date'], 'reason' => ['required', 'string', 'max:255'], 'details' => ['nullable', 'string', 'max:2000'],
        ]);
        DB::table('student_leaves')->insert($data + ['student_id' => $this->me($request)->id, 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()]);

        return back()->with('ok', 'Leave application sent.');
    }

    public function downloads(): View
    {
        return view('portal.downloads', ['downloads' => DB::table('downloads')->orderByDesc('date')->get()]);
    }

    public function idCard(Request $request): View
    {
        return view('portal.id-card', ['me' => $this->me($request), 'validUntil' => Carbon::parse($this->me($request)->session->end_date ?? now()->endOfYear())]);
    }
}
