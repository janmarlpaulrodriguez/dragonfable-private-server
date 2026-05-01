<?php
namespace hiperesp\server\projection;

use hiperesp\server\vo\HouseShopVO;

class HouseShopProjection extends Projection {

    public function loaded(HouseShopVO $shop): \SimpleXMLElement {

        $xml = new \SimpleXMLElement('<shop/>');
        $shopEl = $xml->addChild('shop');
        $shopEl->addAttribute('ShopID', $shop->id);
        $shopEl->addAttribute('strCharacterName', $shop->name);
        $shopEl->addAttribute('intCount', -100);

        foreach($shop->getHouses() as $house) {
            $houseEl = $shopEl->addChild('sHouses');

            $houseEl->addAttribute('HouseID', $house->id);
            $houseEl->addAttribute('strHouseName', $house->name);
            $houseEl->addAttribute('strHouseDescription', $house->description);
            $houseEl->addAttribute('bitVisible', $house->visible);
            $houseEl->addAttribute('bitDestroyable', $house->destroyable);
            $houseEl->addAttribute('bitEquippable', $house->equippable);
            $houseEl->addAttribute('bitRandomDrop', $house->randomDrop);
            $houseEl->addAttribute('bitSellable', $house->sellable);
            $houseEl->addAttribute('bitDragonAmulet', $house->dragonAmulet);
            $houseEl->addAttribute('bitEnc', $house->enc);
            $houseEl->addAttribute('intCost', $house->cost);
            $houseEl->addAttribute('intCurrency', $house->currency);
            $houseEl->addAttribute('intRarity', $house->rarity);
            $houseEl->addAttribute('intLevel', $house->level);
            $houseEl->addAttribute('intCategory', $house->category);
            $houseEl->addAttribute('intEquipSpot', $house->equipSpot);
            $houseEl->addAttribute('intType', $house->type);
            $houseEl->addAttribute('bitRandom', $house->random);
            $houseEl->addAttribute('intElement', $house->element);
            $houseEl->addAttribute('strType', $house->strType);
            $houseEl->addAttribute('strIcon', $house->icon);
            $houseEl->addAttribute('strDesignInfo', $house->designInfo);
            $houseEl->addAttribute('strFileName', $house->swf);
            $houseEl->addAttribute('intRegion', $house->region);
            $houseEl->addAttribute('intTheme', $house->theme);
            $houseEl->addAttribute('intSize', $house->size);
            $houseEl->addAttribute('intBaseHP', $house->baseHP);
            $houseEl->addAttribute('intStorageSize', $house->storageSize);
            $houseEl->addAttribute('intMaxGuards', $house->maxGuards);
            $houseEl->addAttribute('intMaxRooms', $house->maxRooms);
            $houseEl->addAttribute('intMaxExtItems', $house->maxExtItems);

        }

        return $xml;
    }

}