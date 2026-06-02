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

$this->app->jbdebug->mark('template::basket-success::start');
$this->app->jblayout->setView($this);
$this->app->document->setTitle(JText::_('JBZOO_CART_ITEMS'));
$this->app->jbwrapper->start();
$myorderid = $this->order->id;
$order = JBModelOrder::model()->getById($myorderid);
$cartItems = $order->getItems(false);
?>

<h1 class="title"><?php echo JText::_('JBZOO_CART_ORDER_SUCCESS_CREATED'); ?></h1>
<?php
   foreach ($cartItems as $cartItem) {
       $itemPrice = $order->val($cartItem->get('total'));
       $yaParams['goods'][] = array(
           'id'       => $cartItem->get('item_id'),
           'name'     => $cartItem->get('item_name'),
           'price'    => $itemPrice->val(),
           'quantity' => $cartItem->get('quantity', 1),
       );
   }

   $mydatecreat = date('d.m.Y H:i:s', strtotime("+3 hours", strtotime($order->created)));
?>
<h2 style="color: #EB7811; text-transform: uppercase; font-family: 'Clear Sans'; font-size: 18px; margin-top: 30px;">Детали вашего заказа</h2>
   <div class="row">
      <div class="col-12 col-sm-12 col-md-12 col-lg-12 col-xl-12">
         <ul style="list-style: none;">
            <li><strong>Номер вашего заказа:</strong> <?php echo $order->id; ?></li>
            <li><strong>Дата и время:</strong> <?php echo $mydatecreat; ?></li>
            <?php 
               foreach ($yaParams as $oderitemmy) {
                  foreach ($oderitemmy as $oderitemmyx) {
                     $nameorderitem = $oderitemmyx['name'];
                     $priceorderitem = $oderitemmyx['price'];
                     $kolvoorderitem = $oderitemmyx['quantity'];
                     $itogoitem = $kolvoorderitem*$priceorderitem;

                     echo "<li><strong>Наименование:</strong> ";
                     echo "{$nameorderitem}<br/>";
                     //echo "<td width='70'><span style='font-size:14px;font-family:Consolas,monaco,monospace;color:#333333'>{$priceorderitem}</span></td>";
                     //echo "<td width='80'><span style='font-size:14px;font-family:Consolas,monaco,monospace;color:#333333'>{$kolvoorderitem}</span></td>";
                     //echo "<td width='100'><span style='font-size:14px;font-family:Consolas,monaco,monospace;color:#333333'>{$itogoitem}</span></td>";
                     echo "</li>";
                  }
               }
            ?>
         </ul>
      </div>
   </div>

<?php //echo $this->app->jblayout->renderIndex('basket-success'); ?>

<?php
$this->app->jbwrapper->end();
$this->app->jbdebug->mark('template::basket-success::finish');
