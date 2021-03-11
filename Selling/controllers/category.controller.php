
<?php

include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';//
require_once 'db.sqlsrv.php';//

class CategoryController
{

    public function __construct()
    {
    }
    function addCategory($cat)
    {
        try {

            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                        set @code = -1
                            insert into sell_category (cate_name, remark, user_id)
                            values (N'$cat->cate_name', N'$cat->remark', $user_id)
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
                PrintJSON("", "add category Ok! ", 1);
            } else {
                PrintJSON("", "add category fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function updateCategory($cat)
    {
        try {
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                        set @code = -1
                             update sell_category set cate_name=N'$cat->cate_name',remark=N'$cat->remark',user_id='$user_id' where cate_id='$cat->cate_id'
                        set @code = 0";
            $sql = "declare @code int
                                    begin 
                                        $subsql
                                    end
                                    select @code as errcode";
            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "update category Ok! ", 1);
            } else {
                PrintJSON("", "update category fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    function categoryList($cat)
    {
        try {
            $db = new db_mssql();

            if($cat->page == "" && $cat->limit == ""){
                $sql = "select * from sell_category order by cate_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($cat->page - 1) * $cat->limit);

            $sql = "select * from sell_category ";
            if (isset($cat->keyword) && $cat->keyword != "") {
                $sql .= "where 
                                cate_id like '%$cat->keyword%' or
                                cate_name like N'%$cat->keyword%'  ";
            }
            $sql_page = "order by cate_id desc offset $offset rows fetch next $cat->limit rows only  ";
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
}
