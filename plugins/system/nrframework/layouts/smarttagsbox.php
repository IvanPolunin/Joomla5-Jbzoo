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

?>
<div class="st" id="smarttags">
	<div class="st_overlay"></div>
	<div class="st_box">
		<div class="st_toolbar">
			<input placeholder="<?php echo Text::_('NR_SMARTTAGS_SEARCH_PLACEHOLDER') ?>" class="st_input_search" type="text"/>
		</div>
		<div class="st_container">
			<div class="st_nav"></div>
			<div class="st_tabs"></div>
		</div>
	</div>
</div>