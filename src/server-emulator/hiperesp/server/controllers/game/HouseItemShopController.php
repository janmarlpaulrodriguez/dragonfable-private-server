<?php declare(strict_types=1);
namespace hiperesp\server\controllers\game;

use hiperesp\server\controllers\Controller;
use hiperesp\server\attributes\Inject;
use hiperesp\server\attributes\Request;
use hiperesp\server\enums\Input;
use hiperesp\server\enums\Output;
use hiperesp\server\projection\HouseItemShopProjection;
use hiperesp\server\services\HouseItemShopService;

class HouseItemShopController extends Controller {

    #[Inject] private HouseItemShopService $houseItemShopService;

    #[Request(
        endpoint: '/cf-loadhouseitemshop.asp',
        inputType: Input::NINJA2,
        outputType: Output::XML
    )]
    public function load(\SimpleXMLElement $input): \SimpleXMLElement {
        $shop = $this->houseItemShopService->getShop((int)$input->intShopID);
        if ($shop == null) {
            return new \SimpleXMLElement('<houseshop><shop ShopID="0" strCharacterName="Shop Not Found"/></houseshop>');
        }
        return HouseItemShopProjection::instance()->loaded($shop);
    }

}