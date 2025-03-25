<?php

namespace App\Services;

use App\Repositories\ProcessInfoRepository;

class ProcessInfoService
{
    protected $_processInfoRepository;

    // コンストラクタ
    // 引数に入れることでnewしてくれる
    public function __construct(ProcessInfoRepository $processInfoRepository)
    {
        $this->_processInfoRepository = $processInfoRepository;
    }

    // public function insert($data)
    // {
    //     // dataに対する処理はここに書く
    //     // 

    //     $this->_processInfoRepository->insert($data);
    // }

    // DB:ExcelInfoの全取得（all）
    public function excel_info_process_get()
    {
        return $this->_processInfoRepository->excel_info_process_get();
    }
    
    // DB:ProcessInfoの全取得（all）
    public function process_info_get()
    {
        return $this->_processInfoRepository->process_info_get();
    }

    
    // // 取得したidのデータを取得
    // public function excel_info_find_by_id($id)
    // {
    //     return $this->_processInfoRepository->excel_info_find_by_id($id);
    // }
    
    // 取得したidのデータを取得
    public function process_info_find_by_id($excel_info_id)
    {
        return $this->_processInfoRepository->process_info_find_by_id($excel_info_id);
    }

    // 取得したidのデータを取得
    public function get_workers()
    {
        return $this->_processInfoRepository->get_workers();
    }
    
    public function upsert($data, $id, $excelInfo)
    {
        // dataに対する処理はここに書く
        return $this->_processInfoRepository->upsert($data, $id, $excelInfo);
    }
}
