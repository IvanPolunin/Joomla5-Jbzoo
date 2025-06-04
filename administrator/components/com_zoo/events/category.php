<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use \Joomla\CMS\Plugin\PluginHelper;

/**
 * Deals with category events.
 *
 * @package Component.Events
 */
class CategoryEvent {

	/**
	 * Placeholder for the init event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function init($event) {

		$category = $event->getSubject();

	}

    /**
     * Placeholder for the save event
     *
     * @param  AppEvent $event The event triggered
     */
    public static function save($event) {

        // get Item object
        $category = $event->getSubject();

        // is the item new (we have the id anyway)
        $new = $event['new'];

        // Change something
        $category->name = 'newname';
    }

	/**
	 * Trigger joomla content plugins on the category contents and clears the route cache
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function saved($event) {

		$category = $event->getSubject();
		$new = $event['new'];

		PluginHelper::importPlugin('content');
        $category->app->system->application->triggerevent('onContentAfterSave', array(
            $category->app->component->self->name.'.category',
            &$category,
            $new
        ));

		$category->app->route->clearCache();
	}

	/**
	 * Trigger joomla content plugins on the category contents and clears the route cache
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function deleted($event) {

		$category = $event->getSubject();

		PluginHelper::importPlugin('content');
        $category->app->system->application->triggerEvent('onContentAfterDelete', array(
            $category->app->component->self->name.'.category',
            &$category)
        );

		$category->app->route->clearCache();

	}

	/**
	 * Trigger joomla content plugins on the category contents and clears the route cache
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function stateChanged($event) {

		$category = $event->getSubject();
		$old_state = $event['old_state'];

		PluginHelper::importPlugin('content');
        $category->app->system->application->triggerEvent('onContentChangeState', array(
            $category->app->component->self->name.'.category',
            array($category->id),
            $category->published)
        );

		$category->app->route->clearCache();

	}

}
