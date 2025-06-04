<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\GroupedlistField;
use Joomla\CMS\HTML\HTMLHelper;

class JFormFieldNRFonts extends GroupedlistField
{
	/**
	 * Method to get the field option groups.
	 *
	 * @return  array  The field option objects as a nested array in groups.
	 *
	 * @since   1.6
	 */
	protected function getGroups()
	{
		$groups = array();

		foreach (NRFramework\Fonts::getFontGroups() as $name => $fontGroup)
		{
			// Initialize the group if necessary.
			if (!isset($groups[$name]))
			{
				$groups[$name] = array();
			}

			foreach ($fontGroup as $font)
			{
				$groups[$name][] = HTMLHelper::_('select.option', $font, $font);
			}
		}

		// Merge any additional groups in the XML definition.
		return array_merge(parent::getGroups(), $groups);
	}
}