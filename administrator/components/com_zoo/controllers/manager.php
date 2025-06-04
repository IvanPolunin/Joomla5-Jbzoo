<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ManagerController
		The controller class for application manager
*/

use Joomla\Archive\Archive;
use Joomla\Archive\Zip;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\PluginHelper;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Toolbar\Toolbar;
use Joomla\CMS\Toolbar\ToolbarHelper;
use Joomla\CMS\Uri\Uri;

class ManagerController extends AppController {

	public $group;
	public $application;

	public function __construct($default = array()) {
		parent::__construct($default);

        // check ACL
        if (!$this->zoo->user->isAdmin()) {
            throw new ManagerControllerException("Invalid Access Permissions!", 1);
        }

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

		// get application group
		$this->group = $this->zoo->request->getString('group');

		// if group exists
		if ($this->group) {

			// add group to base url
			$this->baseurl .= '&group='.$this->group;

			// create application object
			$this->application = $this->zoo->object->create('Application');
			$this->application->setGroup($this->group);
		}

		// register tasks
		$this->registerTask('addtype', 'edittype');
		$this->registerTask('applytype', 'savetype');
		$this->registerTask('applyelements', 'saveelements');
		$this->registerTask('applyassignelements', 'saveassignelements');
		$this->registerTask('applysubmission', 'savesubmission');
	}

	public function display($cachable = false, $urlparams = false) {

		// set toolbar items
		$this->zoo->toolbar->title(Text::_('App Manager'), $this->zoo->get('icon'));
		ToolBar::getInstance('toolbar')->appendButton('Popup', 'warning', 'Check For Modifications', Route::_(Uri::root() . 'administrator/index.php?option='.$this->zoo->component->self->name.'&controller='.$this->controller.'&task=checkmodifications&tmpl=component', true, 2), 570, 350);
		ToolBar::getInstance('toolbar')->appendButton('Popup', 'checkmark', 'Check Requirements', Route::_(Uri::root() . 'administrator/index.php?option='.$this->zoo->component->self->name.'&controller='.$this->controller.'&task=checkrequirements&tmpl=component', true, 2), 570, 350);
		ToolbarHelper::preferences('com_zoo');
		$this->zoo->toolbar->custom('cleandb', 'refresh', 'Refresh', 'Clean Database', false);
		$this->zoo->toolbar->custom('dobackup', 'archive', 'Backup Database', 'Backup Database', false);
		$this->zoo->zoo->toolbarHelp();

		// get applications
		$this->applications = $this->zoo->application->groups();
        uasort($this->applications, function ($a, $b) {
            return strtotime($b->getMetadata('creationdate')) - strtotime($a->getMetadata('creationdate'));
        });

		// display view
		$this->getView()->display();
	}

	public function info() {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Information').': '.$this->application->getMetaData('name'));
		$this->zoo->toolbar->custom('doexport', 'archive', 'Archive', 'Export', false);
		$this->zoo->toolbar->custom('uninstallapplication', 'delete', 'Delete', 'Uninstall', false);
		$this->zoo->toolbar->deleteList('APP_DELETE_WARNING', 'removeapplication');
		$this->zoo->zoo->toolbarHelp();

		// get application instances for selected group
		$this->applications = $this->zoo->application->getApplications($this->application->getGroup());

		// display view
		$this->getView()->setLayout('info')->display();
	}

	public function installApplication() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// get the uploaded file information
		$userfile = $this->input->files->get('install_package', null, 'RAW');

