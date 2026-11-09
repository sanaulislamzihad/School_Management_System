<?php

namespace App\Support;

use App\Models\Employee;
use Illuminate\Support\Facades\DB;

/** Dropdown options shared by forms. Each option is value/label, plus parent for dependent selects. */
class Lookups
{
    private const TABLES = [
        'classes' => ['classes', 'name', 'position'],
        'sections' => ['sections', 'name', 'name'],
        'subjects' => ['subjects', 'name', 'name'],
        'sessions' => ['academic_sessions', 'name', 'name'],
        'shifts' => ['shifts', 'name', 'name'],
        'exams' => ['exams', 'name', 'id'],
        'fee_heads' => ['fee_heads', 'name', 'name'],
        'account_heads' => ['account_heads', 'name', 'name'],
    ];

    public static function table(string $lookup): string
    {
        return $lookup === 'teachers' ? 'employees' : self::TABLES[$lookup][0];
    }

    /** @param  Employee|null  $teacher  When set, classes, sections and subjects narrow to this teacher's links. */
    public static function get(string $lookup, ?Employee $teacher = null): array
    {
        if ($lookup === 'teachers') {
            return DB::table('employees')->where(['kind' => 'Teacher', 'status' => 'Active'])->orderBy('name')
                ->get()->map(fn ($r) => ['value' => $r->id, 'label' => $r->name])->all();
        }

        [$table, $label, $order] = self::TABLES[$lookup];
        $query = DB::table($table)->orderBy($order);

        if ($teacher && in_array($lookup, ['classes', 'sections', 'subjects'], true)) {
            $column = ['classes' => 'class_id', 'sections' => 'section_id', 'subjects' => 'subject_id'][$lookup];
            $query->whereIn('id', DB::table('teacher_subjects')->where('employee_id', $teacher->id)->pluck($column));
        }

        return $query->get()->map(fn ($r) => array_filter([
            'value' => $r->id,
            'label' => $r->{$label},
            'parent' => in_array($lookup, ['sections', 'subjects'], true) ? $r->class_id : null,
        ], fn ($v) => $v !== null))->all();
    }
}
