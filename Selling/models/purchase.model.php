<?php

function validatePurchase($pur)
{

    for ($i = 0; $i < sizeof($pur); $i++) {
        $product_id = $pur[$i]['product_id'];
        $device_no = $pur[$i]['device_no'];
        $bprice = $pur[$i]['bprice'];
        $quantity = $pur[$i]['quantity'];
        $amount = $pur[$i]['amount'];

        validateProduct_id($product_id);
        validateDevice_no($device_no);
        validateBprice($bprice);
        validateQuantity($quantity);
        validateAmount($amount);

    }
}
function validateProduct_id($data)
{
    $db = new db_mssql();
    $sql = "select * from sell_product where product_id='$data'";
    $pro = $db->query($sql);
    if ($pro == 0) {
        PrintJSON("", "product ID: " . $data . " is not available!", 0);
        die();
    }
}
function validateDevice_no($data)
{
    $db = new db_mssql();
    $sql = "select * from sell_purchase where device_no='$data' and status !=5 ";
    $device = $db->query($sql);

    if ($device > 0) {
        PrintJSON("", " device ID: " . $data . " already exist", 0);
        die();
    } else if (strlen($data) < 5) {
        PrintJSON("", "device ID must be greater than 4  deigits ", 0);
        die();
    }
}

function validateBprice($data)
{
    if ($data == "") {
        PrintJSON("", "bprice is empty!", 0);
        die();
    }
}
function validateQuantity($data)
{
    if ($data == "") {
        PrintJSON("", "Quantity is empty", 0);
        die();
    } else if (!is_numeric($data)) {
        PrintJSON("", "Quantity is number only", 0);
        die();
    }
}

function validateAmount($data)
{
    if ($data == "") {
        PrintJSON("", "amount is empty!", 0);
        die();
    }
}
