<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikashopCartContainsProducts extends HikashopBase
{
    public function prepareSelection()
    {
		return $this->getPreparedSelection();
    }

    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['hikashop.cart_contains_products'];

    /**
	 *  Pass check
	 *
	 *  @return  bool
	 */
	public function pass()
	{
		return $this->passProductsInCart(['product_id', 'cart_product_parent_id'], 'cart_product_quantity');
    }
}