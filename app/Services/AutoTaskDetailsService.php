<?php

namespace App\Services;

use App\Repositories\AutoTaskDetailsRepository;

class AutoTaskDetailsService
{
    protected $_autoTaskDetailsRepository;

    // コンストラクタ
    // 引数に入れることでnewしてくれる
    public function __construct(AutoTaskDetailsRepository $autoTaskDetailsRepository)
    {
        $this->_autoTaskDetailsRepository = $autoTaskDetailsRepository;
    }

    // AutoTaskDetailsのデータ全取得
    public function auto_task_details_get()
    {
        return $this->_autoTaskDetailsRepository->auto_task_details_get();
    }

    // 使わない
    public function insert($data)
    {
        // dataに対する処理はここに書く
        // 

        $this->_autoTaskDetailsRepository->insert($data);
    }

    // 取得したidのデータを取得
    public function auto_task_datails_find_by_id($excel_info_id)
    {
        return $this->_autoTaskDetailsRepository->auto_task_datails_find_by_id($excel_info_id);
    }

    public function upsert($data, $id, $excelInfo)
    {
        // dataに対する処理はここに書く
        return $this->_autoTaskDetailsRepository->upsert($data, $id, $excelInfo);
    }
}
