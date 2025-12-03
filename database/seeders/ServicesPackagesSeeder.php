<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Service;
use App\Models\Package;

class ServicesPackagesSeeder extends Seeder
{
    public function run(): void
    {
        // Create Services
        $services = [
            ['name' => 'Website Design', 'price' => 500, 'duration' => 2880, 'status' => 'active'],
            ['name' => 'Logo Design', 'price' => 150, 'duration' => 480, 'status' => 'active'],
            ['name' => 'SEO Optimization', 'price' => 300, 'duration' => 1440, 'status' => 'active'],
            ['name' => 'Content Writing', 'price' => 100, 'duration' => 240, 'status' => 'active'],
            ['name' => 'Social Media Setup', 'price' => 200, 'duration' => 480, 'status' => 'active'],
            ['name' => 'Email Marketing Setup', 'price' => 250, 'duration' => 720, 'status' => 'active'],
            ['name' => 'Analytics Integration', 'price' => 100, 'duration' => 120, 'status' => 'active'],
            ['name' => 'Maintenance (Monthly)', 'price' => 150, 'duration' => 480, 'status' => 'active'],
        ];

        foreach ($services as $index => $service) {
            Service::create(array_merge($service, ['sort_order' => $index]));
        }

        // Create Packages
        $packages = [
            [
                'name' => 'Starter Package',
                'description' => 'Perfect for small businesses just getting started.',
                'price' => 699,
                'discount_price' => 599,
                'validity_days' => 30,
                'status' => 'active',
                'is_featured' => false,
                'services' => [1, 2] // Website Design, Logo Design
            ],
            [
                'name' => 'Business Package',
                'description' => 'Comprehensive solution for growing businesses.',
                'price' => 1299,
                'discount_price' => 999,
                'validity_days' => 60,
                'status' => 'active',
                'is_featured' => true,
                'services' => [1, 2, 3, 4, 5] // Multiple services
            ],
            [
                'name' => 'Enterprise Package',
                'description' => 'Full-service package for established enterprises.',
                'price' => 2499,
                'discount_price' => 1999,
                'validity_days' => 90,
                'status' => 'active',
                'is_featured' => true,
                'services' => [1, 2, 3, 4, 5, 6, 7, 8] // All services
            ],
        ];

        foreach ($packages as $index => $packageData) {
            $serviceIds = $packageData['services'];
            unset($packageData['services']);
            
            $package = Package::create(array_merge($packageData, ['sort_order' => $index]));
            
            foreach ($serviceIds as $serviceId) {
                $package->services()->attach($serviceId, ['quantity' => 1]);
            }
        }
    }
}