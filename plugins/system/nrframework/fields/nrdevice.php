<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;

require_once JPATH_PLUGINS . '/system/nrframework/helpers/fieldlist.php';

class JFormFieldNRDevice extends NRFormFieldList
{
	/**
	 *  Browsers List
	 *
	 *  @var  array
	 */
	public $options = array(
        'desktop' => 'NR_DESKTOPS',
        'mobile'  => 'NR_MOBILES',
        'tablet'  => 'NR_TABLETS'
	);

	protected function getOptions()
	{
		asort($this->options);

		foreach ($this->options as $key => $option)
		{
			$options[] = HTMLHelper::_('select.option', $key, Text::_($option));
		}

		return array_merge(parent::getOptions(), $options);
	}
}