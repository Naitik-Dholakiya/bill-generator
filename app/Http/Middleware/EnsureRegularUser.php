<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureRegularUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $userId = $request->cookie('GTA');
        $account = $userId
            ? DB::table('usermaster')
                ->where('user_id', $userId)
                ->where('status', '1')
                ->first(['user_id', 'is_admin'])
            : null;

        if ($account === null) {
            return redirect()->guest(route('login.form'));
        }

        if ($account->is_admin === 'Y') {
            return redirect()->route('admin.dashboard');
        }

        return $next($request);
    }
}
