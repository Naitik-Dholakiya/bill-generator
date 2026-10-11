<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function index(Request $request)
    {
        $query = DB::table('usermaster');
        if ($search = trim((string) $request->query('q'))) {
            $query->where(function ($users) use ($search) {
                $users->where('full_name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('company_name', 'like', "%{$search}%");
            });
        }

        $users = $query
            ->orderByDesc('created_at')
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create', [
            'templates' => config('invoice.templates'),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        DB::table('usermaster')->insert($this->userData($validated) + [
            'password' => securePassword($validated['password'], $validated['email']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return redirect()->route('admin.users.index')->with('success', 'User account created.');
    }

    public function edit(int $userId)
    {
        $user = DB::table('usermaster')->where('user_id', $userId)->first();
        abort_if($user === null, 404, 'User not found.');

        return view('admin.users.edit', [
            'user' => $user,
            'templates' => config('invoice.templates'),
        ]);
    }

    public function update(Request $request, int $userId)
    {
        $user = DB::table('usermaster')->where('user_id', $userId)->first();
        abort_if($user === null, 404, 'User not found.');

        $validated = $request->validate($this->rules($userId, false));
        $currentAdminId = $request->cookie('GTA');
        if ((int) $currentAdminId === $userId
            && ($validated['is_admin'] !== 'Y' || $validated['status'] !== '1')) {
            return back()
                ->withErrors(['is_admin' => 'You cannot remove administrator access or deactivate your own account.'])
                ->withInput();
        }

        $userData = $this->userData($validated);
        if (!empty($validated['password'])) {
            $userData['password'] = securePassword($validated['password'], $validated['email']);
        }

        DB::table('usermaster')->where('user_id', $userId)->update($userData + [
            'updated_at' => now(),
        ]);

        if ((int) $currentAdminId === $userId) {
            Cookie::queue('CSGO', $validated['full_name'], 60 * 24 * 30);
        }

        return redirect()->route('admin.users.index')->with('success', 'User account updated.');
    }

    private function rules(?int $userId = null, bool $creating = true): array
    {
        $emailRule = Rule::unique('usermaster', 'email');
        if ($userId !== null) {
            $emailRule->ignore($userId, 'user_id');
        }

        return [
            'full_name' => ['required', 'string', 'max:100'],
            'email' => [
                'required',
                'email',
                'max:100',
                $emailRule,
            ],
            'phone' => ['nullable', 'string', 'max:15'],
            'company_name' => ['nullable', 'string', 'max:150'],
            'company_address' => ['nullable', 'string'],
            'company_gstin' => ['nullable', 'string', 'max:15'],
            'password' => [$creating ? 'required' : 'nullable', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['required', Rule::in(['N', 'Y'])],
            'status' => ['required', Rule::in(['0', '1', '2'])],
            'is_jewelry' => ['required', 'boolean'],
            'invoice_template' => ['required', 'string', Rule::in(array_keys(config('invoice.templates')))],
        ];
    }

    private function userData(array $validated): array
    {
        return [
            'full_name' => $validated['full_name'],
            'email' => $validated['email'],
            'phone' => $validated['phone'] ?? null,
            'company_name' => $validated['company_name'] ?? null,
            'company_address' => $validated['company_address'] ?? null,
            'company_gstin' => $validated['company_gstin'] ?? null,
            'is_admin' => $validated['is_admin'],
            'status' => $validated['status'],
            'is_jewelry' => (bool) $validated['is_jewelry'],
            'invoice_template' => $validated['invoice_template'],
        ];
    }
}
