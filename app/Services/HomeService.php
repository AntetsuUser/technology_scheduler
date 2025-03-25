<?php

namespace App\Services;

use App\Repositories\HomeRepository;

class HomeService
{
    protected $_homeRepository;

    // コンストラクタ
    // 引数に入れることでnewしてくれる
    public function __construct(HomeRepository $homeRepository)
    {
        $this->_homeRepository = $homeRepository;
    }

    // public function insert($data)
    // {
    //     // dataに対する処理はここに書く
    //     // 

    //     $this->_homeRepository->insert($data);
    // }

    // ExcelInfoのデータを全取得
    public function excel_info_get()
    {
        return $this->_homeRepository->excel_info_get();
    }

    // 取得したidのデータを取得
    public function excel_info_find_by_id($id)
    {
        return $this->_homeRepository->excel_info_find_by_id($id);
    }

    public function upsert($data, $id, $excel_info_id)
    {
        // dataに対する処理はここに書く
        return $this->_homeRepository->upsert($data, $id, $excel_info_id);
    }

    // 工程の削除
    public function delete($id)
    {
        // dd("HS : " . $id);

        // dataに対する処理はここに書く
        return $this->_homeRepository->delete($id);
    }
}
