<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Worker extends Model
{
    use HasFactory;

    protected $table = 'worker';

    /**
     * file_type        / 「process（加工）」か「auto（自動）」か
     * name             /　作業者、担当者の名前
    */

    // 変更する可能性があるものは宣言しておく（create or insertするカラム名）
    protected $fillable = [
        'file_type',
        'name'
    ];
}