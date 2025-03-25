<?php

namespace App\Repositories;

use App\Models\ExcelInfo;

class HomeRepository
{
    /*
     *
    */


    // process、auto どちらも処理は同じ ======================================================================
    // public function insert($data)
    // {
    //     // トランザクションの開始
    //     // DB::beginTransaction();

    //    // 同じfile_nameとsheet_nameが存在するか確認
    //    $exists = ExcelInfo::where('file_name', $data['file_name'])
    //         ->where('sheet_name', $data['sheet_name'])
    //         // ->where('fily_type', $data['fily_type'])
    //         ->exists();

    //     // 存在しない場合のみ挿入
    //     if (!$exists) 
    //     {
    //         ExcelInfo::create($data);
    //     } 
    //     else
    //     {
    //         // 重複する場合はupdated_atフィールドのみ更新
    //         ExcelInfo::where('file_name', $data['file_name'])
    //             ->where('sheet_name', $data['sheet_name'])
    //             // ->where('fily_type', $data['fily_type'])
    //             ->update(['updated_at' => now()]);

    //         // デバッグ用
    //         // dump('被り / アップデート');
    //     }
    // }

    // ExcellInfo 取得
    public function excel_info_get()
    {
        return ExcelInfo::all();
    }

    // 特定のIDのデータを取得
    public function excel_info_find_by_id($id)
    {
        return ExcelInfo::find($id);
    }

    // データの書込、更新（excel部分の編集だと新規データに）----------------------------------------------------
    public function upsert($data, $id, $excel_info_id)
    {
        $data['id'] = $excel_info_id;

        // 新しいレコードを挿入または更新
        return ExcelInfo::upsert(
            [$data], 
            ['id'],  // 一意キーとして使用するカラム名

            // ['file_id', 'sheet_id', 'file_type', 'complate_state']

            ['file_type', 'complate_state']
        );
    }

    // データの削除（他のテーブルもExcelInfoに紐づいている為、まとめて消える） ------------------------------------
    public function delete($id)
    {
        $model = ExcelInfo::find($id);

        if ($model) 
        {
            // // 論理削除
            // $model->delete();

            // 完全削除（消したデータで行う処理はない為）
            $model->forceDelete();
        }
    }
}