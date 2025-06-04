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
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Articles multiselect element class
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage html
 *        
 */
class Articles {
	/**
	 * Build the multiple select list for Menu Links/Pages
	 * 
	 * @access public
	 * @return array
	 */
	public static function getArticles() {
		$db = Factory::getContainer()->get('DatabaseDriver');
		
		$articleOptions = array();
		$articleOptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_JMAP_NOARTICLES'), 'value', 'text');
		$categories = array();
		$categories = HTMLHelper::_('category.options', 'com_content');
		
				
		if(!empty($categories)) {
			foreach ($categories as $category) {
				if(!$category->value) {
					continue;
				}
				// Get category indent from cat to replicate on articles
				preg_match('/^([-\s])+/', $category->text, $matches);
				$indent = null;
				if(isset($matches[0])) {
					$indent = $matches[0];
				}
				
				// Get a list of articles in this category
				$query = "SELECT c.id AS value, c.title AS text" .
						 "\n FROM " . $db->quoteName('#__content') . " AS " . $db->quoteName('c') .
						 "\n WHERE c.state = 1" .
						 "\n AND c.catid = " . (int)$category->value .
						 "\n ORDER BY c.ordering";
				$db->setQuery ( $query );
				$articles = $db->loadObjectList ();
				
				// Group articles by OPTGROUP category
				$articleOptions[] = HTMLHelper::_ ( 'select.option', '<OPTGROUP>', $category->text );
				
				if(!empty($articles)) {
					foreach ($articles as $article) {
						$articleOptions[] = HTMLHelper::_('select.option', $article->value, $indent . $article->text, 'value', 'text');
					}
				}
				
				// Close the OPTGROUP
				$articleOptions[] = HTMLHelper::_ ( 'select.option', '</OPTGROUP>');
			}
		}
		
		return $articleOptions;
	}
	
	/**
	 * Build the multiple select list for Workflow/Stages
	 *
	 * @access public
	 * @return array
	 */
	public static function getWorkflowStages() {
		$db = Factory::getContainer()->get('DatabaseDriver');
	
		// Load com_workflow language
		$jLang = Factory::getApplication()->getLanguage();
		$jLang->load('com_workflow', JPATH_ADMINISTRATOR, 'en-GB', true, false);
		if($jLang->getTag() != 'en-GB') {
			$jLang->load('com_workflow', JPATH_ADMINISTRATOR, null, true, false);
		}
		
		$stageOptions = array();
		$stageOptions[] = HTMLHelper::_('select.option', '0', Text::_('COM_JMAP_NO_WORKFLOW_STAGE'), 'value', 'text');
		$workflows = array();
		$workflows = HTMLHelper::_('workflowstage.existing', array('title'=>''));
	
	
		if(!empty($workflows)) {
			foreach ($workflows as $workflowTitle => $stages) {
				if(!$workflowTitle) {
					continue;
				}
				
				// Group articles by OPTGROUP category
				$stageOptions[] = HTMLHelper::_ ( 'select.option', '<OPTGROUP>', $workflowTitle);
	
				if(!empty($stages)) {
					foreach ($stages as $stage) {
						$stageOptions[] = HTMLHelper::_('select.option', $stage->value, $stage->text, 'value', 'text');
					}
				}
	
				// Close the OPTGROUP
				$stageOptions[] = HTMLHelper::_ ( 'select.option', '</OPTGROUP>');
			}
		}
	
		return $stageOptions;
	}
}