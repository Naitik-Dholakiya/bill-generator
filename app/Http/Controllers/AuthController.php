<?php

namespace App\Http\Controllers;

// Import necessary classes
use App\Exception\CustomException;
use App\Models\usermaster;
use Illuminate\Http\Request;
// Import Cookie
use Illuminate\Support\Facades\Cookie;
// Import Models
use Illuminate\Support\Facades\DB;

// Import Custom Exception
// Import Custom Helper

class AuthController extends Controller
{
    // Load The Views
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

        return view('auth.dashboard');
    }

    public function logout()
    {
        session()->flush();

        return redirect('/')
            ->with('success', 'Logged out successfully.');
    }

    public function login(Request $request)
    {
        try {
            $validated = $request->validate([
                'tb_email' => ['required', 'email', 'max:150'],
                'tb_password' => ['required', 'max:20'],
            ], [], [
                'tb_email' => 'Email',
                'tb_password' => 'Password',
            ]);

            $user = DB::table('usermaster as um')
                ->select([
                    'um.user_id',
                    'um.full_name',
                    'um.email',
                    'um.password',
                    'um.phone',
                    'um.is_admin',
                    'um.status',
                ])
                ->where('um.email', $validated['tb_email'])
                ->where('um.is_admin', 'N')
                ->where('um.status', '1')
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

            return redirect('/dashboard')
                ->with('success', 'Login successful.');

            // } catch (CustomException $e) {

            //     return back()
            //         ->withErrors([
            //             'error' => $e->getMessage(),
            //         ])
            //         ->withInput();
            // }catch (\Exception $e) {
            //     dd($e->getMessage());
        } catch (\Exception $e) {

            return back()
                ->withErrors([
                    'error' => 'Something went wrong. Please try again later.',
                ]);
        }
    }

    public function addDummyUser()
    {
        $user = new usermaster;
        $user->full_name = 'Naitik Dholakiya';
        $user->email = 'Naitik@gmail.com';
        $email = 'Naitik@gmail.com';
        $password = 'Naitik@123';

        $securePassword = securePassword($password, $email);
        $user->password = $securePassword;
        $user->is_admin = 'N';
        $user->status = '1';
        $user->save();

        //         dd(
        //     securePassword('Naitik@123', 'Naitik@gmail.com')
        // );
        return response()->json(['message' => 'Dummy user added successfully', 'password' => $user->password]);
    }

    public function check()
    {
        $email = 'Naitik@gmail.com';
        $password = 'Naitik@123';

        $securePassword = securePassword($password, $email);

        $userPassword = DB::table('usermaster')->where('email', '=', $email)->first()->password;

        exit(json_encode(['message' => $userPassword, 'securePassword' => $securePassword]));
    }
}
