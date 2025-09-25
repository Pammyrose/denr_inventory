<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_maintenance', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id');
            $table->foreign('asset_id')->references('asset_id')->on('assets')->onDelete('cascade');
            $table->string('action')->comment('Repair or Upgrade');
            $table->date('action_date');
            $table->timestamps();
            $table->index(['asset_id', 'action_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_maintenance');
    }
};