@php
    $user = $user ?? null;
    $field = 'block text-sm font-semibold text-slate-700 dark:text-zinc-200';
    $input = 'mt-1.5 min-h-11 w-full rounded-xl border border-slate-200 bg-white px-3.5 text-sm text-slate-900 outline-none transition focus:border-violet-400 focus:ring-4 focus:ring-violet-500/10 dark:border-zinc-700 dark:bg-zinc-950 dark:text-zinc-100';
@endphp

<div class="space-y-6">
    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
        <div class="mb-5">
            <h3 class="font-semibold text-slate-900 dark:text-white">Account profile</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">User and business details shown on invoices.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="{{ $field }}">Full name
                <input name="full_name" required maxlength="100" value="{{ old('full_name', $user->full_name ?? '') }}" class="{{ $input }}">
                @error('full_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="{{ $field }}">Email address
                <input name="email" type="email" required maxlength="100" value="{{ old('email', $user->email ?? '') }}" class="{{ $input }}">
                @error('email')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="{{ $field }}">Phone
                <input name="phone" maxlength="15" value="{{ old('phone', $user->phone ?? '') }}" class="{{ $input }}">
                @error('phone')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="{{ $field }}">Business name
                <input name="company_name" maxlength="150" value="{{ old('company_name', $user->company_name ?? '') }}" class="{{ $input }}">
                @error('company_name')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="{{ $field }} sm:col-span-2">Business address
                <textarea name="company_address" rows="3" class="{{ $input }}">{{ old('company_address', $user->company_address ?? '') }}</textarea>
                @error('company_address')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="{{ $field }}">GSTIN
                <input name="company_gstin" maxlength="15" value="{{ old('company_gstin', $user->company_gstin ?? '') }}" class="{{ $input }}">
                @error('company_gstin')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
        <div class="mb-5">
            <h3 class="font-semibold text-slate-900 dark:text-white">Sign-in credentials</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">{{ $user ? 'Leave password blank to keep the current password. To resend credentials, enter and confirm a new password, then choose Send email again.' : 'Create the initial password for this account.' }}</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="{{ $field }}">Password{{ $user ? ' (optional)' : '' }}
                <input id="password" name="password" type="password" autocomplete="new-password" {{ $user ? '' : 'required' }} minlength="8" class="{{ $input }}">
                @error('password')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
            <label class="{{ $field }}">Confirm password
                <input name="password_confirmation" type="password" autocomplete="new-password" {{ $user ? '' : 'required' }} minlength="8" class="{{ $input }}">
            </label>
        </div>
        @error('send_credentials')<p class="mt-3 text-xs text-red-600">{{ $message }}</p>@enderror
    </section>

    <section class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm dark:border-zinc-800 dark:bg-zinc-900 sm:p-7">
        <div class="mb-5">
            <h3 class="font-semibold text-slate-900 dark:text-white">Access and invoice configuration</h3>
            <p class="mt-1 text-sm text-slate-500 dark:text-zinc-400">Configure account permissions and its invoice defaults.</p>
        </div>
        <div class="grid gap-5 sm:grid-cols-2">
            <label class="{{ $field }}">Account role
                <select name="is_admin" required class="{{ $input }}">
                    <option value="N" @selected(old('is_admin', $user->is_admin ?? 'N') === 'N')>Regular user</option>
                    <option value="Y" @selected(old('is_admin', $user->is_admin ?? 'N') === 'Y')>Administrator</option>
                </select>
            </label>
            <label class="{{ $field }}">Account status
                <select name="status" required class="{{ $input }}">
                    <option value="1" @selected((string) old('status', $user->status ?? '1') === '1')>Active</option>
                    <option value="0" @selected((string) old('status', $user->status ?? '1') === '0')>Inactive</option>
                    <option value="2" @selected((string) old('status', $user->status ?? '1') === '2')>Blocked</option>
                </select>
            </label>
            <label class="{{ $field }}">Jewelry Touch fields
                <select name="is_jewelry" required class="{{ $input }}">
                    <option value="0" @selected((string) old('is_jewelry', (int) ($user->is_jewelry ?? 0)) === '0')>Disabled — standard invoice</option>
                    <option value="1" @selected((string) old('is_jewelry', (int) ($user->is_jewelry ?? 0)) === '1')>Enabled — jewelry invoice</option>
                </select>
                <span class="mt-1.5 block text-xs font-normal text-slate-500 dark:text-zinc-400">Touch is optional and is displayed on product and invoice lines for this account.</span>
            </label>
            <label class="{{ $field }}">Assigned invoice template
                <select name="invoice_template" required class="{{ $input }}">
                    @foreach ($templates as $key => $template)
                        <option value="{{ $key }}" @selected(old('invoice_template', $user->invoice_template ?? config('invoice.default')) === $key)>{{ $template['label'] }}</option>
                    @endforeach
                </select>
                @error('invoice_template')<span class="mt-1 block text-xs text-red-600">{{ $message }}</span>@enderror
            </label>
        </div>
    </section>

    <div class="flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
        <a href="{{ route('admin.users.index') }}" class="inline-flex min-h-11 items-center justify-center rounded-xl border border-slate-200 px-5 text-sm font-semibold text-slate-700 hover:bg-slate-50 dark:border-zinc-700 dark:text-zinc-200 dark:hover:bg-zinc-800">Cancel</a>
        @if ($user)
            <button type="submit" name="send_credentials" value="1" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl border border-violet-300 px-5 text-sm font-semibold text-violet-700 transition hover:bg-violet-50 dark:border-violet-700 dark:text-violet-300 dark:hover:bg-violet-950/40">
                <i class="ti ti-mail-forward" aria-hidden="true"></i> Send email again
            </button>
        @endif
        <button type="submit" class="inline-flex min-h-11 items-center justify-center gap-2 rounded-xl bg-violet-600 px-5 text-sm font-semibold text-white shadow-sm transition hover:bg-violet-700">
            <i class="ti ti-device-floppy" aria-hidden="true"></i> {{ $submitLabel }}
        </button>
    </div>
</div>
