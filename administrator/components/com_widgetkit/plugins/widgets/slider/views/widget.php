<?php

// JS Options
$options = array();
$options[] = (!$settings['infinite']) ? 'finite: true' : '';
$options[] = ($settings['center']) ? 'center: true' : '';
$options[] = ($settings['autoplay']) ? 'autoplay: true ' : '';
$options[] = ($settings['interval'] != '7000') ? 'autoplay-interval: ' . $settings['interval'] : '';
$options[] = ($settings['autoplay_pause']) ? '' : 'pause-on-hover: false';

$options = implode(';', array_filter($options));

// Grid
$grid  = '{wk}-grid {wk}-grid-match {wk}-child-width-1-'.$settings['columns'];
$grid .= $settings['columns_small'] ? ' {wk}-child-width-1-'.$settings['columns_small'].'@s' : '';
$grid .= $settings['columns_medium'] ? ' {wk}-child-width-1-'.$settings['columns_medium'].'@m' : '';
$grid .= $settings['columns_large'] ? ' {wk}-child-width-1-'.$settings['columns_large'].'@l' : '';
$grid .= $settings['columns_xlarge'] ? ' {wk}-child-width-1-'.$settings['columns_xlarge'].'@xl' : '';

$grid .= in_array($settings['gutter'], array('collapse','large','medium','small')) ? ' {wk}-grid-'.$settings['gutter'] : '' ;

// Title Size
switch ($settings['title_size']) {
    case 'medium':
        $title_size = '{wk}-heading-medium {wk}-margin-remove-top';
        break;
    default:
        $title_size = '{wk}-' . $settings['title_size'] . ' {wk}-margin-remove-top';
}

// Content Size
switch ($settings['content_size']) {
    case 'large':
        $content_size = '{wk}-text-large';
        break;
    case 'h1':
    case 'h2':
    case 'h3':
    case 'h4':
    case 'h5':
    case 'h6':
        $content_size = '{wk}-' . $settings['content_size'];
        break;
    default:
        $content_size = '';
}

// Link Style
switch ($settings['link_style']) {
    case 'button':
        $link_style = '{wk}-button {wk}-button-default';
        break;
    case 'primary':
        $link_style = '{wk}-button {wk}-button-primary';
        break;
    case 'button-large':
        $link_style = '{wk}-button {wk}-button-large {wk}-button-default';
        break;
    case 'primary-large':
        $link_style = '{wk}-button {wk}-button-large {wk}-button-primary';
        break;
    case 'button-link':
        $link_style = '{wk}-button {wk}-button-link';
        break;
    default:
        $link_style = '';
}

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

// Overlays
$overlay_hover = ($settings['overlay_hover']) ? '{wk}-transition-' . $settings['overlay_animation'] : '';
$background = ($settings['overlay_background'] == 'hover') ? '{wk}-transition-' . $settings['overlay_animation'] : '';

?>

