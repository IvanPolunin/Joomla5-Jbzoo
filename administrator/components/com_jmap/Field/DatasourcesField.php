<?php
namespace JExtstore\Component\JMap\Administrator\Field;
/**  
 * @package JMAP::administrator::components::com_jmap
 * @subpackage Field
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Form\FormField;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Data sources available
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage Field
 */
class DatasourcesField extends FormField {
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'datasources';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$dataSources = array ();
		$dataSourcesOptions = array();
		
		// get a list of the menu items
		$query = "SELECT dsource.id, dsource.name, dsource.type" .
				 "\n FROM #__jmap AS dsource" .
				 "\n WHERE dsource.published = 1" .
				 "\n ORDER BY dsource.type, dsource.ordering";
		$db->setQuery ( $query );
		$dataSources = $db->loadObjectList ();
		
		$lastDSType = null;
		$tmpDSType = null;
		foreach ( $dataSources as $dataSource ) {
			if ($dataSource->type != $lastDSType) {
				if ($tmpDSType) {
					$dataSourcesOptions [] = HTMLHelper::_ ( 'select.option', '</OPTGROUP>' );
				}
				$dataSourcesOptions [] = HTMLHelper::_ ( 'select.option', '<OPTGROUP>', strtoupper($dataSource->type) );
				$lastDSType = $dataSource->type;
				$tmpDSType = $dataSource->type;
			}
				
			$dataSourcesOptions [] = HTMLHelper::_ ( 'select.option', $dataSource->id, $dataSource->name );
		}
		if ($lastDSType !== null) {
			$dataSourcesOptions [] = HTMLHelper::_ ( 'select.option', '</OPTGROUP>' );
		}
		
		return HTMLHelper::_('select.genericlist', $dataSourcesOptions, $this->name. '[]', 'multiple="multiple" size="20" style="width: 250px; height: 250px;" class="form-select"', 'value', 'text', $this->value);
	} 
}
