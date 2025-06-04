<?php
namespace JExtstore\Component\JMap\Administrator\Dispatcher;
/**
 * Backend entrypoint dispatcher of the component application
 *
 * @package JMAP::administrator::components::com_jmap
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ();

use Joomla\CMS\Dispatcher\ComponentDispatcher;
use Joomla\CMS\Application\CMSApplicationInterface;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Joomla\CMS\Router\Route;
use Joomla\CMS\MVC\Factory\MVCFactoryInterface;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\Input\Input;
use Joomla\Registry\Registry;
use JExtstore\Component\JMap\Administrator\Framework\Loader;

/**
 * Dispatcher class for the component backend
 */
class Dispatcher extends ComponentDispatcher {
	/**
	 * The extension namespace
	 *
	 * @var string
	 */
	protected $namespace = 'JExtstore\\Component\\JMap';
	
	/**
	 * Constructor for Dispatcher
	 *
	 * @param   CMSApplicationInterface     $app                The application instance
	 * @param   Input                       $input              The input instance
	 * @param   MVCFactoryInterface  $mvcFactory  The MVC factory instance
	 *
	 * @since   4.0.0
	 */
	public function __construct(CMSApplicationInterface $app, Input $input, MVCFactoryInterface $mvcFactory) {
		// Set MySql 5.7.8+ strict mode off
		Factory::getContainer()->get('DatabaseDriver')->setQuery("SET @@SESSION.sql_mode = ''")->execute();
		
		if(!ComponentHelper::getParams('com_jmap')->get('enable_debug', 0)) {
			ini_set('display_errors', 0);
			ini_set('error_reporting', E_ERROR);
		}
		
		// Kill CSP headers
		$httpHeadersPlugin = PluginHelper::getPlugin('system', 'httpheaders');
		if(is_object($httpHeadersPlugin)) {
			$httpHeadersPluginParams = new Registry($httpHeadersPlugin->params);
			if($httpHeadersPluginParams->get('contentsecuritypolicy', 0)) {
				$app->setHeader('content-security-policy', null, true);
				$app->setHeader('content-security-policy-report-only', null, true);
			}
		}
		
		// Auto loader setup
		// Register autoloader prefix
		require_once  JPATH_COMPONENT . '/Framework/Loader.php';
		Loader::setup();
		Loader::registerNamespacePsr4($this->namespace . '\Site', JPATH_COMPONENT );
		Loader::registerNamespacePsr4($this->namespace . '\Administrator', JPATH_COMPONENT_ADMINISTRATOR );

		// Class aliasing
		if(!class_exists('JMapRoute')) {
			class_alias('\\JExtstore\\Component\\JMap\\Administrator\\Framework\\Helpers\\Route', 'JMapRoute');
		}
		
		// Manage partial language translations
		$jLang = $app->getLanguage();
		$jLang->load('com_jmap', JPATH_COMPONENT_ADMINISTRATOR, 'en-GB', true, true);
		if($jLang->getTag() != 'en-GB') {
			$jLang->load('com_jmap', JPATH_ADMINISTRATOR, null, true, false);
			$jLang->load('com_jmap', JPATH_COMPONENT_ADMINISTRATOR, null, true, false);
		}
		
		// Access check.
		if (!$app->getIdentity()->authorise('core.manage', 'com_jmap')) {
			throw new \Joomla\CMS\Access\Exception\NotAllowed(Text::_('COM_JMAP_ERROR_ALERT_NOACCESS_THIS_COMPONENT'), 403);
		}
		
		/**
		 * All SMVC logic is based on controller.task correcting the wrong Joomla concept
		 * of base execute on view names.
		 * When task is not specified because Joomla force view query string such as menu
		 * the view value is equals to controller and viewname = controller.display
		 */
		$controller_command = $app->getInput()->get('task', '');
		if (strpos($controller_command, '.')) {
			list($controller_name, $controller_task) = explode('.', $controller_command);
		} elseif ($controller_command) {
			$controller_name = $controller_command;
			$app->getInput()->set('controller', $controller_name);
			$app->getInput()->set('task', 'display');
		} else {
			// Defaults
			$app->getInput()->set('controller', 'cpanel');
			$app->getInput()->set('task', 'display');
		}
		
		if(isset($controller_name)) {
			$path = JPATH_COMPONENT . DIRECTORY_SEPARATOR . 'Controller' . DIRECTORY_SEPARATOR . ucfirst($controller_name) . 'Controller.php';
			if (!file_exists($path)) {
				$app->enqueueMessage(Text::_('COM_JMAP_ERROR_NO_CONTROLLER_FILE'), 'error');
				$app->redirect(Route::_('index.php?option=com_jmap'));
			}
		}
		
		parent::__construct ( $app, $input, $mvcFactory );
	}
}
