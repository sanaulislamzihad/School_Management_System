<?php

namespace Tests\Feature;

use App\Models\Employee;
use App\Models\Student;
use App\Models\User;
use App\Support\ResourceRegistry;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Tests\TestCase;

class SchoolPagesTest extends TestCase
{
    use RefreshDatabase;

    protected $seed = true;

    protected $seeder = DatabaseSeeder::class;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function user(string $role): User
    {
        if ($role === 'teacher') {
            return Employee::whereHas('assignments')->orderBy('id')->firstOrFail()->user;
        }

        return User::where('role', $role)->firstOrFail();
    }

    /** Every GET page in an area, with sample route parameters. */
    private function urls(string $prefix, string $role): array
    {
        $urls = [];
        foreach (Route::getRoutes()->getRoutes() as $route) {
            $name = (string) $route->getName();
            if (! str_starts_with($name, $prefix) || ! in_array('GET', $route->methods(), true)) {
                continue;
            }

            if (in_array('resource', $route->parameterNames(), true)) {
                $reflection = new \ReflectionMethod(ResourceRegistry::class, 'all');
                foreach (array_keys($reflection->invoke(null)) as $resource) {
                    if (in_array($role, ResourceRegistry::get($resource)['roles'] ?? ['admin'], true)) {
                        $urls[] = route($name, ['resource' => $resource]);
                    }
                }

                continue;
            }

            $params = [];
            foreach ($route->parameterNames() as $parameter) {
                $params[$parameter] = match ($parameter) {
                    'student' => Student::first()->id,
                    'classTest' => DB::table('class_tests')->when($role === 'teacher', fn ($q) => $q->where('employee_id', $this->user('teacher')->employee->id))->value('id'),
                    'id' => DB::table('notices')->where('audience', 'Everyone')->value('id'),
                };
            }
            $urls[] = route($name, $params);
        }

        return $urls;
    }

    public function test_public_pages_load(): void
    {
        foreach (['/', '/notices', '/teachers', '/office-staff', '/people/governing-body', '/page/history', '/message/head-teacher', '/events', '/routine', '/holidays', '/transport', '/scholars', '/gallery/photos', '/gallery/videos', '/credits', '/result', '/apply', '/contact', '/student-login', '/staff-login'] as $url) {
            $this->get($url)->assertOk();
        }
    }

    public function test_every_dashboard_page_loads_for_its_role(): void
    {
        foreach (['admin' => 'admin.', 'teacher' => 'teacher.', 'student' => 'portal.'] as $role => $prefix) {
            $user = $this->user($role);
            foreach ($this->urls($prefix, $role) as $url) {
                $this->actingAs($user)->get($url)->assertOk();
            }
        }
    }

    public function test_guests_are_sent_to_sign_in(): void
    {
        $this->get('/admin')->assertRedirect(route('staff.login'));
        $this->get('/teacher')->assertRedirect(route('staff.login'));
        $this->get('/portal')->assertRedirect(route('student.login'));
    }

    public function test_roles_cannot_enter_other_areas(): void
    {
        $this->actingAs($this->user('student'))->get('/admin')->assertForbidden();
        $this->actingAs($this->user('student'))->get('/teacher')->assertForbidden();
        $this->actingAs($this->user('teacher'))->get('/admin')->assertForbidden();
        $this->actingAs($this->user('teacher'))->get('/portal')->assertForbidden();
        $this->actingAs($this->user('admin'))->get('/portal')->assertForbidden();
    }

    public function test_sign_in_respects_portal(): void
    {
        $student = $this->user('student');

        $this->post('/login', ['login' => $student->login, 'password' => 'student123', 'portal' => 'staff'])->assertSessionHasErrors('login');
        $this->assertGuest();

        $this->post('/login', ['login' => $student->login, 'password' => 'student123', 'portal' => 'student'])->assertRedirect(route('portal.dashboard'));
        $this->assertAuthenticatedAs($student);
    }

