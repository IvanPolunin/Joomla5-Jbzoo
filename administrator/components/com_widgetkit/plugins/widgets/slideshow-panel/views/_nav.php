<?php

// Nav
$nav = '';
$nav_item = '';

switch ($settings['nav']) {
    case 'dotnav':
        $nav  = '{wk}-dotnav';
        break;
    case 'thumbnails':
        $nav = '{wk}-thumbnav';
        $nav_item = ($settings['nav_align'] == 'justify') ? ' class="{wk}-width-1-' . count($items) . '"' : '';
        break;
}

// Alignment
$nav .= ($settings['nav_align'] != 'justify') ? ' {wk}-flex-' . $settings['nav_align'] : '';

?>

<ul class="<?= $nav ?> {wk}-margin-remove-bottom" {wk}-switcher="connect: #wk-switcher<?= $settings['id'] ?>; animation: {wk}-animation-fade" {wk}-margin>
<?php foreach ($items as $i => $item) :

        // Alternative Media Field
        $field = 'media';
        if ($settings['thumbnail_alt']) {
            foreach ($item as $media_field) {
                if (($item[$media_field] != $item['media']) && ($item->type($media_field) == 'image')) {
                    $field = $media_field;
                    break;
                }
            }
        }

        // Thumbnails
        $thumbnail = '';
        if ($settings['nav'] == 'thumbnails' &&  ($item->type($field) == 'image' || $item[$field . '.poster'])) {

            $attrs           = array();
            $width           = ($settings['thumbnail_width'] != 'auto') ? $settings['thumbnail_width'] : $item[$field . '.width'];
            $height          = ($settings['thumbnail_height'] != 'auto') ? $settings['thumbnail_height'] : $item[$field . '.height'];

            $attrs['alt']    = strip_tags($item['title']);
            $attrs['width']  = $width;
            $attrs['height'] = $height;

            if ($settings['thumbnail_width'] != 'auto' || $settings['thumbnail_height'] != 'auto') {
                $thumbnail = $item->thumbnail($item->type($field) == 'image' ? $field : $field . '.poster', $width, $height, $attrs);
            } else {
                $thumbnail = $item->media($item->type($field) == 'image' ? $field : $field . '.poster', $attrs);
            }
        }

    ?>
    <li<?= $nav_item ?>><a href="#"><?= $thumbnail ?: $item['title'] ?></a></li>
<?php endforeach ?>
</ul>

<script>

    (function(UIkit){

        var $ = UIkit.util;
        var selSlideshow = '#wk-<?= $settings['id'] ?> [{wk}-slideshow]';
        var selSwitcher = '#wk-<?= $settings['id'] ?> [{wk}-switcher]';
        var selSwitcherTarget = '#wk-<?= $settings['id'] ?> .{wk}-switcher';

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
