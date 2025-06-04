<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

class Update320 implements iUpdate {

    /**
     * @inheritDoc
     */
    public function getNotifications($app) {}

    /**
     * @inheritDoc
     */
    public function run($app) {
        // add asset_id to application table
        $fields = $app->database->getTableColumns(ZOO_TABLE_APPLICATION);
        if (!array_key_exists('asset_id', $fields)) {
            $app->database->query('ALTER TABLE '.ZOO_TABLE_APPLICATION.' ADD `asset_id` int(10) UNSIGNED NOT NULL DEFAULT \'0\' COMMENT \'FK to the #__assets table.\' AFTER `id`');
        }
        // fix rgt value in old ZOO demo installations
        $result = $app->database->queryResult('SELECT MAX(`rgt`) + 1 FROM `#__assets`');
        $app->database->query('UPDATE `#__assets` SET `rgt` = '.$result.' WHERE `id`=1');
    }
}
