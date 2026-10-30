<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@carlacard.com'],
            [
                'name' => 'مدير المتجر',
                'password' => Hash::make(
                    env('ADMIN_PASSWORD', 'ChangeThisPassword123!')
                ),
                'role' => 'admin',
            ]
        );
    }
}