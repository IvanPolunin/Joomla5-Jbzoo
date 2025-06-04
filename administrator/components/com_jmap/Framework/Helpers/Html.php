<?php
namespace JExtstore\Component\JMap\Administrator\Framework\Helpers;
/**
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage helpers
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html
 */
defined('_JEXEC') or die('Restricted access');
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;

/**
 * Override for html utility functions
 *
 * @package JMAP::FRAMEWORK::administrator::components::com_jmap
 * @subpackage framework
 * @subpackage helpers
 * @since 3.5
 */
class Html {
	/**
	 * Default values for options. Organized by option group.
	 *
	 * @var     array
	 * @since   1.5
	 */
	protected static $optionDefaults = [
			'option' => [
					'option.attr'         => null,
					'option.disable'      => 'disable',
					'option.id'           => null,
					'option.key'          => 'value',
					'option.key.toHtml'   => true,
					'option.label'        => null,
					'option.label.toHtml' => true,
					'option.text'         => 'text',
					'option.text.toHtml'  => true,
					'option.class'        => 'class',
					'option.onclick'      => 'onclick',
			],
	];
	
	/**
	 * Generates a yes/no radio list.
	 *
	 * @param   string  $name      The value of the HTML name attribute
	 * @param   array   $attribs   Additional HTML attributes for the `<select>` tag
	 * @param   string  $selected  The key that is selected
	 * @param   string  $yes       Language key for Yes
	 * @param   string  $no        Language key for no
	 * @param   mixed   $id        The id for the field or false for no id
	 *
	 * @return  string  HTML for the radio list
	 *
	 * @since   1.5
	 */
	public static function booleanlist($name, $attribs = array(), $selected = null, $yes = 'JYES', $no = 'JNO', $id = false) {
		$arr = array(HTMLHelper::_('select.option', '1', Text::_($yes)), HTMLHelper::_('select.option', '0', Text::_($no)));
	
		return self::radiolist( $arr, $name, $attribs, 'value', 'text', (int) $selected, $id);
	}
	
	/**
	 * Generates an HTML radio list.
	 *
	 * @param   array    $data       An array of objects
	 * @param   string   $name       The value of the HTML name attribute
	 * @param   string   $attribs    Additional HTML attributes for the `<select>` tag
	 * @param   mixed    $optKey     The key that is selected
	 * @param   string   $optText    The name of the object variable for the option value
	 * @param   string   $selected   The name of the object variable for the option text
	 * @param   boolean  $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True if options will be translated
	 *
	 * @return  string  HTML for the select list
	 *
	 * @since   1.5
	 */
	public static function radiolist($data, $name, $attribs = null, $optKey = 'value', $optText = 'text', $selected = null, $idtag = false, $translate = false) {
		if (is_array($attribs))
		{
			$attribs = ArrayHelper::toString($attribs);
		}
	
		$id_text = $idtag ?: $name;
	
		$html = '<div class="controls">';
	
		foreach ($data as $obj)
		{
			$k = $obj->$optKey;
			$t = $translate ? Text::_($obj->$optText) : $obj->$optText;
			$id = (isset($obj->id) ? $obj->id : null);
	
			$extra = '';
			$id = $id ? $obj->id : $id_text . $k;
	
			if (is_array($selected))
			{
				foreach ($selected as $val)
				{
					$k2 = is_object($val) ? $val->$optKey : $val;
	
					if ($k == $k2)
					{
						$extra .= ' selected="selected" ';
						break;
					}
				}
			}
			else
			{
				$extra .= ((string) $k === (string) $selected ? ' checked="checked" ' : '');
			}
	
			$html .= "\n\t" . '<label for="' . $id . '" id="' . $id . '-lbl" class="radio">';
			$html .= "\n\t\n\t" . '<input type="radio" name="' . $name . '" id="' . $id . '" value="' . $k . '" ' . $extra
			. $attribs . '>' . $t;
			$html .= "\n\t" . '</label>';
		}
	
		$html .= "\n";
		$html .= '</div>';
		$html .= "\n";
	
		return $html;
	}
	
