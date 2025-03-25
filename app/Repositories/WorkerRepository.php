<?php

namespace App\Repositories;

use App\Models\Worker;

class WorkerRepository
{
    /*
     *
    */

    // Worker 取得
    public function worker_get()
    {
        // return Worker::all();
        return Worker::pluck('name');   // name カラムのみ取得
    }

    // // データの書込、更新（excel部分の編集だと新規データに）----------------------------------------------------
    // public function upsert($data, $id, $excel_info_id)
    // {
    //     $data['id'] = $excel_info_id;

    //     // 新しいレコードを挿入または更新
    //     return ExcelInfo::upsert(
    //         [$data], 
    //         ['id'],  // 一意キーとして使用するカラム名

    //         // ['file_id', 'sheet_id', 'file_type', 'complate_state']

    //         ['file_type', 'complate_state']
    //     );
    // }

    // // データの削除（他のテーブルもExcelInfoに紐づいている為、まとめて消える） ------------------------------------
    // public function delete($id)
    // {
    //     $model = ExcelInfo::find($id);

    //     if ($model) 
    //     {
    //         // // 論理削除
    //         // $model->delete();

    //         // 完全削除（消したデータで行う処理はない為）
    //         $model->forceDelete();
    //     }
    // }
}