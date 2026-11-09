<?php

namespace App\Http\Controllers;

use App\Services\ResultService;
use App\Support\Lookups;
use App\Support\ResourceRegistry;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home', [
            'notices' => $this->publicNotices()->limit(6)->get(),
            'messages' => DB::table('web_messages')->orderBy('id')->get(),
            'links' => DB::table('links')->orderBy('title')->get(),
            'achievers' => DB::table('achievers')->orderByDesc('year')->get(),
            'teachers' => $this->teacherQuery()->limit(10)->get(),
            'events' => DB::table('events')->where('date', '>=', today()->toDateString())->orderBy('date')->limit(3)->get(),
            'photos' => DB::table('gallery')->orderBy('id')->limit(8)->get(),
            'videos' => DB::table('videos')->orderBy('id')->limit(4)->get(),
            'about' => DB::table('web_pages')->where('slug', 'history')->first(),
            'stats' => [
                'students' => DB::table('students')->where('status', 'Active')->count(),
                'teachers' => DB::table('employees')->where(['kind' => 'Teacher', 'status' => 'Active'])->count(),
                'classes' => DB::table('classes')->count(),
                'years' => now()->year - (int) setting('established', '2000'),
            ],
        ]);
    }

    public function notices(): View
    {
        return view('public.notices', ['notices' => $this->publicNotices()->paginate(12)]);
    }

    public function notice(int $id): View
    {
        $notice = DB::table('notices')->where(['id' => $id, 'audience' => 'Everyone'])->first();
        abort_unless($notice, 404);

        return view('public.notice', compact('notice'));
    }

    public function teachers(): View
    {
        return view('public.people', ['title' => 'Our Teachers', 'people' => $this->teacherQuery()->get()]);
    }

    public function officeStaff(): View
    {
        return view('public.people', ['title' => 'Office Staff', 'people' => DB::table('employees')->where(['kind' => 'Staff', 'status' => 'Active', 'show_public' => true])->orderBy('name')->get()]);
    }

    public function people(string $group): View
    {
        $groups = ResourceRegistry::peopleGroups();
        abort_unless(isset($groups[$group]), 404);

        return view('public.people', ['title' => $groups[$group], 'people' => DB::table('web_people')->where('group_name', $group)->orderBy('position')->get()]);
    }

    public function page(string $slug): View
    {
        $page = DB::table('web_pages')->where('slug', $slug)->first();
        abort_unless($page, 404);

        return view('public.page', ['title' => $page->title, 'body' => $page->body, 'image' => $page->image, 'related' => DB::table('web_pages')->where('menu_group', $page->menu_group)->orderBy('title')->get()]);
    }

    public function message(string $slug): View
    {
        $message = DB::table('web_messages')->where('slug', $slug)->first();
        abort_unless($message, 404);

        return view('public.message', compact('message'));
    }

    public function events(): View
    {
        return view('public.events', ['events' => DB::table('events')->orderByDesc('date')->get()]);
    }

    public function routine(Request $request): View
    {
        $periods = collect();
        if ($request->filled(['class_id', 'section_id'])) {
            $periods = TimetableController::sectionPeriods((int) $request->query('section_id'));
        }

        return view('public.routine', ['classes' => Lookups::get('classes'), 'sections' => Lookups::get('sections'), 'periods' => $periods]);
    }

    public function holidays(): View
    {
        return view('public.simple-table', ['title' => 'Holiday Calendar', 'headers' => ['Holiday', 'From', 'To'], 'rows' => DB::table('holidays')->orderBy('from_date')->get()->map(fn ($h) => [$h->title, nice_date($h->from_date), nice_date($h->to_date)])]);
    }

    public function transport(): View
    {
        return view('public.simple-table', ['title' => 'School Transport', 'headers' => ['Route', 'Vehicle', 'Monthly fare'], 'rows' => DB::table('transport_routes')->orderBy('name')->get()->map(fn ($r) => [$r->name, $r->vehicle, money($r->fare)])]);
    }

    public function scholars(): View
    {
        return view('public.simple-table', ['title' => 'Top Achievers', 'headers' => ['Name', 'Exam', 'GPA', 'Year'], 'rows' => DB::table('achievers')->orderByDesc('year')->get()->map(fn ($a) => [$a->name, $a->exam, $a->gpa, $a->year])]);
    }

    public function photos(): View
    {
        return view('public.photos', ['photos' => DB::table('gallery')->orderBy('id')->get()]);
    }

    public function videos(): View
    {
        return view('public.videos', ['videos' => DB::table('videos')->orderBy('id')->get()]);
    }

    public function credits(): View
    {
        return view('public.credits', ['media' => json_decode(file_get_contents(resource_path('data/media.json')), true)]);
    }

    public function result(): View
    {
        return view('public.result', ['exams' => $this->publishedExams(), 'result' => null, 'student' => null, 'searched' => false]);
    }

    public function resultLookup(Request $request): View
    {
        $data = $request->validate(['exam_id' => ['required', 'integer'], 'student_no' => ['required', 'string', 'max:20'], 'dob' => ['required', 'date']]);

        $exam = DB::table('exams')->where(['id' => $data['exam_id'], 'is_published' => true])->first();
        $student = $exam ? DB::table('students')->where('student_no', $data['student_no'])->whereDate('dob', $data['dob'])->first() : null;

        return view('public.result', [
            'exams' => $this->publishedExams(),
            'student' => $student,
            'exam' => $exam,
            'result' => $student ? ResultService::forStudent($exam->id, $student) : null,
            'searched' => true,
        ]);
    }

    public function apply(): View
    {
        return view('public.apply', ['classes' => Lookups::get('classes')]);
    }

    public function applyStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'gender' => ['required', 'in:Male,Female,Other'],
            'dob' => ['required', 'date', 'before:today'],
            'class_id' => ['required', 'exists:classes,id'],
            'guardian_name' => ['required', 'string', 'max:120'],
            'phone' => ['required', 'regex:/^[0-9+\-\s]{8,20}$/'],
            'email' => ['nullable', 'email', 'max:120'],
            'address' => ['nullable', 'string', 'max:255'],
            'previous_school' => ['nullable', 'string', 'max:160'],
        ]);

        do {
            $reference = 'ABC-'.now()->format('y').'-'.strtoupper(Str::random(6));
        } while (DB::table('admissions')->where('reference', $reference)->exists());

        DB::table('admissions')->insert($data + ['reference' => $reference, 'status' => 'Pending', 'created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('apply')->with('reference', $reference);
    }

    public function contact(): View
    {
        return view('public.contact');
    }

    public function contactStore(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['nullable', 'email', 'max:120'],
            'phone' => ['nullable', 'string', 'max:30'],
            'subject' => ['nullable', 'string', 'max:160'],
            'message' => ['required', 'string', 'max:3000'],
        ]);

        DB::table('contact_messages')->insert($data + ['created_at' => now(), 'updated_at' => now()]);

        return redirect()->route('contact')->with('sent', true);
    }

    private function publicNotices()
    {
        return DB::table('notices')->where('audience', 'Everyone')->orderByDesc('pinned')->orderByDesc('date');
    }

    private function teacherQuery()
    {
        return DB::table('employees')->where(['kind' => 'Teacher', 'status' => 'Active', 'show_public' => true])->orderBy('id');
    }

    private function publishedExams()
    {
        return DB::table('exams')->where('is_published', true)->orderByDesc('start_date')->get();
    }
}
