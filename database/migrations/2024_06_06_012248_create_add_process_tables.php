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
        Schema::create('excel_info', function (Blueprint $table) 
        {
            $table->id();

            $table->string('file_name', 255)->nullable()->comment('ファイル名');
            $table->string('sheet_name', 255)->nullable()->comment('シート名');

            // 20240911 追加
            $table->string('file_type', 255)->nullable()->comment('処理の種類'); 

            // ajaxで使えなくなるから変更
            $table->string('complate_state', 255)->nullable()->comment('完了判定');

            $table->timestamps();

            // deleted_at
            // $table->softDeletes()->nullable();
        });
        
        Schema::create('process_info', function (Blueprint $table) 
        {
            $table->id();
            $table->foreignId('excel_info_id')->nullable()->constrained('excel_info')->onDelete('cascade')->comment('エクセル情報（工程）');

            // 20240917z328 追加
            $table->integer('row_number')->nullable()->comment('行番号'); #process_oinfoでも取得するため

            $table->string('department', 255)->nullable()->comment('製造課');
            $table->string('processing_item', 255)->nullable()->comment('品目');
            $table->string('processing_number', 255)->nullable()->comment('品番');
            $table->string('equipment_category', 255)->nullable()->comment('機種');
            $table->string('equipment_number', 255)->nullable()->comment('設備番号');

            // 20240927 追加
            $table->string('worker', 255)->nullable()->comment('担当者');

            $table->timestamps();

            // deleted_at
            // $table->softDeletes()->nullable();
        });

        Schema::create('process_task_details', function (Blueprint $table) 
        {
            $table->id();
            $table->foreignId('excel_info_id')->nullable()->constrained('excel_info')->onDelete('cascade')->comment('エクセル情報（工程）');

            // 202409111420 追加
            $table->integer('row_number')->nullable()->comment('行番号');

            $table->integer('task_number')->nullable()->comment('No.');
            $table->string('item', 255)->nullable()->comment('項目');
            $table->string('details', 255)->nullable()->comment('詳細');
            // $table->string('progress', 255)->nullable()->comment('進捗'); // 次回のmigrateで消す
            $table->date('plan_day')->nullable()->comment('予定日');
            $table->date('start_day')->nullable()->comment('着手日');
            $table->date('complete_day')->nullable()->comment('完了日');
            $table->date('dead_line')->nullable()->comment('納期');
            $table->string('worker', 255)->nullable()->comment('担当者');

            $table->timestamps();
            
            // deleted_at
            // $table->softDeletes()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
