<?php
/**
 * @package   Smart Search - ZOO
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\Component\Finder\Administrator\Indexer\Adapter;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Language\Text;
use Joomla\Component\Finder\Administrator\Indexer\Helper;
use Joomla\Component\Finder\Administrator\Indexer\Indexer;
use Joomla\Component\Finder\Administrator\Indexer\Result;
use Joomla\Database\QueryInterface;
use Joomla\Registry\Registry;

defined('JPATH_BASE') or die;

class plgFinderZOOSmartSearch extends Adapter {

	public $app;

	protected $context = 'ZOO';
	protected $extension = 'com_zoo';
	protected $layout = 'item';
	protected $type_title = 'ZOO Item';
	protected $table = '#__zoo_item';
	protected $state_field = 'state';
    protected $renderer;

	public function __construct(&$subject, $config)	{

		// load ZOO config
		if (!File::exists(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php') || !ComponentHelper::getComponent('com_zoo', true)->enabled || !function_exists('iconv')) {
			return;
		}
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');

		// Get the ZOO App instance
		$this->app = App::getInstance('zoo');

		parent::__construct($subject, $config);

		// load zoo frontend language file
		$this->app->system->language->load('com_zoo');

	}

	protected function index(Result $item) {

		// Check if the extension is enabled
		if (ComponentHelper::isEnabled($this->extension) == false || !$item->id) {
			return;
		}

		if (!$zoo_item = $this->app->table->item->get($item->id, true)) {
			return;
		}

        $item->context = 'com_zoo.item';

		$registry = new Registry;
		$registry->loadArray($zoo_item->getParams()->get("metadata.", []));
		$item->metadata = $registry;

		$item->metaauthor = $zoo_item->getParams()->get("metadata.author");

		$item->addInstruction(Indexer::META_CONTEXT, 'link');
		$item->addInstruction(Indexer::META_CONTEXT, 'metakey');
		$item->addInstruction(Indexer::META_CONTEXT, 'metadesc');
		$item->addInstruction(Indexer::META_CONTEXT, 'metaauthor');
		$item->addInstruction(Indexer::META_CONTEXT, 'author');
		$item->addInstruction(Indexer::META_CONTEXT, 'created_by_alias');
		$item->addInstruction(Indexer::META_CONTEXT, 'element_data');

		$item->summary = $this->renderer->render('item.default', array('item' => $zoo_item));
		$item->url = $this->getURL($item->id, $this->extension, $this->layout);
		$item->route = $this->app->route->item($zoo_item, false);
        if (version_compare(JVERSION, '4.0', '<')) {
            $item->path = Helper::getContentPath($item->route);
        }
		$item->state = ($zoo_item->searchable == 1) && ($zoo_item->state == 1);

		$item->element_data = $this->app->database->queryResultArray('SELECT value FROM '.ZOO_TABLE_SEARCH.' WHERE item_id = '.(int) $item->id);

		$item->addTaxonomy('Type', $zoo_item->getType()->name);

        // Add the author taxonomy data.
        if (!empty($item->author) || !empty($item->created_by_alias)) {
            $item->addTaxonomy('Author', !empty($item->created_by_alias) ? $item->created_by_alias : $item->author, $item->state);
        }

		foreach ($zoo_item->getRelatedCategories(true) as $category) {
			$item->addTaxonomy('Category', $category->name);
		}

		foreach ($zoo_item->getTags() as $tag) {
			$item->addTaxonomy('Tag', $tag);
		}

		Helper::getContentExtras($item);

		$this->indexer->index($item);

	}

	protected function setup() {

		$this->renderer = $this->app->renderer->create('item')->addPath(array($this->app->path->path('component.site:'), $this->app->path->path('plugins:finder/zoosmartsearch/')));

		return true;
	}

	protected function getListQuery($sql = null) {

		$db = Factory::getDbo();

		$sql = is_a($sql, 'JDatabaseQuery') || $sql instanceof QueryInterface ? $sql : $db->getQuery(true);
		$sql->select('a.id, a.name AS title, a.alias');
		$sql->select('a.created_by_alias, a.modified, a.modified_by, a.created_by');
		$sql->select('a.publish_up AS publish_start_date, a.publish_down AS publish_end_date');
		$sql->select('a.access, a.state, a.searchable');
        $sql->from("$this->table AS a");

        $sql->select('u.name AS author');
        $sql->join('LEFT', '#__users AS u ON u.id = a.created_by');

		return $sql;

	}

	protected function getStateQuery() {
		$sql = $this->db->getQuery(true);
		$sql->select('a.id, a.state, a.access, a.searchable');
		$sql->from($this->table . ' AS a');

		return $sql;
	}

	public function onFinderAfterSave($context, $row) {
		if ($context == $this->app->component->self->name.'.item') {
			$this->reindex($row->id);
		}

		return true;
	}

	public function onFinderAfterDelete($context, $table) {
		if ($context == $this->app->component->self->name.'.item') {
			$id = $table->id;
		} elseif ($context == 'com_finder.index') {
			$id = $table->link_id;
		} else {
			return true;
		}

		return $this->remove((int) $id);
	}

	public function registerZOOEvents() {
		if ($this->app) {
			$this->app->event->dispatcher->connect('type:assignelements', array($this, 'assignElements'));
		}
	}

	public function assignElements() {
		$this->app->system->application->enqueueMessage(Text::_('Only text based elements are allowed in the search layouts'), 'notice');
	}

}
