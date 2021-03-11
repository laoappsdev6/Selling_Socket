<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/report.controller.php";

try {
    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);
    $date = (object) $json;
    $control = new ReportController();

    if ($m == "report_payment") {
        $control->reportPayment($date);
    } else if ($m == "report_purchase") {
        $control->reportPurchase($date);
    } else if ($m == "report_product") {
        $control->reportProduct();
    } else if ($m == "report_cancel_invoice") {
        $control->reportCancel_invoice($date);
    } else if ($m == "report_purchase_data") {
        $control->reportPurchase_Data($date);
    } else if ($m == "report_install") {
        $control->reportInstall($date);
    } else if ($m == "report_second") {
        $control->reportSecond($date);
    } else if ($m == "report_invoice") {
        $control->reportInvoice($date);
    } else if ($m == "report_server_renewal_ok") {
        $control->serverRenewaled($date);
    }else if ($m == "report_server_renewalling") {
        $control->serverRenewalling($date);
    }else if ($m == "report_server_no_renewal") {
        $control->serverNoRenewal($date);
    } else {
        PrintJSON("", "method not provided", 0);
    }
} catch (Exception $e) {
    print_r($e);
}
