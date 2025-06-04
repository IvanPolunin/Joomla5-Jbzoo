<?php

// Second Image as Overlay
$media2 = '';
if ($settings['media_overlay'] == 'image') {
    foreach ($item as $field) {
        if ($field != 'media' && $item->type($field) == 'image') {
            $media2 = $field;
            break;
        }
    }
}

// Media Type
$attrs  = array('class' => '');
$width  = $item['media.width'];
$height = $item['media.height'];

if ($item->type('media') == 'image') {
    $attrs['alt'] = strip_tags($item['title']);

    $attrs['class'] .= ($settings['media_animation'] != 'none' && !$media2) ? ' {wk}-transition-' . $settings['media_animation'] . ' {wk}-transition-opaque' : '';

    $width  = ($settings['image_width'] != 'auto') ? $settings['image_width'] : '';
    $height = ($settings['image_height'] != 'auto') ? $settings['image_height'] : '';
}

if ($item->type('media') == 'video') {
    $attrs['controls'] = true;
}

if ($item->type('media') == 'iframe') {
    $attrs['{wk}-responsive'] = true;
}

$attrs['width']  = $width ?: '';
$attrs['height'] = $height ?: '';

if (($item->type('media') == 'image') && ($settings['image_width'] != 'auto' || $settings['image_height'] != 'auto')) {
    $media = $item->thumbnail('media', $width, $height, $attrs);
} else {
    $media = $item->media('media', $attrs);
}

// Second Image as Overlay
if ($media2) {

    $attrs['class'] .= ' {wk}-position-cover';
    $attrs['class'] .= ($settings['media_animation'] != 'none') ? ' {wk}-transition-' . $settings['media_animation'] : ' {wk}-transition-fade';

    $media2 = $item->thumbnail($media2, $width, $height, $attrs);
}

// Link and Overlay
$overlay       = '';
$overlay_hover = '';
$panel_hover   = '';

if ($item['link']) {

    if ($settings['panel_link']) {

        if (($settings['media_overlay'] == 'icon') ||
            ($media2) ||
            ($item['media'] && $settings['media'] && $settings['media_animation'] != 'none')) {
            $panel_hover = '{wk}-transition-toggle';
        }

    } elseif ($settings['media_overlay'] == 'link' || $settings['media_overlay'] == 'icon' || $settings['media_overlay'] == 'image') {
        $overlay = '<a class="{wk}-position-cover" href="' . $item->escape('link') . '"' . $link_target . ' title="' . $item->escape('title') . '"></a>';
        $overlay_hover = ' {wk}-transition-toggle';
    }

    if ($settings['media_overlay'] == 'icon') {
        $overlay = '<div class="{wk}-overlay-primary {wk}-position-cover {wk}-transition-fade"><div class="{wk}-position-center"><span class="' . ($settings['overlay_animation'] != 'fade' ? '{wk}-transition-opaque {wk}-transition-' . $settings['overlay_animation'] : '') . '" {wk}-overlay-icon></span></div></div>' . $overlay;
    }

    if ($media2) {
        $overlay = $media2 . $overlay;
    }

}

if ($overlay || ($settings['panel_link'] && $settings['media_animation'] != 'none')) {
    $media  = '<div class="{wk}-inline-clip' . $overlay_hover . '">' . $media . $overlay . '</div>';
}

?>

<div class="<?= $panel ?> <?= $panel_hover ?> {wk}-text-<?= $settings['text_align'] ?>">

    <?php if ($item['link'] && $settings['panel_link']) : ?>
    <a class="{wk}-position-cover {wk}-position-z-index" href="<?= $item->escape('link') ?>"<?= $link_target ?> title="<?= $item->escape('link') ?>"></a>
    <?php endif ?>

    <?php if ($item['media'] && $settings['media']) : ?>
    <div class="{wk}-card-media-top {wk}-text-center"><?= $media ?></div>
    <?php endif ?>

    <div class="{wk}-card-body">

        <?php if ($item['title'] && $settings['title']) : ?>
        <<?= $settings['title_element'] ?> class="<?= $title_size ?>">

            <?php if ($item['link']) : ?>
                <a class="{wk}-link-reset" href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $item['title'] ?></a>
            <?php else : ?>
                <?= $item['title'] ?>
            <?php endif ?>

        </<?= $settings['title_element'] ?>>
        <?php endif ?>

        <?php if ($item['content'] && $settings['content']) : ?>
        <div class="{wk}-margin"><?= $item['content'] ?></div>
        <?php endif ?>

        <?php if ($item['link'] && $settings['link']) : ?>
        <p><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
        <?php endif ?>

    </div>

</div>
