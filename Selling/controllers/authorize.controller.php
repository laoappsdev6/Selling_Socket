<?php

include("../services/services.php");
include_once('../services/common.inc.php');
require_once 'svc.class.php';
require_once 'db.class.php';
require_once 'db.sqlsrv.php';

class LoginController
{

    public function __construct()
    {
    }

    function checkLogin($u)
    {
        $db = new db_mssql();
        $sql = "select * from sell_user where username='$u->username' and password='$u->password' ";
        $sql1 = "select fname,lname,username,password,phonenumber from sell_user where username='$u->username' and password='$u->password'";
        $name = $db->query($sql);
        $list = $db->query($sql1);
        $row = $name[0];
        if ($name > 0) {
                 echo json_encode(array('status' => "1",
                             'token' => registerToken($row),
                             'data'=> $list[0]
                            ));
        }else{
            $sql = "select * from sell_user where username='$u->username'";
            $name = $db->query($sql);

            $sql1 = "select * from sell_user where password='$u->password'";
            $pass = $db->query($sql1);
        
            if($name == 0 && $pass == 0){
                PrintJSON("","Wrong username and password!!!",0);
            }else if($name > 0 && $pass == 0){
                PrintJSON("","Wrong password!!!",0);
            }else if($name == 0 && $pass >0){
                PrintJSON("","Wrong username!!!",0);
            }
        }
    }
}
