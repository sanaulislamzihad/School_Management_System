<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class StaffAttendanceController extends Controller
{
    public function index(Request $request): View
    {
        $date = $request->date('date')?->toDateString() ?? today()->toDateString();
        $marked = DB::table('staff_attendance')->where('date', $date)->pluck('status', 'employee_id');
        $employees = DB::table('employees')->where('status', 'Active')->orderBy('employee_no')->get()->each(function ($e) use ($marked) {
            $e->attendance = $marked[$e->id] ?? null;
        });

        return view('admin.staff-attendance', compact('employees', 'date'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'date' => ['required', 'date', 'before_or_equal:today'],
            'status' => ['required', 'array'],
            'status.*' => ['required', 'in:Present,Absent,Late,Leave'],
        ]);

        $valid = DB::table('employees')->where('status', 'Active')->pluck('id')->flip();
        $date = $request->date('date')->toDateString();
        $rows = [];
        foreach ($data['status'] as $employeeId => $status) {
            if ($valid->has($employeeId)) {
                $rows[] = ['employee_id' => $employeeId, 'date' => $date, 'status' => $status];
            }
        }
        DB::table('staff_attendance')->upsert($rows, ['employee_id', 'date'], ['status']);

        return back()->with('ok', 'Staff attendance saved.');
    }
}
