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
use Joomla\CMS\Session\Session;
?>
<form onsubmit="return false;">
<div class="source-editor">
  <div class="uk-navbar">
    <div class="uk-navbar-content uk-padding-remove uk-margin-small uk-margin-small-top">
        <button type="button" class="uk-button" data-action="undo" title="<?php echo Text::_('WF_SOURCE_UNDO', 'Undo'); ?>"><i class="uk-icon uk-icon-undo"></i></button>
        <button type="button" class="uk-button" data-action="redo" title="<?php echo Text::_('WF_SOURCE_REDO', 'Redo'); ?>"><i class="uk-icon uk-icon-redo"></i></button>

        <button type="button" class="uk-button uk-button-checkbox uk-active" data-action="highlight" title="<?php echo Text::_('WF_SOURCE_HIGHLIGHT', 'Highlight'); ?>"><i class="uk-icon uk-icon-code-highlight"></i></button>
        <button type="button" class="uk-button uk-button-checkbox uk-active" data-action="linenumbers" title="<?php echo Text::_('WF_SOURCE_NUMBERS', 'Line Numbers'); ?>"><i class="uk-icon uk-icon-code-linenumbers"></i></button>
        <button type="button" class="uk-button uk-button-checkbox uk-active" data-action="wrap" title="<?php echo Text::_('WF_SOURCE_WRAP', 'Wrap Lines'); ?>"><i class="uk-icon uk-icon-code-wrap"></i></button>
        <button type="button" class="uk-button" data-action="format" title="<?php echo Text::_('WF_SOURCE_FORMAT', 'Format Code'); ?>"><i class="uk-icon uk-icon-code-format"></i></button>
    </div>
    <div class="uk-navbar-content uk-navbar-flip uk-grid uk-grid-small uk-margin-small-top">
      <div class="uk-form uk-display-inline-block uk-margin-small">
        <input id="source_search_value" placeholder="<?php echo Text::_('WF_SOURCE_SEARCH', 'Search'); ?>" type="text" />
        <button type="button" class="uk-button" data-action="search" title="<?php echo Text::_('WF_SOURCE_SEARCH', 'Search'); ?>"><i class="uk-icon uk-icon-code-search-next"></i></button>
        <button type="button" class="uk-button" data-action="search-previous" title="<?php echo Text::_('WF_SOURCE_SEARCH_PREV', 'Search Previous'); ?>"><i class="uk-icon uk-icon-code-search-previous"></i></button>
      </div>

        <div class="uk-form uk-display-inline-block uk-margin-top-remove uk-margin-small">
          <input id="source_replace_value" placeholder="<?php echo Text::_('WF_SOURCE_REPLACE', 'Replace'); ?>" type="text" />
          <button type="button" class="uk-button" data-action="replace" title="<?php echo Text::_('WF_SOURCE_REPLACE', 'Replace'); ?>"><i class="uk-icon uk-icon-code-replace"></i></button>
          <button type="button" class="uk-button" data-action="replace-all" title="<?php echo Text::_('WF_SOURCE_REPLACE_ALL', 'Replace All'); ?>"><i class="uk-icon uk-icon-code-replace-all"></i></button>

          <button type="button" class="uk-button uk-button-checkbox" data-action="matchcase" title="<?php echo Text::_('WF_SOURCE_SOURCE_MATCHCASE', 'Match Case'); ?>"><i class="uk-icon uk-icon-code-search-matchcase"></i></button>
          <button type="button" class="uk-button uk-button-checkbox" data-action="wholeword" title="<?php echo Text::_('WF_SOURCE_SOURCE_WHOLEWORD', 'Whole Word'); ?>"><i class="uk-icon uk-icon-code-search-wholeword"></i></button>
          <button type="button" class="uk-button uk-button-checkbox" data-action="regex" title="<?php echo Text::_('WF_SOURCE_SOURCE_REGEX', 'Regular Expression'); ?>"><i class="uk-icon uk-icon-code-search-regex"></i></button>
        </div>
    </div>
  </div>
  <div class="source-editor-container"></div>
</div>
    <div class="actionPanel uk-modal-footer">
        <button type="button" class="save uk-button uk-button-primary"><i class="uk-icon-check uk-margin-small-right"></i><?php echo Text::_('WF_LABEL_SAVE'); ?></button>
    </div>
    <input type="hidden" id="src" value="" />
    <input type="hidden" name="<?php echo Session::getFormToken(); ?>" value="1" />
</form>