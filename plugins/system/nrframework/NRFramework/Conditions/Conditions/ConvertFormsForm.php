<?php

/**
 * @author          Tassos.gr <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework\Conditions\Conditions;

defined('_JEXEC') or die;

use NRFramework\Conditions\Condition;

class ConvertFormsForm extends Condition
{
    /**
     * Returns the condition value.
     * 
     * @return  array
     */
	public function value()
	{
		return $this->getForms();
	}

    /**
     * Returns all form IDs submitted by the visitor.
     * If the user is logged in, we try to get the forms by user's ID
     * Otherwise, the visitor cookie ID will be used instead.
     *
     * @return  array
     */
	private function getForms()
	{
		$class = '\ConvertForms\Helper';

		if (!class_exists($class))
		{
			return;
		}

		// Sanity check
		if (!method_exists($class, 'getVisitorSubmittedForms'))
		{
			return;
		}

		return $class::getVisitorSubmittedForms();
	}
}