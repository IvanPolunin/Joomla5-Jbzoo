<?php
/**
 * @package   ZOO Item
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Filesystem\File;

class mod_zooitemInstallerScript {

	public function install($parent) {}

	public function uninstall($parent) {}

	public function update($parent) {}

	public function preflight($type, $parent) {

		if (strtolower($type) == 'update') {

			// load config
			require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

			// get app
			$zoo = App::getInstance('zoo');

			foreach ($zoo->filesystem->readDirectoryFiles($parent->getParent()->getPath('source'), $parent->getParent()->getPath('source').'/', '/(positions\.(config|xml)|metadata\.xml)$/', true) as $file) {
				File::delete($file);
			}

		}
	}

	public function postflight($type, $parent) {}
}
