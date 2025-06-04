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
use Akeeba\Engine\Platform;
use Silly\Application;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class ProfileList
{
	public static function register(Application $app)
	{
		$app
			->command('profile:list', new self())
			->descriptions(
				'List backup profiles'
			);
	}

	public function __invoke(InputInterface $input, OutputInterface $output, SymfonyStyle $io)
	{
		$db = Factory::getDatabase();
		$query = $db->getQuery(true)
			->select([
				$db->quoteName('id'),
				$db->quoteName('description'),
			])
			->from($db->quoteName('#__ak_profiles'));

		$profiles = $db->setQuery($query)->loadObjectList() ?: [];

		$table = $io->createTable();
		$table->setHeaders([
			'ID', 'Description'
		]);

		foreach ($profiles as $info)
		{
			$table->addRow([
				$info->id,
				$info->description,
			]);
		}

		$table->render();
	}
}