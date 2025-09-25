<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\OrgUnit;

class OrgUnitTableSeeder extends Seeder
{
    public function run()
    {
        $departments = [
            ['org_code' => 'PMD', 'name' => 'PMD', 'description' => 'Project Management Department'],
            ['org_code' => 'FIN', 'name' => 'Finance', 'description' => 'Finance Department'],
            ['org_code' => 'ADM', 'name' => 'Admin', 'description' => 'Administration Department'],
            ['org_code' => 'LEG', 'name' => 'Legal', 'description' => 'Legal Department'],
            ['org_code' => 'CDD', 'name' => 'CDD', 'description' => 'Community Development Department'],
            ['org_code' => 'SMD', 'name' => 'SMD', 'description' => 'Sales and Marketing Department'],
            ['org_code' => 'LPD', 'name' => 'LPDD', 'description' => 'Logistics and Procurement Department'],
            ['org_code' => 'ENF', 'name' => 'Enforcement', 'description' => 'Enforcement Department'],
            ['org_code' => 'MSD', 'name' => 'MSD', 'description' => 'Management Services Department'],
            ['org_code' => 'TEC', 'name' => 'Technical', 'description' => 'Technical Department'],
        ];

        foreach ($departments as $dept) {
            OrgUnit::updateOrCreate(
                ['org_code' => $dept['org_code']],
                ['name' => $dept['name'], 'description' => $dept['description']]
            );
        }
    }
}