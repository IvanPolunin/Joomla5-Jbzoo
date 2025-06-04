<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\String\StringHelper;
use NRFramework\HTML;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;
use Joomla\CMS\Language\Text;
use Joomla\Filesystem\File;
use Joomla\Registry\Registry;

// Initialize Tassos Library
require_once __DIR__ . '/autoload.php';

class plgSystemNRFramework extends CMSPlugin
{
	/**
	 *  Auto load plugin language 
	 *
	 *  @var  boolean
	 */
	protected $autoloadLanguage = true;
	
	/**
	 *  The Joomla Application object
	 *
	 *  @var  object
	 */
	protected $app;

    /**
     *  Update UpdateSites after the user has entered a Download Key
     *
     *  @param   string  $context  The component context
     *  @param   string  $table    
     *  @param   boolean $isNew    
     *
     *  @return  void
     */
	public function onExtensionAfterSave($context, $table, $isNew)
	{
		// Run only on Tassos Framework edit form
		if (
			$this->app->isClient('site')
			|| $context != 'com_plugins.plugin'
			|| $table->element != 'nrframework'
			|| !isset($table->params)
		)
		{
			return;
		}

		// Set Download Key & fix Update Sites
		$upds = new NRFramework\Updatesites();
		$upds->update();
	}

	/**
	 *  Handling of PRO for extensions
	 *  Throws a notice message if the Download Key is missing before downloading the package
	 *
	 *  @param   string  &$url      Update Site URL
	 *  @param   array   &$headers  
	 */
	public function onInstallerBeforePackageDownload(&$url, &$headers)
	{
		$uri  = Uri::getInstance($url);
		$host = $uri->getHost();

		// This is not a Tassos.gr extension
		if (strpos($host, 'tassos.gr') === false)
		{
			return true;
		}

		// If it's a Free version. No need to check for the Download Key. 
		if (strpos($url, 'free') !== false)
		{
			return true;
		}

		// This is a Pro version. Let's validate the Download Key.
		$download_id = $this->params->get('key', '');
		
		// Append it to the URL
		if (!empty($download_id))
		{
			$uri->setVar('dlid', $download_id);
			$url = $uri->toString();
			return true;
		} 
	
		$this->app->enqueueMessage('To be able to update the Pro version of this extension via the Joomla updater, you will need enter your Download Key in the settings of the <a href="' . Uri::base() . 'index.php?option=com_plugins&view=plugins&filter_search=tassos">Tassos Framework System Plugin</a>');
		return true;
	}

    /**
     *  Listens to AJAX requests on ?option=com_ajax&format=raw&plugin=nrframework
     *
     *  @return void
     */
    public function onAjaxNrframework()
    {
		Session::checkToken('request') or jexit(Text::_('JINVALID_TOKEN'));

        // Check if we have a valid task
		$task = $this->app->input->get('task', null);

		$non_admin_tasks = [
			'include'
		];
		
		// Only in backend
        if (!in_array(strtolower($task), $non_admin_tasks) && !$this->app->isClient('administrator'))
        {
            return;
        }

		// Check if we have a valid method task
		$taskMethod = 'ajaxTask' . $task;

		if (!method_exists($this, $taskMethod))
		{
			die('Task not found');
		}

		$this->$taskMethod();
	}

	/**
	 * Handles the Widgets AJAX requests.
	 * 
	 * @return  void
	 */
	public function onAjaxWidgets()
	{
		Session::checkToken('request') or jexit(Text::_('JINVALID_TOKEN'));

		$widget = $this->app->input->get('widget', null);

		$class = '\NRFramework\Widgets\\' . $widget;

		if (!class_exists($class))
		{
			return;
		}

		$task = $this->app->input->get('task');

		(new $class)->onAjax($task);
	}
	
	private function ajaxTaskInclude()
	{
		$input = $this->app->input;

		$file  = $input->get('file');
		$path  = JPATH_SITE . '/' . $input->get('path', '', 'RAW');
		$class = $input->get('class');

		$file_to_include = $path . $file . '.php';

		if (!file_exists($file_to_include))
		{
			die('FILE_ERROR');
		}

		@include_once $file_to_include;

		if (!class_exists($class))
		{
			die('CLASS_ERROR');
		}

		if (!method_exists($class, 'onAJAX'))
		{
			die('METHOD_ERROR');
		}

		(new $class())->onAJAX($input->getArray());
	}

	/**
	 * Notices AJAX requests.
	 * 
	 * @return  void
	 */
	private function ajaxTaskNotices()
	{
		if (!Session::checkToken('request'))
		{
			echo json_encode([
				'error' => true,
				'message' => Text::_('JINVALID_TOKEN')
			]);
			die();
		}
		
		$input = $this->app->input;

		$action = $input->get('action', null);

		$allowed_actions = [
			'downloadkey',
			'ajaxnotices'
		];

		if (!in_array($action, $allowed_actions))
		{
			echo json_encode([
				'error' => true,
				'response' => 'Invalid action.'
			]);
			die();
		}

		$error = false;
		$response = '';

		switch ($action)
		{
			case 'downloadkey':
				// Get Download Key
				if (!$download_key = $input->get('download_key', null, 'string'))
				{
					echo json_encode([
						'error' => true,
						'response' => 'Missing download key.'
					]);
					die();
				}
				
				// Try and update the Download Key
				if (!\NRFramework\Functions::updateDownloadKey($download_key))
				{
					echo json_encode([
						'error' => true,
						'response' => 'Cannot update download key.'
					]);
					die();
				}

				$response = Text::_('NR_DOWNLOAD_KEY_UPDATED');
				break;
			case 'ajaxnotices':
				// Get element
				if (!$ext_element = $input->get('ext_element', null, 'string'))
				{
					echo json_encode([
						'error' => true,
						'response' => 'Missing extension element.'
					]);
					die();
				}

				// Get xml
				if (!$ext_xml = $input->get('ext_xml', null, 'string'))
				{
					echo json_encode([
						'error' => true,
						'response' => 'Missing extension xml.'
					]);
					die();
				}

				// Get type
				if (!$ext_type = $input->get('ext_type', null, 'string'))
				{
					echo json_encode([
						'error' => true,
						'response' => 'Missing extension type.'
					]);
					die();
				}

				// Current URL
				if (!$current_url = $input->get('current_url', null, 'string'))
				{
					echo json_encode([
						'error' => true,
						'response' => 'Missing current URL.'
					]);
					die();
				}
				
				// Get excluded notices
				$exclude = $input->get('exclude', null, 'string');
				$exclude = array_filter(explode(',', $exclude));

				$notices = \NRFramework\Notices\Notices::getInstance([
					'ext_element' => $ext_element,
					'ext_xml' => $ext_xml,
					'ext_type' => $ext_type,
					'exclude' => $exclude,
					'current_url' => $current_url
				])->getNotices();

				echo json_encode([
					'error' => false,
					'notices' => $notices
				]);
				die();
				break;
		}

		echo json_encode([
			'error' => $error,
			'response' => $response
		]);
	}

