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

<div id="tag-area">
    <input type="text" value="<?php echo implode(', ', $tags) ?>" placeholder="<?php echo Text::_('Add tag') ?>" />
	<p><?php echo Text::_('Choose from the most used tags') ?>:</p>
	<?php if (count($most)) : ?>
    <div class="tag-cloud">
        <?php foreach ($most as $tag) : ?>
        <a title="<?php echo $tag->items . ' item' . ($tag->items != 1 ? 's' : '') ?>"><?php echo $tag->name ?></a>
        <?php endforeach ?>
    </div>
	<?php endif ?>
</div>

<script type="text/javascript">
    jQuery(function($) {
        $('#tag-area').Tag({url: '<?php echo $link ?>', inputName: '<?php echo $this->getControlName('value', true) ?>'});
    });
</script>
