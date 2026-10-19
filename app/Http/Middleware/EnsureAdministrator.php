<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdministrator
{
    public function handle(Request $request, Closure $next): Response
    {
        $adminId = $request->cookie('GTA');
        $admin = $adminId
            ? DB::table('usermaster')
                ->where('user_id', $adminId)
                ->where('is_admin', 'Y')
                ->where('status', '1')
                ->first(['user_id', 'full_name'])
            : null;

        if ($admin === null) {
            return redirect()->guest(route('login.form'));
        }

        $request->attributes->set('authenticated_admin', $admin);

        return $next($request);
    }
}
