<?php
namespace Database\Seeders;

use App\Models\Service;
use App\Models\Company;
use Illuminate\Database\Seeder;

class ServiceSeeder extends Seeder
{
    public function run(): void
    {
        $companies = Company::all();
        
        $services = [
            ['name' => 'Basic Haircut', 'category' => 'Hair', 'duration_minutes' => 30, 'price' => 35.00],
            ['name' => 'Hair Wash & Style', 'category' => 'Hair', 'duration_minutes' => 45, 'price' => 50.00],
            ['name' => 'Hair Color', 'category' => 'Hair', 'duration_minutes' => 120, 'price' => 85.00],
            ['name' => 'Highlights', 'category' => 'Hair', 'duration_minutes' => 150, 'price' => 120.00],
            ['name' => 'Deep Conditioning', 'category' => 'Hair', 'duration_minutes' => 30, 'price' => 25.00],
            ['name' => 'Swedish Massage', 'category' => 'Wellness', 'duration_minutes' => 60, 'price' => 80.00],
            ['name' => 'Deep Tissue Massage', 'category' => 'Wellness', 'duration_minutes' => 60, 'price' => 90.00],
            ['name' => 'Hot Stone Massage', 'category' => 'Wellness', 'duration_minutes' => 90, 'price' => 120.00],
            ['name' => 'Basic Facial', 'category' => 'Skincare', 'duration_minutes' => 45, 'price' => 65.00],
            ['name' => 'Anti-Aging Facial', 'category' => 'Skincare', 'duration_minutes' => 60, 'price' => 85.00],
            ['name' => 'Acne Treatment', 'category' => 'Skincare', 'duration_minutes' => 60, 'price' => 75.00],
            ['name' => 'Basic Manicure', 'category' => 'Nails', 'duration_minutes' => 30, 'price' => 25.00],
            ['name' => 'Gel Manicure', 'category' => 'Nails', 'duration_minutes' => 45, 'price' => 40.00],
            ['name' => 'Basic Pedicure', 'category' => 'Nails', 'duration_minutes' => 45, 'price' => 35.00],
            ['name' => 'Spa Pedicure', 'category' => 'Nails', 'duration_minutes' => 60, 'price' => 50.00],
        ];

        foreach ($companies as $company) {
            foreach ($services as $service) {
                Service::factory()->create(array_merge($service, [
                    'company_id' => $company->id,
                ]));
            }
        }
    }
}