<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\Lookups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class AttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->teacher($request);
        $date = $request->date('date')?->toDateString() ?? today()->toDateString();
        $students = collect();
        $saved = false;

        if ($request->filled(['class_id', 'section_id'])) {
            $this->authorizeSection($teacher, (int) $request->query('class_id'), (int) $request->query('section_id'));
            $marked = DB::table('attendance')->where('date', $date)->pluck('status', 'student_id');
            $students = DB::table('students')->where(['class_id' => $request->query('class_id'), 'section_id' => $request->query('section_id'), 'status' => 'Active'])
                ->orderBy('roll')->get()->each(function ($s) use ($marked) {
                    $s->attendance = $marked[$s->id] ?? null;
                });
            $saved = $students->contains(fn ($s) => $s->attendance !== null);
        }

        return view('shared.attendance', [
            'classes' => Lookups::get('classes', $teacher), 'sections' => Lookups::get('sections', $teacher),
            'students' => $students, 'date' => $date, 'saved' => $saved,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'class_id' => ['required', 'integer'], 'section_id' => ['required', 'integer'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'array'], 'status.*' => ['required', 'in:Present,Absent,Late,Leave'],
        ]);
        $this->authorizeSection($this->teacher($request), (int) $data['class_id'], (int) $data['section_id']);

        $valid = DB::table('students')->where(['class_id' => $data['class_id'], 'section_id' => $data['section_id'], 'status' => 'Active'])->pluck('id')->flip();
        $date = $request->date('date')->toDateString();
        $rows = [];
        foreach ($data['status'] as $studentId => $status) {
            if ($valid->has($studentId)) {
                $rows[] = ['student_id' => $studentId, 'date' => $date, 'status' => $status, 'marked_by' => $request->user()->id];
            }
        }
        DB::table('attendance')->upsert($rows, ['student_id', 'date'], ['status', 'marked_by']);

        return back()->with('ok', 'Attendance saved for '.count($rows).' students.');
    }

    private function teacher(Request $request): ?Employee
    {
        return $request->user()->role === 'teacher' ? $request->user()->employee : null;
    }

    private function authorizeSection(?Employee $teacher, int $classId, int $sectionId): void
    {
        abort_if($teacher && ! $teacher->teaches($classId, $sectionId), 403, 'This class is not assigned to you.');
    }
}
