<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Department;
use App\Models\Designation;

class DepartmentDesignationSeeder extends Seeder
{
    public function run(): void
    {
        // Create Departments
        $departments = [
            ['name' => 'Sales', 'description' => 'Sales and Business Development'],
            ['name' => 'Marketing', 'description' => 'Marketing and Communications'],
            ['name' => 'IT', 'description' => 'Information Technology'],
            ['name' => 'Human Resources', 'description' => 'HR and Recruitment'],
            ['name' => 'Finance', 'description' => 'Finance and Accounting'],
            ['name' => 'Operations', 'description' => 'Operations Management'],
            ['name' => 'Customer Service', 'description' => 'Customer Support'],
            ['name' => 'Procurement', 'description' => 'Purchasing and Procurement'],
        ];

        foreach ($departments as $dept) {
            Department::firstOrCreate(
                ['name' => $dept['name']],
                $dept
            );
        }

        // Create Designations
        $designations = [
            ['name' => 'Manager', 'description' => 'Department Manager'],
            ['name' => 'Assistant Manager', 'description' => 'Assistant to Manager'],
            ['name' => 'Team Leader', 'description' => 'Team Lead'],
            ['name' => 'Senior Executive', 'description' => 'Senior Level Executive'],
            ['name' => 'Executive', 'description' => 'Mid-level Executive'],
            ['name' => 'Junior Executive', 'description' => 'Entry-level Executive'],
            ['name' => 'Intern', 'description' => 'Internship Position'],
            ['name' => 'Director', 'description' => 'Department Director'],
            ['name' => 'CEO', 'description' => 'Chief Executive Officer'],
            ['name' => 'CFO', 'description' => 'Chief Financial Officer'],
            ['name' => 'CTO', 'description' => 'Chief Technology Officer'],
        ];

        foreach ($designations as $desig) {
            Designation::firstOrCreate(
                ['name' => $desig['name']],
                $desig
            );
        }
    }
}
