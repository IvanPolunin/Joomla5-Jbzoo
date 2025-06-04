<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Layout\FileLayout;

class JFormFieldTFAddressLookup extends FormField
{
	public function getInput()
	{
		$group_class = isset($this->element['group_class']) ? (string) $this->element['group_class'] : 'stack mb-0';
		$label = isset($this->element['label']) ? (string) $this->element['label'] : 'NR_ADDRESS';
		$autocomplete = true;

		if (isset($this->element['autocomplete']) & (string) $this->element['autocomplete'] === 'false')
		{
			$autocomplete = false;
		}

		Text::script('NR_UNTITLED_MARKER');

		$payload = [
			'id' => $this->id,
			'label' => $label,
			'name' => $this->name,
			'value' => $this->value,
			'visible' => true,
			'autocomplete' => $autocomplete,
			'group_class' => $group_class
		];

        $layout = new FileLayout('addresslookup', JPATH_PLUGINS . '/system/nrframework/layouts');
        return $layout->render($payload);
	}
}