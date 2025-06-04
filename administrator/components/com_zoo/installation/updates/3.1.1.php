<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

class Update311 implements iUpdate {

    /**
     * @inheritDoc
     */
	public function getNotifications($app) {}

    /**
     * @inheritDoc
     */
	public function run($app) {
		// refresh database indexes
		$app->update->refreshDBTableIndexes();
	}

}
