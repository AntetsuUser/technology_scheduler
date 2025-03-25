<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Illuminate\Support\Facades\Storage;

// phpで動くwebsocket
use WebSocket\Client;

// DB（Models）
use App\Models\ExcelInfo;
use App\Models\ProcessInfo;
use App\Models\AutoInfo;

// service
// 更新しているかどうかを管理
use App\Services\UpdateManagementService;
use App\Services\HomeService;
use App\Repositories\HomeRepository;

// 工程
use App\Services\ProcessInfoService;
use App\Services\ProcessTaskDetailsService;
use App\Repositories\ProcessInfoRepository;
use App\Repositories\ProcessTaskDetailsRepository;

// 自動化
use App\Services\AutoInfoService;
use App\Services\AutoTaskDetailsService;
use App\Repositories\AutoInfoRepository;
use App\Repositories\AutoTaskDetailsRepository;

class WebSocketController extends Controller
{
    // サービスクラスとの紐付け
    protected $_updateManagementService;
    protected $_homeService;

    // 工程
    protected $_processInfoService;
    protected $_processTaskDetailsService;

    // 自動化
    protected $_autoInfoService;
    protected $_autoTaskDetailsService;

    // __constructは1つしか使えないから、まとめて置く                                                          // 工程　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　　// 自動化
    public function __construct(UpdateManagementService $updateManagementService, HomeService $homeService, ProcessInfoService $processInfoService, ProcessTaskDetailsService $processTaskDetailsService, AutoInfoService $autoInfoService, AutoTaskDetailsService $autoTaskDetailsService)
    {
        $this->_updateManagementService = $updateManagementService;
        $this->_homeService = $homeService;

        // 工程
        $this->_processInfoService = $processInfoService;
        $this->_processTaskDetailsService = $processTaskDetailsService;

        // 自動化
        $this->_autoInfoService = $autoInfoService;
        $this->_autoTaskDetailsService = $autoTaskDetailsService;
    }

