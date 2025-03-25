<?php

namespace App\Repositories;

use App\Models\ExcelInfo;
use App\Models\ProcessInfo;
use Illuminate\Support\Facades\DB;

class ProcessInfoRepository
{
    /*
     *
    */

    // process（加工）処理 ====================================================================================
    // テーブル全体を取得（file_type が process のものだけ取得）
    public function excel_info_process_get()
    {
        // return ExcelInfo::all();

        return ExcelInfo::where('file_type', 'process')->get();
    }

    // // 特定のIDのデータを取得
    // public function excel_info_find_by_id($id)
    // {
    //     return ExcelInfo::find($id);
    // }

    // ProceslInfo 取得
    public function process_info_get()
    {
        return ProcessInfo::all();
    }

    // idで取得
    public function process_info_find_by_id($excel_info_id)
    {
        // return ProcessInfo::find($id);
        return ProcessInfo::where('excel_info_id', $excel_info_id)->get();
    }

    // ProcessInfoからworkerだけを取得
    public function get_workers()
    {
        return ProcessInfo::select('worker')->distinct()->get(); // distinctを追加することで重複を排除
    }


    // データの書込、更新（excel部分の編集だと新規データに）
    public function upsert($data, $id, $excelInfo)
    {
        // ExcelInfoのレコードを取得
        $excelInfoID = ExcelInfo::where('file_name', $excelInfo['file_name'])
            ->where('sheet_name', $excelInfo['sheet_name'])
            ->first();

        // 被っているのが無かったらnullになる
        $excel_info_id = $excelInfoID ? $excelInfoID->id : null;

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
        return ProcessInfo::upsert(
            [$data], 
            ['id'],  // 一意キーとして使用するカラム名
            // ['name']

            // ['worker']

            // 品目、機種以外更新
            ['department', 'processing_number', 'equipment_number', 'worker']
        );
    }

    // auto（自動化）処理 ====================================================================================
}