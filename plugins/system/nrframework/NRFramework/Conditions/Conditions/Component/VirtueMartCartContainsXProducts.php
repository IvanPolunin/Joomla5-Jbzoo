<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class VirtueMartCartContainsXProducts extends VirtueMartBase
{
    /**
     * Shortcode aliases for this Condition
     */
    public static $shortcode_aliases = ['virtuemart.contains_x_products'];

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

	public function value()
	{
		if (!$cartProducts = $this->getCartProducts())
		{
			return false;
		}

		return count($cartProducts);
	}
}