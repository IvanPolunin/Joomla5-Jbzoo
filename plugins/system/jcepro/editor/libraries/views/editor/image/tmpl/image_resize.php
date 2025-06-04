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
<h3 id="transform-resize" data-action="resize">
	<a href="#">
		<?php echo Text::_('WF_MANAGER_TRANSFORM_RESIZE', 'Resize'); ?>
	</a>
</h3>
<div class="uk-form">
	<div class="uk-grid uk-grid-small uk-form-constrain">
		<label for="resize_width" class="uk-width-1-5">
        	<?php echo Text::_('WF_LABEL_WIDTH'); ?>
        </label>

		<div class="uk-width-4-5">
			<div class="uk-form-controls uk-width-1-4">
				<input type="text" id="resize_width" value="" class="uk-text-center" />
			</div>

			<label class="uk-float-left uk-text-bold uk-width-1-10 uk-text-center">&times;</label>

			<label for="resize_height" class="uk-float-left uk-width-1-4 uk-margin-small-left">
				<?php echo Text::_('WF_LABEL_HEIGHT'); ?>
			</label>

			<div class="uk-form-controls uk-width-1-4 uk-margin-small-left">
				<input type="text" id="resize_height" value="" class="uk-text-center" />
			</div>
		</div>
	</div>
	<div class="uk-grid uk-grid-small">
		<label for="resize_presets" class="uk-width-1-5">
			<?php echo Text::_('WF_LABEL_PRESETS'); ?>
		</label>
		<div class="uk-width-4-5">
			<select id="resize_presets" class="uk-width-1-1">
				<option value="1:1"><?php echo Text::_('WF_MANAGER_TRANSFORM_PRESET_ORIGINAL'); ?></option>
				<?php foreach ($this->lists['resize'] as $option): ?>
					<option value="<?php echo $option; ?>"><?php echo $option; ?></option>
				<?php endforeach;?>
			</select>
		</div>
	</div>
	<div class="uk-form-row uk-align-right uk-margin-top uk-margin-bottom">
		<button  type="button" id="resize_apply" class="uk-button uk-button-primary apply" data-function="resize">
			<?php echo Text::_('WF_LABEL_APPLY'); ?>
		</button>
		<button  type="button" id="resize_reset" class="uk-button reset" data-function="resize">
			<?php echo Text::_('WF_LABEL_RESET'); ?>
		</button>
	</div>
</div>