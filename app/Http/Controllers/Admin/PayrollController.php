<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PayrollController extends Controller
{
    public function index(Request $request): View
    {
        $month = preg_match('/^\d{4}-\d{2}$/', (string) $request->query('month')) ? $request->query('month') : now()->format('Y-m');
        $slips = DB::table('payroll as p')->join('employees as e', 'p.employee_id', '=', 'e.id')->select('p.*', 'e.name', 'e.designation', 'e.employee_no')->where('p.month', $month)->orderBy('e.employee_no')->get();

        return view('admin.payroll', ['month' => $month, 'slips' => $slips]);
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate(['month' => ['required', 'date_format:Y-m']]);
        $created = 0;

        foreach (DB::table('employees')->where('status', 'Active')->get() as $employee) {
            $allowance = round($employee->basic_salary * 0.1);
            $created += DB::table('payroll')->insertOrIgnore([
                'employee_id' => $employee->id, 'month' => $data['month'], 'basic' => $employee->basic_salary, 'allowance' => $allowance, 'deduction' => 0,
                'net' => $employee->basic_salary + $allowance, 'status' => 'Unpaid',
            ]);
        }

        return back()->with('ok', $created ? "$created salary slips created." : 'Slips for this month already exist.');
    }

    public function pay(int $id): RedirectResponse
    {
        abort_unless(DB::table('payroll')->where(['id' => $id, 'status' => 'Unpaid'])->update(['status' => 'Paid', 'paid_on' => today()->toDateString()]), 404);

        return back()->with('ok', 'Salary marked as paid.');
    }
}
