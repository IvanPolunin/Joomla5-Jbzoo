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

$document = WFDocument::getInstance();
?>
<div class="uk-form-row uk-grid uk-grid-small">
    <div class="uk-width-4-5 uk-width-mini-1-1">
        <div class="uk-form-row uk-grid uk-grid-small">
            <label class="uk-form-label uk-width-1-5" for="src" class="hastip" title="<?php echo Text::_('WF_LABEL_URL_DESC'); ?>"><?php echo Text::_('WF_LABEL_URL'); ?></label>
            <div class="uk-form-controls uk-form-icon uk-form-icon-flip uk-width-3-4 uk-flex-item-auto">
                <input id="src" type="text" value="" class="filebrowser" required />
            </div>
        </div>
        <div class="uk-form-row uk-grid uk-grid-small">
            <label class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_DIMENSIONS_DESC'); ?>">
                <?php echo Text::_('WF_LABEL_DIMENSIONS'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5 uk-form-constrain">
                <div class="uk-form-controls">
                    <input type="text" id="width" value="" />
                </div>
                <div class="uk-form-controls">
                    <strong class="uk-form-label uk-text-center uk-vertical-align-middle">&times;</strong>
                </div>
                <div class="uk-form-controls">
                    <input type="text" id="height" value="" />
                </div>
                <label class="uk-form-label">
                    <input class="uk-constrain-checkbox" type="checkbox" checked />
                    <?php echo Text::_('WF_LABEL_PROPORTIONAL'); ?>
                </label>
            </div>
        </div>
        <div class="uk-form-row uk-grid uk-grid-small">
            <label for="align" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_ALIGN_DESC'); ?>">
                <?php echo Text::_('WF_LABEL_ALIGN'); ?>
            </label>
            <div class="uk-width-2-5">
                <div class="uk-form-controls uk-width-9-10">
                    <select id="align">
                        <option value=""><?php echo Text::_('WF_OPTION_NOT_SET'); ?></option>
                        <optgroup label="------------">
                            <option value="left"><?php echo Text::_('WF_OPTION_ALIGN_LEFT'); ?></option>
                            <option value="center"><?php echo Text::_('WF_OPTION_ALIGN_CENTER'); ?></option>
                            <option value="right"><?php echo Text::_('WF_OPTION_ALIGN_RIGHT'); ?></option>
                        </optgroup>
                        <optgroup label="------------">
                            <option value="top"><?php echo Text::_('WF_OPTION_ALIGN_TOP'); ?></option>
                            <option value="middle"><?php echo Text::_('WF_OPTION_ALIGN_MIDDLE'); ?></option>
                            <option value="bottom"><?php echo Text::_('WF_OPTION_ALIGN_BOTTOM'); ?></option>
                        </optgroup>
                    </select>
                </div>
            </div>
            <div class="uk-width-2-5 html4">
                <label for="frameborder" class="hastip uk-form-label uk-width-3-5" title="<?php echo Text::_('WF_IFRAME_FRAMEBORDER_DESC'); ?>"><?php echo Text::_('WF_IFRAME_FRAMEBORDER'); ?></label>
                <div class="uk-form-controls uk-width-2-5">
                    <input type="checkbox" id="frameborder" value="1" checked />
                </div>
            </div>
        </div>
        <div class="uk-hidden-mini uk-grid uk-grid-small uk-form-row">
            <label for="margin" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_LABEL_MARGIN_DESC'); ?>">
                <?php echo Text::_('WF_LABEL_MARGIN'); ?>
            </label>
            <div class="uk-form-controls uk-width-4-5 uk-grid uk-grid-small uk-form-equalize">
                <label for="margin_top" class="uk-form-label">
                    <?php echo Text::_('WF_OPTION_TOP'); ?>
                </label>
                <div class="uk-form-controls">
                    <input type="text" id="margin_top" value="" />
                </div>
                <label for="margin_right" class="uk-form-label">
                    <?php echo Text::_('WF_OPTION_RIGHT'); ?>
                </label>
                <div class="uk-form-controls">
                    <input type="text" id="margin_right" value="" />
                </div>
                <label for="margin_bottom" class="uk-form-label">
                    <?php echo Text::_('WF_OPTION_BOTTOM'); ?>
                </label>
                <div class="uk-form-controls">
                    <input type="text" id="margin_bottom" value="" />
                </div>
                <label for="margin_left" class="uk-form-label">
                    <?php echo Text::_('WF_OPTION_LEFT'); ?>
                </label>
                <div class="uk-form-controls">
                    <input type="text" id="margin_left" value="" />
                </div>
                <label class="uk-form-label">
                    <input type="checkbox" class="uk-equalize-checkbox" />
                    <?php echo Text::_('WF_LABEL_EQUAL'); ?>
                </label>
            </div>
        </div>
        <div class="uk-grid uk-grid-small uk-form-row html4">
            <label for="scrolling" class="hastip uk-form-label uk-width-1-5" title="<?php echo Text::_('WF_IFRAME_SCROLLING_DESC'); ?>">
                <?php echo Text::_('WF_IFRAME_SCROLLING'); ?>
            </label>
            <div class="uk-form-controls uk-width-1-5">
                <select id="scrolling">
                    <option value="auto"><?php echo Text::_('WF_OPTION_AUTO'); ?></option>
                    <option value="yes"><?php echo Text::_('JYES'); ?></option>
                    <option value="no"><?php echo Text::_('JNO'); ?></option>
                </select>
            </div>
        </div>
    </div>
    <div class="uk-width-1-5 uk-hidden-mini">
        <div class="preview">
            <img id="sample" src="<?php echo $document->image('sample.jpg', 'media'); ?>" alt="sample.jpg" />
            <?php echo Text::_('WF_LOREM_IPSUM'); ?>
        </div>
    </div>
</div>