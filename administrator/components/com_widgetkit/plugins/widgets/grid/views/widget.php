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

// Panel
$panel_alt = '';
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
    case 'sequence1' :
        $panel = '{wk}-card {wk}-card-default';
        $panel_alt = '{wk}-card {wk}-card-primary';
        $card = true;
        break;
    case 'sequence2' :
        $panel = '{wk}-card {wk}-card-default';
        $panel_alt = '{wk}-card {wk}-card-secondary';
        $card = true;
        break;
    case 'sequence3' :
        $panel = '{wk}-card {wk}-card-primary';
        $panel_alt = '{wk}-card {wk}-card-secondary';
        $card = true;
        break;
    case 'sequence4' :
        $panel = '{wk}-card {wk}-card-secondary';
        $panel_alt = '{wk}-card {wk}-card-primary';
        $card = true;
        break;
}

// Panel Sequence
$panel = array(
    $panel,
    $panel_alt ?: $panel
);

// Media Width
$media_width = '{wk}-width-' . $settings['media_width'] . '@' . $settings['media_breakpoint'];

switch ($settings['media_width']) {
    case '1-5':
        $content_width = '4-5';
        break;
    case '1-4':
        $content_width = '3-4';
        break;
    case '1-3':
        $content_width = '2-3';
        break;
    case '2-5':
        $content_width = '3-5';
        break;
    case '1-2':
        $content_width = '1-2';
        break;
}

$content_width = '{wk}-width-' . $content_width . '@' . $settings['media_breakpoint'];

// Content Align
$content_align  = $settings['content_align'] ? '{wk}-flex-middle' : '';

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

// Animation
$animation = ($settings['animation'] != 'none') ? ' {wk}-scrollspy="cls: {wk}-animation-' . $settings['animation'] . '; target: &gt; div &gt; .{wk}-panel, &gt; div &gt; .{wk}-card; delay: 200"' : '';

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

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

    <div id="wk-grid<?= $settings['id'] ?>" class="<?= $grid ?> {wk}-text-<?= $settings['text_align'] ?> <?= $settings['class'] ?>" <?= $grid_js ?> <?= $animation ?>>

    <?php foreach ($items as $i => $item) :

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

                    $panel_hover .= in_array($settings['panel'], array('default', 'primary', 'secondary', 'sequence1', 'sequence2', 'sequence3', 'sequence4')) ? ' {wk}-card-hover' : '';

                    if (($settings['media_overlay'] == 'icon') ||
                        ($media2) ||
                        ($socials && $settings['media_overlay'] == 'social-buttons') ||
                        ($item['media'] && $settings['media'] && $settings['media_animation'] != 'none')) {
                        $panel_hover .= ' {wk}-transition-toggle';
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

            // Meta
            $meta = '';
            if ($item['date']) {
                $date = '<time datetime="'.$item['date'].'">'.$app['date']->format($item['date'], $settings['date_format']).'</time>';
                if ($item['author']) {
                    $meta = $app['translator']->trans('Written by %author% on %date%',  array('%author%' => $item['author'], '%date%' => $date));
                } else {
                    $meta = $app['translator']->trans('Written on %date%',  array('%date%' => $date));
                }
            } elseif ($item['author']) {
                $meta = $app['translator']->trans('Written by %author%',  array('%author%' => $item['author']));
            }

            if ($item['categories']) {

                $categories = array();
                foreach ($item['categories'] as $category => $url) {
                    $categories[] = '<a href="'.$url.'">'.$category.'</a>';
                }
                $categories = implode(', ', array_filter($categories));

                $meta .= ($meta) ? '. ' : '';
                $meta .= $app['translator']->trans('Posted in %categories%',  array('%categories%' => $categories));

            }

        ?>

        <div <?= $filter ?>>
            <div class="<?= $panel[$i % 2] ?><?= $panel_hover ?>">

                <?php if ($item['link'] && $settings['panel_link']) : ?>
                <a class="{wk}-position-cover {wk}-position-z-index" href="<?= $item->escape('link') ?>"<?= $link_target ?> title="<?= $item->escape('title') ?>"></a>
                <?php endif ?>

                <?php if ($item['badge'] && $settings['badge'] && $settings['badge_position'] == 'panel') : ?>
                <div class="{wk}-position-z-index {wk}-position-top-right {wk}-position-medium <?= $badge_style ?>"><?= $item['badge'] ?></div>
                <?php endif ?>

                <?php if ($item['media'] && $settings['media'] && $settings['media_align'] == 'teaser') : ?>
                <div class="{wk}-text-center <?= $card ? '{wk}-card-media-top' : '{wk}-margin {wk}-margin-remove-top' ?>"><?= $media ?></div>
                <?php endif ?>

                <?php if ($item['media'] && $settings['media'] && in_array($settings['media_align'], array('left', 'right'))) : ?>
                <div class="{wk}-grid <?= $content_align ?>" {wk}-grid>
                    <div class="<?= $media_width ?><?php if ($settings['media_align'] == 'right') echo ' {wk}-flex-last@' . $settings['media_breakpoint'] ?> <?= $card ? '{wk}-card-media-' . $settings['media_align'] : '' ?>">
                        <?= $media ?>
                    </div>
                    <div class="<?= $content_width ?>">
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

                        <?php if ($meta) : ?>
                        <p class="{wk}-article-meta"><?= $meta ?></p>
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
                        <p><a <?php if($link_style) echo 'class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                        <?php endif ?>

                    <?php if ($card) : ?>
                    </div>
                    <?php endif ?>

                <?php if ($item['media'] && $settings['media'] && in_array($settings['media_align'], array('left', 'right'))) : ?>
                    </div>
                </div>
                <?php endif ?>

            </div>
        </div>

    <?php endforeach ?>

    </div>

<?php if (isset($tags) && $tags && $settings['filter'] != 'none') : ?>
</div>
<?php endif ?>
