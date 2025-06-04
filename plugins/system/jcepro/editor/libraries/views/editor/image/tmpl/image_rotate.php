<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (C) 2005 - 2020 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

?>
<h3 id="transform-rotate" data-action="rotate">
	<a href="#">
		<?php echo Text::_('WF_MANAGER_TRANSFORM_ROTATE', 'Rotate'); ?>
	</a>
</h3>
<div class="uk-form">
	<div class="uk-grid uk-grid-mini">
		<label for="rotate-angle" class="uk-width-2-10"><?php echo Text::_('WF_MANAGER_TRANSFORM_ROTATE', 'Rotate'); ?></label>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-angle-clockwise"><i class="uk-icon uk-icon-redo"></i><span class="uk-text"><?php echo Text::_('WF_MANAGER_TRANSFORM_ROTATE_RIGHT', 'Right'); ?></span></button>
		</div>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-angle-anticlockwise"><i class="uk-icon uk-icon-undo"></i><span class="uk-text"><?php echo Text::_('WF_MANAGER_TRANSFORM_ROTATE_LEFT', 'Left'); ?></span></button>
		</div>
	</div>
	<div class="uk-grid uk-grid-mini">
		<label for="rotate-flip" class="uk-width-2-10"><?php echo Text::_('WF_MANAGER_TRANSFORM_FLIP', 'Flip'); ?></label>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-flip-vertical"><i class="uk-icon uk-icon-flipv"></i><span class="uk-text"><?php echo Text::_('WF_MANAGER_TRANSFORM_FLIP_VERTICAL', 'Vertical'); ?></span></button>
		</div>
		<div class="uk-width-4-10">
			<button type="button" class="uk-button uk-width-1-1 uk-flex uk-flex-middle uk-flex-space-around" id="rotate-flip-horizontal"><i class="uk-icon uk-icon-fliph"></i><span class="uk-text"><?php echo Text::_('WF_MANAGER_TRANSFORM_FLIP_HORIZONTAL', 'Horizontal'); ?></span></button>
		</div>
	</div>
</div>