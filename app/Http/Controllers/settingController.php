<?php

namespace App\Http\Controllers;

use App\Models\usermaster;
use Illuminate\Http\Request;

class settingController extends Controller
{
    public function index()
    {
        $userId = request()->cookie('GTA');
        $setting = usermaster::where('user_id', $userId)->firstOrFail();

        return view('settings.index', compact('setting'));
    }

    public function changePassword(Request $request)
    {
        try {
            $validated = $request->validate([
                'tb_password' => 'required|min:6|confirmed',

            ], [], [
                'tb_password' => 'Password',
            ]);

            $userId = request()->cookie('GTA');
            $user = usermaster::where('user_id', $userId)->firstOrFail();

            // Update the password
            $user->password = securePassword($validated['tb_password'], $user->email);
            $user->save();
            return redirect()->back()->with('success','Password Changed Successfully');
        } catch (\Exception $e) {
            return back()
                ->withErrors([
                    'error' =>
                    // => $e->getMessage() ?: 'Something went wrong. Please try again later.',
                    'Something went wrong. Please try again later.',
                ]);
        }
    }
}
