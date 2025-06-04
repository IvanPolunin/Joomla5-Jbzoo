<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Pagination\Pagination;

/**
 * Helper for dealing with pagination
 *
 * @package Framework.Helpers
 */
class PaginationHelper extends AppHelper {

	/**
	 * Create a JPagination object
	 *
	 * @param int $total The total number of items
	 * @param int $limitstart The starting number of the pagination
	 * @param int $limit The limit of the current pagination
	 * @param string $name The name of the pagination object
	 * @param string $type The type of the paginator object class
	 *
	 * @return Pagination The pagination object
	 *
	 * @since 1.0.0
	 */
	public function create($total, $limitstart, $limit, $name = '', $type = '') {

		if (empty($type)) {
			return new Pagination($total, $limitstart, $limit);
		}

		// load Pagination class
		$class = $type.'Pagination';
		$this->app->loader->register($class, 'classes:pagination.php');

		return $this->app->object->create($class, array($name, $total, $limitstart, $limit));

	}

}
