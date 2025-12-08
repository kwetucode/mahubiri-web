<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
            ['name' => 'user'],
            ['name' => 'admin'],
            ['name' => 'church_admin'],
            ['name' => 'moderator'],
            ['name' => 'independent_preacher'],
        ];
        foreach ($roles as $role) {
            Role::updateOrInsert(['name' => $role['name']], $role);
        }
    }
}
