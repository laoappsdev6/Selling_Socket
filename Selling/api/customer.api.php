<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/customer.controller.php";
include_once "../models/customer.model.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new CustomerController();
    $ctm = new CustomerModel($json);

    if ($m == "addcustomer") {
            $ctm->checkAllProperties();
            $control->addCustomer($ctm);
    } else if ($m == "updatecustomer") {
            $ctm->checkId();
            $ctm->checkAllProperties();
            $control->updateCustomer($ctm);
    } else if ($m == "deletecustomer") {
            $ctm->checkId();
            $control->deleteCustomer($ctm);
    } else if ($m == "customerlist") {
        $cut = (object) $json;
      $control->CustomerList($cut);
    } else if ($m == "customerlist_group") {
        $cut = (object) $json;
      $control->CustomerListGroup($cut);
    } else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
