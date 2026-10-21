<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class EnsureResourceOwnership
{
    public function handle(
        Request $request,
        Closure $next,
        string $table,
        string $primaryKey,
        string $routeParameter = 'id'
    ): Response {
        $userId = $request->attributes->get('authenticated_user_id');
        $resourceId = $request->route($routeParameter);

        abort_unless($userId !== null && is_scalar($resourceId) && ctype_digit((string) $resourceId), 404);

        $ownedResourceExists = DB::table($table)
            ->where($primaryKey, $resourceId)
            ->where('created_by', $userId)
            ->exists();

        abort_unless($ownedResourceExists, 404);

        return $next($request);
    }
}
