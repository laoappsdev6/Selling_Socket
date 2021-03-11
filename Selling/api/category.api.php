<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include("../controllers/category.controller.php");
include_once("../models/category.model.php");

try {
    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);
//hh
    $cate = new CategoryModel($json);
    $control = new CategoryController();

    if ($m == "addcategory") {  
        $cate->validateName();
        $control->addCategory($cate);
    } else if ($m == "updatecategory") {
        $cate->checkId();
        $control->updateCategory($cate);
    } else if ($m == "categorylist") {
        $control->categoryList($cate);
    } else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
