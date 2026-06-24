<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $this->upsertUser('admin@example.com', 'Admin User', 'admin');
        $this->upsertUser('staff@example.com', 'Staff User', 'staff');
        $this->upsertUser('test@example.com', 'Test User', 'admin');
    }

    private function upsertUser(string $email, string $name, string $role): void
    {
        $user = User::query()->firstOrNew(['email' => $email]);

        $user->forceFill([
            'name' => $name,
            'password' => Hash::make('password'),
            'role' => $role,
            'email_verified_at' => now(),
        ])->save();
    }
}