	/**
	 * Method to create an icon for saving a new ordering in a grid
	 *
	 * @param   array   $rows   The array of rows of rows
	 * @param   string  $image  The image [UNUSED]
	 * @param   string  $task   The task to use, defaults to save order
	 *
	 * @return  string
	 *
	 * @since   1.5
	 */
	public static function order($rows, $image = 'filesave.png', $task = 'saveorder') {
		return '<a href="javascript:JMapSaveOrder('
				. (count($rows) - 1) . ', \'' . $task . '\')" rel="tooltip" class="saveorder btn btn-sm btn-secondary float-end" title="'
				. Text::_('JLIB_HTML_SAVE_ORDER') . '"><span class="icon-menu-2"></span></a>';
	}
	
	/**
	 * Generates an HTML selection list.
	 *
	 * @param   array    $data       An array of objects, arrays, or scalars.
	 * @param   string   $name       The value of the HTML name attribute.
	 * @param   mixed    $attribs    Additional HTML attributes for the `<select>` tag. This
	 *                               can be an array of attributes, or an array of options. Treated as options
	 *                               if it is the last argument passed. Valid options are:
	 *                               Format options, see {@see HTMLHelper::$formatOptions}.
	 *                               Selection options, see {@see JHtmlSelect::options()}.
	 *                               list.attr, string|array: Additional attributes for the select
	 *                               element.
	 *                               id, string: Value to use as the select element id attribute.
	 *                               Defaults to the same as the name.
	 *                               list.select, string|array: Identifies one or more option elements
	 *                               to be selected, based on the option key values.
	 * @param   string   $optKey     The name of the object variable for the option value. If
	 *                               set to null, the index of the value array is used.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string).
	 * @param   mixed    $idtag      Value of the field id or null by default
	 * @param   boolean  $translate  True to translate
	 *
	 * @return  string  HTML for the select list.
	 *
	 * @since   1.5
	 */
	public static function genericlist(
			$data,
			$name,
			$attribs = null,
			$optKey = 'value',
			$optText = 'text',
			$selected = null,
			$idtag = false,
			$translate = false
			) {
				// Set default options
				$options = array_merge(HTMLHelper::$formatOptions, ['format.depth' => 0, 'id' => false]);
				
				if (\is_array($attribs) && \func_num_args() === 3) {
					// Assume we have an options array
					$options = array_merge($options, $attribs);
				} else {
					// Get options from the parameters
					$options['id']             = $idtag;
					$options['list.attr']      = $attribs;
					$options['list.translate'] = $translate;
					$options['option.key']     = $optKey;
					$options['option.text']    = $optText;
					$options['list.select']    = $selected;
				}
				
				$attribs = '';
				
				if (isset($options['list.attr'])) {
					if (\is_array($options['list.attr'])) {
						$attribs = ArrayHelper::toString($options['list.attr']);
					} else {
						$attribs = $options['list.attr'];
					}
					
					if ($attribs !== '') {
						$attribs = ' ' . $attribs;
					}
				}
				
				$id = $options['id'] !== false ? $options['id'] : $name;
				$id = str_replace(['[', ']', ' '], '', $id);
				
				// If the selectbox contains "form-select-color-state" then load the JS file
				if (strpos($attribs, 'form-select-color-state') !== false) {
					Factory::getDocument()->getWebAssetManager()
					->registerAndUseScript(
							'webcomponent.select-colour',
							'system/fields/select-colour.min.js',
							['dependencies' => ['wcpolyfill']],
							['type'         => 'module']
							);
				}
				
				$baseIndent = str_repeat($options['format.indent'], $options['format.depth']++);
				$html       = $baseIndent . '<select' . ($id !== '' ? ' id="' . $id . '"' : '') . ' name="' . $name . '"' . $attribs . '>' . $options['format.eol']
				. static::options($data, $options, 'text', null, $translate) . $baseIndent . '</select>' . $options['format.eol'];
				
				return $html;
	}
	
