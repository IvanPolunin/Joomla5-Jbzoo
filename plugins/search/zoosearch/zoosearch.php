<?php
/**
 * @package   Search - ZOO
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Plugin\PluginHelper;

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgSearchZoosearch extends CmsPlugin {

	/* menu item mapping */
	public $menu;

    /**
     * @var App
     */
	public $zoo;

	/*
		Function: plgSearchZoosearch
		  Constructor.

		Parameters:
	      $subject - Array
	      $config - Array

	   Returns:
	      Void
	*/
	public function __construct($subject, $config) {

		// make sure ZOO exists
		if (!ComponentHelper::getComponent('com_zoo', true)->enabled) {
			return;
		}

		parent::__construct($subject, $config);

		// load config
        if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php')) {
            return;
        }
        require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');
        if (!ComponentHelper::getComponent('com_zoo', true)->enabled) {
            return;
        }

		$this->zoo = App::getInstance('zoo');
	}

	/*
		Function: onSearchAreas
		  Get search areas.

	   Returns:
	      Array - Search areas
	*/
	public function onSearchAreas() {
		static $areas = array();
		return $areas;
	}

	/*
		Function: onSearch
		  Get search results. The sql must return the following fields that are used in a common display routine: href, title, section, created, text, browsernav

		Parameters:
	      $text - Target search string
	      $phrase - Matching option, exact|any|all
	      $ordering - Ordering option, newest|oldest|popular|alpha|category
	      $areas - An array if the search it to be restricted to areas, null if search all

	   Returns:
	      Array - Search results
	*/
	public function onContentSearch($text, $phrase = '', $ordering = '', $areas = null) {

        if (!$this->zoo) {
            return [];
        }

		$db	  = $this->zoo->database;

		// init vars
		$now  = $db->Quote($this->zoo->date->create()->toSQL());
		$null = $db->Quote($db->getNullDate());
		$text = trim($text);

		// return empty array, if no search text provided
		if (empty($text)) {
			return array();
		}

		// get plugin info
	 	$plugin   = PluginHelper::getPlugin('search', 'zoosearch');
	 	$params   = $this->zoo->parameter->create($plugin->params);
		$fulltext = $params->get('search_fulltext', 0) && strlen($text) > 3 && intval($db->getVersion()) >= 4;
		$limit    = $params->get('search_limit', 50);

        $elements = array();
        foreach ($this->zoo->application->groups() as $application) {
            foreach($application->getTypes() as $type) {
                foreach ($type->getElements() as $element) {
                    if (!$element->canAccess()) {
                        $elements[] = $db->Quote($element->identifier);
                    }
                }
            }
        }

        $access = $elements ? 'NOT element_id in ('.implode(',', $elements).')' : '1';

		// prepare search query
		switch ($phrase) {
			case 'exact':

				if ($fulltext) {
					$text    = $db->escape($text);
					$where[] = "MATCH(a.name) AGAINST ('\"{$text}\"' IN BOOLEAN MODE)";
					$where[] = "MATCH(b.value) AGAINST ('\"{$text}\"' IN BOOLEAN MODE) AND $access";
					$where[] = "MATCH(c.name) AGAINST ('\"{$text}\"' IN BOOLEAN MODE)";
					$where   = implode(" OR ", $where);
				} else {
					$text	= $db->Quote('%'.$db->escape($text, true).'%', false);
					$like   = array();
					$like[] = 'a.name LIKE '.$text;
					$like[] = "b.value LIKE $text AND $access";
					$like[] = 'c.name LIKE '.$text;
					$where 	= '(' .implode(') OR (', $like).')';
				}

				break;

			case 'all':
			case 'any':
			default:

				if ($fulltext) {
					$text    = $db->escape($text);
					$where[] = "MATCH(a.name) AGAINST ('{$text}' IN BOOLEAN MODE)";
					$where[] = "MATCH(b.value) AGAINST ('{$text}' IN BOOLEAN MODE) AND $access";
					$where[] = "MATCH(c.name) AGAINST ('{$text}' IN BOOLEAN MODE)";
					$where   = implode(" OR ", $where);
				} else {
					$words 	= explode(' ', $text);
					$wheres = array();

					foreach ($words as $word) {
						$word     = $db->Quote('%'.$db->escape($word, true).'%', false);
						$like     = array();
						$like[]   = 'a.name LIKE '.$word;
						$like[]   = 'EXISTS (SELECT value FROM '.ZOO_TABLE_SEARCH.' WHERE a.id = item_id AND value LIKE '.$word.' AND '.$access.')';
						$like[]   = 'EXISTS (SELECT name FROM '.ZOO_TABLE_TAG.' WHERE a.id = item_id AND name LIKE '.$word.')';
						$wheres[] = implode(' OR ', $like);
					}

					$where = '('.implode(($phrase == 'all' ? ') AND (' : ') OR ('), $wheres).')';
				}
		}

		// set search ordering
		switch ($ordering) {
			case 'newest':
				$order = 'a.created DESC';
				break;

			case 'oldest':
				$order = 'a.created ASC';
				break;

			case 'popular':
				$order = 'a.hits DESC';
				break;

			case 'alpha':
			case 'category':
			default:
				$order = 'a.name ASC';
		}

		// set query options
		$select     = "DISTINCT a.*";
        $from       = ZOO_TABLE_ITEM." AS a"
			         ." LEFT JOIN ".ZOO_TABLE_SEARCH." AS b ON a.id = b.item_id"
		             ." LEFT JOIN ".ZOO_TABLE_TAG." AS c ON a.id = c.item_id";
		$conditions = array("(".$where.")"
                     ." AND a.searchable = 1"
                     ." AND a." . $this->zoo->user->getDBAccessString()
                     ." AND (a.state = 1"
		             ." AND (a.publish_up = ".$null." OR a.publish_up <= ".$now.")"
		             ." AND (a.publish_down = ".$null." OR a.publish_down >= ".$now."))");

		// execute query
		$items = $this->zoo->table->item->all(compact('select', 'from', 'conditions', 'order', 'limit'));

		// create search result rows
		$rows = array();
		if (!empty($items)) {

			// set renderer
			$renderer = $this->zoo->renderer->create('item')->addPath(array($this->zoo->path->path('component.site:'), $this->zoo->path->path('plugins:search/zoosearch/')));

			foreach ($items as $item) {
				$row = new stdClass();
				$row->title = $item->name;
				$row->text = $renderer->render('item.default', array('item' => $item));
				$row->href = $this->zoo->route->item($item);
				$row->created = $item->created;
				$row->section = '';
				$row->browsernav = 2;
				$rows[] = $row;
			}
		}

		return $rows;
	}

	public function registerZOOEvents() {
		if ($this->zoo) {
			$this->zoo->event->dispatcher->connect('type:assignelements', array($this, 'assignElements'));
		}
	}

	public function assignElements() {
		$this->zoo->system->application->enqueueMessage(Text::_('Only text based elements are allowed in the search layouts'), 'notice');
	}

}
