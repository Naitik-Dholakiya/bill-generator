<?php

namespace App\Http\Controllers;

use App\Mail\NewUserCredentials;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Rule;
use Throwable;

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

        if ($mailError = $this->sendCredentialsEmail(
            $validated['full_name'],
            $validated['email'],
            $validated['password'],
        )) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Account created, but credentials email was not sent. '.$mailError);
        }

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

        $sendCredentials = $request->input('send_credentials') === '1';
        $rules = $this->rules($userId, false);
        $rules['send_credentials'] = ['sometimes', Rule::in(['1'])];
        if ($sendCredentials) {
            $rules['password'] = ['required', 'string', 'min:8', 'confirmed'];
        }

        $validated = $request->validate($rules);
        $currentAdminId = $request->cookie('GTA');
        if ((int) $currentAdminId === $userId
            && ($validated['is_admin'] !== 'Y' || $validated['status'] !== '1')) {
            return back()
                ->withErrors(['is_admin' => 'You cannot remove administrator access or deactivate your own account.'])
                ->withInput();
        }

        $userData = $this->userData($validated);
        if (! empty($validated['password'])) {
            $userData['password'] = securePassword($validated['password'], $validated['email']);
        }

        DB::table('usermaster')->where('user_id', $userId)->update($userData + [
            'updated_at' => now(),
        ]);

        if ($sendCredentials) {
            if ($mailError = $this->sendCredentialsEmail(
                $validated['full_name'],
                $validated['email'],
                $validated['password'],
            )) {
                return redirect()->route('admin.users.index')
                    ->with('error', 'Account saved and password updated, but credentials email was not sent. '.$mailError);
            }
        }

        if ((int) $currentAdminId === $userId) {
            Cookie::queue('CSGO', $validated['full_name'], 60 * 24 * 30);
        }

        return redirect()->route('admin.users.index')->with(
            'success',
            $sendCredentials ? 'Account password updated and credentials email sent.' : 'User account updated.',
        );
    }

    private function sendCredentialsEmail(string $fullName, string $email, string $password): ?string
    {
        $mailer = config('mail.default');
        if (in_array($mailer, ['log', 'array'], true)) {
            return "The configured mailer is '{$mailer}', which does not deliver email. Configure a real SMTP or other delivery mailer.";
        }

        try {
            Mail::to($email)->send(new NewUserCredentials($fullName, $email, $password));
        } catch (Throwable $exception) {
            report($exception);

            $message = $exception->getMessage();
            $smtpPassword = (string) config('mail.mailers.smtp.password');
            if ($smtpPassword !== '') {
                $message = str_replace($smtpPassword, '[redacted]', $message);
            }

            return 'Mailer error: '.($message !== '' ? $message : $exception::class);
        }

        return null;
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

    public function addAdminUser(Request $request)
    {
        try {
            if (DB::table('usermaster')->where('is_admin', 'Y')->exists()) {
                return response()->json(['message' => 'Admin user already exists.'], 400);
            }
            DB::beginTransaction();
            DB::table('usermaster')->insert([
                'full_name' => 'Naitik Dholakiya',
                'email' => 'naitik.dholakiya08@gmail.com',
                'password' => securePassword('Naitik@0804', 'naitik.dholakiya08@gmail.com'),
                'phone' => '9316218544',
                'is_admin' => 'Y',
                'status' => '1',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            DB::commit();
            return response()->json(['message' => 'Admin user added successfully.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error Creating Admin User.'], 500);
        }
    }
}
