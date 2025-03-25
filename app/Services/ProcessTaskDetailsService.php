<?php

namespace App\Services;

use App\Repositories\ProcessTaskDetailsRepository;

class ProcessTaskDetailsService
{
    protected $_processTaskDetailsRepository;

    // コンストラクタ
    // 引数に入れることでnewしてくれる
    public function __construct(ProcessTaskDetailsRepository $processTaskDetailsRepository)
    {
        $this->_processTaskDetailsRepository = $processTaskDetailsRepository;
    }

    public function process_task_details_get()
    {
        return $this->_processTaskDetailsRepository->process_task_details_get();
    }
    // 取得したidのデータを取得
    public function process_task_datails_find_by_id($excel_info_id)
    {
        return $this->_processTaskDetailsRepository->process_task_datails_find_by_id($excel_info_id);
    }

    // 取得したデータを取得
    public function get_filter_workers()
    {
        return $this->_processTaskDetailsRepository->get_filter_workers();
    }

    public function upsert($data, $id, $excelInfo)
    {
        // dataに対する処理はここに書く
        return $this->_processTaskDetailsRepository->upsert($data, $id, $excelInfo);
    }
}
