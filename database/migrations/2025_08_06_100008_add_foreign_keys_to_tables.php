<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->foreign('org_code')->references('org_code')->on('org_units')->onDelete('cascade');
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->foreign('email')->references('email')->on('users')->onDelete('cascade');
            $table->foreign('emp_status')->references('id')->on('emp_stats')->onDelete('cascade');
            $table->foreign('position_name')->references('id')->on('positions')->onDelete('cascade');
            $table->foreign('assignment_name')->references('id')->on('assignment_places')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['org_code']);
        });

        Schema::table('employees', function (Blueprint $table) {
            $table->dropForeign(['email']);
            $table->dropForeign(['emp_status']);
            $table->dropForeign(['position_name']);
            $table->dropForeign(['assignment_name']);
        });
    }
};