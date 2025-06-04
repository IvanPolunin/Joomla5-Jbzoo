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

extract($displayData);

if (!$tags)
{
    return;
}
?>
<div class="tf-gallery-tags position-<?php echo $tags_position; ?>">
    <?php if ($all_tags_item_label): ?>
        <a href="#" class="tf-gallery-tags--item active" data-tag="*">
            <?php echo $all_tags_item_label; ?>
        </a>
    <?php endif; ?>
    <?php foreach ($tags as $tag): ?>
        <a href="#" class="tf-gallery-tags--item" data-tag="<?php echo $tag; ?>">
            <?php echo $tag; ?>
        </a>
    <?php endforeach; ?>
</div>

<?php if ($tags_mobile === 'dropdown'): ?>
<select class="tf-gallery-tags tf-gallery-tags-dropdown position-<?php echo $tags_position; ?>">
    <option value="" disabled><?php echo Text::_('NR_FILTER_BY_TAG'); ?></option>
    <?php if ($all_tags_item_label): ?>
        <option class="tf-gallery-tags--item active" value="*">
            <?php echo $all_tags_item_label; ?>
        </option>
    <?php endif; ?>
    <?php foreach ($tags as $tag): ?>
        <option class="tf-gallery-tags--item" value="<?php echo $tag; ?>">
            <?php echo $tag; ?>
        </option>
    <?php endforeach; ?>
</select>
<?php endif; ?>