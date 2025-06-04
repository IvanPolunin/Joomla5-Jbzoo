<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

Use Joomla\CMS\Factory;

class VirtueMartPurchasedProduct extends VirtueMartBase
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

        if (!$user = Factory::getUser())
        {
            return;
        }

        if (!$user->id)
        {
            return;
        }

        $query = $this->db->getQuery(true)
            ->clear()
            ->select('DISTINCT o.virtuemart_order_id')
            ->from('#__virtuemart_orders AS o')
            ->leftJoin('#__virtuemart_order_items AS oi ON oi.virtuemart_order_id = o.virtuemart_order_id')
            ->where('oi.virtuemart_product_id IN (' . implode(',', $product_ids) . ')')
            ->where('o.order_status IN ("C", "S", "F")')
            ->where('o.virtuemart_user_id = ' . (int) $user->id);

        $this->db->setQuery($query);

        return $this->db->loadColumn();
    }
}