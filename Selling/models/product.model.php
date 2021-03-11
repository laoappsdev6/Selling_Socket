<?php

class ProductModel
{
    public $product_id;
    public $cate_id;
    public $product_name;
    public $dtype_id;
    public $device_type;
    public $bprice;
    public $sprice;
    public $remark;

    public $page;
    public $limit;
    public $keyword;

    public function __construct($object)
    {
            if (!$object) {
                PrintJSON("", "data is empty!", 0);
                die();
            }
        foreach ($object as $property => $value) {
            if (property_exists('ProductModel', $property)) {
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
        $sql = "select * from sell_product where product_id='$this->product_id' ";
        $name = $db->query($sql);
        if ($name == 0) {

            PrintJSON("", " product ID: " . $this->product_id . " is not available!", 0);
            die();
        }
    }
    function validate($p)
    {
        switch ($p) {
            case 'cate_id':
                $this->validateCate_id();
                break;
            case 'product_name':
                $this->validateProduct_name();
                break;
            case 'bprice':
                $this->validateBprice();
                break;
            case 'sprice':
                $this->validateSprice();
                break;
        }
    }
    function validateCate_id()
    {
        $db = new db_mssql();
        $sql = "select * from sell_category where cate_id='$this->cate_id'";
        $data = $db->query($sql);
        if ($data == 0) {
            PrintJSON("", "category ID is not available!", 0);
            die();
        }
    }
    function validateProduct_name()
    {
        if (strlen($this->product_name) < 2) {
            PrintJSON("", "Product name is short ", 0);
            die();
        }
    }
    function validateDevice_type()
    {
        $db = new db_mssql();
        $sql = "select * from sell_product where device_type='$this->device_type' ";
        $type = $db->query($sql);

        if ($type > 0) {

            PrintJSON("", "device type : " . $this->device_type . " already exist", 0);
            die();
        } else if (strlen($this->device_type) < 2) {
            PrintJSON("", "device type is short ", 0);
            die();
        }
    }
    function validateBprice()
    {
        if ($this->bprice == "") {
            PrintJSON("", "bprice is empty!", 0);
            die();
        }
    }
    function validateSprice()
    {
        if ($this->sprice == "") {
            PrintJSON("", "Sprice is empty!", 0);
            die();
        }
    }
}
