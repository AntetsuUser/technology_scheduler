<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\ScheduleController;    // スケジュールコントローラー
use App\Http\Controllers\ListController;        // リストコントローラー
use App\Http\Controllers\ExcelReadController;

use App\Http\Controllers\ModalController;       // モーダル

use App\Http\Controllers\WebSocketController;
/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    // return view('welcome');
    return view('index');
});

// Route::get('/test_ws', function () {
//     return view('ws_test');
// });

// ws受信時の処理
Route::controller(WebSocketController::class)->group(function ()
{
    // 工程 更新処理開始
    Route::get('/update/message', 'updateMessage')->name('update.message');      

    // websocket開始（工程）
    Route::post('/update/process', 'updateProcess')->name('update.process');

    // websocket開始（自動）
    Route::post('/update/auto', 'updateAuto')->name('update.auto');
    
    // DB登録
    Route::post('/update/store', 'store')->name('update.store');

});

// ws検証用
Route::prefix('ws')->controller(ExcelReadController::class)->group( function() 
{
    Route::get('/', 'test')->name('test');
                            // ->middleware('check.updating');

    // Route::post('/process_update_start', 'process_update_start')->name('process_update_start');
    // Route::post('/process_update_complete', 'process_update_complete')->name('process_update_complete');
});

// スケジュール部分
Route::prefix('schedule')->controller(ScheduleController::class)->group( function() 
{
    // 期間入力
    Route::get('/', 'item')->name('item');

    // 加工 =================================================================================================
    Route::prefix('process')->group(function() 
    {
        // 表示
        // Route::post('/', 'processing')->name('schedule.processing');
        Route::get('/', 'process')->name('schedule.process');

        // Route::get('/worker', 'process_worker')->name('schedule.process_worker');
        Route::post('/worker', 'process_worker')->name('schedule.process_worker');

        // 削除確認画面
        Route::post('/comfirm', 'process_comfirm')->name('schedule.process_confirm');
        
        // 削除
        Route::post('/delete', 'process_delete')->name('schedule.process_delete');
    });

    // 自動化
    Route::prefix('auto')->group(function() 
    {
        // 表示
        // Route::post('/', 'auto')->name('schedule.auto');
        Route::get('/', 'auto')->name('schedule.auto');

        // 削除確認画面
        Route::post('/comfirm', 'auto_confirm')->name('schedule.auto_confirm');

        // 削除
        Route::post('/delete', 'auto_delete')->name('schedule.auto_delete');
    });

    
    // /schedule/auto/
    // Route::get('/auto', 'auto')->name('schedule.auto');
    Route::post('/auto', 'auto')->name('schedule.auto');
});


// 一覧
Route::get('/list', [ListController::class, 'list'])->name('list.list');

