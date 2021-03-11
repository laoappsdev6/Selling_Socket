
<?php

include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';//
require_once 'db.sqlsrv.php';//

class CurrencyController
{

    public function __construct()
    {
    }
    function addCurrency($cur)
    {
        try {

            $db = new db_mssql();
            $subsql = "
                        set @code = -1
                            insert into sell_currency (currency_name,rate,status,remark)
                            values (N'$cur->currency_name', N'$cur->rate',$cur->status,'$cur->remark')
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
                PrintJSON("", "add currency Ok! ", 1);
            } else {
                PrintJSON("", "add currency fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function updateCurrency($cur)
    {
        try {
            $db = new db_mssql();
            $subsql = "
                        set @code = -1
                             update sell_currency set currency_name=N'$cur->currency_name',rate='$cur->rate',status='$cur->status',remark='$cur->remark' where currency_id='$cur->currency_id'
                        set @code = 0";
            $sql = "declare @code int
                                    begin 
                                        $subsql
                                    end
                                    select @code as errcode";
            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "update currenct Ok! ", 1);
            } else {
                PrintJSON("", "update currenct fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function currencyList($cat)
    {
        try {
            $db = new db_mssql();

            if($cat->page == "" && $cat->limit == ""){
                $sql = "select * from sell_currency order by currency_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($cat->page - 1) * $cat->limit);

            $sql = "select * from sell_currency ";
            if (isset($cat->keyword) && $cat->keyword != "") {
                $sql .= "where 
                                currency_id like '%$cat->keyword%' or
                                currency_name like '%$cat->keyword%' or
                                rate like '%$cat->keyword%' or
                                status like N'%$cat->keyword%'  ";
            }
            $sql_page = "order by currency_id desc offset $offset rows fetch next $cat->limit rows only  ";
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
            $total_page = ceil($number_count / $cat->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$cat->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
                }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function currencyListActive($cat)
    {
        try {
            $db = new db_mssql();

                $sql = "select * from sell_currency where status = 1  order by currency_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
        } catch (Exception $e) {
            print_r($e);
        }
    }
}
