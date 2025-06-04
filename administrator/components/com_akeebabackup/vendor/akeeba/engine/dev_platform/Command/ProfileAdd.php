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

class ProfileAdd
{
	public static function register(Application $app)
	{
		$app
			->command('profile:add [--description=]', new self())
			->defaults(
				[
					'description' => sprintf("Profile added on %s", (new \DateTime())->format('Y-m-d H:i:s T')),
				]
			)
			->descriptions(
				'Add a backup profile',
				[
					'--description' => 'Profile description',
				]
			);
	}

	public function __invoke(string $description, InputInterface $input, OutputInterface $output, SymfonyStyle $io)
	{
		$db    = Factory::getDatabase();

		$o = (object)[
			'id' => null,
			'description' => $description,
		];

		$db->insertObject('#__ak_profiles', $o, 'id');

		$io->success("Profile #{$o->id} added.");
	}
}