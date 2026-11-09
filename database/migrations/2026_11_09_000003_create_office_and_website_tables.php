<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fee_heads', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->decimal('amount', 12, 2)->default(0);
            $t->enum('frequency', ['Monthly', 'Yearly', 'One time'])->default('Monthly');
        });

        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_no')->unique();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('fee_head_id')->nullable()->constrained()->nullOnDelete();
            $t->string('title');
            $t->string('period')->nullable();
            $t->decimal('amount', 12, 2);
            $t->decimal('paid', 12, 2)->default(0);
            $t->date('due_date')->nullable();
            $t->enum('status', ['Unpaid', 'Partial', 'Paid'])->default('Unpaid')->index();
            $t->unique(['student_id', 'fee_head_id', 'period']);
        });

        Schema::create('payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $t->decimal('amount', 12, 2);
            $t->string('method');
            $t->string('reference')->nullable();
            $t->date('paid_on')->index();
            $t->foreignId('received_by')->nullable()->constrained('users')->nullOnDelete();
        });

        Schema::create('account_heads', function (Blueprint $t) {
            $t->id();
            $t->string('name')->unique();
            $t->enum('kind', ['Income', 'Expense']);
        });

        Schema::create('transactions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('account_head_id')->constrained();
            $t->decimal('amount', 12, 2);
            $t->date('date');
            $t->string('note')->nullable();
        });

        Schema::create('payroll', function (Blueprint $t) {
            $t->id();
            $t->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $t->string('month', 7);
            $t->decimal('basic', 12, 2);
            $t->decimal('allowance', 12, 2)->default(0);
            $t->decimal('deduction', 12, 2)->default(0);
            $t->decimal('net', 12, 2);
            $t->enum('status', ['Unpaid', 'Paid'])->default('Unpaid');
            $t->date('paid_on')->nullable();
            $t->unique(['employee_id', 'month']);
        });

        Schema::create('admissions', function (Blueprint $t) {
            $t->id();
            $t->string('reference')->unique();
            $t->string('name');
            $t->string('gender');
            $t->date('dob');
            $t->foreignId('class_id')->constrained('classes');
            $t->string('guardian_name');
            $t->string('phone');
            $t->string('email')->nullable();
            $t->string('address')->nullable();
            $t->string('previous_school')->nullable();
            $t->enum('status', ['Pending', 'Approved', 'Rejected'])->default('Pending')->index();
            $t->foreignId('student_id')->nullable()->constrained()->nullOnDelete();
            $t->timestamps();
        });

        Schema::create('promotions', function (Blueprint $t) {
            $t->id();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->foreignId('from_class_id')->constrained('classes');
            $t->foreignId('to_class_id')->constrained('classes');
            $t->foreignId('from_session_id')->constrained('academic_sessions');
            $t->foreignId('to_session_id')->constrained('academic_sessions');
            $t->timestamps();
        });

        Schema::create('books', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('author')->nullable();
            $t->string('isbn')->nullable();
            $t->string('category')->nullable();
            $t->unsignedInteger('copies')->default(1);
            $t->string('shelf')->nullable();
        });

        Schema::create('book_issues', function (Blueprint $t) {
            $t->id();
            $t->foreignId('book_id')->constrained()->cascadeOnDelete();
            $t->foreignId('student_id')->constrained()->cascadeOnDelete();
            $t->date('issue_date');
            $t->date('due_date');
            $t->date('return_date')->nullable();
        });

        Schema::create('transport_routes', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('vehicle')->nullable();
            $t->string('driver')->nullable();
            $t->string('phone')->nullable();
            $t->decimal('fare', 12, 2)->default(0);
        });

        Schema::create('hostels', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('kind')->nullable();
            $t->unsignedInteger('capacity')->default(0);
            $t->string('warden')->nullable();
            $t->string('phone')->nullable();
        });

        Schema::create('web_pages', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('title');
            $t->text('body')->nullable();
            $t->string('menu_group')->nullable();
            $t->string('image')->nullable();
        });

        Schema::create('web_people', function (Blueprint $t) {
            $t->id();
            $t->string('group_name')->index();
            $t->string('name');
            $t->string('designation')->nullable();
            $t->string('phone')->nullable();
            $t->string('photo')->nullable();
            $t->unsignedInteger('position')->default(0);
        });

        Schema::create('web_messages', function (Blueprint $t) {
            $t->id();
            $t->string('slug')->unique();
            $t->string('heading');
            $t->string('name');
            $t->string('designation')->nullable();
            $t->string('photo')->nullable();
            $t->text('body')->nullable();
        });

        Schema::create('events', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->date('date');
            $t->string('location')->nullable();
            $t->text('body')->nullable();
        });

        Schema::create('gallery', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('category')->nullable();
            $t->string('image_url');
        });

        Schema::create('videos', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('src');
            $t->string('poster')->nullable();
        });

        Schema::create('achievers', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('exam')->nullable();
            $t->string('gpa')->nullable();
            $t->string('year')->nullable();
            $t->string('photo')->nullable();
        });

        Schema::create('links', function (Blueprint $t) {
            $t->id();
            $t->string('title');
            $t->string('url');
        });

        Schema::create('contact_messages', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('subject')->nullable();
            $t->text('message');
            $t->boolean('is_read')->default(false);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        foreach (['contact_messages', 'links', 'achievers', 'videos', 'gallery', 'events', 'web_messages', 'web_people', 'web_pages', 'hostels', 'transport_routes', 'book_issues', 'books', 'promotions', 'admissions', 'payroll', 'transactions', 'account_heads', 'payments', 'invoices', 'fee_heads'] as $table) {
            Schema::dropIfExists($table);
        }
    }
};
