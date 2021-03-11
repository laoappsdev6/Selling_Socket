
<?php

include "../services/services.php";
include_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';

class DataController
{
    public function __construct()
    {
    }
    public function addDataPurchase($dtsim, $detail)
    {
        try {
            date_default_timezone_set("Asia/Vientiane");
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $data_date = date("Y-m-d");
            $subsql = "
                   declare @ID    int
                        set @code = -1
                          insert into sell_data_purchase (supplier,total,date,status,remark,user_id)
                            values (N'$dtsim->supplier','$dtsim->total','$data_date','0',N'$dtsim->remark',$user_id)

                         set @ID = @@IDENTITY

                         set @code = -3
                            insert into sell_data_detail (data_id,product_id,device_sim,quantity,price,total,date,status,remark,user_id)
                                values ";

            for ($i = 0; $i < sizeof($detail); $i++) {
                $product_id = $detail[$i]['product_id'];
                $device_sim = $detail[$i]['device_sim'];
                $quantity = $detail[$i]['quantity'];
                $price = $detail[$i]['price'];
                $total = $detail[$i]['total'];
                $remark = $detail[$i]['remark'];

                if ($i == sizeof($detail) - 1) {
                    $subsql .= "(@ID,$product_id,'$device_sim',$quantity,'$price','$total','$data_date','0',N'$remark',$user_id)
                      set @code = 0";
                } else {
                    $subsql .= "(@ID,$product_id,'$device_sim',$quantity,'$price','$total','$data_date','0',N'$remark',$user_id),";
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

                                    select @code as errcode,  @ID as last_id";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];
            $last_id = $data[0]['last_id'];
            if (!is_null($error_code) && $error_code == 0) {
                echo json_encode(array("data_id" => "$last_id", "message" => "add data Ok", "status" => "1"));
            } else {
                PrintJSON("", "add  data fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function updateDataPurchase($dtsim, $detail)
    {
        try {
            date_default_timezone_set("Asia/Vientiane");
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $data_date = date("Y-m-d");
            $subsql = "
                   declare @ID    int
                        set @code = -1
                            update sell_data_purchase set supplier='$dtsim->supplier',total='$dtsim->total',remark=N'$dtsim->remark' where data_id='$dtsim->data_id'

                         set @code = -3
                         ";

            for ($i = 0; $i < sizeof($detail); $i++) {

                $product_id = $detail[$i]['product_id'];
                $device_sim = $detail[$i]['device_sim'];
                $quantity = $detail[$i]['quantity'];
                $price = $detail[$i]['price'];
                $total = $detail[$i]['total'];
                $status = $detail[$i]['status'];
                $remark = $detail[$i]['remark'];

                if (!isset($detail[$i]['detail_id']) || $detail[$i]['detail_id'] == "") {

                    $subsql .= " insert into sell_data_detail (data_id,product_id,device_sim,quantity,price,total,date,status,remark,user_id)
                                values ($dtsim->data_id,$product_id,'$device_sim',$quantity,'$price','$total','$data_date','0',N'$remark',$user_id)";
                  
                } else {
                    $detail_id = $detail[$i]['detail_id'];
                    if ($i == sizeof($detail) - 1) {

                        $subsql .= "update sell_data_detail set product_id='$product_id',device_sim='$device_sim',quantity='$quantity',price='$price',total='$total',last_update='$data_date',status='$status',remark=N'$remark',user_id='$user_id' where detail_id='$detail_id'";
                    } else {
                        $subsql .= "update sell_data_detail set product_id='$product_id',device_sim='$device_sim',quantity='$quantity',price='$price',total='$total',last_update='$data_date',status='$status',remark=N'$remark',user_id='$user_id' where detail_id='$detail_id' ";
                    }
                }

            }
            $subsql .= "set @code = 0";
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
                PrintJSON("", "update  data Ok! ", 1);
            } else {
                PrintJSON("", "update  data fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function datalist($inv)
    {
        try {
            $db = new db_mssql();

            if ($inv->page == "" && $inv->limit == "") {
                $sql = "select data_id,supplier,data_references,total,date,dp.status,dp.remark,image,dp.user_id,u.fname
                from sell_data_purchase dp
                INNER JOIN sell_user as u ON dp.user_id = u.user_id
                order by data_id desc   ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($inv->page - 1) * $inv->limit);

                $sql = "select data_id,supplier,data_references,total,date,dp.status,dp.remark,image,dp.user_id,u.fname
                from sell_data_purchase dp
                INNER JOIN sell_user as u ON dp.user_id = u.user_id ";
                if (isset($inv->keyword) && $inv->keyword != "") {
                    $sql .= "where
                                data_id like '%$inv->keyword%' or
                                supplier like '%$inv->keyword%' or
                                data_references like '%$inv->keyword%' or
                                total like '%$inv->keyword%' or
                                date like '%$inv->keyword%' or
                                dp.status like '%$inv->keyword%' or
                                dp.remark like '%$inv->keyword%' or
                                dp.user_id like '%$inv->keyword%'  ";
                }
                $sql_page = "order by data_id desc offset $offset rows fetch next $inv->limit rows only  ";
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
    public function getDataList_one($data_id)
    {
        try {
            $db = new db_mssql();
            $sql1 = "select data_id,supplier,data_references,total,date,dp.status,dp.remark,image,dp.user_id,u.fname
            from sell_data_purchase dp
            INNER JOIN sell_user as u ON dp.user_id = u.user_id
            where  data_id='$data_id'";
            $data1 = $db->query($sql1);
            $list1 = json_encode($data1[0]);
            $sql2 = "select detail_id,data_id,dd.product_id,p.product_name,device_sim,dd.quantity,price,total,date,dd.status,last_update,dd.remark,dd.user_id,u.fname
                    from sell_data_detail as dd
                    INNER JOIN sell_product as p ON dd.product_id = p.product_id
                    INNER JOIN sell_user as u ON dd.user_id = u.user_id
                    where  data_id='$data_id' ";
            $data2 = $db->query($sql2);
            $list2 = json_encode($data2);

            $json = "{ \"data\":$list1,
                      \"data_detail\":$list2
                      }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function purchaseData($dt)
    {
        try {

            $db = new db_mssql();
            $subsql = "
                        set @code = -1
                            update sell_data_purchase set status = 1 where data_id = '$dt->data_id'
                        set @code = 0";
            // echo $subsql;die();
            $sql = "declare @code int
                                    begin
                                        $subsql
                                    end
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "purchase data Ok! ", 1);
            } else {
                PrintJSON("", "purchase data fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function paymentData($dt)
    {
        try {

            if (isset($dt->data) && $dt->image != "") {
                $type = explode('/', explode(';', $dt->image)[0])[1];
                $p = preg_replace('#^data:image/\w+;base64,#i', '', $dt->image);
                $name_image = "invoice-$dt->data_id-$dt->references.$type";
                $name = MY_PATH . $name_image;
                $image = base64_to_jpeg($p, $name);
            } else {
                $name_image = "";
            }

            $db = new db_mssql();

            $user_id = $_SESSION["uid"];
            $purchase_date = date("Y-m-d");
            $sql = "select * from sell_data_detail where status = 0 and  data_id ='$dt->data_id'";
            $pur = $db->query($sql);
            // echo json_encode($pur);die();
            $subsql = "
                        set @code = -1
                        insert into sell_purchase (product_id, device_no,device_sim,bprice,quantity,amount,purchase_date,status,remark, user_id)
                            values ";

            for ($i = 0; $i < sizeof($pur); $i++) {
                $data_id = $pur[$i]['data_id'];
                $product_id = $pur[$i]['product_id'];
                $device_sim = $pur[$i]['device_sim'];
                $bprice = $pur[$i]['price'];
                $quantity = $pur[$i]['quantity'];
                $amount = $pur[$i]['total'];
                $remark = $pur[$i]['remark'];

                if ($i == sizeof($pur) - 1) {
                    $subsql .= "($product_id,'Data-$data_id-$i','$device_sim','$bprice',$quantity,'$amount','$purchase_date',1, N'$remark',$user_id) ";
                } else {
                    $subsql .= "($product_id,'Data-$data_id-$i','$device_sim','$bprice',$quantity,'$amount','$purchase_date',1, N'$remark',$user_id),";
                }
            }

            $subsql .= "
                         set @code = -2
                             update sell_data_purchase set status = 2, data_references='$dt->references',image='$name_image' where data_id = '$dt->data_id'
                            set @code = -3 ";

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
            // echo $subsql;die();
            $sql = "declare @code int
                                    begin
                                        $subsql
                                    end
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "payed data Ok! ", 1);
            } else {
                PrintJSON("", "payed data fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}