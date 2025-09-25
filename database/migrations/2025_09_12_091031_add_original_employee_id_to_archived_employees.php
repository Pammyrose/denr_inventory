<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddOriginalEmployeeIdToArchivedEmployees extends Migration
{
    public function up()
    {
        Schema::table('archived_employees', function (Blueprint $table) {
            $table->unsignedBigInteger('original_employee_id')->nullable()->after('id');
        });
    }

    public function down()
    {
        Schema::table('archived_employees', function (Blueprint $table) {
            $table->dropColumn('original_employee_id');
        });
    }
}