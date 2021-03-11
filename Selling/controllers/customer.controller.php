<?php
include "../services/services.php";
include_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';
class CustomerController
{
    public function __construct()
    {
    }
    public function addCustomer($cu)
    {
        try {
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                        set @code = -1
                        insert into sell_customer (short_name, full_name, phone, remark, user_id,group_id,group_name)
                            values (N'$cu->name', N'$cu->fname', N'$cu->phone', N'$cu->remark', $user_id,$cu->group_id,N'$cu->group_name')
                        set @code = 0";
            //    echo $subsql;die();
            $sql = "declare @code int
                                    begin
                                        $subsql
                                    end
                                    select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "add customer Ok! ", 1);
            } else {
                PrintJSON("", "add customer fail! error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function updateCustomer($cu)
    {
        try {
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                        set @code = -1
                        update sell_customer set short_name = N'$cu->name', full_name = N'$cu->fname', phone = N'$cu->phone', remark = N'$cu->remark',user_id='$user_id',group_id='$cu->group_id',group_name='$cu->group_name' where customer_id = $cu->custid
                        set @code = 0   ";

            $sql = "declare @code int
                                begin
                                    $subsql
                                end
                                select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "update Customer Ok ", 1);
            } else {
                PrintJSON("", "update fail error:" . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        }

    }
    public function deleteCustomer($cu)
    {
        try {
            $db = new db_mssql();
            $user_id = $_SESSION["uid"];
            $subsql = "
                 set @code = -1
                delete from sell_customer where customer_id =$cu->custid
                set @code = 0 ";

            $sql = "declare @code int
                            begin
                                $subsql
                            end
                            select @code as errcode";

            $data = $db->queryLastDS($sql);
            $error_code = $data[0]['errcode'];

            if (!is_null($error_code) && $error_code == 0) {
                PrintJSON("", "id: " . $cu->custid . " delete Ok!", 1);
            } else {
                PrintJSON("", "delete fail error: " . $error_code, 0);
                die();
            }
        } catch (Exception $e) {
            print_r($e);
        } 

    }
    public function CustomerList($cu)
    {
        try {

            $db = new db_mssql();
            $user_id = $_SESSION["uid"];

                $offset = (($cu->page - 1) * $cu->limit);

                $sql = "select customer_id id,short_name name,full_name fname,phone p,c.remark r,c.user_id,u.fname uname,group_id,group_name  
                from sell_customer as c
                INNER JOIN sell_user as u ON c.user_id = u.user_id ";
                if (isset($cu->keyword) && $cu->keyword != "") {
                    $sql .= "where
                        customer_id like '%$cu->keyword%' or
                        short_name like N'%$cu->keyword%' or
                        full_name like N'%$cu->keyword%' or
                        phone like '%$cu->keyword%' or
                        c.remark like '%$cu->keyword%'
                          ";
                }
                $sql_page = "order by customer_id desc offset $offset rows fetch next  $cu->limit rows only  ";
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
                $total_page = ceil($number_count / $cu->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$cu->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
        } catch (Exception $e) {
            print_r($e);
        }

    }
    public function CustomerListGroup($cu)
    {
        try {

            $db = new db_mssql();
            $user_id = $_SESSION["uid"];

                $offset = (($cu->page - 1) * $cu->limit);

                $sql = "select customer_id id,short_name name,full_name fname,phone p,c.remark r,c.user_id,u.fname uname,group_id,group_name  
                from sell_customer as c
                INNER JOIN sell_user as u ON c.user_id = u.user_id where group_id !='' and group_name!='' ";
                if (isset($cu->keyword) && $cu->keyword != "") {
                    $sql .= "and(
                        customer_id like '%$cu->keyword%' or
                        short_name like N'%$cu->keyword%' or
                        full_name like N'%$cu->keyword%' or
                        phone like '%$cu->keyword%' or
                        c.remark like '%$cu->keyword%'
                          )";
                }
                $sql_page = "order by customer_id desc offset $offset rows fetch next  $cu->limit rows only  ";
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
                $total_page = ceil($number_count / $cu->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$cu->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
        } catch (Exception $e) {
            print_r($e);
        }

    }

}
