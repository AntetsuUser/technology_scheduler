<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ProcessInfo extends Model
{
    use HasFactory;

    protected $table = 'process_info';

    // 変更する可能性があるものは宣言しておく
    /*  
        excel_info_id       / 判定のため（ファイル名、シート名が一致）
        row_number          / 行番号
        department          / 製造課	
        processing_item     / 品目
        processing_number   / 品番
        equipment_category  / 機種
        equipment_number    / 設備番号
        worker              / 担当者 
    */

    protected $fillable = [
        'excel_info_id',
        'row_numer',
        'department',
        'processing_item',
        'processing_number',
        'equipment_category',
        'equipment_number',
        'worker'
    ];
}
