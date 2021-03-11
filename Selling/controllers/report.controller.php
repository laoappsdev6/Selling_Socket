
<?php

require_once "../services/services.php";
require_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
require_once "../socket/client.php";

class ReportController
{

    public function __construct()
    {
    }

    public function reportPayment($get)
    {
        try {
            $db = new db_mssql();

            $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,i.status,i.remark,pay_by,i.user_id,u.fname
                from sell_invoices as i
                INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                INNER JOIN sell_user as u ON i.user_id = u.user_id
                where i.status = 2  and invoice_date between '$get->firstdate' and '$get->lastdate' order by invoice_id desc ";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportPurchase($get)
    {
        try {
            $db = new db_mssql();

            $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,p.dtype_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id,u.fname
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                INNER JOIN sell_user as u ON c.user_id = u.user_id
                where  c.purchase_date between '$get->firstdate' and '$get->lastdate' order by c.purchase_id desc ";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportProduct()
    {
        try {
            $db = new db_mssql();
            $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id,u.fname
                        from sell_product as p
                        inner join sell_category as c on p.cate_id=c.cate_id
                        INNER JOIN sell_user as u ON p.user_id = u.user_id order by product_id desc";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportCancel_invoice($get)
    {
        try {
            $db = new db_mssql();
            $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,i.status,i.remark,pay_by,i.user_id,u.fname
                from sell_invoices as i
                INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                INNER JOIN sell_user as u ON i.user_id = u.user_id
                where  i.status = 3  and invoice_date between '$get->firstdate' and '$get->lastdate' order by invoice_id desc ";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportPurchase_Data($get)
    {
        try {
            $db = new db_mssql();
            $sql = "select data_id,supplier,data_references,total,date,dp.status,dp.remark,image,dp.user_id,u.fname
                from sell_data_purchase dp
                INNER JOIN sell_user as u ON dp.user_id = u.user_id
                where dp.status !=0 and date between '$get->firstdate' and '$get->lastdate' order by data_id desc ";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportInstall($get)
    {
        try {
            $db = new db_mssql();

            $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,p.dtype_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id,u.fname
            from sell_purchase as c
            INNER JOIN sell_product as p ON c.product_id = p.product_id
            INNER JOIN sell_user as u ON c.user_id = u.user_id
            where c.status = 2 and p.cate_id =(select gps from sell_invoice_prefix) and  c.purchase_date between '$get->firstdate' and '$get->lastdate' order by c.purchase_id desc ";
            // echo $sql;die();
            $data = $db->query($sql);
            if ($data > 0) {
                for ($i = 0; $i < sizeof($data); $i++) {

                    $imei_ref = $data[$i]["device_no"];
                    $subsql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.status,o.remark,last_update,quaranteed,o.user_id,
                        p.product_name,p.dtype_id,p.device_type,p.cate_id,
                        pc.device_no,pc.device_sim,
                        c.customer_id,short_name,full_name
                        from sell_invoices_order as o
                        INNER JOIN sell_product as p ON o.product_id = p.product_id
                        INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                        INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where imei_references = '$imei_ref' ";

                    $subdata = $db->query($subsql);
                    $data[$i]['sub_references'] = $subdata;
                }
                $list = json_encode($data);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportSecond($get)
    {
        try {
            $db = new db_mssql();

            $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,p.dtype_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id,u.fname
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                INNER JOIN sell_user as u ON c.user_id = u.user_id
                where c.status = 3  order by c.purchase_id desc ";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;

        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function reportInvoice($get)
    {
        try {
            $db = new db_mssql();

            $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,i.status,i.remark,pay_by,i.user_id,u.fname
                from sell_invoices as i
                INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                INNER JOIN sell_user as u ON i.user_id = u.user_id
                where  i.status IN(1,2)  and  invoice_date between '$get->firstdate' and '$get->lastdate' order by invoice_id desc ";
            $doquery = $db->query($sql);
            if ($doquery > 0) {
                $list = json_encode($doquery);
            } else {
                $list = json_encode([]);
            }
            $json = "{\"Data\":$list}";
            echo $json;

        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function serverRenewaled($pur)
    {
        try {
            $db = new db_mssql();
            if ($pur->page == "" && $pur->limit == "") {
                $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,device_type,u.fname as name_of_user,
                    i.customer_id,i.invoice_date,i.status as status_invoice,
                    c.short_name,full_name,group_id,group_name,pc.status as status_install,pc.install_date
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_user as u ON o.user_id = u.user_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    where pc.status=2 and i.status IN (1,2)  and p.cate_id IN ((select data from sell_invoice_prefix),(select server2 from sell_invoice_prefix)) and
                    invoice_date between '$pur->firstdate' and '$pur->lastdate' order by o.invoice_id desc ";
                $data = $db->query($sql);

                $socket = new SocketClient();
                $array = array(
                    "command" => "device_object",
                    "m" => "get_details_category",
                    "data" => [$data],
                    "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                );
                $json = json_encode($array);
                $msg = $socket->send($json);
                $result = json_decode($msg, true);
                $devno = $result['data'];

                if ($data > 0) {
                    for ($a = 0; $a < sizeof($data); $a++) {
                        $imei_ref = $data[$a]['imei_references'];
                        if ($devno > 0) {
                            $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                            if (is_numeric($ok)) {
                                $data[$a]['object_id'] = $devno[$ok]['object_id'];
                                $data[$a]['device_sim'] = $devno[$ok]['device_sim'];
                                $data[$a]['device_pass'] = $devno[$ok]['device_pass'];
                                $data[$a]['device_state'] = $devno[$ok]['device_state'];
                                $data[$a]['online'] = $devno[$ok]['online'];
                                $data[$a]['valid'] = $devno[$ok]['valid'];
                                $data[$a]['install_time'] = $devno[$ok]['install_time'];
                                $data[$a]['install_addr'] = $devno[$ok]['install_addr'];
                                $data[$a]['last_stamp'] = $devno[$ok]['last_stamp'];
                                $data[$a]['object_kind'] = $devno[$ok]['object_kind'];
                                $data[$a]['object_flag'] = $devno[$ok]['object_flag'];
                                $data[$a]['userdef_flag'] = $devno[$ok]['userdef_flag'];
                                $data[$a]['ztime'] = $devno[$ok]['ztime'];
                                $data[$a]['driver'] = $devno[$ok]['driver'];
                            }
                        }
                    }
                }
                $list = json_encode($data);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($pur->page - 1) * $pur->limit);

                $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,device_type,u.fname as name_of_user,
                    i.customer_id,i.invoice_date,i.status as status_invoice,
                    c.short_name,full_name,group_id,group_name,pc.status as status_install,pc.install_date
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_user as u ON o.user_id = u.user_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    where pc.status=2 and p.cate_id IN ((select data from sell_invoice_prefix),(select server2 from sell_invoice_prefix)) and
                    invoice_date between '$pur->firstdate' and '$pur->lastdate'";

                if (isset($pur->keyword) && $pur->keyword != "") {
                    $sql .= " and (
                             o.invoice_id like '%$pur->keyword%' or
                             imei_references like '%$pur->keyword%' or
                             p.product_name like '%$pur->keyword%' or
                             u.fname like '%$pur->keyword%' or
                             i.customer_id like '%$pur->keyword%' or
                             c.short_name like '%$pur->keyword%' or
                             c.full_name like '%$pur->keyword%' or
                             c.group_id like '%$pur->keyword%' or
                             c.group_name like '%$pur->keyword%' )";
                }
                $sql_page = "order by o.invoice_id desc offset $offset rows fetch next $pur->limit rows only  ";
                // echo $sql.$sql_page;die();
                $doquery = $db->query($sql);

                if ($doquery > 0) {
                    $count = sizeof($doquery);
                    if ($count > 0) {
                        $data = $db->query($sql . $sql_page);

                        $socket = new SocketClient();
                        $array = array(
                            "command" => "device_object",
                            "m" => "get_details_category",
                            "data" => [$data],
                            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                        );
                        $json = json_encode($array);
                        $msg = $socket->send($json);
                        $result = json_decode($msg, true);
                        $devno = $result['data'];

                        if ($data > 0) {
                            for ($a = 0; $a < sizeof($data); $a++) {
                                $imei_ref = $data[$a]['imei_references'];
                                if ($devno > 0) {
                                    $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                                    if (is_numeric($ok)) {
                                        $data[$a]['object_id'] = $devno[$ok]['object_id'];
                                        $data[$a]['device_sim'] = $devno[$ok]['device_sim'];
                                        $data[$a]['device_pass'] = $devno[$ok]['device_pass'];
                                        $data[$a]['device_state'] = $devno[$ok]['device_state'];
                                        $data[$a]['online'] = $devno[$ok]['online'];
                                        $data[$a]['valid'] = $devno[$ok]['valid'];
                                        $data[$a]['install_time'] = $devno[$ok]['install_time'];
                                        $data[$a]['install_addr'] = $devno[$ok]['install_addr'];
                                        $data[$a]['last_stamp'] = $devno[$ok]['last_stamp'];
                                        $data[$a]['object_kind'] = $devno[$ok]['object_kind'];
                                        $data[$a]['object_flag'] = $devno[$ok]['object_flag'];
                                        $data[$a]['userdef_flag'] = $devno[$ok]['userdef_flag'];
                                        $data[$a]['ztime'] = $devno[$ok]['ztime'];
                                        $data[$a]['driver'] = $devno[$ok]['driver'];
                                    }
                                }
                            }
                        }

                        $list1 = json_encode($data);
                    }
                } else {
                    $list1 = json_encode([]);
                    $count = 0;
                }

                $number_count = $count;
                $total_page = ceil($number_count / $pur->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$pur->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function serverRenewalling($pur)
    {
        try {
            $db = new db_mssql();
            if ($pur->page == "" && $pur->limit == "") {
                $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,device_type,u.fname as name_of_user,
                    i.customer_id,i.invoice_date,i.status as status_invoice,
                    c.short_name,full_name,group_id,group_name,pc.status as status_install,pc.install_date
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_user as u ON o.user_id = u.user_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    where i.status IN(1,2) and pc.status=0 and p.cate_id IN ((select data from sell_invoice_prefix),(select server2 from sell_invoice_prefix)) and
                    invoice_date between '$pur->firstdate' and '$pur->lastdate' order by o.invoice_id desc ";
                // echo $sql;die();
                $data = $db->query($sql);

                $socket = new SocketClient();
                $array = array(
                    "command" => "device_object",
                    "m" => "get_details_category",
                    "data" => [$data],
                    "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                );
                $json = json_encode($array);
                $msg = $socket->send($json);
                $result = json_decode($msg, true);
                $devno = $result['data'];

                if ($data > 0) {
                    for ($a = 0; $a < sizeof($data); $a++) {
                        $imei_ref = $data[$a]['imei_references'];
                        if ($devno > 0) {
                            $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                            if (is_numeric($ok)) {
                                $data[$a]['object_id'] = $devno[$ok]['object_id'];
                                $data[$a]['device_sim'] = $devno[$ok]['device_sim'];
                                $data[$a]['device_pass'] = $devno[$ok]['device_pass'];
                                $data[$a]['device_state'] = $devno[$ok]['device_state'];
                                $data[$a]['online'] = $devno[$ok]['online'];
                                $data[$a]['valid'] = $devno[$ok]['valid'];
                                $data[$a]['install_time'] = $devno[$ok]['install_time'];
                                $data[$a]['install_addr'] = $devno[$ok]['install_addr'];
                                $data[$a]['last_stamp'] = $devno[$ok]['last_stamp'];
                                $data[$a]['object_kind'] = $devno[$ok]['object_kind'];
                                $data[$a]['object_flag'] = $devno[$ok]['object_flag'];
                                $data[$a]['userdef_flag'] = $devno[$ok]['userdef_flag'];
                                $data[$a]['ztime'] = $devno[$ok]['ztime'];
                                $data[$a]['driver'] = $devno[$ok]['driver'];
                            }
                        }
                    }
                }
                $list = json_encode($data);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($pur->page - 1) * $pur->limit);

                $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,device_type,u.fname as name_of_user,
                    i.customer_id,i.invoice_date,i.status as status_invoice,
                    c.short_name,full_name,group_id,group_name,pc.status as status_install,pc.install_date
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_user as u ON o.user_id = u.user_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    where i.status IN(1,2) and pc.status=0 and p.cate_id IN ((select data from sell_invoice_prefix),(select server2 from sell_invoice_prefix)) and
                    invoice_date between '$pur->firstdate' and '$pur->lastdate'";

                if (isset($pur->keyword) && $pur->keyword != "") {
                    $sql .= " and (
                             o.invoice_id like '%$pur->keyword%' or
                             imei_references like '%$pur->keyword%' or
                             p.product_name like '%$pur->keyword%' or
                             u.fname like '%$pur->keyword%' or
                             i.customer_id like '%$pur->keyword%' or
                             c.short_name like '%$pur->keyword%' or
                             c.full_name like '%$pur->keyword%' or
                             c.group_id like '%$pur->keyword%' or
                             c.group_name like '%$pur->keyword%' )";
                }
                $sql_page = "order by o.invoice_id desc offset $offset rows fetch next $pur->limit rows only  ";
                // echo $sql.$sql_page;die();
                $doquery = $db->query($sql);

                if ($doquery > 0) {
                    $count = sizeof($doquery);
                    if ($count > 0) {
                        $data = $db->query($sql . $sql_page);

                        $socket = new SocketClient();
                        $array = array(
                            "command" => "device_object",
                            "m" => "get_details_category",
                            "data" => [$data],
                            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                        );
                        $json = json_encode($array);
                        $msg = $socket->send($json);
                        $result = json_decode($msg, true);
                        $devno = $result['data'];

                        if ($data > 0) {
                            for ($a = 0; $a < sizeof($data); $a++) {
                                $imei_ref = $data[$a]['imei_references'];
                                if ($devno > 0) {
                                    $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                                    if (is_numeric($ok)) {
                                        $data[$a]['object_id'] = $devno[$ok]['object_id'];
                                        $data[$a]['device_sim'] = $devno[$ok]['device_sim'];
                                        $data[$a]['device_pass'] = $devno[$ok]['device_pass'];
                                        $data[$a]['device_state'] = $devno[$ok]['device_state'];
                                        $data[$a]['online'] = $devno[$ok]['online'];
                                        $data[$a]['valid'] = $devno[$ok]['valid'];
                                        $data[$a]['install_time'] = $devno[$ok]['install_time'];
                                        $data[$a]['install_addr'] = $devno[$ok]['install_addr'];
                                        $data[$a]['last_stamp'] = $devno[$ok]['last_stamp'];
                                        $data[$a]['object_kind'] = $devno[$ok]['object_kind'];
                                        $data[$a]['object_flag'] = $devno[$ok]['object_flag'];
                                        $data[$a]['userdef_flag'] = $devno[$ok]['userdef_flag'];
                                        $data[$a]['ztime'] = $devno[$ok]['ztime'];
                                        $data[$a]['driver'] = $devno[$ok]['driver'];
                                    }
                                }
                            }
                        }

                        $list1 = json_encode($data);
                    }
                } else {
                    $list1 = json_encode([]);
                    $count = 0;
                }

                $number_count = $count;
                $total_page = ceil($number_count / $pur->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$pur->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function serverNoRenewal($pur)
    {
        try {
            $db = new db_mssql();
            if ($pur->page == "" && $pur->limit == "") {
                $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,device_type,u.fname as name_of_user,
                    i.customer_id,i.invoice_date,i.status as status_invoice,
                    c.short_name,full_name,group_id,group_name,pc.status as status_install,pc.install_date
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_user as u ON o.user_id = u.user_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    where i.status=0 and p.cate_id IN ((select data from sell_invoice_prefix),(select server2 from sell_invoice_prefix)) and
                    invoice_date between '$pur->firstdate' and '$pur->lastdate' order by o.invoice_id desc ";
                // echo $sql;die();
                $data = $db->query($sql);
                
                $socket = new SocketClient();
                $array = array(
                    "command" => "device_object",
                    "m" => "get_details_category",
                    "data" => [$data],
                    "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                );
                $json = json_encode($array);
                $msg = $socket->send($json);
                $result = json_decode($msg, true);
                $devno = $result['data'];

                if ($data > 0) {
                    for ($a = 0; $a < sizeof($data); $a++) {
                        $imei_ref = $data[$a]['imei_references'];
                        if ($devno > 0) {
                            $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                            if (is_numeric($ok)) {
                                $data[$a]['object_id'] = $devno[$ok]['object_id'];
                                $data[$a]['device_sim'] = $devno[$ok]['device_sim'];
                                $data[$a]['device_pass'] = $devno[$ok]['device_pass'];
                                $data[$a]['device_state'] = $devno[$ok]['device_state'];
                                $data[$a]['online'] = $devno[$ok]['online'];
                                $data[$a]['valid'] = $devno[$ok]['valid'];
                                $data[$a]['install_time'] = $devno[$ok]['install_time'];
                                $data[$a]['install_addr'] = $devno[$ok]['install_addr'];
                                $data[$a]['last_stamp'] = $devno[$ok]['last_stamp'];
                                $data[$a]['object_kind'] = $devno[$ok]['object_kind'];
                                $data[$a]['object_flag'] = $devno[$ok]['object_flag'];
                                $data[$a]['userdef_flag'] = $devno[$ok]['userdef_flag'];
                                $data[$a]['ztime'] = $devno[$ok]['ztime'];
                                $data[$a]['driver'] = $devno[$ok]['driver'];
                            }
                        }
                    }
                }
                $list = json_encode($data);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($pur->page - 1) * $pur->limit);

                $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,device_type,u.fname as name_of_user,
                    i.customer_id,i.invoice_date,i.status as status_invoice,
                    c.short_name,full_name,group_id,group_name,pc.status as status_install,pc.install_date
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_user as u ON o.user_id = u.user_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    where i.status=0 and p.cate_id IN ((select data from sell_invoice_prefix),(select server2 from sell_invoice_prefix)) and
                    invoice_date between '$pur->firstdate' and '$pur->lastdate'";

                if (isset($pur->keyword) && $pur->keyword != "") {
                    $sql .= " and (
                             o.invoice_id like '%$pur->keyword%' or
                             imei_references like '%$pur->keyword%' or
                             p.product_name like '%$pur->keyword%' or
                             u.fname like '%$pur->keyword%' or
                             i.customer_id like '%$pur->keyword%' or
                             c.short_name like '%$pur->keyword%' or
                             c.full_name like '%$pur->keyword%' or
                             c.group_id like '%$pur->keyword%' or
                             c.group_name like '%$pur->keyword%' )";
                }
                $sql_page = "order by o.invoice_id desc offset $offset rows fetch next $pur->limit rows only  ";
                // echo $sql.$sql_page;die();
                $doquery = $db->query($sql);

                if ($doquery > 0) {
                    $count = sizeof($doquery);
                    if ($count > 0) {
                        $data = $db->query($sql . $sql_page);

                        $socket = new SocketClient();
                        $array = array(
                            "command" => "device_object",
                            "m" => "get_details_category",
                            "data" => [$data],
                            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                        );
                        $json = json_encode($array);
                        $msg = $socket->send($json);
                        $result = json_decode($msg, true);
                        $devno = $result['data'];

                        if ($data > 0) {
                            for ($a = 0; $a < sizeof($data); $a++) {
                                $imei_ref = $data[$a]['imei_references'];
                                // print_r($devno);die();
                                if ($devno > 0) {
                                    $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                                    if (is_numeric($ok)) {
                                        $data[$a]['object_id'] = $devno[$ok]['object_id'];
                                        $data[$a]['device_sim'] = $devno[$ok]['device_sim'];
                                        $data[$a]['device_pass'] = $devno[$ok]['device_pass'];
                                        $data[$a]['device_state'] = $devno[$ok]['device_state'];
                                        $data[$a]['online'] = $devno[$ok]['online'];
                                        $data[$a]['valid'] = $devno[$ok]['valid'];
                                        $data[$a]['install_time'] = $devno[$ok]['install_time'];
                                        $data[$a]['install_addr'] = $devno[$ok]['install_addr'];
                                        $data[$a]['last_stamp'] = $devno[$ok]['last_stamp'];
                                        $data[$a]['object_kind'] = $devno[$ok]['object_kind'];
                                        $data[$a]['object_flag'] = $devno[$ok]['object_flag'];
                                        $data[$a]['userdef_flag'] = $devno[$ok]['userdef_flag'];
                                        $data[$a]['ztime'] = $devno[$ok]['ztime'];
                                        $data[$a]['driver'] = $devno[$ok]['driver'];
                                    }
                                }
                            }
                        }

                        $list1 = json_encode($data);
                    }
                } else {
                    $list1 = json_encode([]);
                    $count = 0;
                }

                $number_count = $count;
                $total_page = ceil($number_count / $pur->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$pur->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
