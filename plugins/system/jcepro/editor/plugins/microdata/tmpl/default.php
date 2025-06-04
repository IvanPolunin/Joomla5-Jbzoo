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

$tabs = WFTabs::getInstance();

?>
<form action="#">
	<!-- Render Tabs -->
	<?php $tabs->render();?>
	<!-- Token -->
	<input type="hidden" id="token" name="<?php echo Session::getFormToken(); ?>" value="1" />
</form>
<div class="actionPanel uk-modal-footer">
	<button class="uk-button" id="cancel"><?php echo Text::_('WF_LABEL_CANCEL') ?></button>
	<button class="uk-button" id="help"><?php echo Text::_('WF_LABEL_HELP') ?></button>
	<button class="uk-button" id="insert"><?php echo Text::_('WF_LABEL_INSERT') ?></button>
</div>
