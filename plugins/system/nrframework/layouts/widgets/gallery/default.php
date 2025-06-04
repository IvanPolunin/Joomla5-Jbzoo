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

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

extract($displayData);

if (!$items || !is_array($items) || !count($items))
{
	return;
}

if (!$readonly && !$disabled && $lightbox)
{
    HTMLHelper::script('plg_system_nrframework/widgets/gallery/gallery.js', ['relative' => true, 'version' => 'auto']);
}

if ($load_stylesheet)
{
	HTMLHelper::stylesheet('plg_system_nrframework/widgets/gallery.css', ['relative' => true, 'version' => 'auto']);
}

if ($style === 'justified')
{
    HTMLHelper::script('plg_system_nrframework/vendor/justified.layout.min.js', ['relative' => true, 'version' => 'auto']);
    HTMLHelper::script('plg_system_nrframework/widgets/gallery/justified.js', ['relative' => true, 'version' => 'auto']);
}

if ($load_css_vars && !empty($custom_css))
{
	Factory::getDocument()->addStyleDeclaration($custom_css);
}

// Add global CSS vars
$global_css = '.nrf-widget.tf-gallery-wrapper.' . $id . ' {
    --mobile-tags-default-style: ' . ($tags_mobile === 'show' ? 'flex' : 'none') . ';
    --mobile-tags-dropdown-style: ' . ($tags_mobile === 'dropdown' ? 'flex' : 'none') . ';
}';
Factory::getDocument()->addStyleDeclaration($global_css);
?>
<div class="nrf-widget tf-gallery-wrapper<?php echo $css_class; ?>" <?php echo $atts; ?>>
    <?php if ($tags_position === 'above'): ?>
        <?php echo $this->sublayout('tags', $displayData); ?>
    <?php endif; ?>
    
    <div class="gallery-items<?php echo $gallery_items_css; ?>">
        <?php
            foreach ($items as $index => $item)
            {
                // If its an invalid image path, show a warning and continue
                if (isset($item['invalid']) && $show_warnings)
                {
                    echo '<div><strong>Warning:</strong> ' . sprintf(Text::_('NR_INVALID_IMAGE_PATH'), $item['path']) . '</div>';
                    continue;
                }

                $item['index'] = $index;
                $displayData['item'] = $item;
                echo $this->sublayout('item', $displayData);
            }
        ?>
    </div>

    <?php if ($tags_position === 'below'): ?>
        <?php echo $this->sublayout('tags', $displayData); ?>
    <?php endif; ?>

    <?php 
        if ($lightbox)
        {
            echo $this->sublayout('glightbox', $displayData);
        }
    ?>
</div>