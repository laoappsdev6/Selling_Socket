
<?php

include "../services/services.php";
include_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';

class DashboardController
{
    public function __construct()
    {
    }
 
    public function customerDebtor($get)
    {
        try {
            $db = new db_mssql();

            $offset = (($get->page - 1) * $get->limit);

            $sql = "select customer_id,short_name,full_name,phone,remark,user_id,group_id,group_name,
                (select sum(amount) from sell_invoices where sell_invoices.customer_id = sell_customer.customer_id ) as total
                from sell_customer 
                where customer_id IN (select customer_id from sell_invoices where status =1)";

            if (isset($get->keyword) && $get->keyword != "") {
                $sql .= "and( 
                                customer_id like '%$get->keyword%' or
                                short_name like N'%$get->keyword%' or
                                full_name like N'%$get->keyword%' or
                                phone like '%$get->keyword%' or
                                group_id like '%$get->keyword%' or
                                group_name like '%$get->keyword%' )";
            }
            $sql_page = "order by total desc offset $offset rows fetch next $get->limit rows only  ";
            // echo $sql.$sql_page;die();
            $doquery = $db->query($sql);

            if($doquery > 0){
                $count = sizeof($doquery); 
                if($count > 0){
                    $data = $db->query($sql.$sql_page);

                    for($i=0;$i < sizeof($data);$i++){
                        $customer_id = $data[$i]['customer_id'];

                        $sql1 = "select * from sell_invoices where status=1 and  customer_id ='$customer_id'";
                        $data1 = $db->query($sql1);
                        
                        $data[$i]['invoice_list']=$data1;

                    }

                    $list1 = json_encode($data);
                }
            }else{
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $get->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$get->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function device_for_sell($pur)
    {
        try {
            $db = new db_mssql();
            $user_id = $_SESSION['uid'];
            $time_zone = (float) $_SESSION['timezone'];

            if ($pur->page == "" && $pur->limit == "") {

                $sql = "select c.purchase_id,p.product_name,p.product_id,p.dtype_id,p.device_type,p.cate_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                        from sell_purchase as c
                        INNER JOIN sell_product as p ON c.product_id = p.product_id
                        where status IN($pur->status) and  p.cate_id =(select $pur->category from sell_invoice_prefix)";

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
                $sql .="order by purchase_id desc";
                // echo $sql;die();
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($pur->page - 1) * $pur->limit);

                $sql = "select c.purchase_id,p.product_name,p.product_id,p.dtype_id,p.device_type,p.cate_id,c.device_no,c.device_sim,c.bprice,c.quantity,c.amount,c.purchase_date,c.status,c.install_date,c.remark,c.user_id
                        from sell_purchase as c
                        INNER JOIN sell_product as p ON c.product_id = p.product_id
                        where status IN($pur->status) and  p.cate_id =(select $pur->category from sell_invoice_prefix)";
 
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
        $sql_page = "order by c.purchase_id desc offset $offset rows fetch next $pur->limit rows only  ";
                // echo $sql.$sql_page;die();
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
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
?>