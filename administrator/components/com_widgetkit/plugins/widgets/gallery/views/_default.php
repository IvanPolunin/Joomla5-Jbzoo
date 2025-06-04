<?php

// Panel
$panel = '';
switch ($settings['panel']) {
    case 'blank' :
        $panel = '{wk}-panel';
        break;
    case 'default' :
        $panel = '{wk}-card {wk}-card-default';
        break;
    case 'primary' :
        $panel = '{wk}-card {wk}-card-primary';
        break;
    case 'secondary' :
        $panel = '{wk}-card {wk}-card-secondary';
        break;
    case 'hover' :
        $panel = '{wk}-card {wk}-card-hover';
        break;
}

// Buttons
$buttons = array();
if ($item['link'] && $settings['link']) {
    $buttons['link'] = '<a ' . $button_link . ' href="' . $item->escape('link') . '"' . $link_target . '>' . $app['translator']->trans($settings['link_text']) . '</a>';
}
if ($settings['lightbox'] && $settings['lightbox_link']) {
    if ($settings['lightbox'] === 'slideshow') {
        $buttons['lightbox'] = '<a ' . $button_lightbox . ' href="#wk-modal' . $settings['id'] . '" data-index="'.$index.'" {wk}-toggle>' . $app['translator']->trans($settings['lightbox_text']) . '</a>';
    } else {
        $buttons['lightbox'] = '<a ' . $button_lightbox . ' data-js-lightbox ' . $lightbox . ' ' . $lightbox_caption . '>' . $app['translator']->trans($settings['lightbox_text']) . '</a>';
    }
}

// Overlay Background
$background = ($settings['overlay_background'] == 'hover') ? '{wk}-transition-' . $settings['overlay_animation'] : '';

?>

<div class="<?= $panel ?>">

    <?php if ($settings['panel'] != 'blank') : ?>
    <div class="{wk}-card-media-top">
    <?php endif ?>

        <div class="{wk}-inline-clip {wk}-transition-toggle <?= $border ?>">

            <?= $thumbnail ?>

            <?php if ($thumbnail_overlay) : ?>
                <?= $thumbnail_overlay ?>
            <?php endif ?>

            <?php if ($settings['overlay_background'] != 'none') : ?>
            <div class="{wk}-overlay {wk}-overlay-primary {wk}-position-cover <?= $background ?>"></div>
            <?php endif ?>

            <?php if ($settings['overlay_center'] == 'icon' || (!$buttons && $settings['overlay_center'] == 'buttons') || (!($item['content'] && $settings['content']) && $settings['overlay_center'] == 'content')) : ?>
                <div class="{wk}-position-center {wk}-light">
                    <span class="{wk}-transition-<?= $settings['overlay_animation'] ?>" {wk}-overlay-icon></span>
                </div>
            <?php elseif (($settings['overlay_center'] == 'buttons') || ($settings['overlay_center'] == 'content')) : ?>
                <div class="{wk}-overlay {wk}-position-cover {wk}-transition-<?= $settings['overlay_animation'] ?> {wk}-flex {wk}-flex-center {wk}-flex-middle {wk}-text-center {wk}-light">
                    <div>

                        <?php if ($item['content'] && $settings['content'] && $settings['overlay_center'] == 'content') : ?>
                        <div><?= $item['content'] ?></div>
                        <?php endif ?>

                        <?php if ($buttons && $settings['overlay_center'] == 'buttons') : ?>
                        <div class="{wk}-grid {wk}-grid-small {wk}-flex-center" {wk}-grid>

                            <?php if (isset($buttons['link'])) : ?>
                            <div><?= $buttons['link'] ?></div>
                            <?php endif ?>

                            <?php if (isset($buttons['lightbox'])) : ?>
                            <div><?= $buttons['lightbox'] ?></div>
                            <?php endif ?>

                        </div>
                        <?php endif ?>

                    </div>
                </div>
            <?php endif ?>

            <?php if ($settings['overlay_center'] != 'none' && ($settings['overlay_center'] != 'buttons' || !$buttons)) : ?>
                <?php if ($settings['lightbox']) : ?>
                    <?php if ($settings['lightbox'] === 'slideshow') : ?>
                        <a class="{wk}-position-cover" href="#wk-modal<?= $settings['id'] ?>" data-index="<?= $index ?>" {wk}-toggle <?= $lightbox_caption ?>></a>
                    <?php else : ?>
                        <a class="{wk}-position-cover" data-js-lightbox <?= $lightbox ?> <?= $lightbox_caption ?>></a>
                    <?php endif ?>
                <?php elseif ($item['link']) : ?>
                    <a class="{wk}-position-cover" href="<?= $item->escape('link') ?>"<?= $link_target ?>></a>
                <?php endif ?>
            <?php endif ?>

        </div>

    <?php if ($settings['panel'] != 'blank') : ?>
    </div>
    <div class="{wk}-card-body">
    <?php endif ?>

    <?php if ($buttons && $settings['overlay_center'] != 'buttons') : ?>
        <div class="{wk}-grid-small {wk}-flex-middle" {wk}-grid>

            <?php if (($item['title'] && $settings['title']) || ($item['content'] && $settings['content'] && $settings['overlay_center'] != 'content')) : ?>
            <div class="{wk}-width-expand">

                <?php if ($item['title'] && $settings['title']) : ?>
                <<?= $settings['title_element'] ?> class="<?= $title_size ?> {wk}-margin-remove-bottom"><?= $item['title'] ?></<?= $settings['title_element'] ?>>
                <?php endif ?>

                <?php if ($item['content'] && $settings['content'] && $settings['overlay_center'] != 'content') : ?>
                <div><?= $item['content'] ?></div>
                <?php endif ?>

            </div>
            <?php endif ?>

            <div class="{wk}-width-auto">

                <div class="{wk}-grid {wk}-grid-small" {wk}-grid>

                    <?php if (isset($buttons['link'])) : ?>
                    <div><?= $buttons['link'] ?></div>
                    <?php endif ?>

                    <?php if (isset($buttons['lightbox'])) : ?>
                    <div><?= $buttons['lightbox'] ?></div>
                    <?php endif ?>

                </div>

            </div>

        </div>
    <?php else : ?>

        <?php if ($item['title'] && $settings['title']) : ?>
        <<?= $settings['title_element'] ?> class="<?= $title_size ?> {wk}-margin-remove-bottom"><?= $item['title'] ?></<?= $settings['title_element'] ?>>
        <?php endif ?>

        <?php if ($item['content'] && $settings['content'] && $settings['overlay_center'] != 'content') : ?>
        <div><?= $item['content'] ?></div>
        <?php endif ?>

    <?php endif ?>

    <?php if ($settings['panel'] != 'blank') : ?>
    </div>
    <?php endif ?>

</div>
