<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoTaskDetails extends Model
{
    use HasFactory;

    protected $table = 'auto_task_details';

    /*
        excel_info_id   / 判定のため（ファイル名、シート名が一致）
        row_number      / 行番号
        task_number     / 表示するときに使う番号
        item            / 項目	
        details         / 詳細
        plan_day        / 予定日
        start_day       / 着手日
        complete_day    / 完了日
        dead_line       / 納期
        worker          / 担当者
    */

    // 変更する可能性があるものは宣言しておく
    protected $fillable = [
        'excel_info_id',
        'row_number',
        'task_number',
        'item',
        'details',
        'plan_day',
        'start_day',
        'complete_day',
        'dead_line',
        'worker'
    ];
}
