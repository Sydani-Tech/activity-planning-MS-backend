<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Create departments based on Excel data
        $departments = [
            'HCD' => 'Health Care Development',
            'HCD-PA' => 'Health Care Development - Program Area',
            'DS/PHI' => 'Disease Surveillance / Public Health Intelligence',
            'FA-HCD' => 'Finance & Admin - HCD',
            'LGCS/PHDA' => 'Local Government Community Services / PHDA',
            'PS/MOH' => 'Permanent Secretary / Ministry of Health',
            'DPHRS' => 'Director of Pharmaceutical Services',
            'PRS/SS' => 'Planning Research & Statistics',
            'PS/HCPA' => 'Primary Health Care Programs Area',
            'General' => 'General Administration',
        ];

        foreach ($departments as $code => $description) {
            Department::create([
                'name' => $code,
                'description' => $description,
            ]);
        }

        // Create default Super Admin
        User::create([
            'name' => 'Super Admin',
            'email' => 'admin@nigerstate.gov.ng',
            'password' => Hash::make('Admin@1234'),
            'role' => 'super_admin',
            'is_active' => true,
        ]);

        // Create test users for each role
        $hcd = Department::where('name', 'HCD')->first();

        User::create([
            'name' => 'Admin User',
            'email' => 'admin@smoh.ng',
            'password' => Hash::make('Admin@1234'),
            'role' => 'admin',
            'department_id' => $hcd->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Focal Person',
            'email' => 'focal@smoh.ng',
            'password' => Hash::make('Admin@1234'),
            'role' => 'focal_person',
            'department_id' => $hcd->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Program Manager',
            'email' => 'manager@smoh.ng',
            'password' => Hash::make('Admin@1234'),
            'role' => 'program_manager',
            'department_id' => $hcd->id,
            'is_active' => true,
        ]);

        User::create([
            'name' => 'Commissioner',
            'email' => 'executive@smoh.ng',
            'password' => Hash::make('Admin@1234'),
            'role' => 'executive',
            'is_active' => true,
        ]);
    }
}
