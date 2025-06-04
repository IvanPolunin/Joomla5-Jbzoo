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

<div id="<?php echo $this->identifier; ?>">
	<table>
		<?php echo $this->app->html->_('zoo.editrow', Text::_('Rating'), $this->getRating()); ?>
		<?php echo $this->app->html->_('zoo.editrow', Text::_('Votes'), (int) $this->get('votes', 0)); ?>
	</table>

	<?php if ($this->get('votes', 0)) : ?>

		<input name="reset-rating" type="button" class="button" value="<?php echo Text::_('Reset'); ?>"/>

		<script type="text/javascript">
			jQuery('#<?php echo $this->identifier; ?>').EditElementRating({ url: '<?php echo $url; ?>' });
		</script>

	<?php endif; ?>

</div>
