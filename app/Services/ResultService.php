<?php

namespace App\Services;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/** Turns stored marks into grades, GPA and merit positions. */
class ResultService
{
    public static function grades(): Collection
    {
        return DB::table('grades')->orderByDesc('min_mark')->get();
    }

    private static function gradeFor(float $percent, Collection $grades): object
    {
        return $grades->first(fn ($g) => $percent >= (float) $g->min_mark) ?? $grades->last();
    }

    /** Merit list for one exam and class (optionally one section), best result first. */
    public static function sheet(int $examId, int $classId, ?int $sectionId = null): array
    {
        $grades = self::grades();
        $students = DB::table('students as st')->leftJoin('sections as se', 'st.section_id', '=', 'se.id')
            ->select('st.id', 'st.name', 'st.student_no', 'st.roll', 'se.name as section_name')
            ->where('st.class_id', $classId)->where('st.status', 'Active')
            ->when($sectionId, fn ($q) => $q->where('st.section_id', $sectionId))->get();

        $marks = DB::table('marks as m')->join('subjects as su', 'm.subject_id', '=', 'su.id')
            ->where('m.exam_id', $examId)->whereIn('m.student_id', $students->pluck('id'))
            ->select('m.student_id', 'm.marks', 'm.full_marks', 'su.name as subject')->orderBy('su.name')->get()->groupBy('student_id');

        $rows = [];
        foreach ($students as $student) {
            $list = $marks->get($student->id);
            if (! $list) {
                continue;
            }
            $subjects = $list->map(function ($m) use ($grades) {
                $grade = self::gradeFor(($m->marks / $m->full_marks) * 100, $grades);

                return ['subject' => $m->subject, 'marks' => (float) $m->marks, 'full' => (float) $m->full_marks, 'letter' => $grade->letter, 'point' => (float) $grade->point];
            })->values()->all();

            $failed = collect($subjects)->contains(fn ($s) => $s['point'] <= 0);
            $gpa = $failed ? 0.0 : round(collect($subjects)->avg('point'), 2);
            $rows[] = [
                'student' => $student, 'subjects' => $subjects, 'total' => array_sum(array_column($subjects, 'marks')), 'full' => array_sum(array_column($subjects, 'full')),
                'gpa' => $gpa, 'passed' => ! $failed, 'letter' => $failed ? 'F' : $grades->first(fn ($g) => $gpa >= (float) $g->point)->letter,
            ];
        }

        usort($rows, fn ($a, $b) => [$b['passed'], $b['gpa'], $b['total']] <=> [$a['passed'], $a['gpa'], $a['total']]);
        foreach ($rows as $i => &$row) {
            $row['rank'] = $i + 1;
        }

        return $rows;
    }

    /** One student's result for one exam, with class position, or null if no marks exist. */
    public static function forStudent(int $examId, object $student): ?array
    {
        return collect(self::sheet($examId, (int) $student->class_id, $student->section_id ? (int) $student->section_id : null))
            ->first(fn ($row) => $row['student']->id === $student->id);
    }
}
