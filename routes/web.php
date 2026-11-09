<?php

use App\Http\Controllers\Admin\AdmissionController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\FeeController;
use App\Http\Controllers\Admin\LeaveController;
use App\Http\Controllers\Admin\LibraryController;
use App\Http\Controllers\Admin\PayrollController;
use App\Http\Controllers\Admin\PromotionController;
use App\Http\Controllers\Admin\ResultController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\StaffAttendanceController;
use App\Http\Controllers\Admin\StudentController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\ClassTestMarkController;
use App\Http\Controllers\MarkController;
use App\Http\Controllers\PortalController;
use App\Http\Controllers\PublicController;
use App\Http\Controllers\ResourceController;
use App\Http\Controllers\TeacherController;
use App\Http\Controllers\TimetableController;
use Illuminate\Support\Facades\Route;

// Public website
Route::controller(PublicController::class)->group(function () {
    Route::get('/', 'home')->name('home');
    Route::get('/notices', 'notices')->name('notices.index');
    Route::get('/notices/{id}', 'notice')->whereNumber('id')->name('notices.show');
    Route::get('/teachers', 'teachers')->name('teachers');
    Route::get('/office-staff', 'officeStaff')->name('office-staff');
    Route::get('/people/{group}', 'people')->name('people');
    Route::get('/page/{slug}', 'page')->name('page');
    Route::get('/message/{slug}', 'message')->name('message');
    Route::get('/events', 'events')->name('events');
    Route::get('/routine', 'routine')->name('routine');
    Route::get('/holidays', 'holidays')->name('holidays');
    Route::get('/transport', 'transport')->name('transport');
    Route::get('/scholars', 'scholars')->name('scholars');
    Route::get('/gallery/photos', 'photos')->name('gallery.photos');
    Route::get('/gallery/videos', 'videos')->name('gallery.videos');
    Route::get('/credits', 'credits')->name('credits');
    Route::get('/result', 'result')->name('result');
    Route::post('/result', 'resultLookup')->middleware('throttle:15,1')->name('result.lookup');
    Route::get('/apply', 'apply')->name('apply');
    Route::post('/apply', 'applyStore')->middleware('throttle:6,1')->name('apply.store');
    Route::get('/contact', 'contact')->name('contact');
    Route::post('/contact', 'contactStore')->middleware('throttle:6,1')->name('contact.store');
});

// Sign in
Route::middleware('guest')->group(function () {
    Route::get('/student-login', [AuthController::class, 'studentForm'])->name('student.login');
    Route::get('/staff-login', [AuthController::class, 'staffForm'])->name('staff.login');
    Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:20,1')->name('login');
});
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::put('/password', [AuthController::class, 'changePassword'])->name('password.change');
});

/** Pages shared by admins and teachers. Controllers narrow teachers to their assigned classes. */
$staffRoutes = function () {
    Route::get('/r/{resource}', [ResourceController::class, 'index'])->name('resource.index');
    Route::post('/r/{resource}', [ResourceController::class, 'store'])->name('resource.store');
    Route::put('/r/{resource}/{id}', [ResourceController::class, 'update'])->whereNumber('id')->name('resource.update');
    Route::delete('/r/{resource}/{id}', [ResourceController::class, 'destroy'])->whereNumber('id')->name('resource.destroy');

    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');
    Route::get('/marks', [MarkController::class, 'index'])->name('marks.index');
    Route::post('/marks', [MarkController::class, 'store'])->name('marks.store');
    Route::get('/class-tests/{classTest}/marks', [ClassTestMarkController::class, 'edit'])->whereNumber('classTest')->name('class-test-marks.edit');
    Route::post('/class-tests/{classTest}/marks', [ClassTestMarkController::class, 'update'])->whereNumber('classTest')->name('class-test-marks.update');
};

