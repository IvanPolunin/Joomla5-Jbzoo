<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

// No direct access to this file
defined('_JEXEC') or die;

use Joomla\CMS\Form\Field\CheckboxField;
use NRFramework\HTML;

class JFormFieldNRToggle extends CheckboxField
{
	/**
	 * On state value
	 *
	 * @var int
	 */
	protected $on_value = 1;

	/**
	 * Off state value
	 *
	 * @var int
	 */
	protected $off_value = 0;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string
	 */
	public function getInput()
	{
		HTML::stylesheet('plg_system_nrframework/toggle.css');

		$required = $this->required ? ' required aria-required="true"' : '';
		$checked  = $this->checked ? ' checked' : '';
		$class	  = !empty($this->class) ? ' ' . $this->class : '';

		// Fix bug inherited from the Checkbox field where the input remains checked even if save it unchecked.
		if ($this->checked && (string) $this->value == (string) $this->off_value)
		{
			$checked = '';
		}

		return '
			<span class="nrtoggle' . $class . '">
				<input type="hidden" name="' . $this->name . '" id="' . $this->id . '_" value="' . $this->off_value . '">
				<input type="checkbox" name="' . $this->name . '" id="' . $this->id . '" value="'
				. htmlspecialchars($this->on_value, ENT_COMPAT, 'UTF-8') . '"' . $checked . $required . ' />
				<label for="' . $this->id . '"></label>
			</span>
		';
	}
}