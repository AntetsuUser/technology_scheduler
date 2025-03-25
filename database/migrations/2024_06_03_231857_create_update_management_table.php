<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('update_management', function (Blueprint $table) {
            $table->id();
            $table->boolean('process_update')->default(false);
            $table->boolean('auto_update')->default(false);
            // $table->boolean('process')->default(false);
            // $table->boolean('auto')->default(false);
            // $table->boolean('list')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('update_management');
    }
};
