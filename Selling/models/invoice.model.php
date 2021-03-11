<?php

class InvoiceModel
{
    public $invoice_id;
    public $discount;
    public $tax;
    public $amount;
    public $customer_id;
    public $remark;

    public $pay_by;

    public $invoice_references;
    public $prefix_references;


    public function __construct($object)
    {
            if (!$object) {
                PrintJSON("", "data is empty!", 0);
                die();
            }
        foreach ($object as $property => $value) {
            if (property_exists('InvoiceModel', $property)) {
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
        $sql = "select * from sell_invoices where invoice_id ='$this->invoice_id' ";
        $name = $db->query($sql);
        
        if ($name == 0) {
            PrintJSON(""," invoice ID: ".$this->invoice_id. " is not available!", 0);
            die();
        }
    }
    function validatePayment(){
        if($this->pay_by == ""){
            PrintJSON("","pay ment is empty!",0);
            die();
        }
    }
    function validate($p)
    {
        switch ($p) {
            case 'amount':
                $this->validateAmount();
                break;
            case 'customer_id':
                $this->validateCustomer_id();
                break;
        }
    }
    function validateAmount()
    {
        if ($this->amount == "") {
            PrintJSON("", "amount is empty", 0);
            die();
        }
    }
    function validateCustomer_id()
    {
        if ($this->customer_id == "") {
            PrintJSON("", "Customer is empty", 0);
            die();
        }
    }
}
