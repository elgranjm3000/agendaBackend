<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Company;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();

        foreach ($companies as $company) {
            // Create owner
            User::factory()->create([
                'company_id' => $company->id,
                'name' => 'Owner User',
                'email' => "owner@{$company->slug}.com",
                'password' => Hash::make('password'),
                'role' => 'owner',
            ]);

            // Create manager
            User::factory()->create([
                'company_id' => $company->id,
                'name' => 'Manager User',
                'email' => "manager@{$company->slug}.com",
                'password' => Hash::make('password'),
                'role' => 'manager',
            ]);

            // Create staff members
            User::factory(3)->create([
                'company_id' => $company->id,
                'role' => 'staff',
            ]);

            // Create viewer
            User::factory()->create([
                'company_id' => $company->id,
                'role' => 'viewer',
            ]);
        }
    }
}