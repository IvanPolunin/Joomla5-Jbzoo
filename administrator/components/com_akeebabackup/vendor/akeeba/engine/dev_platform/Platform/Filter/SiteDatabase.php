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
 * Add site's main database to the backup set.
 */
class SiteDatabase extends FilterBase
{
	public function __construct()
	{
		// This is a directory inclusion filter.
		$this->object      = 'db';
		$this->subtype     = 'inclusion';
		$this->method      = 'direct';
		$this->filter_name = 'SiteDatabase';

		// Add a new record for the core Joomla! database
		// Get core database options
		$configuration = Factory::getConfiguration();

		$options = [
			'port'     => $configuration->get('akeeba.platform.dbport', ''),
			'host'     => $configuration->get('akeeba.platform.dbhost', ''),
			'user'     => $configuration->get('akeeba.platform.dbusername', ''),
			'password' => $configuration->get('akeeba.platform.dbpassword', ''),
			'database' => $configuration->get('akeeba.platform.dbname', ''),
			'prefix'   => $configuration->get('akeeba.platform.dbprefix', ''),
			'ssl'      => [
				'enable'             => $configuration->get('akeeba.platform.dbencryption', '0') == 1,
				'cipher'             => $configuration->get('akeeba.platform.dbsslcipher', ''),
				'ca'                 => $configuration->get('akeeba.platform.dbsslca', ''),
				'capath'             => $configuration->get('akeeba.platform.dbsslcapath', ''),
				'key'                => $configuration->get('akeeba.platform.dbsslkey', ''),
				'cert'               => $configuration->get('akeeba.platform.dbsslcert', ''),
				'verify_server_cert' => $configuration->get('akeeba.platform.dbsslverifyservercert', 0) == 1,
			],
		];

		$dbdriver = $configuration->get('akeeba.platform.dbdriver', 'mysqli');

		if (($dbdriver == 'mysql') && !function_exists('mysql_connect'))
		{
			$dbdriver = 'mysqli';
		}

		$driver = '\\Akeeba\\Engine\\Driver\\' . ucfirst($dbdriver);

		// This is the format of the database inclusion filters
		$options['ssl'] = $options['ssl'] ?? [];
		$options['ssl'] = is_array($options['ssl']) ? $options['ssl'] : [];

		$entry = [
			'host'                  => ($options['host'] ?? null) ?: null,
			'port'                  => ($options['port'] ?? null) ?: null,
			'socket'                => ($options['socket'] ?? null) ?: null,
			'username'              => $options['user'] ?? null,
			'password'              => $options['password'] ?? null,
			'database'              => $options['database'] ?? null,
			'prefix'                => $options['prefix'] ?? '',
			'dumpFile'              => 'site.sql',
			'driver'                => $driver,
			'dbencryption'          => ($options['ssl']['enable'] ?? false) ? 1 : 0,
			'dbsslcipher'           => ($options['ssl']['cipher'] ?? '') ?: '',
			'dbsslca'               => ($options['ssl']['ca'] ?? '') ?: '',
			'dbsslcapath'           => ($options['ssl']['capath'] ?? '') ?: '',
			'dbsslkey'              => ($options['ssl']['key'] ?? '') ?: '',
			'dbsslcert'             => ($options['ssl']['cert'] ?? '') ?: '',
			'dbsslverifyservercert' => ($options['ssl']['verify_server_cert'] ?? false) ? 1 : 0,
		];

		// We take advantage of the filter class magic to inject our custom filters
		$this->filter_data['[SITEDB]'] = $entry;

		parent::__construct();
	}
}
