<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikashopPurchasedProduct extends HikashopBase
{
    /**
	 *  Pass check
	 *
	 *  @return  bool
	 */
	public function pass()
	{
		if (!is_array($this->selection) || empty($this->selection))
		{
			return;
		}

        return $this->hasPurchased($this->selection);
    }
    
    /**
     * Returns which given products has the current logged-in user purchased.
     * 
     * @param   array  $product_ids
     * 
     * @return  array
     */
    private function hasPurchased($product_ids = [])
    {
        if (!$product_ids)
        {
            return;
        }

        if (!$user = $this->factory->getUser())
        {
            return;
        }

        if (!$user->id)
        {
            return;
        }

        $query = $this->db->getQuery(true)
            ->clear()
            ->select('DISTINCT op.order_id')
            ->from('#__hikashop_order_product AS op')
            ->leftJoin('#__hikashop_order AS o ON o.order_id = op.order_id')
            ->leftJoin('#__hikashop_user AS u ON u.user_id = o.order_user_id')
            ->where('op.product_id IN (' . implode(',', $product_ids) . ')')
            ->where('o.order_status IN ("confirmed", "shipped")')
            ->where('u.user_cms_id = ' . (int) $user->id);

        $this->db->setQuery($query);

        return $this->db->loadColumn();
    }
}