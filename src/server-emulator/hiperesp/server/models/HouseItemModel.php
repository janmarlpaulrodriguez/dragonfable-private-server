<?php declare(strict_types=1);
namespace hiperesp\server\models;

use hiperesp\server\exceptions\DFException;
use hiperesp\server\vo\HouseItemVO;
use hiperesp\server\vo\HouseItemShopVO;

class HouseItemModel extends Model {

    const COLLECTION = 'houseItem';
    const SHOP_ASSOCIATION = 'houseItemShop_houseItem';

    public function getById(int $itemId): HouseItemVO {
        $item = $this->storage->select(self::COLLECTION, ['id' => $itemId]);
        if(isset($item[0]) && $item = $item[0]) {
            return new HouseItemVO($item);
        }
        throw new DFException(DFException::ITEM_NOT_FOUND);
    }

    /** @return array<HouseItemVO> */
    public function getByShop(HouseItemShopVO $shop): array {
        $itemIds = \array_map(function(array $item): int {
            return (int)$item['houseItemId'];
        }, $this->storage->select(self::SHOP_ASSOCIATION, ['houseItemShopId' => $shop->id], null));

        if (empty($itemIds)) {
            return [];
        }

        return \array_map(function(array $item): HouseItemVO {
            return new HouseItemVO($item);
        }, $this->storage->select(self::COLLECTION, ['id' => $itemIds], null));
    }

}
