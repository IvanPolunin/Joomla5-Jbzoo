<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use YOOtheme\Application;

class plgSystemZoopro extends CMSPlugin
{
	public function onAfterInitialise()
	{
		if (!$zoo = $this->getZoo()) {
			return;
		}

		// check if YOOtheme Pro is loaded
        if (!class_exists(Application::class, false)) {
            return;
        }

		// register alias
		class_alias(App::class, YOOtheme\Zoo::class);

		// register plugin
		JLoader::registerNamespace('YOOtheme\\Builder\\Joomla\\Zoo\\', __DIR__ . '/src', false, false, 'psr4');

		// register applications
		$zoo->path->register(__DIR__ . '/applications', 'applications');

        // load a single module from the same directory
		$app = Application::getInstance();
		$app->set(YOOtheme\Zoo::class, $zoo);
        $app->load(__DIR__ . '/bootstrap.php');
	}

	protected function getZoo()
	{
		$config = JPATH_ADMINISTRATOR . '/components/com_zoo/config.php';
		$component = ComponentHelper::getComponent('com_zoo', true);

		if (!$component->enabled || !is_file($config)) {
			return;
		}

		require_once($config);

		if (!class_exists(App::class) or !$app = App::getInstance('zoo')) {
			return;
		}

		return $app;
	}
}
