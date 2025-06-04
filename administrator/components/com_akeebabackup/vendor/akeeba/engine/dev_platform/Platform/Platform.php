<?php

namespace Akeeba\Engine\Platform;

use Akeeba\Engine\DevPlatform\Translate\Text;
use Akeeba\Engine\Driver\Base as DatabaseDriverBase;
use Akeeba\Engine\Driver\Sqlite;
use Akeeba\Engine\Factory;
use Akeeba\Engine\Platform\Base as BasePlatform;
use Akeeba\Engine\Psr\Log\LogLevel;
use Joomla\Uri\Uri;

class Development extends BasePlatform
{
	private static $profile_id = 1;

	private static ?DatabaseDriverBase $dbDriver = null;

	private static ?array $configOptions = null;

	private array $flashVariables = [];

	public function __construct()
	{
		$this->priority     = 1;
		$this->platformName = 'development';
		$this->proxyEnabled = false;
	}

	public function set_flash_variable($name, $value)
	{
		$this->flashVariables[$name] = $value;
	}

	public function get_flash_variable($name, $default = null)
	{
		if (!array_key_exists($name, $this->flashVariables))
		{
			return $default;
		}

		$value = $this->flashVariables[$name];

		unset($this->flashVariables[$name]);

		return $value;
	}

	public function redirect($url)
	{
		echo "\nURL Redirection is not implemented\n";
	}

	public function getPlatformVersion()
	{
		return [
			'name'    => 'Development',
			'version' => 'dev',
		];
	}

	public function get_active_profile()
	{
		if (defined('AKEEBA_PROFILE'))
		{
			return AKEEBA_PROFILE;
		}

		return 1;
	}

	public function get_backup_origin()
	{
		return 'cli';
	}

	public function get_default_database_driver($use_platform = true)
	{
		return Sqlite::class;
	}

	public function get_platform_database_options()
	{
		return [
			'version'  => 3,
			'database' => __DIR__ . '/../dev.sqlite',
			'user'     => 'dev',
			'password' => 'dev',
		];
	}

	public function translate($key)
	{
		return Text::_($key);
	}

	public function get_installer_images_path()
	{
		return __DIR__ . '/../../../angie/release/jpa';
	}

	public function get_platform_configuration_option($key, $default)
	{
		if (self::$configOptions === null)
		{
			self::$configOptions = [
				"frontend_email_on_finish" => false,
				"frontend_email_when"      => "always",
				"frontend_email_subject"   => "",
				"frontend_email_body"      => "",
				"frontend_email_address"   => "",
				"update_dlid"              => "",
				"push_preference"          => 0,
				"push_apikey"              => "",
				"useencryption"            => 0,
			];

			$configFile = __DIR__ . '/../config.json';

			if (file_exists($configFile))
			{
				self::$configOptions = array_merge(
					self::$configOptions,
					json_decode(file_get_contents($configFile), true)
				);
			}
		}

		return self::$configOptions[$key] ?? $default;
	}

	public function get_profile_name($id = null)
	{
		if (empty($id))
		{
			$id = $this->get_active_profile();
		}

		$id = (int) $id;

		$db  = Factory::getDatabase($this->get_platform_database_options());
		$sql = (method_exists($db, 'createQuery') ? $db->createQuery() : $db->getQuery(true))
			->select($db->qn('description'))
			->from($db->qn('#__akeebabackup_profiles'))
			->where($db->qn('id') . ' = ' . $db->q($id));
		$db->setQuery($sql);

		return $db->loadResult();
	}

	public function get_stock_directories()
	{
		return [
			'[SITEROOT]'       => $this->get_site_root(),
			'[ROOTPARENT]'     => @realpath($this->get_site_root() . '/..'),
			'[SITETMP]'        => sys_get_temp_dir(),
			'[DEFAULT_OUTPUT]' => __DIR__ . '/../backup',
			'[HOST]'           => $this->get_host(),
		];
	}

	public function get_timestamp_database($date = 'now')
	{
		return (new \DateTime($date))->format('Y-m-d H:i:s');
	}

	public function log_platform_special_directories()
	{
		$ret = [];

		Factory::getLog()->log(
			LogLevel::INFO, "Computed <root>    :" . $this->get_site_root(), ['translate_root' => false]
		);

		// Detect UNC paths and warn the user
		if (DIRECTORY_SEPARATOR == '\\')
		{
			if ((substr(JPATH_ROOT, 0, 2) == '\\\\') || (substr(JPATH_ROOT, 0, 2) == '//'))
			{
				if (!isset($ret['warnings']))
				{
					$ret['warnings'] = [];
				}

				$ret['warnings'] = array_merge(
					$ret['warnings'], [
					'Your site\'s root is using a UNC path (e.g. \\\\SERVER\\path\\to\\root). PHP has known bugs which may',
					'prevent it from working properly on a site like this. Please take a look at',
					'https://bugs.php.net/bug.php?id=40163 and https://bugs.php.net/bug.php?id=52376. As a result your',
					'backup may fail.',
				]
				);
			}
		}

		if (empty($ret))
		{
			$ret = null;
		}

		return $ret;
	}

	public function get_host()
	{
		static $deadLockTest = false;

		$siteUrl = null;

		if (!$deadLockTest)
		{
			$deadLockTest = true;
			$siteUrl      = Factory::getConfiguration()->get('akeeba.platform.site_url', '');
			$siteUrl      = trim($siteUrl);
			$deadLockTest = false;
		}

		if (empty($siteUrl))
		{
			return 'akeeba.invalid';
		}

		return (new Uri($siteUrl))->getHost();
	}

	public function get_site_name()
	{
		return 'Engine Development';
	}

	public function get_site_root()
	{
		return realpath(__DIR__ . '/..');
	}
}