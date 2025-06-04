<?php
/**
 * Akeeba Engine
 *
 * @package   akeebaengine
 * @copyright Copyright (c)2006-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   https://www.gnu.org/licenses/gpl-3.0.html GNU General Public License version 3, or later
 *
 * This program is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, version 3.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with this program. If not, see
 * <https://www.gnu.org/licenses/>.
 */

namespace Akeeba\Engine\Filter\Stack;

use Akeeba\Engine\Filter\Base;

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Exclude Joomla 3.9+ actions log table
 */
class StackActionlogs extends Base
{
    public function __construct()
    {
        $this->object  = 'dbobject';
        $this->subtype = 'content';
        $this->method  = 'api';

        parent::__construct();
    }

    protected function is_excluded_by_api($test, $root)
    {
	    static $excluded = array(
		    '#__action_logs',
	    );

	    // Is it one of the blacklisted tables?
	    if(in_array($test, $excluded))
	    {
	    	return true;
	    }

	    // No match? Just include the file!
	    return false;
    }

}
