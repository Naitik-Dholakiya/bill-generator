<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class PromoteUserToAdministrator extends Command
{
    protected $signature = 'admin:promote {email : Email address of the existing account}';

    protected $description = 'Grant administrator access to an existing user account';

    public function handle(): int
    {
        $user = DB::table('usermaster')->where('email', $this->argument('email'))->first(['user_id', 'full_name', 'is_admin']);
        if ($user === null) {
            $this->error('No account was found with that email address.');

            return self::FAILURE;
        }

        if ($user->is_admin === 'Y') {
            $this->info("{$user->full_name} already has administrator access.");

            return self::SUCCESS;
        }

        if (!$this->confirm("Grant administrator access to {$user->full_name}?")) {
            $this->info('No changes made.');

            return self::SUCCESS;
        }

        DB::table('usermaster')->where('user_id', $user->user_id)->update([
            'is_admin' => 'Y',
            'updated_at' => now(),
        ]);

        $this->info('Administrator access granted. Sign in through the regular login page.');

        return self::SUCCESS;
    }
}
