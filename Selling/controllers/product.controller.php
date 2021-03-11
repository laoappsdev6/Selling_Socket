<?php
include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
class ProductController
{

    public function __construct()
    {
    }
    function addProducts($pro)
    {
        try {

            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                        set @code = -1
                             insert into sell_product (cate_id,product_name,dtype_id,device_type,quantity,bprice,sprice,remark,user_id)
                             values ($pro->cate_id, N'$pro->product_name',$pro->dtype_id,N'$pro->device_type',0,'$pro->bprice','$pro->sprice',N'$pro->remark', $user_id)
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
                PrintJSON("", "add product Ok! ", 1);
            } else {
                PrintJSON("", "add product fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function updateProducts($pro)
    {
        try {
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                 set @code = -1
                    update sell_product set cate_id='$pro->cate_id',product_name=N'$pro->product_name',dtype_id='$pro->dtype_id', device_type=N'$pro->device_type', bprice='$pro->bprice',
                            sprice='$pro->sprice',remark=N'$pro->remark',user_id='$user_id' where product_id='$pro->product_id'  
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
                PrintJSON("", "update product Ok! ", 1);
            } else {
                PrintJSON("", "update product fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }

    function productsList($pro)
    {
        try {
            $db = new db_mssql();
            
            if($pro->page == "" && $pro->limit == ""){
                $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id from sell_product as p inner join sell_category as c
                        on p.cate_id=c.cate_id order by product_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($pro->page - 1) * $pro->limit);

            $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id from sell_category as c inner join sell_product as p
                    on  p.cate_id=c.cate_id ";

            if (isset($pro->keyword) && $pro->keyword != "") {
                $sql .= " where 
                                p.cate_id like '%$pro->keyword%' or
                                cate_name like '%$pro->keyword%' or
                                product_id like '%$pro->keyword%' or
                                product_name like N'%$pro->keyword%' or
                                bprice like '%$pro->keyword%' or
                                quantity like '%$pro->keyword%' or
                                sprice like '%$pro->keyword%' or
                                device_type like '%$pro->keyword%'  ";
            }
            $sql_page = "order by product_id desc offset $offset rows fetch next $pro->limit rows only  ";

            // echo $sql;die();
            $doquery = $db->query($sql);

            if($doquery > 0){
                $count = sizeof($doquery); 
                if($count > 0){
                     $data = $db->query($sql.$sql_page);
                     $list1 = json_encode($data);
                }
            }else{
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $pro->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$pro->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
                }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function productList_Purchase($pro)
    {
        try {
            $db = new db_mssql();
            
            if($pro->page == "" && $pro->limit == ""){
                $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id from sell_product as p inner join sell_category as c
                        on p.cate_id=c.cate_id where p.cate_id !=(select data from sell_invoice_prefix) order by product_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($pro->page - 1) * $pro->limit);

            $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id from sell_category as c inner join sell_product as p
                    on  p.cate_id=c.cate_id where p.cate_id !=(select data from sell_invoice_prefix)";

            if (isset($pro->keyword) && $pro->keyword != "") {
                $sql .= " and ( 
                                p.cate_id like '%$pro->keyword%' or
                                cate_name like '%$pro->keyword%' or
                                product_id like '%$pro->keyword%' or
                                product_name like N'%$pro->keyword%' or
                                bprice like '%$pro->keyword%' or
                                quantity like '%$pro->keyword%' or
                                sprice like '%$pro->keyword%' or
                                device_type like '%$pro->keyword%' ) ";
            }
            $sql_page = "order by product_id desc offset $offset rows fetch next $pro->limit rows only  ";

            // echo $sql;die();
            $doquery = $db->query($sql);

            if($doquery > 0){
                $count = sizeof($doquery); 
                if($count > 0){
                     $data = $db->query($sql.$sql_page);
                     $list1 = json_encode($data);
                }
            }else{
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $pro->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$pro->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
                }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function productList_Data($pro)
    {
        try {
            $db = new db_mssql();
            
            if($pro->page == "" && $pro->limit == ""){
                $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id from sell_product as p inner join sell_category as c
                        on p.cate_id=c.cate_id where p.cate_id =(select data from sell_invoice_prefix) order by product_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($pro->page - 1) * $pro->limit);

            $sql = "select product_id,p.cate_id,cate_name,product_name,dtype_id,device_type,quantity,bprice,sprice,p.remark,p.user_id from sell_category as c inner join sell_product as p
                    on  p.cate_id=c.cate_id where p.cate_id =(select data from sell_invoice_prefix)";

            if (isset($pro->keyword) && $pro->keyword != "") {
                $sql .= " and ( 
                                p.cate_id like '%$pro->keyword%' or
                                cate_name like '%$pro->keyword%' or
                                product_id like '%$pro->keyword%' or
                                product_name like N'%$pro->keyword%' or
                                bprice like '%$pro->keyword%' or
                                quantity like '%$pro->keyword%' or
                                sprice like '%$pro->keyword%' or
                                device_type like '%$pro->keyword%' ) ";
            }
            $sql_page = "order by product_id desc offset $offset rows fetch next $pro->limit rows only  ";

            // echo $sql;die();
            $doquery = $db->query($sql);

            if($doquery > 0){
                $count = sizeof($doquery); 
                if($count > 0){
                     $data = $db->query($sql.$sql_page);
                     $list1 = json_encode($data);
                }
            }else{
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $pro->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$pro->page,
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
