<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikashopTotalSpend extends HikashopBase
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
	 *  Returns the condtion value.
	 * 
	 *  @return  float
	 */
	public function value()
	{
		if (!$user = $this->factory->getUser())
        {
            return;
        }

        if (!$user->id)
        {
            return;
        }

		$db = $this->db;

		$query = $db->getQuery(true)
			->clear()
			->select('SUM(o.order_full_price) AS total')
			->from('#__hikashop_order AS o')
			->leftJoin('#__hikashop_user AS u ON u.user_id = o.order_user_id')
			->where('o.order_status IN (\'shipped\', \'confirmed\')')
			->where('u.user_cms_id = ' . (int) $user->id);

		$db->setQuery($query);

		return (float) $db->loadResult();
	}
}