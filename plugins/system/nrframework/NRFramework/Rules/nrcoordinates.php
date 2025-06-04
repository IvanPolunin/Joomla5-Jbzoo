<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\FormRule;

class JFormRuleNRCoordinates extends FormRule
{
	/**
	 * The regular expression to use in testing a form field value.
	 *
	 * @var    string
	 * @since  11.1
	 * @link   http://www.w3.org/TR/html-markup/input.email.html
	 */
	protected $regex = '^[-+]?([1-8]?\d(\.\d+)?|90(\.0+)?),\s*[-+]?(180(\.0+)?|((1[0-7]\d)|([1-9]?\d))(\.\d+)?)$';

	public function test(SimpleXMLElement $element, $value, $group = null, Joomla\Registry\Registry $input = null, Joomla\CMS\Form\Form $form = null)
	{
		$value = trim($value);

		// If the field is empty and not required, the field is valid.
		$required = ((string) $element['required'] == 'true' || (string) $element['required'] == 'required');

		if (!$required && empty($value))
		{
			return true;
		}

		// Test the value against the regular expression.
		return parent::test($element, $value, $group, $input, $form);
	}
}