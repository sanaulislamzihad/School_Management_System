<?php

namespace App\Http\Controllers;

use App\Support\Lookups;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TimetableController extends Controller
{
    public const DAYS = ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'];

    private static function query()
    {
        return DB::table('routines as r')
            ->join('subjects as su', 'r.subject_id', '=', 'su.id')
            ->join('classes as c', 'r.class_id', '=', 'c.id')
            ->join('sections as se', 'r.section_id', '=', 'se.id')
            ->leftJoin('employees as e', 'r.employee_id', '=', 'e.id')
            ->select('r.*', 'su.name as subject_name', 'c.name as class_name', 'se.name as section_name', 'e.name as teacher_name', 'e.photo as teacher_photo')
            ->orderBy('r.start_time');
    }

    public static function sectionPeriods(int $sectionId): Collection
    {
        return self::query()->where('r.section_id', $sectionId)->get()->groupBy('day');
    }

    public static function teacherPeriods(int $employeeId): Collection
    {
        return self::query()->where('r.employee_id', $employeeId)->get()->groupBy('day');
    }

    public function admin(Request $request): View
    {
        $periods = $request->filled('section_id') ? self::sectionPeriods($request->integer('section_id')) : collect();

        return view('admin.timetable', ['classes' => Lookups::get('classes'), 'sections' => Lookups::get('sections'), 'periods' => $periods]);
    }

    public function teacher(Request $request): View
    {
        return view('teacher.timetable', ['periods' => self::teacherPeriods($request->user()->employee->id)]);
    }
}
