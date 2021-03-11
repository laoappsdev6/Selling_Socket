<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/payment.controller.php";
include_once "../models/payment.model.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new PaymentController();
    $model = new PaymentModel($json);
    if ($m == "addpayment") {
        $model->checkAllProperties();
        $control->addPayment($model);
    }  else if ($m == "paymentlist") {
        $control->paymentList($model);
    } else if ($m == "paymentlist_invoice") {
        $control->paymentListInvoice($model);
    }  else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
