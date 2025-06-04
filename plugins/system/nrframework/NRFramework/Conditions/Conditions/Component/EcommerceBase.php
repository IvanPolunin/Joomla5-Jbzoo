<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

class EcommerceBase extends ComponentBase
{
	/**
	 * Pass method for "Amount In Cart" condition.
	 * 
	 * @return  bool
	 */
	public function passAmountInCart()
	{
		// Whether we exclude shipping cost
		$exclude_shipping_cost = $this->params->get('exclude_shipping_cost', '0') === '1';

		$shipping_total = 0;
		$amount = 0;

		switch ($this->params->get('total', 'total'))
		{
			case 'total':
				$amount = $this->getCartTotal();
				if ($exclude_shipping_cost)
				{
					$shipping_total = -$this->getShippingTotal();
				}
				break;
			
			case 'subtotal':
				$amount = $this->getCartSubtotal();
				if (!$exclude_shipping_cost)
				{
					$shipping_total = $this->getShippingTotal();
				}
				break;
		}

		// Calculate final amount
		$amount = $amount + $shipping_total;

		$operator = $this->options->get('operator', 'equal');

		$selection = (float) $this->selection;

		// Range selection
		if ($operator === 'range')
		{
			$selection = [
				'value1' => $selection,
				'value2' => (float) $this->options->get('params.value2', false)
			];
		}

        return $this->passByOperator($amount, $selection, $operator);
	}

	/**
	 * Pass method for "Products In Cart" condition.
	 * 
	 * @param   string  $cart_product_item_id_key
	 * 
	 * @return  bool
	 */
	protected function passProductsInCart($cart_product_item_id_key = ['id'], $product_prop_key = 'quantity')
	{
		// Get cart products
		if (!$cartProducts = $this->getCartProducts())
		{
			return false;
		}

		// Get condition products
		if (!$conditionProducts = $this->selection)
		{
			return false;
		}

		if (!is_array($conditionProducts))
		{
			return false;
		}

		// Ensure all condition's products exist in the cart
		$foundCartProducts = array_filter(
			$cartProducts,
			function ($prod) use ($conditionProducts, $cart_product_item_id_key, $product_prop_key)
			{
				$prod = (array) $prod;

				// Check the ID first
				foreach ($cart_product_item_id_key as $id_key)
				{
					$valid = array_filter($conditionProducts, function($item) use ($prod, $id_key) {
						return isset($item['value']) && (int) $item['value'] === (int) $prod[$id_key];
					});

					if ($valid)
					{
						break;
					}
				}

				// If not valid, abort
				if (!$valid)
				{
					return;
				}

				// Get valid product
				$valid_product = reset($valid);

				// Ensure product has property
				$product_property_value = isset($prod[$product_prop_key]) ? (int) $prod[$product_prop_key] : false;
				if (!$product_property_value)
				{
					return $valid;
				}

				// We need an operator other than "any"
				if (!isset($valid_product['operator']) || $valid_product['operator'] === 'any')
				{
					return $valid;
				}
			
				// Ensure value 1 is valid
				$product_value1 = isset($valid_product['value1']) ? (int) $valid_product['value1'] : false;
				if (!$product_value1)
				{
					return $valid;
				}

				$product_value2 = isset($valid_product['value2']) ? (int) $valid_product['value2'] : false;

				// Default selection
				$selection = $product_value1;

				// Range selection
				if ($valid_product['operator'] === 'range')
				{
					$selection = [
						'value1' => $product_value1,
						'value2' => $product_value2
					];
				}

				return $this->passByOperator($product_property_value, $selection, $valid_product['operator']);
			}
		);

		return count($foundCartProducts);
	}

