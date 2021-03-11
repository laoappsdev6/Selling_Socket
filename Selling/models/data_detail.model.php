<?php

function validateData_detail($detail,$check = false){

    for($i = 0; $i < sizeof($detail); $i++ ){
        $product_id = $detail[$i]['product_id'];
        $device_sim = $detail[$i]['device_sim'];
        $quantity = $detail[$i]['quantity'];
        $price = $detail[$i]['price'];
        $total = $detail[$i]['total'];

        if(!$check){
            $detail_id = $detail[$i]['detail_id'];
            validateDeatil_id($detail_id);
        }
        validateProduct_id($product_id);
        validateDevice_sim($device_sim);
        validateSprice($price);
        validateQuantity($quantity);
        validateTotal($total);
    }
}
function validateDeatil_id($data){
    $db = new db_mssql();
        $sql = "select * from sell_data_detail where detail_id='$data'";
        $pro = $db->query($sql);
        if($pro == 0){
            PrintJSON("", "detail ID: ".$data." is not available!", 0);
            die();
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
function validateDevice_sim($data){
    if($data == ""){
        PrintJSON("","Device SIM is empty!",0);
        die();
    }
}

function validateSprice($data){
    if($data == ""){
        PrintJSON("","price is empty!",0);
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
