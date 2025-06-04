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
<div class="media_option iframe">
    <h4><?php echo Text::_('WF_MEDIAMANAGER_IFRAME_OPTIONS'); ?></h4>

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
        <label for="loading" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_LOADING_DESC'); ?>"><?php echo Text::_('WF_LABEL_LOADING'); ?></label>
        <div class="uk-form-controls uk-width-1-5">
            <select id="loading">
                <option value=""><?php echo Text::_('WF_OPTION_NOT_SET'); ?></option>
                <option value="lazy"><?php echo Text::_('WF_OPTION_LOADING_LAZY'); ?></option>
                <option value="eager"><?php echo Text::_('WF_OPTION_LOADING_EAGER'); ?></option>
            </select>
        </div>
    </div>
</div>
</div>