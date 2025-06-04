<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// load js
use Joomla\CMS\Language\Text;

$this->app->document->addScript('assets:js/image.js');

// init vars
$width 	= $parent->getValue($name.'_width');
$height = $parent->getValue($name.'_height');

// create image select html
$html[] = '<input class="image-select" type="text" name="'.$control_name.'['.$name.']'.'" value="'.$value.'" />';
$html[] = '<div class="image-measures">';
$html[] = Text::_('Width').' <input type="text" name="'.$control_name.'['.$name.'_width]'.'" value="'.$width.'" style="width:65px;" />';
$html[] = Text::_('Height').' <input type="text" name="'.$control_name.'['.$name.'_height]'.'" value="'.$height.'" style="width:65px;" />';
$html[] = '</div>';

echo implode("\n", $html);
