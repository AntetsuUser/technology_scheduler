<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\SessionRequest;

// DB（Models）
use App\Models\ExcelInfo;
use App\Models\ProcessInfo;
use App\Models\ProcessTaskDetails;
use App\Models\AutoInfo;
use App\Models\AutoTaskDetails;

// service
use App\Services\HomeService;
use App\Services\ProcessInfoService;
use App\Services\ProcessTaskDetailsService;
use App\Services\AutoInfoService;
use App\Services\AutoTaskDetailsService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class ListController extends Controller
{ 
    // 工程
    protected $_processInfoService;
    protected $_processTaskDetailsService;

    // 自動化
    protected $_autoInfoService;
    protected $_autoTaskDetailsService;

    // __constructは1つしか使えないから、まとめて置く                                                          // 工程　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　// 自動化
    public function __construct(HomeService $homeService, ProcessInfoService $processInfoService, ProcessTaskDetailsService $processTaskDetailsService, AutoInfoService $autoInfoService, AutoTaskDetailsService $autoTaskDetailsService)
    {
        $this->_homeService = $homeService;

        // 工程
        $this->_processInfoService = $processInfoService;
        $this->_processTaskDetailsService = $processTaskDetailsService;

        // 自動化
        $this->_autoInfoService = $autoInfoService;
        $this->_autoTaskDetailsService = $autoTaskDetailsService;
    }

    //一覧画面
    public function list()
    {
        $listData = [];

        // それぞれのテーブルの中身を取得
        $ExcelInfo_all = $this->_homeService->excel_info_get();

        // 中身を取り出す。
        foreach ($ExcelInfo_all as $excelInfo)
        {
            // 工程、自動化の詳細はすべて「Excel_info」の id に紐づいている
            $excelInfo_id = $excelInfo->id;
            // ファイル名、シート名
            $fileName = $excelInfo->file_name;
            $sheetName = $excelInfo->sheet_name;
            $fileType = $excelInfo->file_type;

            // 加工 ===========================================================================================================
            if ($fileType == "process")
            {
                $processInfo_data = $this->_processInfoService->process_info_find_by_id($excelInfo_id);
                $processTaskDetails_data = $this->_processTaskDetailsService->process_task_datails_find_by_id($excelInfo_id);
    
                // 各ExcelInfoに対して初期化
                if (!isset($listData[$fileName][$sheetName]))
                {
                    $listData[$fileName][$sheetName] = [
                        "情報" => [
                            "処理" => $fileType,
                            "ファイル名" => $fileName,
                            "シート名" => $sheetName,
                            "製造課" => null, // 初期値を設定
                            "機種" => [],
                            "設備番号" => [],
                            "品目" => null, // 初期値を設定
                            "品番" => null, // 初期値を設定
                            "担当者" => [],
                        ],
                        "リスト" => [],
                    ];
                }

                // 工程情報 -------------------------------------------------------------------------------------------------------- 
                foreach ($processInfo_data as $processinfo)
                {
                    // excel_info_idが一致する場合にデータを追加
                    $listData[$fileName][$sheetName]["情報"]["製造課"] =  $processinfo->department;
                    $listData[$fileName][$sheetName]["情報"]["機種"][] = $processinfo->equipment_category;
                    $listData[$fileName][$sheetName]["情報"]["設備番号"][] = $processinfo->equipment_number;
                    $listData[$fileName][$sheetName]["情報"]["品目"] = $processinfo->processing_item;
                    $listData[$fileName][$sheetName]["情報"]["品番"] = $processinfo->processing_number;
                    $listData[$fileName][$sheetName]["情報"]["担当者"][] = $processinfo->worker;
                }

                // 工程リスト -----------------------------------------------------------------------------------------------------
                foreach ($processTaskDetails_data as $processtaskdetails)
                {
                    // 番号、項目、詳細
                    $taskNumber = $processtaskdetails->task_number;
                    $item = $processtaskdetails->item;
                    $details = $processtaskdetails->details;

                    // 項目が既に存在するか確認
                    if (!isset($listData[$fileName][$sheetName]["リスト"][$taskNumber][$item])) {
                        $listData[$fileName][$sheetName]["リスト"][$taskNumber][$item] = [];
                    }
        
                    // 詳細情報を追加
                    $listData[$fileName][$sheetName]["リスト"][$taskNumber][$item][$details] = [
                        "予定日" => $processtaskdetails->plan_day,
                        "着手日" => $processtaskdetails->start_day,
                        "完了日" => $processtaskdetails->complete_day,
                        "納期" => $processtaskdetails->dead_line,
                        "作成日" => $processtaskdetails->created_at
                    ];
                }
            }
            else
            {
                // 自動化 ===========================================================================================================
                $autoInfo_data = $this->_autoInfoService->auto_info_find_by_id($excelInfo_id);
                $autoTaskDetails_data = $this->_autoTaskDetailsService->auto_task_datails_find_by_id($excelInfo_id);

                // 各ExcelInfoに対して初期化
                if (!isset($listData[$fileName][$sheetName]))
                {
                    $listData[$fileName][$sheetName] = [
                        "情報" => [
                            "処理" => $fileType,
                            "ファイル名" => $fileName,
                            "シート名" => $sheetName,
                            "製造課" => null, // 初期値を設定
                            "区分" => null,
                            "工程" => null,
                            "設備No" => null,
                            "RB納期" => null,
                            "担当者" => [],
                        ],
                        "リスト" => [],
                    ];
                }

                // 自動化情報 -----------------------------------------------------------------------------------------------------------
                foreach ($autoInfo_data as $autoinfo)
                {
                    // excel_info_idが一致する場合にデータを追加
                    // 行番号は使わない予定なので省いている
                    $listData[$fileName][$sheetName]["情報"]["製造課"] =  $autoinfo->department;
                    $listData[$fileName][$sheetName]["情報"]["区分"] = $autoinfo->auto_item;
                    $listData[$fileName][$sheetName]["情報"]["工程"] = $autoinfo->auto_process;
                    $listData[$fileName][$sheetName]["情報"]["設備No"] = $autoinfo->equipment_number;
                    $listData[$fileName][$sheetName]["情報"]["RB納期"] = $autoinfo->rb_dead_line;
                    $listData[$fileName][$sheetName]["情報"]["担当者"][] = $autoinfo->worker;
                }

                // 自動化リスト ---------------------------------------------------------------------------------------------------------
                foreach ($autoTaskDetails_data as $autotaskdetails)
                {
                    // 番号、項目、詳細
                    $taskNumber = $autotaskdetails->task_number;
                    $item = $autotaskdetails->item;
                    $details = $autotaskdetails->details;

                    // 項目が既に存在するか確認
                    if (!isset($listData[$fileName][$sheetName]["リスト"][$taskNumber][$item])) {
                        $listData[$fileName][$sheetName]["リスト"][$taskNumber][$item] = [];
                    }
        
                    // 詳細情報を追加
                    $listData[$fileName][$sheetName]["リスト"][$taskNumber][$item][$details] = [
                        "予定日" => $autotaskdetails->plan_day,
                        "着手日" => $autotaskdetails->start_day,
                        "完了日" => $autotaskdetails->complete_day,
                        "納期" => $autotaskdetails->dead_line,
                        "担当者" => $autotaskdetails->worker
                    ];
                }
            }
        }

        // dd($listData);

        $data = []; // 最終的な配列を格納する
        $whichProcess = "";

        // JSONデータをループ処理
        foreach ($listData as $fileName => $sheets) 
        {
            foreach ($sheets as $sheetName => $sheetData) 
            {   
                foreach ($sheetData["リスト"] as $taskNumber => $tasks) 
                {
                    foreach ($tasks as $item => $details) 
                    {
                        foreach ($details as $detailName => $dates) 
                        {
                            if ($sheetData["情報"]["処理"] == "process")
                            {
                                $whichProcess = "加工";
                            }
                            else
                            {
                                $whichProcess = "自動化";
                            }

                            $data[] = [
                                $whichProcess,
                                $fileName,
                                $sheetName,
                                $item,
                                $detailName,
                                $dates["予定日"] ?? null,
                                $dates["着手日"] ?? null,
                                $dates["完了日"] ?? null,
                                $dates["納期"] ?? null,
                                $dates["作成日"] ?? null
                            ];
                        }
                    }
                }
            }
        }


        // 結果を確認
        // dd($data);

        // ヘッダー
        $headers = ['No.','ファイル名','シート名','製造課','NC','M/C','加工品番','担当者','工程番号','項目','詳細','予定日','着手日','完了日','納期','予定日','実績','備考'];
        $headers = ['処理', 'ファイル名', 'シート名', '項目' ,'詳細', '予定日', '着手日', '完了日', '納期', '作成日'];
        
        return view('list.list', compact('headers', 'data'));
    }
}
