<?php

namespace App\Repositories;

use App\Models\UpdateManagement;

class UpdateManagementRepository
{
    public function upsert($data)
    {
        // dd($data);
        UpdateManagement::upsert(
            // 追加もしくは更新するデータ（idがnullの場合は追加）
            // 複数行追加できるため、一つの場合でも、配列に入れる
            [$data],
            ['id'],          // 存在するかどうかを確認するためのカラム
            ['process_update', 'auto_update']     // 更新したいカラム
            // ['process', 'auto', 'list']     // 更新したいカラム
        );
    }

    public function select()
    {
        // return UpdateManagement::first();
        return UpdateManagement::find(1);
        // return UpdateManagement::all();
    }

    // public function upsert($data, $user_id)
    // {
    //     $data['user_id'] = $user_id;
    //     Todo::upsert(
    //         [$data],
    //         ['id'], 
    //         ['title', 'date', 'mail', 'content']
    //     );
    // }
}