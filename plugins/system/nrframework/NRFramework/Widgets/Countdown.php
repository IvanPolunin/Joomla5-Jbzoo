<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Widgets;

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

/**
 * Countdown
 */
class Countdown extends Widget
{
	/**
	 * Widget default options
	 *
	 * @var array
	 */
	protected $widget_options = [
		/**
		 * The Countdown type:
		 * 
		 * - static: Counts down to a specific date and time. Universal deadline for all visitors.
		 * - evergreen: Set-and-forget solution. The countdown starts when your visitor sees the offer.
		 */
		'countdown_type' => 'static',

		// The Static Countdown Date
		'value' => '',

		/**
		 * The timezone that will be used.
		 * 
		 * - server - Use server's timezone
		 * - client - Use client's timezone
		 */
		'timezone' => 'server',

		// Dynamic Days
		'dynamic_days' => 0,

		// Dynamic Hours
		'dynamic_hours' => 0,

		// Dynamic Minutes
		'dynamic_minutes' => 0,

		// Dynamic Seconds
		'dynamic_seconds' => 0,
		
		/**
		 * The countdown format.
		 * 
		 * Available tags:
		 * {years}
		 * {months}
		 * {days}
		 * {hours}
		 * {minutes}
		 * {seconds}
		 */
		'format' => '{days} days, {hours} hours, {minutes} minutes and {seconds} seconds',

		/**
		 * The countdown theme.
		 * 
		 * Available themes:
		 * default
		 * oneline
		 * custom
		 */
		'theme' => 'default',

		/**
		 * Set the action once countdown finishes.
		 * 
		 * Available values:
		 * keep 	- Keep the countdown visible
		 * hide 	- Hide the countdown
		 * restart 	- Restart the countdown
		 * message	- Show a message
		 * redirect	- Redirect to a URL
		 */
		'countdown_action' => 'keep',

		/**
		 * The message appearing after the countdown has finished.
		 * 
		 * Requires `countdown_action` to be set to `message`
		 * 
		 * Example: Countdown finished.
		 */
		'finish_text' => '',

		/**
		 * The redirect URL once the countdown expires.
		 * 
		 * Requires `countdown_action` to be set to `redirect`
		 */
		'redirect_url' => '',

		/**
		 * Widget Settings
		 */
		// Gap
		'gap' => 20,
		
		// Background Color
		'background_color' => '',

		/**
		 * Unit Display Settings
		 */
		// Whether to display Days
		'days' => true,

		// Days Label
		'days_label' => 'Days',
		
		// Whether to display Hours
		'hours' => true,

		// Hours Label
		'hours_label' => 'Hrs',
		
		// Whether to display Minutes
		'minutes' => true,

		// Minutes Label
		'minutes_label' => 'Mins',
		
		// Whether to display Seconds
		'seconds' => true,
		
		// Seconds Label
		'seconds_label' => 'Secs',
		
		// Whether to display a separator between the units
		'separator' => false,
		
		// Whether to display numbers in 00 or 0 format
		'double_zeroes_format' => true,

		/**
		 * Unit Item Settings
		 */
		// The size (width, height) of the unit item in pixels
		'item_size' => null,

		// Each item padding
		'item_padding' => null,
		
		// The unit item border width
		'item_border_width' => '',

		// The unit item border style
		'item_border_style' => '',

		// The unit item border color
		'item_border_color' => '',

		// The unit item border radius
		'item_border_radius' => null,

		// Item Background Color
		'item_background_color' => '',

		/**
		 * Unit Digits Container Settings
		 */
		// Digits wrapper Min Width
		'digits_wrapper_min_width' => 0,

		// The digits wrapper padding
		'digits_wrapper_padding' => null,

		// The digits wrapper border radius
		'digits_wrapper_border_radius' => null,

		// The digits wrapper background color.
		'digits_wrapper_background_color' => '',

		/**
		 * Unit Digit Settings
		 */
		// Digits Font Size
		'digits_font_size' => 25,

		// Digits Font Weight
		'digits_font_weight' => '400',

		// Digit Min Width
		'digit_min_width' => 0,

		// The digits padding
		'digits_padding' => null,

		// The digits border radius
		'digit_border_radius' => null,

		// Digits Gap
		'digits_gap' => null,

		// Digit Item Background Color. This applies for each of the 2 digits on a unit.
		'digit_background_color' => '',

		// Digit Item Text Color
		'digit_text_color' => '',

		/**
		 * Unit Label Settings
		 */
		// Label Font Size
		'label_font_size' => 13,

		// Label Font Weight
		'label_font_weight' => '400',

		// Unit Label Margin Top. The spacing between the unit and its label.
		'unit_label_margin_top' => 5,

		// Unit Label Color
		'unit_label_text_color' => '',

		// Extra attributes added to the widget
		'atts' => '',

		// TODO: Remove in the future
		'css_vars' => [],

		// Preview HTML used prior to JS initializing the Countdown
		'preview_html' => ''
	];

