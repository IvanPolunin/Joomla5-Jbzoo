<?php
namespace JExtstore\Component\JMap\Administrator\Extension;
/**
 * Component class for com_jmap
 *
 * @package JMAP::components::com_jmap
 * @subpackage Extension
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ();

use Joomla\CMS\Factory;
use Joomla\CMS\Extension\BootableExtensionInterface;
use Joomla\CMS\Component\Router\RouterServiceInterface;
use Joomla\CMS\Component\Router\RouterServiceTrait;
use Joomla\CMS\Extension\MVCComponent;
use Psr\Container\ContainerInterface;

/**
 * Component class for com_jmap
 * @package JMAP::components::com_jmap
 * @subpackage Extension
 * @since 2.0
 */
class JMapComponent extends MVCComponent implements BootableExtensionInterface, RouterServiceInterface {
	use RouterServiceTrait;
	
	/**
	 * Booting the extension. This is the function to set up the environment of the extension like
	 * registering new class loaders, etc.
	 *
	 * If required, some initial set up can be done from services of the container, eg.
	 * registering HTML services.
	 *
	 * @param   ContainerInterface  $container  The container
	 *
	 * @return  void
	 */
	public function boot(ContainerInterface $container) {
		$option = Factory::getApplication()->getInput()->get('option') ?? 'com_jmap';
		
		if($option != 'com_jmap') {
			return;
		}
		
		// Define component path.
		if (!defined('JPATH_COMPONENT')) {
			define('JPATH_COMPONENT', JPATH_BASE . '/components/' . $option);
		}
		
		if (!defined('JPATH_COMPONENT_SITE')) {
			define('JPATH_COMPONENT_SITE', JPATH_SITE . '/components/' . $option);
		}
		
		if (!defined('JPATH_COMPONENT_ADMINISTRATOR')) {
			define('JPATH_COMPONENT_ADMINISTRATOR', JPATH_ADMINISTRATOR . '/components/' . $option);
		}
	}
}
