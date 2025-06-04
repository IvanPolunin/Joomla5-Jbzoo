<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2022 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework\Controls;

defined('_JEXEC') or die;

class Controls
{
	/**
	 * The Control Factory.
	 * 
	 * @var  ControlFactory
	 */
	protected $factory;

	/**
	 * The main selector that will be used for all controls generated CSS.
	 * 
	 * Each control can override this by setting the "selector" property.
	 * 
	 * @var  string
	 */
	protected $selector;

	/**
	 * Define which breakpoints to exclude from the CSS generation.
	 * 
	 * @var  array
	 */
	protected $exclude_breakpoints = [];

	public function __construct($factory = null, $selector = null, $exclude_breakpoints = [])
	{
		if (!$factory)
		{
			$factory = new ControlFactory();
		}
		$this->factory = $factory;

		$this->selector = $selector;
		$this->exclude_breakpoints = $exclude_breakpoints;
	}
	
	public function generateCSS($controls = [])
	{
		$cssArray = $this->getCSSArray($controls);

		// Get the final CSS
		return \NRFramework\Helpers\Controls\CSS::generateCSS($cssArray);
    }

	protected function getCSSArray($controls = [])
	{
		if (!$controls || !is_array($controls))
		{
			return;
		}

		$parsedControls = [];
		
        $cssArray = [
			'desktop' => [],
			'tablet' => [],
			'mobile' => []
		];

		// Get the responsive CSS for each control
        foreach ($controls as $control_payload)
		{
			// Set any breakpoints to exclude when generating CSS
			$control_payload['exclude_breakpoints'] = $this->exclude_breakpoints;
			
			// Set the selector
			if (!isset($control_payload['selector']))
			{
				$control_payload['selector'] = $this->selector;
			}

			$control_payload['parsedControls'] = $parsedControls;
			
            if (!$control = $this->factory->createControl($control_payload))
			{
				continue;
			}

			if (!$control_css = $control->getCSS())
			{
				continue;
			}

			$selector = $control->getSelector();

			if (isset($control_payload['property']))
			{
				$parsedControls[] = [
					'property' => $control_payload['property'],
					'control' => $control
				];
			}

			if ($control->isResponsive())
			{
				foreach ($control_css as $breakpoint => $control_payload)
				{
					if (is_null($control_payload))
					{
						continue;
					}
		
					$cssArray[$breakpoint][] = [
						'selector' => $selector,
						'css' => $control_payload
					];
				}
			}
			else
			{
				$cssArray['desktop'][] = [
					'selector' => $selector,
					'css' => $control_css
				];
			}
        }

		return $cssArray;
	}
}