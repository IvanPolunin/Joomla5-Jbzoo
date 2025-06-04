<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Form\FormHelper;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNR_Inline extends NRFormField
{
	public function renderField($options = [])
	{
		HTMLHelper::stylesheet('plg_system_nrframework/inline-control-group.css', ['relative' => true, 'version' => 'auto']);

		$start = $this->get('start', 1);
		$end   = $this->get('end', 0);

		if ($start && !$end)
		{
			// Apply showon on the start of the inline field
			$showon = '';
			if ($dataShowon = $this->get('showon'))
			{
				$dataShowon = FormHelper::parseShowOnConditions($dataShowon, $this->formControl, $this->group);
				$showon = ' data-showon="' . htmlspecialchars(json_encode($dataShowon)) . '"';
			}
			
			$label = $this->getLabel() ? '<div class="control-label">' . $this->getLabel() . '</div>' : '';
			
			return '<div class="control-group"' . $showon . '>' . $label . '<div class="controls"><div class="inline-control-group' . ($this->class ? ' ' . $this->class : '') . '">';
		}

		return '</div></div>' . ($this->getLabel() ? '</div>' : '') . '</div>';
	}
}