<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

#[Fillable(['invoice_no', 'student_id', 'fee_head_id', 'title', 'period', 'amount', 'paid', 'due_date', 'status'])]
class Invoice extends Model
{
    public $timestamps = false;

    protected function casts(): array
    {
        return ['amount' => 'float', 'paid' => 'float', 'due_date' => 'date'];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function due(): float
    {
        return round($this->amount - $this->paid, 2);
    }

    public static function nextNumber(int $offset = 0): string
    {
        $last = (int) DB::table('invoices')->max('id');

        return sprintf('INV-%s-%05d', now()->year, $last + 1 + $offset);
    }
}
