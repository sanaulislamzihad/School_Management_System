<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class LeaveController extends Controller
{
    public function students(): View
    {
        return view('admin.leaves', [
            'title' => 'Student leave', 'subtitle' => 'Applications sent from the student portal.', 'decideRoute' => 'admin.student-leaves.decide',
            'leaves' => DB::table('student_leaves as l')->join('students as s', 'l.student_id', '=', 's.id')->join('classes as c', 's.class_id', '=', 'c.id')
                ->select('l.*', 's.name as person', DB::raw("c.name as detail"))->orderByRaw("l.status = 'Pending' desc")->orderByDesc('l.created_at')->paginate(20),
        ]);
    }

    public function staff(): View
    {
        return view('admin.leaves', [
            'title' => 'Staff leave', 'subtitle' => 'Leave requests from teachers.', 'decideRoute' => 'admin.staff-leaves.decide',
            'leaves' => DB::table('staff_leaves as l')->join('employees as e', 'l.employee_id', '=', 'e.id')
                ->select('l.*', 'e.name as person', 'e.designation as detail')->orderByRaw("l.status = 'Pending' desc")->orderByDesc('l.created_at')->paginate(20),
        ]);
    }

    public function decideStudent(int $id, string $decision): RedirectResponse
    {
        return $this->decide('student_leaves', $id, $decision);
    }

    public function decideStaff(int $id, string $decision): RedirectResponse
    {
        return $this->decide('staff_leaves', $id, $decision);
    }

    private function decide(string $table, int $id, string $decision): RedirectResponse
    {
        $status = $decision === 'approve' ? 'Approved' : 'Rejected';
        abort_unless(DB::table($table)->where(['id' => $id, 'status' => 'Pending'])->update(['status' => $status, 'updated_at' => now()]), 404);

        return back()->with('ok', "Leave $status.");
    }
}
