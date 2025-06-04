<?php

/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Conditions\Conditions;

defined('_JEXEC') or die;

use NRFramework\Conditions\Condition;
/**
 * @deprecated Use the NewVisitor condition instead.
 */
class ReturningNewVisitor extends Condition
{

	public function pass()
	{
		// Get visitor instance
		$visitor = new \NRFramework\Visitor();

		// Create and update cookies as needed
		$visitor->createOrUpdateCookie();

		// Check if user is new
		$isNew = $visitor->isNew();

		return $this->operator === 'new' ? $isNew : !$isNew;
	}
}