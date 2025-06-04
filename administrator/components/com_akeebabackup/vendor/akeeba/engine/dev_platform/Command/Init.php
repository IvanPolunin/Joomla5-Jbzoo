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

namespace Akeeba\Engine\DevPlatform\Command;

use Akeeba\Engine\Factory;
use Silly\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class Init
{
	public static function register(Application $app)
	{
		$app
			->command('init', new self())
			->descriptions('Initializes the SQLite database');
	}

	public function __invoke(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
	{
		$io->title('Initialization');

		$db    = Factory::getDatabase();
		$query = $db->getQuery(true)
			->select($db->quoteName('name'))
			->from($db->quoteName('sqlite_schema'))
			->where(
				[
					$db->quoteName('type') . ' = ' . $db->q('table'),
					$db->quoteName('name') . ' NOT LIKE ' . $db->q('sqlite_%'),
				]
			);

		$tables      = $db->setQuery($query)->loadColumn();
		$knownTables = $this->getKnownTables();

		$output->writeln('<info>Checking tables</info>');

		foreach ($knownTables as $table)
		{
			$message = sprintf(' %-20s', $table);

			if (in_array($table, $tables))
			{
				$output->writeln('<info>' . '✅' . $message . '</info>');

				continue;
			}

			$output->writeln('<info>' . '➕' . $message . '</info>');
			$this->installTable($table);
		}
	}

	private function getKnownTables(): array
	{
		$ret = [];
		$di  = new \DirectoryIterator(__DIR__ . '/../sql');

		/** @var \DirectoryIterator $file */
		foreach ($di as $file)
		{
			if ($file->isDot() || !$file->isFile() || $file->getExtension() !== 'sql')
			{
				continue;
			}

			$ret[] = $file->getBasename('.sql');
		}

		return $ret;
	}

	private function installTable($table)
	{
		$db = Factory::getDatabase();

		$file     = __DIR__ . '/../sql/' . $table . '.sql';
		$contents = file_get_contents($file);

		$queries = explode(";", $contents);
		$queries = array_map('trim', $queries);
		$queries = array_filter($queries);

		foreach ($queries as $query)
		{
			$db->setQuery($query)->execute();
		}
	}

}