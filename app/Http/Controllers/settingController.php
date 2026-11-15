<?php

namespace App\Http\Controllers;

use App\Models\usermaster;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Validation\Rule;

class settingController extends Controller
{
    public function index()
    {
        $userId = request()->cookie('GTA');
        $setting = usermaster::where('user_id', $userId)->firstOrFail();

        return view('settings.index', compact('setting'));
    }

    public function update(Request $request)
    {
        $userId = (int) $request->cookie('GTA');
        $user = usermaster::where('user_id', $userId)->firstOrFail();

        $validated = $request->validate([
            'full_name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:100', Rule::unique('usermaster', 'email')->ignore($user->user_id, 'user_id')],
            'phone' => ['nullable', 'string', 'max:15'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_address' => ['nullable', 'string', 'max:2000'],
            'company_gstin' => ['nullable', 'string', 'max:15', 'regex:/^[0-9A-Z]{15}$/i'],
            'stock_negative_policy' => ['required', Rule::in(['warn_allow', 'block', 'allow'])],
        ]);

        DB::table('usermaster')
            ->where('user_id', $userId)
            ->update([
                'full_name' => $validated['full_name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'] ?? null,
                'company_name' => $validated['company_name'] ?? null,
                'company_address' => $validated['company_address'] ?? null,
                'company_gstin' => isset($validated['company_gstin']) ? strtoupper($validated['company_gstin']) : null,
                'stock_negative_policy' => $validated['stock_negative_policy'],
                'updated_at' => now(),
            ]);
        Cookie::queue('CSGO', $validated['full_name'], 60 * 24 * 30);

        return redirect()
            ->route('settings')
            ->with('success', 'Settings updated successfully.');
    }

    public function changePassword(Request $request)
    {
        $validated = $request->validate([
            'tb_password' => ['required', 'string', 'min:8', 'confirmed'],
        ], [], [
            'tb_password' => 'Password',
        ]);

        $userId = (int) $request->cookie('GTA');
        $user = usermaster::where('user_id', $userId)->firstOrFail();
        $user->password = securePassword($validated['tb_password'], $user->email);
        $user->save();

        return redirect()->back()->with('success', 'Password changed successfully.');
    }
}
