<?php

class CategoryModel
{
    public $cate_id;
    public $cate_name;
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
            if (property_exists('CategoryModel', $property)) {
                $this->$property = $value;
            }
        }
    }
    function checkId()
    {
        $db = new db_mssql();
        $sql = "select * from sell_category where cate_id='$this->cate_id' ";
        $name = $db->query($sql);
        
        if ($name == 0) {
            PrintJSON(""," category ID: ".$this->cate_id. " is not available!", 0);
            die();
        } 
    }
    function validateName()
    {
        $db = new db_mssql();
        $sql = "select * from sell_category where cate_name='$this->cate_name' ";
        $name = $db->query($sql);

        if ($name > 0) {
            PrintJSON(""," category name: ".$this->cate_name. " already exist", 0);
            die();
        } else  if (strlen($this->cate_name) < 2) {
            PrintJSON("", " category name is short ", 0);
            die();
        }
    }
}
