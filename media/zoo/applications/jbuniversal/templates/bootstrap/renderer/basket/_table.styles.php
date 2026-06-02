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

?>

<style type="text/css">
    @media (max-width: 767px) {

        .jbcart-table tbody .jbcart-row .jbcart-name:before {
            content: "<?php echo JText::_('JBZOO_CART_ITEM_NAME'); ?>";
        }

        .jbcart-table tbody .jbcart-row .jbcart-price:before {
            content: "<?php echo JText::_('JBZOO_CART_ITEM_PRICE'); ?>";
        }

        .jbcart-table tbody .jbcart-row .jbcart-quantity:before {
            content: "<?php echo JText::_('JBZOO_CART_ITEM_QUANTITY'); ?>";
        }

        .jbcart-table tbody .jbcart-row .jbcart-subtotal:before {
            content: "<?php echo JText::_('JBZOO_CART_ITEM_SUBTOTAL'); ?>";
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper {
            position: relative;
            display: inline-block;
            width: auto;
            padding-right: 30px;
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper tbody,
        .jbcart-table .jbcart-quantity .quantity-wrapper tr,
        .jbcart-table .jbcart-quantity .quantity-wrapper td {
            display: block;
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper td[rowspan] {
            vertical-align: top;
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper .jsCountBox {
            margin: 0;
            padding-top: 4px;
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper .plus,
        .jbcart-table .jbcart-quantity .quantity-wrapper .minus {
            position: absolute;
            right: 0;
            width: 26px;
            height: 24px;
            line-height: 24px;
            text-align: center;
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper .plus {
            top: 0;
        }

        .jbcart-table .jbcart-quantity .quantity-wrapper .minus {
            bottom: 0;
        }

        .jbcart-table .jbcart-quantity .input-quantity {
            position: static !important;
            display: block;
            text-align: center;
            height: 40px;
            line-height: 40px;
            padding: 0;
            vertical-align: middle;
        }

        .jbcart-table .jbcart-quantity .item-count {
            display: flex;
            align-items: center;
            height: 40px;
        }

        .jbcart-table .jbcart-delete {
            vertical-align: middle;
        }

        .jbcart-table .jbcart-delete .btn {
            display: inline-block;
            margin: 0;
        }

        .jbcart-form-checkbox .jbcart-form-control div > div {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .jbcart-form-checkbox .jbcart-form-control input[type="checkbox"] {
            margin-top: 0;
            width: 10% !important;
        }
    }
</style>
