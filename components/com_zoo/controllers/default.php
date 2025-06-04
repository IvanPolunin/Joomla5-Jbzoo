<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: DefaultController
		Site controller class
*/

use Joomla\CMS\Document\Feed\FeedItem;
use Joomla\CMS\Document\HtmlDocument;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;

class DefaultController extends AppController {

    /**
     * @var Application
     */
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

		// get application
		$this->application = $this->zoo->zoo->getApplication();

		// get Joomla application
		$this->joomla = $this->zoo->system->application;

		// get params
		$this->params = $this->joomla->getParams();

		// get pathway
		$this->pathway = $this->joomla->getPathway();

		// registers tasks
		$this->registerTask('frontpage', 'category');
	}

	/*
	 	Function: display
			View method for MVC based architecture

		Returns:
			Void
	*/
	public function display($cachable = false, $urlparams = false) {

		// execute task
		$this->taskMap['display'] = null;
		$this->taskMap['__default'] = null;
		$this->execute($this->zoo->request->getCmd('view'));
	}

	/*
	 	Function: callElement
			Element callback method

		Returns:
			Void
	*/
	public function callElement() {

		// get request vars
		$element = $this->zoo->request->getCmd('element', '');
		$method  = $this->zoo->request->getCmd('method', '');
		$args    = $this->zoo->request->getVar('args', array(), 'default', 'array');
		$item_id = (int) $this->zoo->request->getInt('item_id', 0);

		// get user
		$user = $this->zoo->user->get();

		// get item
		$item = $this->zoo->table->item->get($item_id);

		// raise 404 if item does not exist or is not published
		if (empty($item) || !$item->isPublished()) {
			return $this->zoo->error->raiseError(404, Text::_('Item not found'));
		}

		// raise warning when item can not be accessed
		if (!$item->canAccess($user)) {
			return $this->zoo->error->raiseError(403, Text::_('Unable to access item'));
		}

		// get element and execute callback method
		if ($element = $item->getElement($element)) {
			$element->callback($method, $args);
		}
	}

	public function item() {

		// get request vars
		$item_id = (int) $this->zoo->request->getInt('item_id', $this->params->get('item_id', 0));

		// get item
		$this->item = $this->zoo->table->item->get($item_id);

		// get user
		$user = $this->zoo->user->get();

		// raise 404 if item does not exist or is not published
		if (empty($this->item) || !$this->item->isPublished()) {
			return $this->zoo->error->raiseError(404, Text::_('Item not found'));
		}

		// raise 403 if user is not allowed to view item
		if (!$this->item->canAccess($user)) {

			// Show error message if logged in and cannot access item
			if ($user->id) {
				return $this->zoo->error->raiseWarning(403, Text::_('Unable to access item'));
			}

			// redirect to login for guest users
			$return = urlencode(base64_encode($this->zoo->route->item($this->item, false)));
			$link   = Route::_(sprintf('index.php?option=com_users&view=login&return=%s', $return), false);

			$this->setRedirect($link, Text::_('Unable to access item'), 'error');
			return $this->redirect();
		}

		// add canonical
		if ($this->zoo->system->document instanceof HtmlDocument) {
			$this->zoo->system->document->addHeadLink(Route::_($this->zoo->route->item($this->item, false), true, 0, true), 'canonical');
			$headData = $this->zoo->system->document->getHeadData();
				foreach ($headData['links'] as $key => $value) {
					if ($value['relation'] == 'canonical' && $key != Route::_($this->zoo->route->item($this->item, false), true, 0, true)) {
						unset($headData['links'][$key]);
				}
			}
			$this->zoo->system->document->setHeadData($headData);
		}

		// get category_id
		$category_id = (int) $this->zoo->request->getInt('category_id', $this->item->getPrimaryCategoryId());

		// create item pathway
		$itemid = $this->params->get('item_id');
		if ($this->item->id != $itemid) {
			$categories = $this->application->getCategoryTree(true);
			if (isset($categories[$category_id])) {
				$category = $categories[$category_id];
				$addpath = false;
				$catid   = $this->params->get('category');
				foreach ($category->getPathway() as $cat) {
					if (!$catid || $addpath) {
						$link = Route::_($this->zoo->route->category($cat));
						$this->pathway->addItem($cat->name, $link);
					}
					if ($catid && $catid == $cat->id) {
						$addpath = true;
					}
				}
			}

			$this->pathway->addItem($this->item->name, $this->zoo->route->item($this->item));
		}

		// update hit count
		$this->item->hit();

        // get page title, if exists
		$title = $this->item->getParams()->get('metadata.title');
		$title = empty($title) ? $this->item->name : $title;
		if ($menu = $this->zoo->menu->getActive() and @$menu->query['view'] == 'item' and $this->zoo->parameter->create($menu->getParams())->get('item_id') == $itemid) {
			if ($page_title = $this->zoo->parameter->create($menu->getParams())->get('page_title')) {
				$title = $page_title;
			}
		}

	 	// set metadata
		$this->zoo->document->setTitle($this->zoo->zoo->buildPageTitle($title));
		$system_params = $this->zoo->parameter->create($this->zoo->system->application->getParams());
		if ($this->zoo->system->config->get('MetaAuthor')) $this->zoo->document->setMetadata('author', $this->item->getAuthor());
		if ($description = $this->item->getParams()->get('metadata.description')) $this->zoo->document->setDescription($description);
		foreach (array('keywords', 'author', 'robots') as $meta) {
			if ($value = $this->item->getParams()->get("metadata.$meta") or $value = $system_params->get($meta)){
				$this->zoo->document->setMetadata($meta, $value);
			}
		}

		$this->params   = $this->item->getParams('site');
        $this->template = $this->application->getTemplate();
		$this->renderer = $this->zoo->renderer->create('item')->addPath($this->zoo->path->path('component.site:'));

        $view = $this->getView('item')->setLayout('item');

        // Add template paths
        if ($this->template) {
            $this->renderer->addPath($this->template->getPath());
        }

        $view->display();
	}

    public function submission() {

        // perform the request task
		$this->request->set('task', $this->request->get('layout', ''));
		$this->zoo->dispatch('submission');

    }

	public function category() {

		// get request vars
		$page        = (int) $this->zoo->request->getInt('page', 1);
		$category_id = (int) $this->zoo->request->getInt('category_id', $this->params->get('category'));

		// init vars
		$this->categories = $this->application->getCategoryTree(true, $this->zoo->user->get(), true);

		// raise 404 if category does not exist
		if ($category_id && !$this->zoo->table->category->get($category_id)) {
			return $this->zoo->error->raiseError(404, Text::_('Category not found'));
		}

		// raise warning when category can not be accessed
		if (!isset($this->categories[$category_id])) {
			return $this->zoo->error->raiseError(403, Text::_('Unable to access category'));
		}

		$this->category   = $this->categories[$category_id];
		$params	          = $category_id ? $this->category->getParams('site') : $this->application->getParams('frontpage');
		$this->item_order = $params->get('config.item_order');
		$ignore_priority  = $params->get('config.ignore_item_priority', false);
		$layout 		  = $category_id == 0 ? 'frontpage' : 'category';
		$items_per_page   = $params->get('config.items_per_page') ?: 15;
		$subcategories    = $category_id && $params->get('config.include_subcategories');
		$offset			  = max(($page - 1) * $items_per_page, 0);

		// get categories and items
        $ids              = $subcategories ? array_merge([$category_id], array_keys($this->category->getChildren(true))) : $category_id;
		$this->items      = $this->zoo->table->item->getByCategory($this->application->id, $ids, true, null, $this->item_order, $offset, $items_per_page, $ignore_priority);
		$item_count		  = $this->category->id == 0
            ? $this->zoo->table->item->getItemCountFromCategory($this->application->id, $category_id, true)
            : ($subcategories
                ? $this->category->totalItemCount()
                : $this->category->itemCount()
            );

		// set categories to display
		$this->selected_categories = $this->category->getChildren();

		// get item pagination
		$this->pagination = $this->zoo->pagination->create($item_count, $page, $items_per_page, 'page', 'app');
		$this->pagination->setShowAll($items_per_page == 0);
		$this->pagination_link = $layout == 'category' ? $this->zoo->route->category($this->category, false) : $this->zoo->route->frontpage($this->application->id);

		// create pathway
		$addpath = false;
		$catid   = $this->params->get('category');
		foreach ($this->category->getPathway() as $cat) {
			if (!$catid || $addpath) {
				$this->pathway->addItem($cat->name, $this->zoo->route->category($cat));
			}
			if ($catid && $catid == $cat->id) {
				$addpath = true;
			}
		}

		// get metadata
		$title		 = $params->get('metadata.title') ? $params->get('metadata.title') : ($category_id ? $this->category->name : '');
		$description = $params->get('metadata.description');
		$keywords    = $params->get('metadata.keywords');

		if ($menu = $this->zoo->menu->getActive() and in_array(@$menu->query['view'], array('category', 'frontpage')) and $menu_params = $this->zoo->parameter->create($menu->getParams()) and $menu_params->get('category') == $category_id) {

			if ($page_title = $menu_params->get('page_title') or $page_title = $menu->title) {
				$title = $page_title;
			}

			if ($page_description = $menu_params->get('menu-meta_description')) {
				$description = $page_description;
			}

			if ($page_keywords = $menu_params->get('menu-meta_keywords')) {
				$keywords = $page_keywords;
			}

		}

		// set page title
		if ($title) {
			$this->zoo->document->setTitle($this->zoo->zoo->buildPageTitle($title));
		}

		if ($description) {
			$this->zoo->document->setDescription($description);
		}

		if ($keywords) {
			$this->zoo->document->setMetadata('keywords', $keywords);
		}

		// set metadata
		$system_params = $this->zoo->parameter->create($this->zoo->system->application->getParams());
		foreach (array('author', 'robots') as $meta) {
			if ($value = $params->get("metadata.$meta") or $value = $system_params->get($meta)){
				$this->zoo->document->setMetadata($meta, $value);
			}
		}

		// add feed links
		if ($params->get('config.show_feed_link') && $this->zoo->system->document instanceof HtmlDocument) {
			if ($alternate = $params->get('config.alternate_feed_link')) {
				$this->zoo->document->addHeadLink($alternate, 'alternate', 'rel', array('type' => 'application/rss+xml', 'title' => 'RSS 2.0'));
			} else {
				$this->zoo->document->addHeadLink(Route::_($this->zoo->route->feed($this->category, 'rss')), 'alternate', 'rel', array('type' => 'application/rss+xml', 'title' => 'RSS 2.0'));
				$this->zoo->document->addHeadLink(Route::_($this->zoo->route->feed($this->category, 'atom')), 'alternate', 'rel', array('type' => 'application/atom+xml', 'title' => 'Atom 1.0'));
			}
		}

		// set alphaindex
		if ($params->get('template.show_alpha_index')) {
			$this->alpha_index = $this->_getAlphaindex();
		}

		$this->params   = $params;
        $this->template = $this->application->getTemplate();
		$this->renderer = $this->zoo->renderer->create('item')->addPath($this->zoo->path->path('component.site:'));

		$view = $this->getView($layout)->setLayout($layout);

		// Add template paths
		if ($this->template) {
		    $this->renderer->addPath($this->template->getPath());
        }

        $view->display();
	}

	public function alphaindex() {

		// get request vars
		$page             = $this->zoo->request->getInt('page', 1);
		$this->alpha_char = $this->zoo->request->getString('alpha_char', '');

		// get params
		$params 	      = $this->application->getParams('site');
		$items_per_page   = $params->get('config.items_per_page', 15);
		$ignore_priority  = $params->get('config.ignore_item_priority', false);
		$this->item_order = $params->get('config.item_order');
		$add_alpha_index  = $params->get('config.alpha_index', 0);

		// get categories
		$this->categories = $add_alpha_index == 1 || $add_alpha_index == 3 ? $this->application->getCategoryTree(true, $this->zoo->user->get(), true) : array();

		// set alphaindex
		$this->alpha_index = $this->_getAlphaindex();
		$this->alpha_char = empty($this->alpha_char) ? $this->alpha_index->getOther() : $this->alpha_index->getChar($this->alpha_char);

		// get items
		$this->items = array();
		if ($add_alpha_index == 2 || $add_alpha_index == 3) {
			$table = $this->zoo->table->item;
			if ($this->alpha_char == $this->alpha_index->getOther()) {
				$this->items = $table->getByCharacter($this->application->id, $this->alpha_index->getIndex(), true, true, null, $this->item_order, 0, 0, $ignore_priority);
			} else {
				$this->items = $table->getByCharacter($this->application->id, $this->alpha_char, false, true, null, $this->item_order, 0, 0, $ignore_priority);
			}
		}

		// get item pagination
		$this->pagination = $this->zoo->pagination->create(count($this->items), $page, $items_per_page, 'page', 'app');
		$this->pagination->setShowAll($items_per_page == 0);
		$this->pagination_link = $this->zoo->route->alphaindex($this->application->id, $this->alpha_char);

		// slice out items
		if (!$this->pagination->getShowAll()) {
			$this->items = array_slice($this->items, $this->pagination->limitStart(), $items_per_page);
		}

		// set category and categories to display
		if (isset($this->categories[0])) {
			$this->category = $this->categories[0];
		}
		$this->selected_categories = $this->alpha_index->getObjects($this->alpha_char, 'category');

		// create pathway
		$this->pathway->addItem(Text::_('Alpha Index'), Route::_($this->zoo->route->alphaindex($this->application->id, $this->alpha_char)));

		$this->params   = $params;
        $this->template = $this->application->getTemplate();
		$this->renderer = $this->zoo->renderer->create('item')->addPath($this->zoo->path->path('component.site:'));

		$view = $this->getView('alphaindex')->setLayout('alphaindex');

        // Add template paths
        if ($this->template) {
            $this->renderer->addPath($this->template->getPath());
        }

        $view->display();
	}

	public function tag() {

		// get request vars
		$page      = (int) $this->zoo->request->getInt('page', 1);
		$this->tag = $this->zoo->request->getString('tag', '');

		// raise 404 if tag does not exist
		if (!$this->zoo->table->tag->getAll($this->application->id, $this->tag)) {
			return $this->zoo->error->raiseError(404, Text::_('Tag not found'));
		}

		// get params
		$params 	 	  = $this->application->getParams('site');
		$items_per_page   = $params->get('config.items_per_page', 15);
		$this->item_order = $params->get('config.item_order');
		$ignore_priority  = $params->get('config.ignore_item_priority', false);

		// get categories and items
		$this->categories = $this->application->getCategoryTree(true);
		$this->items = $this->zoo->table->item->getByTag($this->application->id, $this->tag, true, null, $this->item_order, 0, 0, $ignore_priority);

		// get item pagination
		$this->pagination = $this->zoo->pagination->create(count($this->items) , $page, $items_per_page, 'page', 'app');
		$this->pagination->setShowAll($items_per_page == 0);
		$this->pagination_link = $this->zoo->route->tag($this->application->id, $this->tag);

		// slice out items
		if (!$this->pagination->getShowAll()) {
			$this->items = array_slice($this->items, $this->pagination->limitStart(), $items_per_page);
		}

		// set alphaindex
		if ($params->get('template.show_alpha_index')) {
			$this->alpha_index = $this->_getAlphaindex();
		}

	 	// set metadata
		$this->zoo->document->setTitle($this->zoo->zoo->buildPageTitle($this->tag));

		// create pathway
		$this->pathway->addItem(Text::_('Tags').': '.$this->tag, Route::_($this->zoo->route->tag($this->application->id, $this->tag)));

		$this->params   = $params;
        $this->template = $this->application->getTemplate();
		$this->renderer = $this->zoo->renderer->create('item')->addPath($this->zoo->path->path('component.site:'));

        $view = $this->getView('tag')->setLayout('tag');

        // Add template paths
        if ($this->template) {
            $this->renderer->addPath($this->template->getPath());
        }

        $view->display();
	}

	public function feed() {

		// get request vars
		$category_id = (int) $this->zoo->request->getInt('category_id', $this->params->get('category'));

		// get params
		$all_categories	= $this->application->getCategoryTree(true);

		// raise warning when category can not be accessed
		if (!isset($all_categories[$category_id])) {
			return $this->zoo->error->raiseWarning(404, Text::_('Unable to access category'));
		}

		$category 		= $all_categories[$category_id];
		$params 	 	= $category_id ? $category->getParams('site') : $this->application->getParams('frontpage');
		$show_feed_link = $params->get('config.show_feed_link', 0);
		$feed_title     = $params->get('config.feed_title', '');

		// raise error when feed is disabled
		if (empty($show_feed_link)) {
			return $this->zoo->error->raiseError(404, Text::_('Unable to access feed'));
		}

		// get feed items from category
		if ($category_id) {
			$categories = $category->getChildren(true);
		}
		$categories[$category->id] = $category;

		$feed_limit = $this->zoo->system->config->get('feed_limit');

		$feed_items = $this->zoo->table->item->getByCategory($this->application->id, array_keys($categories), true, null, array('_itempublish_up', '_reversed'), 0, $feed_limit, true);

		// set title
		if ($feed_title) {
			$this->zoo->system->document->setTitle($this->zoo->zoo->buildPageTitle(html_entity_decode($this->getView()->escape($feed_title))));
		}

		// set feed link
		$this->zoo->system->document->setLink(Route::_($category_id ? $this->zoo->route->category($category) : $this->zoo->route->frontpage($this->application->id)));

        // set feed description
        $this->zoo->system->document->setDescription(html_entity_decode($this->getView()->escape($this->zoo->system->document->getDescription())));

		// set renderer
		$renderer = $this->zoo->renderer->create('item')->addPath(array($this->zoo->path->path('component.site:'), $this->application->getTemplate()->getPath()));

		foreach ($feed_items as $feed_item) {

			// create feed item
			$item         	   = new FeedItem();
			$item->title  	   = html_entity_decode($this->getView()->escape($feed_item->name));
			$item->link   	   = $this->zoo->route->item($feed_item);
			$item->date 	   = $feed_item->publish_up;
			$item->author	   = $feed_item->getAuthor();
			$item->description = $this->_relToAbs($renderer->render('item.feed', array('item' => $feed_item)));

			// add to feed document
			$this->zoo->system->document->addItem($item);
		}

	}

	protected function _getAlphaindex() {
        return $this->zoo->application->getAlphaIndex($this->application);
	}

	protected function _relToAbs($text)	{

		// convert relative to absolute url
		$base = Uri::base();
		$text = preg_replace("/(href|src)=\"(?!http|ftp|https|mailto)(?!\/)([^\"]*)\"/", "$1=\"$base\$2\"", $text);
		$base = Uri::getInstance()->toString(array('scheme', 'user', 'pass', 'host', 'port'));
		$text = preg_replace("/(href|src)=\"(?!http|ftp|https|mailto)([^\"]*)\"/", "$1=\"$base\$2\"", $text);
		return $text;
	}

	// @deprecated as of 2.5.7
	protected function _buildPageTitle($title) {
		return $this->zoo->zoo->buildPageTitle($title);
	}

    protected function createView($name, $prefix = '', $type = '', $config = array()) {
        $view = parent::createView($name, $prefix, $type, $config);
        $template = $this->application->getTemplate();

        if ($template) {
            $view->addTemplatePath($template->getPath());
        }

        return $view;
    }
}

/*
	Class: DefaultControllerException
*/
class DefaultControllerException extends AppException {}
