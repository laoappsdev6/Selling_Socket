<?php
include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
class PaymentTypeController
{

    public function __construct()
    {
    }
    function addPaymentType($get)
    {
        try {

            $db = new db_mssql();
            $subsql = "
                        set @code = -1
                             insert into sell_payment_type (payment_type_name,bank,account_name,account_no,currency_id,status,type)
                             values (N'$get->payment_type_name', N'$get->bank','$get->account_name','$get->account_no','$get->currency_id','$get->status',N'$get->type')
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
                PrintJSON("", "add payment type Ok! ", 1);
            } else {
                PrintJSON("", "add payment type fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function updatePaymentType($get)
    {
        try {
            $db = new db_mssql();
            $subsql = "
                 set @code = -1
                    update  sell_payment_type set payment_type_name=N'$get->payment_type_name',bank=N'$get->bank',account_name='$get->account_name',
                    account_no='$get->account_no',currency_id='$get->currency_id',status='$get->status',type='$get->type' where payment_type_id='$get->payment_type_id'
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
                PrintJSON("", "update payment type Ok! ", 1);
            } else {
                PrintJSON("", "update payment type fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }

    function paymentTypeList($pro)
    {
        try {
            $db = new db_mssql();
            
            if($pro->page == "" && $pro->limit == ""){
                $sql = "select payment_type_id,payment_type_name,bank,account_name,account_no,p.currency_id,c.currency_name,c.rate,p.status,type
                        from sell_payment_type as p 
                        INNER JOIN sell_currency as c ON p.currency_id = c.currency_id order by payment_type_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($pro->page - 1) * $pro->limit);

            $sql = "select payment_type_id,payment_type_name,bank,account_name,account_no,p.currency_id,c.currency_name,c.rate,p.status,type
                    from sell_payment_type as p 
                    INNER JOIN sell_currency as c ON p.currency_id = c.currency_id ";

            if (isset($pro->keyword) && $pro->keyword != "") {
                $sql .= " where 
                                payment_type_name like '%$pro->keyword%' or
                                bank like '%$pro->keyword%' or
                                account_name like '%$pro->keyword%' or
                                account_no like N'%$pro->keyword%' or
                                c.currency_name like '%$pro->keyword%'  ";
            }
            $sql_page = "order by payment_type_id desc offset $offset rows fetch next $pro->limit rows only  ";

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
    function paymentTypeListActive($pro)
    {
        try {
            $db = new db_mssql();
            
                $sql = "select payment_type_id,payment_type_name,bank,account_name,account_no,p.currency_id,c.currency_name,c.rate,p.status,type
                        from sell_payment_type as p 
                        INNER JOIN sell_currency as c ON p.currency_id = c.currency_id where p.status = 1 order by payment_type_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
