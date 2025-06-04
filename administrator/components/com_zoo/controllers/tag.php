<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: TagController
		The controller class for tag
*/

use Joomla\CMS\Language\Text;

class TagController extends AppController {

	public $application;

	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->tag;

		// get application
		$this->application 	= $this->zoo->zoo->getApplication();

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

	}

	public function display($cachable = false, $urlparams = false) {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Tags'));
		$this->zoo->toolbar->deleteList();
		$this->zoo->zoo->toolbarHelp();

		$this->zoo->html->_('behavior.tooltip');

		// get request vars
		$state_prefix     = $this->option.'_'.$this->application->id.'.tags.';
		$filter_order	  = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_order', 'filter_order', '', 'cmd');
		$filter_order_Dir = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$limit		      = $this->zoo->system->application->getUserStateFromRequest('global.list.limit', 'limit', $this->zoo->system->config->get('list_limit'), 'int');
		$limitstart		  = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'limitstart', 'limitstart', 0,	'int');
		$search	          = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'search', 'search', '', 'string');
		$search			  = $this->zoo->string->strtolower($search);

		// is filtered ?
		$this->is_filtered = !empty($search);

		// in case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);

		// get data
		$filter     = ($filter_order) ? $filter_order . ' ' . $filter_order_Dir : '';

		$count = (int) $this->table->count($this->application->id, $search);
		$limitstart = $limitstart > $count ? floor($count / $limit) * $limit : $limitstart;

		$this->tags = $this->table->getAll($this->application->id, $search, '', $filter, $limitstart, $limit);

		$this->pagination = $this->zoo->pagination->create($count, $limitstart, $limit);

		// table ordering and search filter
		$this->lists['order_Dir'] = $filter_order_Dir;
		$this->lists['order']     = $filter_order;
		$this->lists['search']    = $search;

		// display view
		$this->getView()->display();
	}

	public function remove() {

		// init vars
		$tags = $this->zoo->request->get('cid', 'string', array());

		if (count($tags) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a tag to delete'));
		}

		try {

			// delete tags
			$this->table->delete($tags, $this->application->id);

			// set redirect message
			$msg = Text::_('Tag Deleted');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseWarning(0, Text::_('Error Deleting Tag').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function update() {

		// init vars
		$old = $this->zoo->request->getString('old');
		$new = $this->zoo->request->getString('new');
		$msg = null;

		try {

			// update tag
			if (!empty($new) && $old != $new) {
				$this->table->update($this->application->id, $old, $new);

				// set redirect message
				$msg = Text::_('Tag Updated Successfully');
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseWarning(0, Text::_('Error Updating Tag').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl, $msg);
	}

}

/*
	Class: TagControllerException
*/
class TagControllerException extends AppException {}
