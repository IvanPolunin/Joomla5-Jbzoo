<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: FrontpageController
		The controller class for frontpage
*/

use Joomla\CMS\Language\Text;

class FrontpageController extends AppController {

	public $application;

	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->application;

		// get application
		$this->application 	= $this->zoo->zoo->getApplication();

		// check ACL
		if (!$this->application->canManageFrontpage()) {
			throw new FrontpageControllerException("Invalid Access Permissions", 1);
		}

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

		// register tasks
		$this->registerTask('apply', 'save');

	}

	public function display($cachable = false, $urlparams = false) {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Frontpage'));
		$this->zoo->toolbar->apply();
		$this->zoo->zoo->toolbarHelp();

		// get params
		$this->params = $this->application->getParams();

		// display view
		$this->getView()->display();
	}

	public function save() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$post = $this->zoo->request->get('post:', 'array');
		$post['description'] = $this->zoo->request->getVar('description', '', 'post', 'string', 'raw');

		try {

			// bind post
			self::bind($this->application, $post, array('params'));

			// set params
			$this->application->params = $this->application
				->getParams()
				->remove('content.')
				->remove('config.')
				->remove('template.')
				->set('content.', @$post['params']['content'])
				->set('config.', @$post['params']['config'])
				->set('template.', @$post['params']['template']);

			// save application
			$this->table->save($this->application);

			// set redirect message
			$msg = Text::_('Frontpage Saved');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Saving Frontpage').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

}

/*
	Class: FrontpageControllerException
*/
class FrontpageControllerException extends AppException {}
