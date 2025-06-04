<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ItemController
		The controller class for item
*/

use Joomla\CMS\Language\Text;
use Joomla\Utilities\ArrayHelper;

class ItemController extends AppController {

	public $application;

	const MAX_MOST_USED_TAGS = 8;

	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->item;

		// get application
		$this->application 	= $this->zoo->zoo->getApplication();

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

		// set user
		$this->user = $this->zoo->user->get();

		// register tasks
		$this->registerTask('element', 'display');
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save');
	}

	public function display($cachable = false, $urlparams = false) {

		// get app from Request (currently used in zooapplication element)
		if ($id = $this->zoo->request->getInt('app_id')) {
			$this->application = $this->zoo->table->application->get($id);
		}

		// get database
		$this->db = $this->zoo->database;

		// set toolbar items
		$canCreate    = false;
		$canDelete    = false;
		$canEditState = false;

		foreach ($this->application->getTypes() as $type) {
			if ($type->canCreate()) {
				$canCreate = true;
			}
			if ($type->canDelete()) {
				$canDelete = true;
			}
			if ($type->canEditState()) {
				$canEditState = true;
			}
		}
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Items'));
		if ($canCreate) {
			$this->zoo->toolbar->addNew();
		}
		$this->zoo->toolbar->editList();
		if ($canEditState) {
			$this->zoo->toolbar->publishList();
			$this->zoo->toolbar->unpublishList();
		}
		if ($this->application->canManageFrontpage()) {
			$this->zoo->toolbar->custom('togglefrontpage', 'checkin', 'checkin', 'Toggle Frontpage', true);
		}
		if ($canCreate) {
			$this->zoo->toolbar->custom('docopy', 'copy.png', 'copy_f2.png', 'Copy');
		}
		if ($canDelete) {
			$this->zoo->toolbar->deleteList();
		}

		$this->zoo->zoo->toolbarHelp();

		$this->zoo->html->_('behavior.tooltip');

		// get request vars
		$this->filter_item	= $this->zoo->request->getInt('item_filter', 0);
		$this->type_filter	= $this->zoo->request->get('type_filter', 'array', array());
		$state_prefix       = $this->option.'_'.$this->application->id.'.'.($this->getTask() == 'element' ? 'element' : 'item').'.';
		$filter_order	    = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_order', 'filter_order', 'a.created', 'cmd');
		$filter_order_Dir   = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_order_Dir', 'filter_order_Dir', 'desc', 'word');
		$filter_category_id = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_category_id', 'filter_category_id', '-1', 'string');
		$limit		        = $this->zoo->system->application->getUserStateFromRequest('global.list.limit', 'limit', $this->zoo->system->config->get('list_limit'), 'int');
		$limitstart			= $this->zoo->system->application->getUserStateFromRequest($state_prefix.'limitstart', 'limitstart', 0,	'int');
		$filter_type     	= $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_type', 'filter_type', '', 'string');
		$filter_author_id   = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter_author_id', 'filter_author_id', 0, 'int');
		$search	            = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'search', 'search', '', 'string');
		$search			    = $this->zoo->string->strtolower($search);

		// is filtered ?
		$this->is_filtered = $filter_category_id <> '-1' || !empty($filter_type) || !empty($filter_author_id) || !empty($search);

		$this->users  = $this->table->getUsers($this->application->id);
		$this->groups = $this->zoo->zoo->getGroups();

		// select
		$select = 'a.*, EXISTS (SELECT true FROM '.ZOO_TABLE_CATEGORY_ITEM.' WHERE item_id = a.id AND category_id = 0) as frontpage';

		// get from
		$from = $this->table->name.' AS a';

		// get data from the table
		$where = array();

		// application filter
		$where[] = 'a.application_id = ' . (int) $this->application->id;

		// access filter
        if (!$this->user->authorise('core.admin')) {
            $where[] = "a.{$this->zoo->user->getDBAccessString()}";
        }

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
				. ' OR LOWER(a.alias) LIKE '.$this->db->Quote('%'.$this->db->escape($search, true).'%', false) . ')';
		}

		$options = array(
            'select' => 'a.id',
			'from' =>  $from,
			'conditions' => array(implode(' AND ', $where)),
			'group' => 'a.id');

		$count = $this->table->count($options);

		$options['select'] = $select;
        $options['order'] = $filter_order.' '.$filter_order_Dir;

		// in case limit has been changed, adjust limitstart accordingly
		$limitstart = ($limit != 0 ? (floor($limitstart / $limit) * $limit) : 0);
		$limitstart = $limitstart > $count ? floor($count / $limit) * $limit : $limitstart;

		$this->items = $this->table->all($limit > 0 ? array_merge($options, array('offset' => $limitstart, 'limit' => $limit)) : $options);
		$this->items = array_merge($this->items);

		$this->pagination = $this->zoo->pagination->create($count, $limitstart, $limit);

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

		// display view
		$layout = $this->getTask() == 'element' ? 'element' : 'default';
		$this->getView()->setLayout($layout)->display();
	}

	public function loadtags() {

		// get request vars
		$tag = $this->zoo->request->getString('tag', '');

		echo $this->zoo->tag->loadTags($this->application->id, $tag);

	}

	public function add() {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Item') .': <small><small>[ '.Text::_('New').' ]</small></small>');
		$this->zoo->toolbar->cancel();

		// get types
		$this->types = array();
		foreach ($this->application->getTypes() as $name => $type) {
			if ($this->zoo->user->canCreate(null, $type->getAssetName())) {
				$this->types[$name] = $type;
			}
		}

		// no types available ?
		if (count($this->types) == 0) {
			$this->zoo->error->raiseNotice(0, Text::_('Please create a type first.'));
			$this->zoo->system->application->redirect($this->zoo->link(array('controller' => 'manager', 'task' => 'types', 'group' => $this->application->application_group), false));
		}

		// only one type ? then skip type selection
		if (count($this->types) == 1) {
			$type = array_shift($this->types);
			$this->zoo->system->application->redirect($this->baseurl.'&task=edit&type='.$type->id);
		}

		// display view
		$this->getView()->setLayout('add')->display();
	}

	public function edit() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// get request vars
		$cid  = $this->zoo->request->get('cid.0', 'int');
		$edit = $cid > 0;

		// get item
		if ($edit) {
			if (!$this->item = $this->zoo->table->item->get($cid)) {
				$this->zoo->error->raiseError(500, Text::sprintf('Unable to access item with id %s', $cid));
				return;
			}

			// check ACL
			if (!$this->item->canEdit()) {
				throw new ItemControllerException("Invalid access permissions", 1);
			}
		} else {
			$this->item = $this->zoo->object->create('Item');
			$this->item->application_id = $this->application->id;
			$this->item->type = $this->zoo->request->getVar('type');
			$this->item->publish_down = $this->zoo->database->getNullDate();
			$this->item->access = $this->zoo->joomla->getDefaultAccess();

			// check ACL
			if (!$this->item->canCreate()) {
				throw new ItemControllerException("Invalid access permissions", 1);
			}
		}

		// get item params
		$this->params = $this->item->getParams();

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Item').': '.$this->item->name.' <small><small>[ '.($edit ? Text::_('Edit') : Text::_('New')).' ]</small></small>');
		$this->zoo->toolbar->apply();
		$this->zoo->toolbar->save();
		$this->zoo->toolbar->save2new();
		if ($edit) {
			$this->zoo->toolbar->save2copy();
		}
		$this->zoo->toolbar->cancel('cancel', $edit ? 'Close' : 'Cancel');
		$this->zoo->zoo->toolbarHelp();

		// published select
		$this->lists['select_published'] = $this->zoo->html->_('select.booleanlist', 'state', null, $this->item->state);

		// published searchable
		$this->lists['select_searchable'] = $this->zoo->html->_('select.booleanlist', 'searchable', null, $this->item->searchable);

		// categories select
		$related_categories = $this->item->getRelatedCategoryIds();
		$this->lists['select_frontpage']  = $this->zoo->html->_('select.booleanlist', 'frontpage', null, in_array(0, $related_categories));
		$this->lists['select_categories'] = count($this->application->getCategoryTree()) > 1 ?
				$this->zoo->html->_('zoo.categorylist', $this->application, array(), 'categories[]', 'size="15" multiple="multiple" data-no_results_text="'.Text::_('No results match').'" data-placeholder="'.Text::_('Select Category').'"', 'value', 'text', $related_categories, false, false, 0 ,'<sup>|_</sup>&nbsp;', '.&nbsp;&nbsp;&nbsp;', '')
				: '<a href="'.$this->zoo->link(array('controller' => 'category'), false).'" >'.Text::_('Please add categories first').'</a>';
		$this->lists['select_primary_category'] = $this->zoo->html->_('zoo.categorylist', $this->application, array($this->zoo->html->_('select.option', '', Text::_('COM_ZOO_NONE'))), 'params[primary_category]', 'data-no_results_text="'.Text::_('No results match').'"', 'value', 'text', $this->params->get('config.primary_category'), false, false, 0 ,'<sup>|_</sup>&nbsp;', '.&nbsp;&nbsp;&nbsp;', '');
		// most used tags
		$this->lists['most_used_tags'] = $this->zoo->table->tag->getAll($this->application->id, null, null, 'items DESC, a.name ASC', null, self::MAX_MOST_USED_TAGS);

		// comments enabled select
		$this->lists['select_enable_comments'] = $this->zoo->html->_('select.booleanlist', 'params[enable_comments]', null, $this->params->get('config.enable_comments', 1));

		// display view
		$this->getView()->setLayout('edit')->display();
	}

	public function save() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$now        = $this->zoo->date->create();
		$frontpage  = $this->zoo->request->getBool('frontpage', false);
		$categories	= $this->zoo->request->get('categories', 'array', array());
		$details	= $this->zoo->request->get('details', null);
		$cid        = $this->zoo->request->get('cid.0', 'int');
		$tzoffset   = $this->zoo->date->getOffset();
		$post       = array_merge(
            $this->zoo->request->get('post:', 'array', array()),
            $details,
            // workaround to support bc for behavior in Zoo < 4.0
            array('elements' => $this->zoo->string->applyTextFilters(
                $this->zoo->system->application->input->post->get('elements', array(), 'raw'))
            )
        );

		try {

			// get item
			if ($cid) {
				$item = $this->table->get($cid);
			} else {
				$item = $this->zoo->object->create('Item');
				$item->application_id = $this->application->id;
				$item->type = $this->zoo->request->getVar('type');
			}

			// Check ACL
			if (!$cid && !$item->canCreate() || $cid && !$item->canEdit()) {
				throw new ItemControllerException("Invalid access permissions", 1);
			}

			if (!$item->canEditState()) {
				unset($item->state);
			}

			// bind item data
			self::bind($item, $post, array('elements', 'params', 'created_by'));
            $created_by = isset($post['created_by']) ? $post['created_by'] : 0;
            $item->created_by = empty($created_by) ? $this->zoo->user->get()->id : ($created_by == 'NO_CHANGE' ? $item->created_by : $created_by);
			$tags = isset($post['tags']) ? $post['tags'] : array();
			$item->setTags($tags);

			// bind element data
            $elements = $item->getElements();
			foreach ($elements as $id => $element) {
                if ($element->canAccess() || $this->user->authorise('core.admin')) {
                    if (isset($post['elements'][$id])) {
                        $element->bindData($post['elements'][$id]);
                    } else {
                        $element->bindData();
                    }
                }
			}

			foreach ($item->elements as $id => $element) {
			    if (!isset($elements[$id])) {
			        $item->elements->remove($id);
                }
            }

			// set alias
			if (!strlen(trim($item->alias))) {
				$item->alias = $this->zoo->string->sluggify($item->name);
			}
			$item->alias = $this->zoo->alias->item->getUniqueAlias($item->id, $this->zoo->string->sluggify($item->alias));

			// set modified
			$item->modified	   = $now->toSQL();
			$item->modified_by = $this->user->get('id');

			// set created date
			try {
                $item->created = $this->zoo->date->create($item->created, $tzoffset)->toSQL();
            } catch (Exception $e) {
                $item->created = $this->zoo->date->create()->toSQL();
            }

			// set publish up date
            try {
                $item->publish_up = $this->zoo->date->create($item->publish_up, $tzoffset)->toSQL();
            } catch (Exception $e) {
                $item->publish_up = $this->zoo->date->create()->toSQL();
            }

			// set publish down date
            try {
                $item->publish_down = $this->zoo->date->create($item->publish_down, $tzoffset)->toSQL();
            } catch (Exception $e) {
                $item->publish_down = $this->zoo->database->getNullDate();
            }

			// get primary category
			$primary_category = @$post['params']['primary_category'];
			if (empty($primary_category) && count($categories)) {
				$primary_category = $categories[0];
			}

			// set params
			$item->getParams()
				->remove('metadata.')
				->remove('template.')
				->remove('content.')
				->remove('config.')
				->set('metadata.', @$post['params']['metadata'])
				->set('template.', @$post['params']['template'])
				->set('content.', @$post['params']['content'])
				->set('config.', @$post['params']['config'])
				->set('config.enable_comments', @$post['params']['enable_comments'])
				->set('config.primary_category', $primary_category);

			// save item
			$this->table->save($item);

			// make sure categories contain primary category
			if (!empty($primary_category) && !in_array($primary_category, $categories)) {
				$categories[] = $primary_category;
			}

			// save category relations
			if ($frontpage) {
				$categories[] = 0;
			}
			$this->zoo->category->saveCategoryItemRelations($item, $categories);

			// set redirect message
			$msg = Text::_('Item Saved');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Saving Item').' ('.$e.')');
			$this->_task = 'apply';
			$msg = null;

		}

		$link = $this->baseurl;
		switch ($this->getTask()) {
			case 'save2copy' :
			case 'apply' :
				$link .= '&task=edit&type='.$item->type.'&cid[]='.$item->id;
				break;
			case 'save2new' :
				$link .= '&task=add';
				break;
		}

		$this->setRedirect($link, $msg);
	}

	public function save2copy() {
        $this->zoo->request->setVar('id', '0');
        $this->zoo->request->setVar('name', $this->zoo->request->get('name', 'string').' ('.Text::_('Copy').')');
		$this->zoo->request->set('cid.0', 0);
		$this->save();
	}

	public function docopy() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$now  = $this->zoo->date->create()->toSQL();
		$cid  = $this->zoo->request->get('cid', 'int', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a item to copy'));
		}

		try {

			// copy items
			foreach ($cid as $id) {

				// get item
				$item       = $this->table->get($id);
				$categories = $item->getRelatedCategoryIds();

				// Check ACL
				if (!$item->canCreate()) {
					continue;
				}

				// copy item
				$item->id          = 0;                         						// set id to 0, to force new item
				$item->name       .= ' ('.Text::_('Copy').')'; 						// set copied name
				$item->alias       = $this->zoo->alias->item->getUniqueAlias($id, $item->alias.'-copy'); 	// set copied alias
				$item->state       = 0;                         						// unpublish item
				$item->created	   = $item->modified = $now;
				$item->created_by  = $item->modified_by = $this->user->get('id');
				$item->hits		   = 0;

				// copy tags
				$item->setTags($this->zoo->table->tag->getItemTags($id));

				// save copied item/element data
				$this->table->save($item);

				// save category relations
				$this->zoo->category->saveCategoryItemRelations($item, $categories);
			}

			// set redirect message
			$msg = Text::_('Item Copied');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Copying Item').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function remove() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$cid = $this->zoo->request->get('cid', 'int', array());

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select an item to delete'));
		}

		try {

			// delete items
			foreach ($cid as $id) {
				$item = $this->table->get($id);

				// Check ACL
				if (!$item || !$item->canDelete()) {
					continue;
				}

				$this->table->delete($item);
			}

			// set redirect message
			$msg = Text::_('Item Deleted');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseWarning(0, Text::_('Error Deleting Item').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function savepriority() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$msg      = Text::_('Order Priority saved');
		// init vars
		$priority = $this->zoo->request->get('priority', 'array', array());

		try {

			// update the priority for items
			foreach ($priority as $id => $value) {
				$item = $this->table->get((int) $id);

				// only update, if changed and ACL is checked
				if ($item->canEdit() && $item->priority != $value) {
					$item->priority = $value;
					$this->table->save($item);
				}
			}

			// set redirect message
			$msg = json_encode(array(
				'group' => 'info',
				'title' => Text::_('Success!'),
				'text'  => Text::_('Item Priorities Saved')));

		} catch (AppException $e) {

			// raise error on exception
			$msg = json_encode(array(
				'group' => 'error',
				'title' => Text::_('Error!'),
				'text'  => Text::_('Error editing item priority').' ('.$e.')'));

		}

		echo $msg;
	}

	public function resethits() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$msg = null;
		$cid = $this->zoo->request->get('cid.0', 'int');

		try {

			// get item
			$item = $this->table->get($cid);

			// Check ACL
			if (!$item->canEdit()) {
				throw new ItemControllerException("Invalid access permissions", 1);
			}

			// reset hits
			if ($item->hits > 0) {
				$item->hits = 0;

				// save item
				$this->table->save($item);

				// set redirect message
				$msg = Text::_('Item Hits Reseted');
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Reseting Item Hits').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl.'&task=edit&cid[]='.$item->id, $msg);
	}

	public function publish() {
		$this->_editState(1);
	}

	public function unpublish() {
		$this->_editState(0);
	}

	public function makeSearchable() {
		$this->_editSearchable(1);
	}

	public function makeNoneSearchable() {
		$this->_editSearchable(0);
	}

	public function enableComments() {
		$this->_editComments(1);
	}

	public function disableComments() {
		$this->_editComments(0);
	}

	protected function _editState($state) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select an item to edit publish state'));
		}

		try {

			// update item state
			foreach ($cid as $id) {
				// check ACL
				if ($this->table->get($id)->canEditState()) {
					$this->table->get($id)->setState($state, true);
				}
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error editing Item Published State').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

	protected function _editSearchable($searchable) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select an item to edit searchable state'));
		}

		try {

			// update item searchable
			foreach ($cid as $id) {
				$item = $this->table->get($id);

				// Check ACL
				if (!$item->canEdit()) {
					continue;
				}

				$item->searchable = $searchable;
				$this->table->save($item);
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error editing Item Searchable State').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

	protected function _editComments($enabled) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select an item to enable/disable comments'));
		}

		try {

			// update item comments
			foreach ($cid as $id) {
				$item = $this->table->get($id);

				// Check ACL
				if (!$item->canEdit()) {
					continue;
				}

				$item->params = $item
					->getParams()
					->set('config.enable_comments', $enabled);

				$this->table->save($item);
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error enabling/disabling Item Comments').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

	public function toggleFrontpage() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select an item to toggle item frontpage setting'));
		}

		try {

			// toggle item frontpage
			foreach ($cid as $id) {
				$item = $this->table->get($id);

				// Check ACL
				if (!$item->canEdit()) {
					continue;
				}

				$categories = array_map('intval', $item->getRelatedCategoryIds());
				if (($key = array_search(0, $categories, true)) !== false) {
					unset($categories[$key]);
				} else {
					array_push($categories, 0);
				}

				$this->zoo->category->saveCategoryItemRelations($item, $categories);

			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error toggling item frontpage setting').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);

	}

	public function callElement() {

		// get request vars
		$element_identifier = $this->zoo->request->getString('elm_id', '');
		$item_id			= $this->zoo->request->getInt('item_id', 0);
		$type	 			= $this->zoo->request->getString('type', '');
		$this->method 		= $this->zoo->request->getCmd('method', '');
		$this->args       	= $this->zoo->request->getVar('args', array(), 'default', 'array');

		ArrayHelper::toString($this->args);

		// load element
		if ($item_id) {
			$item = $this->table->get($item_id);
		} elseif (!empty($type)) {
			$item = $this->zoo->object->create('Item');
			$item->application_id = $this->application->id;
			$item->type = $type;
		} else {
			return;
		}

		// Check ACL
		if (!$item->canEdit()) {
			return;
		}

		// execute callback method
		if ($element = $item->getElement($element_identifier)) {
			echo $element->callback($this->method, $this->args);
		}

	}

}

/*
	Class: ItemControllerException
*/
class ItemControllerException extends AppException {}
