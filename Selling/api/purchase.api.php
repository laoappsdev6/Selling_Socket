<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/purchase.controller.php";
include_once "../models/purchase.model.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new PurchaseController();

    if ($m == "addpurchase") {
        validatePurchase($json['purchase']);
        $control->addPurchase($json['purchase']);
    } else if ($m == "purchaselist") {
        $json = (object) $json;
        $control->purchaseList($json);
    } else if ($m == "listgps") {
        $gps = (object) $json;
        $control->ListGPS($gps);
    } else if ($m == "listsim") {
        $sim = (object) $json;
        $control->ListSIM($sim);
    }else if ($m == "listsim_second") {
        $sim = (object) $json;
        $control->ListSIM_second($sim);
    } else if ($m == "listserver") {
        $server = (object) $json;
        $control->ListSERVER($server);
    } else if ($m == "listserver2") {
        $server = (object) $json;
        $control->ListSERVER2($server);
    } else if ($m == "listdata") {
        $data = (object) $json;
        $control->ListDATA($data);
    } else if ($m == "listall") {
        $sim = (object) $json;
        $control->ListAll($sim);
    } else {
        PrintJSON("", "method not provided", 0);
    }
} catch (Exception $e) {
    print_r($e);
}
