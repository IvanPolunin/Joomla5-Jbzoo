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

// Overlay
$overlay = '{wk}-overlay';
switch ($settings['overlay']) {
    case 'center':
        $overlay .= ' {wk}-position-cover {wk}-flex {wk}-flex-center {wk}-flex-middle {wk}-text-center';
        break;
    case 'middle-left':
        $overlay .= ' {wk}-position-cover {wk}-flex {wk}-flex-middle';
        break;
    default:
        $overlay .= ' {wk}-position-' . $settings['overlay'];
}

$overlay .= $settings['overlay_background'] ? ' {wk}-overlay-primary' : ' {wk}-light';

if ($settings['overlay_animation'] == 'slide' && !in_array($settings['overlay'], array('center', 'middle-left'))) {
    $overlay .= ' {wk}-transition-slide-' . $settings['overlay'];
} else {
    $overlay .= ' {wk}-transition-fade';
}

// Title Size
switch ($settings['title_size']) {
    case 'medium':
        $title_size = '{wk}-heading-medium';
        break;
    default:
        $title_size = '{wk}-' . $settings['title_size'];
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

// Badge Style
switch ($settings['badge_style']) {
    case 'badge':
        $badge_style = '{wk}-label';
        break;
    case 'success':
        $badge_style = '{wk}-label {wk}-label-success';
        break;
    case 'warning':
        $badge_style = '{wk}-label {wk}-label-warning';
        break;
    case 'danger':
        $badge_style = '{wk}-label {wk}-label-danger';
        break;
    case 'text-muted':
        $badge_style  = '{wk}-text-muted';
        break;
    case 'text-primary':
        $badge_style  = '{wk}-text-primary';
        break;
}

// Custom Class
$class = $settings['class'] ? 'class="' . $settings['class'] . '"' : '';

?>

<div <?= $class ?> {wk}-slideshow="<?= $options ?>">

    <div class="{wk}-position-relative {wk}-visible-toggle">

        <ul class="{wk}-slideshow-items <?php if ($settings['overlay'] != 'none') echo '{wk}-transition-active' ?>" <?= $viewport ?>>
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

                <?php if ($item['media'] && $settings['media']) : ?>

                    <?php if ($settings['kenburns']) : ?>
                    <div class="{wk}-position-cover {wk}-animation-kenburns {wk}-animation-reverse <?= $settings['kenburns_animation'] ? '{wk}-' . $settings['kenburns_animation'] : '{wk}-transform-origin-' . $kenburns_alternate[$i % count($kenburns_alternate)] ?>">
                    <?php endif ?>

                        <?= $media ?>

                    <?php if ($settings['kenburns']) : ?>
                    </div>
                    <?php endif ?>

                    <?php if ($settings['overlay'] != 'none' && (($item['title'] && $settings['title']) || ($item['content'] && $settings['content']) || ($item['link'] && $settings['link']))) : ?>
                    <div class="<?= $overlay ?>">

                        <?php if (in_array($settings['overlay'], array('center', 'middle-left'))) : ?>
                        <div>
                        <?php endif ?>

                        <?php if ($item['title'] && $settings['title']) : ?>
                        <<?= $settings['title_element'] ?> class="<?= $title_size ?>">

                            <?= $item['title'] ?>

                            <?php if ($item['badge'] && $settings['badge']) : ?>
                            <span class="{wk}-margin-small-left <?= $badge_style ?>"><?= $item['badge'] ?></span>
                            <?php endif ?>

                        </<?= $settings['title_element'] ?>>
                        <?php endif ?>

                        <?php if ($item['content'] && $settings['content']) : ?>
                        <div class="<?= $content_size ?> {wk}-margin"><?= $item['content'] ?></div>
                        <?php endif ?>

                        <?php if ($item['link'] && $settings['link']) : ?>
                        <p><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                        <?php endif ?>

                        <?php if (in_array($settings['overlay'], array('center', 'middle-left'))) : ?>
                        </div>
                        <?php endif ?>

                    </div>
                    <?php endif ?>

                    <?php if ($item['link'] && $settings['link_media']) : ?>
                    <a href="<?= $item->escape('link') ?>" class="{wk}-position-cover" <?= $link_target ?>></a>
                    <?php endif ?>

                <?php elseif(($item['title'] && $settings['title']) || ($item['content'] && $settings['content'])) : ?>

                    <?php if ($item['title'] && $settings['title']) : ?>
                    <<?= $settings['title_element'] ?> class="<?= $title_size ?>">

                        <?= $item['title'] ?>

                        <?php if ($item['badge'] && $settings['badge']) : ?>
                        <span class="{wk}-margin-small-left <?= $badge_style ?>"><?= $item['badge'] ?></span>
                        <?php endif ?>

                    </<?= $settings['title_element'] ?>>
                    <?php endif ?>

                    <?php if ($item['content'] && $settings['content']) : ?>
                    <div class="{wk}-margin"><?= $item['content'] ?></div>
                    <?php endif ?>

                    <?php if ($item['link'] && $settings['link']) : ?>
                    <p><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                    <?php endif ?>

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
        <div class="{wk}-overlay {wk}-position-bottom<?= $settings['nav'] == 'dotnav' && $settings['nav_contrast'] ? ' {wk}-light' : '' ?>">
            <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
        </div>
        <?php endif ?>

    </div>

    <?php if (!$settings['nav_overlay'] && ($settings['nav'] != 'none')) : ?>
    <div class="{wk}-margin">
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
    </div>
    <?php endif ?>

</div>
