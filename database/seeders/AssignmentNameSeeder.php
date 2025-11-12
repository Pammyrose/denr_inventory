<?php


namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentNameSeeder extends Seeder
{
    public function run()
    {
        $assignments = ['CAR'];

        foreach ($assignments as $assignment) {
            DB::table('assignments')->insert([
                'name'       => $assignment,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}