<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class jewelryUserController extends Controller
{
    public function index(Request $request)
    {
        $this->authorizeAdministrator($request->cookie('GTA'));

        $users = DB::table('usermaster')
            ->select('user_id', 'full_name', 'email', 'company_name', 'is_jewelry')
            ->orderBy('full_name')
            ->paginate(20);

        return view('admin.jewelry-users.index', compact('users'));
    }

    public function update(Request $request, int $userId)
    {
        $this->authorizeAdministrator($request->cookie('GTA'));

        $validated = $request->validate([
            'is_jewelry' => ['required', 'boolean'],
        ]);

        $updated = DB::table('usermaster')
            ->where('user_id', $userId)
            ->update([
                'is_jewelry' => (bool) $validated['is_jewelry'],
                'updated_at' => now(),
            ]);

        abort_if($updated === 0 && !DB::table('usermaster')->where('user_id', $userId)->exists(), 404, 'User not found.');

        return redirect()
            ->route('admin.jewelry-users.index')
            ->with('success', 'Jewelry invoice access updated.');
    }

    private function authorizeAdministrator(?string $userId): void
    {
        abort_unless(
            $userId !== null && DB::table('usermaster')
                ->where('user_id', $userId)
                ->where('is_admin', 'Y')
                ->exists(),
            403,
            'Administrator access required.'
        );
    }
}
