<?php

class PaymentModel
{
    public $payment_id;
    public $invoice_id;
    public $rate_now;
    public $payment_type_id;
    public $amount;
    public $rate_amount;
    public $status;
    public $remark;
    public $pay_by;

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
            if (property_exists('PaymentModel', $property)) {
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
            case 'invoice_id':
                $this->validateInvoiceID();
                break;
            case 'rate_now':
                $this->validateRateNow();
                break;
            case 'payment_type_id':
                $this->validatePaymentTypeID();
                break;
        }
    }
    public function validateInvoiceID()
    {
        if (empty($this->invoice_id)) {
            PrintJSON("", "invoice ID is empty! ", 0);
            die();
        }
    }
    public function validateRateNow()
    {
        if (empty($this->rate_now)) {
            PrintJSON("", "rate now is empty! ", 0);
            die();
        }
    }
    public function validatePaymentTypeID()
    {
        if (empty($this->payment_type_id)) {
            PrintJSON("", "payment type ID is empty! ", 0);
            die();
        }
    }
}
