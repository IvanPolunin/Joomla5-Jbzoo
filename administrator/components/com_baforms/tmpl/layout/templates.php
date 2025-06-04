<?php
/**
* @package   Gridbox
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

defined('_JEXEC') or die;

use Joomla\CMS\Language\Text;

foreach ($this->templates as $key => $value) {
    if ($key == 'columns') {
        continue;
    }
?>
<template data-key="<?php echo $key ?>">
<?php
    $templateStr = str_replace('[ba-rows]', '', $value);
    $templateStr = str_replace('[ba-columns]', $this->templates->column, $templateStr);
    $templateStr = str_replace('[ba-forms-fields]', '', $templateStr);
    echo $templateStr;
?>
</template>    
<?php
}
?>
<template data-key="focus-underline">
    <span class="ba-focus-underline"></span>
</template>
<template data-key="condition-logic-filter">
    <li class="conditional-logic-filter">
        <div class="conditional-logic-checkbox">
            <label class="ba-form-checkbox">
                <input type="checkbox">
                <span></span>
            </label>
        </div>
        <div class="conditional-logic-title">
            <input type="text" value="New Rule">
            <span class="ba-focus-underline"></span>
        </div>
        <div class="conditional-logic-icons">
            
        </div>
    </li>
</template>
<template data-key="condition-logic-when">
    <div class="condition-logic-horizontal-fields-wrapper" data-ind="when">
        <div class="ba-settings-item ba-settings-select-type">
            <select class="forms-fields-list select-condition-when-field" data-key="field">
                <option hidden value=""><?php echo Text::_('FIELD'); ?></option>
            </select>
        </div>
        <div class="ba-settings-item ba-settings-select-type">
            <select class="select-condition-when-state" data-key="state">
                <option hidden value=""><?php echo Text::_('STATE'); ?></option>
                <option value="not-empty"><?php echo Text::_('FILLED_OUT'); ?></option>
                <option value="empty"><?php echo Text::_('NOT_FILLED_OUT'); ?></option>
                <option value="equal"><?php echo Text::_('EQUAL_TO'); ?></option>
                <option value="not-equal"><?php echo Text::_('NOT_EQUAL_TO'); ?></option>
                <option value="greater"><?php echo Text::_('GREATER_THAN'); ?></option>
                <option value="less"><?php echo Text::_('LESS_THAN'); ?></option>
                <option value="contain"><?php echo Text::_('CONTAINS'); ?></option>
                <option value="not-contain"><?php echo Text::_('DOES_NOT_CONTAIN'); ?></option>
            </select>
        </div>
        <div class="ba-settings-item ba-settings-input-type condition-when-value-wrapper">
            <input type="text" class="condition-when-value" placeholder="<?php echo Text::_('VALUE'); ?>" data-key="value">
        </div>
        <div class="ba-settings-item ba-settings-icon-type">
            <i class="zmdi zmdi-delete delete-condition-row"></i>
        </div>
    </div>
</template>
<template data-key="condition-when-value-select">
    <div class="ba-settings-item ba-settings-select-type condition-when-value-wrapper">
        <select data-key="value" class="condition-when-value">
            <option hidden value=""><?php echo Text::_('VALUE'); ?></option>
        </select>
    </div>
</template>
<template data-key="condition-when-value-input">
    <div class="ba-settings-item ba-settings-input-type condition-when-value-wrapper">
        <input type="text" class="condition-when-value" placeholder="<?php echo Text::_('VALUE'); ?>" data-key="value">
    </div>
</template>
<template data-key="condition-logic-do">
    <div class="condition-logic-horizontal-fields-wrapper" data-ind="do">
        <div class="ba-settings-item ba-settings-select-type">
            <select class="select-condition-do-action" data-key="action">
                <option hidden value=""><?php echo Text::_('ACTION'); ?></option>
                <option value="show"><?php echo Text::_('SHOW_FIELD'); ?></option>
                <option value="hide"><?php echo Text::_('HIDE_FIELD'); ?></option>
                <option value="move"><?php echo Text::_('MOVE_TO_PAGE'); ?></option>
            </select>
        </div>
        <div class="ba-settings-item ba-settings-select-type condition-do-action-wrapper">
            <select class="forms-fields-list select-condition-do-field" data-key="field">
                <option hidden value=""><?php echo Text::_('FIELD'); ?></option>
            </select>
        </div>
        <div class="ba-settings-item ba-settings-icon-type">
            <i class="zmdi zmdi-delete delete-condition-row"></i>
        </div>
    </div>
</template>
<template data-key="condition-do-fields-action">
    <div class="ba-settings-item ba-settings-select-type condition-do-action-wrapper">
        <select class="forms-fields-list select-condition-do-field" data-key="field">
            <option hidden value=""><?php echo Text::_('FIELD'); ?></option>
        </select>
    </div>
</template>
<template data-key="condition-do-pages-action">
    <div class="ba-settings-item ba-settings-select-type condition-do-action-wrapper">
        <select class="forms-pages-list select-condition-do-field" data-key="field">
            <option hidden value=""><?php echo Text::_('PAGE'); ?></option>
        </select>
    </div>
</template>
<template data-key="templates-element">
    <div class="templates-element ba-work-area-element" data-group="">
        <div class="templates-element-image"></div>
        <span></span>
    </div>
</template>
<template data-key="select-modal-cp-position">
    <div class="select-modal-cp-position">
        <i class="zmdi zmdi-more-vert"></i>
        <select>
            <option value=""><?php echo Text::_('SEPARATE_WINDOW'); ?></option>
            <option value="right"><?php echo Text::_('PANEL_TO_RIGHT'); ?></option>
        </select>
    </div>
</template>
<template data-key="resize-handle-left">
    <div class="resize-handle-left resizable-handle" data-direction="left"></div>
</template>