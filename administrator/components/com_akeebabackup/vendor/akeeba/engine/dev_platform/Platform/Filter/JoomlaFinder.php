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

namespace Akeeba\Engine\Filter;

use Akeeba\Engine\Factory;
use Akeeba\Engine\Filter\Base as FilterBase;

// Protection against direct access
defined('AKEEBAENGINE') or die();

/**
 * Joomla!-specific Filter: Finder
 *
 * Exclude Finder (Smart Search) tables
 */
class JoomlaFinder extends FilterBase
{	
	function __construct()
	{
		$this->object	= 'dbobject';
		$this->subtype	= 'content';
		$this->method	= 'api';
		$this->filter_name = 'JoomlaFinder';
		
		if (Factory::getConfiguration()->get('akeeba.platform.scripttype', 'generic') !== 'joomla')
		{
			$this->enabled = false;
		}
	}

	protected function is_excluded_by_api($test, $root)
	{
		static $finderTables = array(
			'#__finder_links', '#__finder_links_terms0', '#__finder_links_terms1',
			'#__finder_links_terms2', '#__finder_links_terms3', '#__finder_links_terms4',
			'#__finder_links_terms5', '#__finder_links_terms6', '#__finder_links_terms7',
			'#__finder_links_terms8', '#__finder_links_terms9', '#__finder_links_termsa',
			'#__finder_links_termsb', '#__finder_links_termsc', '#__finder_links_termsd',
			'#__finder_links_termse', '#__finder_links_termsf', '#__finder_taxonomy',
			'#__finder_taxonomy_map', '#__finder_terms'
		);
		
		// Not the site's database? Include the tables
		if($root != '[SITEDB]') return false;
		
		// Is it one of the blacklisted tables?
		if(in_array($test, $finderTables)) return true;

		// No match? Just include the file!
		return false;
	}

}
