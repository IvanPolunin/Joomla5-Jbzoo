<?php

// JS Options
$options = array();
$options[] = 'sets: true';
$options[] = (!$settings['infinite']) ? 'finite: true' : '';
$options[] = ($settings['autoplay']) ? 'autoplay: true ' : '';
$options[] = ($settings['interval'] != '7000') ? 'autoplay-interval: ' . $settings['interval'] : '';
$options[] = ($settings['autoplay_pause']) ? '' : 'pause-on-hover: false';

if ($settings['filter'] != 'none') {
    // Set custom slides selector (exclude filtered items)
    $options[] = htmlentities('selSlides: > :not([style*="display: none"])');
}

$options = implode('; ', array_filter($options));

// Grid
$grid  = '{wk}-grid {wk}-grid-match {wk}-child-width-1-'.$settings['columns'];
$grid .= $settings['columns_small'] ? ' {wk}-child-width-1-'.$settings['columns_small'].'@s' : '';
$grid .= $settings['columns_medium'] ? ' {wk}-child-width-1-'.$settings['columns_medium'].'@m' : '';
$grid .= $settings['columns_large'] ? ' {wk}-child-width-1-'.$settings['columns_large'].'@l' : '';
$grid .= $settings['columns_xlarge'] ? ' {wk}-child-width-1-'.$settings['columns_xlarge'].'@xl' : '';

$grid .= in_array($settings['gutter'], array('collapse','large','medium','small')) ? ' {wk}-grid-'.$settings['gutter'] : '' ;

// Panel
$card = false;
switch ($settings['panel']) {
    case 'blank' :
    case 'header' :
        $panel = '{wk}-panel';
        break;
    case 'space' :
        $panel = '{wk}-panel {wk}-padding';
        break;
    case 'default' :
        $panel = '{wk}-card {wk}-card-default';
        $card = true;
        break;
    case 'primary' :
        $panel = '{wk}-card {wk}-card-primary';
        $card = true;
        break;
    case 'secondary' :
        $panel = '{wk}-card {wk}-card-secondary';
        $card = true;
        break;
    case 'hover' :
        $panel = '{wk}-card {wk}-card-hover';
        $card = true;
        break;
}

