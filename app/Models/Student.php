<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'user_id', 'student_no', 'name', 'photo', 'gender', 'dob', 'blood_group', 'religion', 'birth_reg', 'phone', 'email',
    'present_address', 'permanent_address', 'medical_notes', 'session_id', 'class_id', 'section_id', 'shift_id', 'roll',
    'admission_date', 'status', 'father_name', 'father_phone', 'father_occupation', 'mother_name', 'mother_phone',
    'mother_occupation', 'guardian_name', 'guardian_relation', 'guardian_phone', 'previous_school', 'notes',
])]
class Student extends Model
{
    protected function casts(): array
    {
        return ['dob' => 'date', 'admission_date' => 'date'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function schoolClass(): BelongsTo
    {
        return $this->belongsTo(SchoolClass::class, 'class_id');
    }

    public function section(): BelongsTo
    {
        return $this->belongsTo(Section::class);
    }

    public function shift(): BelongsTo
    {
        return $this->belongsTo(Shift::class);
    }

    public function session(): BelongsTo
    {
        return $this->belongsTo(AcademicSession::class, 'session_id');
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    /** Next register number for a session: two-digit year followed by a five-digit sequence. */
    public static function nextNumber(string $sessionName): string
    {
        $prefix = substr($sessionName, -2);
        $last = DB::table('students')->where('student_no', 'like', $prefix.'%')->max('student_no');

        return $last ? (string) ((int) $last + 1) : $prefix.'00001';
    }
}
