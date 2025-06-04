<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ItemController
		Item controller class
*/

use Joomla\CMS\Language\Text;

class ItemController extends AppController {

    const PAGINATION_LIMIT = 20;

	public $application;

 	/*
		Function: Constructor

		Parameters:
			$default - Array

		Returns:
			DefaultController
	*/
	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->item;

		// get application
		$this->application = $this->zoo->zoo->getApplication();

		// set user
		$this->user = $this->zoo->user->get();

	}

	public function element() {

		// include template css
		$template = $this->zoo->database->queryResult('SELECT template FROM #__template_styles WHERE client_id = 1 AND home = 1');
		$this->zoo->document->addStylesheet("root:administrator/templates/$template/css/template.css");

		// get database
		$this->db = $this->zoo->database;

		// get Joomla application
		$this->joomla = $this->zoo->system->application;

		// get request vars
		$this->filter_item	= $this->zoo->request->getInt('item_filter', 0);
		$this->type_filter	= $this->zoo->request->get('type_filter', 'array', array());
		$state_prefix       = $this->option.'_'.$this->application->id.'.'.($this->getTask() == 'element' ? 'element' : 'item').'.';
		$filter_order	    = $this->joomla->getUserStateFromRequest($state_prefix.'filter_order', 'filter_order', 'a.created', 'cmd');
		$filter_order_Dir   = $this->joomla->getUserStateFromRequest($state_prefix.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_category_id = $this->joomla->getUserStateFromRequest($state_prefix.'filter_category_id', 'filter_category_id', '-1', 'string');
		$filter_type     	= $this->joomla->getUserStateFromRequest($state_prefix.'filter_type', 'filter_type', '', 'string');
		$filter_author_id   = $this->joomla->getUserStateFromRequest($state_prefix.'filter_author_id', 'filter_author_id', 0, 'int');
		$search	            = $this->joomla->getUserStateFromRequest($state_prefix.'search', 'search', '', 'string');
		$search			    = $this->zoo->string->strtolower($search);
		$page				= $this->zoo->request->getInt('page', 1);
		$limit				= ItemController::PAGINATION_LIMIT;

		// is filtered ?
		$this->is_filtered = $filter_category_id <> '-1' || !empty($filter_type) || !empty($filter_author_id) || !empty($search);

		$this->users  = $this->table->getUsers($this->application->id);
		$this->groups = $this->zoo->zoo->getGroups();

		// select
		$select = 'a.*';

		// get from
		$from = $this->table->name.' AS a';

		// get data from the table
		$where = array();

		// application filter
		$where[] = 'a.application_id = ' . (int) $this->application->id;

		// category filter
		if ($filter_category_id === '') {
			$from   .= ' LEFT JOIN '.ZOO_TABLE_CATEGORY_ITEM.' AS ci ON a.id = ci.item_id';
			$where[] = 'ci.item_id IS NULL';
        } else if ($filter_category_id > -1) {
			$from   .= ' LEFT JOIN '.ZOO_TABLE_CATEGORY_ITEM.' AS ci ON a.id = ci.item_id';
			$where[] = 'ci.category_id = ' . (int) $filter_category_id;
		}

		// type filter
		if (!empty($this->type_filter)) {
			$where[] = 'a.type IN ("' . implode('", "', $this->type_filter) . '")';
		} else if (!empty($filter_type)) {
			$where[] = 'a.type = "' . (string) $filter_type . '"';
		}

		// item filter
		if ($this->filter_item > 0) {
			$where[] = 'a.id != ' . (int) $this->filter_item;
		}

		// author filter
		if ($filter_author_id > 0) {
			$where[] = 'a.created_by = ' . (int) $filter_author_id;
		}

		if ($search) {
			$from   .= ' LEFT JOIN '.ZOO_TABLE_TAG.' AS t ON a.id = t.item_id';
			$where[] = '(LOWER(a.name) LIKE '.$this->db->Quote('%'.$this->db->escape($search, true).'%', false)
				. ' OR LOWER(t.name) LIKE '.$this->db->Quote('%'.$this->db->escape($search, true).'%', false)
				. ' OR LOWER(a.alias) LIKE '.$this->db->Quote('%'.$this->db->escape($search, true).'%', false).')';
		}

		// access filter
		$where[] = 'a.'.$this->zoo->user->getDBAccessString($this->user);

		// state filter
		$where[] = 'a.state = 1';

		$options = array(
			'select' => $select,
			'from' =>  $from,
			'conditions' => array(implode(' AND ', $where)),
			'order' => $filter_order.' '.$filter_order_Dir,
			'group' => 'a.id');

		$count = $this->table->count($options);
		// in case limit has been changed, adjust limitstart accordingly
		$limitstart = ($page - 1) * $limit;

		$this->items = $this->table->all($limit > 0 ? array_merge($options, array('offset' => $limitstart, 'limit' => $limit)) : $options);
		$this->items = array_merge($this->items);

		$this->pagination = $this->zoo->pagination->create($count, $limitstart, $limit, 'page', 'app');

		// category select
		$options = array();
        $options[] = $this->zoo->html->_('select.option', '-1', '- ' . Text::_('Select Category') . ' -');
        $options[] = $this->zoo->html->_('select.option', '', '- ' . Text::_('uncategorized') . ' -');
		$options[] = $this->zoo->html->_('select.option', '0', '- '.Text::_('Frontpage'));
		$this->lists['select_category'] = $this->zoo->html->_('zoo.categorylist', $this->application, $options, 'filter_category_id', 'class="inputbox auto-submit"', 'value', 'text', $filter_category_id);

		// type select
		$options = array($this->zoo->html->_('select.option', '0', '- '.Text::_('Select Type').' -'));
		$this->lists['select_type'] = $this->zoo->html->_('zoo.typelist', $this->application, $options, 'filter_type', 'class="inputbox auto-submit"', 'value', 'text', $filter_type, false, false, $this->type_filter);

		// author select
		$options = array($this->zoo->html->_('select.option', '0', '- '.Text::_('Select Author').' -'));
		$this->lists['select_author'] = $this->zoo->html->_('zoo.itemauthorlist',  $options, 'filter_author_id', 'class="inputbox auto-submit"', 'value', 'text', $filter_author_id);

		// table ordering and search filter
		$this->lists['order_Dir'] = $filter_order_Dir;
		$this->lists['order']	  = $filter_order;
		$this->lists['search']    = $search;

		$this->getView()->setLayout('element')->display();

	}

}
