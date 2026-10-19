<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Load The Views
    public function showLogin()
    {
        $userId = Cookie::get('GTA');
        $user = $userId
            ? DB::table('usermaster')->where('user_id', $userId)->where('status', '1')->first(['is_admin'])
            : null;

        if ($user !== null) {
            return redirect()->route($user->is_admin === 'Y' ? 'admin.dashboard' : 'dashboard');
        }

        return view('auth.login');
    }

    public function dashboard()
    {
        if (Cookie::get('GTA') == null) {
            return redirect('/');
        }

        return view('dashboard.dashboard');
    }

    public function logout()
    {
        session()->invalidate();
        session()->regenerateToken();
        Cookie::queue(Cookie::forget('GTA'));
        Cookie::queue(Cookie::forget('CSGO'));

        return redirect('/')
            ->with('success', 'Logged out successfully.');
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'tb_email' => ['required', 'email', 'max:150'],
                'tb_password' => ['required', 'string'],
                'remember' => ['sometimes', 'boolean'],
            ], [], [
                'tb_email' => 'Email',
                'tb_password' => 'Password',
            ]);

            $user = DB::table('usermaster')
                ->select(['user_id', 'full_name', 'email', 'password', 'phone', 'is_admin', 'status'])
                ->where('email', $validated['tb_email'])
                ->where('status', '1')
                ->first();

            if (! $user) {

                return back()
                    ->withErrors([
                        'tb_email' => 'Invalid email or password.',
                    ])
                    ->withInput();
            }

            $hashedPassword = securePassword(
                $validated['tb_password'],
                $validated['tb_email']
            );

            if ($hashedPassword !== $user->password) {

                return back()
                    ->withErrors([
                        'tb_password' => 'Invalid email or password.',
                    ])
                    ->withInput();
            }

            $request->session()->regenerate();

            $cookieLifetime = $request->boolean('remember') ? 60 * 24 * 30 : 0;
            Cookie::queue('GTA', $user->user_id, $cookieLifetime);
            Cookie::queue('CSGO', $user->full_name, $cookieLifetime);

            return redirect()->intended(route($user->is_admin === 'Y' ? 'admin.dashboard' : 'dashboard'))
                ->with('success', 'Login successful.');

        } catch (\Exception $e) {

            return back()
                ->withErrors([
                    'error' => 'Something went wrong. Please try again later.',
                ]);
        }
    }

}
