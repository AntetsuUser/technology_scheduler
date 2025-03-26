<?php

namespace App\Http\Controllers;

// request
use Illuminate\Http\Request;
use App\Http\Requests\PostRequest;
use App\Http\Requests\SessionRequest;

// DB（Models）
use App\Models\ExcelInfo;
use App\Models\ProcessInfo;
use App\Models\ProcessTaskDetails;
use App\Models\AutoInfo;
use App\Models\AutoTaskDetails;
use App\Models\Worker;

// service
use App\Services\HomeService;
use App\Services\ProcessInfoService;
use App\Services\ProcessTaskDetailsService;
use App\Services\AutoInfoService;
use App\Services\AutoTaskDetailsService;
use App\Services\WorkerService;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

// 日付
use Carbon\Carbon;

class ScheduleController extends Controller
{
    // サービスクラスとの紐付け
    protected $_homeService;
    protected $_processInfoService;
    protected $_processTaskDetailsService;
    protected $_autoInfoService;
    protected $_autoTaskDetailsService;
    protected $_workerService;
    
    public function __construct(HomeService $homeService, 
                                ProcessInfoService $processInfoService, 
                                ProcessTaskDetailsService $processTaskDetailsService,
                                AutoInfoService $autoInfoService, 
                                AutoTaskDetailsService $autoTaskDetailsService,
                                WorkerService $workerService
                            )
    {
        $this->_homeService = $homeService;
        $this->_processInfoService = $processInfoService;
        $this->_processTaskDetailsService = $processTaskDetailsService;
        $this->_autoInfoService = $autoInfoService;
        $this->_autoTaskDetailsService = $autoTaskDetailsService;
        $this->_workerService = $workerService;
    }
    
    // 日付選択画面 ===========================================================================================================
    public function item(Request $request)
    {
        // 加工か自動化の画面の判定
        $action = $request->query('action');

        return view('schedule.item', compact('action'));
    }

    // process => 加工画面（ルーティングで呼ばれてる）============================================================================
    public function process(PostRequest $request)
    {  
        // // itemからの取得（hidden）
        $action = $request->action;
        $params = $request->only(['start_date', 'end_date']);
    
        $now = Carbon::now(); // 時間設定（asia Tokyo）

        // それぞれのテーブルの中身を取得
        $ExcelInfo_all = $this->_processInfoService->excel_info_process_get();
        $ProcessInfo_all = $this->_processInfoService->process_info_get();
        $ProcessTaskDetails_all = $this->_processTaskDetailsService->process_task_details_get();

        // 担当者だけを取得する
        $get_workers = $this->_processInfoService->get_workers();

        // bladeに送るために、データを格納する配列
        $jsonData = [];

        // 中身を取り出す。
        foreach ($ExcelInfo_all as $excelInfo)
        {
            // ExcelInfoのID。ProcessInfoとProcessTaskDetailの「excel_info_id」と結びついている。
            $excelInfo_id = $excelInfo->id;

            // ファイル名、シート名
            $fileName = $excelInfo->file_name;
            $sheetName = $excelInfo->sheet_name;
            $complateState = $excelInfo->complate_state;
            
            // excelInfo_idを使って、対応したProcessInfoとProcessTaskDetails内のdataを取得
            $ProcessInfo_data = $this->_processInfoService->process_info_find_by_id($excelInfo_id);
            $ProcessTaskDetails_data = $this->_processTaskDetailsService->process_task_datails_find_by_id($excelInfo_id);

            // 各ExcelInfoに対して初期化
            if (!isset($jsonData[$fileName][$sheetName]))
            {
                $jsonData[$fileName][$sheetName] = [
                    "工程情報" => [
                        "ファイル名" => $fileName,
                        "シート名" => $sheetName,
                        "製造課" => null, // 初期値を設定
                        "機種" => [],
                        "設備番号" => [],
                        "品目" => null, // 初期値を設定
                        "品番" => null, // 初期値を設定
                        "担当者" => [],
                    ],
                    "工程リスト" => [],
                    "完了判定" => $complateState,  // まず「完了」と仮定
                    "id" => $excelInfo_id
                ];
            }

            // 工程情報 -------------------------------------------------------------------------------------------------------- 
            foreach ($ProcessInfo_data as $processinfo)
            {
                // excel_info_idが一致する場合にデータを追加
                $jsonData[$fileName][$sheetName]["工程情報"]["製造課"] =  $processinfo->department;
                $jsonData[$fileName][$sheetName]["工程情報"]["機種"][] = $processinfo->equipment_category;
                $jsonData[$fileName][$sheetName]["工程情報"]["設備番号"][] = $processinfo->equipment_number;
                $jsonData[$fileName][$sheetName]["工程情報"]["品目"] = $processinfo->processing_item;
                $jsonData[$fileName][$sheetName]["工程情報"]["品番"] = $processinfo->processing_number;
                $jsonData[$fileName][$sheetName]["工程情報"]["担当者"][] = $processinfo->worker;
            }

            // 工程リスト -----------------------------------------------------------------------------------------------------
            foreach ($ProcessTaskDetails_data as $processtaskdetails)
            {
                // 番号、項目、詳細
                $taskNumber = $processtaskdetails->task_number;
                $item = $processtaskdetails->item;
                $details = $processtaskdetails->details;

                // 項目が既に存在するか確認
                if (!isset($jsonData[$fileName][$sheetName]["工程リスト"][$taskNumber][$item])) {
                    $jsonData[$fileName][$sheetName]["工程リスト"][$taskNumber][$item] = [];
                }
    
                // 詳細情報を追加
                $jsonData[$fileName][$sheetName]["工程リスト"][$taskNumber][$item][$details] = [
                    "予定日" => $processtaskdetails->plan_day,
                    "着手日" => $processtaskdetails->start_day,
                    "完了日" => $processtaskdetails->complete_day,
                    "納期" => $processtaskdetails->dead_line,
                    "担当者" => $processtaskdetails->worker
                ];
            }
        }
        
        // DBから取得すると順番がバラバラになる為、ソートして並び替える ------------------------------------------------------------
        foreach ($jsonData as $fileName => &$sheets) 
        {
            foreach ($sheets as $sheetName => &$data) 
            {
                ksort($data["工程リスト"]);
            }
        }

        // 表示させたい jsonData（jsでの判定で使う）, どの処理か判定の action（この場合は process, その他データの params） 
        return view('schedule.process', compact('params','jsonData', 'get_workers', 'action'));
    }

