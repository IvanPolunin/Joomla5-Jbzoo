<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

namespace NRFramework;

// No direct access
defined('_JEXEC') or die;

use NRFramework\Cache;
use NRFramework\Functions;
use NRFramework\Extension;
use Joomla\CMS\Language\Text;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

class HTML
{
	/**
	 * Display field help text as tooltip in Joomla 4
	 *
	 * @return void
	 */
	public static function fixFieldTooltips()
	{
		// Run once
        static $run;

        if ($run)
        {
            return;
        }

        $run = true;

		HTMLHelper::_('bootstrap.popover');

		$doc = Factory::getDocument();

		$doc->addStyleDeclaration('
			.form-text, .form-control-feedback {
				display:none;
			}
			.tooltip .arrow:before {
				border-top-color:#444;
				border-bottom-color:#444;
			}
			.tooltip-inner {
				text-align: left;
				background-color: #444;
				padding: 7px 9px;
				max-width:300px;
			}
		');

		$doc->addScriptDeclaration('
			document.addEventListener("DOMContentLoaded", function() {
				initPopover();
		
				document.addEventListener("joomla:updated", initPopover);
		
				function initPopover(event) {
					var target = event && event.target ? event.target : document;
					var fields = target.querySelectorAll(".control-group");
		
					fields.forEach(function(field) {
						var desc = field.querySelector(".form-text");
			
						if (desc) {
							var label = field.querySelector("label");
			
							if (label) {
								label.classList.add("tTooltip");
								label.setAttribute("title", desc.innerHTML);
							}
						}
					});
		
					jQuery(target).find(".tTooltip").tooltip({
						placement: "top",
						html: true,
						delay: {
							show: 200
						}
					});
				}
			});
		');
	}
	
	/**
	 * Renders the HTML layout
	 * 
	 * @param   string  $layout		The HTML class of the layout.
	 * @param   array   $options	A list of attributes passed to the layout
	 * 
	 * @return  string
	 */
	public static function render($layout, $options = [])
	{
		if (!$layout)
		{
			return;
		}

		$class = '\NRFramework\HTML\\' . $layout;

		// ensure class exists
		if (!class_exists($class))
		{
			return;
		}

		return (new $class($options))->render();
	}
	
	/**
	 * Renders Pro Button
	 *
	 * @param string $feature_label  The text that will be used as the modal popup feature
	 *
	 * @return void
	 */
	public static function renderProButton($feature_label = null)
	{
		include_once JPATH_PLUGINS . '/system/nrframework/fields/pro.php';

        $field   = new \JFormFieldNR_PRO;
        $element = new \SimpleXMLElement('
			<field name="pro" type="nr_pro"
				label="' . $feature_label . '"
            />');

        $field->setup($element, null);

        echo $field->__get('input');
	}

    /**
     *  Renders a modal that will be shown on Pro only features
	 * 
	 *  @param    string  $extension_name
     *
     *  @return   void
     */
    public static function renderProOnlyModal($extension = null)
    {
		$hash = 'proOnlyModal';

		// Render modal once
		if (Cache::get($hash))
		{
			return;
		}

		$options = [
			'extension_name' => is_null($extension) ? Extension::getExtensionNameByRequest(true) : Extension::getExtensionName($extension),
			'upgrade_url'    => Extension::getTassosExtensionUpgradeURL($extension)
		];

		$html = LayoutHelper::render('proonlymodal', $options, dirname(__DIR__) . '/layouts');

		echo HTMLHelper::_('bootstrap.renderModal', 'proOnlyModal', ['backdrop' => 'static'], $html);

		Cache::set($hash, true);
	}

	public static function smartTagsBox($options = array())
	{
		HTMLHelper::_('jquery.framework');

		include_once JPATH_PLUGINS . '/system/nrframework/fields/smarttagsbox.php';

		$field   = new \JFormFieldSmartTagsBox;
		$element = new \SimpleXMLElement('<field name="pro" type="SmartTagsBox"/>');
		
		$field->setup($element, null);

		return $field->__get('input');
	}

	/**
	 * Construct the HTML for the input field in a tree
	 * Logic from administrator\components\com_modules\views\module\tmpl\edit_assignment.php
	 */
	public static function treeselect(&$options, $name, $value, $id, $size = 300, $simple = 0, $class = '')
	{
		Functions::loadLanguage('com_menus', JPATH_ADMINISTRATOR);
		Functions::loadLanguage('com_modules', JPATH_ADMINISTRATOR);

		if (empty($options))
		{
			return '<fieldset class="radio">' . Text::_('NR_NO_ITEMS_FOUND') . '</fieldset>';
		}

		if (!is_array($value))
		{
			$value = explode(',', $value);
		}

		$count = 0;
		if ($options != -1)
		{
			foreach ($options as $option)
			{
				$count++;
				if (isset($option->links))
				{
					$count += count($option->links);
				}
			}
		}

		if ($options == -1)
		{
			if (is_array($value))
			{
				$value = implode(',', $value);
			}
			if (!$value)
			{
				$input = '<textarea name="' . $name . '" id="' . $id . '" cols="40" rows="5">' . $value . '</textarea>';
			}
			else
			{
				$input = '<input type="text" name="' . $name . '" id="' . $id . '" value="' . $value . '" size="60">';
			}

			return '<fieldset class="radio"><label for="' . $id . '">' . Text::_('NR_ITEM_IDS') . ':</label>' . $input . '</fieldset>';
		}

		if ($simple)
		{
			$attr = 'style="width: ' . $size . 'px" multiple="multiple"';

			if (!empty($class))
			{
				$attr .= ' class="' . $class . '"';
			}

			$html = HTMLHelper::_('select.genericlist', $options, $name, trim($attr), 'value', 'text', $value, $id);

			return $html;
		}

		HTMLHelper::script('plg_system_nrframework/treeselect.js', ['relative' => true, 'version' => true]);
		HTMLHelper::stylesheet('plg_system_nrframework/treeselect.css', ['relative' => true, 'version' => true]);

		$html = array();

		$html[] = '<div class="nr_treeselect" id="' . $id . '">';
		$html[] = '
			<div class="form-inline nr_treeselect-controls">

				<span class="nr_treeselect_control">' . Text::_('JSELECT') . ':
					<a class="nr_treeselect-checkall" href="javascript:;">' . Text::_('JALL') . '</a>,
					<a class="nr_treeselect-uncheckall" href="javascript:;">' . Text::_('JNONE') . '</a>,
					<a class="nr_treeselect-toggleall" href="javascript:;">' . Text::_('NR_TOGGLE') . '</a>
				</span>
				
				<span class="nr_treeselect_control">' . Text::_('NR_EXPAND') . ':
					<a class="nr_treeselect-expandall" href="javascript:;">' . Text::_('JALL') . '</a>,
					<a class="nr_treeselect-collapseall" href="javascript:;">' . Text::_('JNONE') . '</a>
				</span>
				
				<span class="nr_treeselect_control">' . Text::_('JSHOW') . ':
					<a class="nr_treeselect-showall" href="javascript:;">' . Text::_('JALL') . '</a>,
					<a class="nr_treeselect-showselected" href="javascript:;">' . Text::_('NR_SELECTED') . '</a>
				</span>

				<span class="nr_treeselect_control nr_treeselect-filter right">
					<input type="text" name="nr_treeselect-filter" class="search-query nr_treeselect-filter" size="16"
					autocomplete="off" placeholder="' . Text::_('JSEARCH_FILTER') . '" aria-invalid="false" tabindex="-1">
				</span>
			</div>';

		$o = array();
		foreach ($options as $option)
		{
			$option->level = isset($option->level) ? $option->level : 0;
			$o[]           = $option;
			if (isset($option->links))
			{
				foreach ($option->links as $link)
				{
					$link->level = $option->level + (isset($link->level) ? $link->level : 1);
					$o[]         = $link;
				}
			}
		}

		$html[]    = '<ul class="nr_treeselect-ul" style="max-height:300px;min-width:' . $size . 'px;overflow-x: hidden;">';
		$prevlevel = 0;

		foreach ($o as $i => $option)
		{
			if ($prevlevel < $option->level)
			{
				// correct wrong level indentations
				$option->level = $prevlevel + 1;

				$html[] = '<ul class="nr_treeselect-sub">';
			}
			else if ($prevlevel > $option->level)
			{
				$html[] = str_repeat('</li></ul>', $prevlevel - $option->level);
			}
			else if ($i)
			{
				$html[] = '</li>';
			}

			$labelclass = trim('pull-left ' . (isset($option->labelclass) ? $option->labelclass : ''));

			$html[] = '<li>';

			$item = '<div class="' . trim('nr_treeselect-item pull-left ' . (isset($option->class) ? $option->class : '')) . '">';
			if (isset($option->title))
			{
				$labelclass .= ' nav-header';
			}

			if (isset($option->title) && (!isset($option->value) || !$option->value))
			{
				$item .= '<label class="' . $labelclass . '">' . $option->title . '</label>';
			}
			else
			{
				$selected = in_array($option->value, $value) ? ' checked="checked"' : '';
				$disabled = (isset($option->disable) && $option->disable) ? ' readonly="readonly" style="visibility:hidden"' : '';

				$item .= '<input type="checkbox" class="pull-left" name="' . $name . '" id="' . $id . $option->value . '" value="' . $option->value . '"' . $selected . $disabled . '>
					<label for="' . $id . $option->value . '" class="' . $labelclass . '">' . $option->text . '</label>';
			}
			$item .= '</div>';
			$html[] = $item;

			if (!isset($o[$i + 1]) && $option->level > 0)
			{
				$html[] = str_repeat('</li></ul>', (int) $option->level);
			}
			$prevlevel = $option->level;
		}
		$html[] = '</ul>';
		$html[] = '
			<div style="display:none;" class="nr_treeselect-menu-block">
				<div class="pull-left nav-hover nr_treeselect-menu">
					<div class="btn-group">
						<a href="#" data-toggle="dropdown" data-bs-toggle="dropdown" class="dropdown-toggle btn btn-secondary">
							<span class="caret"></span>
						</a>
						<ul class="dropdown-menu">
							<li class="nav-header">' . Text::_('COM_MODULES_SUBITEMS') . '</li>
							<li class="divider"></li>
							<li>
								<a class="checkall" href="javascript:;">
									<span class="icon-checkbox"></span> 
									' . Text::_('JSELECT') . '
								</a>
							</li>
							<li>
								<a class="uncheckall" href="javascript:;">
									<span class="icon-checkbox-unchecked"></span>
									' . Text::_('COM_MODULES_DESELECT') . '
								</a>
							</li>
							<div class="nr_treeselect-menu-expand">
								<li class="divider"></li>
								<li><a class="expandall" href="javascript:;"><span class="icon-plus"></span> ' . Text::_('NR_EXPAND') . '</a></li>
								<li><a class="collapseall" href="javascript:;"><span class="icon-minus"></span> ' . Text::_('NR_COLLAPSE') . '</a></li>
							</div>
						</ul>
					</div>
				</div>
			</div>';
		$html[] = '</div>';

		$html = implode('', $html);
		return $html;
	}

	public static function treeselectSimple(&$options, $name, $value, $id, $size = 300, $class = '')
	{
		return self::treeselect($options, $name, $value, $id, $size, 1, $class);
	}

	/**
	 * Wrapper for the HTMLHelper::script method to support old method signatures in Joomla < 3.7.0.
	 *
	 * @param  string $path
	 *
	 * @deprecated Since we no longer support 3.7.0, use HTMLHelper::script directly. 
	 * @return void
	 */
	public static function script($path)
	{
		if (version_compare(JVERSION, '3.7.0', 'lt'))
		{
			HTMLHelper::script($path, false, true);
		} else 
		{
			HTMLHelper::script($path, ['relative' => true, 'version' => 'auto']);
		}
	}

	/**
	 * Wrapper for the HTMLHelper::stylesheet method to support old method signatures in Joomla < 3.7.0.
	 *
	 * @param  string $path
	 *
	 * @return void
	 * @deprecated Since we no longer support 3.7.0, use HTMLHelper::script directly.
	 */
	public static function stylesheet($path)
	{
		if (version_compare(JVERSION, '3.7.0', 'lt'))
		{
			HTMLHelper::stylesheet($path, false, true);
		} else 
		{
			HTMLHelper::stylesheet($path, ['relative' => true, 'version' => 'auto']);
		}
	}

	/**
	 * For Backwards Compatibility
	 * 
	 * @deprecated 4.9.50
	 */
	public static function checkForOutdatedExtension($extension, $days_old = 120)
	{
		if (!Extension::isOutdated($extension, $days_old))
		{
			return;
		}

		// Load extension's language file
		Functions::loadLanguage($extension);
		
		$payload = [
			'extension' => Text::_($extension),
			'days_old' => $days_old
		];
		
		// load template
		return LayoutHelper::render('outdated_extension', $payload, dirname(__DIR__) . '/layouts');
	}

	public static function updateNotification($extension)
	{
		$version_installed = Extension::getVersion($extension);
		$version_latest    = Extension::getLatestVersion($extension);

		if (!$needsUpdate = version_compare($version_latest, $version_installed, 'gt'))
		{
			return;
		}

		// Load extension's language file
		Functions::loadLanguage($extension);

		// Extension Title
		$title = Text::_($extension);
		$title = str_replace('System -', '', $title); // Remove plugin folder prefix from plugins

		// Render Layout
		$data = [
			'title' 	        => $title,
			'version_installed' => $version_installed,
			'version_latest'    => $version_latest,
			'ispro' 	        => Extension::isPro($extension),
			'upgradeurl'        => Extension::getTassosExtensionUpgradeURL($extension),
			'product_url'       => Extension::getProductURL($extension)
		];

		return LayoutHelper::render('updatechecker', $data, JPATH_PLUGINS . '/system/nrframework/layouts');
	}

	/**
	 * TODO: Not used anywhere, should delete.
	 * 
	 * @deprecated 4.11.7
	 */
	public static function checkForUpdates($element)
	{
		HTMLHelper::script('plg_system_nrframework/updatechecker.js', ['relative' => true, 'version' => true]);
		HTMLHelper::stylesheet('plg_system_nrframework/updatechecker.css', ['relative' => true, 'version' => true]);

		return '
			<div class="nr_updatechecker"
				data-element="' . $element. '" 
				data-base=' . Uri::base() . ' 
				data-token=' . Session::getFormToken() . '>
			</div>
		';
	}
}