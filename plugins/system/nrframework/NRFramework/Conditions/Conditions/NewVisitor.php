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

class NewVisitor extends Condition
{
    public static $shortcode_aliases = ['isnewvisitor'];

	/**
	 * Checks whether the visitor is new or returning
	 *
	 * @return boolean True when visitor is new
	 */
	public function value() 
	{
		$visitor = new \NRFramework\Visitor();
		$visitor->createOrUpdateCookie();

		return $visitor->isNew();
	}
}