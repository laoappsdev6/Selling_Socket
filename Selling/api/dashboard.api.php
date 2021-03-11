<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

require_once "../controllers/dashboard.controller.php";
require_once "../socket/client.php";

try {
    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);
    $dash = (object) $json;
    $control = new DashboardController();

    if ($m == "customer_debtor") {
        $control->customerDebtor($dash);
    } else if ($m == "server_expired") {
        $socket = new SocketClient();
        $array = array(
            "command" => "device_object",
            "m" => "server_expired",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        $result = json_decode($data, true);
        $json = json_encode($result['data'][0]);
        echo $json;
    } else if ($m == "device_online") {
        $socket = new SocketClient();
        $array = array(
            "command" => "device_object",
            "m" => "device_online",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        $result = json_decode($data, true);
        $json = json_encode($result['data'][0]);
        echo $json;
    } else if ($m == "device_for_sell") {
        $control->device_for_sell($dash);
    } else {
        PrintJSON("", "method not provided", 0);
    }
} catch (Exception $e) {
    print_r($e);
}
