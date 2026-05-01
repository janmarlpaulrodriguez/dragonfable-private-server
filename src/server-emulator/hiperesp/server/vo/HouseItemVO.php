<?php declare(strict_types=1);
namespace hiperesp\server\vo;

class HouseItemVO extends ValueObject {

    public readonly string $name;
    public readonly string $description;
    public readonly int $visible;
    public readonly bool $destroyable;
    public readonly bool $equippable;
    public readonly bool $randomDrop;
    public readonly bool $sellable;
    public readonly bool $dragonAmulet;
    public readonly bool $enc;
    public readonly int $cost;
    public readonly int $currency;
    public readonly int $maxStackSize;
    public readonly int $rarity;
    public readonly int $level;
    public readonly int $maxLevel;
    public readonly int $category;
    public readonly int $equipSpot;
    public readonly string $type;
    public readonly int $random;
    public readonly int $element;
    public readonly string $swf;

}
