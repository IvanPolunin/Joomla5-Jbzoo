<?php

// Id
$settings['id'] = substr(uniqid(), -3);

// Grid
$grid_options   = array();
$grid_js = '';

$grid = '{wk}-child-width-1-'.$settings['columns'];
$grid .= $settings['columns_small'] ? ' {wk}-child-width-1-'.$settings['columns_small'].'@s' : '';
$grid .= $settings['columns_medium'] ? ' {wk}-child-width-1-'.$settings['columns_medium'].'@m' : '';
$grid .= $settings['columns_large'] ? ' {wk}-child-width-1-'.$settings['columns_large'].'@l' : '';
$grid .= $settings['columns_xlarge'] ? ' {wk}-child-width-1-'.$settings['columns_xlarge'].'@xl' : '';
$grid .= in_array($settings['gutter'], array('collapse','large','medium','small')) ? ' {wk}-grid-'.$settings['gutter'] : '' ;

if ($settings['grid'] == 'masonry') {
    $grid_options[] = 'masonry: next';
} else {
    $grid .= ' {wk}-grid-match';
    $grid_js .= '{wk}-height-match="target: &gt; div &gt; .{wk}-panel, &gt; div &gt; .{wk}-card; row: true"';
}

$grid_options[] = $settings['parallax'] ? 'parallax: ' . ($settings['parallax_translate'] ? intval($settings['parallax_translate'])  : '150') : '';
$grid_options   = implode(';', array_filter($grid_options));

$grid_js .= $grid_options ? ' {wk}-grid="' . $grid_options . '"' : ' {wk}-grid';

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

    // Filter Nav
    switch ($settings['filter']) {
        case 'text':
            $filter_nav = '{wk}-subnav';
            break;
        case 'lines':
            $filter_nav = '{wk}-subnav {wk}-subnav-divider';
            break;
        case 'nav':
            $filter_nav = '{wk}-subnav {wk}-subnav-pill';
            break;
        case 'tabs':
            $filter_nav = '{wk}-tab';
            break;
    }

    $filter_nav .= ($settings['filter_align'] != 'left') ? ' {wk}-flex-' . $settings['filter_align'] : '';

    $filter_js .= '{wk}-filter="target: #wk-grid' . $settings['id'] . '"';

}

// Lightbox Default
$lightbox_js = '';
if ($settings['lightbox'] === 'default') {
    $lightbox_js = '{wk}-lightbox="container: .{wk}-gallery-container; toggle: a[data-js-lightbox]"';
}

// Title Size
switch ($settings['title_size']) {
    case 'medium':
        $title_size = '{wk}-heading-medium';
        break;
    default:
        $title_size = '{wk}-' . $settings['title_size'];
}

// Lightbox Title Size
switch ($settings['lightbox_title_size']) {
    case 'medium':
        $lightbox_title_size = '{wk}-heading-medium';
        break;
    default:
        $lightbox_title_size = '{wk}-' . $settings['lightbox_title_size'];
}

// Content Size
switch ($settings['lightbox_content_size']) {
    case 'large':
        $lightbox_content_size = '{wk}-text-large';
        break;
    case 'h1':
    case 'h2':
    case 'h3':
    case 'h4':
    case 'h5':
    case 'h6':
        $lightbox_content_size = '{wk}-' . $settings['lightbox_content_size'];
        break;
    default:
        $lightbox_content_size = '';
}

// Button: Link
switch ($settings['link_style']) {
    case 'icon':
        $button_link = '{wk}-icon="icon: ' . $settings['link_icon'] . '; ratio: 0.666"';
        break;
    case 'icon-small':
        $button_link = '{wk}-icon="' . $settings['link_icon'] . '"';
        break;
    case 'icon-medium':
        $button_link = '{wk}-icon="icon: ' . $settings['link_icon'] . '; ratio: 1.333"';
        break;
    case 'icon-large':
        $button_link = '{wk}-icon="icon: ' . $settings['link_icon'] . '; ratio: 1.666"';
        break;
    case 'icon-button':
        $button_link = 'class="{wk}-icon-button" {wk}-icon="' . $settings['link_icon'] . '"';
        break;
    case 'button':
        $button_link = 'class="{wk}-button {wk}-button-default"';
        break;
    case 'primary':
        $button_link = 'class="{wk}-button {wk}-button-primary"';
        break;
    case 'button-large':
        $button_link = 'class="{wk}-button {wk}-button-large {wk}-button-default"';
        break;
    case 'primary-large':
        $button_link = 'class="{wk}-button {wk}-button-large {wk}-button-primary"';
        break;
    case 'button-link':
        $link_style = 'class="{wk}-button {wk}-button-link"';
        break;
    default:
        $button_link = '';
}

