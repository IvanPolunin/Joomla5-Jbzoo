<?php
namespace JExtstore\Component\JMap\Administrator\Field;
/**
 * @package JMAP::administrator::components::com_jmap
 * @subpackage Field
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Factory;
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Language\Text;

/**
 * Form Field class for the Joomla Platform.
 * Provides radio button inputs
 *
 * @package     Joomla.Platform
 * @subpackage  Form
 * @link        http://www.w3.org/TR/html-markup/command.radio.html#command.radio
 * @since       11.1
 */
class GoogleauthField extends FormField
{
	/**
	 * The form field type.
	 *
	 * @var    string
	 * @since  11.1
	 */
	protected $type = 'googleauth';

	/**
	 * Method to get the radio button field input markup.
	 *
	 * @return  string  The field input markup.
	 *
	 * @since   11.1
	 */
	protected function getInput() {
		$app = Factory::getApplication();
		$extensionMVCFactory = $app->bootComponent('com_jmap')->getMVCFactory();
		$googleModel = $extensionMVCFactory->createModel('Google', 'Administrator');
		
		if(!$googleModel->getComponentParams()->get('enable_google_indexing_api', 0)) {
			return '<span data-bs-content="' . Text::_('COM_JMAP_START_GOOGLE_INDEXING_DISABLED_DESC') . '" class="badge bg-danger hasPopover"><span class="icon-warning"></span> ' .
											Text::_('COM_JMAP_START_GOOGLE_INDEXING_DISABLED') . '</span>';
		}
		
		// Composer autoloader
		require_once JPATH_COMPONENT_ADMINISTRATOR. '/Framework/composer/autoload_real.php';
		\ComposerAutoloaderInitcb4c0ac1dedbbba2f0b42e9cdf4d93d7::getLoader();
		
		$authLink = $googleModel->indexingAPIAuthUpdate();
		if ($googleModel->getComponentParams()->get('google_indexing_authcode') && $googleModel->getComponentParams()->get('google_indexing_authtoken')) {
			return 	'<span id="google_authentication_reset" data-bs-content="' . Text::_('COM_JMAP_GOOGLE_AUTHENTICATION_LOGOUT_DESC') . '" class="badge bg-success hasPopover hasButton">' .
					'<span class="icon-lock"></span> ' . Text::_('COM_JMAP_GOOGLE_AUTHENTICATION_LOGOUT') . '</span>';
		} else {
			return 	'<a target="_blank" href="' . $authLink . '" data-bs-content="' . Text::_('COM_JMAP_START_GOOGLE_AUTHENTICATION_DESC') . '" class="badge bg-primary hasPopover hasButton">' .
					'<span class="icon-lock"></span> ' . Text::_('COM_JMAP_START_GOOGLE_AUTHENTICATION') . '</a>';
		}
	}
}
