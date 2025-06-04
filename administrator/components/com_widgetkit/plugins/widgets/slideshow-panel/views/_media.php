<?php

// JS Options
$options = array();
$options[] = ($settings['animation'] != 'slide') ? 'animation: ' . $settings['animation'] : '';
$options[] = ($settings['autoplay']) ? 'autoplay: true ' : '';
$options[] = ($settings['interval'] != '7000') ? 'autoplay-interval: ' . $settings['interval'] : '';
$options[] = ($settings['autoplay_pause']) ? '' : 'pause-on-hover: false';
if ($settings['height'] != 'viewport') {
    $options[] = $settings['ratio'] ? 'ratio: ' . $settings['ratio'] : '';
    $options[] = $settings['min_height'] ? 'min-height: ' . $settings['min_height'] : '';
}

$options = implode(';', array_filter($options));

// Viewport
$viewport = '';
if ($settings['height'] == 'viewport') {
    $viewport = $settings['min_height'] ? '{wk}-height-viewport="'. $settings['min_height'] . '"' : '{wk}-height-viewport';
}

// Kenburns
$kenburns_alternate = [
    'center-left',
    'top-right',
    'bottom-left',
    'top-center',
    '', // center-center
    'bottom-right',
];

// Nav
$nav = '{wk}-position-bottom {wk}-margin-bottom';

switch ($settings['nav_align']) {
    case 'left':
        $nav .= ' {wk}-margin-left';
        break;
    case 'right':
        $nav .= ' {wk}-margin-right';
        break;
    case 'center':
    case 'justify':
        $nav .= ' {wk}-margin-left {wk}-margin-right';
        break;
}

$nav .= ($settings['nav_overlay'] && $settings['nav'] == 'dotnav' && $settings['nav_contrast']) ? ' {wk}-light' : '';

?>

<div class="{wk}-position-relative {wk}-visible-toggle" {wk}-slideshow="<?= $options ?>">

    <ul class="{wk}-slideshow-items" <?= $viewport ?>>
        <?php foreach ($items as $i => $item) :

                // Media Type
                $attrs  = array('class' => '');
                $width  = $item['media.width'];
                $height = $item['media.height'];

                if ($item->type('media') == 'image') {
                    $attrs['alt'] = strip_tags($item['title']);
                    $width  = ($settings['image_width'] != 'auto') ? $settings['image_width'] : '';
                    $height = ($settings['image_height'] != 'auto') ? $settings['image_height'] : '';
                }

                if ($item->type('media') == 'video') {
                    $attrs['playsinline'] = true;
                    $attrs['loop']     = true;
                    $attrs['muted']    = true;
                    $attrs['{wk}-video'] = 'autoplay: inview';
                }

                if ($item->type('media') == 'iframe') {
                    $attrs['{wk}-video'] = 'autoplay: inview; automute: true';
                }

                $attrs['{wk}-cover'] = true;

                $attrs['width']  = $width ?: '';
                $attrs['height'] = $height ?: '';

                if (($item->type('media') == 'image') && ($settings['image_width'] != 'auto' || $settings['image_height'] != 'auto')) {
                    $media = $item->thumbnail('media', $width, $height, $attrs);
                } else {
                    $media = $item->media('media', $attrs);
                }

        ?>

            <li>

                <?php if ($settings['kenburns']) : ?>
                <div class="{wk}-position-cover {wk}-animation-kenburns {wk}-animation-reverse <?= $settings['kenburns_animation'] ? '{wk}-' . $settings['kenburns_animation'] : '{wk}-transform-origin-' . $kenburns_alternate[$i % count($kenburns_alternate)] ?>">
                <?php endif ?>

                    <?= $media ?>

                <?php if ($settings['kenburns']) : ?>
                </div>
                <?php endif ?>

            </li>

        <?php endforeach ?>
    </ul>

    <?php if (in_array($settings['slidenav'], array('top-left', 'top-right', 'bottom-left', 'bottom-right'))) : ?>
    <div class="{wk}-slidenav-container {wk}-position-<?= $settings['slidenav'] ?> {wk}-position-small {wk}-hidden-hover <?php if ($settings['nav_contrast']) echo '{wk}-light' ?>">
        <a href="#" {wk}-slidenav-previous {wk}-slideshow-item="previous"></a>
        <a href="#" {wk}-slidenav-next {wk}-slideshow-item="next"></a>
    </div>
    <?php elseif ($settings['slidenav'] == 'default') : ?>

        <?php if ($settings['nav_contrast']) : ?>
        <div class="{wk}-light">
        <?php endif ?>

            <a href="#" class="{wk}-position-center-left {wk}-position-small {wk}-hidden-hover" {wk}-slidenav-previous {wk}-slideshow-item="previous"></a>
            <a href="#" class="{wk}-position-center-right {wk}-position-small {wk}-hidden-hover" {wk}-slidenav-next {wk}-slideshow-item="next"></a>

        <?php if ($settings['nav_contrast']) : ?>
        </div>
        <?php endif ?>

    <?php endif ?>

    <?php if ($settings['nav_overlay'] && ($settings['nav'] != 'none')) : ?>
    <div class="<?= $nav ?>">
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
    </div>
    <?php endif ?>

</div>
