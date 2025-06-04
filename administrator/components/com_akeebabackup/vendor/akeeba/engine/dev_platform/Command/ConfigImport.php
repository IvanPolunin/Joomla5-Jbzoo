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

class ConfigImport
{
	public static function register(Application $app)
	{
		$app
			->command('config:import [file] [--profile=]', new self())
			->defaults(
				[
					'file' => getcwd() . '/config.json',
					'profile' => 1,
				]
			)
			->descriptions(
				'Import profile configuration parameters from a JSON file',
				[
					'--profile' => 'Profile ID',
				]
			);
	}

	public function __invoke(
		string $file, int $profile, InputInterface $input, OutputInterface $output, SymfonyStyle $io
	)
	{
		define('AKEEBA_PROFILE', $profile);
		$platform = Platform::getInstance();
		$platform->load_configuration($profile);

		$config = Factory::getConfiguration();

		$config->resetProtectedKeys();

		$json = file_get_contents($file);
		$data = json_decode($json, true);

		foreach ($data as $k => $v)
		{
			$config->set($k, $v);
		}

		$platform->save_configuration($profile);
	}
}