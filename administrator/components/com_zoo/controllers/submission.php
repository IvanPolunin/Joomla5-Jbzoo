<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: SubmissionController
		The controller class for submission
*/

use Joomla\CMS\Access\Access;
use Joomla\CMS\Language\Text;

class SubmissionController extends AppController {

	public $application;

	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->submission;

		// get application
		$this->application 	= $this->zoo->zoo->getApplication();

		// check ACL
		if (!$this->application->isAdmin()) {
			throw new ConfigurationControllerException("Invalid Access Permissions!", 1);
		}

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

		// register tasks
        $this->registerTask('add', 'edit');
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save' );
	}

	public function display($cachable = false, $urlparams = false) {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Submissions'));
		$this->zoo->toolbar->addNew();
		$this->zoo->toolbar->editList();
		$this->zoo->toolbar->custom('docopy', 'copy.png', 'copy_f2.png', 'Copy');
		$this->zoo->toolbar->deleteList();
		$this->zoo->zoo->toolbarHelp();

		$this->zoo->html->_('behavior.tooltip');

		$state_prefix       = $this->option.'_'.$this->application->id.'.submission';
		$filter_order	    = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_order', 'filter_order', 'name', 'cmd');
		$filter_order_Dir   = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');

        // get data from the table
		$where = array();

		// application filter
		$where[] = 'application_id = ' . (int) $this->application->id;

		$options = array(
			'conditions' => array(implode(' AND ', $where)),
			'order' => $filter_order.' '.$filter_order_Dir);

		$this->submissions = $this->table->all($options);
        $this->submissions = array_merge($this->submissions);

		// table ordering and search filter
		$this->lists['order_Dir'] = $filter_order_Dir;
		$this->lists['order']	  = $filter_order;

		// display view
		$this->getView()->display();
	}

	public function edit() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// get request vars
		$cid  = $this->zoo->request->get('cid.0', 'int');
		$edit = $cid > 0;

		// get item
		if ($edit) {
			$this->submission = $this->table->get($cid);
		} else {
			$this->submission = $this->zoo->object->create('Submission');
			$this->submission->application_id = $this->application->id;
            $this->submission->access = 1;
		}

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Submission').': '.$this->submission->name.' <small><small>[ '.($edit ? Text::_('Edit') : Text::_('New')).' ]</small></small>');
		$this->zoo->toolbar->apply();
		$this->zoo->toolbar->save();
		$this->zoo->toolbar->save2new();
		$this->zoo->toolbar->cancel('cancel', $edit ? 'Close' : 'Cancel');
		$this->zoo->zoo->toolbarHelp();

        // published select
		$this->lists['select_published'] = $this->zoo->html->_('select.booleanlist', 'state', null, $this->submission->state);

		// access select
		$this->lists['select_access'] = $this->zoo->html->_('zoo.accesslevel', array(), 'access', 'class="inputbox"', 'value', 'text',$this->submission->access);

        // tooltip select
		$this->lists['select_tooltip'] = $this->zoo->html->_('select.booleanlist', 'params[show_tooltip]', null, $this->submission->showTooltip());

		// item captcha select
		$options = array($this->zoo->html->_('select.option', '', '- '.Text::_('Select Plugin').' -'));
		$this->lists['select_item_captcha'] = $this->zoo->html->_('zoo.pluginlist', $options, 'params[captcha]', '', 'value', 'text', $this->submission->getParams()->get('captcha', null), false, true, 'captcha');

        // type select
        $this->types = array();
        foreach ($this->application->getTypes() as $type) {

            // list types with submission layouts only
            if (count($this->zoo->type->layouts($type, 'submission')) > 0) {

                $form = $this->submission->getForm($type->id);

                $this->types[$type->id]['name'] = $type->name;

                $options = array($this->zoo->html->_('select.option', '', '- '.Text::_('not submittable').' -'));
                $this->types[$type->id]['select_layouts'] = $this->zoo->html->_('zoo.layoutlist', $type, 'submission', $options, 'params[form]['.$type->id.'][layout]', '', 'value', 'text', $form->get('layout'));

                $options = array($this->zoo->html->_('select.option', '', '- '.Text::_('uncategorized').' -'));
                $this->types[$type->id]['select_categories'] = $this->zoo->html->_('zoo.categorylist', $this->application, $options, 'params[form]['.$type->id.'][category]', 'size="1"', 'value', 'text', $form->get('category'));

            }
        }

        // display view
		$this->getView()->setLayout('edit')->display();
	}

	public function save() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$post = $this->zoo->request->get('post:', 'array', array());
		$cid  = $this->zoo->request->get('cid.0', 'int');

		try {

			// get item
			if ($cid) {
				$submission = $this->table->get($cid);
			} else {
				$submission = $this->zoo->object->create('Submission');
				$submission->application_id = $this->application->id;
			}

			// bind submission data
			self::bind($submission, $post, array('params'));

			// Force alias to be set
			if (!strlen(trim($submission->alias))) {
				$submission->alias = $this->zoo->string->sluggify($submission->name);
			}

            // generate unique slug
            $submission->alias = $this->zoo->alias->submission->getUniqueAlias($submission->id, $this->zoo->string->sluggify($submission->alias));

			// set params
			$submission->getParams()
                ->set('form.', @$post['params']['form'])
                ->set('trusted_mode', @$post['params']['trusted_mode'])
				->set('show_tooltip', @$post['params']['show_tooltip'])
				->set('max_submissions', @$post['params']['max_submissions'])
				->set('captcha', @$post['params']['captcha'])
				->set('captcha_guest_only', @$post['params']['captcha_guest_only'])
				->set('email_notification', @$post['params']['email_notification'])
				->set('config.', @$post['params']['config'])
				->set('content.', @$post['params']['content']);

			// save submission
			$this->table->save($submission);

			// set redirect message
			$msg = Text::_('Submission Saved');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Saving Submission').' ('.$e.')');
			$this->_task = 'apply';
			$msg = null;

		}

		$link = $this->baseurl;
		switch ($this->getTask()) {
			case 'apply' :
				$link .= '&task=edit&cid[]='.$submission->id;
				break;
			case 'save2new' :
				$link .= '&task=add';
				break;
		}

		$this->setRedirect($link, $msg);
	}

	public function remove() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$cid = $this->zoo->request->get('cid', 'int', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a submission to delete'));
		}

		try {

			// delete items
			foreach ($cid as $id) {
				$this->table->delete($this->table->get($id));
			}

			// set redirect message
			$msg = Text::_('Submission Deleted');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseWarning(0, Text::_('Error Deleting Submission').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function docopy() {
		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$cid = $this->zoo->request->get('cid', 'int', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a submission to copy'));
		}

		try {

			// copy submissions
			foreach ($cid as $id) {

				// get submission
				$submission = $this->table->get($id);

				// copy submission
				$submission->id         = 0;                         // set id to 0, to force new category
				$submission->name      .= ' ('.Text::_('Copy').')'; // set copied name
				$submission->alias      = $this->zoo->alias->submission->getUniqueAlias($id, $submission->alias.'-copy'); // set copied alias

				// save copied category data
				$this->table->save($submission);
			}

            // set redirect message
			$msg = Text::_('Submission Copied');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Copying Category').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function publish() {
		$this->_editState(1);
	}

	public function unpublish() {
		$this->_editState(0);
	}

	protected function _editState($state) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a submission to edit publish state'));
		}

		try {

			// update item state
			foreach ($cid as $id) {
				$submission = $this->table->get($id);
				$submission->state = $state;
				$this->table->save($submission);
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error editing Submission Published State').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

	public function enableTrustedMode() {
		$this->_editTrustedMode(1);
	}

	public function disableTrustedMode() {
		$this->_editTrustedMode(0);
	}

	protected function _editTrustedMode($enabled) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a submission to enable/disable Trusted Mode'));
		}

		try {

			// update item state
			foreach ($cid as $id) {
				$submission = $this->table->get($id);

				// trusted mode can only be enabled for nonpublic access
				if ($enabled == true) {
					if (!Access::checkGroup($this->zoo->zoo->getGroup($submission->access)->id, 'core.login.site')) {
						throw new AppException('Trusted mode can\'t be enabled for public access');
					}
				}

				$submission->getParams()
					->set('trusted_mode', $enabled);

				$this->table->save($submission);
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error enabling/disabling Submission Trusted Mode').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

}

/*
	Class: SubmissionControllerException
*/
class SubmissionControllerException extends AppException {}
