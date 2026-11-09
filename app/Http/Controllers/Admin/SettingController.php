<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class SettingController extends Controller
{
    private const FIELDS = [
        'school_name' => ['School name', 'required'], 'tagline' => ['Tagline', 'nullable'], 'address' => ['Address', 'required'],
        'phone' => ['Phone', 'required'], 'email' => ['Email', 'required'], 'eiin' => ['EIIN', 'nullable'], 'school_code' => ['School code', 'nullable'],
        'established' => ['Year established', 'required'], 'ticker' => ['Home page notice ticker', 'nullable'],
    ];

    public function edit(): View
    {
        return view('admin.settings', ['fields' => self::FIELDS, 'values' => DB::table('settings')->pluck('value', 'key')]);
    }

    public function update(Request $request): RedirectResponse
    {
        $rules = collect(self::FIELDS)->map(fn ($f) => [$f[1], 'string', 'max:500'])->all();
        $rules['email'][] = 'email';
        $rules['established'] = ['required', 'digits:4'];
        $data = $request->validate($rules, [], collect(self::FIELDS)->map(fn ($f) => $f[0])->all());

        foreach ($data as $key => $value) {
            DB::table('settings')->updateOrInsert(['key' => $key], ['value' => $value]);
        }

        return back()->with('ok', 'Settings saved.');
    }
}
