<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArchivedPositionsTable extends Migration
{
    public function up()
    {
        Schema::create('archived_positions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('original_position_id')->nullable();
            $table->string('item_code');
            $table->string('name');
            $table->text('desc')->nullable();
            $table->string('salary_grade')->nullable();
            $table->string('org_code')->nullable();
            $table->timestamp('archived_at')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('archived_positions');
    }
}