    public function test_teacher_is_limited_to_assigned_classes(): void
    {
        $teacher = $this->user('teacher');
        $employee = Employee::where('user_id', $teacher->id)->whereHas('assignments')->first() ?? Employee::whereHas('assignments')->first();
        $teacher = $employee->user;

        $other = DB::table('sections')->whereNotIn('id', DB::table('teacher_subjects')->where('employee_id', $employee->id)->pluck('section_id'))->first();
        if ($other) {
            $this->actingAs($teacher)->get(route('teacher.attendance.index', ['class_id' => $other->class_id, 'section_id' => $other->id]))->assertForbidden();
            $this->actingAs($teacher)->post(route('teacher.attendance.store'), ['class_id' => $other->class_id, 'section_id' => $other->id, 'date' => today()->toDateString(), 'status' => [1 => 'Present']])->assertForbidden();
        }

        $notMine = DB::table('homework')->where('employee_id', '!=', $employee->id)->first();
        $this->actingAs($teacher)->delete(route('teacher.resource.destroy', ['resource' => 'homework', 'id' => $notMine->id]))->assertForbidden();
        $this->assertDatabaseHas('homework', ['id' => $notMine->id]);

        $this->actingAs($teacher)->get(route('teacher.resource.index', ['resource' => 'notices']))->assertNotFound();
    }

    public function test_teacher_homework_is_owned_by_them(): void
    {
        $link = DB::table('teacher_subjects')->first();
        $employee = Employee::find($link->employee_id);

        $this->actingAs($employee->user)->post(route('teacher.resource.store', ['resource' => 'homework']), [
            'class_id' => $link->class_id, 'section_id' => $link->section_id, 'subject_id' => $link->subject_id,
            'title' => 'New worksheet', 'assigned_date' => today()->toDateString(), 'due_date' => today()->addDay()->toDateString(),
        ])->assertSessionHasNoErrors();

        $this->assertDatabaseHas('homework', ['title' => 'New worksheet', 'employee_id' => $employee->id]);
    }

    public function test_result_lookup_needs_matching_date_of_birth(): void
    {
        $student = Student::first();
        $exam = DB::table('exams')->where('is_published', true)->first();

        $this->post('/result', ['exam_id' => $exam->id, 'student_no' => $student->student_no, 'dob' => '2001-01-01'])->assertOk()->assertSee('No result found');
        $this->post('/result', ['exam_id' => $exam->id, 'student_no' => $student->student_no, 'dob' => $student->dob->format('Y-m-d')])->assertOk()->assertSee($student->name)->assertSee('GPA');
    }

    public function test_unpublished_results_stay_hidden_from_students(): void
    {
        $exam = DB::table('exams')->where('is_published', true)->first();
        DB::table('exams')->update(['is_published' => false]);

        $this->actingAs($this->user('student'))->get(route('portal.results'))->assertOk()->assertDontSee($exam->name)->assertSee('No published results');
    }

    public function test_payment_cannot_exceed_due_and_updates_status(): void
    {
        $invoice = DB::table('invoices')->where('status', 'Unpaid')->first();
        $admin = $this->user('admin');
        $base = ['method' => 'Cash', 'paid_on' => today()->toDateString()];

        $this->actingAs($admin)->post(route('admin.fees.collect', $invoice->id), $base + ['amount' => $invoice->amount + 1])->assertSessionHasErrors('amount');
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'Unpaid']);

        $this->actingAs($admin)->post(route('admin.fees.collect', $invoice->id), $base + ['amount' => $invoice->amount])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('invoices', ['id' => $invoice->id, 'status' => 'Paid']);
    }

    public function test_admission_approval_creates_a_student_who_can_sign_in(): void
    {
        $application = DB::table('admissions')->where('status', 'Pending')->first();
        $section = DB::table('sections')->where('class_id', $application->class_id)->first();

        $response = $this->actingAs($this->user('admin'))->post(route('admin.admissions.approve', $application->id), ['section_id' => $section->id]);
        $response->assertSessionHas('credentials');
        $credentials = session('credentials');

        $this->assertDatabaseHas('admissions', ['id' => $application->id, 'status' => 'Approved']);
        $this->post('/logout');
        $this->post('/login', ['login' => $credentials['login'], 'password' => $credentials['password'], 'portal' => 'student'])->assertRedirect(route('portal.dashboard'));
    }

    public function test_timetable_blocks_double_booked_teacher(): void
    {
        $period = DB::table('routines')->first();
        $other = DB::table('sections')->where('id', '!=', $period->section_id)->first();
        $subject = DB::table('subjects')->where('class_id', $other->class_id)->first();

        $this->actingAs($this->user('admin'))->post(route('admin.resource.store', ['resource' => 'routines']), [
            'class_id' => $other->class_id, 'section_id' => $other->id, 'subject_id' => $subject->id, 'day' => $period->day,
            'employee_id' => $period->employee_id, 'start_time' => $period->start_time, 'end_time' => $period->end_time,
        ])->assertSessionHasErrors();
    }
}
