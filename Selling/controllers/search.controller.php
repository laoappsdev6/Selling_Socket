
<?php

include "../services/services.php";
include_once '../services/common.inc.php';
require_once 'svc.class.php';
require_once 'db.class.php'; //
require_once 'db.sqlsrv.php'; //

class SearchController
{

    public function __construct()
    {
    }
    public function searchInvoice($inv)
    {
        try {
            $db = new db_mssql();

            if ($inv->page == "" && $inv->limit == "") {
                $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                         from sell_invoices as i
                         INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                         where  status IN(1,2) ";
                if ((isset($inv->key) && $inv->key != "") && (isset($inv->value) && $inv->value != "")) {
                    $sql .= "and $inv->key ='$inv->value' ";
                }
                $sql .= " order by invoice_id desc  ";
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($inv->page - 1) * $inv->limit);

                $sql = "select invoice_id,invoice_prefix,invoice_references,prefix_references,discount,tax,amount,i.customer_id,c.short_name,c.full_name,invoice_date,status,i.remark,pay_by,i.user_id
                        from sell_invoices as i
                        INNER JOIN sell_customer as c ON i.customer_id = c.customer_id
                        where  status IN (1,2) ";
                if ((isset($inv->key) && $inv->key != "") && (isset($inv->value) && $inv->value != "")) {
                    $sql .= "and $inv->key ='$inv->value' ";
                }
                if (isset($inv->keyword) && $inv->keyword != "") {
                    $sql .= "and (
                                invoice_id like '%$inv->keyword%' or
                                discount like '%$inv->keyword%' or
                                tax like '%$inv->keyword%' or
                                amount like '%$inv->keyword%' or
                                i.customer_id like '%$inv->keyword%' or
                                c.short_name like N'%$inv->keyword%' or
                                c.full_name like N'%$inv->keyword%' or
                                invoice_date like '%$inv->keyword%' or
                                i.status like '%$inv->keyword%' or
                                i.remark like '%$inv->keyword%' or
                                pay_by like N'%$inv->keyword%' or
                                i.user_id like '%$inv->keyword%' or
                                invoice_prefix like '%$inv->keyword%' ) ";
                }
                $sql_page = "order by invoice_id desc offset $offset rows fetch next $inv->limit rows only  ";
                // echo $sql . $sql_page;die();
                $doquery = $db->query($sql);

                if ($doquery > 0) {
                    $count = sizeof($doquery);
                    if ($count > 0) {
                        $data = $db->query($sql . $sql_page);
                        // for ($i = 0; $i < sizeof($data); $i++) {

                        //     $invoice_id = $data[$i]["invoice_id"];
                        //     $subsql = "select order_id,invoice_id,p.product_id,p.product_name,p.dtype_id,p.device_type,c.device_sim,p.cate_id,p.device_type,i.purchase_id,c.device_no,c.object_id,imei_references,i.sprice,i.quantity,total,i.status,last_update,quaranteed,i.remark,i.user_id
                        //     from sell_invoices_order as i
                        //     INNER JOIN sell_product as p ON i.product_id = p.product_id
                        //     INNER JOIN sell_purchase as c ON i.purchase_id = c.purchase_id
                        //     where invoice_id='$invoice_id' ";

                        //     $subdata = $db->query($subsql);
                        //     $data[$i]['invoice_order'] = $subdata;
                        // }
                        $list1 = json_encode($data);
                    }
                } else {
                    $list1 = json_encode([]);
                    $count = 0;
                }

                $number_count = $count;
                $total_page = ceil($number_count / $inv->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$inv->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function searchInvoiceOrder($inv)
    {
        try {
            $db = new db_mssql();

            if ($inv->page == "" && $inv->limit == "") {
                $sql = "select order_id,i.invoice_id,p.product_id,p.product_name,p.dtype_id,p.device_type,c.device_sim,p.cate_id,i.purchase_id,c.device_no,c.object_id,c.status as install_status,c.install_date,imei_references,i.sprice,i.quantity,total,i.status,last_update,quaranteed,i.remark,i.user_id
                from sell_invoices_order as i
                INNER JOIN sell_product as p ON i.product_id = p.product_id
                INNER JOIN sell_purchase as c ON i.purchase_id = c.purchase_id
                INNER jOIN sell_invoices as v ON i.invoice_id = v.invoice_id
                where v.status IN (1,2) ";
                if ((isset($inv->key) && $inv->key != "") && (isset($inv->value) && $inv->value != "")) {
                    $sql .= "and $inv->key ='$inv->value' ";
                }
                $sql .="order by order_id desc ";
                // echo $sql;die();
                $doquery = $db->query($sql);
                $list = json_encode($doquery);
                $json = "{\"Data\":$list}";
                echo $json;
            } else {
                $offset = (($inv->page - 1) * $inv->limit);

                $sql = "select order_id,i.invoice_id,p.product_id,p.product_name,p.dtype_id,p.device_type,c.device_sim,p.cate_id,i.purchase_id,c.device_no,c.object_id,c.status as install_status,c.install_date,imei_references,i.sprice,i.quantity,total,i.status,last_update,quaranteed,i.remark,i.user_id
                from sell_invoices_order as i
                INNER JOIN sell_product as p ON i.product_id = p.product_id
                INNER JOIN sell_purchase as c ON i.purchase_id = c.purchase_id
                INNER jOIN sell_invoices as v ON i.invoice_id = v.invoice_id
                where v.status IN (1,2) ";

                if ((isset($inv->key) && $inv->key != "") && (isset($inv->value) && $inv->value != "")) {
                    if($inv->key =="device_no"){
                        $sql .=" and (c.device_no ='$inv->value' or imei_references ='$inv->value') ";
                    }
                    $sql .= "and $inv->key ='$inv->value' ";
                }
                if (isset($inv->keyword) && $inv->keyword != "") {
                    $sql .= "and (
                                order_id like '%$inv->keyword%' or
                                i.invoice_id like '%$inv->keyword%' or
                                p.product_name like '%$inv->keyword%' or
                                p.device_type like '%$inv->keyword%' or
                                c.device_sim like '%$inv->keyword%' or
                                c.device_no like '%$inv->keyword%' or
                                imei_references like '%$inv->keyword%' or
                                last_update like '%$inv->keyword%'
                                 ) ";
                }
                $sql_page = "order by order_id desc offset $offset rows fetch next $inv->limit rows only  ";
                // echo $sql.$sql_page;die();
                $doquery = $db->query($sql);

                if ($doquery > 0) {
                    $count = sizeof($doquery);
                    if ($count > 0) {
                        $data = $db->query($sql . $sql_page);
                        $list1 = json_encode($data);
                    }
                } else {
                    $list1 = json_encode([]);
                    $count = 0;
                }

                $number_count = $count;
                $total_page = ceil($number_count / $inv->limit);
                $list3 = json_encode($total_page);
                $json = "{  \"Data\":$list1,
                        \"Page\":$inv->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
                echo $json;
            }
        } catch (Exception $e) {
            print_r($e);
        }
    }
    public function searchCustomer($cu)
    {
        try {

            $db = new db_mssql();

            $offset = (($cu->page - 1) * $cu->limit);

            $sql = "select customer_id id,short_name name,full_name fname,phone p,c.remark r,c.user_id,u.fname uname,group_id,group_name
                from sell_customer as c
                INNER JOIN sell_user as u ON c.user_id = u.user_id ";
                 if ((isset($cu->key) && $cu->key != "") && (isset($cu->value) && $cu->value != "")) {
                    $sql .= "and $cu->key ='$cu->value' ";
                }
            if (isset($cu->keyword) && $cu->keyword != "") {
                $sql .= "where
                        customer_id like '%$cu->keyword%' or
                        short_name like N'%$cu->keyword%' or
                        full_name like N'%$cu->keyword%' or
                        phone like '%$cu->keyword%' or
                        c.remark like '%$cu->keyword%'
                          ";
            }
            $sql_page = "order by customer_id desc offset $offset rows fetch next  $cu->limit rows only  ";
            // echo $sql.$sql_page;die();
            $doquery = $db->query($sql);

            if ($doquery > 0) {
                $count = sizeof($doquery);
                if ($count > 0) {
                    $data = $db->query($sql . $sql_page);
                    $list1 = json_encode($data);
                }
            } else {
                $list1 = json_encode([]);
                $count = 0;
            }

            $number_count = $count;
            $total_page = ceil($number_count / $cu->limit);
            $list3 = json_encode($total_page);
            $json = "{  \"Data\":$list1,
                        \"Page\":$cu->page,
                        \"Pagetotal\":$list3,
                        \"Datatotal\":$number_count
                    }";
            echo $json;
        } catch (Exception $e) {
            print_r($e);
        }

    }
    public function searchGPS($get)
    {
        try {

            $db = new db_mssql();
            if (count($get['Data']) > 0) {
                for ($i = 0; $i < sizeof($get['Data']); $i++) {
                    $customer_id = $get['Data'][$i]['cinfo'];
                    $sql = "select customer_id,short_name,full_name
                from sell_customer
                where customer_id ='$customer_id'";
                    $customer = $db->query($sql);

                    $get['Data'][$i]['sell_customer_id'] = $customer[$i]['customer_id'];
                    $get['Data'][$i]['sell_short_name'] = $customer[$i]['short_name'];
                    $get['Data'][$i]['sell_full_name'] = $customer[$i]['full_name'];
                }
                echo json_encode($get);
            } else {
                PrintJSON("", "Data is not available", 0);
            }

        } catch (Exception $e) {
            print_r($e);
        }
    }
}
