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
        // auto ----------------------------------------------------------------------------------------------------------------
        Schema::create('auto_info', function (Blueprint $table) 
        {
            $table->id();
            $table->foreignId('excel_info_id')->nullable()->constrained('excel_info')->onDelete('cascade')->comment('エクセル情報（自動化）');
            $table->integer('row_number')->nullable()->comment('行番号'); #process_oinfoでも取得するため
            $table->string('department', 255)->nullable()->comment('製造課');
            $table->string('auto_item', 255)->nullable()->comment('区分');
            $table->string('auto_process', 255)->nullable()->comment('工程');
            $table->string('equipment_number', 255)->nullable()->comment('設備番号');
            $table->string('rb_dead_line', 255)->nullable()->comment('RB納期');
            $table->string('worker', 255)->nullable()->comment('担当者');
            $table->timestamps();
        });

        Schema::create('auto_task_details', function (Blueprint $table) 
        {
            $table->id();
            $table->foreignId('excel_info_id')->nullable()->constrained('excel_info')->onDelete('cascade')->comment('エクセル情報（自動化）');
            $table->integer('row_number')->nullable()->comment('行番号');
            $table->integer('task_number')->nullable()->comment('No.');
            $table->string('item', 255)->nullable()->comment('項目');
            $table->string('details', 255)->nullable()->comment('作業詳細');
 
            $table->date('plan_day')->nullable()->comment('予定日');
            $table->date('start_day')->nullable()->comment('着手日');
            $table->date('complete_day')->nullable()->comment('完了日');
            $table->date('dead_line')->nullable()->comment('納期');
            $table->string('worker', 255)->nullable()->comment('担当者');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auto_info');
        Schema::dropIfExists('auto_task_details');
    }
};
