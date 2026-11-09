<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Support\Lookups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class FeeController extends Controller
{
    public const METHODS = ['Cash', 'bKash', 'Nagad', 'Bank'];

    public function index(Request $request): View
    {
        $invoices = $this->invoiceQuery()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = '%'.trim($request->query('q')).'%';
                $query->where(fn ($q) => $q->where('i.invoice_no', 'like', $term)->orWhere('s.name', 'like', $term)->orWhere('s.student_no', 'like', $term));
            })
            ->when($request->filled('status'), fn ($q) => $q->where('i.status', $request->query('status')))
            ->when($request->filled('class_id'), fn ($q) => $q->where('s.class_id', $request->integer('class_id')))
            ->orderByRaw("i.status = 'Paid'")->orderByDesc('i.id')->paginate(20)->withQueryString();

        return view('admin.fees', [
            'invoices' => $invoices, 'classes' => Lookups::get('classes'), 'heads' => DB::table('fee_heads')->orderBy('name')->get(), 'methods' => self::METHODS,
            'collected' => (float) DB::table('payments')->sum('amount'),
            'outstanding' => $this->outstanding(),
            'students' => DB::table('students')->where('status', 'Active')->orderBy('name')->get(['id', 'name', 'student_no']),
        ]);
    }

    public function dues(Request $request): View
    {
        $dues = $this->invoiceQuery()->where('i.status', '!=', 'Paid')
            ->when($request->filled('class_id'), fn ($q) => $q->where('s.class_id', $request->integer('class_id')))
            ->orderBy('i.due_date')->paginate(30)->withQueryString();

        return view('admin.dues', ['dues' => $dues, 'classes' => Lookups::get('classes'), 'outstanding' => $this->outstanding()]);
    }

    public function storeInvoice(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'student_id' => ['required', 'exists:students,id'], 'fee_head_id' => ['required', 'exists:fee_heads,id'],
            'amount' => ['required', 'numeric', 'min:1'], 'period' => ['required', 'string', 'max:30'], 'due_date' => ['required', 'date'],
        ]);

        if (DB::table('invoices')->where($request->only('student_id', 'fee_head_id', 'period'))->exists()) {
            throw ValidationException::withMessages(['period' => 'This student already has that fee for this period.']);
        }

        Invoice::create($data + ['invoice_no' => Invoice::nextNumber(), 'title' => DB::table('fee_heads')->where('id', $data['fee_head_id'])->value('name'), 'status' => 'Unpaid']);

        return back()->with('ok', 'Invoice created.');
    }

    public function generate(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'fee_head_id' => ['required', 'exists:fee_heads,id'], 'month' => ['required', 'date_format:Y-m'],
            'due_date' => ['required', 'date'], 'class_id' => ['nullable', 'exists:classes,id'],
        ]);
        $head = DB::table('fee_heads')->find($data['fee_head_id']);
        $students = DB::table('students')->where('status', 'Active')->when($data['class_id'] ?? null, fn ($q, $id) => $q->where('class_id', $id))->pluck('id');

        $created = DB::transaction(function () use ($students, $head, $data) {
            $count = 0;
            foreach ($students as $studentId) {
                $count += DB::table('invoices')->insertOrIgnore([
                    'invoice_no' => Invoice::nextNumber(), 'student_id' => $studentId, 'fee_head_id' => $head->id, 'title' => $head->name,
                    'period' => $data['month'], 'amount' => $head->amount, 'paid' => 0, 'due_date' => $data['due_date'], 'status' => 'Unpaid',
                ]);
            }

            return $count;
        });

        return back()->with('ok', $created ? "$created invoices created." : 'Invoices for this month already exist.');
    }

    public function collect(Request $request, Invoice $invoice): RedirectResponse
    {
        $data = $request->validate([
            'amount' => ['required', 'numeric', 'min:1'], 'method' => ['required', 'in:'.implode(',', self::METHODS)],
            'reference' => ['nullable', 'string', 'max:80'], 'paid_on' => ['required', 'date', 'before_or_equal:today'],
        ]);

        DB::transaction(function () use ($invoice, $data, $request) {
            $fresh = Invoice::lockForUpdate()->findOrFail($invoice->id);
            if ((float) $data['amount'] > $fresh->due()) {
                throw ValidationException::withMessages(['amount' => 'Amount cannot be more than the due of '.money($fresh->due()).'.']);
            }
            $paid = round($fresh->paid + $data['amount'], 2);
            $fresh->update(['paid' => $paid, 'status' => $paid >= $fresh->amount ? 'Paid' : 'Partial']);
            DB::table('payments')->insert($data + ['invoice_id' => $fresh->id, 'received_by' => $request->user()->id]);
        });

        return back()->with('ok', 'Payment recorded.');
    }

    private function invoiceQuery()
    {
        return DB::table('invoices as i')->join('students as s', 'i.student_id', '=', 's.id')->leftJoin('classes as c', 's.class_id', '=', 'c.id')
            ->select('i.*', 's.name as student_name', 's.student_no', 's.guardian_phone', 'c.name as class_name');
    }

    private function outstanding(): float
    {
        return (float) DB::table('invoices')->where('status', '!=', 'Paid')->selectRaw('coalesce(sum(amount - paid), 0) as due')->value('due');
    }
}
