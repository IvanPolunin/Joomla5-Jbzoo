<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Installer\Installer;

class Update2412 implements iUpdate {

    /**
     * @inheritDoc
     */
	public function getNotifications($app) {}

    /**
     * @inheritDoc
     */
	public function run($app) {

		// uninstall shortcut plugin
		// set query
		$query = 'SELECT extension_id as id FROM #__extensions WHERE element = '.$app->database->Quote('zooshortcut');

		// query extension id and client id
		if ($res = $app->database->queryObject($query)) {
			$installer = new Installer();
			$installer->uninstall('plugin', $res->id, 0);
		}
	}

}
