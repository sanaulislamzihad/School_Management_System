<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClassTestMarkController extends Controller
{
    public function edit(Request $request, int $classTest): View
    {
        $test = $this->findTest($request, $classTest);
        $existing = DB::table('class_test_marks')->where('class_test_id', $test->id)->pluck('marks', 'student_id');
        $students = DB::table('students')->where(['class_id' => $test->class_id, 'section_id' => $test->section_id, 'status' => 'Active'])->orderBy('roll')->get()
            ->each(function ($s) use ($existing) {
                $s->mark = isset($existing[$s->id]) ? (float) $existing[$s->id] : null;
            });

        return view('shared.class-test-marks', compact('test', 'students'));
    }

    public function update(Request $request, int $classTest): RedirectResponse
    {
        $test = $this->findTest($request, $classTest);
        $data = $request->validate(['marks' => ['required', 'array'], 'marks.*' => ['nullable', 'numeric', 'min:0', 'max:'.$test->full_marks]]);
        $valid = DB::table('students')->where(['class_id' => $test->class_id, 'section_id' => $test->section_id, 'status' => 'Active'])->pluck('id')->flip();

        DB::transaction(function () use ($data, $valid, $test) {
            foreach ($data['marks'] as $studentId => $mark) {
                if (! $valid->has($studentId)) {
                    continue;
                }
                $key = ['class_test_id' => $test->id, 'student_id' => $studentId];
                $mark === null ? DB::table('class_test_marks')->where($key)->delete() : DB::table('class_test_marks')->updateOrInsert($key, ['marks' => $mark]);
            }
        });

        return back()->with('ok', 'Marks saved.');
    }

    private function findTest(Request $request, int $id): object
    {
        $test = DB::table('class_tests as t')->join('classes as c', 't.class_id', '=', 'c.id')->join('sections as s', 't.section_id', '=', 's.id')->join('subjects as su', 't.subject_id', '=', 'su.id')
            ->select('t.*', 'c.name as class_name', 's.name as section_name', 'su.name as subject_name')->where('t.id', $id)->first();
        abort_unless($test, 404);

        if ($request->user()->role === 'teacher') {
            abort_unless((int) $test->employee_id === $request->user()->employee->id, 403);
        }

        return $test;
    }
}
