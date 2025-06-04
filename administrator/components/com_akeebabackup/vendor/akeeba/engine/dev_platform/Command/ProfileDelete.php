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

class ProfileDelete
{
	public static function register(Application $app)
	{
		$app
			->command('profile:delete id', new self())
			->descriptions(
				'Delete a backup profile',
				[
					'id'          => 'Profile ID',
				]
			);
	}

	public function __invoke(
		int $id, InputInterface $input, OutputInterface $output, SymfonyStyle $io
	)
	{
		$db = Factory::getDatabase();

		if ($id == 1)
		{
			$io->error('Cannot delete default backup profile.');

			return;
		}

		$query = $db->getQuery(true)
			->select(
				[
					$db->quoteName('id'),
					$db->quoteName('description'),
				]
			)
			->from($db->quoteName('#__ak_profiles'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$profile = $db->setQuery($query)->loadObject();

		if (!is_object($profile) || !isset($profile->id) || $profile->id != $id)
		{
			$io->error(sprintf("Profile ID %d does not exist", $id));

			return;
		}

		$query = $db->getQuery(true)
			->delete($db->quoteName('#__ak_profiles'))
			->where($db->quoteName('id') . ' = ' . $db->quote($id));

		$db->setQuery($query)->execute();

		$io->success("Profile #$id deleted.");
	}
}