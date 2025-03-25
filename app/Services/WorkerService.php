<?php

namespace App\Services;

use App\Repositories\WorkerRepository;

class WorkerService
{
    protected $_workerRepository;

    // コンストラクタ
    // 引数に入れることでnewしてくれる
    public function __construct(WorkerRepository $workerRepository)
    {
        $this->_workerRepository = $workerRepository;
    }

    // // 作成、更新
    // public function upsert($data, $id, $excel_info_id)
    // {
    //     // dataに対する処理はここに書く
    //     return $this->_homeRepository->upsert($data, $id, $excel_info_id);
    // }

    // // 工程の削除
    // public function delete($id)
    // {
    //     // dd("HS : " . $id);

    //     // dataに対する処理はここに書く
    //     return $this->_homeRepository->delete($id);
    // }

    // Worker 取得
    public function worker_get()
    {
        // 値の受け取り
        return $this->_workerRepository->worker_get();
    }
}