switch ($settings['link_style']) {
    case 'icon':
    case 'icon-small':
    case 'icon-medium':
    case 'icon-large':
    case 'icon-button':
        $settings['link_text'] = '';
        break;
}

// Button: Lightbox
switch ($settings['lightbox_style']) {
    case 'icon':
        $button_lightbox = '{wk}-icon="icon: ' . $settings['lightbox_icon'] . '; ratio: 0.666"';
        break;
    case 'icon-small':
        $button_lightbox = '{wk}-icon="' . $settings['lightbox_icon'] . '"';
        break;
    case 'icon-medium':
        $button_lightbox = '{wk}-icon="icon: ' . $settings['lightbox_icon'] . '; ratio: 1.333"';
        break;
    case 'icon-large':
        $button_lightbox = '{wk}-icon="icon: ' . $settings['lightbox_icon'] . '; ratio: 1.666"';
        break;
    case 'icon-button':
        $button_lightbox = 'class="{wk}-icon-button" {wk}-icon="' . $settings['lightbox_icon'] . '"';
        break;
    case 'button':
        $button_lightbox = 'class="{wk}-button {wk}-button-default"';
        break;
    case 'primary':
        $button_lightbox = 'class="{wk}-button {wk}-button-primary"';
        break;
    case 'button-large':
        $button_lightbox = 'class="{wk}-button {wk}-button-large {wk}-button-default"';
        break;
    case 'primary-large':
        $button_lightbox = 'class="{wk}-button {wk}-button-large {wk}-button-primary"';
        break;
    case 'button-link':
        $link_style = 'class="{wk}-button {wk}-button-link"';
        break;
    default:
        $button_lightbox = '';
}

switch ($settings['lightbox_style']) {
    case 'icon':
    case 'icon-small':
    case 'icon-medium':
    case 'icon-large':
    case 'icon-button':
        $settings['lightbox_text'] = '';
        break;
}

// Media Border
$border = ($settings['media_border'] != 'none') ? '{wk}-border-' . $settings['media_border'] : '';

// Animation
$animation = ($settings['animation'] != 'none') ? ' {wk}-scrollspy="cls: {wk}-animation-' . $settings['animation'] . '; target: &gt; div &gt; .{wk}-panel, &gt; div &gt; .{wk}-card; delay: 200"' : '';

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

// Force overlay style
if (!in_array($settings['overlay'], array('default', 'center', 'bottom'))) {
    $settings['overlay'] = 'default';
}

?>

<?php if (isset($tags) && $tags && $settings['filter'] != 'none') : ?>
<div <?= $filter_js ?>>

    <ul class="<?= $filter_nav ?>">

        <?php if ($settings['filter_all']) : ?>
        <li class="{wk}-active" {wk}-filter-control><a href="#"><?= $app['translator']->trans('All') ?></a></li>
        <?php endif ?>

        <?php foreach ($tags as $i => $tag) : ?>
        <?php $selector = htmlspecialchars("[data-tag~='" . str_replace(["'", ' '], ["\'", '__'], $tag) . "']", ENT_QUOTES) ?>
        <li <?= !$settings['filter_all'] && $i == '0' ? 'class="{wk}-active"' : '' ?> {wk}-filter-control="<?= $selector ?>"><a href="#"><?= ucwords($tag) ?></a></li>
        <?php endforeach ?>

    </ul>

