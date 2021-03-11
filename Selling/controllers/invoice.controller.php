
<?php

require_once "../services/services.php";
require_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
require_once "../socket/client.php";

class InvoiceController
{

    public function __construct()
    {
    }
    public function addOrder($invoice, $order)
    {
        try {
            date_default_timezone_set("Asia/Vientiane");
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $invoice_date = date("Y-m-d");
            $subsql = "
                   declare @ID    int
                        set @code = -1
                          insert into sell_invoices (invoice_prefix,discount,tax,amount,customer_id,invoice_date,status,remark,pay_by,user_id)
                            values ('','$invoice->discount','$invoice->tax','$invoice->amount',$invoice->customer_id,'$invoice_date',0,N'$invoice->remark','',$user_id)

                         set @ID = @@IDENTITY

                         set @code = -3
                            insert into sell_invoices_order (invoice_id,product_id,purchase_id,imei_references,sprice,quantity,total,status,remark,last_update,quaranteed,user_id)
                                values ";

            for ($i = 0; $i < sizeof($order); $i++) {
                $product_id = $order[$i]['product_id'];
                $purchase_id = $order[$i]['purchase_id'];
                $imei_references = $order[$i]['imei_references'];
                $sprice = $order[$i]['sprice'];
                $quantity = $order[$i]['quantity'];
                $total = $order[$i]['total'];
                $quaranteed = $order[$i]['quaranteed'];
                $remark = $order[$i]['remark'];
                if ($i == sizeof($order) - 1) {
                    $subsql .= "(@ID,$product_id,$purchase_id,'$imei_references','$sprice',$quantity,'$total','0',N'$remark','$invoice_date',N'$quaranteed',$user_id)
                      set @code = 0";
                } else {
                    $subsql .= "(@ID,$product_id,$purchase_id,'$imei_references','$sprice',$quantity,'$total','0',N'$remark','$invoice_date',N'$quaranteed',$user_id),";
                }
            }
            //   echo $subsql;die();
            $sql = "declare @code int
                                    begin try
                                        begin tran
                                        $subsql
                                        commit tran
                                    end try
                                    begin catch
                                        rollback tran
                                    end catch
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "add  order Ok! ", 1);
            } else {
                PrintJSON("", "add  order fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function Dispose($invoice, $order)
    {
        try {
            $db = new db_mssql();
            date_default_timezone_set("Asia/Vientiane");
            $user_id = $_SESSION["uid"];
            $invoice_date = date("Y-m-d");
            $subsql = "
                   declare @ID    int
                        set @code = -1
                          insert into sell_invoices (invoice_prefix,discount,tax,amount,customer_id,invoice_date,status,remark,pay_by,user_id)
                            values ('','0','0','$invoice->amount',0,'$invoice_date',5,N'$invoice->remark','', $user_id)

                         set @ID = @@IDENTITY

                         set @code = -3
                            insert into sell_invoices_order (invoice_id,product_id,purchase_id,imei_references,sprice,quantity,total,status,remark,last_update,quaranteed,user_id)
                                values ";

            for ($i = 0; $i < sizeof($order); $i++) {
                $product_id = $order[$i]['product_id'];
                $purchase_id = $order[$i]['purchase_id'];
                $imei_references = $order[$i]['imei_references'];
                $sprice = $order[$i]['sprice'];
                $quantity = $order[$i]['quantity'];
                $total = $order[$i]['total'];
                // $quaranteed = $order[$i]['quaranteed'];
                $remark = $order[$i]['remark'];
                if ($i == sizeof($order) - 1) {
                    $subsql .= "(@ID,$product_id,$purchase_id,'$imei_references','$sprice',$quantity,'$total','5',N'$remark','$invoice_date','',$user_id)
                      set @code = -4";
                } else {
                    $subsql .= "(@ID,$product_id,$purchase_id,'$imei_references','$sprice',$quantity,'$total','5',N'$remark','$invoice_date','',$user_id),";
                }
            }
            for ($a = 0; $a < sizeof($order); $a++) {
                $product_id = $order[$a]['product_id'];
                $quantity = $order[$a]['quantity'];

                if ($a == sizeof($order) - 1) {
                    $subsql .= "
                            update sell_product set quantity = quantity - $quantity where product_id='$product_id'
                                set @code = -5 ";
                } else {
                    $subsql .= "
                             update sell_product set quantity = quantity - $quantity where product_id='$product_id'";
                }
            }

            $subsql .= "
                        update sell_purchase set status = 5 where purchase_id IN (";

            for ($a = 0; $a < sizeof($order); $a++) {
                $purchase_id = $order[$a]['purchase_id'];
                if ($a == sizeof($order) - 1) {
                    $subsql .= "$purchase_id)
                    set @code = 0";
                } else {
                    $subsql .= "$purchase_id,";
                }
            }

            // echo $subsql; die();
            $sql = "declare @code int
                                    begin try
                                        begin tran
                                        $subsql
                                        commit tran
                                    end try
                                    begin catch
                                        rollback tran
                                    end catch
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "sell dispose Ok! ", 1);
            } else {
                PrintJSON("", "sell dispose order fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function disposeList($inv)
    {
        try {
            $db = new db_mssql();

            $offset = (($inv->page - 1) * $inv->limit);

            $sql = "select invoice_id,invoice_prefix,discount,tax,amount,invoice_date,status,remark,pay_by,user_id
                        from sell_invoices
                        where  status =5 ";
            if (isset($inv->keyword) && $inv->keyword != "") {
                $sql .= "and (
                                invoice_id like '%$inv->keyword%' or
                                discount like '%$inv->keyword%' or
                                tax like '%$inv->keyword%' or
                                amount like '%$inv->keyword%' or
                                full_name like '%$inv->keyword%' or
                                invoice_date like '%$inv->keyword%' or
                                status like '%$inv->keyword%' or
                                remark like '%$inv->keyword%' or
                                pay_by like '%$inv->keyword%' or
                                user_id like '%$inv->keyword%' or
                                invoice_prefix like '%$inv->keyword%' ) ";
            }
            $sql_page = "order by invoice_id desc offset $offset rows fetch next $inv->limit rows only  ";
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
            $total_page = ceil($number_count / $inv->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$inv->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function Setting($prefix)
    {
        try {
            $db = new db_mssql();
            $sql1 = "
                    set @code = -1
                         update sell_invoice_prefix set title='$prefix->title',number='$prefix->number',gps='$prefix->gps',sim='$prefix->sim',server='$prefix->server',data='$prefix->data',server2='$prefix->server2'
                            set @code =0
                        ";
            $sql = "declare @code int
                        begin
                            $sql1
                        end
                        select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "setting Ok! ", 1);
            } else {
                PrintJSON("", "setting  fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function Settinglsit()
    {
        try {
            $db = new db_mssql();
            $sql1 = "select * from sell_invoice_prefix";
            $data = $db->query($sql1);
            echo json_encode($data);
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function Invoice($id)
    {
        try {
            $db = new db_mssql();
            $max = "select * from sell_invoice_prefix";
            $data = $db->query($max);
            if ($data[0]['number'] == 0) {
                $num = 0;
            } else {
                $num = $data[0]['number'];
            }
            $number = $num + 1;

            $invoice_prefix = $data[0]['title'] . $number;

            // $sql

            $sql1 = "select product_id,purchase_id,quantity,order_id from sell_invoices_order where invoice_id='$id->invoice_id'";
            $order = $db->query($sql1);
            $sql = "
                    set @code = -1
                        update sell_invoices set invoice_prefix = '$invoice_prefix', status= 1 where invoice_id = $id->invoice_id

                    set @code = -2
                        update sell_invoice_prefix set number = $number

                    set @code = -3
                    update sell_purchase set status = 0 where purchase_id IN (";

            for ($a = 0; $a < sizeof($order); $a++) {
                $purchase_id = $order[$a]['purchase_id'];
                if ($a == sizeof($order) - 1) {
                    $sql .= "$purchase_id)
                            set @code = -4";
                } else {
                    $sql .= "$purchase_id,";
                }
            }
            $sql .= "
                    update sell_invoices_order set status = 1 where order_id IN (";
            for ($a = 0; $a < sizeof($order); $a++) {
                $order_id = $order[$a]['order_id'];
                if ($a == sizeof($order) - 1) {
                    $sql .= "$order_id)
                            set @code = -5";
                } else {
                    $sql .= "$order_id,";
                }
            }
            for ($a = 0; $a < sizeof($order); $a++) {
                $product_id = $order[$a]['product_id'];
                $quantity = $order[$a]['quantity'];

                if ($a == sizeof($order) - 1) {
                    $sql .= "
                            update sell_product set quantity = quantity - $quantity where product_id='$product_id'
                                set @code = 0";
                } else {
                    $sql .= "
                             update sell_product set quantity = quantity - $quantity where product_id='$product_id'";
                }
            }
            // echo $sql;die();
            $squery = "declare @code int
                            begin try
                                begin tran
                                $sql
                                commit tran
                            end try
                            begin catch
                                rollback tran
                            end catch
                            select @code as errcode";

            $data = $db->queryLastDS($squery);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "issure an invoice Ok! ", 1);
            } else {
                PrintJSON("", "issure an invoice fail! error: " . $error_code, 0);
                die();
            }

        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function rePay($invoice, $order)
    {
        try {
            date_default_timezone_set("Asia/Vientiane");
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $invoice_date = date("Y-m-d");
            $subsql = "
                   declare @ID    int
                        set @code = -1
                          insert into sell_invoices (invoice_prefix,invoice_references,prefix_references,discount,tax,amount,customer_id,invoice_date,status,remark,pay_by,user_id)
                            values ('','$invoice->invoice_references','$invoice->prefix_references','0','0','$invoice->amount',$invoice->customer_id,'$invoice_date',3,N'$invoice->remark','',$user_id)

                         set @ID = @@IDENTITY

                         set @code = -2
                            insert into sell_invoices_order (invoice_id,product_id,purchase_id,imei_references,sprice,quantity,total,status,remark,last_update,quaranteed,user_id)
                                values ";

            $subsql1 = "";
            $subsql2 = "";
            for ($i = 0; $i < sizeof($order); $i++) {
                $product_id = $order[$i]['product_id'];
                $purchase_id = $order[$i]['purchase_id'];
                $imei_references = $order[$i]['imei_references'];
                $sprice = $order[$i]['sprice'];
                $quantity = $order[$i]['quantity'];
                $total = $order[$i]['total'];
                $quaranteed = $order[$i]['quaranteed'];
                $remark = $order[$i]['remark'];

                $cate_id = $order[$i]['cate_id'];
                $objid = $order[$i]['object_id'];
                $status_install = $order[$i]['status_install'];

                if ($i == sizeof($order) - 1) {
                    $subsql .= "(@ID,$product_id,$purchase_id,'$imei_references','$sprice',$quantity,'$total','3',N'$remark','$invoice_date',N'$quaranteed',$user_id)
                      set @code = -3";
                } else {
                    $subsql .= "(@ID,$product_id,$purchase_id,'$imei_references','$sprice',$quantity,'$total','3',N'$remark','$invoice_date',N'$quaranteed',$user_id),";
                }

                $sql1 = "select sim from sell_invoice_prefix";
                $sim = $db->query($sql1);

                $object_id = array("objid" => "$objid");

                if ($cate_id == $sim[0]['sim']) {

                    if ($status_install == 2) {
                        $socket = new SocketClient();
                        $array = array(
                            "command" => "device_object",
                            "m" => "deletedevice",
                            "data" => [$object_id],
                            "token" => "eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJsYW9hcHBzLmNvbSIsImF1ZCI6Imp3dC5sYW9hcHBzLmNvbSIsImlhdCI6MTM1Njk5OTUyNCwibmJmIjoxMzU3MDAwMDAwLCJkYXRhIjp7InVpZCI6MSwidW5hbWUiOiJhZG1pbmlzdHJhdG9yMSIsInBhc3MiOiIyMjIyMjIiLCJlbWFpbCI6IiIsInJ0aW1lIjoiMDU6MzMiLCJybWFpbCI6MSwibXR5cGUiOiIwXzFfMSwxXzBfMSwyXzBfMSwwXzBfMiwwXzBfMywwXzFfNCwwXzBfMTIsMF8wXzEzLDBfMV8xNCIsInZhbGlkIjoxLCJybmFtZSI6IkFkbWluaXN0cmF0b3IiLCJsYXQiOjE3OTY0NjUwLCJsbmciOjEwMjYwNzE1MCwiZGF0ZV9mbXQiOiJ5eXl5LU1NLWRkIiwidGltZV9mbXQiOiJISDptbTpzcyIsInNvbmRfYWxhcm0iOjAsInBvcHVwX2FsYXJtIjowLCJ1ZCI6MCwidWYiOjAsInV0IjowLCJ1cyI6MCwiY2xpZW50X3RpbWVfem9uZSI6MCwibGFuZyI6bnVsbCwib2tpbmQiOjY0fSwidXBkYXRldGltZSI6MTYwNjM3NDc3NDM4NTF9.44IK-AsZJtqOhYURTfUgiOhhybhKzKLlIjBINZ9tz-E",
                        );
                        $json = json_encode($array);
                        $data = $socket->send($json);
                        // echo $data;

                        $subsql1 .= "
                        update sell_purchase set status ='3',remark =N'ຊິມມືສອງ' where purchase_id ='$purchase_id' ";
                    } else {
                        $subsql1 .= "
                        update sell_purchase set status ='1' where purchase_id ='$purchase_id'";
                    }

                } else {
                    $subsql1 .= "
                            update sell_purchase set status ='1' where purchase_id ='$purchase_id' \n ";
                }

                if ($i == sizeof($order) - 1) {
                    $subsql2 .= "
                        update sell_product set quantity = quantity + $quantity where product_id='$product_id'";
                } else {
                    $subsql2 .= "
                        set @code = -4
                         update sell_product set quantity = quantity + $quantity where product_id='$product_id'";
                }

            }
            $subsql2 .= "
                        set @code = -6
                        update sell_invoices_order set status = 3 where order_id IN(";
            for ($a = 0; $a < sizeof($order); $a++) {
                $order_id = $order[$a]['order_id'];
                if ($a == sizeof($order) - 1) {
                    $subsql2 .= "$order_id)
                                         set @code = 0";
                } else {
                    $subsql2 .= "$order_id,";
                }
            }
            $allsql = $subsql . $subsql1 . $subsql2;
            // echo $allsql;die();
            $sql = "declare @code int
                                    begin try
                                        begin tran
                                        $allsql
                                        commit tran
                                    end try
                                    begin catch
                                        rollback tran
                                    end catch
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "cancel invoice Ok! ", 1);
            } else {
                PrintJSON("", "cancel invoice fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function repayList($inv)
    {
        try {
            $db = new db_mssql();

            $offset = (($inv->page - 1) * $inv->limit);

            $sql = "select invoice_id,invoice_prefix,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                        from sell_invoices as i
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where  status = 3 ";
            if (isset($inv->keyword) && $inv->keyword != "") {
                $sql .= "and (
                                invoice_id like '%$inv->keyword%' or
                                discount like '%$inv->keyword%' or
                                tax like '%$inv->keyword%' or
                                amount like '%$inv->keyword%' or
                                i.customer_id like '%$inv->keyword%' or
                                c.short_name like N'%$inv->keyword%' or
                                c.full_name like N'%$inv->keyword%' or
                                invoice_date like '%$inv->keyword%' or
                                i.status like '%$inv->keyword%' or
                                i.remark like '%$inv->keyword%' or
                                pay_by like '%$inv->keyword%' or
                                i.user_id like '%$inv->keyword%' or
                                invoice_prefix like '%$inv->keyword%' ) ";
            }
            $sql_page = "order by invoice_id desc offset $offset rows fetch next $inv->limit rows only  ";
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
            $total_page = ceil($number_count / $inv->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$inv->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function Invoicelist($inv)
    {
        try {
            $db = new db_mssql();

            if ($inv->page == "" && $inv->limit == "") {
                $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                         from sell_invoices as i
                         INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                         where  status != 5 order by invoice_id desc  ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($inv->page - 1) * $inv->limit);

                $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                        from sell_invoices as i
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where  status != 5 ";
                if (isset($inv->keyword) && $inv->keyword != "") {
                    $sql .= "and (
                                invoice_id like '%$inv->keyword%' or
                                discount like '%$inv->keyword%' or
                                tax like '%$inv->keyword%' or
                                amount like '%$inv->keyword%' or
                                i.customer_id like '%$inv->keyword%' or
                                c.short_name like N'%$inv->keyword%' or
                                c.full_name like N'%$inv->keyword%' or
                                invoice_date like '%$inv->keyword%' or
                                i.status like '%$inv->keyword%' or
                                i.remark like '%$inv->keyword%' or
                                pay_by like '%$inv->keyword%' or
                                i.user_id like '%$inv->keyword%' or
                                invoice_prefix like '%$inv->keyword%' ) ";
                }
                $sql_page = "order by invoice_id desc offset $offset rows fetch next $inv->limit rows only  ";
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
                $total_page = ceil($number_count / $inv->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$inv->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function getInvoice($inv)
    {
        try {
            $db = new db_mssql();
            $sql1 = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                    from sell_invoices as i
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    where  status !=5  and invoice_id='$inv->invoice_id'";
            $data1 = $db->query($sql1);
            $list1 = json_encode($data1[0]);

            $sql2 = "select order_id,invoice_id,p.product_id,p.product_name,p.dtype_id,p.device_type,c.device_sim,p.cate_id,p.device_type,i.purchase_id,c.device_no,imei_references,i.sprice,i.quantity,total,i.status,last_update,quaranteed,i.remark,i.user_id,
                    c.status as status_install,c.object_id
                    from sell_invoices_order as i
                    INNER JOIN sell_product as p ON i.product_id = p.product_id
                    INNER JOIN sell_purchase as c ON i.purchase_id = c.purchase_id
                    where i.status!=5 and  invoice_id='$inv->invoice_id' ";
            $data2 = $db->query($sql2);
            $list2 = json_encode($data2);

            $json = "{ \"invoice\":$list1,
                      \"invoice_order\":$list2
                      }";
            echo $json;

        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function Payment($inv)
    {
        try {
            $db = new db_mssql();
            $sql = "
                    set @code = -1
                    update sell_invoices set status = 2, pay_by=N'$inv->pay_by' where invoice_id='$inv->invoice_id'
                    set @code = 0
                    ";
            $sqlquery = "declare @code int
                            begin
                                $sql
                            end
                            select @code as errcode";

            $data = $db->queryLastDS($sqlquery);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "pay ment Ok! ", 1);
            } else {
                PrintJSON("", "pay ment fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function getInstall($inv)
    {
        try {
            $db = new db_mssql();
            $sql1 = "select invoice_id,invoice_prefix,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                    from sell_invoices as i
                    INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                    where  status in (1,2) and invoice_id='$inv->invoice_id'";
            $data1 = $db->query($sql1);
            $list1 = json_encode($data1[0]);

            $sql2 = "select order_id,invoice_id,p.product_id,p.product_name,p.dtype_id,p.device_type,c.device_sim,p.cate_id,p.device_type,i.purchase_id,c.device_no,c.object_id,imei_references,i.sprice,i.quantity,total,i.status,last_update,quaranteed,i.remark,i.user_id
                    from sell_invoices_order as i
                    INNER JOIN sell_product as p ON i.product_id = p.product_id
                    INNER JOIN sell_purchase as c ON i.purchase_id = c.purchase_id
                    where c.status = 0 and  invoice_id='$inv->invoice_id' ";
            $data2 = $db->query($sql2);
            $list2 = json_encode($data2);

            $json = "{ \"invoice\":$list1,
                      \"invoice_order\":$list2
                      }";
            echo $json;

        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListInstall($inv)
    {
        try {
            $db = new db_mssql();

            if ($inv->page == "" && $inv->limit == "") {
                $sql = "select invoice_id,invoice_prefix,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                         from sell_invoices as i
                         INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                         where  status in (1,2) order by invoice_id desc  ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($inv->page - 1) * $inv->limit);

                $sql = "select invoice_id,invoice_prefix,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                        from sell_invoices as i
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where  status in(1,2) ";
                if (isset($inv->keyword) && $inv->keyword != "") {
                    $sql .= "and (
                                invoice_id like '%$inv->keyword%' or
                                discount like '%$inv->keyword%' or
                                tax like '%$inv->keyword%' or
                                amount like '%$inv->keyword%' or
                                i.customer_id like '%$inv->keyword%' or
                                c.short_name like N'%$inv->keyword%' or
                                c.full_name like N'%$inv->keyword%' or
                                invoice_date like '%$inv->keyword%' or
                                i.status like '%$inv->keyword%' or
                                i.remark like '%$inv->keyword%' or
                                pay_by like '%$inv->keyword%' or
                                i.user_id like '%$inv->keyword%' or
                                invoice_prefix like '%$inv->keyword%' ) ";
                }
                $sql_page = "order by invoice_id desc offset $offset rows fetch next $inv->limit rows only  ";
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
                $total_page = ceil($number_count / $inv->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$inv->page,
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
