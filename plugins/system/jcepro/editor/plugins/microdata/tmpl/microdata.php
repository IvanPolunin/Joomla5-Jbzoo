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
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="itemtype" class="uk-form-label uk-width-3-10"><?php echo Text::_('WF_LABEL_TYPE'); ?></label>

    <div class="uk-form-controls uk-width-7-10">
        <input type="text" id="itemtype" class="uk-datalist" list="itemtype_datalist" disabled />
        <datalist id="itemtype_datalist"></datalist>
    </div>
</div>

<div class="uk-form-row uk-grid uk-grid-small">
    <label for="itemprop" class="uk-form-label uk-width-3-10"><?php echo Text::_('WF_LABEL_PROPERTY'); ?></label>
    <div class="uk-form-controls uk-width-7-10">
        <input type="text" id="itemprop" class="uk-datalist" list="itemprop_datalist" disabled />
        <datalist id="itemprop_datalist"></datalist>
    </div>
</div>

<div class="uk-form-row uk-grid uk-grid-small">
    <label for="itemid" class="uk-form-label uk-width-3-10"><?php echo Text::_('WF_LABEL_ID'); ?></label>
    <div class="uk-form-controls uk-width-7-10">
        <input type="text" value="" id="itemid" />
    </div>
</div>

<div class="uk-form-row itemtype-options">
    <label class="uk-width-1-2"><input type="radio" id="itemtype-replace" checked="checked" name="itemtype-option" class="uk-margin-small-right" /><?php echo Text::_('WF_LABEL_REPLACE'); ?></label>
    <label class="uk-width-1-2"><input type="radio" id="itemtype-new" name="itemtype-option" class="uk-margin-small-right" /><?php echo Text::_('WF_LABEL_NEW'); ?></label>
</div>