<?php endif ?>

    <div id="wk-grid<?= $settings['id'] ?>" class="<?= $grid ?> <?= $settings['class'] ?>" <?= $grid_js ?> <?= $animation ?> <?= $lightbox_js ?>>

    <?php foreach ($items as $index => $item) : ?>
        <?php if ($item['media']) :

            // Second Image as Thumbnail Overlay
            $thumbnail_overlay = '';
            $lightbox_alt      = '';
            foreach ($item as $field) {
                if ($field != 'media' && $item->type($field) == 'image') {
                    $thumbnail_overlay = ($settings['overlay'] == 'default' && $settings['overlay_image']) ? $field : '';
                    $lightbox_alt = $settings['lightbox_alt'] ? $field : '';
                    break;
                }
            }

            // Thumbnails
            $thumbnail = '';

            if (($item->type('media') == 'image' || $item['media.poster'])) {

                $attrs           = array('class' => '');
                $width           = ($settings['image_width'] != 'auto') ? $settings['image_width'] : '';
                $height          = ($settings['image_height'] != 'auto') ? $settings['image_height'] : '';

                $attrs['alt']    = strip_tags($item['title']);
                $attrs['width']  = $width;
                $attrs['height'] = $height;

                $attrs['class'] .= ($settings['image_animation'] != 'none' && !$thumbnail_overlay) ? '{wk}-transition-' . $settings['image_animation'] . ' {wk}-transition-opaque' : '';

                if ($settings['image_width'] != 'auto' || $settings['image_height'] != 'auto') {
                    $thumbnail = $item->thumbnail($item->type('media') == 'image' ? 'media' : 'media.poster', $width, $height, $attrs);
                } else {

                    if(($item->type('media') == 'image') && $settings['grid'] == 'masonry'){
                        // adding the size of the original image to the attributes, so that on load the canvas can be created ( see script at the end of the file ).
                        if ($img  = $app['image']->create($item->get('media'))) {
                            $size = getimagesize($img->getPathName());
                            $width  = $size[0];
                            $height = $size[1];
                            $attrs['width'] = $width;
                            $attrs['height'] = $height;
                        }
                    }

                    $thumbnail = $item->media($item->type('media') == 'image' ? 'media' : 'media.poster', $attrs);
                }
            }

            // Lightbox
            $lightbox = '';
            $field = $lightbox_alt ?: 'media';
            if ($settings['lightbox']) {
                if ($item->type($field) == 'image') {
                    if ($settings['lightbox_width'] != 'auto' || $settings['lightbox_height'] != 'auto') {

                        $width  = ($settings['lightbox_width'] != 'auto') ? $settings['lightbox_width'] : '';
                        $height = ($settings['lightbox_height'] != 'auto') ? $settings['lightbox_height'] : '';

                        $lightbox = 'href="' . htmlspecialchars($item->thumbnail($field, $width, $height, $attrs, true), ENT_COMPAT, null, false) . '" data-type="image"';
                    } else {
                        $lightbox = 'href="' . $item[$field] . '" data-type="image"';
                    }
                } else {
                    $lightbox = 'href="' . $item[$field] . '"';
                }
            }

            // Second Image as Overlay
            if ($thumbnail_overlay) {

                $attrs['class'] .= ' {wk}-position-cover';
                $attrs['class'] .= ($settings['image_animation'] != 'none') ? ' {wk}-transition-' . $settings['image_animation'] : '';

                $thumbnail_overlay = $item->thumbnail($thumbnail_overlay, $width, $height, $attrs);
            }

            // Lightbox Caption
            $lightbox_caption = '';
            switch ($settings['lightbox_caption']) {
                case 'title':
                    $lightbox_caption = $item['title'];
                    break;
                case 'content':
                    $lightbox_caption = $item['lightbox_content'] ?: $item['content'];
                    break;
            }
            $lightbox_caption = $lightbox_caption ? 'data-caption="' . strip_tags($lightbox_caption) .'"' : '';

            // Filter
            $filter = '';
            if ($item['tags'] && $settings['filter'] != 'none') {
                $tags = implode(' ', array_map(function ($tag) {
                    return htmlspecialchars(str_replace(' ', '__', $tag), ENT_COMPAT, 'UTF-8', false);
                }, $item['tags']));
                $filter = " data-tag=\"{$tags}\"";
            }

        ?>

        <div<?= $filter ?>>
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_' . $settings['overlay'] . '.php', @compact('item', 'settings', 'title_size', 'border', 'thumbnail', 'thumbnail_overlay', 'lightbox', 'lightbox_caption', 'button_link', 'button_lightbox', 'link_target', 'index', 'width', 'height')) ?>
        </div>

        <?php endif ?>
    <?php endforeach ?>

    </div>

