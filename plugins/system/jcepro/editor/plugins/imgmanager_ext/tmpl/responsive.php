<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

?>

<div class="uk-repeatable uk-placeholder uk-position-relative uk-margin-top-remove uk-margin-small-bottom uk-grid uk-grid-collapse">

  <div class="uk-form-row uk-grid uk-grid-collapse uk-width-9-10">
    <label for="responsive_media_query" class="uk-form-label uk-width-2-10"><?php echo Text::_('WF_IMGMANAGER_EXT_LABEL_SOURCE', 'Source'); ?></label>
    <div class="uk-width-3-4 uk-form-controls uk-grid uk-grid-small">
      <div class="uk-width-4-6">
        <input type="text" name="responsive_source[]" class="uk-persistent-focus uk-active" />
      </div>
      <div class="uk-width-1-6 uk-form-icon uk-form-icon-flip">
        <input type="text" name="responsive_width_descriptor[]" pattern="[0-9]+" class="uk-text-center" aria-label="<?php echo Text::_('WF_IMGMANAGER_EXT_LABEL_WIDTH_DESCRIPTOR'); ?>" />
        <i class="uk-icon-none">w</i>
      </div>
      <div class="uk-width-1-6 uk-form-icon uk-form-icon-flip">
        <input type="text" name="responsive_pixel_density[]" pattern="[0-9\.]+" class="uk-text-center" aria-label="<?php echo Text::_('WF_IMGMANAGER_EXT_LABEL_PIXEL_DENSITY'); ?>" />
        <i class="uk-icon-none">x</i>
      </div>
    </div>
  </div>

  <div class="uk-margin-small-right">
    <button class="uk-button uk-button-link uk-repeatable-create" aria-label="<?php echo Text::_('WF_LABEL_ADD'); ?>" title="<?php echo Text::_('WF_LABEL_ADD'); ?>"><i class="uk-icon-plus"></i></button>
    <button class="uk-button uk-button-link uk-repeatable-delete" aria-label="<?php echo Text::_('WF_LABEL_REMOVE'); ?>" title="<?php echo Text::_('WF_LABEL_REMOVE'); ?>"><i class="uk-icon-trash"></i></button>
  </div>

</div>

<div class="uk-form-row uk-grid uk-grid-small">
  <label for="responsive_sizes" class="uk-form-label uk-width-2-10"><?php echo Text::_('WF_IMGMANAGER_EXT_LABEL_SIZES', 'Sizes'); ?></label>
  <div class="uk-form-controls uk-width-8-10">
      <input type="text" id="responsive_sizes" />
  </div>
</div>