<?php
/**
 * @package   Content - ZOO Shortcode
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Router\Route;

// no direct access
defined('_JEXEC') or die('Restricted access');

class plgContentZooshortcode extends CmsPlugin {

	public $app;

	public function onPrepareContent(&$row, &$params, $page=0) {
		return $this->_prepareContent($row, $params, $page);
	}

	public function onContentPrepare($context, &$article, &$params, $page = 0) {
		return $this->_prepareContent($article, $params, $page);
	}

	protected function _prepareContent(&$article, &$params, $page = 0) {

		// simple performance check to determine whether text should be processed further
		if (strpos($article->text ?: '', 'zooitem') === false && strpos($article->text ?: '', 'zoocategory') === false && strpos($article->text ?: '', 'zoofrontpage') === false) {
			return true;
		}

		// load ZOO config
		if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php')) {
			return;
		}
		require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');
        if (!ComponentHelper::getComponent('com_zoo', true)->enabled) {
            return;
        }

		// Get the ZOO App instance
		$this->app = App::getInstance('zoo');

		$this->_doReplacement($article, 'item');
		$this->_doReplacement($article, 'category');
		$this->_doReplacement($article, 'frontpage');

		return true;

	}

	protected function _doReplacement(&$article, $name) {
		// expression to search for
		$regex   = '/{zoo'.$name.':\s*(\S*)(?:\s*text:\s*(.*?))?(?:\s*output:\s*(.*?))?}/i';
		$matches = array();

		// find all instances of plugin and put in $matches
		preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

		foreach ($matches as $match) {

			// $match[0] is full pattern match, $match[1] is the item id or alias
			$id = $match[1];
			$text = !empty($match[2]) ? $match[2] : '';
			$output = !empty($match[3]) ? $match[3] : 'link'; // url | link

			switch ($name) {
				case 'frontpage':

					if ($id && ($object = $this->app->table->application->get($id))) {
						$result = Route::_($this->app->route->frontpage($id));
					}
					break;

				default:

					// translate alias
					if (!is_numeric($id)) {
						$id = $this->app->alias->$name->translateAliasToID($id);
					}

					if ($id && ($object = $this->app->table->$name->get($id))) {
						$result = $this->app->route->$name($object);
					}
					break;
			}

			if (isset($object)) {

				// make sure text is set
				$text = $text ?: $object->name;

				if ($output == 'link') {
					$result = sprintf('<a title="%s" href="%s">%s</a>', $object->name, $result, $text);
				}

			} else {
				$result = '';
			}

			// We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
			$article->text = preg_replace("|$match[0]|", $result, $article->text, 1);

		}

		return true;

	}

}
