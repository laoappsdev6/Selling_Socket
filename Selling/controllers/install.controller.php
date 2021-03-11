
<?php

require_once "../services/services.php";
require_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
require_once "../socket/client.php";

class InstallController
{

    public function __construct()
    {
    }

    public function updateObject_id($objid, $purchase_id, $st)
    {
        try {
            $db = new db_mssql();
            $json = array("objid" => "$objid");
            $socket = new SocketClient();
            $array = array(
                "command" => "device_object",
                "m" => "getinstall_date",
                "data" => [$json],
                "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
            );
            $json = json_encode($array);
            $data = $socket->send($json);
            $result = json_decode($data, true);
            $install_data = $result['data'][0];
            $date = $install_data['install_time'];
            $sql = "
                    set @code = -1
                         update sell_purchase set object_id = '$objid', status = '2',install_date='$date' where purchase_id IN ($purchase_id)
                    set @code = 0 ";

            $sql = "declare @code int
                                    begin
                                        $sql
                                    end
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                if ($st == 1) {
                    PrintJSON("", "Install Device Ok! ", 1);
                } else if ($st == 2) {
                    PrintJSON("", "Update Device Ok! ", 1);
                } else if ($st == 3) {
                    PrintJSON("", "Change GPS Ok! ", 1);
                } else {
                    PrintJSON("", "Ok! ", 1);
                }

            } else {
                PrintJSON("", "Install Device fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function updatedata_install($purchase_id)
    {
        try {
            $db = new db_mssql();
            $sql = "
                    set @code = -1
                         update sell_purchase set status = '2' where purchase_id IN ($purchase_id)
                    set @code = 0 ";

            $sql = "declare @code int
                                    begin
                                        $sql
                                    end
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "Install data Ok! ", 1);
            } else {
                PrintJSON("", "Install data fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function get_details_all($detail)
    {
        try {
            $db = new db_mssql();

            $offset = (($detail->page - 1) * $detail->limit);

            $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.status,o.remark,last_update,quaranteed,o.user_id,
                        p.product_name,p.dtype_id,p.device_type,p.cate_id,
                        pc.device_no,pc.device_sim,
                        c.customer_id,short_name,full_name,
                        i.invoice_prefix
                        from sell_invoices_order as o
                        INNER JOIN sell_product as p ON o.product_id = p.product_id
                        INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                        INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where o.status = 1 and
                                i.status IN (1,2) and
                                pc.status = 0 ";
            if (isset($detail->keyword) && $detail->keyword != "") {
                $sql .= "and (
                                o.order_id like '%$detail->keyword%' or
                                o.invoice_id like '%$detail->keyword%' or
                                i.invoice_prefix like '%$detail->keyword%' or
                                o.purchase_id like '%$detail->keyword%' or
                                o.product_id like '%$detail->keyword%' or
                                o.purchase_id like '%$detail->keyword%' or
                                o.imei_references like '%$detail->keyword%' or
                                o.last_update like '%$detail->keyword%' or
                                o.quaranteed like '%$detail->keyword%' or
                                o.user_id like '%$detail->keyword%' or
                                p.product_name like '%$detail->keyword%' or
                                p.dtype_id like '%$detail->keyword%' or
                                p.device_type like '%$detail->keyword%' or
                                p.cate_id like '%$detail->keyword%' or
                                c.customer_id like '%$detail->keyword%' or
                                c.short_name like '%$detail->keyword%' or
                                c.full_name like '%$detail->keyword%' ) ";
            }
            $sql_page = "order by order_id desc offset $offset rows fetch next $detail->limit rows only  ";
            $doquery = $db->query($sql);

            if ($doquery > 0) {
                $count = sizeof($doquery);
                if ($count > 0) {
                    $data = $db->query($sql . $sql_page);
                    $list1 = json_encode($data);
                }
            } else {
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $detail->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$detail->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function get_details_category($detail)
    {
        try {
            $db = new db_mssql();

            $sql1 = "select imei_references from sell_invoices_order as o INNER JOIN sell_product as p ON o.product_id = p.product_id
                    where p.cate_id =(select $detail->category from sell_invoice_prefix) and o.status =1";
            $data1 = $db->query($sql1);

            $socket = new SocketClient();
            $array = array(
                "command" => "device_object",
                "m" => "get_details_category",
                "data" => [$data1],
                "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
            );
            $json = json_encode($array);
            $msg = $socket->send($json);
            $result = json_decode($msg, true);
            $devno = $result['data'];

            $offset = (($detail->page - 1) * $detail->limit);

            $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.status,o.remark,last_update,quaranteed,o.user_id,
                    p.product_name,p.dtype_id,p.device_type,p.cate_id,
                    pc.device_no,pc.device_sim as new_sim,
                    c.customer_id,short_name,full_name,
                    i.invoice_prefix
                    from sell_invoices_order as o
                    INNER JOIN sell_product as p ON o.product_id = p.product_id
                    INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                    INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    where pc.status = 0 and i.status IN(1,2) and p.cate_id=(select $detail->category from sell_invoice_prefix)
                    and imei_references IN(";
            if ($devno > 0) {
                for ($a = 0; $a < sizeof($devno); $a++) {
                    $device_no = $devno[$a]['device_no'];
                    if ($a == sizeof($devno) - 1) {
                        $sql .= "'$device_no')";
                    } else {
                        $sql .= "'$device_no',";
                    }
                }
            } else {
                PrintJSON("", "Data server is not available", 0);
                die();
            }

            if (isset($detail->keyword) && $detail->keyword != "") {
                $sql .= "and (
                                o.order_id like '%$detail->keyword%' or
                                o.invoice_id like '%$detail->keyword%' or
                                i.invoice_prefix like '%$detail->keyword%' or
                                o.purchase_id like '%$detail->keyword%' or
                                o.product_id like '%$detail->keyword%' or
                                o.purchase_id like '%$detail->keyword%' or
                                o.imei_references like '%$detail->keyword%' or
                                o.last_update like '%$detail->keyword%' or
                                o.quaranteed like '%$detail->keyword%' or
                                o.user_id like '%$detail->keyword%' or
                                p.product_name like '%$detail->keyword%' or
                                p.dtype_id like '%$detail->keyword%' or
                                p.device_type like '%$detail->keyword%' or
                                p.cate_id like '%$detail->keyword%' or
                                c.customer_id like '%$detail->keyword%' or
                                c.short_name like '%$detail->keyword%' or
                                pc.device_sim like '%$detail->keyword%' or
                                c.full_name like '%$detail->keyword%' ) ";
            }
            $sql_page = " order by order_id desc offset $offset rows fetch next $detail->limit rows only  ";
            // echo $sql . $sql_page;die();
            $doquery = $db->query($sql);

            if ($doquery > 0) {
                $count = sizeof($doquery);
                if ($count > 0) {
                    $data = $db->query($sql . $sql_page);

                    for ($a = 0; $a < sizeof($data); $a++) {
                        $imei_ref = $data[$a]['imei_references'];
                        $ok = array_search("$imei_ref", array_column($devno, 'device_no'));
                        if (is_numeric($ok)) {
                            $data[$a]['old_sim'] = $devno[$ok]['device_sim'];
                            $data[$a]['carname'] = $devno[$ok]['object_flag'];
                        }
                    }
                    $list1 = json_encode($data);
                }
            } else {
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $detail->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$detail->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function get_install_item_list($detail)
    {
        try {
            $db = new db_mssql();

            $offset = (($detail->page - 1) * $detail->limit);

            $sql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.status,o.remark,last_update,quaranteed,o.user_id,
                        p.product_name,p.dtype_id,p.device_type,p.cate_id,
                        pc.device_no,pc.device_sim,
                        c.customer_id,short_name,full_name,group_id,group_name,
                        i.invoice_prefix
                        from sell_invoices_order as o
                        INNER JOIN sell_product as p ON o.product_id = p.product_id
                        INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                        INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where o.status = 1 and
                                i.status IN (1,2) and
                                pc.status = 0 and
                                p.cate_id=(select gps from sell_invoice_prefix)";
            if (isset($detail->keyword) && $detail->keyword != "") {
                $sql .= "and (
                                o.order_id like '%$detail->keyword%' or
                                o.invoice_id like '%$detail->keyword%' or
                                i.invoice_prefix like '%$detail->keyword%' or
                                o.purchase_id like '%$detail->keyword%' or
                                o.product_id like '%$detail->keyword%' or
                                o.purchase_id like '%$detail->keyword%' or
                                o.imei_references like '%$detail->keyword%' or
                                o.last_update like '%$detail->keyword%' or
                                o.quaranteed like '%$detail->keyword%' or
                                o.user_id like '%$detail->keyword%' or
                                p.product_name like '%$detail->keyword%' or
                                p.dtype_id like '%$detail->keyword%' or
                                p.device_type like '%$detail->keyword%' or
                                p.cate_id like '%$detail->keyword%' or
                                c.customer_id like '%$detail->keyword%' or
                                c.short_name like '%$detail->keyword%' or
                                c.full_name like '%$detail->keyword%' ) ";
            }
            $sql_page = "order by order_id desc offset $offset rows fetch next $detail->limit rows only  ";
            $doquery = $db->query($sql);

            if ($doquery > 0) {
                $count = sizeof($doquery);
                if ($count > 0) {
                    $data = $db->query($sql . $sql_page);

                    for ($i = 0; $i < sizeof($data); $i++) {

                        $imei_ref = $data[$i]["device_no"];
                        $subsql = "select order_id,o.invoice_id,o.product_id,o.purchase_id,imei_references,o.sprice,o.quantity,total,o.status,o.remark,last_update,quaranteed,o.user_id,
                            p.product_name,p.dtype_id,p.device_type,p.cate_id,
                            pc.device_no,pc.device_sim,
                            c.customer_id,short_name,full_name,group_id,group_name
                            from sell_invoices_order as o
                            INNER JOIN sell_product as p ON o.product_id = p.product_id
                            INNER JOIN sell_purchase as pc ON o.purchase_id = pc.purchase_id
                            INNER JOIN sell_invoices as i ON o.invoice_id = i.invoice_id
                            INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                            where o.status = 1 and
                                    i.status IN (1,2) and
                                    pc.status = 0 and  imei_references = '$imei_ref' ";

                        $subdata = $db->query($subsql);
                        $data[$i]['sub_references'] = $subdata;
                    }
                    $list1 = json_encode($data);
                }
            } else {
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $detail->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$detail->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function installed_list($list)
    {
        try {

            $db = new db_mssql();
            for ($i = 0; $i < sizeof($list["Data"]); $i++) {
                $objid = $list["Data"][$i]["objid"];
                $cid = $list["Data"][$i]["cinfo"];
                $sql = "select quaranteed
                from sell_purchase as pc
                INNER JOIN sell_invoices_order as i ON pc.purchase_id = i.purchase_id
                INNER JOIN sell_product as p ON i.product_id = p.product_id
                where object_id ='$objid' and p.cate_id =(select gps from sell_invoice_prefix)";
                $data = $db->query($sql);

                $sql2 = "select customer_id,short_name,full_name from sell_customer where customer_id ='$cid' ";
                // echo $sql2;die();
                $data2 = $db->query($sql2);
                $list["Data"][$i]['quaranteed'] = $data[0]["quaranteed"];
                $list["Data"][$i]['customer_id'] = $data2[0]["customer_id"];
                $list["Data"][$i]['short_name'] = $data2[0]["short_name"];
                $list["Data"][$i]['full_name'] = $data2[0]["full_name"];

            }
            echo json_encode($list);
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function changeGPS($data_old, $new)
    {
        date_default_timezone_set("Asia/Vientiane");
        $expired_date = date("Y-m-d h:i:s.u");

        $object_kind = $data_old[0]['object_kind'];
        $object_flag = $data_old[0]['object_flag'];
        $userdef_flag = $data_old[0]['userdef_flag'];
        $customer_id = $data_old[0]['customer_id'];
        $group_id = $data_old[0]['group_id'];
        $password = $data_old[0]['password'];
        $time_zone = $data_old[0]['time_zone'];
        $driver = $data_old[0]['driver_job_number'];
        $remark = $data_old[0]['remark'];
        $device_no = $data_old[0]['device_no'];
        $device_sim = $data_old[0]['device_sim'];
        $device_pass = $data_old[0]['device_pass'];
        $dtype_id = $data_old[0]['dtype_id'];
        $device_state = $data_old[0]['device_state'];
        $online = $data_old[0]['online'];
        $valid = $data_old[0]['valid'];
        $install_time = $data_old[0]['install_time']['date'];
        $install_addr = $data_old[0]['install_addr'];
        $last_stamp = $data_old[0]['last_stamp']['date'];
        $object_id = $data_old[0]['object_id'];

        $json = array(
            "objid" => "$object_id",
            "dtype" => "$dtype_id",
            "dstate" => "$device_state",
            "devno" => "$device_no",
            "simno" => "$device_no",
            "dpass" => "$device_pass",
            "stamp" => "$install_time",
            "iaddr" => "$install_addr",
            "estamp" => "$expired_date",
            "cinfo" => "$customer_id",
            "ginfo" => "$group_id",
            "okind" => "$object_kind",
            "oflag" => "$object_flag",
            "uflag" => "$userdef_flag",
            "ztime" => "$time_zone",
            "driver" => "$driver",
            "remark" => "$new->remark",
        );
        // echo json_encode($json);
        $socket = new SocketClient();
        $array = array(
                        "command"=>"device_object",
                        "m"=>"updateobject",
                        "data"=>[$json],
                        "token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E"
                        );
        $json = json_encode($array);     
        $result = $socket->send($json);
        echo $result;

        $json = array(
            "purchase_id" => "$new->purchase_id",
            "dtype" => "$dtype_id",
            "dstate" => "$device_state",
            "devno" => "$new->new_devno",
            "simno" => "$device_sim",
            "dpass" => "$device_pass",
            "stamp" => "$install_time",
            "iaddr" => "$install_addr",
            "estamp" => "$last_stamp",
            "cinfo" => "$customer_id",
            "ginfo" => "$group_id",
            "okind" => "$object_kind",
            "oflag" => "$object_flag",
            "uflag" => "$userdef_flag",
            "ztime" => "$time_zone",
            "driver" => "$driver",
            "remark" => "$remark",
        );
        // echo json_encode($json);die();
        $socket = new SocketClient();
        $array = array(
                        "command"=>"device_object",
                        "m"=>"addobject",
                        "data"=>[$json],
                        "token"=>"eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E"
                        );
        $json = json_encode($array);     
        $result = $socket->send($json);
        echo $result;
    }
}
