<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// get element from parent parameter form
use Joomla\CMS\Language\Text;

$element = $parent->element;

// init vars
$id = uniqid('group-');
$i  = 0;

?>

<div id="<?php echo $id; ?>" class="elements">
	<ul>
		<?php
			foreach ($element->config->get('element', array()) as $opt) {
				echo '<li>'.$element->editElement($control_name, $i++, $opt['name'], $opt['type']).'</li>';
			}
		?>
		<li class="hidden" ><?php echo $element->editElement($control_name, '0', '', ''); ?></li>
	</ul>
	<div class="add"><?php echo Text::_('Add Element'); ?></div>
</div>

<script type="text/javascript">
	jQuery('#<?php echo $id; ?>').ElementGroup();
</script>
