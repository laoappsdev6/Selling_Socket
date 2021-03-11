<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/data.controller.php";
include_once "../models/data.model.php";
include_once "../models/data_detail.model.php";
try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new DataController();

    if ($m == "adddata") {
        $dt = new DataModel($json['data']);
        $dt->checkAllProperties();
        validateData_detail($json['data_detail'],true);
        $control->addDataPurchase($dt, $json['data_detail']);
    } else if ($m == "updatedata") {
        $dt = new DataModel($json['data']);
        $dt->checkId();
        $dt->checkAllProperties();
        validateData_detail($json['data_detail'],true);
        $control->updateDataPurchase($dt, $json['data_detail']);
    } else if ($m == "datalist") {
        $dt = (object) $json;
        $control->datalist($dt);
    } else if ($m == "getdatalist_one") {
        $dt = (object) $json;
        $db = new db_mssql();
        $sql = "select * from sell_data_purchase where data_id ='$dt->data_id' ";
        $name = $db->query($sql);
        if ($name == 0) {
            PrintJSON("", " data ID: " . $dt->data_id . " is not available!", 0);
            die();
        } else {
            $control->getDataList_one($dt->data_id);
        }
    } else if ($m == "purchase_data") {
        $dt = new DataModel($json);
        $dt->checkId();
        $control->purchaseData($dt);
    }else if ($m == "payment_data") {
        $dt = new DataModel($json);
        $dt->checkId();
        $dt->validateReferences();
        $dt->validateImage();
        $control->paymentData($dt);
    } else {
        PrintJSON("", "method not provided", 0);
    }
} catch (Exception $e) {
    print_r($e);
}