	/**
	 * Class constructor
	 *
	 * @param array $options
	 */
	public function __construct($options = [])
	{
		parent::__construct($options);

        Text::script('NR_AND_LC');

		$this->prepare();
		
		if ($this->options['load_css_vars'] && $this->options['theme'] !== 'custom')
		{
			$this->options['custom_css'] .= $this->getWidgetCSS();

			/**
			 * TODO: Remove in the future
			 * 
			 * For compatibility purposes for old customers using old
			 * ACF version used by ACF Previewer which is required to
			 * style the Countdown in the previewer.
			 */
			$this->options['css_vars'] = $this->options['custom_css'];
		}
	}

	/**
	 * Prepares the countdown.
	 * 
	 * @return  void
	 */
	private function prepare()
	{
		$this->options['css_class'] .= ' is-preview ' . $this->options['theme'];

		if (!empty($this->options['value']) && $this->options['value'] !== '0000-00-00 00:00:00')
		{
			if ($this->options['countdown_type'] === 'static' && $this->options['timezone'] === 'server')
			{
				// Get timezone
				$tz = new \DateTimeZone(Factory::getApplication()->getCfg('offset', 'UTC'));

				// Convert given date time to UTC
				$this->options['value'] = date_create($this->options['value'], $tz)->setTimezone(new \DateTimeZone('UTC'))->format('c');
				
				// Apply server timezone
				$this->options['value'] = (new \DateTime($this->options['value']))->setTimezone($tz)->format('c');
			}
		}

		$this->options['preview_html'] = $this->getPreviewHTML();

		// Set countdown payload
		$payload = [
			'data-countdown-type="' . $this->options['countdown_type'] . '"',
			'data-value="' . $this->options['value'] . '"',
			'data-timezone="' . $this->options['timezone'] . '"',
			'data-separator="' . (json_decode($this->options['separator']) ? 'true' : 'false') . '"',
			'data-double-zeroes-format="' . (json_decode($this->options['double_zeroes_format']) ? 'true' : 'false') . '"',
			'data-dynamic-days="' . $this->options['dynamic_days'] . '"',
			'data-dynamic-hours="' . $this->options['dynamic_hours'] . '"',
			'data-dynamic-minutes="' . $this->options['dynamic_minutes'] . '"',
			'data-dynamic-seconds="' . $this->options['dynamic_seconds'] . '"',
			'data-finish-text="' . htmlspecialchars($this->options['finish_text']) . '"',
			'data-redirect-url="' . $this->options['redirect_url'] . '"',
			'data-theme="' . $this->options['theme'] . '"',
			'data-countdown-action="' . $this->options['countdown_action'] . '"',
			'data-days="' . (json_decode($this->options['days']) ? 'true' : 'false') . '"',
			'data-days-label="' . $this->options['days_label'] . '"',
			'data-hours="' . (json_decode($this->options['hours']) ? 'true' : 'false') . '"',
			'data-hours-label="' . $this->options['hours_label'] . '"',
			'data-minutes="' . (json_decode($this->options['minutes']) ? 'true' : 'false') . '"',
			'data-minutes-label="' . $this->options['minutes_label'] . '"',
			'data-seconds="' . (json_decode($this->options['seconds']) ? 'true' : 'false') . '"',
			'data-seconds-label="' . $this->options['seconds_label'] . '"'
		];

		// Only set the format for custom-themed countdown instances
		if ($this->options['theme'] === 'custom')
		{
			$payload[] = 'data-format="' . htmlspecialchars($this->options['format']) . '"';
		}

		$this->options['atts'] = implode(' ', $payload);
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
                'property' => '--digits-background-color',
                'value' => $this->options['digits_wrapper_background_color']
			],
            [
                'property' => '--background-color',
                'value' => $this->options['background_color']
			],
            [
                'property' => '--item-background-color',
                'value' => $this->options['item_background_color']
			],
            [
                'property' => '--unit-label-text-color',
                'value' => $this->options['unit_label_text_color']
			],
            [
                'property' => '--digit-background-color',
                'value' => $this->options['digit_background_color']
			],
            [
                'property' => '--digit-text-color',
                'value' => $this->options['digit_text_color']
			],
            [
                'property' => '--unit-label-margin-top',
                'value' => $this->options['unit_label_margin_top'],
				'unit' => 'px'
			],
            [
                'property' => '--digits-wrapper-min-width',
                'value' => $this->options['digits_wrapper_min_width'],
				'unit' => 'px'
			],
            [
                'property' => '--digit-min-width',
                'value' => $this->options['digit_min_width'],
				'unit' => 'px'
			],
            [
                'property' => '--digits-font-weight',
                'value' => $this->options['digits_font_weight']
			],
            [
                'property' => '--label-font-weight',
                'value' => $this->options['label_font_weight']
			],
            [
                'property' => '--item-border',
				'type' => 'Border',
                'value' => [
					'width' => $this->options['item_border_width'],
					'style' => $this->options['item_border_style'],
					'color' => $this->options['item_border_color'],
					'unit' => 'px'
				]
			],