// Title Size
switch ($settings['title_size']) {
    case 'medium':
        $title_size = '{wk}-heading-medium {wk}-margin-remove-top';
        break;
    default:
        $title_size = '{wk}-' . $settings['title_size'] . ' {wk}-margin-remove-top';
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

// Media Border
$border = ($settings['media_border'] != 'none') ? '{wk}-border-' . $settings['media_border'] : '';

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

// Filter
$filter_js = '';
if ($settings['filter'] != 'none') {

    // Filter Tags
    $tags = array();

    if (isset($settings['filter_tags']) && is_array($settings['filter_tags'])) {
        $tags = $settings['filter_tags'];
    }

    if(!count($tags)){
        foreach ($items as $i => $item) {
            if ($item['tags']) {
                $tags = array_merge($tags, $item['tags']);
            }
        }
        $tags = array_unique($tags);

        natsort($tags);
        $tags = array_values($tags);
    }

    if ($tags) {
        $filter_js .= " {wk}-filter=\"target: .{wk}-slider-items; animation: fade\"";
    }

}

?>

<div class="<?= $card && $settings['gutter'] != 'collapse' ? '{wk}-slider-container-offset' : '' ?> <?= $settings['class'] ?>" {wk}-slider="<?= $options ?>"<?= $filter_js ?: '' ?>>

    <?php if (!empty($tags) && $settings['filter_position'] == 'top') : ?>
    <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_filter.php', compact('items', 'settings', 'tags')) ?>
    <?php endif ?>

    <div class="{wk}-position-relative {wk}-visible-toggle {wk}-margin">

        <ul class="{wk}-slider-items <?= $grid ?>">
        <?php foreach ($items as $item) :

                // Social Buttons
                $socials = '';
                if ($settings['social_buttons']) {
                    $socials .= $item['twitter'] ? '<div><a class="{wk}-icon-button" {wk}-icon="twitter" href="'. $item->escape('twitter') .'"></a></div>': '';
                    $socials .= $item['facebook'] ? '<div><a class="{wk}-icon-button" {wk}-icon="facebook" href="'. $item->escape('facebook') .'"></a></div>': '';
                    $socials .= $item['email'] ? '<div><a class="{wk}-icon-button" {wk}-icon="mail" href="mailto:'. $item->escape('email') .'"></a></div>': '';
                }

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

                    $attrs['class'] .= $border ?: '';
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

                        $panel_hover .= in_array($settings['panel'], array('default', 'primary', 'secondary')) ? ' {wk}-card-hover' : '';

                        if (($settings['media_overlay'] == 'icon') ||
                            ($media2) ||
                            ($socials && $settings['media_overlay'] == 'social-buttons') ||
                            ($item['media'] && $settings['media'] && $settings['media_animation'] != 'none')) {
                            $panel_hover .= ' {wk}-transition-toggle';
                        }

                    } elseif ($settings['media_overlay'] == 'link' || $settings['media_overlay'] == 'icon' || $settings['media_overlay'] == 'image') {
                        $overlay = '<a class="{wk}-position-cover" href="' . $item->escape('link') . '"' . $link_target . ' title="' . $item->escape('link') . '"></a>';
                        $overlay_hover = ' {wk}-transition-toggle';
                    }

                    if ($settings['media_overlay'] == 'icon') {
                        $overlay = '<div class="{wk}-overlay-primary {wk}-position-cover {wk}-transition-fade"><div class="{wk}-position-center"><span class="' . ($settings['overlay_animation'] != 'fade' ? '{wk}-transition-opaque {wk}-transition-' . $settings['overlay_animation'] : '') . '" {wk}-overlay-icon></span></div></div>' . $overlay;
                    }

                    if ($media2) {
                        $overlay = $media2 . $overlay;
                    }

                }

                if ($socials && $settings['media_overlay'] == 'social-buttons') {

                    $overlay  = '<div class="{wk}-overlay {wk}-overlay-primary {wk}-position-cover {wk}-transition-' . $settings['overlay_animation'] . ' {wk}-flex {wk}-flex-center {wk}-flex-middle {wk}-text-center">';
                    $overlay .= '<div class="{wk}-grid {wk}-grid-small" {wk}-grid>' . $socials . '</div>';
                    $overlay .= '</div>';

                    $overlay_hover = !$settings['panel_link'] ? ' {wk}-transition-toggle' : '';
                }

                if ($overlay || ($settings['panel_link'] && $settings['media_animation'] != 'none')) {
                    $media  = '<div class="{wk}-inline-clip' . $overlay_hover . ' ' . $border . '">' . $media . $overlay . '</div>';
                }

                // Filter
                $filter = '';
                if ($item['tags'] && $settings['filter'] != 'none') {
                    $tags = implode(' ', array_map(function ($tag) {
                        return htmlspecialchars(str_replace(' ', '__', $tag), ENT_COMPAT, 'UTF-8', false);
                    }, $item['tags']));
                    $filter = "data-tag=\"{$tags}\"";
                }

            ?>

            <li <?= $filter ?>>

                <div class="<?= $panel ?><?= $panel_hover ?> {wk}-text-<?= $settings['text_align'] ?>">

                    <?php if ($item['link'] && $settings['panel_link']) : ?>
                    <a class="{wk}-position-cover {wk}-position-z-index" href="<?= $item->escape('link') ?>"<?= $link_target ?> title="<?= $item->escape('title') ?>"></a>
                    <?php endif ?>

                    <?php if ($item['badge'] && $settings['badge'] && $settings['badge_position'] == 'panel') : ?>
                    <div class="{wk}-position-z-index {wk}-position-top-right {wk}-position-medium <?= $badge_style ?>"><?= $item['badge'] ?></div>
                    <?php endif ?>

                    <?php if ($item['media'] && $settings['media'] && $settings['media_align'] == 'teaser') : ?>
                    <div class="{wk}-text-center <?= $card ? '{wk}-card-media-top' : '{wk}-margin {wk}-margin-remove-top' ?>"><?= $media ?></div>
                    <?php endif ?>

                    <?php if ($card) : ?>
                    <div class="{wk}-card-body">
                    <?php endif ?>

                        <?php if ($item['media'] && $settings['media'] && $settings['media_align'] == 'top') : ?>
                        <div class="{wk}-margin {wk}-text-center"><?= $media ?></div>
                        <?php endif ?>

                        <?php if ($item['title'] && $settings['title']) : ?>
                        <<?= $settings['title_element'] ?> class="<?= $title_size ?>">

                            <?php if ($item['link']) : ?>
                                <a class="{wk}-link-reset" href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $item['title'] ?></a>
                            <?php else : ?>
                                <?= $item['title'] ?>
                            <?php endif ?>

                            <?php if ($item['badge'] && $settings['badge'] && $settings['badge_position'] == 'title') : ?>
                            <span class="{wk}-margin-small-left <?= $badge_style ?>"><?= $item['badge'] ?></span>
                            <?php endif ?>

                        </<?= $settings['title_element'] ?>>
                        <?php endif ?>

                        <?php if ($item['media'] && $settings['media'] && $settings['media_align'] == 'bottom') : ?>
                        <div class="{wk}-margin {wk}-text-center"><?= $media ?></div>
                        <?php endif ?>

                        <?php if ($item['content'] && $settings['content']) : ?>
                        <div class="{wk}-margin"><?= $item['content'] ?></div>
                        <?php endif ?>

                        <?php if ($socials && ($settings['media_overlay'] != 'social-buttons')) : ?>
                        <div class="{wk}-grid {wk}-grid-small {wk}-flex-<?= $settings['text_align'] ?>" {wk}-grid><?= $socials ?></div>
                        <?php endif ?>

                        <?php if ($item['link'] && $settings['link']) : ?>
                        <p><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                        <?php endif ?>

                        <?php if ($item['media'] && $settings['media'] && $settings['media_align'] == 'last') : ?>
                            <div class="{wk}-margin-top {wk}-text-center"><?= $media ?></div>
                        <?php endif ?>

                    <?php if ($card) : ?>
                    </div>
                    <?php endif ?>

                </div>

            </li>

        <?php endforeach ?>
        </ul>

        <?php if (in_array($settings['slidenav'], array('default', 'top-left', 'top-right', 'bottom-left', 'bottom-right'))) : ?>

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

        <?php endif ?>

    </div>

    <?php if ($settings['slidenav'] == 'below') : ?>
    <div class="{wk}-flex {wk}-flex-<?= $settings['slidenav_align'] ?> {wk}-margin-top">
        <a href="#" {wk}-slidenav-previous {wk}-slider-item="previous"></a>
        <a href="#" {wk}-slidenav-next {wk}-slider-item="next"></a>
    </div>
    <?php endif ?>

    <?php if ($settings['nav']) : ?>
    <ul class="{wk}-slider-nav {wk}-dotnav {wk}-flex-center {wk}-margin-remove-bottom"></ul>
    <?php endif ?>

    <?php if (!empty($tags) && $settings['filter_position'] == 'bottom') : ?>
    <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_filter.php', compact('items', 'settings', 'tags')) ?>
    <?php endif ?>

</div>
