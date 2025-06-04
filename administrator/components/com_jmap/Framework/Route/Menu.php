<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Route;
/**
 *
* @package JMAP::FRAMEWORK::administrator::components::com_jmap
* @subpackage framework
* @subpackage route
* @author Joomla! Extensions Store
* @copyright (C) 2021 - Joomla! Extensions Store
* @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
*/
defined ( '_JEXEC' ) or die ();
use Joomla\CMS\Factory;
use Joomla\Registry\Registry;

/**
 * Helper to route menu links
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage route
 * @since 2.3
 */
class Menu {
	/**
	 * Array to hold the menu items
	 *
	 * @var array
	 * @since 11.1
	 */
	protected $_items = array ();
	
	/**
	 * Identifier of the default menu item
	 *
	 * @var integer
	 * @since 11.1
	 */
	protected $_default = array ();
	
	/**
	 * Identifier of the active menu item
	 *
	 * @var integer
	 * @since 11.1
	 */
	protected $_active = 0;

	/**
	 * Switch and check possible JSON errors
	 * 
	 * @access private
	 * @param string $jsonString
	 * @return mixed The decoded object or false if error is detected
	 */
	private function json_validate($jsonString) {
		$error = false;
		// Decode the JSON data
		$result = json_decode($jsonString);
	
		switch (json_last_error()) {
			case JSON_ERROR_DEPTH:
			case JSON_ERROR_STATE_MISMATCH:
			case JSON_ERROR_CTRL_CHAR:
			case JSON_ERROR_SYNTAX:
			case JSON_ERROR_UTF8:
			case JSON_ERROR_RECURSION:
			case JSON_ERROR_INF_OR_NAN:
			case JSON_ERROR_UNSUPPORTED_TYPE:
				$error = true;
				break;
		}
	
		if ($error) {
			return false;
		}
	
		// JSON is correct
		return $result;
	}
	
	/**
	 * Get menu item by id.
	 *
	 * @return object The item object.
	 *        
	 * @since 11.1
	 */
	public function getActive() {
		if ($this->_active) {
			$item = &$this->_items [$this->_active];
			return $item;
		}
		
		return null;
	}
	
	/**
	 * Gets menu items by attribute
	 *
	 * @param mixed $attributes
	 *        	The field name(s).
	 * @param mixed $values
	 *        	The value(s) of the field. If an array, need to match field names
	 *        	each attribute may have multiple values to lookup for.
	 * @param boolean $firstonly
	 *        	If true, only returns the first item found
	 *        	
	 * @return array
	 *
	 * @since 11.1
	 */
	public function getItems($attributes, $values, $firstonly = false) {
		$items = array ();
		$attributes = ( array ) $attributes;
		$values = ( array ) $values;
		$app = Factory::getContainer()->get(\Joomla\CMS\Application\SiteApplication::class);
		
		if ($app->isClient('site')) {
			// Filter by language if not set
			if (($key = array_search ( 'language', $attributes )) === false) {
				if ($app->getLanguageFilter ()) {
					$attributes [] = 'language';
					$values [] = array (
							$app->getLanguage ()->getTag (),
							'*' 
					);
				}
			} elseif ($values [$key] === null) {
				unset ( $attributes [$key] );
				unset ( $values [$key] );
			}
			
			// Filter by access level if not set
			if (($key = array_search ( 'access', $attributes )) === false) {
				$attributes [] = 'access';
				$appInstance = Factory::getApplication();
				$values [] = $appInstance->getIdentity ()->getAuthorisedViewLevels ();
			} elseif ($values [$key] === null) {
				unset ( $attributes [$key] );
				unset ( $values [$key] );
			}
		}
		
		foreach ( $this->_items as $item ) {
			if (! is_array ( $item )) {
				continue;
			}
			
			$test = true;
			for($i = 0, $count = count ( $attributes ); $i < $count; $i ++) {
				if (is_array ( $values [$i] )) {
					if (! in_array ( $item [$attributes [$i]], $values [$i] )) {
						$test = false;
						break;
					}
				} else {
					if ($item [$attributes [$i]] != $values [$i]) {
						$test = false;
						break;
					}
				}
			}
			
			if ($test) {
				if ($firstonly) {
					return $item;
				}
				
				$items [$item ['id']] = $item;
			}
		}
		
		return $items;
	}
	
	/**
	 * Gets the parameter object for a certain menu item
	 *
	 * @param integer $id
	 *        	The item id
	 *        	
	 * @return Registry A Registry object
	 *        
	 * @since 11.1
	 */
	public function getParams($id) {
		if ($menu = $this->getItem ( $id )) {
			return $menu->getParams();
		} else {
			return new Registry ();
		}
	}
	
	/**
	 * Loads the menu items
	 *
	 * @return array
	 *
	 * @since 11.1
	 */
	public function load() {
		// Initialise variables.
		$db = Factory::getContainer()->get('DatabaseDriver');
		$query = method_exists ( $db, 'createQuery' ) ? $db->createQuery () : $db->getQuery ( true );
		$currentDate = Factory::getDate()->toSql();
		
		$query->select ( 'm.id, m.menutype, m.title, m.alias, m.note, m.path AS route, m.link, m.type, m.level, m.language' );
		$query->select ( 'm.browserNav, m.access, m.params, m.home, m.img, m.template_style_id, m.component_id, m.parent_id' );
		$query->select ( 'e.element as component' );
		$query->from ( '#__menu AS m' );
		$query->leftJoin ( '#__extensions AS e ON m.component_id = e.extension_id' );
		$query->where ( 'm.published = 1' );
		$query->where ( 'm.parent_id > 0' );
		$query->where ( 'm.client_id = 0' );
		$query->where('(' . $query->isNullDatetime('m.publish_up') . ' OR m.publish_up <= ' . $db->quote($currentDate) . ')');
		$query->where('(' . $query->isNullDatetime('m.publish_down') . ' OR m.publish_down >= ' . $db->quote($currentDate) . ')');
		$query->order ( 'm.lft' );
		
		// Set the query
		$db->setQuery ( $query );
		if (! ($this->_items = $db->loadAssocList ( 'id' ))) {
			return false;
		}
		
		foreach ( $this->_items as &$item ) {
			// Get parent information.
			$parent_tree = array ();
			if (isset ( $this->_items [$item ['parent_id']] )) {
				$parent_tree = $this->_items [$item ['parent_id']] ['tree'];
			}
			
			// Create tree.
			$parent_tree [] = $item ['id'];
			$item ['tree'] = $parent_tree;
			
			// Create the query array.
			$url = str_replace ( 'index.php?', '', $item ['link'] );
			$url = str_replace ( '&amp;', '&', $url );
			
			parse_str ( $url, $item ['query'] );
		}
	}
	
	/**
	 * Class constructor
	 *
	 * @param array $options
	 *        	An array of configuration options.
	 *
	 * @since 11.1
	 */
	public function __construct($options = array()) {
		// Load the menu items
		$this->load ();

		foreach ( $this->_items as $item ) {
			if ($item ['home']) {
				$this->_default [trim ( $item ['language'] )] = $item ['id'];
			}

			if($this->json_validate($item ['params'])) {
				// Decode the item params
				$result = new Registry ($item ['params']);
				$item ['params'] = $result;
			}
		}
	}
}
