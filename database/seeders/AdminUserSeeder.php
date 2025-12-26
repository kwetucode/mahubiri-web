<?php

namespace Database\Seeders;

use App\Enums\RoleType;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Check if admin user already exists
        $adminExists = User::where('email', 'admin@mahubiri.com')->exists();

        if (!$adminExists) {
            User::create([
                'name' => 'Administrateur',
                'email' => 'admin@mahubiri.com',
                'password' => Hash::make('password'), // Change this in production
                'email_verified_at' => now(),
                'role_id' => RoleType::ADMIN,
            ]);

            $this->command->info('Admin user created successfully!');
            $this->command->info('Email: admin@mahubiri.com');
            $this->command->info('Password: password');
            $this->command->warn('⚠️  Please change the password in production!');
        } else {
            $this->command->info('Admin user already exists.');
        }
    }
}
