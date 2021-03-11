<?php
require_once "../services/services.php";
class SocketClient
{

    private $host = "127.0.0.1";
    private $port = 20205;
    private $data_limit = 9999999;
    private $socket;

    public function __construct()
    {
        $this->socket = socket_create(AF_INET, SOCK_STREAM, 0) or die(PrintJSON("", "Connot create Socket Client", 0));
        @socket_connect($this->socket, $this->host, $this->port) or die(PrintJSON("", "Cannot connect to Server", 0));
    }
    
    public function send($msg)
    {
        socket_write($this->socket, $msg, strlen($msg)) or die(PrintJSON("", "Cannot send message to Server", 0));
        $reply = socket_read($this->socket, $this->data_limit) or die(PrintJSON("", "Connot read data from Server", 0));
        return $reply;
        $this->onClose();
    }

    public function onClose() 
    {
        socket_close($this->socket);
    }
}

// $socket = new SocketClient();
// $json = json_decode(file_get_contents('php://input'), true);
// $get = json_encode($json);
// $data = $socket->send($get);
// echo $data;
?>
