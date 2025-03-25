<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Services\UpdateManagementService;

class ExcelReadController extends Controller
{
    protected $_updateManagementService;
    public function __construct(UpdateManagementService $updateManagementService)
    {
        $this->_updateManagementService = $updateManagementService;
    }

    // wsテスト画面
    public function test()
    {
        // $process_update = session()->get('updating');
        // dd($process_update);

        $update_session = $this->_updateManagementService->select();

        $is_update_process = $update_session['process_update'];
        $is_update_auto = $update_session['auto_update'];
        
        // process : 加工 / アップデート中
        if($is_update_process)
        {
            session()->put('process_update', true);
        }
        else
        {
            session()->put('process_update', false);
        }

        // auto : 自動 / アップデート
        if($is_update_auto)
        {
            session()->put('auto_update', true);
        }
        else
        {
            session()->put('auto_update', false);
        }

        // テスト画面に飛ばす
        return view('ws.ws_test');
    }
}
