<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UpdateManagement extends Model
{
    use HasFactory;

    // DBとの紐付けを明示的に
    protected $table = 'update_management';

    protected $fillable = [
        // DBに書き込まれるカラムを指定する
        'process_update',
        'auto_update'
        // 'process',
        // 'auto',
        // 'list',
    ];

    // // 初期値を定義する
    // protected $attributes = [
    //     // 'process' => false,
    //     // 'auto' => false,
    //     // 'list' => false,
    // ];
}