    // 更新（工程）
    public function updateProcess(Request $request)
    {        
        // アップデートが被らないか確認
        $is_update_process = session()->get('process_update');
        session()->forget('process_update');
        $is_update_process = session()->get('process_update');
        
        // それぞれのテーブルの中身を取得
        $ExcelInfo_all = $this->_processInfoService->excel_info_process_get();
        $ProcessInfo_all = $this->_processInfoService->process_info_get();
        $ProcessTaskDetails_all = $this->_processTaskDetailsService->process_task_details_get();

        // 状態の取得：「process」
        $action = $request->action;

        // 選択された開始日と終了日の取得
        $params = $request->only([
            'start_date',
            'end_date'
        ]);

        // 複数人が同時にアップデートしているか
        if ($is_update_process == "true")
        {
            // return redirect('/ws')->withErrors(['error' => 'wswsws更新中です。しばらくお待ちください。']);
            dd("更新中");
            return redirect('schedule/' . $action )->withErrors(['error' => '更新中です。しばらくお待ちください。']);         
        }
        else
        {
            try
            {
                // python/ws_server.py に接続
                // 60秒のタイムアウトを設定しておく ← それでも切れる場合は秒数を伸ばす。
                $client = new Client("ws://192.168.3.96:6789", ['timeout' => 60]);
                
                // processを呼び出す
                $client->text("process");
                
                // 更新しているかどうかのフラグ
                session()->put('process_update', true);
                $data['id'] = 1;
                $data['process_update'] = true;
                $this->_updateManagementService->upsert($data);

                $res = null; // 初期値を設定
                
                // json受け取り
                $res = $client->receive();

                // 接続を閉じる
                $client->close();
            } 
            catch (ConnectionException $e) 
            {
                // 接続エラー
                // return redirect('/ws')->withErrors(['error' => 'サーバに接続出来ませんでした。']);
                return redirect('schedule/' . $action )->withErrors(['error' => 'サーバに接続出来ませんでした。']);
            } 
            catch (\Exception $e) 
            {
                // その他のエラー
                // return redirect('/ws')->withErrors(['error' => '更新に失敗しました。']);
                // エラーメッセージに詳細を追加してリダイレクト
                // dd($e);

                return redirect('schedule.process_update')->withErrors(['error' => '更新に失敗しました（工程）: ' . $e->getMessage()]);
            }
            finally
            {
                // 更新が終わった処理
                session()->put('process_update', false);
                $data['id'] = 1;
                $data['process_update'] = false;
                $this->_updateManagementService->upsert($data);

                // WebSocketが閉じられていない場合、ここで安全に閉じる
                // if (isset($client)) 
                // {
                //     // dump("閉じられてない");
                //     $client->close(); // 接続が開かれている場合は確実に閉じる
                // }
                // else
                // {
                //     dump("閉じられてる");
                // }

                if (isset($client) && $client->isConnected()) 
                {
                    $client->close();
                }
                else
                {
                    // dump("閉じられてるか接続されていない");
                }
                

                // JSONファイルのパスを指定
                $jsonFilePath = '/home/pi/Desktop/scheduler/technology_scheduler/process.json'; // storage/app/data.jsonにある場合

                // JSONファイルを読み込む
                if (file_exists($jsonFilePath)) 
                {
                    $excelJson = file_get_contents($jsonFilePath);

                    // JSONデータを配列にデコード
                    // DBへの書き込み処理用
                    $json = json_decode($excelJson, true);
                    // compactでviewに送る用
                    $jsonData = json_decode($excelJson, true);
                }
                else 
                {
                    $error = json(['error' => 'File not found.'], 404);
                    dd($error);
                }

                // 初期化
                $excel_info_id = 0;

                // ExcelInfoのIDでの検索のときに使う（ExcelInfoの中身をすべて取得）
                $ExcelInfo_all = $this->_processInfoService->excel_info_process_get();

                // resが返ってきていたら（データがあったら）
                if ($json != null)
                {
                    foreach ($json as $file => $sheets)
                    {
                        foreach ($sheets as $sheetName => $sheetData)
                        {
                            // excel_infoへ書き込むデータ
                            $excelInfo = [
                                'id' => null,

                                'file_name' => $file, // file_nameフィールドに値を提供
                                'sheet_name' => $sheetName,

                                // // 完了判定の書き込み 
                                'complate_state' => $sheetData['完了判定'],

                                // update_processの中なので直接指定している
                                'file_type' => 'process'
                            ];

                            // 一応初期化
                            $id = null;

                            // DBにデータがあるか判定用
                            $isDiscovery = false;

                            // DBに同じデータがあるか
                            foreach ($ExcelInfo_all as $excel_info) 
                            {
                                // 書き込もうとしているファイル名、シート名と一緒のものはあるか
                                if ($excel_info->file_name == $file && $excel_info->sheet_name == $sheetName) 
                                {                              
                                    $excel_info_id = $excel_info->id;
                                    $isDiscovery = true;
                                }
                            }

                            // ない場合は「0」とし新しいデータ
                            if ($isDiscovery != true)
                            {
                                $excel_info_id = 0;
                            }

                            // DB : ExcelInfoに書き込み
                            $this->_homeService->upsert($excelInfo, $id, $excel_info_id);

                            // ====================================================================================================

                            // process_info,process_task_detailsの処理
                            foreach ($sheetData as $sheet_key => $item)
                            {
                                // データの種類判別 : 「工程情報」「工程リスト」「完了判定」
                                if ($sheet_key == "工程情報")
                                {
                                    // 語尾に ~s が付いているのは、複数あって配列で使うため
                                    $file_name = $item['ファイル名'];
                                    $sheet_name = $item['シート名'];
                                    $row_numbers = $item['行番号'];
                                    $department = $item['製造課'];
                                    $processing_item = $item['品目'];
                                    $processing_number = $item['品番'];
                                    $equipment_categories = $item['機種'];
                                    // $equipment_numbers = $item['設備No'];
                                    $equipment_numbers = $item['設備番号'];         // autoは['設備Noでやっている']
                                    $workers = $item['担当者'];

                                    // 中身分 / 機種の数量で判定（データの総数と一緒だから）
                                    for($i = 0; $i < count($equipment_categories); $i++)
                                    {
                                        // 行番号、機種、設備番号、担当者（複数あるもの）
                                        $row_number = $row_numbers[$i];
                                        $equipment_category = $equipment_categories[$i];
                                        $equipment_number = $equipment_numbers[$i];
                                        $worker = $workers[$i];

                                        // process_infoへ書き込むデータ
                                        $processInfo = [
                                            'id' => null,
                                            'excel_info_id' => null,
                                            'row_number' => $row_number,
                                            'department' => $department,
                                            'processing_item' => $processing_item,
                                            'processing_number' => $processing_number,
                                            'equipment_category' => $equipment_category,
                                            'equipment_number' => $equipment_number,
                                            'worker' => $worker
                                        ];

                                        // 判定の為（要らないかも）
                                        $excelInfo = [
                                            'file_name' => $file_name,
                                            'sheet_name' => $sheet_name,
                                        ];

                                        // 一応初期化
                                        $id = null;

                                        // DBに対応する値があるか確認（あれば更新 : 途中で変わりそうなのは名前だけ？）
                                        foreach ($ProcessInfo_all as $processInfoItem)
                                        {
                                            // 品目と機種だけは、必須項目にしておく
                                            if ($processInfoItem->excel_info_id == $excel_info_id &&
                                                    // $processInfoItem->department == $department &&                 # 製造課
                                                    // $processInfoItem->processing_item == $processing_item &&       # 品目
                                                    // $processInfoItem->processing_number == $processing_number &&   # 品番
                                                    // $processInfoItem->equipment_category == $equipment_category && # 機種
                                                    // $processInfoItem->equipment_number == $equipment_number        # 設備番号

                                                    // $processInfoItem->department == $department &&                 # 製造課
                                                    $processInfoItem->processing_item == $processing_item &&       # 品目
                                                    // $processInfoItem->processing_number == $processing_number &&   # 品番
                                                    $processInfoItem->equipment_category == $equipment_category # 機種
                                                    // $processInfoItem->equipment_number == $equipment_number        # 設備番号
                                                ) 
                                            {
                                                $processInfo['id'] = $processInfoItem->id;
                                                break;
                                            }
                                        }
                                        
                                        // DB : ProcessInfoに書き込み
                                        $this->_processInfoService->upsert($processInfo, $id, $excelInfo);
                                    }
                                }

                                // ====================================================================================================

                                elseif ($sheet_key == "工程リスト")
                                {
                                    foreach($item as $key => $process)
                                    {
                                        // 詳細分
                                        foreach($process as $process_key => $detail)
                                        {
                                            foreach($detail as $detail_key => $process_data)
                                            {
                                                // それぞれの日付（矢印部分）
                                                $plan_day = !empty(trim($process_data['予定日'])) ? $process_data['予定日'] : null;
                                                $start_day = !empty(trim($process_data['着手日'])) ? $process_data['着手日'] : null;
                                                $complete_day = !empty(trim($process_data['完了日'])) ? $process_data['完了日'] : null;
                                                $dead_line = !empty(trim($process_data['納期'])) ? $process_data['納期'] : null;

                                                // 担当者（追加）
                                                $detail_worker = !empty(trim($process_data['担当者'])) ? $process_data['担当者'] : null;

                                                $row_number = !empty(trim($process_data['行番号'])) ? $process_data['行番号'] : null;

                                                // process_task_detailsに書き込むデータ
                                                $processTask = [
                                                    'id' => null,
                                                    'excel_info_id' => null,
                                                    'row_number' => $row_number,
                                                    'task_number' => $key,
                                                    'item' => $process_key,
                                                    'details' => $detail_key,
                                                    'plan_day' => $plan_day,
                                                    'start_day' => $start_day,
                                                    'complete_day' => $complete_day,
                                                    'dead_line' => $dead_line,
                                                    'worker' => $detail_worker
                                                ];

                                                // 判定の為（要らないかも）
                                                $excelInfo = [
                                                    'file_name' => $file_name,
                                                    'sheet_name' => $sheet_name,
                                                ];

                                                // 一応初期化
                                                $id = null;

                                                // DBに対応する値があるか確認（あれば更新）
                                                foreach ($ProcessTaskDetails_all as $taskDetail) 
                                                {
                                                    // 項目、詳細は必須項目（書き換えられない）
                                                    if ($taskDetail->excel_info_id == $excel_info_id &&
                                                            $taskDetail->row_number == $row_number &&
                                                            $taskDetail->item == $process_key &&
                                                            $taskDetail->details == $detail_key
                                                        ) 
                                                    {
                                                        $processTask['id'] = $taskDetail->id;
                                                        break;
                                                    }
                                                }

                                                // DB : ProcessTaskDetailsに書き込み
                                                $this->_processTaskDetailsService->upsert($processTask, $id, $excelInfo);
                                            }
                                        }
                                    }
                                }
                                else // 完了判定部分（特に処理ないので何も書かない）
                                {}
                            }
                        }
                    }
                }
                else
                {
                    dump("ws通信が上手く出来ていません");
                }
                
                // アップデート確認画面へ
                return view('schedule.process_update', compact('params','action','jsonData'))->with('flash_message', '更新が完了しました');
            }
        }
    }

