<?php



// Slideshow Options
$options = array();
$options[] = ($settings['slide_animation'] != 'slide') ? 'animation: ' . $settings['slide_animation'] : '';
$options[] = ($settings['autoplay']) ? 'autoplay: true ' : '';
$options[] = ($settings['interval'] != '7000') ? 'autoplay-interval: ' . $settings['interval'] : '';
$options[] = ($settings['autoplay_pause']) ? '' : 'pause-on-hover: false';
$options[] = $settings['ratio'] ? 'ratio: ' . $settings['ratio'] : '';
$options[] = $settings['min_height'] ? 'min-height: ' . $settings['min_height'] : '';

$options = implode(';', array_filter($options));

// Count media fields
$fields = array();
foreach ($item as $field) {
    if (in_array($item->type($field), array('image', 'video', 'iframe'))) {
        $fields[] = $field;
    }
}

?>

<?php if (count($fields) > 1) : ?>
<div {wk}-slideshow="<?= $options ?>">

    <div class="{wk}-position-relative {wk}-visible-toggle">

        <ul class="{wk}-slideshow-items">
        <?php foreach ($fields as $field) :

                // Media Type
                $attrs  = array();
                $width  = $item[$field . '.width'];
                $height = $item[$field . '.height'];

                if ($item->type($field) == 'image') {
                    $attrs['alt'] = strip_tags($item['title']);
                    $width  = ($settings['image_width'] != 'auto') ? $settings['image_width'] : '';
                    $height = ($settings['image_height'] != 'auto') ? $settings['image_height'] : '';
                }

                if ($item->type($field) == 'video') {
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

                if (($item->type($field) == 'image') && ($settings['image_width'] != 'auto' || $settings['image_height'] != 'auto')) {
                    $media = $item->thumbnail($field, $width, $height, $attrs);
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
                    $media = $item->media($field, $attrs);
                }

            ?>

            <li>

                <?php if ($settings['kenburns']) : ?>
                <div class="{wk}-position-cover {wk}-animation-kenburns {wk}-animation-reverse">
                <?php endif ?>

                    <?= $media ?>

                <?php if ($settings['kenburns']) : ?>
                </div>
                <?php endif ?>

            </li>

        <?php endforeach ?>
        </ul>

        <?php if ($item['link'] && $settings['link']) : ?>
        <a class="{wk}-position-cover" href="<?= $item->escape('link') ?>"<?= $link_target ?>></a>
        <?php endif ?>

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

        <?php if ($settings['nav_overlay']) : ?>
        <div class="{wk}-overlay {wk}-position-bottom<?= $settings['nav'] == 'dotnav' && $settings['nav_contrast'] ? ' {wk}-light' : '' ?>">
            <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('item', 'fields', 'settings')) ?>
        </div>
        <?php endif ?>

    </div>

    <?php if (!$settings['nav_overlay']) : ?>
    <div class="{wk}-margin">
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('item', 'fields', 'settings')) ?>
    </div>
    <?php endif ?>

</div>

<?php elseif (count($fields) == 1) :

    $field = $fields[0];

    // Media Type
    $attrs  = array();
    $width  = $item[$field . '.width'];
    $height = $item[$field . '.height'];

    if ($item->type($field) == 'image') {
        $attrs['alt'] = strip_tags($item['title']);
        $width  = ($settings['image_width'] != 'auto') ? $settings['image_width'] : $width;
        $height = ($settings['image_height'] != 'auto') ? $settings['image_height'] : $height;
    }

    if ($item->type($field) == 'video') {
        $attrs['playsinline'] = true;
        $attrs['loop']     = true;
        $attrs['muted']    = true;
        $attrs['{wk}-video'] = 'autoplay: inview';
    }

    if ($item->type($field) == 'iframe') {
        $attrs['{wk}-video'] = 'autoplay: inview; automute: true';
        $attrs['{wk}-responsive'] = true;
    }

    $attrs['width']  = $width ?: '';
    $attrs['height'] = $height ?: '';

    if (($item->type($field) == 'image') && ($settings['image_width'] != 'auto' || $settings['image_height'] != 'auto')) {
        $media = $item->thumbnail($field, $width, $height, $attrs);
    } else {
        $media = $item->media($field, $attrs);
    }

    echo $media;

endif ?>
