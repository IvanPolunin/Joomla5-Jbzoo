<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

/*
	Class: ElementGooglemaps
		The google maps element class
*/

use Joomla\CMS\Language\Text;

class ElementGooglemaps extends Element implements iSubmittable {

    /*
       Function: getValue
            Returns the element's value.

       Parameters:
            $params - render parameter

        Returns:
            Value
    */
    public function getValue($params = array()) {

        $latlng = $this->get('latlng');

        if ($latlng) {
            [$lat, $lng] = explode(',', $latlng);
            return ['lat' => $lat, 'lng' => $lng];
        }

        // get geocode cache
        $cache = $this->app->cache->create($this->app->path->path('cache:') . '/geocode_cache');
        if (!$cache->check()) {
            $this->app->system->application->enqueueMessage(sprintf('Cache not writable. Please update the file permissions! (%s)', $this->app->path->path('cache:') . '/geocode_cache'), 'notice');
            return;
        }

        $location = $this->get('location');

        // get map center coordinates
        try {

            $latlng = $this->app->googlemaps->locate($location, $cache, $this->config->get('key'));

        } catch (GooglemapsHelperException $e) {
            $this->app->system->application->enqueueMessage($e, 'notice');
            return;
        }

        // save location to geocode cache
        if ($cache) {
            $cache->save();
        }

        // save location to item
        if ($latlng) {
            $table = $this->app->table->item;
            if ($item = $table->get($this->getItem()->id, true)) {
                $element = $item->getElement($this->identifier);
                $element->bindData([
                    'location' => $location,
                    'latlng'   => "{$latlng['lat']},{$latlng['lng']}"]
                );
                $table->save($item);
            }
        }

        return $latlng;

    }

    /*
        Function: render
            Renders the element.

       Parameters:
            $params - render parameter

        Returns:
            String - html
    */
	public function render($params = array()) {

        $center = $this->getValue($params);

        if (!$center) {
            return;
        }

		// init vars
		$params			   = $this->app->data->create($params);
		$locale            = $this->config->get('locale');
		$key			   = $this->config->get('key');

		// init display params
		$layout   		   = $params->get('layout');
		$width             = $params->get('width');
		$width_unit        = $params->get('width_unit');
		$height            = $params->get('height');
		$information       = $params->get('information');

		// determine locale
		if (empty($locale) || $locale == 'auto') {
			$locale = $this->app->user->getBrowserDefaultLanguage();
		}

		// get marker text
		$marker_text = '';
		$renderer = $this->app->renderer->create('item')->addPath(array($this->app->path->path('component.site:'), $this->_item->getApplication()->getTemplate()->getPath()));
		if ($item = $this->getItem()) {
			$path   = 'item';
			$prefix = 'item.';
			$type   = $item->getType()->id;
			if ($renderer->pathExists($path.DIRECTORY_SEPARATOR.$type)) {
				$path   .= DIRECTORY_SEPARATOR.$type;
				$prefix .= $type.'.';
			}

			if (in_array($layout, $renderer->getLayouts($path))) {
				$marker_text = $renderer->render($prefix.$layout, array('item' => $item));
			} else {
				$marker_text = $item->name;
			}
		}

		// add assets
		$this->app->document->addStylesheet('elements:googlemaps/googlemaps.css');

		// css parameters
		$maps_id           = 'googlemaps-'.uniqid();
		$css_module_width  = 'width: '.$width.$width_unit.';';
		$css_module_height = 'height: '.$height.'px;';
		$data = json_encode(array(
			'lat' => $center['lat'],
			'lng' => $center['lng'],
			'popup' => (boolean) $params->get('marker_popup'),
			'text' => $this->app->googlemaps->stripText($marker_text),
			'zoom' => (int) $params->get('zoom_level'),
			'mapCtrl' => $params->get('map_controls'),
			'zoomWhl' => (boolean) $params->get('scroll_wheel_zoom'),
			'mapType' => $params->get('map_type'),
			'typeCtrl' => (boolean) $params->get('type_controls'),
			'directions' => (boolean) $params->get('directions'),
			'locale' => $locale,
			'mainIcon' => $params->get('main_icon'),
			'msgFromAddress' => Text::_('From address:'),
			'msgGetDirections' => Text::_('Get directions'),
			'msgEmpty' => Text::_('Please fill in your address.'),
			'msgNotFound' => Text::_('SORRY, ADDRESS NOT FOUND'),
			'msgAddressNotFound' => ', ' . Text::_('NOT FOUND')
		));

		// js parameters
		$javascript = "jQuery(function($) { $('#$maps_id').Googlemaps({$data}); });";

		// render layout
		if ($layout = $this->getLayout()) {
			return $this->renderLayout($layout, compact('maps_id', 'javascript', 'css_module_width', 'css_module_height', 'information', 'locale', 'key'));
		}

		return null;
	}

	/*
		Function: loadAssets
			Load elements css/js assets.

		Returns:
			Void
	*/
	public function loadAssets() {
        if ($key = $this->config->get('key')) {
            $this->app->system->document->addScript("https://maps.googleapis.com/maps/api/js?libraries=places&key={$key}&language={$this->config->get('locale')}&callback=Function.prototype");
        }
	}

	/*
	   Function: edit
	       Renders the edit form field.

	   Returns:
	       String - html
	*/
	public function edit() {
        if ($layout = $this->getLayout('edit.php')) {
            return $this->renderLayout($layout);
        }

        return null;
	}

	/*
		Function: renderSubmission
			Renders the element in submission.

	   Parameters:
            $params - AppData submission parameters

		Returns:
			String - html
	*/
	public function renderSubmission($params = array()) {
        return $this->edit();
	}

	/*
		Function: validateSubmission
			Validates the submitted element

	   Parameters:
            $value  - AppData value
            $params - AppData submission parameters

		Returns:
			Array - cleaned value
	*/
	public function validateSubmission($value, $params) {
        $validator = $this->app->validator->create('textfilter', array('required' => $params->get('required')), array('required' => 'Please enter a location'));
        return array('location' => $validator->clean($value->get('location')), 'latlng' => $validator->clean($value->get('latlng')));
    }
}
