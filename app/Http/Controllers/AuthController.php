<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Hash;

class AuthController extends Controller
{
    public function showRegister()
    {
        return view('auth.register');
    }

    public function showLogin()
    {
        return view('auth.login');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:6|confirmed'
        ]);

        User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password)
        ]);

        return redirect('/login')
            ->with('success', 'Registration Successful');
    }

    public function login(Request $request)
    {
        $user = User::where('email', $request->email)->first();

        if ($user && Hash::check($request->password, $user->password))
        {
            session([
                'user_id' => $user->id,
                'user_name' => $user->name
            ]);

            return redirect('/dashboard');
        }

        return back()->with('error', 'Invalid Credentials');
    }

    public function dashboard()
    {
        if (!session()->has('user_id'))
        {
            return redirect('/login');
        }

        return view('dashboard');
    }

    public function logout()
    {
        session()->flush();

        return redirect('/login');
    }
}