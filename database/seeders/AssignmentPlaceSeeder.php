<?php
namespace Database\Seeders;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AssignmentPlaceSeeder extends Seeder
{
    public function run()
    {
        DB::table('assignment_places')->insert([
            ['id' => 1, 'name' => 'Head Office', 'created_at' => now(), 'updated_at' => now()],
            ['id' => 2, 'name' => 'Branch Office', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}