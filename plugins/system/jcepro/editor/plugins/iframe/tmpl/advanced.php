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
    <label for="style" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_STYLE_DESC'); ?>">
        <?php echo Text::_('WF_LABEL_STYLE'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <input id="style" type="text" value="" />
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label class="uk-form-label uk-width-1-5" for="classes" class="hastip" title="<?php echo Text::_('WF_LABEL_CLASSES_DESC'); ?>"><?php echo Text::_('WF_LABEL_CLASSES'); ?></label>
    <div class="uk-form-controls uk-width-4-5">
        <input type="text" id="classes" class="uk-datalist" list="classes_datalist" mulitple />
        <datalist id="classes_datalist"></datalist>
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="title" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_TITLE_DESC'); ?>">
        <?php echo Text::_('WF_LABEL_TITLE'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <input id="title" type="text" value="" />
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="name" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_NAME_DESC'); ?>">
        <?php echo Text::_('WF_LABEL_NAME'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <input id="name" type="text" value="" />
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="id" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_ID_DESC'); ?>">
        <?php echo Text::_('WF_LABEL_ID'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <input id="id" type="text" value="" />
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="id" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_SANDBOX_DESC'); ?>">
        <?php echo Text::_('WF_LABEL_SANDBOX'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <input type="text" id="sandbox" class="uk-datalist" list="sandbox_datalist" multiple />
        <datalist id="sandbox_datalist">
            <option value="">--</option>
            <option value="allow-forms"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_FORMS'); ?></option>
            <option value="allow-modals"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_MODALS'); ?></option>
            <option value="allow-pointer-lock"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_POINTER_LOCK'); ?></option>
            <option value="allow-popups"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_POPUPS'); ?></option>
            <option value="allow-same-origin"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_SAME_ORIGIN'); ?></option>
            <option value="allow-scripts"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_SCRIPTS'); ?></option>
            <option value="allow-top-navigation"><?php echo Text::_('WF_LABEL_SANDBOX_ALLOW_TOP_NAVIGATION'); ?></option>
        </datalist>
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="allowtransparency" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_IFRAME_ALLOWTRANSPARENCY_DESC'); ?>">
        <?php echo Text::_('WF_IFRAME_ALLOWTRANSPARENCY'); ?>
    </label>

    <div class="uk-form-controls uk-width-1-5">
        <select id="allowtransparency">
            <option value="no"><?php echo Text::_('JNO'); ?></option>
            <option value="yes"><?php echo Text::_('JYES'); ?></option>
        </select>
    </div>

    <label for="loading" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_LOADING_DESC'); ?>"><?php echo Text::_('WF_LABEL_LOADING'); ?></label>
    <div class="uk-form-controls uk-width-1-5">
        <select id="loading">
            <option value=""><?php echo Text::_('WF_OPTION_NOT_SET'); ?></option>
            <option value="lazy"><?php echo Text::_('WF_OPTION_LOADING_LAZY'); ?></option>
            <option value="eager"><?php echo Text::_('WF_OPTION_LOADING_EAGER'); ?></option>
        </select>
    </div>
</div>
<div class="uk-form-row uk-grid uk-grid-small">
    <label for="html" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_HTML_DESC'); ?>">
        <?php echo Text::_('WF_LABEL_HTML'); ?>
    </label>

    <div class="uk-form-controls uk-width-4-5">
        <textarea id="html" value=""></textarea>
    </div>
</div>