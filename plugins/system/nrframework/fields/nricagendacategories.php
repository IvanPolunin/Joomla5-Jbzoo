<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die;

require_once __DIR__ . '/treeselect.php';

class JFormFieldNRICagendaCategories extends JFormFieldNRTreeSelect
{
	/**
	 * Indicates whether the options array should be sorted before render.
	 *
	 * @var boolean
	 */
	protected $sortTree = true;

	/**
	 * Get a list of all iCagenda Categories
	 *
	 * @return void
	 */
	protected function getOptions()
	{
		// Get a database object.
		$db = $this->db;

		$query = $db->getQuery(true)
			->select('id as value, title as text, 0 as level, 0 as parent, IF (state=1, 0, 1) as disable')
			->from('#__icagenda_category');
			
		$db->setQuery($query);

		return $db->loadObjectList();
	}
}
