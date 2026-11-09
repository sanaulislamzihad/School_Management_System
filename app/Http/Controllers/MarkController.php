<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Support\Lookups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class MarkController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $this->teacher($request);
        $students = collect();
        $fullMarks = 100;
        $exam = null;

        if ($request->filled(['exam_id', 'class_id', 'section_id', 'subject_id'])) {
            $this->authorizeSubject($teacher, $request->integer('class_id'), $request->integer('section_id'), $request->integer('subject_id'));
            $exam = DB::table('exams')->find($request->integer('exam_id'));
            abort_unless($exam, 404);

            $existing = DB::table('marks')->where(['exam_id' => $exam->id, 'subject_id' => $request->integer('subject_id')])->get()->keyBy('student_id');
            $fullMarks = (float) ($existing->first()->full_marks ?? 100);
            $students = DB::table('students')->where(['class_id' => $request->integer('class_id'), 'section_id' => $request->integer('section_id'), 'status' => 'Active'])
                ->orderBy('roll')->get()->each(function ($s) use ($existing) {
                    $s->mark = isset($existing[$s->id]) ? (float) $existing[$s->id]->marks : null;
                });
        }

        return view('shared.marks', [
            'exams' => Lookups::get('exams'),
            'classes' => Lookups::get('classes', $teacher), 'sections' => Lookups::get('sections', $teacher), 'subjects' => Lookups::get('subjects', $teacher),
            'students' => $students, 'fullMarks' => $fullMarks, 'exam' => $exam,
            'locked' => $teacher && $exam && $exam->is_published,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'exam_id' => ['required', 'exists:exams,id'], 'class_id' => ['required', 'integer'], 'section_id' => ['required', 'integer'], 'subject_id' => ['required', 'integer'],
            'full_marks' => ['required', 'numeric', 'min:1', 'max:1000'],
            'marks' => ['required', 'array'], 'marks.*' => ['nullable', 'numeric', 'min:0'],
        ]);

        $teacher = $this->teacher($request);
        $this->authorizeSubject($teacher, (int) $data['class_id'], (int) $data['section_id'], (int) $data['subject_id']);
        abort_if($teacher && DB::table('exams')->where('id', $data['exam_id'])->value('is_published'), 403, 'Results for this exam are already published.');
        abort_unless(DB::table('subjects')->where(['id' => $data['subject_id'], 'class_id' => $data['class_id']])->exists(), 422);

        $valid = DB::table('students')->where(['class_id' => $data['class_id'], 'section_id' => $data['section_id'], 'status' => 'Active'])->pluck('id')->flip();
        $errors = [];
        foreach ($data['marks'] as $studentId => $mark) {
            if ($mark !== null && (float) $mark > (float) $data['full_marks']) {
                $errors["marks.$studentId"] = 'Marks cannot be more than '.$data['full_marks'].'.';
            }
        }
        if ($errors) {
            throw ValidationException::withMessages($errors);
        }

        $key = ['exam_id' => $data['exam_id'], 'subject_id' => $data['subject_id']];
        DB::transaction(function () use ($data, $valid, $key, $request) {
            foreach ($data['marks'] as $studentId => $mark) {
                if (! $valid->has($studentId)) {
                    continue;
                }
                if ($mark === null) {
                    DB::table('marks')->where($key + ['student_id' => $studentId])->delete();
                } else {
                    DB::table('marks')->updateOrInsert($key + ['student_id' => $studentId], ['marks' => $mark, 'full_marks' => $data['full_marks'], 'entered_by' => $request->user()->id]);
                }
            }
        });

        return back()->with('ok', 'Marks saved.');
    }

    private function teacher(Request $request): ?Employee
    {
        return $request->user()->role === 'teacher' ? $request->user()->employee : null;
    }

    private function authorizeSubject(?Employee $teacher, int $classId, int $sectionId, int $subjectId): void
    {
        abort_if($teacher && ! $teacher->teaches($classId, $sectionId, $subjectId), 403, 'This subject is not assigned to you.');
    }
}
