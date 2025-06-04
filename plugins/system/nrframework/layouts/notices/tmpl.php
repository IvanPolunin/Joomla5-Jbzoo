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

use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Session\Session;

extract($displayData);
?>
<div
	class="tf-notices"
	data-ext-element="<?php echo $ext_element; ?>"
	data-ext-xml="<?php echo $ext_xml; ?>"
	data-ext-type="<?php echo $ext_type; ?>"
	data-exclude="<?php echo htmlspecialchars(json_encode($exclude)); ?>"
	data-root="<?php echo Uri::base(); ?>"
    data-token="<?php echo Session::getFormToken(); ?>"
    data-current-url="<?php echo Uri::getInstance()->toString(); ?>"
>
</div>