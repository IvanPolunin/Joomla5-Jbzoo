<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions\Component;

defined('_JEXEC') or die;

class HikashopCategoryView extends HikashopBase
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
		
        return $this->passCategories('hikashop_category', 'category_parent_id');
    }

	/**
	 *  Returns all parent rows
	 *
	 *  @param   integer  $id      Row primary key
	 *  @param   string   $table   Table name
	 *  @param   string   $parent  Parent column name
	 *  @param   string   $child   Child column name
	 *
	 *  @return  array             Array with IDs
	 */
	public function getParentIds($id = 0, $table = 'hikashop_category', $parent = 'category_parent_id', $child = 'category_id')
	{
		return parent::getParentIds($id, $table, $parent, $child);
	}
}