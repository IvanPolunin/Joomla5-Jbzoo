<?php
/**
 * Service provider of the component application
 *
 * @package JMAP::administrator::components::com_jmap
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Dispatcher\ComponentDispatcherFactoryInterface;
use Joomla\CMS\Extension\ComponentInterface;
use Joomla\CMS\Component\Router\RouterFactoryInterface;
use JExtstore\Component\JMap\Administrator\Extension\JMapComponent;
use Joomla\CMS\Extension\Service\Provider\ComponentDispatcherFactory;
use Joomla\CMS\Extension\Service\Provider\MVCFactory;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Extension\Service\Provider\RouterFactory;
use Joomla\DI\Container;
use Joomla\DI\ServiceProviderInterface;

if(!class_exists('JExtstore\Component\JMap\Administrator\Extension\JMapComponent') && function_exists('opcache_reset')){
	opcache_reset();
	$app = Factory::getApplication();
	if($app->getInput()->get('task') == 'cpanel.installerApp') {
		$app->redirect('index.php?option=com_jmap&task=cpanel.installerApp&tmpl=component');
	} else {
		$app->redirect('index.php?option=com_jmap');
	}
}

/**
 * The extension service provider.
 */
return new class implements ServiceProviderInterface {
	/**
	 * Registers the service provider with a DI container.
	 *
	 * @param Container $container
	 *        	The DI container.
	 *        	
	 * @return void
	 */
	public function register(Container $container) {
		$container->registerServiceProvider ( new MVCFactory ( '\\JExtstore\\Component\\JMap' ) );
		$container->registerServiceProvider ( new ComponentDispatcherFactory ( '\\JExtstore\\Component\\JMap' ) );
		$container->registerServiceProvider ( new RouterFactory('\\JExtstore\\Component\\JMap'));
		
		$container->set ( ComponentInterface::class, static function (Container $container) {
			$component = new JMapComponent ($container->get(ComponentDispatcherFactoryInterface::class));
			$component->setMVCFactory($container->get(MVCFactoryInterface::class));
			$component->setRouterFactory($container->get(RouterFactoryInterface::class));
			return $component;
		} );
	}
};
