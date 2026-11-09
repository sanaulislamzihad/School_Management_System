<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\ResultService;
use App\Support\Lookups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ResultController extends Controller
{
    public function index(Request $request): View
    {
        $exam = $request->filled('exam_id') ? DB::table('exams')->find($request->integer('exam_id')) : null;
        $rows = $exam && $request->filled('class_id')
            ? ResultService::sheet($exam->id, $request->integer('class_id'), $request->filled('section_id') ? $request->integer('section_id') : null)
            : [];

        return view('admin.results', ['exams' => Lookups::get('exams'), 'classes' => Lookups::get('classes'), 'sections' => Lookups::get('sections'), 'exam' => $exam, 'rows' => $rows]);
    }

    public function publish(int $exam): RedirectResponse
    {
        $row = DB::table('exams')->find($exam);
        abort_unless($row, 404);
        DB::table('exams')->where('id', $exam)->update(['is_published' => ! $row->is_published]);

        return back()->with('ok', $row->is_published ? 'Result hidden from students.' : 'Result published for students.');
    }
}