<?php if (isset($tags) && $tags && $settings['filter'] != 'none') : ?>
</div>
<?php endif ?>

<?php if ($settings['lightbox'] === 'slideshow') : ?>
<div id="wk-modal<?= $settings['id'] ?>" class="{wk}-modal-full" {wk}-modal="container: .{wk}-gallery-container">
    <div class="{wk}-modal-dialog">

        <button class="{wk}-modal-close-full {wk}-close-large" type="button" {wk}-close></button>

        <div class="{wk}-grid" {wk}-grid>
            <div class="{wk}-width-1-2@m {wk}-text-center">

                <div class="{wk}-position-relative {wk}-visible-toggle" {wk}-slideshow="animation: fade">
                    <ul class="{wk}-slideshow-items" {wk}-height-viewport="min-height: 300">
                        <?php foreach ($items as $item) :

                            // Alternative Media Field
                            $field = 'media';
                            if ($settings['lightbox_alt']) {
                                foreach ($item as $media_field) {
                                    if (($item[$media_field] != $item['media']) && ($item->type($media_field) == 'image')) {
                                        $field = $media_field;
                                        break;
                                    }
                                }
                            }

                            // Media Type
                            $attrs  = array('class' => '');
                            $width  = $item[$field . '.width'];
                            $height = $item[$field . '.height'];

                            if ($item->type($field) == 'image') {
                                $attrs['alt'] = strip_tags($item['title']);
                                $width  = ($settings['lightbox_width'] != 'auto') ? $settings['lightbox_width'] : $width;
                                $height = ($settings['lightbox_height'] != 'auto') ? $settings['lightbox_height'] : $height;
                            }

                            if ($item->type($field) == 'video') {
                                $attrs['controls'] = true;
                            }

                            if ($item->type($field) == 'iframe') {
                                $attrs['{wk}-responsive'] = true;
                            }

                            $attrs['width']  = $width ?: '';
                            $attrs['height'] = $height ?: '';

                            $attrs['{wk}-cover'] = true;

                            if (($item->type($field) == 'image') && ($settings['lightbox_width'] != 'auto' || $settings['lightbox_height'] != 'auto')) {
                                $media = $item->thumbnail($field, $width, $height, $attrs);
                            } else {
                                $media = $item->media($field, $attrs);
                            }

                        ?>

                            <li>
                                <?= $media ?>
                            </li>

                        <?php endforeach ?>
                    </ul>

                    <?php if ($settings['lightbox_nav_contrast']) : ?>
                    <div class="{wk}-light">
                    <?php endif ?>

                        <a href="#" class="{wk}-position-center-left {wk}-position-small {wk}-hidden-hover" {wk}-slidenav-previous {wk}-slideshow-item="previous"></a>
                        <a href="#" class="{wk}-position-center-right {wk}-position-small {wk}-hidden-hover" {wk}-slidenav-next {wk}-slideshow-item="next"></a>

                    <?php if ($settings['lightbox_nav_contrast']) : ?>
                    </div>
                    <?php endif ?>

                </div>
            </div>
            <div class="{wk}-width-1-2@m {wk}-flex {wk}-flex-middle {wk}-flex-center">

                <div class="{wk}-padding {wk}-width-1-1 <?= $settings['lightbox_content_width'] ? '{wk}-width-' . $settings['lightbox_content_width'] . '@xl' : '' ?>">
                    <ul id="wk-switcher<?= $settings['id'] ?>" class="{wk}-switcher">
                        <?php foreach ($items as $item) : ?>
                        <li>

                            <?php if ($item['title']) : ?>
                            <<?= $settings['lightbox_title_element'] ?> class="<?= $lightbox_title_size ?>"><?= $item['title'] ?></<?= $settings['lightbox_title_element'] ?>>
                            <?php endif ?>

                            <?php if ($item['lightbox_content']) : ?>
                            <div class="{wk}-margin-top <?= $lightbox_content_size ?>"><?= $item['lightbox_content'] ?></div>
                            <?php elseif ($item['content']) : ?>
                            <div class="{wk}-margin-top <?= $lightbox_content_size ?>"><?= $item['content'] ?></div>
                            <?php endif ?>

                            <?php if ($item['link'] && $settings['link']) : ?>
                            <p class="{wk}-margin-remove-bottom"><a href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                            <?php endif ?>

                        </li>
                    <?php endforeach ?>
                    </ul>

                    <div class="{wk}-margin-top">
                        <ul class="{wk}-thumbnav {wk}-margin-remove-bottom" {wk}-switcher="connect: #wk-switcher<?= $settings['id'] ?>; animation: {wk}-animation-fade" {wk}-margin>
                        <?php foreach ($items as $i => $item) :

                                // Thumbnails
                                $thumbnail = '';
                                if (($item->type('media') == 'image' || $item['media.poster'])) {

                                    $attrs           = array();
                                    $width           = ($settings['lightbox_nav_width'] != 'auto') ? $settings['lightbox_nav_width'] : $item['media.width'];
                                    $height          = ($settings['lightbox_nav_height'] != 'auto') ? $settings['lightbox_nav_height'] : $item['media.height'];

                                    $attrs['alt']    = strip_tags($item['title']);
                                    $attrs['width']  = $width;
                                    $attrs['height'] = $height;

                                    if ($settings['lightbox_nav_width'] != 'auto' || $settings['lightbox_nav_height'] != 'auto') {
                                        $thumbnail = $item->thumbnail($item->type('media') == 'image' ? 'media' : 'media.poster', $width, $height, $attrs);
                                    } else {
                                        $thumbnail = $item->media($item->type('media') == 'image' ? 'media' : 'media.poster', $attrs);
                                    }
                                }

                            ?>
                            <li {wk}-slideshow-item="<?= $i ?>"><a href="#"><?= $thumbnail ?: $item['title'] ?></a></li>
                        <?php endforeach ?>
                        </ul>
                    </div>
                </div>

            </div>
        </div>

    </div>
