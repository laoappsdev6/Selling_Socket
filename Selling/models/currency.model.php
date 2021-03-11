<?php

class CurrencyModel
{
    public $currency_id;
    public $currency_name;
    public $rate;
    public $status;
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
            if (property_exists('CurrencyModel', $property)) {
                $this->$property = $value;
            }
        }
    }
    function checkId()
    {
        $db = new db_mssql();
        $sql = "select * from sell_currency where currency_id='$this->currency_id' ";
        $name = $db->query($sql);
        
        if ($name == 0) {
            PrintJSON(""," currency ID: ".$this->currency_id. " is not available!", 0);
            die();
        } 
    }
    function validateName()
    {
        $db = new db_mssql();
        $sql = "select * from sell_currency where currency_name='$this->currency_name'  ";
        $name = $db->query($sql);

        if ($name > 0) {
            PrintJSON(""," currency  name: ".$this->currency_name. " already exist", 0);
            die();
        } else  if (strlen($this->currency_name) < 2) {
            PrintJSON("", " currency_name is short ", 0);
            die();
        }
    }
    function validateRate()
    {
        if($this->rate == ""){
            PrintJSON("","rate is empty! ",0);
            die();
        }
    }
}
