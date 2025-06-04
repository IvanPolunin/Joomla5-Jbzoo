<?php

use YOOtheme\Widgetkit\Content\Content;

// List
$list  = '{wk}-list';
$list .= $settings['list'] ? ' {wk}-list-' . $settings['list'] : '';

// Media Align
$media_align = ($settings['media_align'] == 'right') ? '{wk}-margin-left {wk}-flex-last' : '{wk}-margin-right';

// Content Align
$content_align  = $settings['content_align'] ? '{wk}-flex-middle' : '';

// Media Border
$border = ($settings['media_border'] != 'none') ? '{wk}-border-' . $settings['media_border'] : '';

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

?>

<ul class="<?= $list ?> <?= $settings['class'] ?>">

<?php foreach ($items as $i => $item) :

        // Media Type
        $attrs  = array('class' => '');
        $width  = $item['media.width'];
        $height = $item['media.height'];

        if ($item->type('media') == 'image') {
            $attrs['alt'] = strip_tags($item['title']);

            $attrs['class'] .= $border ?: '';

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

        // Title
        $title = ($settings['title'] == 'title') ? $item['title'] : $item['content'];

        if ($settings['title_truncate']) {
            $title = Content::truncate($title, $settings['title_truncate']);
        }

        switch ($settings['title_size']) {
            case 'default':
                $title = '<' . $settings['title_element'] . ' class="{wk}-margin-remove">' . $title . '</' . $settings['title_element'] . '>';
                break;
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
                $title = '<' . $settings['title_element'] . ' class="{wk}-'. $settings['title_size'] .' {wk}-margin-remove">' . $title . '</' . $settings['title_element'] . '>';
                break;
        }

        // Link Color
        $link_color = ($settings['link_color'] != 'link') ? '{wk}-link-' . $settings['link_color'] : '';

    ?>

    <li>

        <?php if ($item['link'] && $settings['link']) : ?>
        <a class="{wk}-display-block <?= $link_color ?>" href="<?= $item->escape('link') ?>"<?= $link_target ?>>
        <?php endif ?>

            <?php if ($item['media'] && $settings['media']) : ?>
            <div class="{wk}-flex <?= $content_align ?>">
                <div class="<?= $media_align ?>">
                    <?= $media ?>
                </div>
                <div class="{wk}-flex-1">
            <?php endif ?>

                <?= $title ?>

            <?php if ($item['media'] && $settings['media']) : ?>
                </div>
            </div>
            <?php endif ?>

        <?php if ($item['link'] && $settings['link']) : ?>
        </a>
        <?php endif ?>

    </li>

<?php endforeach ?>

</ul>
