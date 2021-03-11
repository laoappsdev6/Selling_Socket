<?php

class PaymentTypeModel
{
    public $payment_type_id;
    public $payment_type_name;
    public $bank;
    public $account_name;
    public $account_no;
    public $currency_id;
    public $status;
    public $type;

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
            if (property_exists('PaymentTypeModel', $property)) {
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
        $sql = "select * from sell_payment_type where payment_type_id='$this->payment_type_id'";
        $name = $db->query($sql);
        if ($name == 0) {

            PrintJSON("", " payment type ID: " . $this->payment_type_id . " is not available!", 0);
            die();
        }
    }
    public function validate($p)
    {
        switch ($p) {
            case 'payment_type_name':
                $this->validatePaymentTypeName();
                break;
            // case 'bank':
            //     $this->validateBank();
            //     break;
            // case 'account_name':
            //     $this->validateAccountNname();
            //     break;
            // case 'account_no':
            //     $this->validateAccountNo();
            //     break;
            case 'currency_id':
                $this->validateCurrencyID();
                break;
        }
    }
    public function validateCurrencyID()
    {
        $db = new db_mssql();
        $sql = "select * from sell_currency where currency_id='$this->currency_id'";
        $data = $db->query($sql);
        if ($data == 0) {
            PrintJSON("", "currency ID is not available!", 0);
            die();
        }
    }
    public function validatePaymentTypeName()
    {
        if (empty($this->payment_type_name)) {
            PrintJSON("", "payment type name is empty! ", 0);
            die();
        }
    }
    // public function validateBank()
    // {
    //     if (empty($this->bank)) {
    //         PrintJSON("", "bank is empty! ", 0);
    //         die();
    //     }
    // }
    // public function validateAccountNname()
    // {
    //     if (empty($this->account_name)) {
    //         PrintJSON("", "account name is empty! ", 0);
    //         die();
    //     }
    // }
    // public function validateAccountNo()
    // {
    //     if (empty($this->account_no)) {
    //         PrintJSON("", "account NO is empty! ", 0);
    //         die();
    //     }
    // }
}
