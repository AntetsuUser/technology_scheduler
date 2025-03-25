<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ExcelInfo extends Model
{
    use HasFactory;

    protected $table = 'excel_info';

    /**
     * file_name        / ファイルの名前
     * sheet_name       / シートの名前
     * 
     * file_type        / 「process（加工）」か「auto（自動）」か
     * complate_state   / 完了判定
    */

    // 変更する可能性があるものは宣言しておく（create or insertするカラム名）
    protected $fillable = [
        'file_name',
        'sheet_name',

        'file_type',
        'complate_state'
    ];
}