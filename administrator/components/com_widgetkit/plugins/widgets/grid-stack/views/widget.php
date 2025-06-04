<?php


// Width
$media_width = '{wk}-width-' . $settings['width'] . '@' . $settings['breakpoint'];

switch ($settings['width']) {
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
    case '3-5':
        $content_width = '2-5';
        break;
    case '2-3':
        $content_width = '1-3';
        break;
    case '3-4':
        $content_width = '1-4';
        break;
    case '4-5':
        $content_width = '1-5';
        break;
    case '1-1':
        $content_width = '1-1';
        break;
}

$content_width = '{wk}-width-' . $content_width . '@' . $settings['breakpoint'];

// Grid Gutter
if ($settings['gutter']) {
    $grid = '{wk}-grid';
} else {
    $grid = '{wk}-grid {wk}-grid-collapse';
}

switch ($settings['gutter_vertical']) {
    case 'collapse':
        $gutter = ' {wk}-margin-remove-top';
        break;
    case 'large':
        $gutter = ' {wk}-margin-large';
        break;
    default:
        $gutter = '';
}

$grid .= $gutter;

// Grid Divider
if ($settings['gutter_vertical'] == 'collapse') {
    $gutter = ' {wk}-margin-remove';
}
$divider = $settings['divider'] ? '<hr class="{wk}-grid-divider ' . $gutter . '">' : '';

// Panel
$panel = $settings['panel'] ? '{wk}-panel {wk}-padding' : '{wk}-panel';

// Content Align
$content_align  = $settings['content_align'] ? '{wk}-flex-middle' : '';

// Text Align
$text_align = $settings['text_align'];

