<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class VirtueMartCategoryView extends VirtueMartBase
{
    /**
	 *  Pass check
	 *
	 *  @return  bool
	 */
	public function pass()
	{
		if (!$this->isCategoryPage())
		{
			return false;
		}

		$this->params->set('view_category', true);
		$this->params->set('view_single', false);
		
        return $this->passCategories('virtuemart_categories', 'category_parent_id');
    }
}