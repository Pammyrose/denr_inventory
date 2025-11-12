<?php

namespace Database\Seeders;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;


class DatabaseSeeder extends Seeder
{
    public function run()
    {

 DB::statement('SET FOREIGN_KEY_CHECKS=0;');
 $this->call([
        
            DivisionSectionUnitSeeder::class,
           
            PositionSeeder::class,
            AssignmentNameSeeder::class,
     
        ]);
 DB::statement('SET FOREIGN_KEY_CHECKS=1;');



    }
}