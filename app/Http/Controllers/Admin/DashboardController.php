<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __invoke(): View
    {
        $today = today()->toDateString();
        $month = now()->format('Y-m');

        $marked = DB::table('attendance')->where('date', $today)->count();
        $present = DB::table('attendance')->where('date', $today)->whereIn('status', ['Present', 'Late'])->count();

        $attendance = DB::table('attendance')->where('date', '>=', today()->subDays(20)->toDateString())
            ->selectRaw("date, round(100.0 * sum(status in ('Present','Late')) / count(*), 1) as rate")
            ->groupBy('date')->orderBy('date')->get()->take(-14)
            ->map(fn ($r) => ['label' => \Carbon\Carbon::parse($r->date)->format('M j'), 'value' => (float) $r->rate, 'display' => $r->rate.'%'])->values()->all();

        $collected = DB::table('payments')->where('paid_on', '>=', now()->startOfMonth()->subMonths(5)->toDateString())
            ->selectRaw("substr(paid_on, 1, 7) as month, sum(amount) as total")->groupBy('month')->pluck('total', 'month');
        $fees = collect(range(5, 0))->map(function ($back) use ($collected) {
            $m = now()->startOfMonth()->subMonths($back);

            return ['label' => $m->format('M'), 'value' => (float) ($collected[$m->format('Y-m')] ?? 0), 'display' => money($collected[$m->format('Y-m')] ?? 0)];
        })->all();

        $byClass = DB::table('classes as c')->leftJoin('students as s', function ($j) {
            $j->on('s.class_id', '=', 'c.id')->where('s.status', 'Active');
        })->select('c.name', DB::raw('count(s.id) as total'))->groupBy('c.id', 'c.name', 'c.position')->orderBy('c.position')->get();

        return view('admin.dashboard', [
            'stats' => [
                'students' => DB::table('students')->where('status', 'Active')->count(),
                'staff' => DB::table('employees')->where('status', 'Active')->count(),
                'attendance' => $marked ? round($present / $marked * 100, 1).'%' : 'Not marked',
                'markedToday' => $marked,
                'feesMonth' => (float) DB::table('payments')->where('paid_on', 'like', "$month%")->sum('amount'),
                'due' => (float) DB::table('invoices')->where('status', '!=', 'Paid')->selectRaw('coalesce(sum(amount - paid), 0) as due')->value('due'),
                'pendingAdmissions' => DB::table('admissions')->where('status', 'Pending')->count(),
                'upcomingExams' => DB::table('exams')->where('start_date', '>=', $today)->count(),
                'pendingLeaves' => DB::table('student_leaves')->where('status', 'Pending')->count() + DB::table('staff_leaves')->where('status', 'Pending')->count(),
            ],
            'attendance' => $attendance,
            'fees' => $fees,
            'gender' => DB::table('students')->where('status', 'Active')->select('gender', DB::raw('count(*) as total'))->groupBy('gender')->pluck('total', 'gender'),
            'invoiceStatus' => DB::table('invoices')->select('status', DB::raw('count(*) as total'))->groupBy('status')->pluck('total', 'status'),
            'byClass' => $byClass,
            'admissions' => DB::table('admissions as a')->join('classes as c', 'a.class_id', '=', 'c.id')->select('a.*', 'c.name as class_name')->orderByDesc('a.created_at')->limit(5)->get(),
            'payments' => DB::table('payments as p')->join('invoices as i', 'p.invoice_id', '=', 'i.id')->join('students as s', 'i.student_id', '=', 's.id')->select('p.*', 's.name as student_name')->orderByDesc('p.paid_on')->orderByDesc('p.id')->limit(5)->get(),
            'notices' => DB::table('notices')->orderByDesc('pinned')->orderByDesc('date')->limit(5)->get(),
        ]);
    }
}
