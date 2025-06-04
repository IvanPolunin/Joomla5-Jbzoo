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

class ConvertForms extends Condition
{
    /**
     *  Returns the assignment's value
     * 
     *  @return array List of campaign IDs
     */
	public function value()
	{
		return $this->getCampaigns();
	}

    /**
     *  Returns campaigns list visitor is subscribed to
     *  If the user is logged in, we try to get the campaigns by user's ID
     *  Otherwise, the visitor cookie ID will be used instead
     *
     *  @return  array  List of campaign IDs
     */
	private function getCampaigns()
	{
		$class = '\ConvertForms\Helper';

		if (!class_exists($class))
		{
			return;
		}

		return $class::getVisitorCampaigns();
	}
}