			// CSS
            [
                'type' => 'Spacing',
                'property' => '--item-padding',
                'value' => $this->options['item_padding'],
				'unit' => 'px'
			],
            [
                'type' => 'Spacing',
                'property' => '--digits-padding',
                'value' => $this->options['digits_wrapper_padding'],
				'unit' => 'px'
			],
            [
                'property' => '--gap',
                'value' => $this->options['gap'],
				'unit' => 'px'
			],
            [
                'property' => '--digits-gap',
                'value' => $this->options['digits_gap'],
				'unit' => 'px'
			],
            [
                'property' => '--item-size',
                'value' => $this->options['item_size'],
				'unit' => 'px'
			],
            [
                'property' => '--digits-font-size',
                'value' => $this->options['digits_font_size'],
				'unit' => 'px'
			],
            [
                'property' => '--label-font-size',
                'value' => $this->options['label_font_size'],
				'unit' => 'px'
			],
            [
                'type' => 'Spacing',
                'property' => '--digit-padding',
                'value' => $this->options['digits_padding'],
				'unit' => 'px'
			],
            [
                'type' => 'Spacing',
                'property' => '--item-border-radius',
                'value' => $this->options['item_border_radius'],
				'unit' => 'px'
			],
            [
                'type' => 'Spacing',
                'property' => '--digits-border-radius',
                'value' => $this->options['digits_wrapper_border_radius'],
				'unit' => 'px'
			],
            [
                'type' => 'Spacing',
                'property' => '--digit-border-radius',
                'value' => $this->options['digit_border_radius'],
				'unit' => 'px'
			],
		];

		$selector = '.nrf-countdown.' . $this->options['id'];
		
		$controlsInstance = new \NRFramework\Controls\Controls(null, $selector, $exclude_breakpoints);
        
		if (!$controlsCSS = $controlsInstance->generateCSS($controls))
		{
			return;
		}

		return $controlsCSS;
	}

	/**
	 * Returns preview HTML.
	 * 
	 * @return  string
	 */
	private function getPreviewHTML()
	{
		if ($this->options['theme'] === 'custom')
		{
			return $this->options['format'];
		}

		$format_items = [
			'days' => $this->options['days'],
			'hours' => $this->options['hours'],
			'minutes' => $this->options['minutes'],
			'seconds' => $this->options['seconds']
		];

		$html = '';

		foreach ($format_items as $key => $value)
		{
			$labelStr = !empty($this->options[$key . '_label']) ? '<span class="countdown-digit-label">' . $this->options[$key . '_label'] . '</span>' : '';
			$html .= '<span class="countdown-item"><span class="countdown-digit ' . $key . '"><span class="digit-number digit-1">0</span><span class="digit-number digit-2">0</span></span>' . $labelStr . '</span>';
		}
		
		return $html;
	}

	/**
	 * Returns all CSS files.
	 * 
	 * @param   string  $theme
	 * 
	 * @return  array
	 */
	public static function getCSS($theme = 'default')
	{
		$css = [];
		
		if ($theme !== 'custom')
		{
			$css[] = 'plg_system_nrframework/widgets/countdown.css';
		}
		else
		{
			$css[] = 'plg_system_nrframework/widgets/widget.css';
		}

		return $css;
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
			'plg_system_nrframework/widgets/countdown.js'
		];
	}
}