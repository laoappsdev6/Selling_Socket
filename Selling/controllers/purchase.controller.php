
<?php

include "../services/services.php";
include_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';

class PurchaseController
{

    public function __construct()
    {
    }
    public function addPurchase($pur)
    {
        try {
            date_default_timezone_set("Asia/Vientiane");
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $purchase_date = date("Y-m-d");
            $subsql = "
                        set @code = -1
                        insert into sell_purchase (product_id, device_no,device_sim,bprice,quantity,amount,purchase_date,status,remark, user_id)
                            values ";

            for ($i = 0; $i < sizeof($pur); $i++) {
                $product_id = $pur[$i]['product_id'];
                $device_no = $pur[$i]['device_no'];
                $device_sim = $pur[$i]['device_sim'];
                $bprice = $pur[$i]['bprice'];
                $quantity = $pur[$i]['quantity'];
                $amount = $pur[$i]['amount'];
                $remark = $pur[$i]['remark'];

                if ($i == sizeof($pur) - 1) {
                    $subsql .= "($product_id,'$device_no','$device_sim','$bprice',$quantity,'$amount','$purchase_date',1, N'$remark',$user_id)
                                         set @code = -2 ";
                } else {
                    $subsql .= "($product_id,'$device_no','$device_sim','$bprice',$quantity,'$amount','$purchase_date',1, N'$remark',$user_id),";
                }
            }

            for ($a = 0; $a < sizeof($pur); $a++) {
                $product_id = $pur[$a]['product_id'];
                $quantity = $pur[$a]['quantity'];

                if ($a == sizeof($pur) - 1) {
                    $subsql .= "update sell_product set quantity = quantity + $quantity where product_id='$product_id'
                                set @code = 0";
                } else {
                    $subsql .= "  update sell_product set quantity = quantity + $quantity where product_id='$product_id'";
                }
            }

            // echo $subsql;
            // die();
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
                PrintJSON("", "add purchase Ok! ", 1);
            } else {
                PrintJSON("", "add purchase fail! error: " . $error_code . $subsql, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }

    public function purchaseList($pur)
    {
        try {
            $db = new db_mssql();

            $offset = (($pur->page - 1) * $pur->limit);

            if ($pur->firstdate == "" && $pur->lastdate == "") {

                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                ";

                if (isset($pur->keyword) && $pur->keyword != "") {
                    $sql .= " where
                                c.purchase_id like '%$pur->keyword%' or
                                p.product_name like '%$pur->keyword%' or
                                p.product_id like '%$pur->keyword%' or
                                c.device_no like '%$pur->keyword%' or
                                c.device_sim like '%$pur->keyword%' or
                                c.bprice like '%$pur->keyword%' or
                                c.quantity like '%$pur->keyword%' or
                                c.amount like '%$pur->keyword%' or
                                c.purchase_date like '%$pur->keyword%' or
                                c.status like '%$pur->keyword%' or
                                c.remark like '%$pur->keyword%'";
                }

            } else {

                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where  c.purchase_date between '$pur->firstdate' and '$pur->lastdate' ";

                if (isset($pur->keyword) && $pur->keyword != "") {
                    $sql .= " and (
                                     c.purchase_id like '%$pur->keyword%' or
                                     p.product_name like '%$pur->keyword%' or
                                     p.product_id like '%$pur->keyword%' or
                                     c.device_no like '%$pur->keyword%' or
                                     c.device_sim like '%$pur->keyword%' or
                                     c.bprice like '%$pur->keyword%' or
                                     c.quantity like '%$pur->keyword%' or
                                     c.amount like '%$pur->keyword%' or
                                     c.purchase_date like '%$pur->keyword%' or
                                     c.status like '%$pur->keyword%' or
                                     c.remark like '%$pur->keyword%' )";
                }
            }
            $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $pur->limit rows only  ";

            // echo $sql;die();
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
            $total_page = ceil($number_count / $pur->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$pur->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListGPS($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where p.cate_id=(select gps from sell_invoice_prefix) and c.status = 1 order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where p.cate_id=(select gps from sell_invoice_prefix) and c.status = 1 ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' )";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                //  echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListSIM($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where p.cate_id=(select sim from sell_invoice_prefix) and c.status  IN (1,3)  order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where p.cate_id=(select sim from sell_invoice_prefix) and c.status IN (1,3) ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' ) ";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                // echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }

    public function ListSIM_second($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where p.cate_id=(select sim from sell_invoice_prefix) and c.status=3  order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where p.cate_id=(select sim from sell_invoice_prefix) and c.status=3 ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' ) ";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                // echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListSERVER($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where p.cate_id=(select server from sell_invoice_prefix) and c.status IN (1,3)  order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where p.cate_id=(select server from sell_invoice_prefix) and c.status IN (1,3) ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' ) ";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                // echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListSERVER2($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where p.cate_id=(select server from sell_invoice_prefix) and c.status IN (1,3)  order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where p.cate_id=(select server2 from sell_invoice_prefix) and c.status IN (1,3) ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' ) ";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                // echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListDATA($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where p.cate_id=(select data from sell_invoice_prefix) and c.status IN (1,3)  order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where p.cate_id=(select data from sell_invoice_prefix) and c.status IN (1,3) ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' ) ";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                // echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function ListAll($gps)
    {
        try {
            $db = new db_mssql();
            if ($gps->page == "" && $gps->limit == "") {
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.remark,c.install_date,c.user_id
                from sell_purchase as c
                INNER JOIN sell_product as p ON c.product_id = p.product_id
                where  c.status IN (1,3)  order by purchase_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($gps->page - 1) * $gps->limit);
                $sql = "select c.purchase_id,p.product_name,p.product_id,p.cate_id,p.device_type,c.device_no,c.device_sim,c.bprice,p.sprice,c.quantity,c.amount,c.purchase_date,c.status,c.remark,c.install_date,c.user_id
                    from sell_purchase as c
                    INNER JOIN sell_product as p ON c.product_id = p.product_id
                    where  c.status IN (1,3) ";

                if (isset($gps->keyword) && $gps->keyword != "") {
                    $sql .= " and (
                                    c.purchase_id like '%$gps->keyword%' or
                                    p.product_name like '%$gps->keyword%' or
                                    p.product_id like '%$gps->keyword%' or
                                    c.device_no like '%$gps->keyword%' or
                                    c.device_sim like '%$gps->keyword%' or
                                    c.bprice like '%$gps->keyword%' or
                                    c.quantity like '%$gps->keyword%' or
                                    c.amount like '%$gps->keyword%' or
                                    c.purchase_date like '%$gps->keyword%' or
                                    c.status like '%$gps->keyword%' or
                                    c.remark like '%$gps->keyword%' ) ";
                }
                $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $gps->limit rows only  ";

                // echo $sql;die();
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
                $total_page = ceil($number_count / $gps->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$gps->page,
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
