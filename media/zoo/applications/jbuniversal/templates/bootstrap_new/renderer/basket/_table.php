<?php
use Joomla\CMS\Language\Text;
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

$this->app->jbassets->less('jbassets:less/cart/table.less');

$string = $this->app->jbstring;
$jbhtml = $this->app->jbhtml;

$cart   = JBCart::getInstance();
$order  = $cart->newOrder();
$config = $cart->getConfig();

$bootstrap = $this->app->jbbootstrap;

echo $this->partial('basket', 'table.styles');

?>

<table class="jbcart-table jsJBZooCartTable">
    <thead>
    <tr>
        <th class="jbcart-col jbcart-col-image"></th>
        <th class="jbcart-col jbcart-col-name"><?php echo Text::_('JBZOO_CART_ITEM_NAME'); ?></th>
        <th class="jbcart-col jbcart-col-price"><?php if ($config->get('tmpl_price4one', 1)) {
                echo Text::_('JBZOO_CART_ITEM_PRICE');
            } ?></th>
        <th class="jbcart-col jbcart-col-quantity"><?php if ($config->get('tmpl_quntity', 1)) {
                echo Text::_('JBZOO_CART_ITEM_QUANTITY');
            } ?></th>
        <th class="jbcart-col jbcart-col-subtotal"><?php if ($config->get('tmpl_subtotal', 1)) {
                echo Text::_('JBZOO_CART_ITEM_SUBTOTAL');
            } ?></th>
        <th class="jbcart-col jbcart-col-delete"></th>
    </tr>
    </thead>

    <tbody>

    <tr class="jbcart-row-empty">
        <td class="jbcart-cell-empty" colspan="6"></td>
    </tr>

    <?php foreach ($view->itemsHtml as $itemKey => $itemHtml) : ?>
        <tr class="jbcart-row jsCartTableRow js<?php echo $itemKey; ?>" data-key="<?php echo $itemKey; ?>">
            <td class="jbcart-image">
                <?php if ($config->get('tmpl_image_show', 1)) {
                    echo $itemHtml['image'];
                } ?>
            </td>
            <td class="jbcart-name">
                <?php echo $itemHtml['name']; ?>
                <?php if ($config->get('tmpl_sku_show', 1)) {
                    echo $itemHtml['sku'];
                } ?>
                <?php echo $itemHtml['params']; ?>
            </td>
            <td class="jbcart-price"><?php
                if ($config->get('tmpl_price4one', 1)) {

                    // По умолчанию показываем то, что уже посчитал order->renderItems()
                    $priceHtml      = $itemHtml['price4one'];
                    $oldPriceHtml   = null;
                    $newPriceHtml   = null;
                    $hasCartDiscount = false;

                    // Попробуем получить JBPrice и цену с учётом скидки по тем же данным, что в корзине
                    if (isset($view->items[$itemKey])) {
                        $itemData = $view->items[$itemKey];

                        $itemArr = array(
                            'item_id'    => method_exists($itemData, 'get') ? $itemData->get('item_id') : (isset($itemData['item_id']) ? $itemData['item_id'] : null),
                            'element_id' => method_exists($itemData, 'get') ? $itemData->get('element_id') : (isset($itemData['element_id']) ? $itemData['element_id'] : null),
                            'template'   => method_exists($itemData, 'get') ? $itemData->get('template') : (isset($itemData['template']) ? $itemData['template'] : null),
                            'variant'    => method_exists($itemData, 'get') ? $itemData->get('variant') : (isset($itemData['variant']) ? $itemData['variant'] : 0),
                            'variations' => method_exists($itemData, 'get') ? (array)$itemData->get('variations') : (isset($itemData['variations']) ? (array)$itemData['variations'] : array()),
                            'selected'   => method_exists($itemData, 'get') ? (array)$itemData->get('selected') : (isset($itemData['selected']) ? (array)$itemData['selected'] : array()),
                            'quantity'   => method_exists($itemData, 'get') ? $itemData->get('quantity', 1) : (isset($itemData['quantity']) ? $itemData['quantity'] : 1),
                        );

                        if (!empty($itemArr['item_id']) && !empty($itemArr['element_id'])) {
                            $cart    = JBCart::getInstance();
                            $priceEl = $cart->getJBPrice($itemArr); // ElementJBPrice

                            if ($priceEl) {
                                $list = $priceEl->getList($itemArr['variations'], array(
                                    'default'  => $itemArr['variant'],
                                    'template' => $itemArr['template'],
                                    'quantity' => $itemArr['quantity'],
                                    'selected' => $itemArr['selected'],
                                ));

                                try {
                                    $variantObj = $list->get($itemArr['variant']);

                                    foreach ($variantObj->all() as $elem) {
                                        if (method_exists($elem, 'getCartPrices')) {
                                            $cartPrices = $elem->getCartPrices();
                                            if (is_array($cartPrices) && !empty($cartPrices['has_discount']) && isset($cartPrices['price'], $cartPrices['total'])) {
                                                $hasCartDiscount = true;

                                                $origVal = $cartPrices['price'];   // JBCartValue без скидки
                                                $discVal = $cartPrices['total'];   // JBCartValue со скидкой

                                                if (is_object($origVal) && method_exists($origVal, 'html')) {
                                                    $oldPriceHtml = $origVal->html();
                                                }
                                                if (is_object($discVal) && method_exists($discVal, 'html')) {
                                                    $newPriceHtml = $discVal->html();
                                                }
                                            }
                                            break;
                                        }
                                    }
                                } catch (Exception $e) {
                                    // В случае любой ошибки просто оставляем базовую цену
                                }
                            }
                        }
                    }

                    if ($hasCartDiscount && $oldPriceHtml !== null && $newPriceHtml !== null) {
                        // Старая цена перечёркнута, новая обычная
                        echo '<span class="jbcart-price-old"><s>' . $oldPriceHtml . '</s></span> ';
                        echo '<span class="jbcart-price-new">' . $newPriceHtml . '</span>';
                    } else {
                        echo $priceHtml;
                    }
                } ?>
            </td>
            <td class="jbcart-quantity"><?php
                if ($config->get('tmpl_quntity', 1)) {
                    echo $itemHtml['quantityEdit'];
                } ?>
            </td>
            <td class="jbcart-subtotal">
                <?php if ($config->get('tmpl_subtotal', 1)) {
                    echo $itemHtml['totalsum'];
                } ?>
            </td>
            <td class="jbcart-delete">
                <a class="btn btn-danger btn-xs btn-small round jsDelete">
                    <?php echo Text::_('JBZOO_CART_DELETE'); ?>
                </a>
            </td>
        </tr>
    <?php endforeach; ?>

    </tbody>
    <tfoot>

    <?php
    if (!empty($view->items) && !empty($view->modifierPrice)) {
        $this->app->jbassets->less('jbassets:less/cart/modifier.less');
        echo $view->modifierOrderPriceRenderer->render('modifier.default', array('order' => $view->order));
    } ?>

    <tr class="jbcart-row-total">
        <td colspan="3" class="jbcart-total-cell">
            <div class="jbcart-items-in-cart">
                <span class="jbcart-label"><?php echo Text::_('JBZOO_CART_TABLE_TOTAL_COUNT'); ?>:</span>
                <span class="jbcart-value jsTotalCount"><?php echo $order->getTotalCount(); ?></span>
            </div>
            <div class="jbcart-price-of-goods">
                <span class="jbcart-label"><?php echo Text::_('JBZOO_CART_TABLE_SUBTOTAL_SUM'); ?>:</span>
                <span class="jbcart-value jsTotalPrice"><?php echo $order->getTotalForItems()->html(); ?></span>
            </div>
        </td>
        <td class="jbcart-shipping-cell">
            <?php if ($view->shipping) : ?>
                <div class="jbcart-label"><?php echo Text::_('JBZOO_CART_TABLE_SHIPPING'); ?>:</div>
                <div class="jbcart-value jsShippingPrice"><?php echo $order->getShippingPrice()->html(); ?></div>
            <?php endif; ?>
        </td>
        <td colspan="2" class="jbcart-total-price-cell">
            <div class="jbcart-label"><?php echo Text::_('JBZOO_CART_TABLE_TOTAL_SUM'); ?>:</div>
            <div class="jbcart-value jsTotal"><?php echo $order->getTotalSum()->html(); ?></div>
        </td>
    </tr>
    <tr class="jbcart-row-remove">
        <td colspan="6" class="jbcart-delete-all-cell">
            <a class="jsDeleteAll item-delete-all btn btn-danger">
                <?php echo $bootstrap->icon('trash', array('type' => 'white')); ?>
                <?php echo Text::_('JBZOO_CART_REMOVE_ALL'); ?>
            </a>
        </td>
    </tr>
    </tfoot>
</table>
