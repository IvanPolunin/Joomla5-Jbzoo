<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>

<div id="<?php echo $this->identifier; ?>" class="repeat-elements">
	<ul class="repeatable-list">

	<?php foreach($this as $self) : ?>
		<li class="repeatable-element">
			<?php echo $this->_addEditor($this->index(), $this->get('value', $this->config->get('default')), $params->get('trusted_mode', false)); ?>
			<span class="delete" title="<?php echo Text::_('Delete Element')?>"></span>
		</li>
	<?php endforeach; ?>

	<?php for ($index = count($this); $index < count($this) + ElementTextarea::MAX_HIDDEN_EDITORS; $index++) : ?>
		<li class="repeatable-element hidden">
			<?php echo $this->_addEditor($index, '', $params->get('trusted_mode', false)); ?>
			<span class="delete" title="<?php echo Text::_('Delete Element')?>"></span>
		</li>
	<?php endfor; ?>

	</ul>
	<p class="add">
		<a href="javascript:void(0);"><?php echo Text::sprintf('Add another %s', Text::_($this->app->string->ucfirst($this->getElementType()))) ?></a>
	</p>
</div>

<script type="text/javascript">
	jQuery('#<?php echo $this->identifier; ?>').ElementRepeatableTextarea();
</script>
