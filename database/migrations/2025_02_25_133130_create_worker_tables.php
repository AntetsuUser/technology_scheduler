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
        // Schema::create('worker_tables', function (Blueprint $table) {
        //     $table->id();
        //     $table->timestamps();
        // });

        // 担当者マスタ？ ----------------------------------------------------------------------------------------------------------------
        Schema::create('worker', function (Blueprint $table) 
        {
            $table->id();
            $table->string('file_type', 255)->nullable()->comment('処理の種類');        //加工か自動化か（今のところ自動化だけ）
            $table->string('name', 255)->nullable()->comment('氏名');                  //加工か自動化か（今のところ自動化だけ）
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Schema::dropIfExists('worker_tables');
        Schema::dropIfExists('worker');
    }
};
