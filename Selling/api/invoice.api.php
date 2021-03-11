<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/invoice.controller.php";
include_once "../models/invoice.model.php";
include_once "../models/invoice_order.model.php";
try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new InvoiceController();

    if ($m == "addorder") {
        $inv = new InvoiceModel($json['invoice']);
        $inv->checkAllProperties();
        validateInvoice_order($json['invoice_order']);
        $control->addOrder($inv, $json['invoice_order']);
    } else if ($m == "dispose") {
        $inv = new InvoiceModel($json['invoice']);
        $control->Dispose($inv, $json['invoice_order']);
    } else if ($m == "invoice") {
        $inv = new InvoiceModel($json);
        $inv->checkId();
        $control->Invoice($inv);
    } else if ($m == "repay") {
        $inv = new InvoiceModel($json['invoice']);
        $inv->checkAllProperties();
        $control->rePay($inv, $json['invoice_order']);
    } else if ($m == "repaylist") {
        $json = (object) $json;
        $control->repayList($json);
    } else if ($m == "disposelist") {
        $json = (object) $json;
        $control->disposeList($json);
    } else if ($m == "setting") {
        $sett = (object) $json;
        $control->Setting($sett);
    } else if ($m == "settinglist") {
        $control->Settinglsit();
    } else if ($m == "invoicelist") {
        $json = (object) $json;
        $control->Invoicelist($json);
    } else if ($m == "getinvoice") {
        $json = (object) $json;
        $control->getInvoice($json);
    } else if ($m == "getinstall") {
        $json = (object) $json;
        $control->getInstall($json);
    } else if ($m == "listinstall") {
        $json = (object) $json;
        $control->ListInstall($json);
    } else if ($m == "payment") {
        $inv = new InvoiceModel($json);
        $inv->checkId();
        $inv->validatePayment();
        $control->Payment($inv);
    } else {
        PrintJSON("", "method not provided", 0);
    }
} catch (Exception $e) {
    print_r($e);
}
