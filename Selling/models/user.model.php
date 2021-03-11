<?php

class UserModel
{
    public $user_id;
    public $fname;
    public $lname;
    public $username;
    public $password;
    public $status;
    public $phonenumber;
    public function __construct($object)
    {
            if (!$object) {
                PrintJSON("", "data is empty!", 0);
                die();
            }
        foreach ($object as $property => $value) {
            if (property_exists('UserModel', $property)) {
                $this->$property = $value;
            }
        }
    }
    function checkAllProperties()
    {
        foreach ($this as $property => $value) {
            $this->validate($property);
        }
    }
    function checkId()
    {
        $db = new db_mssql();
        $sql = "select * from sell_user where user_id='$this->user_id' ";
        $name = $db->query($sql);
        
        if ($name == 0) {
            PrintJSON(""," user ID: ".$this->user_id. " is not available!", 0);
            die();
        } 
    }
    function validate($p)
    {
        switch ($p) {
            case 'fname':
                $this->validateFname();
                break;
            case 'lname':
                $this->validateLname();
                break;
            case 'username':
                $this->validateUsername();
                break;
            case 'password':
                $this->validatePassword();
                break;
            case 'status':
                $this->validateStatus();
                break;
            case 'phonenumber':
                $this->validatePhonenumber();
                break;
        }
    }
    function validateFname()
    {
        if (strlen($this->fname) < 2) {
            PrintJSON("", " First  name is short ", 0);
            die();
        }
    }
    function validateLname()
    {
        if ($this->lname == "") {
            PrintJSON("", "last name is empty ", 0);
            die();
        }
    }
    function validateUsername()
    {
        $db = new db_mssql();
        $sql = "select * from sell_user where username='$this->username' and user_id !='$this->user_id'";
        $name = $db->query($sql);
        if ($name > 0) {
            PrintJSON("", " username: " . $this->username . " already exist", 0);
            die();
        }
        if (strlen($this->username) < 3) {
            PrintJSON("", "username is short ", 0);
            die();
        }
    }
    function validatePassword()
    {
        $db = new db_mssql();
        $sql = "select * from sell_user where password='$this->password' and user_id !='$this->user_id' ";
        $name = $db->query($sql);
        if ($name > 0) {

            PrintJSON("", "password " . $this->password . " already exist", 0);
            die();
        } else if (strlen($this->password) < 6) {
            PrintJSON("", "password must be then 6 digists", 0);
            die();
        }
    }
    function validateStatus()
    {
        if ($this->status == "") {
            PrintJSON("", "status is empty ", 0);
            die();
        }
    }
    function validatePhonenumber()
    {

        if (!is_numeric($this->phonenumber)) {
            PrintJSON("", "phonenumber is number only", 0);
            die();
        } else if (strlen($this->phonenumber) < 10) {
            PrintJSON("", "phonenumber must be 10 deigits and number only", 0);
            die();
        }
    }
}
