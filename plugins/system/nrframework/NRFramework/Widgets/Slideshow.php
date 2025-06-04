<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use \NRFramework\Helpers\Widgets\Gallery as GalleryHelper;
use NRFramework\Mimes;
use NRFramework\File;
use NRFramework\Image;
use Joomla\CMS\Factory;

class Slideshow extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		// Slides per view
		'slides_per_view' => [
			'desktop' => 1
		],

		// Space between slides in px
		'space_between_slides' => [
			'desktop' => 10
		],

		// Enable Infinite Loop
		'infinite_loop' => false,

		// Enable Keyboard Control
		'keyboard_control' => false,

		/**
		 * Set the ordering.
		 * 
		 * Available values:
		 * - default
		 * - alphabetical
		 * - reverse_alphabetical
		 * - random
		 */
		'ordering' => 'default',

		/**
		 * The transition effect.
		 * 
		 * Available values:
		 * - slide
		 * - fade
		 * - cube
		 * - coverflow
		 * - flip
		 */
		'transition_effect' => 'slide',

		// Enable Autoplay
		'autoplay' => false,

		// Autoplay delay
		'autoplay_delay' => 3000,

		// Enable autoplay circular progress
		'autoplay_progress' => false,

		// Show thumbnails below the slideshow
		'show_thumbnails' => false,

		// Set whether to show arrows in the thumbnails slider
		'show_thumbnails_arrows' => false,

		/**
		 * Navigation controls.
		 * 
		 * Accepted values:
		 * - arrows
		 * - dots
		 * - arrows_dots
		 */
		'nav_controls' => false,
		
		// Theme color
		'theme_color' => '#007aff',

		// Set whether to display a lightbox
		'lightbox' => false,
		
		// Set the module key to display whenever we are viewing a single item's lightbox, appearing after the image
		'module' => '',

		// Options
		'options' => []
	];

	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->prepare();

		$this->prepareItems();

		$this->setOrdering();

		$this->setCSSVars();
	}

	private function prepare()
	{
		if ($this->options['lightbox'])
		{
			$this->options['css_class'] .= ' lightbox';
		}
		
		$options = [
			'transition_effect' => $this->options['transition_effect'],
			'infinite_loop' => $this->options['infinite_loop'],
			'keyboard_control' => $this->options['keyboard_control'],
			'autoplay' => $this->options['autoplay'],
			'autoplay_delay' => $this->options['autoplay_delay'],
			'autoplay_progress' => $this->options['autoplay_progress'],
			'show_thumbnails' => $this->options['show_thumbnails'],
			'show_thumbnails_arrows' => $this->options['show_thumbnails_arrows'],
			'lightbox' => $this->options['lightbox'],
			'breakpoints' => \NRFramework\Helpers\Responsive::getBreakpointsSettings(),
			'slides_per_view' => $this->options['slides_per_view'],
			'space_between_slides' => $this->getSpaceBetweenSlides(),
			'nav_controls' => $this->options['nav_controls']
		];

		$this->options['options'] = $options;
	}

	private function getSpaceBetweenSlides()
	{
		$space_between_slides = $this->options['space_between_slides'];

		if (is_array($space_between_slides))
		{
			foreach ($space_between_slides as $key => &$value)
			{
				$value = \NRFramework\Helpers\Controls\Control::getCSSValue($value['value']);
			}
		}

		return $space_between_slides;
	}

	/**
	 * Prepares the items.
	 * 
	 * - Sets the thumbnails image dimensions.
	 * - Assures caption property exist.
	 * 
	 * @return  mixed
	 */
	private function prepareItems()
	{
		if (!is_array($this->options['items']) || !count($this->options['items']))
		{
			return;
		}

		foreach ($this->options['items'] as $key => &$item)
		{
			// Initialize image atts
			$item['img_atts'] = '';

			// Initializes caption if none given
			if (!isset($item['caption']))
			{
				$item['caption'] = '';
			}

            $item['alt'] = !empty($item['caption']) ? mb_substr($item['caption'], 0, 100) : pathinfo($item['url'], PATHINFO_FILENAME);
			
			// Ensure a thumbnail is given
			if (!isset($item['thumbnail_url']))
			{
				// If no thumbnail is given, set it to the full image
				$item['thumbnail_url'] = $item['url'];
				continue;
			}

			// If the thumbnail size for this item is given, set the image attributes
			if (isset($item['thumbnail_size']))
			{
				$item['img_atts'] = 'width="' . $item['thumbnail_size']['width'] . '" height="' . $item['thumbnail_size']['height'] . '"';
				continue;
			}
		}
	}
	
	/**
	 * Sets the ordering of the gallery.
	 * 
	 * @return  void
	 */
	private function setOrdering()
	{
		switch ($this->options['ordering']) {
			case 'random':
				shuffle($this->options['items']);
				break;
			case 'alphabetical':
				usort($this->options['items'], [$this, 'compareByThumbnailASC']);
				break;
			case 'reverse_alphabetical':
				usort($this->options['items'], [$this, 'compareByThumbnailDESC']);
				break;
		}
	}

	/**
	 * Compares thumbnail file names in ASC order
	 * 
	 * @param   array  $a
	 * @param   array  $b
	 * 
	 * @return  bool
	 */
	private function compareByThumbnailASC($a, $b)
	{
		return strcmp(basename($a['thumbnail']), basename($b['thumbnail']));
	}

	/**
	 * Compares thumbnail file names in DESC order
	 * 
	 * @param   array  $a
	 * @param   array  $b
	 * 
	 * @return  bool
	 */
	private function compareByThumbnailDESC($a, $b)
	{
		return strcmp(basename($b['thumbnail']), basename($a['thumbnail']));
	}

	/**
	 * Sets the CSS variables.
	 * 
	 * @return  void
	 */
	private function setCSSVars()
	{
		if (!$this->options['load_css_vars'])
		{
			return;
		}
	
		$controls = [
            [
                'property' => '--slideshow-slides-per-view',
                'value' => $this->options['slides_per_view']
            ],
            [
                'property' => '--slideshow-space-between-slides',
                'value' => $this->options['space_between_slides']
            ]
		];

		$selector = '.nrf-widget.tf-slideshow-wrapper.' . $this->options['id'];
		
		$controlsInstance = new \NRFramework\Controls\Controls(null, $selector);

        if (!$controlsCSS = $controlsInstance->generateCSS($controls))
        {
            return;
        }

        Factory::getDocument()->addStyleDeclaration($controlsCSS);
	}
}