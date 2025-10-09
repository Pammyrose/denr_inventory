<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class RenameEmployeeColumnsAndAddUserId extends Migration
{
    public function up()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop existing foreign key constraints
            $table->dropForeign(['position_id']);
            $table->dropForeign(['assignment_id']);
            $table->dropForeign(['org_unit_id']);

            // Rename columns
            $table->renameColumn('position_id', 'position_name');
            $table->renameColumn('assignment_id', 'assignment_name');
            $table->renameColumn('org_unit_id', 'div_sec_unit');

            // Re-add foreign key constraints with new column names
            $table->foreign('position_name')->references('id')->on('positions')->onDelete('restrict');
            $table->foreign('assignment_name')->references('id')->on('assignment_places')->onDelete('restrict');
            $table->foreign('div_sec_unit')->references('id')->on('org_units')->onDelete('restrict');

            // Add user_id column
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade')->after('div_sec_unit');
        });
    }

    public function down()
    {
        Schema::table('employees', function (Blueprint $table) {
            // Drop user_id foreign key and column
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');

            // Drop renamed foreign key constraints
            $table->dropForeign(['position_name']);
            $table->dropForeign(['assignment_name']);
            $table->dropForeign(['div_sec_unit']);

            // Rename columns back
            $table->renameColumn('position_name', 'position_id');
            $table->renameColumn('assignment_name', 'assignment_id');
            $table->renameColumn('div_sec_unit', 'org_unit_id');

            // Re-add original foreign key constraints
            $table->foreign('position_id')->references('id')->on('positions')->onDelete('restrict');
            $table->foreign('assignment_id')->references('id')->on('assignment_places')->onDelete('restrict');
            $table->foreign('org_unit_id')->references('id')->on('org_units')->onDelete('restrict');
        });
    }
}