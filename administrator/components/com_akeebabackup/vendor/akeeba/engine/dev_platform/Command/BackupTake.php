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

use Akeeba\Engine\Base\Part;
use Akeeba\Engine\DevPlatform\Translate\Text;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform;
use Akeeba\Engine\Psr\Log\LogLevel;
use Silly\Application;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class BackupTake
{
	private float $startTime;

	private float $lastStep;

	public static function register(Application $app)
	{
		$app
			->command('backup:take [description] [--profile=]', new self())
			->defaults(
				[
					'description' => null,
					'profile'     => 1,
				]
			)
			->descriptions(
				'Take a backup',
				[
					'--profile' => 'Profile ID',
				]
			);
	}

	public function __invoke(
		?string $description, int $profile, InputInterface $input, OutputInterface $output, SymfonyStyle $io
	)
	{
		$outputStyle = new OutputFormatterStyle('red', null, ['bold']);
		$output->getFormatter()->setStyle('borked', $outputStyle);

		define('AKEEBA_PROFILE', $profile);
		$platform = Platform::getInstance();
		$platform->load_configuration($profile);

		$description ??= Text::_('COM_AKEEBA_BACKUP_DEFAULT_DESCRIPTION') . ' ' . (new \DateTime())->format(
				Text::_('DATE_FORMAT_LC2')
			);
		$backupId    = $this->getBackupId();
		$tag         = Platform::getInstance()->get_backup_origin();

		Factory::resetState(['maxrun' => 0]);

		$tempVarsTag = $tag . (empty($backupId) ? '' : ('.' . $backupId));

		Factory::getFactoryStorage()->reset($tempVarsTag);
		Factory::nuke();
		Factory::getLog()->log(LogLevel::DEBUG, " -- Resetting Akeeba Engine factory ($tag.$backupId)");
		Platform::getInstance()->load_configuration();

		$kettenrad = Factory::getKettenrad();
		$kettenrad->setBackupId($backupId);
		$kettenrad->setup(
			[
				'description' => $description,
			]
		);

		$firstTime = true;

		$this->startTime = microtime(true);
		$this->lastStep  = microtime(true);

		do
		{
			if (!$firstTime)
			{
				Factory::loadState($tag, $backupId, true);
			}
			else
			{
				$io->section('Starting backup');

				$firstTime = false;

				/**
				 * We need to run tick() twice in the first backup step.
				 *
				 * The first tick() will reset the backup engine and start a new backup. However, no backup record is created
				 * at this point. This means that Factory::loadState() cannot find a backup record, therefore it cannot read
				 * the backup profile being used, therefore it will assume it's profile #1.
				 *
				 * The second tick() creates the backup record without doing much else, fixing this issue.
				 *
				 * However, if you have conservative settings where the min exec time is MORE than the max exec time the second
				 * tick would never run. Therefore we need to tell the first tick to ignore the time settings (since it only
				 * takes a few milliseconds to execute anyway) and then apply the time settings on the second tick (which also
				 * only takes a few milliseconds). This is why we have setIgnoreMinimumExecutionTime before and after the first
				 * tick. DO NOT REMOVE THESE.
				 *
				 * Furthermore, if the first tick reaches the end of backup or an error condition we MUST NOT run the second
				 * tick() since the engine state will be invalid. Hence the check for the state that performs a hard break. This
				 * could happen if you have a sufficiently high max execution time, no break between steps and we fail to
				 * execute any step, e.g. the installer image is missing, a database error occurred or we can not list the files
				 * and directories to back up.
				 *
				 * THEREFORE, DO NOT REMOVE THE LOOP OR THE if-BLOCK IN IT, THEY ARE THERE FOR A GOOD REASON!
				 */
				$kettenrad->setIgnoreMinimumExecutionTime(true);

				for ($i = 0; $i < 2; $i++)
				{
					$kettenrad->tick();

					if (in_array($kettenrad->getState(), [Part::STATE_FINISHED, Part::STATE_ERROR]))
					{
						break;
					}

					$kettenrad->setIgnoreMinimumExecutionTime(false);
				}

				$retArray = $kettenrad->getStatusArray();

				$retArray = $this->backupProgress($io, $retArray);

				// If we are already finished or errored out we need to break immediately.
				if ($retArray['HasRun'] || $retArray['Error'])
				{
					break;
				}
			}

			$io->section('Stepping backup');

			Factory::getTimer()->resetTime();

			$kettenrad->tick();

			$retArray = $kettenrad->getStatusArray();

			if (empty($retArray['Error']) && ($retArray['HasRun'] != 1))
			{
				Factory::saveState($tag, $backupId);
			}

			// TODO Fix me
			if ($retArray['Domain'] === 'finale' && $retArray['stepState'] === 'finished')
			{
				$weirdnessCounter ??= 0;
				$weirdnessCounter++;

				if ($weirdnessCounter > 3)
				{
					$io->warning(
						[
							'FIX ME: THIS SHOULD NOT BE HAPPENING!',
							'',
							'We are in the finished state of the finalisation domain for more than three steps.',
							'This is a bug. Fix me.',
						]
					);

					$retArray['HasRun'] = 1;
				}
			}

			$retArray = $this->backupProgress($io, $retArray);

		} while (!$retArray['HasRun'] && empty($retArray['Error']));

		Factory::nuke();
		Factory::getFactoryStorage()->reset($tempVarsTag);

		if (!$retArray['Error'] && $retArray['HasRun'] == 1)
		{
			$io->success('The backup finished successfully.');
		}
	}

	private function getBackupId(): string
	{
		$microtime    = explode(' ', microtime(false));
		$microseconds = (int) ($microtime[0] * 1000000);

		return 'id-' . gmdate('Ymd-His') . '-' . $microseconds;
	}

	private function recursiveDebugReport(\Throwable $e, SymfonyStyle $io)
	{
		$io->writeln(str_repeat('-', 78));
		$io->writeln('<borked>' . $e->getMessage() . '</borked>');
		$io->writeln('<comment>' . $e->getFile() . ':' . $e->getLine() . '</comment>');
		$io->writeln('');
		$io->writeln($e->getTraceAsString());
		$io->writeln('');

		if ($e->getPrevious() instanceof \Throwable)
		{
			$this->recursiveDebugReport($e->getPrevious(), $io);
		}
	}

	private function backupProgress(SymfonyStyle $io, ?array $retArray): ?array
	{
		$stateText = !$retArray['HasRun'] ? '<fg=yellow>Running</> ▶️' : '<fg=green>Done</> ✅';
		$stateText = !empty($retArray['Error']) ? '<fg=red>Error</> 💀' : $stateText;

		$now          = microtime(true);
		$totalElapsed = $now - $this->startTime;
		$lastStep     = $now - $this->lastStep;

		$this->lastStep = $now;

		$io->text(
			[
				"<options=bold>Elapsed</> : " . $this->formatTime($totalElapsed) . ' <fg=gray>(' . $this->formatTime($lastStep) . ')</>',
				"<options=bold>State</>   : " . $stateText,
				"<options=bold>Domain</>  : " . $retArray['Domain'],
				"<options=bold>Step</>    : " . $retArray['Step'],
				"<options=bold>Substep</> : " . $retArray['Substep'],
			]
		);

		if ($retArray['Warnings'])
		{
			$io->warning($retArray['Warnings']);
		}

		if ($retArray['Error'])
		{
			$io->error($retArray['Error']);
		}

		if (isset($retArray['ErrorException']) && $retArray['ErrorException'] instanceof \Throwable)
		{
			$this->recursiveDebugReport($retArray['ErrorException'], $io);
		}

		return $retArray;
	}

	private function formatTime(float $elapsed)
	{
		$msec    = floor(1000 * ($elapsed - floor($elapsed)));
		$sec     = (int)floor($elapsed);

		$hours   = (int) floor($sec / 3600);
    	$minutes = (int) floor(($sec % 3600) / 60);
    	$seconds = (int) floor($sec % 60);

		if ($hours > 0)
		{
			return sprintf('%02d:%02d:%02d.%03d', $hours, $minutes, $seconds, $msec);
		}

		return sprintf('%02d:%02d.%03d', $minutes, $seconds, $msec);
	}


}