	/**
	 * Generates the option tags for an HTML select list (with no select tag
	 * surrounding the options).
	 *
	 * @param   array    $arr        An array of objects, arrays, or values.
	 * @param   mixed    $optKey     If a string, this is the name of the object variable for
	 *                               the option value. If null, the index of the array of objects is used. If
	 *                               an array, this is a set of options, as key/value pairs. Valid options are:
	 *                               -Format options, {@see HTMLHelper::$formatOptions}.
	 *                               -groups: Boolean. If set, looks for keys with the value
	 *                                "&lt;optgroup>" and synthesizes groups from them. Deprecated. Defaults
	 *                                true for backwards compatibility.
	 *                               -list.select: either the value of one selected option or an array
	 *                                of selected options. Default: none.
	 *                               -list.translate: Boolean. If set, text and labels are translated via
	 *                                Text::_(). Default is false.
	 *                               -option.id: The property in each option array to use as the
	 *                                selection id attribute. Defaults to none.
	 *                               -option.key: The property in each option array to use as the
	 *                                selection value. Defaults to "value". If set to null, the index of the
	 *                                option array is used.
	 *                               -option.label: The property in each option array to use as the
	 *                                selection label attribute. Defaults to null (none).
	 *                               -option.text: The property in each option array to use as the
	 *                               displayed text. Defaults to "text". If set to null, the option array is
	 *                               assumed to be a list of displayable scalars.
	 *                               -option.attr: The property in each option array to use for
	 *                                additional selection attributes. Defaults to none.
	 *                               -option.disable: The property that will hold the disabled state.
	 *                                Defaults to "disable".
	 *                               -option.key: The property that will hold the selection value.
	 *                                Defaults to "value".
	 *                               -option.text: The property that will hold the the displayed text.
	 *                               Defaults to "text". If set to null, the option array is assumed to be a
	 *                               list of displayable scalars.
	 * @param   string   $optText    The name of the object variable for the option text.
	 * @param   mixed    $selected   The key that is selected (accepts an array or a string)
	 * @param   string  $translate  Translate the option values.
	 *
	 * @return  string  HTML for the select list
	 *
	 * @since   1.5
	 */
	public static function options($arr, $optKey = 'value', $optText = 'text', $selected = null, $translate = null)
	{
		$options = array_merge(
				HTMLHelper::$formatOptions,
				static::$optionDefaults['option'],
				['format.depth' => 0, 'groups' => true, 'list.select' => null, 'list.translate' => false]
				);
		
		if (\is_array($optKey)) {
			// Set default options and overwrite with anything passed in
			$options = array_merge($options, $optKey);
		} else {
			// Get options from the parameters
			$options['option.key']     = $optKey;
			$options['option.text']    = $optText;
			$options['list.select']    = $selected;
			$options['list.translate'] = $translate;
		}
		
		$html       = '';
		$baseIndent = str_repeat($options['format.indent'], $options['format.depth']);
		
		foreach ($arr as $elementKey => &$element) {
			$attr  = '';
			$extra = '';
			$label = '';
			$id    = '';
			
			if (\is_array($element)) {
				$key  = $options['option.key'] === null ? $elementKey : $element[$options['option.key']];
				$text = $element[$options['option.text']];
				
				if (isset($element[$options['option.attr']])) {
					$attr = $element[$options['option.attr']];
				}
				
				if (isset($element[$options['option.id']])) {
					$id = $element[$options['option.id']];
				}
				
				if (isset($element[$options['option.label']])) {
					$label = $element[$options['option.label']];
				}
				
				if (isset($element[$options['option.disable']]) && $element[$options['option.disable']]) {
					$extra .= ' disabled="disabled"';
				}
			} elseif (\is_object($element)) {
				$key  = $options['option.key'] === null ? $elementKey : $element->{$options['option.key']};
				$text = $element->{$options['option.text']};
				
				if (isset($element->{$options['option.attr']})) {
					$attr = $element->{$options['option.attr']};
				}
				
				if (isset($element->{$options['option.id']})) {
					$id = $element->{$options['option.id']};
				}
				
				if (isset($element->{$options['option.label']})) {
					$label = $element->{$options['option.label']};
				}
				
				if (isset($element->{$options['option.disable']}) && $element->{$options['option.disable']}) {
					$extra .= ' disabled="disabled"';
				}
				
				if (isset($element->{$options['option.class']}) && $element->{$options['option.class']}) {
					$extra .= ' class="' . $element->{$options['option.class']} . '"';
				}
				
				if (isset($element->{$options['option.onclick']}) && $element->{$options['option.onclick']}) {
					$extra .= ' onclick="' . $element->{$options['option.onclick']} . '"';
				}
			} else {
				// This is a simple associative array
				$key  = $elementKey;
				$text = $element;
			}
			
			/*
			 * The use of options that contain optgroup HTML elements was
			 * somewhat hacked for J1.5. J1.6 introduces the grouplist() method
			 * to handle this better. The old solution is retained through the
			 * "groups" option, which defaults true in J1.6, but should be
			 * deprecated at some point in the future.
			 */
			
			$key = (string) $key;
			
			if ($key === '<OPTGROUP>' && $options['groups']) {
				$html .= $baseIndent . '<optgroup label="' . ($options['list.translate'] ? Text::_($text) : $text) . '">' . $options['format.eol'];
				$baseIndent = str_repeat($options['format.indent'], ++$options['format.depth']);
			} elseif ($key === '</OPTGROUP>' && $options['groups']) {
				$baseIndent = str_repeat($options['format.indent'], --$options['format.depth']);
				$html .= $baseIndent . '</optgroup>' . $options['format.eol'];
			} else {
				// If no string after hyphen - take hyphen out
				$splitText = explode(' - ', $text, 2);
				$text      = $splitText[0];
				
				if (isset($splitText[1]) && $splitText[1] !== '' && !preg_match('/^[\s]+$/', $splitText[1])) {
					$text .= ' - ' . $splitText[1];
				}
				
				if (!empty($label) && $options['list.translate']) {
					$label = Text::_($label);
				}
				
				if ($options['option.label.toHtml']) {
					$label = htmlentities($label);
				}
				
				if (\is_array($attr)) {
					$attr = ArrayHelper::toString($attr);
				} else {
					$attr = trim($attr);
				}
				
				$extra = ($id ? ' id="' . $id . '"' : '') . ($label ? ' label="' . $label . '"' : '') . ($attr ? ' ' . $attr : '') . $extra;
				
				if (\is_array($options['list.select'])) {
					foreach ($options['list.select'] as $val) {
						$key2 = \is_object($val) ? $val->{$options['option.key']} : $val;
						
						if ($key == $key2) {
							$extra .= ' selected="selected"';
							break;
						}
					}
				} elseif ((string) $key === (string) $options['list.select']) {
					$extra .= ' selected="selected"';
				}
				
				if ($options['list.translate']) {
					$text = Text::_($text);
				}
				
				// Generate the option, encoding as required
				$translateString = '';
				if($translate && isset($element->languagecode)) {
					$translateString = 'data-languagecode="' . $element->languagecode . '"';
				}
				$html .= $baseIndent . '<option ' . $translateString . ' value="' . ($options['option.key.toHtml'] ? htmlspecialchars($key, ENT_COMPAT, 'UTF-8') : $key) . '"'
						. $extra . '>';
						$html .= $options['option.text.toHtml'] ? htmlentities(html_entity_decode($text, ENT_COMPAT, 'UTF-8'), ENT_COMPAT, 'UTF-8') : $text;
						$html .= '</option>' . $options['format.eol'];
			}
		}
		
		return $html;
	}
}

