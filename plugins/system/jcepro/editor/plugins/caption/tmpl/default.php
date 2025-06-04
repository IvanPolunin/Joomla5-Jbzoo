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
use Joomla\CMS\Session\Session;
use Joomla\CMS\Uri\Uri;

$plugin = WFEditorPlugin::getInstance();
$tabs = WFTabs::getInstance();
?>
<form action="#" class="uk-form uk-form-horizontal">
    <!-- Render Tabs -->
    <?php $tabs->render();?>

    <div id="preview" class="uk-placeholder">
        <h5 class="uk-margin-small-bottom uk-text-bold"><?php echo Text::_('WF_LABEL_PREVIEW'); ?></h5>

        <figure id="caption">
            <img id="caption_image" src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" alt="Preview" />
            <figcaption id="caption_text"></figcaption>
        </figure>

        <p><?php echo Text::_('WF_LOREM_IPSUM'); ?></p>
    </div>

    <!-- Token -->
    <input type="hidden" id="token" name="<?php echo Session::getFormToken(); ?>" value="1" />
</form>
<div class="actionPanel">
    <button class="button" id="cancel">
        <?php echo Text::_('WF_LABEL_CANCEL') ?>
    </button>
    <button class="button" id="help">
        <?php echo Text::_('WF_LABEL_HELP') ?>
    </button>
    <button class="button" id="insert">
        <?php echo Text::_('WF_LABEL_INSERT') ?>
    </button>
</div>