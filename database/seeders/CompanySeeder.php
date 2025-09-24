<?php

namespace Database\Seeders;

use App\Models\Company;
use Illuminate\Database\Seeder;

class CompanySeeder extends Seeder
{
    public function run(): void
    {
        // Create a demo company
        Company::factory()->create([
            'name' => 'Beauty Salon Demo',
            'slug' => 'beauty-salon-demo',
            'timezone' => 'UTC',
            'currency' => 'USD',
        ]);

        // Create additional random companies
        Company::factory(4)->create();
    }
}