<?php 

namespace App\Services;

use App\Repositories\UpdateManagementRepository;

class UpdateManagementService 
{
    // リポジトリクラスとの紐付け
    protected $_updateManagementRepository;

    // phpのコンストラクタ
    public function __construct(UpdateManagementRepository $updateManagementRepository)
    {
        $this->_updateManagementRepository = $updateManagementRepository;
    }

    public function upsert($data)
    {
        $this->_updateManagementRepository->upsert($data);
    }

    public function select()
    {
        return $this->_updateManagementRepository->select();
    }
}