</div>

<script>

    (function(UIkit){

        var $ = UIkit.util;
        var selToggles = '#wk-grid<?= $settings['id'] ?> [href^="#wk-"][{wk}-toggle]';
        var selSlideshow = '#wk-modal<?= $settings['id'] ?> [{wk}-slideshow]';
        var selSwitcher = '#wk-modal<?= $settings['id'] ?> [{wk}-switcher]';
        var selSwitcherTarget = '#wk-modal<?= $settings['id'] ?> .{wk}-switcher';

        $.on(document, 'click', selToggles, function(e) {
            var index = e.current.dataset.index;
            slideshow().show(index, true);
            switcher().show(index);
        });

        $.on(document, 'show', selSwitcherTarget, function(e) {
            if (slideshow().getIndex() !== switcher().index()) {
                slideshow().show(switcher().index());
            }
        });

        $.on(document, 'itemshow', selSlideshow, function(e) {
            if (slideshow().getIndex() !== switcher().index()) {
                switcher().show(slideshow().getIndex());
            }
        });

        function slideshow() {
            return UIkit.getComponent($.$(selSlideshow), 'slideshow');
        }

        function switcher() {
            return UIkit.getComponent($.$(selSwitcher), 'switcher');
        }

    })(window.UIkitwk || window.UIkit);

</script>
<?php endif ?>

<script>

    (function(UIkit){

        var $ = UIkit.util;

        $.ready(function () {
            if (!$.$('.{wk}-gallery-container')) {
                $.append(document.body, '<div class="{wk}-scope {wk}-gallery-container">');
            }
        });

    })(window.UIkitwk || window.UIkit);

</script>
