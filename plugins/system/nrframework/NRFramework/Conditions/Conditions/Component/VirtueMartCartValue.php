<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class VirtueMartCartValue extends VirtueMartBase
{
	/**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['virtuemart.cart_value'];
  
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
	 *  Pass check
	 *
	 *  @return  bool
	 */
	public function pass()
	{
		return $this->passAmountInCart();
    }

    /**
	 * Returns the cart total billable cost
	 * 
	 * @return  float
	 */
	protected function getCartTotal()
	{
		if (!$cart = $this->getCart())
		{
			return 0;
		}

		if (!isset($cart->cartPrices['billTotal']))
		{
			return 0;
		}

		@include_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/currencydisplay.php';

		if (!class_exists('CurrencyDisplay'))
		{
			return 0;
		}

		$currency = \CurrencyDisplay::getInstance();

		return $currency->roundByPriceConfig($cart->cartPrices['billTotal']);
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

		if (!isset($cart->cartPrices['basePrice']))
		{
			return 0;
		}
		
		@include_once JPATH_ADMINISTRATOR . '/components/com_virtuemart/helpers/currencydisplay.php';

		if (!class_exists('CurrencyDisplay'))
		{
			return 0;
		}

		$currency = \CurrencyDisplay::getInstance();

		return $currency->roundByPriceConfig($cart->cartPrices['basePrice']);
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

		if (!isset($cart->cartPrices['shipmentValue']) || !isset($cart->cartPrices['shipmentTax']))
		{
			return 0;
		}
		
		return $cart->cartPrices['shipmentValue'] + $cart->cartPrices['shipmentTax'];
	}
}