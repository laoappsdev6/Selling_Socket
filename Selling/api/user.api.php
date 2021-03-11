<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
//

include "../controllers/user.controller.php";
include_once "../models/user.model.php";

try {

    Initialization();
    $m = GetMethod();

    $json = json_decode(file_get_contents('php://input'), true);

    $control = new UserController();
    $user = new UserModel($json);
    if ($m == "adduser") {
        $user->checkAllProperties();
        $control->addUser($user);
    } else if ($m == "updateuser") {
        $user->checkId();
        $control->udpateUser($user);
    }else if ($m == "deleteuser") {
        $user->checkId();
        $control->deleteUser($user);
    } else if ($m == "changepassword") {
        $control->changePassword($user);
    } else if ($m == "userlist") {
        $list = (object) $json;
        $control->Userlist($list);
    }else {
        PrintJSON("", "method not provided", 0);
    }

} catch (Exception $e) {
    print_r($e);
}
