<?php

class CustomerModel
{
    public $custid;
    public $name;
    public $fname;
    public $phone;
    public $remark;
    public $group_id;
    public $group_name;

    public $page;
    public $limit;
    public $keyword;
    public function __construct($object=null, $needEmpty = false)
    {
        if (!$needEmpty) {
            if (!$object) {
                PrintJSON("", "data is empty", 0);
                die();
            }
        }
        foreach ($object as $property => $value) {
            if (property_exists('CustomerModel', $property)) {
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
        $sql = "select * from sell_customer where customer_id='$this->custid' ";
        $name = $db->query($sql);
        
        if ($name == 0) {
            PrintJSON(""," customer ID: ".$this->custid. " is not available!", 0);
            die();
        } 
    }
    function validate($p)
    {
        switch ($p) {
            case 'name':
                $this->validateName();
                break;
            case 'fname':
                $this->validateFname();
                break;
            case 'phone':
                $this->validatePhone();
                break;
        }
    }
    function validateName()
    {
        $db = new db_mssql();
        $sql = "select * from sell_customer where short_name='$this->name' and customer_id!='$this->custid'  ";
        $name = $db->query($sql);
        
        if ($name > 0) {
            PrintJSON(""," short name: ".$this->name. " already exit!", 0);
            die();
        } 
        else if (strlen($this->name) < 2) {
            PrintJSON("", "Name is short ", 0);
            die();
        }
    }
    function validateFname()
    { 
        $db = new db_mssql();
        $sql = "select * from sell_customer where full_name='$this->fname' and customer_id!='$this->custid' ";
        $name = $db->query($sql);
        
        if ($name > 0) {
            PrintJSON(""," full name: ".$this->fname. " already exit!", 0);
            die();
        } 
        else
        if (strlen($this->fname) < 3) {
            PrintJSON("", "Full name is short ", 0);
            die();
        }
    }
    function validatePhone()
    {
        $number = preg_match('@[0-9]@', $this->phone);
        if (!$number || strlen($this->phone) < 10) {
            PrintJSON("", "Phonenumber must be 10 deigists and number only", 0);
            die();
        }
    }
}
?>