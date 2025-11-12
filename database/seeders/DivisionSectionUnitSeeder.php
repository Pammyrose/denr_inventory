<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisionSectionUnitSeeder extends Seeder
{
    public function run()
    {
        $units = ['PLANNING AND MANAGEMENT DIVISION'];

        foreach ($units as $unit) {
            DB::table('div_sec_units')->insert([
                'name'       => $unit,
                'org_code' => 'PMD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}