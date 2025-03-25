<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AutoInfo extends Model
{
    use HasFactory;

    protected $table = 'auto_info';

    // 変更する可能性があるものは宣言しておく
    /*  
        excel_info_id       / 判定のため（ファイル名、シート名が一致）
        row_number          / 行番号
        department          / 製造課	
        auto_item           / 区分
        auto_process        / 工程
        equipment_number    / 設備番号
        rb_dead_line        / RB納期
        worker              / 担当者 
    */
    protected $fillable = [
        'excel_info_id',
        'row_number',
        'department',
        'auto_item',
        'auto_process',
        'equipment_number',
        'rb_dead_line',
        'worker'
    ];

}
