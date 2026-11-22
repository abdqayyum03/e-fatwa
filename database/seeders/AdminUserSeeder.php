<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class AdminUserSeeder extends Seeder
{
    /**
     * Create (or update) the admin account from .env, so no password
     * is ever written into the code:
     *
     *   ADMIN_NAME="Admin"
     *   ADMIN_EMAIL=admin@example.com
     *   ADMIN_PASSWORD=choose-a-strong-password
     */
    public function run(): void
    {
        $email = env('ADMIN_EMAIL');
        $password = env('ADMIN_PASSWORD');

        if (! $email || ! $password) {
            $this->command?->warn('ADMIN_EMAIL / ADMIN_PASSWORD not set in .env - skipping admin account.');

            return;
        }

        // forceFill because is_admin is deliberately not mass-assignable,
        // so no form or API request can ever make itself an admin.
        User::firstOrNew(['email' => $email])
            ->forceFill([
                'name' => env('ADMIN_NAME', 'Admin'),
                'password' => $password, // hashed by the User model's cast
                'is_admin' => true,
            ])
            ->save();

        $this->command?->info("Admin account ready: {$email}");
    }
}
