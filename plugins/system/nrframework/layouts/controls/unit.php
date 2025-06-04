<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            http://www.tassos.gr
 * @copyright       Copyright © 2018 Tassos Marinos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\FileLayout;

extract($displayData);
?>
<div class="tf-unit-control form-control<?php echo isset($wrapper_class) && !empty($wrapper_class) ? ' ' . $wrapper_class : ''; ?>" data-hint="<?php echo $hint; ?>">
	<?php
	echo $input;
	if (count($units) > 0)
	{
		$layout = new FileLayout('selector', JPATH_PLUGINS . '/system/nrframework/layouts/controls/unit');
		echo $layout->render($displayData);
	}
	?>
</div>