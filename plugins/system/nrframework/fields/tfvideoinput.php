<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2020 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Form\Field\TextField;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

class JFormFieldTFVideoInput extends TextField
{
    /**
	 * Renders the input field with the video previewer.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$this->setHint();

		$previewer_enabled = isset($this->element['previewer_enabled']) ? (string) $this->element['previewer_enabled'] : 'true';
		if ($previewer_enabled !== 'true')
		{
			return parent::getInput();
		}

		$provider = (string) $this->element['provider'];
		
		$this->class = 'tf-video-url-input-value';

		if (!defined('nrJ4'))
		{
			$this->class .= ' input-xxlarge';
		}
		
		$this->assets();

		$input = '';
		
		if ($provider === 'SelfHostedVideo' && defined('nrJ4'))
		{
			$xml = '
				<field name="' . $this->fieldname . '"
					class="tf-video-url-input-value"
					type="media"
					preview="false"
					types="videos"
					hiddenLabel="true"
				/>
			';

			$this->form->setField(new SimpleXMLElement($xml));
			$field = $this->form->getField($this->fieldname, null, $this->value);
			$field->name = $this->name;
			$field->id = $this->id;
			
			$input = $field->getInput();
		}
		else
		{
			$input = parent::getInput();
		}
		
		return $input . $this->getPreviewerHTML();
	}

	private function getProvider()
	{
		return $this->element['provider'] ? (string) $this->element['provider'] : '';
	}

	private function setHint()
	{
		switch ($this->getProvider())
		{
			case 'YouTube':
				$this->hint = 'https://www.youtube.com/watch?v=IWVJq-4zW24';
				break;
			case 'Vimeo':
				$this->hint = 'https://vimeo.com/146782320';
				break;
			case 'Dailymotion':
				$this->hint = 'https://www.dailymotion.com/video/x8mvsem';
				break;
			case 'Facebook':
				$this->hint = 'https://www.facebook.com/watch/?v=441279306439983';
				break;
			case 'SelfHostedVideo':
				$this->hint = '/media/video.mp4';
				break;
		}
	}

	private function assets()
	{
		HTMLHelper::stylesheet('plg_system_nrframework/tf-video-input.css', ['relative' => true, 'version' => 'auto']);
		HTMLHelper::script('plg_system_nrframework/tf-video-input.js', ['relative' => true, 'version' => 'auto']);
	}

	private function getPreviewerHTML()
	{
		return '<div class="tf-video-input-previewer-wrapper" data-provider="' . $this->getProvider() . '" data-root-url="' . Uri::root() . '" title="' . Text::_('NR_VIDEO_PREVIEW_VIDEO') . '"></div>';
	}
}
