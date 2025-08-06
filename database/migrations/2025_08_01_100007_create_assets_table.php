<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->string('asset_id')->unique();
            $table->string('name');
            $table->string('category');
            $table->string('location');
            $table->date('purchase_date');
            $table->decimal('value', 10, 2);
            $table->string('condition');
            $table->string('assigned')->nullable(); // Or foreignId('assigned')->nullable()->constrained('employees')
            $table->string('status');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};