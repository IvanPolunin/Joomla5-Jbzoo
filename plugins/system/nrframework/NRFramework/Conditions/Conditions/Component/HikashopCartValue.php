<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikashopCartValue extends HikashopBase
{
    public function prepareSelection()
    {
		if ($this->operator === 'range')
		{
			return [
				'value1' => (float) $this->options->get('selection'),
				'value2' => (float) $this->options->get('params.value2', false)
			];
		}

		return (float) $this->options->get('selection');
    }

    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['hikashopcartvalue'];

    /**
	 *  Pass check
	 *
	 *  @return  bool
	 */
	public function pass()
	{
		return $this->passAmountInCart();
    }

    /**
	 * Returns the cart total.
	 * 
	 * @return  float
	 */
	public function getCartTotal()
	{
		if (!$cart = $this->getCart())
		{
			return 0;
		}

		if (!isset($cart->full_total->prices[0]->price_value_with_tax))
		{
			return 0;
		}

		return $cart->full_total->prices[0]->price_value_with_tax;
	}

    /**
	 * Returns the cart subtotal.
	 * 
	 * @return  float
	 */
	public function getCartSubtotal()
	{
		if (!$cart = $this->getCart())
		{
			return 0;
		}

		if (isset($cart->full_total->prices[0]->price_value_without_shipping))
		{
			return $cart->full_total->prices[0]->price_value_without_shipping;
		}

		if (isset($cart->full_total->prices[0]->price_value_without_payment))
		{
			return $cart->full_total->prices[0]->price_value_without_payment;
		}

		return 0;
	}

	/**
	 * Returns the shipping total.
	 * 
	 * @return  float
	 */
	protected function getShippingTotal()
	{
		if (!$cart = $this->getCart())
		{
			return 0;
		}

		if (!isset($cart->shipping))
		{
			return 0;
		}

		if (!is_array($cart->shipping))
		{
			return 0;
		}

		if (!count($cart->shipping))
		{
			return 0;
		}

		$total_fees = 0;
		foreach ($cart->shipping as $item)
		{
			$total_fees += (float) $item->shipping_price;
		}

		return $total_fees;
	}
}