<div class="{wk}-position-relative {wk}-visible-toggle <?= $settings['class'] ?>" {wk}-slider="<?= $options ?>">

    <ul class="{wk}-slider-items <?= $grid ?>" <?php if ($settings['fullscreen']) echo '{wk}-height-viewport="min-height: 300"' ?>>
    <?php foreach ($items as $item) :

            // Media Type
            $width = $item['media.width'];
            $height = $item['media.height'];

            $media = '';

            if ($item->type('media') == 'image' && $settings['media']) {
                if ($settings['image_width'] != 'auto' || $settings['image_height'] != 'auto') {
                    $width  = ($settings['image_width'] != 'auto') ? $settings['image_width'] : '';
                    $height = ($settings['image_height'] != 'auto') ? $settings['image_height'] : '';

                    $media = 'background-image: url(\'' . $item->thumbnail('media', $width, $height, array(), true) . '\');';
                }
                elseif ($media = $item->get('media')) {

                    if ($img = $app['image']->create($media)) {
                        $media = 'background-image: url(\'' . $img->getURL() . '\');';
                    }
                    else {
                        $media = 'background-image: url(\'' . $media . '\');';
                    }

                }
            }

            // `min-height` doesn't work in IE11 and IE10 if flex items are centered vertically
            // can't set `height` when fullscreen
            $min_height = (!$settings['fullscreen']) ? 'height: ' . $settings['min_height'] . 'px;' : '';

            if ($settings['overlay_image'] != 'hover') {
                $media = 'style="' . $min_height . $media . '"';
            }

            // Second Image as Overlay
            $media2 = '';
            if ($settings['overlay_image'] == 'second') {
                foreach ($item as $field) {
                    if ($field != 'media' && $item->type($field) == 'image') {
                        $media2 = 'style="background-image: url(\'' . $item->thumbnail($field, $width, $height, array(), true) . '\');"';
                        break;
                    }
                }
            }

            if ($settings['overlay_image'] == 'hover') {
                $media2 = 'style="' . $media . '"';
                $media  = 'style="' . $min_height . '"';
            }

        ?>

        <li>

            <div class="{wk}-panel {wk}-inline-clip {wk}-transition-toggle {wk}-background-cover" <?= $media ?>>

                <?php if ($media2) : ?>
                <div class="{wk}-position-cover {wk}-background-cover <?= $settings['image_animation'] != 'none' ? '{wk}-transition-' . $settings['image_animation'] : '{wk}-transition-fade' ?>" <?= $media2 ?>></div>
                <?php endif ?>

                <?php if ($settings['overlay_background'] != 'none') : ?>
                <div class="{wk}-position-cover {wk}-overlay-primary <?= $background ?>"></div>
                <?php endif ?>

                <div class="{wk}-overlay {wk}-position-cover <?= $overlay_hover ?> {wk}-flex {wk}-flex-center {wk}-flex-middle {wk}-text-<?= $settings['text_align'] ?> {wk}-light">
                    <div>

                        <?php if ($item['title'] && $settings['title']) : ?>
                        <<?= $settings['title_element'] ?> class="<?= $title_size ?> {wk}-margin-remove-top">

                            <?php if ($item['link']) : ?>
                                <a class="{wk}-link-reset" href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $item['title'] ?></a>
                            <?php else : ?>
                                <?= $item['title'] ?>
                            <?php endif ?>

                        </<?= $settings['title_element'] ?>>
                        <?php endif ?>

                        <?php if ($item['content'] && $settings['content']) : ?>
                        <div class="<?= $content_size ?> {wk}-margin"><?= $item['content'] ?></div>
                        <?php endif ?>

                        <?php if ($item['link'] && $settings['link']) : ?>
                        <p><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                        <?php endif ?>

                    </div>
                </div>

                <?php if ($item['link'] && $settings['overlay_link']) : ?>
                <a class="{wk}-position-cover" href="<?= $item->escape('link') ?>"<?= $link_target ?>></a>
                <?php endif ?>

            </div>

        </li>

    <?php endforeach ?>
    </ul>

    <?php if (in_array($settings['slidenav'], array('top-left', 'top-right', 'bottom-left', 'bottom-right'))) : ?>
    <div class="{wk}-slidenav-container {wk}-position-<?= $settings['slidenav'] ?> {wk}-position-small {wk}-hidden-hover <?php if ($settings['slidenav_contrast']) echo '{wk}-light' ?>">
        <a href="#" {wk}-slidenav-previous {wk}-slider-item="previous"></a>
        <a href="#" {wk}-slidenav-next {wk}-slider-item="next"></a>
    </div>
    <?php elseif ($settings['slidenav'] == 'default') : ?>

        <?php if ($settings['slidenav_contrast']) : ?>
        <div class="{wk}-light">
        <?php endif ?>

            <a href="#" class="{wk}-position-center-left {wk}-position-small {wk}-hidden-hover" {wk}-slidenav-previous {wk}-slider-item="previous"></a>
            <a href="#" class="{wk}-position-center-right {wk}-position-small {wk}-hidden-hover" {wk}-slidenav-next {wk}-slider-item="next"></a>

        <?php if ($settings['slidenav_contrast']) : ?>
        </div>
        <?php endif ?>

    <?php endif ?>

</div>
