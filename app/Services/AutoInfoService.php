<?php

namespace App\Services;

use App\Repositories\AutoInfoRepository;

class AutoInfoService
{
    protected $_autoInfoRepository;

    // コンストラクタ
    // 引数に入れることでnewしてくれる
    public function __construct(AutoInfoRepository $autoInfoRepository)
    {
        $this->_autoInfoRepository = $autoInfoRepository;
    }

    public function insert($data)
    {
        // dataに対する処理はここに書く
        // 

        $this->_autoInfoRepository->insert($data);
    }

    // DB:ExcelInfoの全取得（all）
    public function excel_info_auto_get()
    {
        return $this->_autoInfoRepository->excel_info_auto_get();
    }
    
    // DB:autoInfoの全取得（all）
    public function auto_info_get()
    {
        return $this->_autoInfoRepository->auto_info_get();
    }

    // 取得したidのデータを取得
    public function auto_info_find_by_id($excel_info_id)
    {
        return $this->_autoInfoRepository->auto_info_find_by_id($excel_info_id);
    }

    // 取得したidのデータを取得
    public function get_workers()
    {
        return $this->_autoInfoRepository->get_workers();
    }
    
    public function upsert($data, $id, $excelInfo)
    {
        // dataに対する処理はここに書く
        return $this->_autoInfoRepository->upsert($data, $id, $excelInfo);
    }
}