    // 削除確認画面 ==============================================================================================================
    function process_comfirm(SessionRequest $request)
    {
        // チェックボックスの値を取得
        $checkedValues = $request->input('deletes', []);

        // $action = $request->action;
        $params = $request->only(['start_date', 'end_date']);

        // チェックされた ID に基づいて Excel 情報を取得
        $ExcelInfo_data = [];
        foreach ($checkedValues as $id) 
        {
            // $id に基づいてデータを取得
            // $ExcelInfo_data[] = $this->_processInfoService->excel_info_find_by_id($id);
            $ExcelInfo_data[] = $this->_homeService->excel_info_find_by_id($id);
        }

        return view('schedule.process_confirm', compact('ExcelInfo_data', 'checkedValues', 'params'));
    }

    // 工程の削除 =========================================================================================================
    function process_delete(Request $request)
    {
        // チェックボックスの値を取得
        $checkedValues = $request->input('deletes', []);

        // URL作成の為に取得、設定
        $action = 'process';
        $token = csrf_token();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // チェックの入った id のテーブルを削除する
        if (!empty($checkedValues)) 
        {
            foreach ($checkedValues as $value) 
            {
                $this->_homeService->delete($value);
            }
        }
        else
        {
            return redirect()->route('schedule.process')->with('error', '削除する項目を選択してください。');
        }

        // リダイレクトさせるURLを生成（リダイレクト先の）
        $url = url('/schedule/process') . '?' . http_build_query([
            'action' => $action,
            '_token' => $token,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // URLでリダイレクト
        return redirect($url);
    }

    // 担当者の絞り込み =======================================================================================================
    public function process_worker(Request $request)
    {
        // // itemからの取得（hidden）
        $action = $request->action;
        $params = $request->only(['start_date', 'end_date']);
        // $params = [
        //     'start_date' => '2025/3/1',
        //     'end_date' => '2025/4/31'
        // ];
    
        $now = Carbon::now(); // 時間設定（asia Tokyo）

        // それぞれのテーブルの中身を取得
        $ExcelInfo_all = $this->_processInfoService->excel_info_process_get();
        $ProcessInfo_all = $this->_processInfoService->process_info_get();
        $ProcessTaskDetails_all = $this->_processTaskDetailsService->process_task_details_get();

        // 担当者だけを取得する
        $get_workers = $this->_processInfoService->get_workers();
        $filter_worker = $this->_processTaskDetailsService->get_filter_workers();

        // bladeに送るために、データを格納する配列
        $jsonData = [];
        $workerData = [];

        // 中身を取り出す。
        foreach ($ExcelInfo_all as $excelInfo)
        {
            // ExcelInfoのID。ProcessInfoとProcessTaskDetailの「excel_info_id」と結びついている。
            $excelInfo_id = $excelInfo->id;

            // ファイル名、シート名
            $fileName = $excelInfo->file_name;
            $sheetName = $excelInfo->sheet_name;
            $complateState = $excelInfo->complate_state;
            
            // excelInfo_idを使って、対応したProcessInfoとProcessTaskDetails内のdataを取得
            $ProcessInfo_data = $this->_processInfoService->process_info_find_by_id($excelInfo_id);
            $ProcessTaskDetails_data = $this->_processTaskDetailsService->process_task_datails_find_by_id($excelInfo_id);

            // 各ExcelInfoに対して初期化
            if (!isset($jsonData[$fileName][$sheetName]))
            {
                $jsonData[$fileName][$sheetName] = [
                    "工程情報" => [
                        "ファイル名" => $fileName,
                        "シート名" => $sheetName,
                        "製造課" => null, // 初期値を設定
                        "機種" => [],
                        "設備番号" => [],
                        "品目" => null, // 初期値を設定
                        "品番" => null, // 初期値を設定
                        "担当者" => [],
                    ],
                    "工程リスト" => [],
                    "完了判定" => $complateState,  // まず「完了」と仮定
                    "id" => $excelInfo_id
                ];
            }

            // 工程情報 -------------------------------------------------------------------------------------------------------- 
            foreach ($ProcessInfo_data as $processinfo)
            {
                // excel_info_idが一致する場合にデータを追加
                $jsonData[$fileName][$sheetName]["工程情報"]["製造課"] =  $processinfo->department;
                $jsonData[$fileName][$sheetName]["工程情報"]["機種"][] = $processinfo->equipment_category;
                $jsonData[$fileName][$sheetName]["工程情報"]["設備番号"][] = $processinfo->equipment_number;
                $jsonData[$fileName][$sheetName]["工程情報"]["品目"] = $processinfo->processing_item;
                $jsonData[$fileName][$sheetName]["工程情報"]["品番"] = $processinfo->processing_number;
                $jsonData[$fileName][$sheetName]["工程情報"]["担当者"][] = $processinfo->worker;
            }

            // 工程リスト -----------------------------------------------------------------------------------------------------
            foreach ($ProcessTaskDetails_data as $processtaskdetails)
            {
                // 番号、項目、詳細
                $taskNumber = $processtaskdetails->task_number;
                $item = $processtaskdetails->item;
                $details = $processtaskdetails->details;

                // 項目が既に存在するか確認
                if (!isset($jsonData[$fileName][$sheetName]["工程リスト"][$taskNumber][$item])) {
                    $jsonData[$fileName][$sheetName]["工程リスト"][$taskNumber][$item] = [];
                }
    
                // 詳細情報を追加
                $jsonData[$fileName][$sheetName]["工程リスト"][$taskNumber][$item][$details] = [
                    "予定日" => $processtaskdetails->plan_day,
                    "着手日" => $processtaskdetails->start_day,
                    "完了日" => $processtaskdetails->complete_day,
                    "納期" => $processtaskdetails->dead_line,
                    "担当者" => $processtaskdetails->worker
                ];
            }
        }
        
        // DBから取得すると順番がバラバラになる為、ソートして並び替える ------------------------------------------------------------
        foreach ($jsonData as $fileName => &$sheets) 
        {
            foreach ($sheets as $sheetName => &$data) 
            {
                ksort($data["工程リスト"]);
            }
        }

        $get_workers = $this->_processInfoService->get_workers();

        // 担当者基準での並び替え
        foreach ($ProcessTaskDetails_all as $processtaskdetails) 
        {
            $file_id = $processtaskdetails->excel_info_id;
            $worker = $processtaskdetails->worker;  // 担当者

            $taskNumber = $processtaskdetails->task_number; // 工程番号
            $item = $processtaskdetails->item;  // 項目
            $details = $processtaskdetails->details;  // 詳細
    
            // 担当者をキーに初期化
            if (!isset($workerData[$worker])) 
            {
                $workerData[$worker] = [];
            }
    
            // ファイルの種類をキーに初期化
            if (!isset($workerData[$worker][$file_id])) 
            {
                $workerData[$worker][$file_id] = [];
            }

            // 工程番号をキーに初期化
            if (!isset($workerData[$worker][$file_id][$taskNumber])) 
            {
                $workerData[$worker][$file_id][$taskNumber] = [];
            }
    
            // 項目をキーに初期化]
            if (!isset($workerData[$worker][$file_id][$taskNumber][$item])) {
                $workerData[$worker][$file_id][$taskNumber][$item] = [];
            }
    
            // 詳細情報を追加()
            $workerData[$worker][$file_id][$taskNumber][$item][$details] = [
                "予定日" => $processtaskdetails->plan_day,
                "着手日" => $processtaskdetails->start_day,
                "完了日" => $processtaskdetails->complete_day,
                "納期" => $processtaskdetails->dead_line
            ];
        }

        $get_workers =  $filter_worker;
        return view('schedule.process_worker', compact('params','jsonData', 'get_workers', 'action', 'workerData'));
    }

    // テスト画面 ==================================================================================================================
    function test(Request $request)
    {
        $id = $request->input('id');
        $this->_homeService->delete($id);

        return redirect()->route('schedule.test');
    }

    //自動画面 =====================================================================================================================
    public function auto(PostRequest $request)
    {
        // // itemからの取得（hidden）
        $action = $request->action;
        $params = $request->only(['start_date', 'end_date']);
    
        $now = Carbon::now(); // 時間設定（asia Tokyo）

        // それぞれのテーブルの中身を取得
        $ExcelInfo_all = $this->_autoInfoService->excel_info_auto_get();
        $AutoInfo_all = $this->_autoInfoService->auto_info_get();
        $AutoTaskDetails_all = $this->_autoTaskDetailsService->auto_task_details_get();
    
        // 担当者だけを取得する
        $get_workers = $this->_autoInfoService->get_workers();

        // bladeに送るために、データを格納する配列
        $jsonData = [];

        // 中身を取り出す。
        foreach ($ExcelInfo_all as $excelInfo)
        {
            // ExcelInfoのID。AutoInfoとAutoTaskDetailの「excel_info_id」と結びついている。
            $excelInfo_id = $excelInfo->id;

            // ファイル名、シート名
            $fileName = $excelInfo->file_name;
            $sheetName = $excelInfo->sheet_name;
            $complateState = $excelInfo->complate_state;
 
            // excelInfo_idを使って、対応したAutoInfoとAutoTaskDetails内のdataを取得
            $AutoInfo_data = $this->_autoInfoService->auto_info_find_by_id($excelInfo_id);
            $AutoTaskDetails_data = $this->_autoTaskDetailsService->auto_task_datails_find_by_id($excelInfo_id);

            // 各ExcelInfoに対して初期化
            if (!isset($jsonData[$fileName][$sheetName]))
            {
                $jsonData[$fileName][$sheetName] = [
                    "自動化情報" => [
                        "ファイル名" => $fileName,
                        "シート名" => $sheetName,
                        "製造課" => null, // 初期値を設定
                        "区分" => null,
                        "工程" => null,
                        "設備No" => null,
                        "RB納期" => null,
                        "担当者" => [],
                    ],
                    "自動化リスト" => [],
                    "完了判定" => $complateState,  // まず「完了」と仮定
                    "id" => $excelInfo_id
                ];
            }

            // 自動化情報 -----------------------------------------------------------------------------------------------------------
            foreach ($AutoInfo_data as $autoinfo)
            {
                // excel_info_idが一致する場合にデータを追加
                // 行番号は使わない予定なので省いている
                $jsonData[$fileName][$sheetName]["自動化情報"]["製造課"] =  $autoinfo->department;
                $jsonData[$fileName][$sheetName]["自動化情報"]["区分"] = $autoinfo->auto_item;
                $jsonData[$fileName][$sheetName]["自動化情報"]["工程"] = $autoinfo->auto_process;
                $jsonData[$fileName][$sheetName]["自動化情報"]["設備No"] = $autoinfo->equipment_number;
                $jsonData[$fileName][$sheetName]["自動化情報"]["RB納期"] = $autoinfo->rb_dead_line;
                $jsonData[$fileName][$sheetName]["自動化情報"]["担当者"][] = $autoinfo->worker;
            }

            // 自動化リスト ---------------------------------------------------------------------------------------------------------
            foreach ($AutoTaskDetails_data as $autotaskdetails)
            {
                // 番号、項目、詳細
                $taskNumber = $autotaskdetails->task_number;
                $item = $autotaskdetails->item;
                $details = $autotaskdetails->details;

                // 項目が既に存在するか確認
                if (!isset($jsonData[$fileName][$sheetName]["自動化リスト"][$taskNumber][$item])) {
                    $jsonData[$fileName][$sheetName]["自動化リスト"][$taskNumber][$item] = [];
                }
    
                // 詳細情報を追加
                $jsonData[$fileName][$sheetName]["自動化リスト"][$taskNumber][$item][$details] = [
                    "予定日" => $autotaskdetails->plan_day,
                    "着手日" => $autotaskdetails->start_day,
                    "完了日" => $autotaskdetails->complete_day,
                    "納期" => $autotaskdetails->dead_line,
                    "担当者" => $autotaskdetails->worker
                ];
            }
        }

        // DBから取得すると順番がバラバラになる為、ソートして並び替える ----------------------------------------------------------------
        foreach ($jsonData as $fileName => &$sheets) 
        {
            foreach ($sheets as $sheetName => &$data) 
            {
                ksort($data["自動化リスト"]);
            }
        }

        $action = $request->action;
        $params = $request->only(['start_date', 'end_date']);


        // compactで「worker->name」をbladeに渡してjsで処理
        $check_worker = $this->_workerService->worker_get();
        return view('schedule.auto', compact('params', 'jsonData', 'get_workers', 'action', 'check_worker'));
    }

    //
    function worker_check(SessionRequest $request)
    {
        $worker_get = $this->_workerService->worker_get();
        // dd($worker_get);
    }

    // 削除確認画面 ---------------------------------------------------------------------------------------------------------------
    function auto_confirm(SessionRequest $request)
    {
        // チェックボックスの値を取得
        $checkedValues = $request->input('deletes', []);

        // $action = $request->action;
        $params = $request->only(['start_date', 'end_date']);

        // チェックされた ID に基づいて Excel 情報を取得
        $ExcelInfo_data = [];
        foreach ($checkedValues as $id) 
        {
            // $id に基づいてデータを取得
            // $ExcelInfo_data[] = $this->_processInfoService->excel_info_find_by_id($id);
            $ExcelInfo_data[] = $this->_homeService->excel_info_find_by_id($id);
        }

        return view('schedule.auto_confirm', compact('ExcelInfo_data', 'checkedValues', 'params'));
    }

    // 工程の削除 -----------------------------------------------------------------------------------------------------------------
    function auto_delete(Request $request)
    {
        // チェックボックスの値を取得
        $checkedValues = $request->input('deletes', []);

        // URL作成の為に取得、設定
        $action = 'auto';
        $token = csrf_token();
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // チェックの入った id のテーブルを削除する
        if (!empty($checkedValues)) 
        {
            foreach ($checkedValues as $value) 
            {
                $this->_homeService->delete($value);
            }
        }
        else
        {
            return redirect()->route('schedule.auto')->with('error', '削除する項目を選択してください。');
        }

        // confirmを経由しない場合のリダイレクト
        // return redirect()->route('schedule.processing')->with('status', '削除完了');

        // リダイレクトさせるURLを生成（リダイレクト先の）
        $url = url('/schedule/auto') . '?' . http_build_query([
            'action' => $action,
            '_token' => $token,
            'start_date' => $startDate,
            'end_date' => $endDate,
        ]);

        // URLでリダイレクト
        return redirect($url);
    }
}
