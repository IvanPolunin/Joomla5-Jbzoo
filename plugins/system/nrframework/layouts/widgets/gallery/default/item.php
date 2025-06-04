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

extract($displayData);

use NRFramework\Helpers\Widgets\Gallery as GalleryHelper;
?>
<figure
    class="item"
    <?php echo $tags_position !== 'disabled' && isset($item['tags']) && is_array($item['tags']) && count($item['tags']) ? ' data-tags="' . implode(', ', $item['tags']) . '"' : ''; ?>
>
    <?php if ($lightbox) { ?>
        <a href="<?php echo $item['url']; ?>" class="tf-gallery-lightbox-item <?php echo $id; ?>" data-type="image" data-description=".glightbox-desc.<?php echo $id; ?>.desc-<?php echo $item['index']; ?>">
    <?php } ?>
        <img<?php echo $style === 'justified' ? '' : ' loading="lazy"'; ?> class="<?php echo $thumb_class ?>" src="<?php echo $item['thumbnail_url']; ?>"<?php echo $item['img_atts']; ?> alt="<?php echo strip_tags($item['alt']); ?>" />
    <?php if ($lightbox) { ?>
        </a>
        <div class="glightbox-desc <?php echo $id . ' desc-' . $item['index']; ?>">
            <div class="caption"><?php echo nl2br($item['caption']); ?></div>
            <div class="module"><?php echo !empty($module) ? GalleryHelper::loadModule($module) : ''; ?></div>
        </div>
    <?php } ?>
</figure>