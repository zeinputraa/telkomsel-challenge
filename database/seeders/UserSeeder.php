<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = Role::pluck('id', 'name');

        $accounts = [
            ['name' => 'Admin Testing',    'email' => 'admin@telkomsel.test',    'role' => 'admin'],
            ['name' => 'Staff Testing',    'email' => 'staff@telkomsel.test',    'role' => 'staff'],
            ['name' => 'Manager Testing',  'email' => 'manager@telkomsel.test',  'role' => 'manager'],
            ['name' => 'Karyawan Testing', 'email' => 'karyawan@telkomsel.test', 'role' => 'karyawan'],
        ];

        foreach ($accounts as $akun) {
            User::create([
                'name' => $akun['name'],
                'email' => $akun['email'],
                'password' => Hash::make('password'),
                'role_id' => $roles[$akun['role']],
                'email_verified_at' => now(),
            ]);
        }
    }
}
