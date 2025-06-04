<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: CommentController
		The controller class for comments
*/

use Joomla\CMS\Language\Text;

class CommentController extends AppController {

	public $application;

	public function __construct($default = array()) {
		parent::__construct($default);

		// set table
		$this->table = $this->zoo->table->comment;

		// get application
		$this->application 	= $this->zoo->zoo->getApplication();

		// check ACL
		if (!$this->application->canManageComments()) {
			throw new CommentControllerException("Invalid Access Permissions!", 1);
		}

		// set base url
		$this->baseurl = $this->zoo->link(array('controller' => $this->controller), false);

		// registers tasks
		$this->registerTask('apply', 'save');
		$this->registerTask('save2new', 'save' );
		$this->registerTask('add', 'edit');
	}

	public function display($cachable = false, $urlparams = false) {

		// get request vars
		$state_prefix  = $this->option.'_'.$this->application->id.'.comment.';
		$limit		   = $this->zoo->system->application->getUserStateFromRequest('global.list.limit', 'limit', $this->zoo->system->config->get('list_limit'), 'int');
		$limitstart	   = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'limitstart', 'limitstart', 0, 'int');
		$filter_state  = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter-state', 'filter-state', '', 'string');
		$filter_item   = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter-item', 'filter-item', 0, 'int');
		$filter_author = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'filter-author', 'filter-author', '', 'string');
		$search	       = $this->zoo->system->application->getUserStateFromRequest($state_prefix.'search', 'search', '', 'string');
		$search		   = $this->zoo->string->strtolower($search);

		// is filtered ?
		$this->is_filtered = $filter_state <> '' || !empty($filter_item) || !empty($filter_author) || !empty($search);

		// in case limit has been changed, adjust offset accordingly
		$limitstart = $limit != 0 ? (floor($limitstart / $limit) * $limit) : 0;

		// set toolbar items
		if ($filter_item && $item_object = $this->zoo->table->item->get($filter_item)) {
			$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Comments on') . ': ' . $item_object->name);
		} else {
			$this->zoo->system->application->JComponentTitle = $this->application->getToolbarTitle(Text::_('Comments'));
		}
		$this->zoo->toolbar->custom('approve', 'publish', '', 'Approve');
		$this->zoo->toolbar->custom('unapprove', 'unpublish', '', 'Unapprove');
		$this->zoo->toolbar->custom('spam', 'trash', '', 'Spam');
		$this->zoo->toolbar->deleteList();

		// build where condition
		$where = array('b.application_id = '.(int) $this->application->id);

		if ($filter_state === '') {
			$where[] = 'a.state <> 2'; // all except spam
		} else {
			$where[] = 'a.state = '.(int) $filter_state;
		}

		if ($filter_item) {
			$where[] = 'a.item_id = '.(int) $filter_item;
		}

		if ($filter_author == '_anonymous_') {
			$where[] = 'a.author = ""';
		} elseif ($filter_author) {
			$where[] = 'a.author = "'.$this->zoo->database->escape($filter_author).'"';
		}

		if ($search) {
			$where[] = 'LOWER(a.content) LIKE "%'.$this->zoo->database->escape($search, true).'%"';
		}

		// build query options
		$options = array(
			'select'     => 'a.*',
			'from'       => ZOO_TABLE_COMMENT.' AS a LEFT JOIN '.ZOO_TABLE_ITEM.' AS b ON a.item_id = b.id',
			'conditions' => array(implode(' AND ', $where)),
			'order'      => 'a.created DESC');

		// query comment table
		$count = $this->table->count($options);
		$limitstart = $limitstart > $count ? floor($count / $limit) * $limit : $limitstart;
		$this->comments = $this->table->all($limit > 0 ? array_merge($options, array('offset' => $limitstart, 'limit' => $limit)) : $options);
		$this->pagination = $this->zoo->pagination->create($count, $limitstart, $limit);

		// search filter
		$this->lists['search'] = $search;

		// state select
		$options = array(
			$this->zoo->html->_('select.option', '', '- '.Text::_('Select Status').' -'),
			$this->zoo->html->_('select.option', '0', Text::_('Pending')),
			$this->zoo->html->_('select.option', '1', Text::_('Approved')),
			$this->zoo->html->_('select.option', '2', Text::_('Spam')));
		$this->lists['select_state'] = $this->zoo->html->_('select.genericlist', $options, 'filter-state', 'class="inputbox auto-submit"', 'value', 'text', $filter_state);

		// item select
		$options = array($this->zoo->html->_('select.option', 0, '- '.Text::_('Select Item').' -'));
		$this->lists['select_item'] = $this->zoo->html->_('zoo.itemlist', $this->application, $options, 'filter-item', 'class="inputbox auto-submit"', 'value', 'text', $filter_item);

		// author select
		$options = array(
			$this->zoo->html->_('select.option', '', '- '.Text::_('Select Author').' -'),
			$this->zoo->html->_('select.option', '_anonymous_', '- '.Text::_('Anonymous').' -'));
		$this->lists['select_author'] = $this->zoo->html->_('zoo.commentauthorlist', $this->application, $options, 'filter-author', 'class="inputbox auto-submit"', 'value', 'text', $filter_author);

		// get comment params
		$this->params = $this->zoo->parameter->create($this->application->getParams()->get('global.comments.', array()));

		// display view
		$this->getView()->display();
	}

	public function edit() {

		// get request vars
		$cid = $this->zoo->request->getInt('cid');

		// get comment
		$this->comment = $this->table->get($cid);

		// display view
		$this->getView()->setLayout('_edit')->display();
	}

	public function reply() {

		// get request vars
		$this->cid = $this->zoo->request->getInt('cid');

		// display view
		$this->getView()->setLayout('_reply')->display();
	}

	public function save() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$user = $this->zoo->user->get();
		$post = $this->zoo->request->get('post:', 'array');
		$cid  = $this->zoo->request->get('cid', 'int');
		$pid  = $this->zoo->request->getInt('parent_id', 0);
		$now  = $this->zoo->date->create();

		try {

			// get content as raw and filter it
			$post['content'] = $this->zoo->request->getVar('content', null, '', 'string', 'raw');
			$post['content'] = $this->zoo->comment->filterContentInput($post['content']);

			// get comment or create reply
			if ($cid) {
				$comment = $this->table->get($cid);
			} else {
				$parent  = $this->table->get($pid);
				$comment = $this->zoo->object->create('Comment');
				$comment->item_id = $parent->getItem()->id;
				$comment->user_id = $user->id;
				$comment->author = $user->name;
				$comment->email = $user->email;
				$comment->ip = $this->zoo->useragent->ip();
				$comment->created = $now->toSQL();
				$comment->state = Comment::STATE_APPROVED;
			}

			// bind post data
			self::bind($comment, $post);

			// save comment
			$this->table->save($comment);

			// get view
			$view = $this->getView();

			// set view vars
			$view->set('option', $this->option);
			$view->comment = $comment;

			// display view
			$view->setLayout('_row')->display();

		} catch (AppException $e) {

			// raise error on exception
			echo json_encode(array(
				'group' => 'error',
				'title' => Text::_('Error Saving Comment'),
				'text'  => (string) $e));
		}

	}

	public function remove() {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
		$msg = null;
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a comment to delete'));
		}

		try {

			// delete comments
			foreach ($cid as $id) {
				$this->table->delete($this->table->get($id));
			}

			// set redirect message
			$msg = Text::_('Comment(s) Deleted');


		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseWarning(0, Text::_('Error Deleting Comment(s)').' ('.$e.')');
			$msg = null;

		}

		$this->setRedirect($this->baseurl, $msg);
	}

	/*
		Function: approve
			Approve a comment

		Returns:
			Void
	*/
	public function approve() {
		$this->_editState(1);
	}

	/*
		Function: unapprove
			Unapprove a comment

		Returns:
			Void
	*/
	public function unapprove() {
		$this->_editState(0);
	}

	/*
		Function: spam
			Mark comment as spam

		Returns:
			Void
	*/
	public function spam() {
		$this->_editState(2);
	}

	protected function _editState($state) {

		// check for request forgeries
		$this->zoo->session->checkToken() or jexit('Invalid Token');

		// init vars
        $cid = (array) $this->zoo->request->get('cid', 'int');

		if (count($cid) < 1) {
			$this->zoo->error->raiseError(500, Text::_('Select a comment to edit state'));
		}

		try {

			// update comment state
			foreach ($cid as $id) {
				$this->table->get($id)->setState($state, true);
			}

		} catch (AppException $e) {

			// raise notice on exception
			$this->zoo->error->raiseNotice(0, Text::_('Error editing Comment State').' ('.$e.')');

		}

		$this->setRedirect($this->baseurl);
	}

}

/*
	Class: CommentControllerException
*/
class CommentControllerException extends AppException {}
