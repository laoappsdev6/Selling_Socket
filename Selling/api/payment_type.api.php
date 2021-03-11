<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/payment_type.controller.php";
include_once "../models/payment_type.model.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new PaymentTypeController();
    $model = new PaymentTypeModel($json);
    if ($m == "addpayment_type") {
        $model->checkAllProperties();
        $control->addPaymentType($model);
    } else if ($m == "updatepayment_type") {
        $model->checkId();
        $model->checkAllProperties();
        $control->updatePaymentType($model);
    } else if ($m == "payment_type_list") {
        $control->paymentTypeList($model);
    } else if ($m == "payment_type_list_active") {
        $control->paymentTypeListActive($model);
    }  else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
