<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Toolbar\ToolbarHelper as BaseToolbarHelper;

/**
 * Helper for dealing with the toolbar. Wrapper for JToolBarHelper
 *
 * @package Framework.Helpers
 *
 * @see JToolBarHelper
 */
class ToolbarHelper extends AppHelper {

	/**
	 * Map all the methods to the JToolBarHelper class
	 *
	 * @param string $method The name of the method
	 * @param array $args The list of arguments to pass on to the method
	 *
	 * @return mixed The result of the call
	 *
	 * @see BaseToolbarHelper
	 *
	 * @since 1.0.0
	 */
    public function __call($method, $args) {
		return $this->_call([BaseToolbarHelper::class, $method], $args);
    }

}
