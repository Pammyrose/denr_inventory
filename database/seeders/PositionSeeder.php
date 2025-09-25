<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PositionSeeder extends Seeder
{
    public function run()
    {
        // Ensure OrgUnitSeeder runs first
        $this->call(OrgUnitSeeder::class);

        DB::table('positions')->insert([
            [
                'id' => 1,
                'item_code' => 'POS001',
                'org_code' => 'ORG001',
                'name' => 'Software Engineer',
                'desc' => 'Develops and maintains software applications',
                'salary_grade' => 'SG-15',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 2,
                'item_code' => 'POS002',
                'org_code' => 'ORG002',
                'name' => 'Project Manager',
                'desc' => 'Oversees project execution and team coordination',
                'salary_grade' => 'SG-20',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 3,
                'item_code' => 'POS003',
                'org_code' => 'ORG003',
                'name' => 'Data Analyst',
                'desc' => 'Analyzes data to provide business insights',
                'salary_grade' => 'SG-17',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'id' => 4,
                'item_code' => 'POS004',
                'org_code' => 'ORG004',
                'name' => 'HR Specialist',
                'desc' => 'Manages recruitment and employee relations',
                'salary_grade' => 'SG-18',
                'status' => 'Active',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}