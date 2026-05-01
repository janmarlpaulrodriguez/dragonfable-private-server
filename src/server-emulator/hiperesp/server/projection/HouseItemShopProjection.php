<?php
namespace hiperesp\server\projection;

use hiperesp\server\vo\HouseItemShopVO;

class HouseItemShopProjection extends Projection {

    public function loaded(HouseItemShopVO $shop): \SimpleXMLElement {

        $xml = new \SimpleXMLElement('<houseshop/>');
        $shopEl = $xml->addChild('shop');
        $shopEl->addAttribute('ShopID', $shop->id);
        $shopEl->addAttribute('strCharacterName', $shop->name);

        foreach($shop->getItems() as $item) {
            $itemEl = $shopEl->addChild('sItems');

            $itemEl->addAttribute('ItemID', $item->id);
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
            $itemEl->addAttribute('strType', $item->type);
            $itemEl->addAttribute('bitRandom', $item->random);
            $itemEl->addAttribute('intElement', $item->element);
            $itemEl->addAttribute('strFileName', $item->swf);

        }

        return $xml;
    }

}
