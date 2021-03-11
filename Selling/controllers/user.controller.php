<?php

include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
class UserController
{
    public function __construct()
    {
    }
    function addUser($u)
    {
        try {
            $db = new db_mssql();
            $subsql = "
                                    set @code = -1
                                    insert into sell_user (fname,lname,username,password,status,phonenumber)
                                        values (N'$u->fname', N'$u->lname',N'$u->username','$u->password',$u->status,'$u->phonenumber')
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
                PrintJSON("", "add user Ok! ", 1);
            } else {
                PrintJSON("", "add user fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);

        }
    }
    function udpateUser($u)
    {
        try {
            $db = new db_mssql();
            $subsql = "
                        set @code = -1
                            update sell_user set fname=N'$u->fname',lname=N'$u->lname',username=N'$u->username',password ='$u->password',
                            status='$u->status',phonenumber='$u->phonenumber' where user_id='$u->user_id'
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
                PrintJSON("", "update user  Ok! ", 1);
            } else {
                PrintJSON("", "update user  fail! error: " . $error_code, 0);
                die();
            }
    } catch (Exception $e) {
        print_r($e);

    }
    }
    function deleteUser($u)
    {
        try{
        $db = new db_mssql();
            $subsql = "
                        set @code = -1
                        delete from sell_user where user_id=$u->user_id;
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
                PrintJSON("", "delete user Ok! ", 1);
            } else {
                PrintJSON("", "delete user fail! error: " . $error_code, 0);
                die();
            }
    } catch (Exception $e) {
        print_r($e);

    } 
    }
    function changePassword($u)
    {
        try {
            $db = new db_mssql();
            $subsql = "
                        set @code = -1
                            update sell_user set password ='$u->password' where user_id='$u->user_id'
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
                PrintJSON("", "change password  Ok! ", 1);
            } else {
                PrintJSON("", "change password  fail! error: " . $error_code, 0);
                die();
            }
    } catch (Exception $e) {
        print_r($e);

    }
    }
    function Userlist($cat)
    {
        try {
            $db = new db_mssql();

            if($cat->page == "" && $cat->limit == ""){
                $sql = "select user_id,fname,lname,username,password,status,phonenumber from sell_user order by user_id desc ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            }else{
            $offset = (($cat->page - 1) * $cat->limit);

            $sql = "select user_id,fname,lname,username,password,status,phonenumber from sell_user ";
            if (isset($cat->keyword) && $cat->keyword != "") {
                $sql .= "where 
                                fname like N'%$cat->keyword%' or
                                lname like N'%$cat->keyword%' or
                                phonenumber like '%$cat->keyword%'  ";
            }
            $sql_page = "order by user_id desc offset $offset rows fetch next $cat->limit rows only  ";
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
?>