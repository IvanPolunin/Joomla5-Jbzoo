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
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Datasets available
 *
 * @package JMAP::administrator::components::com_jmap
 * @subpackage Field
 *        
 */
class DatasetsField extends FormField {
	/**
	 * The form field type.
	 *
	 * @var		string
	 * @since	1.6
	 */
	protected $type = 'datasets';

	/**
	 * Method to get the field input markup.
	 *
	 * @return	string	The field input markup.
	 * @since	1.6
	 */
	protected function getInput() {
		$db = Factory::getContainer()->get('DatabaseDriver');
		$dataSets = array ();
		
		// get a list of the menu items
		$query = "SELECT dset.id AS value, dset.name AS text" .
				 "\n FROM #__jmap_datasets AS dset" .
				 "\n WHERE dset.published = 1" .
				 "\n ORDER BY dset.name";
		$db->setQuery ( $query );
		$dataSets = $db->loadObjectList ();
		
		array_unshift($dataSets, HTMLHelper::_('select.option', null, Text::_('COM_JMAP_NODATASET_FILTER')));
		
		return HTMLHelper::_('select.genericlist', $dataSets, $this->name, 'class="form-select"', 'value', 'text', $this->value);
	} 
}
