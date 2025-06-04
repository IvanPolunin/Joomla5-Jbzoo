<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Version;

/**
 * Helper to deal with Joomla versions and basic configuration
 *
 * @package Framework.Helpers
 */
class JoomlaHelper extends AppHelper {

	/**
	 * The current joomla version
	 *
	 * @var Version
	 * @since 1.0.0
	 */
	public $version;

	/**
	 * Class Constructor
	 *
	 * @param App $app A reference to the global app object
	 */
	public function __construct($app) {
		parent::__construct($app);

		JLoader::import('joomla.version');

		$this->version = new Version();
	}

	/**
	 * Get the current Joomla installation short version (i.e: 2.5.3)
	 *
	 * @return string The short version of joomla (ie: 2.5.3)
	 *
	 * @since 1.0.0
	 */
	public function getVersion() {
		return $this->version->getShortVersion();
	}

    /**
     * Get the current Joomla installation release version (i.e: 2.5)
     *
     * @return string The release version of joomla (ie: 2.5)
     *
     * @since 4.1.0
     */
    public function getReleaseVersion() {
        return Version::MAJOR_VERSION . '.' . Version::MINOR_VERSION;
    }

	/**
	 * Check the current version of Joomla
	 *
	 * @param string $version The version to check
	 * @param boolean $release Compare only release versions (2.5 vs 2.5 even if 2.5.6 != 2.5.3)
	 *
	 * @return boolean If the version of Joomla is equal of the one passed
	 *
	 * @since 1.0.0
	 */
	public function isVersion($version, $release = true) {
		return $release ? $this->getReleaseVersion() == $version : $this->getVersion() == $version;
	}

	/**
	 * Get the default access group
	 *
	 * @return int The default group id
	 *
	 * @since 1.0.0
	 */
	public function getDefaultAccess() {
		return $this->app->system->config->get('access');
	}

	/**
	 * Check if the version is joomla 1.5
	 *
	 * @deprecated 2.5 Use JoomlaHelper::isVersion() instead
	 *
	 * @return boolean If is joomla 1.5
	 *
	 * @since 1.0.0
	 */
	public function isJoomla15() {
		return $this->isVersion('1.5');
	}

}
