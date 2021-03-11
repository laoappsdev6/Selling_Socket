<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

require_once "../controllers/product.controller.php";
require_once "../models/product.model.php";
require_once "../socket/client.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new ProductController();

    if ($m == "addproduct") {
        $pro = new ProductModel($json, true);
        $pro->checkAllProperties();
        $pro->validateDevice_type();
        $control->addProducts($pro);
    } else if ($m == "updateproduct") {
        $pro = new ProductModel($json, true);
        $pro->checkId();
        $pro->checkAllProperties();
        $control->updateProducts($pro);
    } else if ($m == "productlist") {
        $pro = new ProductModel($json, true);
        $control->productsList($pro);
    } else if ($m == "productlist_purchase") {
        $pro = new ProductModel($json, true);
        $control->productList_Purchase($pro);
    } else if ($m == "productlist_data") {
        $pro = new ProductModel($json, true);
        $control->productList_Data($pro);
    } else if ($m == "getdtype") {
        $socket = new SocketClient();
        $array = array(
            "command" => "device_object",
            "m" => "getdtype",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        $result = json_decode($data, true);
        $json = json_encode($result['data'][0]);
        echo $json;
    } else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
