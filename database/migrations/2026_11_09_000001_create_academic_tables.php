<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('settings', function (Blueprint $t) {
            $t->string('key')->primary();
            $t->text('value')->nullable();
        });

        Schema::create('academic_sessions', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->boolean('is_current')->default(false);
        });

        Schema::create('classes', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('sections', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $t->string('name');
            $t->unique(['class_id', 'name']);
        });

        Schema::create('shifts', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
        });

        Schema::create('subjects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $t->string('name');
            $t->string('code')->nullable();
            $t->unique(['class_id', 'name']);
        });

        Schema::create('students', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $t->string('student_no')->unique();
            $t->string('name');
            $t->string('photo')->nullable();
            $t->string('gender')->default('Male');
            $t->date('dob')->nullable();
            $t->string('blood_group')->nullable();
            $t->string('religion')->nullable();
            $t->string('birth_reg')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('present_address')->nullable();
            $t->string('permanent_address')->nullable();
            $t->text('medical_notes')->nullable();
            $t->foreignId('session_id')->constrained('academic_sessions');
            $t->foreignId('class_id')->constrained('classes');
            $t->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $t->foreignId('shift_id')->nullable()->constrained('shifts')->nullOnDelete();
            $t->unsignedInteger('roll')->nullable();
            $t->date('admission_date')->nullable();
            $t->enum('status', ['Active', 'Inactive', 'Transferred', 'Passed Out'])->default('Active');
            $t->string('father_name')->nullable();
            $t->string('father_phone')->nullable();
            $t->string('father_occupation')->nullable();
            $t->string('mother_name')->nullable();
            $t->string('mother_phone')->nullable();
            $t->string('mother_occupation')->nullable();
            $t->string('guardian_name')->nullable();
            $t->string('guardian_relation')->nullable();
            $t->string('guardian_phone')->nullable();
            $t->string('previous_school')->nullable();
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['class_id', 'section_id', 'status']);
        });

        Schema::create('employees', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->unique()->constrained()->nullOnDelete();
            $t->string('employee_no')->unique();
            $t->string('name');
            $t->string('photo')->nullable();
            $t->string('designation')->nullable();
            $t->string('department')->nullable();
            $t->enum('kind', ['Teacher', 'Staff'])->default('Teacher');
            $t->string('gender')->nullable();
            $t->string('phone')->nullable();
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->date('joining_date')->nullable();
            $t->decimal('basic_salary', 12, 2)->default(0);
            $t->enum('status', ['Active', 'Inactive'])->default('Active');
            $t->boolean('show_public')->default(true);
            $t->timestamps();
        });

        Schema::create('teacher_subjects', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $t->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $t->boolean('is_class_teacher')->default(false);
            $t->unique(['class_id', 'section_id', 'subject_id']);
            $t->index('employee_id');
        });

        Schema::create('routines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $t->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $t->enum('day', ['Saturday', 'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday']);
            $t->string('start_time', 5);
            $t->string('end_time', 5);
            $t->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $t->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $t->string('room')->nullable();
            $t->index(['class_id', 'section_id', 'day']);
        });
    }

    public function down(): void
    {
        foreach (['routines', 'teacher_subjects', 'employees', 'students', 'subjects', 'shifts', 'sections', 'classes', 'academic_sessions', 'settings'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
