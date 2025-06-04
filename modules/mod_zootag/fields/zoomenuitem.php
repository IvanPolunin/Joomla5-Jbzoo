<?php
/**
 * @package   ZOO Tag
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Form\Field\MenuitemField;
use Joomla\CMS\Language\Text;

defined('JPATH_BASE') or die;

class JFormFieldZooMenuItem extends MenuitemField {

	public $type = 'ZooMenuItem';

	protected function getGroups()	{

		// get app instance
		$app = App::getInstance('zoo');

		// Merge the select item option into existing groups
		return array_merge(array(array($app->html->_('select.option', '', '- '.Text::_('Select Item').' -', 'value', 'text'))), parent::getGroups());

	}

}
