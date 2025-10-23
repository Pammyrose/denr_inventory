<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeesTable extends Migration
{
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('first_name');
            $table->string('middle_name')->nullable();
            $table->string('last_name');
            $table->string('suffix')->nullable();
            $table->string('sex', 1);
            $table->string('email')->unique();
            $table->string('contact_no')->unique();
            $table->string('emp_status');
            

            // ✅ Use foreign key IDs instead of names
            $table->unsignedBigInteger('position_name');
            $table->unsignedBigInteger('assignment_name');
            $table->unsignedBigInteger('div_sec_unit');

            $table->timestamps();

            // ✅ Proper foreign key constraints
            $table->foreign('position_name')->references('id')->on('positions')->onDelete('restrict');
            $table->foreign('assignment_name')->references('id')->on('assignments')->onDelete('restrict');
            $table->foreign('div_sec_unit')->references('id')->on('div_sec_units')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