	/**
	 * Pass method for "Last Purchase Date" condition.
	 * 
	 * @return  bool
	 */
	protected function passLastPurchaseDate()
	{
        if (!$user = Factory::getUser())
        {
            return;
        }

        if (!$user->id)
        {
            return;
        }

		if (!$purchase_date = $this->getLastPurchaseDate($user->id))
		{
			return;
		}

		$purchaseDate = new \DateTime('@' . $purchase_date);
		$purchaseDate->setTimezone(new \DateTimeZone('UTC'));
		$purchaseDate->setTime(0,0);

		$currentDate = new \DateTime('now', new \DateTimeZone('UTC'));

		$pass = false;

		$operator = $this->options->get('params.operator', 'within_hours');

		switch ($operator)
		{
			case 'within_hours':
			case 'within_days':
			case 'within_weeks':
			case 'within_months':
				if (!$within_value = intval($this->options->get('params.within_value')))
				{
					return;
				}
				$period = str_replace('within_', '', $operator);

				$timeframe = strtoupper($period[0]);

				// Hours requires a "T"
				if ($timeframe === 'H')
				{
					$within_value = 'T' . $within_value;
				}

				$interval = new \DateInterval("P{$within_value}{$timeframe}");
				$purchaseDateXDaysAgo = (clone $purchaseDate)->add($interval);
				$interval->invert = 1; // Set invert to 1 to indicate past time

				$pass = $purchaseDateXDaysAgo >= $currentDate;

				break;
			
			case 'equal':
				if (!$this->selection)
				{
					return;
				}

				$selectionDate = new \DateTime($this->selection, new \DateTimeZone('UTC'));

				$pass = $purchaseDate->format('Y-m-d') === $selectionDate->format('Y-m-d');
				break;
			case 'before':
				if (!$this->selection)
				{
					return;
				}

				$selectionDate = new \DateTime($this->selection, new \DateTimeZone('UTC'));

				$pass = $purchaseDate < $selectionDate;

				break;
			case 'after':
				if (!$this->selection)
				{
					return;
				}

				$selectionDate = new \DateTime($this->selection, new \DateTimeZone('UTC'));

				$pass = $purchaseDate > $selectionDate;
				break;
			case 'range':
				if (!$secondDate = $this->options->get('params.value2'))
				{
					return;
				}

				if (!$this->selection)
				{
					return;
				}

				$startDate = new \DateTime($this->selection, new \DateTimeZone('UTC'));
				$endDate = new \DateTime($secondDate, new \DateTimeZone('UTC'));

				$pass = $purchaseDate >= $startDate && $purchaseDate <= $endDate;
				break;
		}

		return $pass;
	}

	/**
	 * Pass method for "Current Product Price" condition.
	 * 
	 * @return  bool
	 */
	public function passCurrentProductPrice()
	{
		// Ensure we are viewing a product page
		if (!$this->isSinglePage())
		{
			return;
		}

		if (!$this->selection)
		{
			return;
		}
	
		// Get current product data
		if (!$product_data = $this->getCurrentProductData())
		{
			return;
		}

		// Get value 1
		$selection = (float) $this->options->get('selection');

		// Range selection
		if ($this->operator === 'range')
		{
			$value2 = (float) $this->options->get('params.value2');
	
			$selection = [
				'value1' => $selection,
				'value2' => $value2
			];
		}

		return $this->passByOperator($product_data['price'], $selection, $this->operator);
	}

	/**
	 * Pass method for "Current Product Stock" condition.
	 * 
	 * @return  bool
	 */
	public function passCurrentProductStock()
	{
		// Ensure we are viewing a product page
		if (!$this->isSinglePage())
		{
			return;
		}
		
		if (!$this->selection)
		{
			return;
		}

		$current_product_id = $this->request->id;

		if (!$product_stock = $this->getProductStock($current_product_id))
		{
			return;
		}
		
		// Get value 1
		$selection = (int) $this->options->get('selection');

		// Range selection
		if ($this->operator === 'range')
		{
			$value2 = (int) $this->options->get('params.value2');
	
			$selection = [
				'value1' => $selection,
				'value2' => $value2
			];
		}

		return $this->passByOperator($product_stock, $selection, $this->operator);
	}

	protected function getPreparedSelection()
	{
		$selection = $this->getSelection();

		if (!is_array($selection))
		{
			return $selection;
		}
		
		foreach ($selection as &$value)
		{
			if (!is_array($value))
			{
				continue;
			}
			
			$params = isset($value['params']) ? $value['params'] : [];
			if ($params)
			{
				if (isset($params['value']))
				{
					$params['value1'] = $params['value'];
				}
				unset($params['value']);
				unset($value['params']);
			}
			$value = array_merge($value, $params);
		}

		return $selection;
	}

    /**
     * Get single page's assosiated categories
     *
     * @param   Integer  The Single Page id
	 * 
     * @return  array
     */
    protected function getSinglePageCategories($id) {}
}