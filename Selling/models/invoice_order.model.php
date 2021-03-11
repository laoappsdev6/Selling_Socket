<?php

function validateInvoice_order($order){

    for($i = 0; $i < sizeof($order); $i++ ){
        $product_id =  $order[$i]['product_id'];
        $purchase_id = $order[$i]['purchase_id'];
        $sprice = $order[$i]['sprice'];
        $quantity = $order[$i]['quantity'];
        $total = $order[$i]['total'];

        validateProduct_id($product_id);
        validatePurchase_id($purchase_id);
        validateSprice($sprice);
        validateQuantity($quantity);
        validateTotal($total);
    }
}

function validateProduct_id($data){
    $db = new db_mssql();
        $sql = "select * from sell_product where product_id='$data'";
        $pro = $db->query($sql);
        if($pro == 0){
            PrintJSON("", "product ID: ".$data." is not available!", 0);
            die();
        }
}

function validatePurchase_id($data){
    $db = new db_mssql();
        $sql = "select * from sell_purchase where status IN (1,3) and purchase_id ='$data' ";
        $name = $db->query($sql);
        
        if ($name == 0) {
            PrintJSON(""," purchase ID: ".$data. " is not available!", 0);
            die();
        }
}
// function validateImei_id($data){
//     if($data == ""){
//         PrintJSON("","IMEI REFERENCES is empty!",0);
//         die();
//     }else  if (!is_numeric($data)) {
//         PrintJSON("", " IMEI REFERENCES is number only ", 0);
//         die();
// }
// }
function validateSprice($data){
    if($data == ""){
        PrintJSON("","sprice is empty!",0);
        die();
    }
}
function validateQuantity($data){
    if($data == ""){
        PrintJSON("","quantity  is empty!",0);
        die();
    }
}
function validateTotal($data){
    if($data == ""){
        PrintJSON("","total  is empty!",0);
        die();
    }
}

?>
