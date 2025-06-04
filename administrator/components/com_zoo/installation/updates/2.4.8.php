<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

class Update248 implements iUpdate {

    /**
     * @inheritDoc
     */
	public function getNotifications($app) {}

    /**
     * @inheritDoc
     */
	public function run($app) {

		// change elements field to type LONGTEXT
		$fields = $app->database->getTableColumns(ZOO_TABLE_ITEM);
		if (isset($fields[ZOO_TABLE_ITEM]) && array_key_exists('elements', $fields[ZOO_TABLE_ITEM])) {
			if ($fields[ZOO_TABLE_ITEM]['elements'] != 'longtext') {
				$app->database->query('ALTER TABLE '.ZOO_TABLE_ITEM.' MODIFY elements LONGTEXT NOT NULL');
			}
		}
	}

}
