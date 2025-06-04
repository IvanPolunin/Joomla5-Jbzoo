<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Html;
/**  
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Content categories for multiselect
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class Categories {
	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @return array
	 */
	public static function getCategories() {
		$categories = array();
		$categories[] = HTMLHelper::_('select.option', '0', Text::_('COM_JMAP_NOCATS'), 'value', 'text');
		$categories = array_merge($categories, HTMLHelper::_('category.options', 'com_content'));
		
		return $categories;
	}
}