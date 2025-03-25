<?php

namespace App\Repositories;

use App\Models\ExcelInfo;
use App\Models\AutoInfo;
use App\Models\AutoTaskDetails;

class AutoTaskDetailsRepository
{
    // auto_task_details のテーブル全体を取得
    public function auto_task_details_get()
    {
        // dump("auto_task_details の 全取得");
        return AutoTaskDetails::all();
    }

    // 特定のIDのデータを取得
    public function auto_task_datails_find_by_id($excel_info_id)
    {
        return AutoTaskDetails::where('excel_info_id', $excel_info_id)->get();
    }

    // データの書込、更新
    public function upsert($data, $id, $excelInfo)
    {
        // ExcelInfoのレコードを取得
        $excelInfoID = ExcelInfo::where('file_name', $excelInfo['file_name'])
            ->where('sheet_name', $excelInfo['sheet_name'])
            ->first();

        // 被っているのが無かったらnullになる
        $excel_info_id = $excelInfoID ? $excelInfoID->id : null;

        // dump($excel_info_id);

        // データに格納
        $data['excel_info_id'] = $excel_info_id;


        // 空 or スペースのみのフィールドをNULLに置き換える
        foreach ($data as $key => $value) 
        {
            if ($value === " " || $value === "") 
            {
                $data[$key] = null;
            }
        }

        // 新しいレコードを挿入または更新
        return AutoTaskDetails::upsert(
            [$data], 
            ['id'],  // 一意キーとして使用するカラム名
            // ['excel_info_id', 'task_number', 'item', 'details', 'progress', 'plan_day', 'start_day', 'complete_day', 'dead_line']
            ['plan_day', 'start_day', 'complete_day', 'dead_line', 'worker']
        );
    }
}