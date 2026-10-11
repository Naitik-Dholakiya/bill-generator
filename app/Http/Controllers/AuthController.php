<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;

class AuthController extends Controller
{
    // Load The Views
    public function showRegister()
    {
        return view('auth.register');
    }

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

    public function register(Request $request)
    {
        $request->validate([
            'tb_name' => 'required',
            'tb_email' => 'required|email|unique:usermaster,email',
            'tb_password' => 'required|min:6|confirmed',
        ], [], [
            'tb_name' => 'Name',
            'tb_email' => 'Email',
            'tb_password' => 'Password',
        ]
        );

        $email = $request->input('tb_email');
        $password = $request->input('tb_password');
        $securePassword = securePassword($password, $email);

        DB::table('usermaster')->insert([
            'full_name' => $request->input('tb_name'),
            'email' => $email,
            'password' => $securePassword,
            'is_admin' => 'N',
            'status' => '1',
            'created_at' => now(),
        ]);

        return redirect('/')
            ->with('success', 'Registration Successful');
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
        session()->flush();
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

            Cookie::queue('GTA', $user->user_id, 60 * 24 * 30);
            Cookie::queue('CSGO', $user->full_name, 60 * 24 * 30);

            return redirect()->route($user->is_admin === 'Y' ? 'admin.dashboard' : 'dashboard')
                ->with('success', 'Login successful.');

        } catch (\Exception $e) {

            return back()
                ->withErrors([
                    'error' => 'Something went wrong. Please try again later.',
                ]);
        }
    }

}