	/**
	 * Templates Library AJAX requests.
	 * 
	 * @return  void
	 */
	private function ajaxTaskTemplatesLibrary()
	{
		if (!Session::checkToken('request'))
		{
			echo json_encode([
				'error' => true,
				'message' => Text::_('JINVALID_TOKEN')
			]);
			die();
		}
		
		$action = $this->app->input->get('action', null);

		$input = new Registry(json_decode(file_get_contents('php://input')));

		$template_id = $input->get('template_id', '');

		$allowed_actions = [
			'get_templates',
			'refresh_templates',
			'insert_template',
			'favorites_toggle'
		];

		if (!in_array($action, $allowed_actions))
		{
			echo json_encode([
				'error' => true,
				'message' => 'Cannot validate request.'
			]);
			die();
		}

		if (!$options = json_decode($input->get('options', []), true))
		{
			echo json_encode([
				'error' => true,
				'message' => 'Cannot validate request.'
			]);
			die();
		}

		$class = '';
		$method = 'tf_library_ajax_' . $action;
		
		switch ($action) {
			case 'get_templates':
			case 'refresh_templates':
			case 'insert_template':
				$class = 'templates';
				
				if ($action === 'insert_template')
				{
					// Ensure a template ID is given
					if (empty($template_id))
					{
						echo json_encode([
							'error' => true,
							'message' => 'Cannot process request.'
						]);
						die();
					}
	
					$options['template_id'] = $template_id;
				}
					
				break;
			case 'favorites_toggle':
				$class = 'favorites';

				// Ensure a template ID is given
				if (empty($template_id))
				{
					echo json_encode([
						'error' => true,
						'message' => 'Cannot process request.'
					]);
					die();
				}

				$options['template_id'] = $template_id;
				
				break;
		}
		
		$library = new \NRFramework\Library\Library($options);

		echo json_encode($library->$class->$method());
	}

	/**
	 * Conditional Builder AJAX requests.
	 * 
	 * @return  void
	 */
	private function ajaxTaskConditionBuilder()
	{
		if (!Session::checkToken('request'))
		{
			echo json_encode([
				'error' => true,
				'message' => Text::_('JINVALID_TOKEN')
			]);
			die();
		}
		
		$input = new Registry(json_decode(file_get_contents('php://input')));

		switch ($input->get('subtask', null))
		{
			// Adding a condition item or group
			case 'add':
				$conditionItemGroup = $input->get('conditionItemGroup', null);
				$groupKey = intval($input->get('groupKey'));
				$conditionKey = intval($input->get('conditionKey'));
				$include_rules = $input->get('include_rules', null);
				$exclude_rules = $input->get('exclude_rules', null);
				$exclude_rules_pro = $input->get('exclude_rules_pro', null) === '1';

				$conditionItem = NRFramework\Conditions\ConditionBuilder::add($conditionItemGroup, $groupKey, $conditionKey, null, $include_rules, $exclude_rules, $exclude_rules_pro);

				// Adding a single condition item
				if (!$input->get('addingNewGroup', false)) {
					echo $conditionItem;
					break;
				}

				$payload = [
					'name' => $conditionItemGroup,
					'groupKey' => $groupKey,
					'groupConditions' => ['enabled' => 1],
					'include_rules' => $include_rules,
					'exclude_rules' => $exclude_rules,
					'exclude_rules_pro' => $exclude_rules_pro,
					'condition_items_parsed' => [$conditionItem],
				];

				// Adding a condition group
				echo NRFramework\Conditions\ConditionBuilder::getLayout('conditionbuilder_group', $payload);
				break;
			case 'options':
				$conditionItemGroup = $input->get('conditionItemGroup', null);
				$name = $input->get('name', null);

				echo NRFramework\Conditions\ConditionBuilder::renderOptions($name, $conditionItemGroup);
				break;
			case 'init_load':
				$payload = [
					'data' => $input->get('data', []),
					'name' => $input->get('name', null),
					'include_rules' => $input->get('include_rules', null),
					'exclude_rules' => $input->get('exclude_rules', null),
					'exclude_rules_pro' => $input->get('exclude_rules_pro', null) === '1'
				];
				
				echo NRFramework\Conditions\ConditionBuilder::initLoad($payload);
				break;
		}
	}

	/**
	 * Remains for backwards compatibility.
	 * 
	 * @deprecated 4.9.50
	 */
	private function ajaxTaskUpdateNotification()
	{
		echo HTML::updateNotification($this->app->input->get('element'));
	}
}