    // 更新（自動化）
    public function updateAuto(Request $request)
    {
        // アップデートが被らないか確認
        $is_update_auto = session()->get('auto_update');
        session()->forget('auto_update');
        $is_update_auto = session()->get('auto_update');
        
        // それぞれのテーブルの中身を取得
        $ExcelInfo_all = $this->_autoInfoService->excel_info_auto_get();
        $AutoInfo_all = $this->_autoInfoService->auto_info_get();
        $AutoTaskDetails_all = $this->_autoTaskDetailsService->auto_task_details_get();

        // 状態の取得：「auto」
        $action = $request->action;

        // 選択された開始日と終了日の取得
        $params = $request->only([
            'start_date',
            'end_date'
        ]);
        
        // 複数人が同時にアップデートしているか
        if ($is_update_auto == "true")
        {
            // return redirect('/ws')->withErrors(['error' => 'wswsws更新中です。しばらくお待ちください。']);
            dd("更新中");
            return redirect('schedule/' . $action )->withErrors(['error' => '更新中です。しばらくお待ちください。']);         
        }
        else
        {
            try
            {
                // python/ws_server.py に接続
                // 60秒のタイムアウトを設定しておく ← それでも切れる場合は秒数を伸ばす。
                $client = new Client("ws://192.168.3.96:6789", ['timeout' => 60]);
                
                // ws側に「auto」と送り、対応した処理をさせる
                $client->text("auto");
                
                // 更新しているかどうかのフラグ
                session()->put('auto_update', true);
                $data['id'] = 1;
                $data['auto_update'] = true;

                // DBの値書き換え
                // $this->_updateManagementService->upsert($data);
                // dump("<p>更新中です。しばらくお待ちください。</p>");

                $res = null; // 初期値を設定
                
                // json受け取り
                $res = $client->receive();

                // 接続を閉じる
                $client->close();
            } 
            catch (ConnectionException $e) 
            {
                // 接続エラー
                // return redirect('/ws')->withErrors(['error' => 'サーバに接続出来ませんでした。']);
                // dd("接続エラー");
                return redirect('schedule/' . $action )->withErrors(['error' => 'サーバに接続出来ませんでした。']);
            }
            catch (\Exception $e) 
            {
                // その他のエラー
                // return redirect('/ws')->withErrors(['error' => '更新に失敗しました。']);
                // エラーメッセージに詳細を追加してリダイレクト
                // dd($e);

                return redirect('schedule.auto_update')->withErrors(['error' => '更新に失敗しました（自動化）: ' . $e->getMessage()]);
            }

            // その他（メインの処理）
            finally
            {
                // 更新が終わった処理
                session()->put('auto_update', false);
                $data['id'] = 1;
                $data['auto_update'] = false;
                // $this->_updateManagementService->upsert($data);

                // まだ接続されているのなら、閉じる
                if (isset($client) && $client->isConnected()) 
                {
                    $client->close();
                }
                else
                {
                    // dump("閉じられてるか接続されていない");
                }

                // JSONファイルのパスを指定
                $jsonFilePath = '/home/pi/Desktop/scheduler/technology_scheduler/auto.json';

                // JSONファイルを読み込む
                if (file_exists($jsonFilePath)) 
                {
                    $excelJson = file_get_contents($jsonFilePath);

                    // JSONデータを配列にデコード
                    // compact用
                    $jsonData = json_decode($excelJson, true);
                    // DBへの書き込み処理用
                    $json = json_decode($excelJson, true);
                }
                else 
                {
                    $error = json(['error' => 'File not found.'], 404);
                    dd($error);
                }

                // 判定で使う変数の初期化
                $excel_info_id = 0;

                // ExcelInfoのIDでの検索のときに使う（ExcelInfoの中身をすべて取得）
                $ExcelInfo_all = $this->_autoInfoService->excel_info_auto_get();

                // resが返ってきていたら（データがあったら）
                if ($json != null)
                {
                    foreach ($json as $file => $sheets)
                    {
                        foreach ($sheets as $sheetName => $sheetData)
                        {
                            // excel_infoへ書き込むデータ
                            $excelInfo = [
                                'id' => null,

                                'file_name' => $file, // file_nameフィールドに値を提供
                                'sheet_name' => $sheetName,

                                // // 完了判定の書き込み 
                                'complate_state' => $sheetData['完了判定'],

                                // update_processの中なので直接指定している
                                'file_type' => 'auto'
                            ];

                            // 一応初期化
                            $id = null;

                            // DBにデータがあるか判定
                            $isDiscovery = false;

                            foreach ($ExcelInfo_all as $excel_info) 
                            {
                                // DB内に、書き込もうとしているファイル名、シート名と一緒のものはあるか
                                if ($excel_info->file_name == $file && $excel_info->sheet_name == $sheetName) 
                                {                              
                                    $excel_info_id = $excel_info->id;
                                    $isDiscovery = true;
                                }
                            }

                            // DBに対応するデータがない場合は「0」を格納し、新しいデータとする
                            if ($isDiscovery != true)
                            {
                                $excel_info_id = 0;
                            }

                            // DB : ExcelInfoに書き込み
                            $this->_homeService->upsert($excelInfo, $id, $excel_info_id);

                            // 自動化情報（auto_info）------------------------------------------------------------------------------------
                            foreach ($sheetData as $sheet_key => $item)
                            {
                                // データの種類判別
                                if ($sheet_key == "自動化情報")
                                {
                                    // 語尾に ~s が付いているのは配列で使うため
                                    $file_name = $item['ファイル名'];
                                    $sheet_name = $item['シート名'];
                                    // $row_numbers = $item['行番号'];
                                    $department = $item['製造課'];
                                    $auto_item = $item['区分'];
                                    $auto_process = $item['工程'];
                                    $equipment_number = $item['設備No'];
                                    $rb_dead_line = $item['RB納期'];
                                    $workers = $item['担当者'];

                                    // 中身分 / 機種の数量で判定（データの総数と一緒だから）
                                    for($i = 0; $i < count($workers); $i++)
                                    {
                                        // 行番号、機種、設備番号、担当者
                                        // $row_number = $row_numbers[$i];
                                        $worker = $workers[$i];

                                        // DBへ送るデータ
                                        $autoInfo = [
                                            'id' => null,
                                            'excel_info_id' => null,
                                            // 'row_number' => $row_number,
                                            'department' => $department,
                                            'auto_item' => $auto_item,
                                            'auto_process' => $auto_process,
                                            'equipment_number' => $equipment_number,
                                            'rb_dead_line' => $rb_dead_line,
                                            'worker' => $worker
                                        ];
                                        
                                        // 判定の為（要らないかも）
                                        $excelInfo = [
                                            'file_name' => $file_name,
                                            'sheet_name' => $sheet_name,
                                        ];

                                        // 初期化
                                        $id = null;

                                         // DBに対応する値があるか確認（あれば更新 : 途中で変わりそうなのは名前だけ？）
                                        foreach ($AutoInfo_all as $autoInfoItem)
                                        {
                                            if ($autoInfoItem->excel_info_id == $excel_info_id &&
                                                    // $processInfoItem->department == $department &&                 # 製造課
                                                    // $processInfoItem->department == $row_number &&                 # 製造課
                                                    $autoInfoItem->worker == $worker                           # 担当者
                                                ) 
                                            {
                                                $autoInfo['id'] = $autoInfoItem->id;
                                                break;
                                            }
                                        }

                                        // DB : ProcessInfoに書き込み
                                        $this->_autoInfoService->upsert($autoInfo, $id, $excelInfo);

                                        // dump($autoInfo);
                                    }
                                }

                                // ====================================================================================================

                                // 自動化リスト（auto_task_details）
                                elseif ($sheet_key == "自動化リスト")
                                {
                                    foreach($item as $key => $auto)
                                    {
                                        // 詳細分
                                        foreach($auto as $auto_key => $detail)
                                        {
                                            // それぞれの日付（矢印部分）
                                            foreach($detail as $detail_key => $auto_data)
                                            {
                                                // dump($auto_data);

                                                $plan_day = !empty(trim($auto_data['予定日'])) ? $auto_data['予定日'] : null;
                                                $start_day = !empty(trim($auto_data['着手日'])) ? $auto_data['着手日'] : null;
                                                $complete_day = !empty(trim($auto_data['完了日'])) ? $auto_data['完了日'] : null;
                                                $dead_line = !empty(trim($auto_data['納期'])) ? $auto_data['納期'] : null;
                                                $worker = !empty(trim($auto_data['担当者'])) ? $auto_data['担当者'] : null;
                                                $row_number = !empty(trim($auto_data['行番号'])) ? $auto_data['行番号'] : null;

                                                // DBへ書き込みデータ
                                                $autoTask = [
                                                    'id' => null,
                                                    'excel_info_id' => null,
                                                    'row_number' => $row_number,
                                                    'task_number' => $key,
                                                    'item' => $auto_key,
                                                    'details' => $detail_key,
                                                    'plan_day' => $plan_day,
                                                    'start_day' => $start_day,
                                                    'complete_day' => $complete_day,
                                                    'dead_line' => $dead_line,
                                                    'worker' => $worker
                                                ];

                                                // 判定の為（要らないかも）
                                                $excelInfo = [
                                                    'file_name' => $file_name,
                                                    'sheet_name' => $sheet_name,
                                                ];

                                                // 初期化
                                                $id = null;

                                                // DBに対応する値があるか確認（あれば更新）
                                                foreach ($AutoTaskDetails_all as $taskDetail) 
                                                {
                                                    if ($taskDetail->excel_info_id == $excel_info_id &&
                                                            $taskDetail->row_number == $row_number &&
                                                            $taskDetail->item == $auto_key &&
                                                            $taskDetail->details == $detail_key
                                                        ) 
                                                    {
                                                        $autoTask['id'] = $taskDetail->id;
                                                        break;
                                                    }
                                                }

                                                // DB : AutoTaskDetailsに書き込み
                                                $this->_autoTaskDetailsService->upsert($autoTask, $id, $excelInfo);
                                            }
                                        }
                                    }
                                }
                                else                                        // 完了判定部分
                                {}
                            }
                        }
                    }
                }
                else
                {
                    // dump($json);
                    dump("ws通信が上手く出来ていません");
                }

                // アップデート画面に推移する
                return view('schedule.auto_update', compact('params','action','jsonData'))->with('flash_message', '更新が完了しました');
            }
        }
    }
}
