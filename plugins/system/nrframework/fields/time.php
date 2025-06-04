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

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNR_Time extends NRFormField
{

	/**
	 * Sets the time value
	 * 
	 * @var  string  $time_value
	 */
	private $time_value = null;

	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	public function getInput()
	{
		// Setup properties
		$this->hint      = $this->get('hint', '00:00');
		$this->class     = $this->get('class');
		$this->default   = $this->get('default', 'now');
		$this->required  = $this->get('required') === 'true';
		$placement 		 = $this->get('placement', 'top');
		$align     		 = $this->get('align', 'left');
		$autoclose 		 = $this->get('autoclose', 'true');
		$donetext  		 = $this->get('donetext', 'Done');

		/**
		 * When an object is created using this class, it cannot set $this->value
		 * So we set $time_value and then use it's value to display the time
		 */
		$this->value = $this->time_value ? $this->time_value : $this->value;
		$this->value = is_null($this->value) ? '' : $this->value;

		// Add styles and scripts to DOM
		HTMLHelper::_('jquery.framework');
		HTMLHelper::script('plg_system_nrframework/vendor/jquery-clockpicker.min.js', ['relative' => true, 'version' => true]);
		HTMLHelper::stylesheet('plg_system_nrframework/vendor/jquery-clockpicker.min.css', ['relative' => true, 'version' => true]);

		static $run;
		// Run once to initialize it
		if (!$run)
		{
			$this->doc->addScriptDeclaration('
				jQuery(function($) {
					$(".clockpicker").clockpicker();
				});
        	');

			// Fix a CSS conflict caused by the template.css on Joomla 3
			if (!defined('nrJ4'))
			{
				// Fuck you template.css
				$this->doc->addStyleDeclaration('
					.clockpicker-align-left.popover > .arrow {
					    left: 25px;
					}
				');
			}

			$run = true;
		}

		return '
			<div class="clockpicker" data-donetext="' . $donetext . '" data-default="' . $this->default . '" data-placement="' . $placement . '" data-align="' . $align . '" data-autoclose="' . $autoclose . '">
				' . parent::getInput() . '
			</div>';
	}

	/**
	 * Sets the $time_value of the time when created as an object
	 * due to not being able to set the $this->value byitself
	 * 
	 * @param   string  $value
	 * 
	 * @return  void
	 */
	public function setValue($value)
	{
		$this->time_value = $value;
	}
}