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

class JFormFieldNRBrowser extends NRFormFieldList
{
	/**
	 *  Browsers List
	 *
	 *  @var  array
	 */
	public $options = array(
		'chrome'  => 'NR_CHROME',
		'firefox' => 'NR_FIREFOX',
		'edge'    => 'NR_EDGE',
		'ie'      => 'NR_IE',
		'safari'  => 'NR_SAFARI',
		'opera'   => 'NR_OPERA'
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