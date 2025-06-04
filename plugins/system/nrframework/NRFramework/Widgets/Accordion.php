<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\CMS\HTML\HTMLHelper;

class Accordion extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		/**
		 * Accordion Settings
		 */

		/**
		 * The titles and contents of the accordion items.
		 * 
		 * Format:
		 * 
		 * [
		 *  	[
		 * 			'title' => 'Title 1',
		 * 			'content' => 'Content 1'
		 * 		],
		 *  	[
		 * 			'title' => 'Title 2',
		 * 			'content' => 'Content 2'
		 * 		],
		 * ]
		 */ 
		'value' => '',

		/**
		 * Choose how spacious or compact you'd like to list accordion.
		 * Consider this the padding of each accordion item.
		 * 
		 * Available values:
		 * - none
		 * - default
		 * - comfortable
		 * - compact
		 */
		'density' => 'default',

		// Set the font size.
		'font_size' => '16px',

		/**
		 * Set the gap between the items.
		 * 
		 * Available values:
		 * - none
		 * - small
		 * - large
		 */
		'gap' => 'none',

		// Set the background color of the panel.
		'panel_background_color' => '#fff',

		// Set the color of the text and the icon.
		'text_color' => '#333',

		// Set the color of the 1px border that affects both item and container. Set to 'none' for no border color.
		'border_color' => '#ddd',

		/**
		 * Set the rounded corners of the items.
		 * 
		 * Available values:
		 * - none
		 * - small
		 * - large
		 */
		'rounded_corners' => 'small',

		/**
		 * Item Icon Settings
		 */
		/**
		 * Set whether to display a toggle icon next to the title, or not.
		 * 
		 * Available values:
		 * - none
		 * - left
		 * - right
		 */
		'show_icon' => 'left',

		/**
		 * Set the icon URL.
		 */
		'icon' => '',

		/**
		 * Behavior
		 */
		/**
		 * Define the initial state of the accordion.
		 * By default all items are initially shown collapsed.
		 * 
		 * Available values:
		 * 
		 * - collapsed: All panels appear as collapsed.
		 * - expanded: All panels appear as expanded.
		 * - expanded-first: Expand the first panel only.
		 */
		'initial_state' => 'collapsed',

		// Set whether to allow only one panel to be expanded at a time.
		'only_one_panel_expanded' => false,
		
		// Set whether to generate the FAQ Schema on the page.
		'generate_faq' => false,

		// Custom Panel CSS Class
		'panel_css_class' => ''
	];

	/**
	 * Class constructor
	 *
	 * @param array $options
	 */
	public function __construct($options = [])
	{
		parent::__construct($options);

		$this->prepare();
	}

	/**
	 * Prepares the FAQ.
	 * 
	 * @return  void
	 */
	private function prepare()
	{
		$this->validateValue();
		
		$this->generateFAQ();
		
		// Set density
		switch ($this->options['density'])
		{
			case 'none':
				$this->options['density'] = 0;
				break;
			case 'default':
				$this->options['density'] = '1em 1.25em';
				break;
			case 'comfortable':
				$this->options['density'] = '1.25em 1.75em';
				break;
			case 'compact':
				$this->options['density'] = '.65em 1.25em';
				break;
		}
		
		// Set gap
		switch ($this->options['gap'])
		{
			case 'none':
				$this->options['gap'] = 0;
				break;
			case 'small':
				$this->options['gap'] = '.3em';
				break;
			case 'large':
				$this->options['gap'] = '.7em';
				break;
		}
		
		// Set rounded corners
		switch ($this->options['rounded_corners'])
		{
			case 'none':
				$this->options['rounded_corners'] = 0;
				break;
			case 'small':
				$this->options['rounded_corners'] = '.3em';
				break;
			case 'large':
				$this->options['rounded_corners'] = '.7em';
				break;
		}

		if ($this->options['only_one_panel_expanded'])
		{
			$this->options['css_class'] .= ' only-one-panel-expanded';
		}
		
		if ($this->options['load_css_vars'])
		{
			$this->options['custom_css'] = $this->getWidgetCSS();
		}
	}

	/**
	 * Validates the value.
	 * 
	 * @return  void
	 */
	private function validateValue()
	{
		if (!is_array($this->options['value']))
		{
			return;
		}

		$st = new \NRFramework\SmartTags\SmartTags();
		
		foreach ($this->options['value'] as $key => &$val)
		{
			if ((isset($val['title']) && empty($val['title'])) && (isset($val['content'])) && empty($val['content']))
			{
				unset($this->options['value'][$key]);
			}

			if ($this->options['pro'])
			{
				$val['title'] = HTMLHelper::_('content.prepare', $val['title']);
				$val['content'] = HTMLHelper::_('content.prepare', $val['content']);
			}
		}
	}

	/**
	 * Generates the FAQ.
	 * 
	 * @return  void
	 */
	private function generateFAQ()
	{
		// Ensure "generate_faq" is enabled
		if (!$this->options['generate_faq'])
		{
			return;
		}
		
		// Ensure we have value
		if (!is_array($this->options['value']) && !count($this->options['value']))
		{
			return;
		}

		// Abort if FAQ cannot be compiled
		if (!$faq = $this->getFAQ())
		{
			return;
		}

		// Hook into GSD to add the FAQ
		Factory::getApplication()->registerEvent('onGSDBeforeRender', function(&$data) use ($faq)
		{
			try
			{
				// get the data
				$tmpData = $data;
				if (defined('nrJ4'))
				{
					$tmpData = $data->getArgument('0');
				}

				// Append the FAQ Schema
				$tmpData[] = $faq;

				// Ensure unique FAQ
				$tmpData = array_unique($tmpData);
				
				// Set back the new value to $data object
				if (defined('nrJ4'))
				{
					$data->setArgument(0, $tmpData);
				}
				else
				{
					$data = $tmpData;
				}

			} catch (\Throwable $th)
			{
				$this->throwError($th->getMessage());
			}
		});
	}

	/**
	 * Returns the FAQ JSON/LD code.
	 * 
	 * @return  string
	 */
	private function getFAQ()
	{
		$autoload_file = JPATH_ADMINISTRATOR . '/components/com_gsd/autoload.php';
		if (!file_exists($autoload_file))
		{
			return;
		}

		require_once $autoload_file;

		$value = $this->options['value'];
		if (is_array($value))
		{
			foreach ($value as $key => &$val)
			{
				if (isset($val['title']))
				{
					$val['question'] = $val['title'];
					unset($val['title']);
				}

				if (isset($val['content']))
				{
					$val['answer'] = $val['content'];
					unset($val['content']);
				}
			}
		}
		
		// Prepare the FAQ
		$payload = [
			'mode' => 'manual',
			'faq_repeater_fields' => json_decode(json_encode($value))
		];
		$payload = new Registry($payload);
		$faq = new \GSD\Schemas\Schemas\FAQ($payload);

		// Get the JSON/LD code of the FAQ
		$json = new \GSD\Json($faq->get());

		// Return the code
		return $json->generate();
	}

	/**
	 * Returns the CSS for the widget.
	 * 
	 * @param   array  $exclude_breakpoints   Define breakpoints to exclude their CSS
	 * 
	 * @return  string
	 */
	public function getWidgetCSS($exclude_breakpoints = [])
	{
		$border_color = $this->options['border_color'] !== 'none' ? $this->options['border_color'] : 'transparent';

		$controls = [];
		
		// If no density is set, set the padding-top to 5px
		if (!$this->options['density'])
		{
			$controls[] = [
				'property' => '--content-padding-top',
				'value' => '5px'
			];
		}

		if (!$this->options['gap'])
		{
			$controls[] = [
				'property' => '--container-border-color',
				'value' => $border_color
			];

			$this->options['css_class'] .= ' no-gap';
		}
		
		$controls = array_merge($controls, [
			// CSS Variables
            [
                'property' => '--panel-background-color',
                'value' => $this->options['panel_background_color']
			],
            [
                'property' => '--text-color',
                'value' => $this->options['text_color']
			],
            [
                'property' => '--panel-border-color',
                'value' => $border_color
			],

			// CSS
            [
                'property' => '--padding',
				'type' => 'Spacing',
                'value' => $this->options['density']
			],
            [
                'property' => '--gap',
                'value' => $this->options['gap'],
				'unit' => 'px'
			],
            [
                'property' => '--rounded-corners',
				'type' => 'Spacing',
                'value' => $this->options['rounded_corners'],
				'unit' => 'px'
			],
            [
                'property' => '--font-size',
                'value' => $this->options['font_size'],
				'unit' => 'px'
			]
		]);

		$selector = '.tf-accordion-widget.' . $this->options['id'];
		
		$controlsInstance = new \NRFramework\Controls\Controls(null, $selector, $exclude_breakpoints);
        
		if (!$controlsCSS = $controlsInstance->generateCSS($controls))
		{
			return;
		}

		return $controlsCSS;
	}

	/**
	 * Returns all CSS files.
	 * 
	 * @return  array
	 */
	public static function getCSS()
	{
		return [
			'plg_system_nrframework/widgets/accordion.css'
		];
	}

	/**
	 * Returns all JS files.
	 * 
	 * @param   string  $theme
	 * 
	 * @return  array
	 */
	public static function getJS()
	{
		return [
			'plg_system_nrframework/widgets/accordion.js'
		];
	}
}