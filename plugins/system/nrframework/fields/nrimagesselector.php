<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\Registry\Registry;
use Joomla\CMS\Uri\Uri;
use NRFramework\Functions;

class JFormFieldNRImagesSelector extends TextField
{
    /**
	 * Renders the Images Selector
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$field_attributes = (array) $this->element->attributes();
		$attributes = isset($field_attributes["@attributes"]) ? $field_attributes["@attributes"] : null;
		$field_attributes = new Registry($attributes);

		if (!$images = $field_attributes->get('images', ''))
		{
			return;
		}

		$pro_items = array_filter(array_map('trim', explode(',', $field_attributes->get('pro_items', ''))));
		$columns = (int) $field_attributes->get('columns', 1);
		$item_width = $field_attributes->get('item_width', 'auto');
		$image_width = $field_attributes->get('image_width', '');
		$width = $field_attributes->get('width', '100%');
		$height = $field_attributes->get('height', '');
		$key_type = $field_attributes->get('key_type', null);
		$mode = $field_attributes->get('mode', null);
		$gap = $field_attributes->get('gap', '10px');
		$item_gap = $field_attributes->get('item_gap', null);

		$class = [];
		
		if (!empty($this->class))
		{
			$class[] = $this->class;
		}

		$images = $this->parseImages($images, $mode);

		// load CSS
		HTMLHelper::script('plg_system_nrframework/images-selector-field.js', ['relative' => true, 'version' => true]);
		HTMLHelper::stylesheet('plg_system_nrframework/images-selector-field.css', ['relative' => true, 'version' => true]);
		
		$layout = new FileLayout('imagesselector', JPATH_PLUGINS . '/system/nrframework/layouts');

		$data = [
			'value'       => !empty($this->value) ? $this->value : $this->default,
			'name' 	      => $this->name,
			'class'       => implode(' ', $class),
			'key_type'    => $key_type,
			'images'      => $images,
			'columns'     => $columns,
			'id'  	      => $this->id,
			'required'    => $this->required,
			'item_width'  => $item_width,
			'image_width' => $image_width,
			'width'       => $width,
			'height'      => $height,
			'mode'    	  => $mode,
			'gap'    	  => $gap,
			'item_gap'    => $item_gap,
			'pro_items'   => $pro_items
		];
		
        return $layout->render($data);
	}

	/**
	 * Parse images.
	 * 
	 * @param   array   $images
	 * @param   string  $mode
	 */
	private function parseImages($images = [], $mode = null)
	{
		// Links
		if ($mode === 'links')
		{
			$images = json_decode($images, true);

			$site_url = Uri::root();

			// Replace {{SITE_URL}}
			foreach ($images as &$image)
			{
				$image['url'] = str_replace('{{SITE_URL}}', $site_url, $image['url']);
			}
			
			return $images;
		}

		// Paths to images
		$paths = explode(',', $images);

		$images = [];
		foreach ($paths as $key => $path)
		{
			// skip empty paths
			if (empty(rtrim(ltrim($path, ' '), ' ')))
			{
				continue;
			}

			if ($imgs = $this->getImagesFromPath($path))
			{
				// add new images to array of images
				$images = array_merge($images, $imgs);
			}
			else
			{
				// check if image exist
				if (file_exists(JPATH_ROOT . '/' . ltrim($path, ' /')))
				{
					// add new image to array of images
					$images[] = ltrim($path, ' /');
				}
			}
		}

		return $images;
	}

    /**
     * Returns all images in path
     * 
     * @return  mixed
     */
	private function getImagesFromPath($path)
	{
		$folder = JPATH_ROOT . '/' . ltrim($path, ' /');

		if (!is_dir($folder) || !$folder_files = scandir($folder))
		{
			return false;
		}
		
		$images = array_diff($folder_files, array('.', '..', '.DS_Store'));
		$images = array_values($images);

		// prepend path to image file names
		array_walk($images, function(&$value, $key) use ($path) { $value = ltrim($path, ' /') . '/' . $value; } );
		
		return $images;
	}
}
