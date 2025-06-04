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

class JFormFieldNROs extends NRFormFieldList
{
	/**
	 *  Browsers List
	 *
	 *  @var  array
	 */
	public $options = array(
        'linux'      => 'NR_LINUX',
        'mac'        => 'NR_MAC',
        'android'    => 'NR_ANDROID',
        'ios'        => 'NR_IOS',
        'windows'    => 'NR_WINDOWS',
        'blackberry' => 'NR_BLACKBERRY',
        'chromeos'   => 'NR_CHROMEOS'
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