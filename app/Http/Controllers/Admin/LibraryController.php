<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class LibraryController extends Controller
{
    public function index(): View
    {
        $issued = DB::table('book_issues')->whereNull('return_date')->select('book_id', DB::raw('count(*) as total'))->groupBy('book_id')->pluck('total', 'book_id');

        return view('admin.library', [
            'issues' => DB::table('book_issues as i')->join('books as b', 'i.book_id', '=', 'b.id')->join('students as s', 'i.student_id', '=', 's.id')
                ->select('i.*', 'b.title', 's.name as student_name', 's.student_no')->orderByRaw('i.return_date is not null')->orderBy('i.due_date')->paginate(20),
            'books' => DB::table('books')->orderBy('title')->get()->filter(fn ($b) => $b->copies > ($issued[$b->id] ?? 0))->values(),
            'students' => DB::table('students')->where('status', 'Active')->orderBy('name')->get(['id', 'name', 'student_no']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'book_id' => ['required', 'exists:books,id'], 'student_id' => ['required', 'exists:students,id'],
            'issue_date' => ['required', 'date', 'before_or_equal:today'], 'due_date' => ['required', 'date', 'after_or_equal:issue_date'],
        ]);

        DB::transaction(function () use ($data) {
            $copies = DB::table('books')->where('id', $data['book_id'])->value('copies');
            if (DB::table('book_issues')->where('book_id', $data['book_id'])->whereNull('return_date')->count() >= $copies) {
                throw ValidationException::withMessages(['book_id' => 'All copies of this book are currently issued.']);
            }
            DB::table('book_issues')->insert($data);
        });

        return back()->with('ok', 'Book issued.');
    }

    public function returnBook(int $id): RedirectResponse
    {
        abort_unless(DB::table('book_issues')->where('id', $id)->whereNull('return_date')->update(['return_date' => today()->toDateString()]), 404);

        return back()->with('ok', 'Book returned.');
    }
}
