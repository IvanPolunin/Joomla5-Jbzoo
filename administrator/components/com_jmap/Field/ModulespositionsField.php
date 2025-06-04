<?php

namespace JExtstore\Component\JMap\Administrator\Field;

/**
 *
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
use Joomla\CMS\Language\Text;
use Joomla\CMS\Component\ComponentHelper;
use Joomla\Component\Modules\Administrator\Helper\ModulesHelper;
use Joomla\Component\Templates\Administrator\Helper\TemplatesHelper;
use Joomla\Utilities\ArrayHelper;

/**
 * Edit Modules Position field.
 *
 * @since 4.0.0
 */
class ModulespositionsField extends FormField {
	/**
	 * The form field type.
	 *
	 * @var string
	 * @since 4.0.0
	 */
	protected $type = 'modulespositions';

	/**
	 * Client name.
	 *
	 * @var string
	 * @since 4.0.0
	 */
	protected $client;

	/**
	 * Method to attach a Form object to the field.
	 *
	 * @param \SimpleXMLElement $element
	 *        	The SimpleXMLElement object representing the `<field>` tag for the form field object.
	 * @param mixed $value
	 *        	The form field value to validate.
	 * @param string $group
	 *        	The field name group control value. This acts as an array container for the field.
	 *        	For example if the field has name="foo" and the group value is set to "bar" then the
	 *        	full field name would end up being "bar[foo]".
	 *        	
	 * @return boolean True on success.
	 *        
	 * @see FormField::setup()
	 * @since 4.0.0
	 */
	public function setup(\SimpleXMLElement $element, $value, $group = null) {
		$result = parent::setup ( $element, $value, $group );

		if ($result === true) {
			$this->client = $this->element ['client'] ? ( string ) $this->element ['client'] : 'site';
		}

		return $result;
	}

	/**
	 * Display a batch widget for the module position selector.
	 *
	 * @param integer $clientId
	 *        	The client ID.
	 * @param integer $state
	 *        	The state of the module (enabled, unenabled, trashed).
	 * @param string $selectedPosition
	 *        	The currently selected position for the module.
	 *        	
	 * @return string The necessary positions for the widget.
	 *        
	 * @since 2.5
	 */
	public function getPositions($clientId, $state = 1, $selectedPosition = '') {
		$templates = array_keys ( ModulesHelper::getTemplates ( $clientId, $state ) );
		$templateGroups = [ ];

		// Add an empty value to be able to deselect a module position
		$option = ModulesHelper::createOption ( '', Text::_ ( 'COM_JMAP_AIGENERATOR_MODULE_NONE' ) );
		$templateGroups [''] = ModulesHelper::createOptionGroup ( '', [ 
				$option
		] );

		// Add positions from templates
		$isTemplatePosition = false;

		foreach ( $templates as $template ) {
			$options = [ ];

			$positions = TemplatesHelper::getPositions ( $clientId, $template );

			if (\is_array ( $positions )) {
				foreach ( $positions as $position ) {
					$text = ModulesHelper::getTranslatedModulePosition ( $clientId, $template, $position ) . ' [' . $position . ']';
					$options [] = ModulesHelper::createOption ( $position, $text );

					if (! $isTemplatePosition && $selectedPosition === $position) {
						$isTemplatePosition = true;
					}
				}

				$options = ArrayHelper::sortObjects ( $options, 'text' );
			}

			$templateGroups [$template] = ModulesHelper::createOptionGroup ( ucfirst ( $template ), $options );
		}

		// Add custom position to options
		$customGroupText = Text::_ ( 'COM_JMAP_AIGENERATOR_DEFAULT_MODULE_ACTIVE_POSITIONS' );
		$editPositions = true;
		$customPositions = ModulesHelper::getPositions ( $clientId, $editPositions );
		array_splice ( $customPositions, 0, 1 );

		$templateGroups [$customGroupText] = ModulesHelper::createOptionGroup ( $customGroupText, $customPositions );

		return $templateGroups;
	}

	/**
	 * Method to get the field input markup.
	 *
	 * @return string The field input markup.
	 *        
	 * @since 4.0.0
	 */
	protected function getInput() {
		// Switcher based on other setting
		$cParams = ComponentHelper::getParams('com_jmap');
		if ($cParams->get('aigenerator_default_module_position_load', 0)) {
			$clientId = $this->client === 'administrator' ? 1 : 0;
			$positions = $this->getPositions ( $clientId );

			$select = HTMLHelper::_ ( 'select.groupedlist', $positions, 'params[aigenerator_default_module_position]', [ 
					'id' => 'params[aigenerator_default_module_position]',
					'list.select' => $this->value,
					'list.attr' => 'class="form-select"'
			] );
			return $select;
		} else {
			return '<input type="text" name="params[aigenerator_default_module_position]" class="form-control" value="' . $this->value . '"/>';
		}
	}
}