		try {

			$result = $this->zoo->install->installApplicationFromUserfile($userfile);
			$update = $result == 2 ? 'updated' : 'installed';

			// set redirect message
			$msg = Text::sprintf('Application group (%s) successfully.', $update);

		} catch (InstallHelperException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::sprintf('Error installing Application group (%s).', $e));
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function uninstallApplication() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		try {

			$this->zoo->install->uninstallApplication($this->application);

			// set redirect message
			$msg = Text::_('Application group uninstalled successful.');
			$link = $this->baseurl;

			// remove current group from redirect link
			$link = str_replace('&group='.$this->group, '', $link);
		} catch (InstallHelperException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::sprintf('Error uninstalling application group (%s).', $e));
			$msg = null;
			$link = $this->baseurl.'&task=info';

		}

		$this->setRedirect($link, $msg);
	}

	public function removeApplication() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$cid = $this->zoo->request->get('cid', 'int', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a application to delete'));
		}

		try {

			$table = $this->zoo->table->application;

			// delete applications
			foreach ($cid as $id) {
				$table->delete($table->get($id));
			}

			// set redirect message
			$msg = Text::_('Application Deleted');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::sprintf('Error Deleting Application (%s).', $e));
			$msg = null;

		}

		$this->setRedirect($this->baseurl.'&task=info', $msg);
	}

	public function types() {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Types').': ' . $this->application->getMetaData('name'));
		$this->zoo->toolbar->addNew('addtype');
		$this->zoo->toolbar->custom('copytype', 'copy', '', 'Copy');
		$this->zoo->toolbar->deleteList('', 'removetype');
		$this->zoo->toolbar->editList('edittype');
		$this->zoo->zoo->toolbarHelp();

		// get types
		$this->types = $this->application->getTypes();

		// get templates
		$this->templates = $this->application->getTemplates();

		// get extensions / trigger layout init event
		$this->extensions = $this->zoo->event->dispatcher->notify($this->zoo->event->create($this->zoo, 'layout:init'))->getReturnValue();

		// display view
		$this->getView()->setLayout('types')->display();
	}

	public function editType() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// get request vars
		$cid  = $this->zoo->request->get('cid.0', 'string', '');
		$this->edit = $cid ? true : false;

		// get type
		if (empty($cid)) {
			$this->type = $this->zoo->object->create('Type', array(null, $this->application));
		} else {
			$this->type = $this->application->getType($cid);
		}

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Type').': '.$this->type->name.' <small><small>[ '.($this->edit ? Text::_('Edit') : Text::_('New')).' ]</small></small>');
		$this->zoo->toolbar->apply('applytype');
		$this->zoo->toolbar->save('savetype');
		$this->zoo->toolbar->cancel('types', $this->edit ?	'Close' : 'Cancel');
		$this->zoo->zoo->toolbarHelp();

		// display view
		ob_start();
		$this->getView()->setLayout('edittype')->display();
		$output = ob_get_contents();
		ob_end_clean();

		// trigger edit event
		$this->zoo->event->dispatcher->notify($this->zoo->event->create($this->type, 'type:editdisplay', array('html' => &$output)));

		echo $output;
	}

	public function copyType() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$msg = '';
		$cid = $this->zoo->request->get('cid', 'string', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a type to copy'));
		}

		// copy types
		foreach ($cid as $id) {
			try {

				// get type
				$type = $this->application->getType($id);

				// copy type
				$copy			  = $this->zoo->object->create('Type', array(null, $this->application));
				$copy->identifier = $type->identifier.'-copy';                   // set copied alias
				$this->zoo->type->setUniqueIndentifier($copy);	// set unique identifier
				$copy->name       = sprintf('%s (%s)', $type->name, Text::_('Copy')); // set copied name

				// give elements a new unique id
				$elements = array();
				foreach ($type->elements as $identifier => $element) {
					if ($type->getElement($identifier) && $type->getElement($identifier)->getGroup() != 'Core') {
						$elements[$this->zoo->utility->generateUUID()] = $element;
					} else {
						$elements[$identifier] = $element;
					}
				}
				$copy->elements = $elements;

				// save copied type
				$copy->save();

				// trigger copied event
				$this->zoo->event->dispatcher->notify($this->zoo->event->create($copy, 'type:copied', array('old_id' => $id)));

				$msg = Text::_('Type Copied');

			} catch (AppException $e) {

				// raise notice on exception
				$this->zoo->error->raiseNotice(0, Text::sprintf('Error Copying Type (%s).', $e));
				$msg = null;
				break;

			}
		}

		$this->setRedirect($this->baseurl.'&task=types', $msg);
	}

	public function saveType() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$post = $this->zoo->request->get('post:', 'array', array());
		$cid  = $this->zoo->request->get('cid.0', 'string', '');

		// get type
		$type = $this->application->getType($cid);

		// type is new ?
		if (!$type) {
			$type = $this->zoo->object->create('Type', array(null, $this->application));
		}

		// filter identifier
		$post['identifier'] = $this->zoo->string->sluggify($post['identifier'] == '' ? $post['name'] : $post['identifier'], true);

		try {

			// set post data and save type
			$type->bind($post);

			// ensure unique identifier
 			$this->zoo->type->setUniqueIndentifier($type);

			// save type
            $type->save();

			// set redirect message
			$msg = Text::_('Type Saved');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::sprintf('Error Saving Type (%s).', $e));
			$this->_task = 'apply';
			$msg = null;

		}

		switch ($this->getTask()) {
			case 'applytype':
				$link = $this->baseurl.'&task=edittype&cid[]='.$type->id;
				break;
			case 'savetype':
			default:
				$link = $this->baseurl.'&task=types';
				break;
		}

		$this->setRedirect($link, $msg);
	}

	public function removeType() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$msg = '';
		$cid = $this->zoo->request->get('cid', 'string', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a type to delete'));
		}

		foreach ($cid as $id) {
			try {

				// delete type
				$type = $this->application->getType($id);
				$type->delete();

				// trigger after save event
				$this->zoo->event->dispatcher->notify($this->zoo->event->create($type, 'type:deleted'));

				// set redirect message
				$msg = Text::_('Type Deleted');

			} catch (AppException $e) {

				// raise notice on exception
				$this->zoo->error->raiseNotice(0, Text::sprintf('Error Deleting Type (%s).', $e));
				$msg = null;
				break;

			}
		}

		$this->setRedirect($this->baseurl.'&task=types', $msg);
	}

	public function editElements() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// get request vars
		$cid = $this->zoo->request->get('cid.0', 'string', '');

		// get type
		$this->type = $this->application->getType($cid);

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Type').': '.$this->type->name.' <small><small>[ '. Text::_('Edit elements').' ]</small></small>');
		$this->zoo->toolbar->apply('applyelements');
		$this->zoo->toolbar->save('saveelements');
		$this->zoo->toolbar->cancel('types', 'Close');
		$this->zoo->zoo->toolbarHelp();

		// sort elements by group
		$this->elements = array();
		foreach ($this->zoo->element->getAll() as $element) {
			$this->elements[$element->getGroup()][$element->getElementType()] = $element;
		}
		ksort($this->elements);
		foreach ($this->elements as $group => $elements) {
			ksort($elements);
			$this->elements[$group] = $elements;
		}

		// display view
		$this->getView()->setLayout('editElements')->display();
	}

	public function addElement() {

		// get request vars
		$element = $this->zoo->request->getWord('element', 'text');

		// load element
		$this->element = $this->zoo->element->create($element);
		$this->element->identifier = $this->zoo->utility->generateUUID();

		// display view
		$this->getView()->setLayout('addElement')->display();
	}

	public function saveElements() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$post = $this->zoo->request->get('post:', 'array', array());
		$cid  = $this->zoo->request->get('cid.0', 'string', '');

		try {

			// save types elements
			$type = $this->application->getType($cid);

			// bind and save elements
			$type->bindElements($post)->save();

			// reset related item search data
			$table = $this->zoo->table->item;
			$items = $table->getByType($type->id, $this->application->id);
			foreach ($items as $item) {
				$table->save($item);
			}

			$msg = Text::_('Elements Saved');

		} catch (AppException $e) {

			$this->zoo->error->raiseNotice(0, Text::sprintf('Error Saving Elements (%s)', $e));
			$this->_task = 'applyelements';
			$msg = null;

		}

		switch ($this->getTask()) {
			case 'applyelements':
				$link = $this->baseurl.'&task=editelements&cid[]='.$type->id;
				break;
			case 'saveelements':
			default:
				$link = $this->baseurl.'&task=types';
				break;
		}

		$this->setRedirect($link, $msg);
	}

	public function assignElements() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// init vars
		$type				 = $this->zoo->request->getString('type');
		$this->relative_path = urldecode($this->zoo->request->getVar('path'));
		$this->path			 = $this->relative_path ? JPATH_ROOT . '/' . $this->relative_path : '';
		$this->layout		 = $this->zoo->request->getString('layout');

		if (strpos($this->relative_path, 'plugins') === 0) {
			@list($_, $plugin_type, $plugin_name) = explode('/', $this->relative_path);
			PluginHelper::importPlugin($plugin_type, $plugin_name);
		}
		$this->app->triggerEvent('registerZOOEvents');

		// get type
		$this->type = $this->application->getType($type);

        if ($this->type) {
            // set toolbar items
            $this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Type').': '.$this->type->name.' <small><small>[ '. Text::_('Assign elements').': '. $this->layout .' ]</small></small>');
            $this->zoo->toolbar->apply('applyassignelements');
			$this->zoo->toolbar->save('saveassignelements');
            $this->zoo->toolbar->cancel('types');
            $this->zoo->zoo->toolbarHelp();

            // get renderer
            $renderer = $this->zoo->renderer->create('item')->addPath($this->path);

            // get positions and config
            $this->config = $renderer->getConfig('item')->get($this->group.'.'.$type.'.'.$this->layout);

            $prefix = 'item.';
            if ($renderer->pathExists('item'.DIRECTORY_SEPARATOR.$type)) {
                $prefix .= $type.'.';
            }
            $this->positions = $renderer->getPositions($prefix.$this->layout);

			// display view
			ob_start();
			$this->getView()->setLayout('assignelements')->display();
			$output = ob_get_contents();
			ob_end_clean();

			// trigger edit event
			$this->zoo->event->dispatcher->notify($this->zoo->event->create($this->type, 'type:assignelements', array('html' => &$output)));

			echo $output;


        } else {

			$this->zoo->error->raiseNotice(0, Text::sprintf('Unable to find type (%s).', $type));
			$this->setRedirect($this->baseurl . '&task=types&group=' . $this->application->getGroup());

		}
	}

	public function saveAssignElements() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$type		   = $this->zoo->request->getString('type');
		$layout		   = $this->zoo->request->getString('layout');
		$relative_path = $this->zoo->request->getVar('path');
		$path		   = $relative_path ? JPATH_ROOT . '/' . urldecode($relative_path) : '';
		$positions	   = $this->zoo->request->getVar('positions', array(), 'post', 'array');

		// unset unassigned position
		unset($positions['unassigned']);

		// get renderer
		$renderer = $this->zoo->renderer->create('item')->addPath($path);

		// clean config
		$config = $renderer->getConfig('item');
		foreach ($config as $key => $value) {
			$parts = explode('.', $key);
			if ($parts[0] == $this->group && !$this->application->getType($parts[1])) {
				$config->remove($key);
			}
		}

		// save config
		$config->set($this->group.'.'.$type.'.'.$layout, $positions);
		$renderer->saveConfig($config, $path.'/renderer/item/positions.config');

		switch ($this->getTask()) {
			case 'applyassignelements':
				$link  = $this->baseurl.'&task=assignelements&type='.$type.'&layout='.$layout.'&path='.$relative_path;
				break;
			default:
				$link = $this->baseurl.'&task=types';
				break;
		}

		$this->setRedirect($link, Text::_('Elements Assigned'));
	}

	public function assignSubmission() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// init vars
		$type           = $this->zoo->request->getString('type');
		$this->template = $this->zoo->request->getString('template');
		$this->layout   = $this->zoo->request->getString('layout');

		// get type
		$this->type = $this->application->getType($type);

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Type').': '.$this->type->name.' <small><small>[ '. Text::_('Assign Submittable elements').': '. $this->layout .' ]</small></small>');
		$this->zoo->toolbar->apply('applysubmission');
		$this->zoo->toolbar->save('savesubmission');
		$this->zoo->toolbar->cancel('types');
		$this->zoo->zoo->toolbarHelp();

		// for template
		$this->path = $this->application->getPath().'/templates/'.$this->template;

		// get renderer
		$renderer = $this->zoo->renderer->create('submission')->addPath($this->path);

		// get positions and config
		$this->config = $renderer->getConfig('item')->get($this->group.'.'.$type.'.'.$this->layout);

		$prefix = 'item.';
		if ($renderer->pathExists('item'.DIRECTORY_SEPARATOR.$type)) {
			$prefix .= $type.'.';
		}
		$this->positions = $renderer->getPositions($prefix.$this->layout);

		// display view
		$this->getView()->setLayout('assignsubmission')->display();
	}

	public function saveSubmission() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$type      = $this->zoo->request->getString('type');
		$template  = $this->zoo->request->getString('template');
		$layout    = $this->zoo->request->getString('layout');
		$positions = $this->zoo->request->getVar('positions', array(), 'post', 'array');

		// unset unassigned position
		unset($positions['unassigned']);

		// for template, module
		$path = '';
		if ($template) {
			$path = $this->application->getPath().'/templates/'.$template;
		}

		// get renderer
		$renderer = $this->zoo->renderer->create('submission')->addPath($path);

		// clean config
		$config = $renderer->getConfig('item');
		foreach ($config as $key => $value) {
			$parts = explode('.', $key);
			if ($parts[0] == $this->group && !$this->application->getType($parts[1])) {
				$config->remove($key);
			}
		}

		// save config
		$config->set($this->group.'.'.$type.'.'.$layout, $positions);
		$renderer->saveConfig($config, $path.'/renderer/item/positions.config');

		switch ($this->getTask()) {
			case 'applysubmission':
				$link  = $this->baseurl.'&task=assignsubmission&type='.$type.'&layout='.$layout;
				$link .= $template ? '&template='.$template : null;
				break;
			default:
				$link = $this->baseurl.'&task=types';
				break;
		}

		$this->setRedirect($link, Text::_('Submitable Elements Assigned'));
	}

	public function doExport() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

        /** @var Zip $zip */
        $zip = (new Archive)->getAdapter('zip');
        $filepath = $this->zoo->path->path("tmp:").'/'.$this->application->getGroup().'.zip';
        $read_directory = $this->application->getPath() . '/';
        $files = $this->zoo->path->files($this->application->getResource(), true);
        $files = array_map(function ($file) use ($read_directory) { return ['name' => $file, 'data' => file_get_contents($read_directory.$file)]; }, $files);
        $zip->create($filepath, $files);
		if (is_readable($filepath) && File::exists($filepath)) {
			$this->zoo->filesystem->output($filepath);
			if (!File::delete($filepath)) {
				$this->zoo->error->raiseNotice(0, Text::sprintf('Unable to delete file(%s).', $filepath));
				$this->setRedirect($this->baseurl.'&task=info');
			}
		} else {
			$this->zoo->error->raiseNotice(0, Text::sprintf('Unable to create file (%s).', $filepath));
			$this->setRedirect($this->baseurl.'&task=info');
		}

	}

    public function checkRequirements() {

		$this->zoo->loader->register('AppRequirements', 'installation:requirements.php');

        $requirements = $this->zoo->object->create('AppRequirements');
        $requirements->checkRequirements();
        $requirements->displayResults();

    }

    public function checkModifications() {

		// add system.css for
		$this->zoo->document->addStylesheet("root:administrator/templates/system/css/system.css");

		try {

			$this->results = $this->zoo->modification->check();

			// display view
			$this->getView()->setLayout('modifications')->display();

		} catch (AppModificationException $e) {

			$this->zoo->error->raiseNotice(0, $e);

		}

    }

    public function cleanModifications() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		try {

			$this->zoo->modification->clean();
			$msg = Text::_('Unknown files removed.');

		} catch (AppModificationException $e) {

			$msg = Text::_('Error cleaning ZOO.');
			$this->zoo->error->raiseNotice(0, $e);

		}

		$route = Route::_($this->baseurl.'&task=checkmodifications&tmpl=component', false);
		$this->setRedirect($route, $msg);

    }

    public function verify() {

		$result = false;

		try {

			$result = $this->zoo->modification->verify();

		} catch (AppModificationException $e) {}

		echo json_encode(compact('result'));

    }

	public function doBackup() {

		if ($result = $this->zoo->backup->all()) {

			$result = $this->zoo->backup->generateHeader() . $result;
			$size = strlen($result);

			while (@ob_end_clean());

			header("Pragma: public");
			header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
			header("Expires: 0");
			header("Content-Transfer-Encoding: binary");
			header('Content-Type: application/zip');
			header('Content-Disposition: attachment;'
				.' filename="zoo-db-backup-'.time().'-'.(md5(implode(',', $this->zoo->backup->getTables()))).'.sql";'
				.' modification-date="'.date('r').'";'
				.' size='.$size.';');
			header("Content-Length: ".$size);

			echo $result;
			return;
		}

		// raise error on exception
		$this->zoo->error->raiseNotice(0, Text::_('Error Creating Backup'));
		$this->setRedirect($this->baseurl);

	}

	public function restoreBackup() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// get the uploaded file information
		$userfile = $this->input->files->get('backupfile');

		try {

			$file = $this->zoo->validator->create('file', array('extension' => array('sql')))->clean($userfile);

			$this->zoo->backup->restore($file['tmp_name']);
			$msg = Text::_('Database backup successfully restored');

		} catch (AppValidatorException $e) {

			$msg = '';
			$this->zoo->error->raiseNotice(0, "Error uploading backup file. ($e) Please upload .sql files only.");

		} catch (RuntimeException $e) {

			$msg = '';
			$this->zoo->error->raiseNotice(0, Text::sprintf("Error restoring ZOO backup. (%s)", $e->getMessage()));

		}

		$this->setRedirect($this->baseurl, $msg);

	}

	public function cleanDB() {

		// set toolbar items
		$this->zoo->toolbar->title('Cleaning database, please don\'t leave this page');

		$this->item_count = $this->zoo->table->item->count();
		$this->steps = (int) (11 + ($this->item_count / 10));

		// display view
		$this->getView()->setLayout('cleandb')->display();

	}

	public function cleanDBStep() {

		$step = $this->zoo->request->getInt('step', 0);
		$row = $this->zoo->request->getInt('row', 0);
		$offset = $this->zoo->request->getInt('offset', 0);

		// init vars
		$items_per_run = 10;
		$db = $this->zoo->database;
		$msg = '';
		$error = '';

		switch ($step) {
			case '1':
				if ($apps = $this->zoo->path->dirs('applications:')) {
					$db->query(sprintf("DELETE FROM ".ZOO_TABLE_APPLICATION." WHERE application_group NOT IN ('%s')", implode("', '", $apps)));
				}
				$msg = 'Cleaning application folders...';
				break;
			case '2':
				$db->query("DELETE FROM ".ZOO_TABLE_ITEM." WHERE type = ''");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning items of undefined type...';
				break;
			case '3':
				$db->query("DELETE FROM ".ZOO_TABLE_ITEM." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_APPLICATION." WHERE id = application_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning items that don\'t belong to an application...';
				break;
			case '4':
				$db->query("DELETE FROM ".ZOO_TABLE_CATEGORY." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_APPLICATION." WHERE id = application_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning categories that don\'t belong to an application...';
				break;
			case '5':
				$db->query("DELETE FROM ".ZOO_TABLE_SUBMISSION." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_APPLICATION." WHERE id = application_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning submissions that don\'t belong to an application...';
				break;
			case '6':
				$db->query("DELETE FROM ".ZOO_TABLE_TAG." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_ITEM." WHERE id = item_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning tags that don\'t belong to an item...';
				break;
			case '7':
				$db->query("DELETE FROM ".ZOO_TABLE_COMMENT." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_ITEM." WHERE id = item_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning comments that don\'t belong to an item...';
				break;
			case '8':
				$db->query("DELETE FROM ".ZOO_TABLE_RATING." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_ITEM." WHERE id = item_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning ratings that don\'t belong to an item...';
				break;
			case '9':
				$db->query("DELETE FROM ".ZOO_TABLE_SEARCH." WHERE NOT EXISTS (SELECT id FROM ".ZOO_TABLE_ITEM." WHERE id = item_id)");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning search values that don\'t belong to an item...';
				break;
			case '10':
				$db->query("DELETE FROM ".ZOO_TABLE_CATEGORY_ITEM." WHERE category_id != 0 AND (NOT EXISTS (SELECT id FROM ".ZOO_TABLE_ITEM." WHERE id = item_id) OR NOT EXISTS (SELECT id FROM ".ZOO_TABLE_CATEGORY." WHERE id = category_id))");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning category to item relations...';
				break;
			case '11':
				// sanatize parent references
				$db->query("UPDATE ".ZOO_TABLE_CATEGORY." SET parent = 0 WHERE parent != 0 AND NOT EXISTS (SELECT id FROM (SELECT id FROM ".ZOO_TABLE_CATEGORY.") as t WHERE t.id = parent)");
				$db->query("UPDATE ".ZOO_TABLE_COMMENT." SET parent_id = 0 WHERE parent_id != 0 AND NOT EXISTS (SELECT id FROM (SELECT id FROM ".ZOO_TABLE_CATEGORY.") as t WHERE t.id = parent_id)");
				$msg = 'Cleaning category and comment parent relations...';
				break;
			case '12':

				$assetNames = array();
				foreach ($this->zoo->application->getApplications() as $application) {
					foreach ($application->getTypes() as $type) {
						$assetNames[] = $type->getAssetName();
					}
				}
				$assetNames = "'".implode("','", $assetNames)."'";

				$db->query("UPDATE ".ZOO_TABLE_APPLICATION." SET asset_id = 0 WHERE NOT EXISTS (SELECT id FROM #__assets WHERE id = asset_id AND name = CONCAT('com_zoo.application.', ".ZOO_TABLE_APPLICATION.".id))");
				$row += $db->getAffectedRows();
				$db->query("DELETE FROM #__assets WHERE name LIKE ('com_zoo.%') AND name NOT IN (". $assetNames .") AND id NOT IN (SELECT asset_id FROM ".ZOO_TABLE_APPLICATION.")");
				$row += $db->getAffectedRows();
				$msg = 'Cleaning asset to application and type relations...';
				break;
			case '13':

				// get the item table
				$table = $this->zoo->table->item;

				try {

					$items = $table->all(array('offset' => $offset, 'limit' => $items_per_run));
					if (empty($items)) {
						echo json_encode(array('forward' => $this->baseurl, 'message' => Text::sprintf('Cleaned database (Removed %s entries) and items search data has been updated.', $row)));
						return;
					}
					foreach ($items as $item) {
						try {

							$table->save($item);

						} catch (Exception $e) {
							$error = Text::sprintf("Error updating search data for item with id %s. (%s)", $item->id, $e);
						}
					}

				} catch (Exception $e) {

					$msg = '';
					$error = Text::sprintf("Error cleaning database. (%s)", $e);

				}
				$msg = sprintf('Resaving items %s to %s', $offset, $offset + $items_per_run);
				$step--;
				$offset += $items_per_run;
		}

		echo json_encode(array(
			'error' => $error,
			'message' => $msg,
			'step' => $step + ($offset / $items_per_run),
			'redirect' => $this->baseurl.sprintf('&task=cleandbstep&format=raw&step=%s&row=%s&offset=%s', ++$step, $row, $offset)
		));
	}

	public function hideUpdateNotification() {
		$this->zoo->update->hideUpdateNotification();
	}

	public function getAlias() {
		$name 		= $this->zoo->request->getString('name', '');
		$force_safe = $this->zoo->request->getBool('force_safe', false);
		echo json_encode($this->zoo->string->sluggify($name, $force_safe));
	}

}

/*
	Class: ManagerControllerException
*/
class ManagerControllerException extends AppException {}
