<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\Lookups;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PromotionController extends Controller
{
    public function index(Request $request): View
    {
        $students = collect();
        if ($request->filled(['session_id', 'class_id', 'section_id'])) {
            $students = DB::table('students')->where(['session_id' => $request->integer('session_id'), 'class_id' => $request->integer('class_id'), 'section_id' => $request->integer('section_id'), 'status' => 'Active'])->orderBy('roll')->get();
        }

        return view('admin.promotions', ['sessions' => Lookups::get('sessions'), 'classes' => Lookups::get('classes'), 'sections' => Lookups::get('sections'), 'students' => $students]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'to_session_id' => ['required', 'exists:academic_sessions,id'],
            'to_class_id' => ['required', 'exists:classes,id'],
            'to_section_id' => ['required', Rule::exists('sections', 'id')->where('class_id', $request->integer('to_class_id'))],
            'student_ids' => ['required', 'array', 'min:1'],
            'student_ids.*' => ['integer', 'exists:students,id'],
        ], ['student_ids.required' => 'Select at least one student.'], ['to_section_id' => 'section']);

        DB::transaction(function () use ($data) {
            foreach (DB::table('students')->whereIn('id', $data['student_ids'])->get() as $student) {
                DB::table('promotions')->insert([
                    'student_id' => $student->id, 'from_class_id' => $student->class_id, 'to_class_id' => $data['to_class_id'],
                    'from_session_id' => $student->session_id, 'to_session_id' => $data['to_session_id'], 'created_at' => now(), 'updated_at' => now(),
                ]);
                DB::table('students')->where('id', $student->id)->update(['session_id' => $data['to_session_id'], 'class_id' => $data['to_class_id'], 'section_id' => $data['to_section_id'], 'updated_at' => now()]);
            }
        });

        return back()->with('ok', count($data['student_ids']).' students promoted.');
    }
}