// Title Size
switch ($settings['title_size']) {
    case 'medium':
        $title_size = '{wk}-heading-medium';
        break;
    default:
        $title_size = '{wk}-' . $settings['title_size'];
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
$animation = ($settings['animation_media'] != 'none' || $settings['animation_content'] != 'none') ? ' {wk}-scrollspy="target: &gt; div &gt; [{wk}-scrollspy-class]; delay: 200"' : '';

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

// Custom Class
$class = $settings['class'] ? ' class="' . $settings['class'] . '"' : '';

?>

<div<?= $class ?> <?= $animation ?>>

<?php foreach ($items as $i => $item) :  ?>

    <?php

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
        if ($settings['media_overlay'] == 'link' || $settings['media_overlay'] == 'icon' || $settings['media_overlay'] == 'image') {

            $media = '<div class="{wk}-inline-clip {wk}-transition-toggle ' . $border . '">' . $media;

            if ($media2) {
                $media .= $media2;
            }

            if ($settings['media_overlay'] == 'icon') {
                $media .= '<div class="{wk}-overlay-primary {wk}-position-cover {wk}-transition-fade">';
                $media .= '<div class="{wk}-position-center"><span class="' . ($settings['overlay_animation'] != 'fade' ? '{wk}-transition-opaque {wk}-transition-' . $settings['overlay_animation'] : '') . '" {wk}-overlay-icon></span></div>';
                $media .= '</div>';
            }

            if ($item['link']) {
                $media .= '<a class="{wk}-position-cover" href="' . $item->escape('link') . '"' . $link_target . '></a>';
            }

            $media .= '</div>';
        }

        if ($socials && $settings['media_overlay'] == 'social-buttons') {
            $media  = '<div class="{wk}-inline-clip {wk}-transition-toggle ' . $border . '">' . $media;
            $media .= '<div class="{wk}-overlay {wk}-overlay-primary {wk}-position-cover {wk}-transition-' . $settings['overlay_animation'] . ' {wk}-flex {wk}-flex-center {wk}-flex-middle {wk}-text-center"><div>';
            $media .= '<div class="{wk}-grid {wk}-grid-small" {wk}-grid>' . $socials . '</div>';
            $media .= '</div></div>';
            $media .= '</div>';
        }

        // Align
        if ($settings['alternate']) {
            $align_flip = $i % 2 == ($settings['align'] == 'left');
        } else {
            $align_flip = ($settings['align'] == 'right');
        }

        // Text Align
        if ($settings['text_align'] == 'media') {
            $text_align = $align_flip ? 'right@' . $settings['breakpoint'] : 'left';
        }

        // Width
        if (!($item['media'] && $settings['media'])) {
            $item_content_width = '{wk}-width-1-1';
        } else {
			$item_content_width = $content_width;
		}

        if (!($item['title'] && $settings['title']) && !($item['content'] && $settings['content'])) {
            $item_media_width = '{wk}-width-1-1';
        } else {
            $item_media_width = $media_width;
        }

        // Animation Media
        $slide_direction = '';
        $slide_width = '';
        switch ($settings['animation_media']) {
            case 'slide':
                $slide_direction = $align_flip ? '-right' : '-left';
                break;
            case 'slide-small':
                $slide_direction = $align_flip ? '-right' : '-left';
                $slide_width = '-small';
                break;
            case 'slide-medium':
                $slide_direction = $align_flip ? '-right' : '-left';
                $slide_width = '-medium';
                break;
        }
        $animation_media = ($settings['animation_media'] != 'none') ? ' {wk}-scrollspy-class="{wk}-animation-' . (in_array($settings['animation_media'], array('slide-small', 'slide-medium')) ? 'slide' : $settings['animation_media']) . $slide_direction . $slide_width . '"' : '';

        // Animation Content
        $slide_direction = '';
        $slide_width = '';
        switch ($settings['animation_content']) {
            case 'slide':
                $slide_direction = $align_flip ? '-left' : '-right';
                break;
            case 'slide-small':
                $slide_direction = $align_flip ? '-left' : '-right';
                $slide_width = '-small';
                break;
            case 'slide-medium':
                $slide_direction = $align_flip ? '-left' : '-right';
                $slide_width = '-medium';
                break;
        }
        $animation_content = ($settings['animation_content'] != 'none') ? ' {wk}-scrollspy-class="{wk}-animation-' . (in_array($settings['animation_content'], array('slide-small', 'slide-medium')) ? 'slide' : $settings['animation_content']) . $slide_direction . $slide_width . '"' : '';

    ?>

    <div class="<?= $grid ?> {wk}-text-<?= $text_align ?> <?= $content_align ?>" {wk}-margin="margin: {wk}-margin-top">

        <?php if ($item['media'] && $settings['media']) : ?>
        <div class="<?= $item_media_width ?> {wk}-text-center<?php if ($align_flip) echo ' {wk}-flex-last@' . $settings['breakpoint'] ?>" <?= $animation_media ?>>
            <?= $media ?>
        </div>
        <?php endif ?>

        <?php if (($item['title'] && $settings['title']) || ($item['content'] && $settings['content'])) : ?>
        <div class="<?= $item_content_width ?>" <?= $animation_content ?>>
            <div class="<?= $panel ?> {wk}-width-1-1">

                <?php if ($item['badge'] && $settings['badge'] && $settings['badge_position'] == 'panel') : ?>
                <div class="{wk}-position-top-right {wk}-position-medium <?= $badge_style ?>"><?= $item['badge'] ?></div>
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

                <?php if ($item['content'] && $settings['content']) : ?>
                <div class="{wk}-margin"><?= $item['content'] ?></div>
                <?php endif ?>

                <?php if ($socials && ($settings['media_overlay'] != 'social-buttons')) : ?>
                <div class="{wk}-grid {wk}-grid-small {wk}-flex-<?= $settings['text_align'] ?>" {wk}-grid><?= $socials ?></div>
                <?php endif ?>

                <?php if ($item['link'] && $settings['link']) : ?>
                <p><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                <?php endif ?>

            </div>
        </div>
        <?php endif ?>

    </div>

    <?php if ($i+1 != count($items)) echo $divider ?>

<?php endforeach ?>

</div>
