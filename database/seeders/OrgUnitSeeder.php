<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class OrgUnitSeeder extends Seeder
{
    public function run()
    {
        DB::table('org_units')->insert([
            [
                'org_code' => 'ORG001',
                'name' => 'IT Department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_code' => 'ORG002',
                'name' => 'Project Management Office',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_code' => 'ORG003',
                'name' => 'Data Analytics Division',
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'org_code' => 'ORG004',
                'name' => 'Human Resources Department',
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}