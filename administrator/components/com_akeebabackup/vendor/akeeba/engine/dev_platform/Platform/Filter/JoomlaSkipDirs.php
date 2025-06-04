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
 * Joomla!-specific Filter: Skip Directories
 *
 * Exclude subdirectories of special directories
 */
class JoomlaSkipDirs extends FilterBase
{	
	function __construct()
	{
		$this->object	= 'dir';
		$this->subtype	= 'children';
		$this->method	= 'direct';
		$this->filter_name = 'JoomlaSkipDirs';

		$configuration = Factory::getConfiguration();
		
		if ($configuration->get('akeeba.platform.scripttype', 'generic') !== 'joomla')
		{
			$this->enabled = false;
			return;
		}

		$root = $configuration->get('akeeba.platform.newroot', '[SITEROOT]');

		$this->filter_data[$root] = array (
			// Output & temp directory of the application
			$this->treatDirectory($configuration->get('akeeba.basic.output_directory')),
			// default temp directory
			'tmp',
			// cache directories
			'cache',
			'administrator/cache',
			// This is not needed except on sites running SVN or beta releases
			'installation',
			// Default backup output for Akeeba Backup
			'administrator/components/com_akeeba/backup',
			// MyBlog's cache
			'components/libraries/cmslib/cache',
			// The logs directory
			// -- Joomla! 1.0 - 3.5
			'logs',
			'log',
			// -- Joomla! 3.6+
			'administrator/log',
			'administrator/logs',
		);
	}
}
