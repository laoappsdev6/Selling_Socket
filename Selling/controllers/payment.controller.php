<?php
include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';

class PaymentController
{

    public function __construct()
    {
    }
    function addPayment($get)
    {
        try {
            $db = new db_mssql();
            date_default_timezone_set("Asia/Vientiane");
            $user_id = $_SESSION["uid"];
            $date_now = date("Y-m-d");
            $subsql = "
                        set @code = -1
                             insert into sell_payment (invoice_id,rate_now,payment_type_id,payment_date,amount,default_amount,status,remark,user_id)
                             values ($get->invoice_id, '$get->rate_now',$get->payment_type_id,'$date_now','$get->amount','$get->default_amount',$get->status,N'$get->remark',$user_id)
                        
                        set @code = -2
                             update sell_invoices set status = 2, pay_by=N'$get->pay_by' where invoice_id='$get->invoice_id'
                        
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
                PrintJSON("", "add payment Ok! ", 1);
            } else {
                PrintJSON("", "add payment fail! error: $error_code", 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function paymentList($pro)
    {
        try {
            $db = new db_mssql();
            
            if($pro->page == "" && $pro->limit == ""){
                $sql = "select payment_id, p.invoice_id,i.invoice_prefix, rate_now, p.payment_type_id, t.payment_type_name, payment_date, p.amount, default_amount, p.status,
                        p.remark, p.user_id, u.fname as name_of_user 
                        from sell_payment as p 
                        INNER JOIN sell_payment_type as t ON p.payment_type_id = t.payment_type_id
                        INNER JOIN sell_user as u ON p.user_id = u.user_id 
                        INNER JOIN sell_invoices as i ON p.invoice_id = i.invoice_id order by payment_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($pro->page - 1) * $pro->limit);

            $sql = "select payment_id, p.invoice_id,i.invoice_prefix, rate_now, p.payment_type_id, t.payment_type_name, payment_date, p.amount, default_amount, p.status,
                    p.remark, p.user_id, u.fname as name_of_user 
                    from sell_payment as p 
                    INNER JOIN sell_payment_type as t ON p.payment_type_id = t.payment_type_id
                    INNER JOIN sell_user as u ON p.user_id = u.user_id 
                    INNER JOIN sell_invoices as i ON p.invoice_id = i.invoice_id ";

            if (isset($pro->keyword) && $pro->keyword != "") {
                $sql .= " where 
                                payment_id like '%$pro->keyword%' or
                                i.invoice_prefix like '%$pro->keyword%' or
                                rate_now like '%$pro->keyword%' or
                                t.payment_type_name like N'%$pro->keyword%' or
                                payment_date like N'%$pro->keyword%' or
                                u.fname like N'%$pro->keyword%' or
                                rate_amount like '%$pro->keyword%'  ";
            }
            $sql_page = "order by payment_id desc offset $offset rows fetch next $pro->limit rows only  ";

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
    function paymentListInvoice($get)
    {
        try {
            $db = new db_mssql();
            
                $sql = "select payment_id, p.invoice_id,i.invoice_prefix, rate_now, p.payment_type_id, t.payment_type_name, payment_date, p.amount, default_amount, p.status,
                p.remark, p.user_id, u.fname as name_of_user 
                from sell_payment as p 
                INNER JOIN sell_payment_type as t ON p.payment_type_id = t.payment_type_id
                INNER JOIN sell_user as u ON p.user_id = u.user_id 
                INNER JOIN sell_invoices as i ON p.invoice_id = i.invoice_id 
                where p.invoice_id = '$get->invoice_id' order by payment_id desc";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
