<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

// create label
$label = '';
if (isset($params['showlabel']) && $params['showlabel']) {
	$label = ($params['altlabel']) ? $params['altlabel'] : $element->config->get('name');
}

// create class attribute
$class = 'element element-'.$element->getElementType().($params['first'] ? ' first' : '').($params['last'] ? ' last' : '');

?>
<span class="<?php echo $class; ?>">
	<?php echo $label.' '.$element->render($params); ?>
</span>