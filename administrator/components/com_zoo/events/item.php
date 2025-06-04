<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use \Joomla\CMS\Plugin\PluginHelper;

/**
 * Deals with item events.
 *
 * @package Component.Events
 */
class ItemEvent {

	/**
	 * Placeholder for the init event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function init($event) {

		$item = $event->getSubject();

	}

	/**
	 * Placeholder for the save event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function save($event) {

		// get Item object
		$item = $event->getSubject();

		// is the item new (we have the id anyway)
		$new = $event['new'];

		// Change something
		$item->name = 'newname';
	}

	/**
	 * Placeholder for the saved event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function saved($event) {

		$item = $event->getSubject();
		$new = $event['new'];

		// Trigger the onFinderAfterSave event.
        PluginHelper::importPlugin('finder');
		$item->app->system->application->triggerEvent('onFinderAfterSave', array($item->app->component->self->name.'.item', &$item, $new));

        // clear item route cache on save
		$item->app->route->clearCache();

	}

	/**
	 * Placeholder for the deleted event
	 *
	 * @param  AppEvent $event The event triggered
	 */

	public static function deleted($event) {

		$item = $event->getSubject();

		// Trigger the onFinderAfterSave event.
        PluginHelper::importPlugin('finder');
		$item->app->system->application->triggerEvent('onFinderAfterDelete', array(
            $item->app->component->self->name.'.item',
            &$item)
        );

		$item->app->route->clearCache();
	}

	/**
	 * Placeholder for the stateChanged event
	 *
	 * @param  AppEvent $event The event triggered
	 */

	public static function stateChanged($event) {

		$item = $event->getSubject();
		$old_state = $event['old_state'];

		PluginHelper::importPlugin('content');
		$item->app->system->application->triggerEvent('onContentChangeState', array(
            $item->app->component->self->name.'.item',
            array($item->id),
            $item->state)
        );

		$item->app->route->clearCache();
	}

	/**
	 * Placeholder for the beforeDisplay event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function beforeDisplay($event) {

		$item = $event->getSubject();

	}

	/**
	 * Placeholder for the afterDisplay event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function afterDisplay($event) {

		$item = $event->getSubject();
		$html = $event['html'];

	}

	/**
	 * Placeholder for the beforeSaveCategoryRelations event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function beforeSaveCategoryRelations($event) {

		// The item
		$item 		= $event->getSubject();
		// The list of category ids
		$categories = $event['categories'];

	}

}
