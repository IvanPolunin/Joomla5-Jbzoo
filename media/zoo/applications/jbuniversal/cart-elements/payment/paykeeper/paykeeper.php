<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

/**
 * Class JBCartElementPaymentPayKeeper
 */
class JBCartElementPaymentPayKeeper extends JBCartElementPayment
{
    public function getRedirectUrl()
    {
        $orderId = $this->getOrderId();
$payform = trim($this->config->get('form_url'));
$secret_key = trim($this->config->get('private_key'));
$nds = trim($this->config->get('nds'));

$order = JBModelOrder::model()->getById($orderId);
$cartItems = $order->getItems(false);

$tax = $this->setTaxes($nds);
$fiscal_cart = [];
        // Добавление товаров в корзину
        foreach ($cartItems as $cartItem)
        {
            $name = $cartItem->item_name;
            $quantity = $cartItem->quantity;
            $price =  $cartItem->total;
            $sum = $price*$quantity;
            $fiscal_cart[] = array(
                "name" => $name,
                "price" => $price,
                "quantity" => $quantity,
                "sum" => $sum,
                "tax" => $tax
            );
        };
        // Добавление доставки в корзину, если она есть
        $shippins_arr = $order->params["shipping"];   
        foreach($shippins_arr as $shipping_item)
        {
            if ($shipping_item['rate']>0)
            {
                $fiscal_cart[] = array
                (
                    "name" => $shipping_item['name'],
                    "price" => $shipping_item['rate'],
                    "quantity" => 1,
                    "sum" => $shipping_item['rate'],
                    "tax" => $tax
                );                
            }
        }
        $encoded_cart = json_encode($fiscal_cart);
        $order_arr = $_POST['order'];
        $user_arr = array();
        foreach($order_arr as $item)
        {
            $user_arr[] = $item['value'];
        }

        $summ = floatval(substr($order->getTotalSum()->text(),0,5));
        $clientid = $user_arr[0];
        $client_email = $user_arr[1];
        $client_phone = $user_arr[2];
        $service_name = '';
        $to_hash = number_format($summ, 2, ".", "") .
        $clientid     .
        $orderId      .
        $service_name .
        $client_email .
        $client_phone .
        $secret_key;
        $sign = hash ('sha256' , $to_hash);
        $payment_parameters = array
        (
            "sum"=>$summ,
            "orderid"=>$orderId,
            "clientid"=>$clientid,
            "client_email"=>$client_email,
            "phone"=>$client_phone,
            "service_name"=>$service_name = '',
            "cart"=>$encoded_cart,
            "sign"=>$sign
        );
        $form = '
        <h3>Сейчас Вы будете перенаправлены на страницу банка.</h3> 
        <form name="payment" id="pay_form" action="'.$payform.'" accept-charset="utf-8" method="post">
        <input type="hidden" name="sum" value = "'.$summ.'"/>
        <input type="hidden" name="orderid" value = "'.$orderId.'"/>
        <input type="hidden" name="clientid" value = "'.$clientid.'"/>
        <input type="hidden" name="client_email" value = "'.$client_email.'"/>
        <input type="hidden" name="client_phone" value = "'.$client_phone.'"/>
        <input type="hidden" name="service_name" value = "'.$service_name.'"/>
        <input type="hidden" name="cart" value = \''.htmlentities($encoded_cart,ENT_QUOTES).'\' />
        <input type="hidden" name="sign" value = "'.$sign.'"/>
        <input type="submit" id="button-confirm" value="Оплатить"/>
        </form>
        <script text="javascript">
        function sendForm() {
            document.getElementById("pay_form").submit();
        }
            sendForm();
         </script>';
        echo $form;
        /** =============================== help functions =============================*/
        
    }
    public function setTaxes($tax_rate)
    {
        $tax ="vat0";
        switch(number_format(floatval($tax_rate), 0, ".", "")) {
            case 10:
                $tax = "vat10";
                break;
            case 20:
                $tax = "vat20";
                break;
        };
        return $tax;
    }
    /**
     * Checks validation
     * @param array $params
     * @return bool|null|void
     */
    public function isValid($params = array())
    {
        $secret_seed = JString::trim($this->config->get('private_key'));
        $id = $this->app->jbrequest->get('id');
        $sum = $this->app->jbrequest->get('sum');
        $clientid = $this->app->jbrequest->get('clientid');
        $orderid = $this->app->jbrequest->get('orderid');
        $key = $this->app->jbrequest->get('key');
        if ($key = md5 ($id . sprintf ("%.2lf", $sum).$clientid.$orderid.$secret_seed))
        {
            return true;
        }
         return false;
    }

    /**
     * Detect order id from merchant's robot request
     * @return int
     */
    public function getRequestOrderId()
    {
        $orderid = $this->app->jbrequest->get('orderid');

        return $orderid;
    }
    public function renderResponse()
    {
        $secret_seed = JString::trim($this->config->get('private_key'));
        $id = $this->app->jbrequest->get('id');
        jexit("OK ".md5($id.$secret_seed));
    }
    /**
     * Detect order id from merchant's robot request
     * @return int|JBCartValue
     */
    public function getRequestOrderSum()
    {

        $sum = $this->app->jbrequest->get('sum');

        return $sum;
    }
}
