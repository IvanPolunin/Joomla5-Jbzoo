<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: CategoryController
		The controller class for category
*/

use Joomla\CMS\Language\Text;

class CategoryController extends AppController {

	public $application;

	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->category;

		// get application
		$this->application 	= $this->zoo->zoo->getApplication();

		// check ACL
		if (!$this->application->canManageCategories()) {
			throw new CategorAppControllerException("Invalid access permissions", 1);
		}

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

		// registers tasks
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save' );
		$this->registerTask('add', 'edit');
	}

	public function display($cachable = false, $urlparams = false) {

		// set toolbar items
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Categories'));
		$this->zoo->toolbar->addNew();
		$this->zoo->toolbar->editList();
		$this->zoo->toolbar->publishList();
		$this->zoo->toolbar->unpublishList();
		$this->zoo->toolbar->custom('docopy', 'copy.png', 'copy_f2.png', 'Copy', true);
		$this->zoo->toolbar->deleteList();
		$this->zoo->toolbar->custom('resetorder', 'refresh.png', 'refresh.png', 'Reorder', false);
		$this->zoo->zoo->toolbarHelp();

		$this->zoo->html->_('behavior.tooltip');

		// get data
		$this->categories = $this->application->getCategoryTree(false, null, true);

		// display view
		$this->getView()->display();
	}

	public function edit() {

		// disable menu
		$this->zoo->request->setVar('hidemainmenu', 1);

		// get request vars
		$cid  = $this->zoo->request->get('cid.0', 'int');
		$edit = $cid > 0;

		// get category
		if ($edit) {
			$this->category = $this->zoo->table->category->get($cid);
		} else {
			$this->category = $this->zoo->object->create('Category');
			$this->category->parent = 0;
		}

		// get category params
		$this->params = $this->category->getParams();

		// set toolbar items
		$text = $edit ? Text::_('Edit') : Text::_('New');
		$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Category').': '.$this->category->name.' <small><small>[ '.$text.' ]</small></small>');
		$this->zoo->toolbar->apply();
		$this->zoo->toolbar->save();
		$this->zoo->toolbar->save2new();
		$this->zoo->toolbar->cancel('cancel', $edit ? 'Close' : 'Cancel');
		$this->zoo->zoo->toolbarHelp();

		// select published state
		$this->lists['select_published'] = $this->zoo->html->_('select.booleanlist', 'published', 'class="inputbox"', $this->category->published);

		// get categories and exclude the current category
		$categories = $this->application->getCategories();
		unset($categories[$this->category->id]);

		// build category tree
		$list = $this->zoo->tree->buildList(0, $this->zoo->tree->build($categories, 'Category'));

		$options = array($this->zoo->html->_('select.option', '0', Text::_('Root')));
		foreach ($list as $item) {
			$options[] = $this->zoo->html->_('select.option', $item->id, '&nbsp;&nbsp;&nbsp;'.$item->treename);
		}

		// select parent category
		$this->lists['select_parent'] = $this->zoo->html->_('zoo.genericlist', $options, 'parent', 'class="inputbox" size="10" data-no_results_text="'.Text::_('No results match').'"', 'value', 'text', $this->category->parent);

		// display view
		$this->getView()->setLayout('edit')->display();
	}

	public function save() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$post = $this->zoo->request->get('post:', 'array');
		$cid  = $this->zoo->request->get('cid.0', 'int');

		// set application
		$post['application_id'] = $this->application->id;

		// get raw description from post data
		$post['description'] = $this->zoo->request->getVar('description', '', 'post', 'string', 'raw');

		try {

			// get category and bind post data
			$category = ($cid) ? $this->table->get($cid) : $this->zoo->object->create('Category');
			self::bind($category, $post, array('params'));

			// Force alias to be set
			if (!strlen(trim($category->alias))) {
				$category->alias = $this->zoo->string->sluggify($category->name);
			}

			$category->alias = $this->zoo->alias->category->getUniqueAlias($category->id, $this->zoo->string->sluggify($category->alias));
			$category->getParams()
				->remove('content.')
				->remove('config.')
				->remove('template.')
				->remove('metadata.')
				->set('content.', @$post['params']['content'])
				->set('config.', @$post['params']['config'])
				->set('template.', @$post['params']['template'])
				->set('metadata.', @$post['params']['metadata']);

			// save category and update category ordering
			$this->table->save($category);
			$this->table->updateorder($this->application->id, $category->parent);

			// set redirect message
			$msg = Text::_('Category Saved');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Saving Category').' ('.$e.')');
			$this->_task = 'apply';
			$msg = null;

		}

		$link = $this->baseurl;
		switch ($this->getTask()) {
			case 'apply' :
				$link .= '&task=edit&cid[]='.$category->id;
				break;
			case 'save2new' :
				$link .= '&task=edit&cid[]=';
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
			$this->zoo->error->raiseError(500, Text::_('Select a Category to delete'));
		}

		try {

			// delete categories
			$parents = array();

			foreach ($cid as $id) {
				$category  = $this->table->get($id);
				$parents[] = $category->parent;
				$this->table->delete($category);
			}

			// update category ordering
			$this->table->updateorder($this->application->id, $parents);

			// set redirect message
			$msg = Text::_('Category Deleted');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Deleting Category').' ('.$e.')');
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
			$this->zoo->error->raiseError(500, Text::_('Select a category to copy'));
		}

		try {

			// copy categories
			$parents = array();
			foreach ($cid as $id) {

				// get category
				$category = $this->table->get($id);

				// copy category
				$category->id         = 0;                         // set id to 0, to force new category
				$category->name      .= ' ('.Text::_('Copy').')'; // set copied name
				$category->alias      = $this->zoo->alias->category->getUniqueAlias($id, $category->alias.'-copy'); // set copied alias
				$category->published  = 0;                         // unpublish category

				// track parent for ordering update
				$parents[] = $category->parent;

				// save copied category data
				$this->table->save($category);
			}

			// update category ordering
			$this->table->updateorder($this->application->id, $parents);

			// set redirect message
			$msg = Text::_('Category Copied');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Copying Category').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	public function saveorder() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// group categories by parent
		$category_ordering = array();
		foreach ($this->zoo->request->get('category', 'array', array()) as $id => $parent) {
			$category_ordering[$parent][] = $id;
		}

		try {

			// get categories
			$categories = $this->table->getAll($this->application->id);

			// update category parent & ordering
			foreach ($category_ordering as $parent => $cat_ids) {
				$parent = $parent == 'root' ? 0 : $parent;
				foreach ($cat_ids as $ordering => $id) {
					// only update, if changed
					if (isset($categories[$id]) && ($categories[$id]->parent != $parent || $categories[$id]->ordering != $ordering)) {
						$categories[$id]->parent = $parent;
						$categories[$id]->ordering = $ordering;
						$this->table->save($categories[$id]);
					}
				}
			}

			// set redirect message
			$msg = json_encode(array(
				'group' => 'info',
				'title' => Text::_('Success!'),
				'text'  => Text::_('New ordering saved')));

		} catch (AppException $e) {

			// raise error on exception
			$msg = json_encode(array(
				'group' => 'error',
				'title' => Text::_('Error!'),
				'text'  => Text::_('Error Reordering Category').' ('.$e.')'));

		}

		echo $msg;
	}

	public function resetorder() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		try {

			// get categories
			$categories = $this->application->getCategoryTree();
			// track parents
			$parents = array();

			foreach ($categories as $category) {
				if ($category->hasChildren()) {
					$parents[] = $category->id;
				}
			}

			// Reorder
			$this->table->updateorder($this->application->id, $parents, 'name');

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error Reordering Categories').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

	public function publish() {
		$this->_editPublished(1, Text::_('Select a category to publish'));
	}

	public function unpublish() {
		$this->_editPublished(0, Text::_('Select a category to unpublish'));
	}

	public function _editPublished($published, $msg) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, $msg);
		}

		try {

			// update published state
			foreach ($cid as $id) {
				$this->table->get($id)->setPublished($published, true);
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error editing Category Published State').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl);
	}

}

/*
	Class: CategorAppControllerException
*/
class CategorAppControllerException extends AppException {}
