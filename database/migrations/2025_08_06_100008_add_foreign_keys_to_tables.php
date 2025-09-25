<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // ✅ positions.org_code → org_units.org_code
        Schema::table('positions', function (Blueprint $table) {
            $table->foreign('org_code')
                  ->references('org_code')
                  ->on('org_units')
                  ->onDelete('cascade');
        });

        Schema::table('employees', function (Blueprint $table) {
            // ✅ Instead of using email as FK, use user_id (if you want relation to users)
            // Example: $table->unsignedBigInteger('user_id')->nullable();
            // $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

            // ✅ Proper foreign keys (need *_id columns in employees)
            $table->foreign('emp_status_id')
                  ->references('id')
                  ->on('emp_status')
                  ->onDelete('cascade');

            $table->foreign('position_id')
                  ->references('id')
                  ->on('positions')
                  ->onDelete('cascade');

            $table->foreign('assignment_id')
                  ->references('id')
                  ->on('assignment_places')
                  ->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropForeign(['org_code']);
        });

        Schema::table('employees', function (Blueprint $table) {
            // Drop by column names used above
            $table->dropForeign(['emp_status_id']);
            $table->dropForeign(['position_id']);
            $table->dropForeign(['assignment_id']);
        });
    }
};
