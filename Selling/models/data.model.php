<?php

class DataModel
{
    public $data_id;
    public $supplier;
    public $references;
    public $total;
    public $status;
    public $remark;
    public $image;

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
            if (property_exists('DataModel', $property)) {
                $this->$property = $value;
            }
        }
    }
    public function checkAllProperties()
    {
        foreach ($this as $property => $value) {
            $this->validate($property);
        }
    }
    public function checkId()
    {
        $db = new db_mssql();
        $sql = "select * from sell_data_purchase where data_id ='$this->data_id' ";
        $name = $db->query($sql);

        if ($name == 0) {
            PrintJSON("", " data ID: " . $this->data_id . " is not available!", 0);
            die();
        }
    }
    public function validateReferences()
    {
        if ($this->references == "") {
            PrintJSON("", "references is empty!", 0);
            die();
        }
    }
    public function validate($p)
    {
        switch ($p) {
            case 'supplier':
                $this->validateSupplier();
                break;
            case 'total':
                $this->validateTotal();
                break;
        }
    }
    public function validateSupplier()
    {
        if ($this->supplier == "") {
            PrintJSON("", "Supplier is empty", 0);
            die();
        }
    }
    public function validateTotal()
    {
        if ($this->total == "") {
            PrintJSON("", "total is empty", 0);
            die();
        }
    }

    public function validateImage()
    {
        if ($this->image == "") {
            PrintJSON("", "image is empty!", 0);
            die();
        }
    }
}
