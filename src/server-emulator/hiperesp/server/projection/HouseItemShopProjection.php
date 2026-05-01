<?php
namespace hiperesp\server\projection;

use hiperesp\server\vo\HouseItemShopVO;

class HouseItemShopProjection extends Projection {

    public function loaded(HouseItemShopVO $shop): \SimpleXMLElement {

        $xml = new \SimpleXMLElement('<houseitemshop/>');
        $shopEl = $xml->addChild('houseitemshop');
        $shopEl->addAttribute('houseItemShopID', $shop->id);
        $shopEl->addAttribute('strName', $shop->name);
        $shopEl->addAttribute('intCount', -100);

        foreach($shop->getItems() as $item) {
            $itemEl = $shopEl->addChild('houseitems');

            $itemEl->addAttribute('HouseItemID', $item->id);
            $itemEl->addAttribute('strItemName', $item->name);
            $itemEl->addAttribute('strItemDescription', $item->description);
            $itemEl->addAttribute('bitVisible', $item->visible);
            $itemEl->addAttribute('bitDestroyable', $item->destroyable);
            $itemEl->addAttribute('bitEquippable', $item->equippable);
            $itemEl->addAttribute('bitRandomDrop', $item->randomDrop);
            $itemEl->addAttribute('bitSellable', $item->sellable);
            $itemEl->addAttribute('bitDragonAmulet', $item->dragonAmulet);
            $itemEl->addAttribute('bitEnc', $item->enc);
            $itemEl->addAttribute('intCost', $item->cost);
            $itemEl->addAttribute('intCurrency', $item->currency);
            $itemEl->addAttribute('intMaxStackSize', $item->maxStackSize);
            $itemEl->addAttribute('intRarity', $item->rarity);
            $itemEl->addAttribute('intLevel', $item->level);
            $itemEl->addAttribute('intMaxLevel', $item->maxLevel);
            $itemEl->addAttribute('intCategory', $item->category);
            $itemEl->addAttribute('intEquipSpot', $item->equipSpot);
            $itemEl->addAttribute('intType', 1); // Reference says intType but I see strType too
            $itemEl->addAttribute('bitRandom', $item->random);
            $itemEl->addAttribute('intElement', $item->element);
            $itemEl->addAttribute('strType', $item->type);
            $itemEl->addAttribute('strFileName', $item->swf);

        }

        return $xml;
    }

}
