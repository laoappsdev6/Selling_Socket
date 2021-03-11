<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

require_once "../controllers/search.controller.php";
require_once "../models/search.model.php";
require_once "../socket/client.php";


try {
    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);
    $search = (object) $json;
//     $cate = new CategoryModel($json);
    $control = new SearchController();

    if ($m == "search_gps") {
        $socket = new SocketClient();
        $array = array(
                        "command"=>"device_object",
                        "m"=>"search_gps",
                        "data"=>[$json],
                        "token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E"
                        ); 
        $json = json_encode($array);     
        $result = $socket->send($json);
        $data = json_decode($result,true);
        $control->searchGPS($data['data'][0]);
    } else if ($m == "search_invoice") {
        $control->searchInvoice($search);
    } else if ($m == "search_customer") {
        $control->searchCustomer($search);
    }else if ($m == "search_invoice_order") {
        $control->searchInvoiceOrder($search);
    }else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
