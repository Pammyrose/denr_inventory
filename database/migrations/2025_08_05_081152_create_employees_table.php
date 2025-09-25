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
            $table->string('emp_status');

            // ✅ Use foreign key IDs instead of names
            $table->unsignedBigInteger('position_id');
            $table->unsignedBigInteger('assignment_id');
            $table->unsignedBigInteger('org_unit_id');

            $table->timestamps();

            // ✅ Proper foreign key constraints
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('restrict');
            $table->foreign('assignment_id')->references('id')->on('assignment_places')->onDelete('restrict');
            $table->foreign('org_unit_id')->references('id')->on('org_units')->onDelete('restrict');
        });
    }

    public function down()
    {
        Schema::dropIfExists('employees');
    }
}
