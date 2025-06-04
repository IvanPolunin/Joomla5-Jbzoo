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

class FAQ extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		/**
		 * FAQ Settings
		 */

		/**
		 * The questions and answers.
		 * 
		 * Format:
		 * 
		 * [
		 *  	[
		 * 			'question' => 'Question 1',
		 * 			'answer' => 'Answer 1'
		 * 		],
		 *  	[
		 * 			'question' => 'Question 2',
		 * 			'answer' => 'Answer 2'
		 * 		],
		 * ]
		 */ 
		'value' => '',

		/**
		 * Requires "show_toggle_icon" to be enabled to work.
		 * 
		 * Define the initial state of the FAQ.
		 * 
		 * Available values:
		 * 
		 * - first-open: Open the first question
		 * - all-open: Opens all questions
		 * - all-closed: Closes all questions
		 */
		'initial_state' => 'first-open',

		// Set whether to have one question open at a time
		'keep_one_question_open' => true,

		// Set the columns.
		'columns' => 1,

		// Set the gap between the items.
		'item_gap' => 16,

		// Set the gap between the columns.
		'column_gap' => 16,

		// Set whether to display a separator between items
		'separator' => false,

		// Set the separator color
		'separator_color' => '',

		/**
		 * Item Settings
		 */
		// Each item background color.
		'item_background_color' => null,

		// Each item border radius.
		'item_border_radius' => null,

		// Each item padding.
		'item_padding' => null,

		/**
		 * Question
		 */
		// Question font size
		'question_font_size' => null,

		// Each question text color.
		'question_text_color' => null,

		/**
		 * Answer
		 */
		// Answer font size
		'answer_font_size' => null,

		// Each answer text color.
		'answer_text_color' => null,

		/**
		 * Icon Settings
		 */
		/**
		 * Whether to show an icon that can toggle the open/close state of the answer.
		 * 
		 * If disabled, all answers will appear by default.
		 * If enabled, all answers will be hidden by default.
		 */
		'show_toggle_icon' => false,

		/**
		 * Set the icon that will be used.
		 * 
		 * Available values:
		 * - arrow
		 * - plus_minus
		 * - circle_arrow
		 * - circle_plus_minus
		 */
		'icon' => 'arrow',

		/**
		 * Set the icon position.
		 * 
		 * Available values:
		 * 
		 * - right
		 * - left
		 */
		'icon_position' => 'right',

		/**
		 * FAQ Schema
		 */
		// Set whether to generate the FAQ Schema on the page.
		'generate_faq' => false,

		// Custom Item CSS Classes
		'item_css_class' => ''
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
		if ($this->options['show_toggle_icon'])
		{
			$this->options['show_toggle_icon'] = true;
			$this->options['css_class'] .= ' has-icons';
			$this->options['css_class'] .= ' position-icon-' . $this->options['icon_position'];
			$this->options['css_class'] .= ' has-icon-' . $this->options['icon'];
		}

		if (!empty($this->options['item_background_color']) && $this->options['item_background_color'] !== 'none')
		{
			$this->options['css_class'] .= ' has-item-bg-color';
		}

		if ($this->options['separator'])
		{
			$this->options['css_class'] .= ' has-separator';
		}

		$this->options['css_class'] .= ' ' . $this->options['initial_state'];

		if ($this->options['keep_one_question_open'])
		{
			$this->options['css_class'] .= ' keep-one-question-open';
		}

		if ((int) $this->options['columns'] > 1)
		{
			$this->options['css_class'] .= ' has-columns';
		}

		$this->generateFAQ();
		
		if ($this->options['load_css_vars'])
		{
			$this->options['custom_css'] = $this->getWidgetCSS();
		}
	}

	private function generateFAQ()
	{
		// Ensure "generate_faq" is enabled
		if (!$this->options['generate_faq'])
		{
			return;
		}
		
		// Ensure we have questions and answers
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
		
		// Prepare the FAQ
		$payload = [
			'mode' => 'manual',
			'faq_repeater_fields' => json_decode(json_encode($this->options['value']))
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
		$controls = [
			// CSS Variables
            [
                'property' => '--item-background-color',
                'value' => $this->options['item_background_color']
			],
            [
                'property' => '--question-text-color',
                'value' => $this->options['question_text_color']
			],
            [
                'property' => '--answer-text-color',
                'value' => $this->options['answer_text_color']
			],
            [
                'property' => '--separator-color',
                'value' => $this->options['separator_color']
			],

			// CSS
            [
                'property' => '--item-padding',
				'type' => 'Spacing',
                'value' => $this->options['item_padding'],
				'unit' => 'px'
			],
            [
                'property' => '--item-gap',
                'value' => $this->options['item_gap'],
				'unit' => 'px'
			],
            [
                'property' => '--column-gap',
                'value' => $this->options['column_gap'],
				'unit' => 'px'
			],
            [
                'property' => '--item-border-radius',
				'type' => 'Spacing',
                'value' => $this->options['item_border_radius'],
				'unit' => 'px'
			],
            [
                'property' => '--question-font-size',
                'value' => $this->options['question_font_size'],
				'unit' => 'px'
			],
            [
                'property' => '--answer-font-size',
                'value' => $this->options['answer_font_size'],
				'unit' => 'px'
			],
		];

		$selector = '.tf-faq-widget.' . $this->options['id'];
		
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
			'plg_system_nrframework/widgets/faq.css'
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
			'plg_system_nrframework/widgets/faq.js'
		];
	}
}