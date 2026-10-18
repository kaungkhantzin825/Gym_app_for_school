<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

/**
 * Promotes an existing account to admin, or creates one.
 *
 * This is the deliberate way out of bootstrap mode: once any account has
 * role=admin, /admin stops being open to every logged-in user.
 */
class PromoteAdmin extends Command
{
    protected $signature = 'admin:promote
                            {email : Email of the account to promote}
                            {--name= : Name to use if the account has to be created}
                            {--password= : Password to use if the account has to be created}';

    protected $description = 'Grant admin access to an account (creating it if needed)';

    public function handle(): int
    {
        $email = (string) $this->argument('email');

        $user = User::where('email', $email)->first();

        if ($user === null) {
            $password = $this->option('password');

            if ($password === null) {
                $password = $this->secret('No account with that email. Password for the new admin?');

                if (blank($password)) {
                    $this->error('A password is required to create the account.');

                    return self::FAILURE;
                }
            }

            $user = User::create([
                'name' => $this->option('name') ?: 'Administrator',
                'email' => $email,
                'password' => $password,
                'role' => 'admin',
            ]);

            $this->info("Created {$user->email} and granted admin access.");

            return self::SUCCESS;
        }

        if ($user->isAdmin()) {
            $this->info("{$user->email} is already an admin.");

            return self::SUCCESS;
        }

        $user->forceFill(['role' => 'admin'])->save();

        $this->info("Granted admin access to {$user->email}.");

        return self::SUCCESS;
    }
}
