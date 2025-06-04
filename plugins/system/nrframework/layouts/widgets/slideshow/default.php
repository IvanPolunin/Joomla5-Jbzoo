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

use Joomla\CMS\Layout\FileLayout;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;

extract($displayData);

if (!$items || !is_array($items) || !count($items))
{
	return;
}

if ($load_stylesheet)
{
    HTMLHelper::script('plg_system_nrframework/vendor/swiper.min.js', ['relative' => true, 'version' => 'auto']);
    HTMLHelper::script('plg_system_nrframework/widgets/slideshow.js', ['relative' => true, 'version' => 'auto']);

	HTMLHelper::stylesheet('plg_system_nrframework/vendor/swiper.min.css', ['relative' => true, 'version' => 'auto']);
	HTMLHelper::stylesheet('plg_system_nrframework/widgets/slideshow.css', ['relative' => true, 'version' => 'auto']);
}

if ($load_css_vars)
{
	Factory::getDocument()->addStyleDeclaration('
		.nrf-widget.tf-slideshow-wrapper.' . $id . ' {
			--swiper-theme-color: ' . $theme_color . ';
		}
		.nrf-widget.tf-slideshow-thumbs-wrapper.' . $id . ' {
			--swiper-theme-color: ' . $theme_color . ';
		}
	');
}
?>
<div class="nrf-widget tf-gallery-wrapper tf-slideshow-wrapper swiper<?php echo $css_class; ?>" role="region" aria-roledescription="carousel" aria-label="<?php echo Text::_('NR_HIGHLIGHTED_CAROUSEL'); ?>" data-options="<?php echo htmlspecialchars(json_encode($options), ENT_COMPAT, 'UTF-8'); ?>" id="tf-slideshow-<?php echo $id; ?>">
    <div class="swiper-wrapper" aria-live="off">
        <?php
            foreach ($items as $index => $item)
            {
                ?>
                <div class="swiper-slide" role="group" aria-roledescription="slide" aria-label="<?php Text::sprintf('NR_X_OF_X', ($index + 1), count($items)); ?>">
                    <?php if ($lightbox): ?>
                        <a href="<?php echo $item['url']; ?>" class="tf-gallery-lightbox-item tf-slideshow-<?php echo $id; ?>" data-type="image" data-description=".glightbox-desc.<?php echo $id; ?>.desc-<?php echo $index; ?>">
                    <?php endif; ?>
                    <img loading="lazy" src="<?php echo $item['url']; ?>"<?php echo $item['img_atts']; ?> alt="<?php echo strip_tags($item['alt']); ?>" />
                    <?php if ($lightbox): ?>
                        </a>
                        <div class="glightbox-desc <?php echo $id . ' desc-' . $index; ?>">
                            <div class="caption"><?php echo nl2br($item['caption']); ?></div>
                            <div class="module"><?php echo !empty($module) ? GalleryHelper::loadModule($module) : ''; ?></div>
                        </div>
                    <?php endif; ?>
                </div>
                <?php
            }
        ?>
    </div>

    <?php if ($options['autoplay_progress']): ?>
    <div class="autoplay-progress-circle">
        <svg viewBox="0 0 48 48">
            <circle cx="24" cy="24" r="20"></circle>
        </svg>
        <span></span>
    </div>
    <?php endif; ?>

    <?php if (in_array($nav_controls, ['arrows', 'arrows_dots'])): ?>
    <div class="swiper-button-prev" aria-label="<?php echo Text::_('NR_PREVIOUS_SLIDE'); ?>"></div>
    <div class="swiper-button-next" aria-label="<?php echo Text::_('NR_NEXT_SLIDE'); ?>"></div>
    <?php endif; ?>

    <?php if (in_array($nav_controls, ['dots', 'arrows_dots'])): ?>
    <div class="swiper-pagination"></div>
    <?php endif; ?>
</div>

<?php if ($show_thumbnails): ?>
<div thumbsSlider="" class="nrf-widget tf-slideshow-thumbs-wrapper swiper<?php echo $css_class; ?>" id="thumbs_tf-slideshow-<?php echo $id; ?>">
    <div class="swiper-wrapper">
        <?php
            foreach ($items as $index => $item)
            {
                ?><div class="swiper-slide"><img loading="lazy" src="<?php echo $item['thumbnail_url']; ?>" alt="<?php echo strip_tags($item['alt']); ?>" /></div><?php
            }
        ?>
    </div>
    <?php if ($show_thumbnails_arrows): ?>
    <div class="swiper-button-next"></div>
    <div class="swiper-button-prev"></div>
    <?php endif; ?>
</div>
<?php endif; ?>

<?php
if ($lightbox)
{
    $layout = new FileLayout('glightbox', JPATH_PLUGINS . '/system/nrframework/layouts/widgets/gallery/default');
    echo $layout->render($displayData);
}
?>