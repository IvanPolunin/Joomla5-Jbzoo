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

<div id="editor_effects" class="uk-flex uk-flex-wrap uk-flex-space-around uk-grid uk-grid-mini"></div>
<div class="uk-form-row uk-align-right uk-margin-top">
  <button type="button" id="effects_apply" class="uk-button" data-function="effects"><?php echo Text::_('WF_LABEL_APPLY'); ?></button>
</div>