<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use Joomla\CMS\Table\Table;

/**
 * Deals with application events.
 *
 * @package Component.Events
 * @since 3.2
 */
class AssetEvent {

    /**
     * Function for the saved event
     *
     * @param  AppEvent $event The event triggered
     */
    public static function saved($event) {
        $application    = $event->getSubject();
        $parentId       = $application->getAssetParentId();
        $name           = $application->getAssetName();
        $title          = $application->getAssetTitle();
        $asset          = Table::getInstance('Asset');

        $asset->loadByName($name);

        if (!$asset->id || $asset->parent_id != $parentId) {

            $asset->name = $name;
            $asset->title = $title;

            if (!$asset->id) {
                $asset->rules = '{}';
            }

            $asset->setLocation($parentId, 'last-child');

            // save asset with empty rules just to get the id,
            // the rule updates are made by ajax
            $asset->store();

        }

        if ($application->asset_id != $asset->id) {
            $application->updateAssetId($asset->id);
        }
    }

    /**
     * Function for the deleted event
     *
     * @param  AppEvent $event The event triggered
     */
    public static function deleted($event) {
        $application = $event->getSubject();
        $name        = $application->getAssetName();
        $asset       = Table::getInstance('Asset');

        if ($asset->loadByName($name))
        {
            $asset->delete();
        }
    }
}
