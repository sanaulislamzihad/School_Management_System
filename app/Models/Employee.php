<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable([
    'user_id', 'employee_no', 'name', 'photo', 'designation', 'department', 'kind', 'gender', 'phone', 'email', 'address',
    'joining_date', 'basic_salary', 'status', 'show_public',
])]
class Employee extends Model
{
    protected function casts(): array
    {
        return ['joining_date' => 'date', 'show_public' => 'boolean', 'basic_salary' => 'float'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(TeacherSubject::class);
    }

    /** True when this teacher is linked to the class and section, optionally for one subject. */
    public function teaches(int $classId, int $sectionId, ?int $subjectId = null): bool
    {
        return DB::table('teacher_subjects')
            ->where(['employee_id' => $this->id, 'class_id' => $classId, 'section_id' => $sectionId])
            ->when($subjectId, fn ($q) => $q->where('subject_id', $subjectId))
            ->exists();
    }

    public static function nextNumber(): string
    {
        $last = DB::table('employees')->max('employee_no');

        return 'T'.(($last ? (int) substr($last, 1) : 1000) + 1);
    }
}
