<?php

namespace Database\Seeders;

use App\Enums\ApplicationStatus as Status;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Creates a demo account with a realistic-looking board,
     * so anyone trying the app can log in and see it working straight away.
     *
     * Login: demo@jobpilot.test / password
     */
    public function run(): void
    {
        $user = User::factory()->create([
            'name' => 'Demo User',
            'email' => 'demo@jobpilot.test',
        ]);

        $jobs = [
            // [company, role, location, salary, status, days since applied]
            ['Northwind Labs', 'Junior Laravel Developer', 'Remote', '£30k–35k', Status::Wishlist, null],
            ['Brightpath Health', 'Backend Developer (PHP)', 'Manchester', '£38k', Status::Wishlist, null],
            ['Pixel & Pine', 'Full Stack Developer', 'Leeds', null, Status::Wishlist, null],
            ['Cloudharbor', 'Graduate Software Engineer', 'London', '£35k', Status::Applied, 3],
            ['Tidewater Fintech', 'PHP Developer', 'Hybrid · Bristol', '£40k', Status::Applied, 6],
            ['Lumen Retail', 'Web Developer', 'Remote', null, Status::Applied, 12],
            ['Orbit Logistics', 'Laravel Developer', 'Birmingham', '£42k', Status::Interview, 10],
            ['Greenline Energy', 'Junior Backend Engineer', 'Remote', '£33k', Status::Interview, 15],
            ['Atlas Learning', 'Software Developer', 'Edinburgh', '£36k', Status::Offer, 21],
            ['Quarry Games', 'Backend Engineer', 'Guildford', null, Status::Rejected, 30],
        ];

        $positions = [];

        foreach ($jobs as [$company, $role, $location, $salary, $status, $daysAgo]) {
            $user->applications()->create([
                'company' => $company,
                'role' => $role,
                'location' => $location,
                'salary' => $salary,
                'status' => $status,
                'position' => $positions[$status->value] = ($positions[$status->value] ?? -1) + 1,
                'applied_at' => $daysAgo ? now()->subDays($daysAgo) : null,
            ]);
        }
    }
}
