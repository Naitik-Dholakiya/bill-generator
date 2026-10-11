<?php

namespace App\Http\Controllers;

use App\Models\usermaster;
use DB;
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
        try {
            $userId = $request->cookie('GTA');
            $user = usermaster::where('user_id', $userId)->firstOrFail();

            $validated = $request->validate([
                'full_name' => ['required', 'string', 'max:100'],
                'email' => ['required', 'email', 'max:100', Rule::unique('usermaster', 'email')->ignore($user->user_id, 'user_id')],
                'phone' => ['nullable', 'string', 'max:15'],
                'company_name' => ['nullable', 'string', 'max:150'],
                'company_address' => ['nullable', 'string'],
                'company_gstin' => ['nullable', 'string', 'max:15'],
            ]);
            DB::beginTransaction();

            DB::table('usermaster')
                ->where('user_id', $userId)
                ->update([
                    'full_name' => $validated['full_name'],
                    'email' => $validated['email'],
                    'phone' => $validated['phone'] ?? null,
                    'company_name' => $validated['company_name'] ?? null,
                    'company_address' => $validated['company_address'] ?? null,
                    'company_gstin' => $validated['company_gstin'] ?? null,
                ]);
            DB::commit();
            Cookie::queue('CSGO', $user->full_name, 60 * 24 * 30);

            return redirect()
                ->route('settings')
                ->with('success', 'Settings updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }

    public function changePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'tb_password' => 'required|min:6|confirmed',

            ], [], [
                'tb_password' => 'Password',
            ]);

            DB::beginTransaction();
            $userId = request()->cookie('GTA');
            $user = usermaster::where('user_id', $userId)->firstOrFail();

            // Update the password
            $user->password = securePassword($validated['tb_password'], $user->email);
            DB::commit();

            return redirect()->back()->with('success', 'Password Changed Successfully');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()
                ->with('error', 'Something went wrong. Please try again.')
                ->withInput();
        }
    }
}
