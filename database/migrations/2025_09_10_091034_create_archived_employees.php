<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('archived_employees', function (Blueprint $table) {
            $table->engine = 'InnoDB';
            $table->id();
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();
            $table->string('suffix')->nullable();
            $table->string('sex', 1)->nullable();
            $table->string('email')->unique()->nullable();
            $table->string('emp_status')->nullable();
            $table->unsignedBigInteger('position_id')->nullable();
            $table->unsignedBigInteger('assignment_id')->nullable();
            $table->unsignedBigInteger('org_unit_id')->nullable();
            $table->timestamp('archived_at')->useCurrent();
            $table->timestamps();

            $table->foreign('position_id')->references('id')->on('positions')->onDelete('restrict');
            $table->foreign('assignment_id')->references('id')->on('assignment_places')->onDelete('restrict');
            $table->foreign('org_unit_id')->references('id')->on('org_units')->onDelete('restrict');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('archived_employees');
    }
};