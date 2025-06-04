<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<div class="tf-gallery-manager-edit-modal-content">
    <div class="tf-gallery-manager-edit-modal-content--preview">
        <img src="" alt="<?php echo Text::_('NR_GALLERY_MANAGER_EDIT_PREVIEW_IMG_ALT'); ?>" />
    </div>
    <div class="tf-gallery-manager-edit-modal-content--form">
        <div class="tf-gallery-manager-edit-modal-content--form--item">
            <label class="tf-gallery-manager-edit-modal-content--form--item--label"><?php echo Text::_('NR_GALLERY_MANAGER_EDIT_ALT_FIELD_LABEL'); ?></label>
            <div class="tf-gallery-manager-edit-modal-content--form--item--content">
                <input type="text" name="alt" class="tf-gallery-manager-edit-modal-content--form--item--content--input form-control" value="" />
            </div>
            <div class="tf-gallery-manager-edit-modal-content--form--item--help"><?php echo Text::_('NR_GALLERY_MANAGER_EDIT_ALT_FIELD_DESC'); ?></div>
        </div>
        <div class="tf-gallery-manager-edit-modal-content--form--item">
            <label class="tf-gallery-manager-edit-modal-content--form--item--label"><?php echo Text::_('NR_GALLERY_MANAGER_EDIT_POPUP_DESC_FIELD_LABEL'); ?></label>
            <div class="tf-gallery-manager-edit-modal-content--form--item--content">
                <textarea name="caption" class="tf-gallery-manager-edit-modal-content--form--item--content--input form-control" rows="3"></textarea>
            </div>
            <div class="tf-gallery-manager-edit-modal-content--form--item--help"><?php echo Text::_('NR_GALLERY_MANAGER_EDIT_POPUP_DESC_FIELD_DESC'); ?></div>
        </div>
        <div class="tf-gallery-manager-edit-modal-content--form--item--help divider"><?php echo Text::_('NR_GALLERY_MANAGER_EDIT_SMART_TAGS_DESC'); ?></div>
        <div class="tf-gallery-manager-edit-modal-content--form--item">
            <label class="tf-gallery-manager-edit-modal-content--form--item--label"><?php echo Text::_('NR_TAGS'); ?></label>
            <div class="tf-gallery-manager-edit-modal-content--form--item--content">
                <?php
                include_once JPATH_PLUGINS . '/system/nrframework/fields/tftagscontrol.php';

                $_field = new \JFormFieldTFTagsControl;

                $element = new \SimpleXMLElement('
                    <field
                        name="tags"
                        type="TFTagsControl"
                        allowCustom="false"
                        multiple="true"
                    />
                ');

                $_field->setup($element, $tags);

                echo $_field->__get('input');
                ?>
            </div>
            <div class="tf-gallery-manager-edit-modal-content--form--item--help"><?php echo Text::_('NR_GALLERY_MANAGER_EDIT_TAGS_FIELD_DESC'); ?></div>
        </div>
        <input type="hidden" class="item_id" />
    </div>
</div>