<?php

namespace App\Repositories;

use App\Models\ExcelInfo;
use App\Models\AutoInfo;
use Illuminate\Support\Facades\DB;

class AutoInfoRepository
{
    /*
     *
    */

    // excel_info / file_type が 'auto' のテーブル全体を取得
    public function excel_info_auto_get()
    {
        // dump("excel_info(auto)の 全取得");
        return ExcelInfo::where('file_type', 'auto')->get();
    }

    // auto_info のテーブル全体を取得
    public function auto_info_get()
    {
        // dump("auto_info の 全取得");
        return AutoInfo::all();
    }

    // idで特定のテーブル取得
    public function auto_info_find_by_id($excel_info_id)
    {
        // return ProcessInfo::find($id);
        return AutoInfo::where('excel_info_id', $excel_info_id)->get();
    }

    // ProcessInfoからworkerだけを取得
    public function get_workers()
    {
        return AutoInfo::select('worker')->distinct()->get(); // distinctを追加することで重複を排除
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
        return AutoInfo::upsert(
            [$data], 
            ['id'],  // 一意キーとして使用するカラム名

            // ['worker']

            // RB納期だけ更新
            // processと比べ、基本的には増えることはなさそう
            ['rb_dead_line']
        );
    }
}