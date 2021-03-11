<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

require_once "../services/services.php";
require_once "../socket/client.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    if ($m == "adddriver") {
        $socket = new SocketClient();
        $array = array(
            "command" => "driver",
            "m" => "adddriver",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        echo $data;
    } else if ($m == "updatedriver") {
        $socket = new SocketClient();
        $array = array(
            "command" => "driver",
            "m" => "updatedriver",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        echo $data;
    } else if ($m == "deletedriver") {
        $socket = new SocketClient();
        $array = array(
            "command" => "driver",
            "m" => "deletedriver",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        echo $data;
    } else if ($m == "driverlist") {
        $socket = new SocketClient();
        $array = array(
            "command" => "driver",
            "m" => "driverlist",
            "data" => [$json],
            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
        );
        $json = json_encode($array);
        $data = $socket->send($json);
        $result = json_decode($data, true);
        $json = json_encode($result['data'][0]);
        echo $json;
    } else if ($m == "driverlistpage") {
        $socket = new SocketClient();
        $array = array(
            "command" => "driver",
            "m" => "driverlistpage",
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
