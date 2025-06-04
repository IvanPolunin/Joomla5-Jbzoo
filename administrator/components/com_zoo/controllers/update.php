<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: UpdateController
		The controller class for updates
*/

use Joomla\CMS\Language\Text;

class UpdateController extends AppController {

	public function __construct($default = array()) {
		parent::__construct($default);

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

	}

	public function display($cachable = false, $urlparams = false) {

		// set toolbar items
		$this->zoo->toolbar->title(Text::_('ZOO Update'), $this->zoo->get('icon'));
		$this->zoo->zoo->toolbarHelp();

		$this->zoo->html->_('behavior.tooltip');

		if (!$this->update = $this->zoo->update->required()) {
			$this->zoo->system->application->redirect($this->zoo->link());
		}

		$this->notifications = $this->zoo->update->getNotifications();

		// display view
		$this->getView()->display();
	}

	public function step() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		$response = $this->zoo->update->run();

		echo json_encode($response);
	}

}

/*
	Class: UpdateAppControllerException
*/
class UpdateAppControllerException extends AppException {}