// Admin
Route::middleware(['auth', 'role:admin'])->prefix('admin')->name('admin.')->group(function () use ($staffRoutes) {
    $staffRoutes();
    Route::get('/', DashboardController::class)->name('dashboard');
    Route::get('/timetable', [TimetableController::class, 'admin'])->name('timetable.index');

    Route::post('/students/{student}/reset-password', [StudentController::class, 'resetPassword'])->name('students.reset-password');
    Route::resource('students', StudentController::class);

    Route::get('/employees', [EmployeeController::class, 'index'])->name('employees.index');
    Route::post('/employees', [EmployeeController::class, 'store'])->name('employees.store');
    Route::put('/employees/{employee}', [EmployeeController::class, 'update'])->name('employees.update');
    Route::delete('/employees/{employee}', [EmployeeController::class, 'destroy'])->name('employees.destroy');
    Route::post('/employees/{employee}/reset-password', [EmployeeController::class, 'resetPassword'])->name('employees.reset-password');

    Route::get('/admissions', [AdmissionController::class, 'index'])->name('admissions.index');
    Route::post('/admissions/{admission}/approve', [AdmissionController::class, 'approve'])->name('admissions.approve');
    Route::post('/admissions/{admission}/reject', [AdmissionController::class, 'reject'])->name('admissions.reject');

    Route::get('/promotions', [PromotionController::class, 'index'])->name('promotions.index');
    Route::post('/promotions', [PromotionController::class, 'store'])->name('promotions.store');

    Route::get('/student-leaves', [LeaveController::class, 'students'])->name('student-leaves.index');
    Route::post('/student-leaves/{id}/{decision}', [LeaveController::class, 'decideStudent'])->whereNumber('id')->whereIn('decision', ['approve', 'reject'])->name('student-leaves.decide');
    Route::get('/staff-leaves', [LeaveController::class, 'staff'])->name('staff-leaves.index');
    Route::post('/staff-leaves/{id}/{decision}', [LeaveController::class, 'decideStaff'])->whereNumber('id')->whereIn('decision', ['approve', 'reject'])->name('staff-leaves.decide');

    Route::get('/staff-attendance', [StaffAttendanceController::class, 'index'])->name('staff-attendance.index');
    Route::post('/staff-attendance', [StaffAttendanceController::class, 'store'])->name('staff-attendance.store');

    Route::get('/payroll', [PayrollController::class, 'index'])->name('payroll.index');
    Route::post('/payroll/generate', [PayrollController::class, 'generate'])->name('payroll.generate');
    Route::post('/payroll/{id}/pay', [PayrollController::class, 'pay'])->whereNumber('id')->name('payroll.pay');

    Route::get('/results', [ResultController::class, 'index'])->name('results.index');
    Route::post('/results/{exam}/publish', [ResultController::class, 'publish'])->whereNumber('exam')->name('results.publish');

    Route::get('/fees', [FeeController::class, 'index'])->name('fees.index');
    Route::get('/fees/dues', [FeeController::class, 'dues'])->name('fees.dues');
    Route::post('/fees/invoice', [FeeController::class, 'storeInvoice'])->name('fees.invoice');
    Route::post('/fees/generate', [FeeController::class, 'generate'])->name('fees.generate');
    Route::post('/fees/{invoice}/collect', [FeeController::class, 'collect'])->name('fees.collect');

    Route::get('/library', [LibraryController::class, 'index'])->name('library.index');
    Route::post('/library', [LibraryController::class, 'store'])->name('library.store');
    Route::post('/library/{id}/return', [LibraryController::class, 'returnBook'])->whereNumber('id')->name('library.return');

    Route::get('/settings', [SettingController::class, 'edit'])->name('settings.edit');
    Route::put('/settings', [SettingController::class, 'update'])->name('settings.update');
});

// Teacher
Route::middleware(['auth', 'role:teacher'])->prefix('teacher')->name('teacher.')->group(function () use ($staffRoutes) {
    $staffRoutes();
    Route::controller(TeacherController::class)->group(function () {
        Route::get('/', 'dashboard')->name('dashboard');
        Route::get('/students', 'students')->name('students');
        Route::get('/notices', 'notices')->name('notices');
        Route::get('/leave', 'leave')->name('leave.index');
        Route::post('/leave', 'leaveStore')->name('leave.store');
        Route::get('/profile', 'profile')->name('profile');
    });
    Route::get('/timetable', [TimetableController::class, 'teacher'])->name('timetable');
});

// Student portal
Route::middleware(['auth', 'role:student'])->prefix('portal')->name('portal.')->controller(PortalController::class)->group(function () {
    Route::get('/', 'dashboard')->name('dashboard');
    Route::get('/profile', 'profile')->name('profile');
    Route::get('/routine', 'routine')->name('routine');
    Route::get('/attendance', 'attendance')->name('attendance');
    Route::get('/assignments', 'assignments')->name('assignments');
    Route::get('/diary', 'diary')->name('diary');
    Route::get('/class-tests', 'classTests')->name('class-tests');
    Route::get('/results', 'results')->name('results');
    Route::get('/accounts', 'accounts')->name('accounts');
    Route::get('/notices', 'notices')->name('notices');
    Route::get('/notices/{id}', 'notice')->whereNumber('id')->name('notices.show');
    Route::get('/leave', 'leave')->name('leave.index');
    Route::post('/leave', 'leaveStore')->name('leave.store');
    Route::get('/downloads', 'downloads')->name('downloads');
    Route::get('/id-card', 'idCard')->name('id-card');
});
