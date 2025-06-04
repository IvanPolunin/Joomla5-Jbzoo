<?php

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

// Overlays
$hover_overlay = ($settings['hover_overlay']) ? '{wk}-transition-' . $settings['overlay_animation'] : '';
$background = ($settings['overlay_background'] == 'hover') ? '{wk}-transition-' . $settings['overlay_animation'] : '';

?>

<div class="{wk}-panel">

    <div class="{wk}-inline-clip {wk}-transition-toggle <?= $border ?>">

        <?= $thumbnail ?>

        <?php if ($settings['overlay_background'] != 'none') : ?>
        <div class="{wk}-overlay {wk}-overlay-primary {wk}-position-cover <?= $background ?>"></div>
        <?php endif ?>

        <div class="{wk}-overlay {wk}-position-cover <?= $hover_overlay ?> {wk}-flex {wk}-flex-center {wk}-flex-middle {wk}-text-center {wk}-light">
            <div>

                <?php if ($item['title'] && $settings['title']) : ?>
                <<?= $settings['title_element'] ?> class="<?= $title_size ?> {wk}-margin-small"><?= $item['title'] ?></<?= $settings['title_element'] ?>>
                <?php endif ?>

                <?php if ($item['content'] && $settings['content']) : ?>
                <div class="{wk}-margin-small"><?= $item['content'] ?></div>
                <?php endif ?>

                <?php if ($buttons) : ?>
                <div class="{wk}-grid {wk}-grid-small {wk}-flex-center {wk}-margin" {wk}-grid>

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

        <?php if (!$buttons) : ?>
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

</div>
