<?php declare(strict_types=1);
namespace hiperesp\server\services;

use hiperesp\server\attributes\Inject;
use hiperesp\server\models\HouseItemShopModel;
use hiperesp\server\vo\HouseItemShopVO;

class HouseItemShopService extends Service {

    #[Inject] private HouseItemShopModel $houseItemShopModel;

    public function getShop(int $shopId): ?HouseItemShopVO {
        return $this->houseItemShopModel->getById($shopId);
    }

}
