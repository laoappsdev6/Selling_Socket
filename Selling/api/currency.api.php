<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include("../controllers/currency.controller.php");
include_once("../models/currency.model.php");

try {
    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);
//hh
    $model = new CurrencyModel($json);
    $control = new CurrencyController();

    if ($m == "addcurrency") {  
        $model->validateName();
        $model->validateRate();
        $control->addCurrency($model);
    } else if ($m == "updatecurrency") {
        $model->checkId();
        $control->updateCurrency($model);
    } else if ($m == "currencylist") {
        $control->currencyList($model);
    } else if ($m == "currencylist_active") {
        $control->currencyListActive($model);
    } else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
