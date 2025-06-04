#!/usr/bin/env php
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

use Akeeba\Engine\DevPlatform\Command;
use Akeeba\Engine\Platform;
use Composer\CaBundle\CaBundle;

require __DIR__ . '/../vendor/autoload.php';

define('AKEEBAENGINE', 1);
define('AKEEBADEBUG', 1);
define('AKEEBADEBUG_ERROR_DISPLAY', 1);
define('AKEEBA_CACERT_PEM', CaBundle::getBundledCaBundlePath());
define('AKEEBA_VERSION', 'dev');
define('AKEEBA_PRO', true);
define('AKEEBA_DATE', (new \DateTime())->format('Y-m-d'));

error_reporting(E_ALL | E_NOTICE | E_DEPRECATED);
ini_set('display_errors', 1);

try
{
	// Load the dev platform
	Platform::addPlatform('Development', __DIR__ . '/Platform');
	$platform = Platform::getInstance();
	$platform->load_version_defines();

	// Run the CLI app
	$app = new Silly\Application();

	Command\Init::register($app);
	//Command\NukeBackups::register($app);
	//Command\NukeProfiles::register($app);
	//Command\NukeEverything::register($app);
	Command\ConfigList::register($app);
	Command\ConfigSet::register($app);
	Command\ConfigExport::register($app);
	Command\ConfigImport::register($app);
	Command\BackupTake::register($app);
	//Command\BackupList::register($app);
	//Command\BackupInfo::register($app);
	//Command\BackupLogView::register($app);
	//Command\BackupDelete::register($app);
	//Command\BackupRemoteDownload::register($app);
	//Command\BackupRemoteUpload::register($app);
	//Command\BackupRemoteDelete::register($app);
	//Command\BackupFreeze::register($app);
	//Command\BackupUnfreeze::register($app);
	Command\ProfileList::register($app);
	Command\ProfileAdd::register($app);
	Command\ProfileRename::register($app);
	Command\ProfileDelete::register($app);
	//Command\FilterList::register($app);
	//Command\FilterAdd::register($app);
	//Command\FilterRemove::register($app);

	$app->run();
}
catch (Throwable $exception)
{
	echo <<< TEXT


==============================================================================
                                ERROR
==============================================================================

{$exception->getMessage()}

{$exception->getFile()}:{$exception->getLine()}

{$exception->getTraceAsString()}

TEXT;
}