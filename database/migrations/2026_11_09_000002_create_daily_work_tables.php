<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $statuses = ['Present', 'Absent', 'Late', 'Leave'];

        Schema::create('attendance', function (Blueprint $t) use ($statuses) {
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->date('date')->index();
            $t->enum('status', $statuses);
            $t->foreignId('marked_by')->nullable()->constrained('users')->nullOnDelete();
            $t->primary(['student_id', 'date']);
        });

        Schema::create('staff_attendance', function (Blueprint $t) use ($statuses) {
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->date('date');
            $t->enum('status', $statuses);
            $t->primary(['employee_id', 'date']);
        });

        Schema::create('exams', function (Blueprint $t) {
            $t->id();
            $t->foreignId('session_id')->constrained('academic_sessions');
            $t->string('name');
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->boolean('is_published')->default(false);
        });

        Schema::create('exam_routines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $t->date('date');
            $t->string('start_time', 5)->nullable();
            $t->string('end_time', 5)->nullable();
            $t->string('room')->nullable();
        });

        Schema::create('marks', function (Blueprint $t) {
            $t->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $t->decimal('marks', 6, 2);
            $t->decimal('full_marks', 6, 2)->default(100);
            $t->foreignId('entered_by')->nullable()->constrained('users')->nullOnDelete();
            $t->primary(['exam_id', 'student_id', 'subject_id']);
        });

        Schema::create('grades', function (Blueprint $t) {
            $t->id();
            $t->string('letter')->unique();
            $t->decimal('point', 3, 2);
            $t->decimal('min_mark', 5, 2);
            $t->decimal('max_mark', 5, 2);
        });

        Schema::create('class_tests', function (Blueprint $t) {
            $t->id();
            $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
            $t->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $t->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $t->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->date('date');
            $t->decimal('full_marks', 6, 2)->default(20);
        });

        Schema::create('class_test_marks', function (Blueprint $t) {
            $t->foreignId('class_test_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->decimal('marks', 6, 2);
            $t->primary(['class_test_id', 'student_id']);
        });

        foreach (['homework' => 'due_date', 'diaries' => 'date'] as $name => $dateColumn) {
            Schema::create($name, function (Blueprint $t) use ($name, $dateColumn) {
                $t->id();
                $t->foreignId('class_id')->constrained('classes')->cascadeOnDelete();
                $t->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
                $t->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
                $t->foreignId('employee_id')->nullable()->constrained()->nullOnDelete();
                $t->string('title');
                $t->text('body')->nullable();
                if ($name === 'homework') {
                    $t->date('assigned_date');
                }
                $t->date($dateColumn);
            });
        }

        Schema::create('notices', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->text('body')->nullable();
            $t->enum('audience', ['Everyone', 'Students', 'Staff'])->default('Everyone');
            $t->string('category')->default('General');
            $t->date('date');
            $t->boolean('pinned')->default(false);
        });

        Schema::create('student_leaves', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->date('from_date');
            $t->date('to_date');
            $t->string('reason');
            $t->text('details')->nullable();
            $t->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $t->timestamps();
        });

        Schema::create('staff_leaves', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->string('type');
            $t->date('from_date');
            $t->date('to_date');
            $t->string('reason');
            $t->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending');
            $t->timestamps();
        });

        Schema::create('holidays', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->date('from_date');
            $t->date('to_date');
        });

        Schema::create('downloads', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('category')->nullable();
            $t->string('url')->nullable();
            $t->date('date')->nullable();
        });
    }

    public function down(): void
    {
        foreach (['downloads', 'holidays', 'staff_leaves', 'student_leaves', 'notices', 'diaries', 'homework', 'class_test_marks', 'class_tests', 'grades', 'marks', 'exam_routines', 'exams', 'staff_attendance', 'attendance'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
