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

?>

<div class="{wk}-panel">

    <div class="{wk}-inline-clip {wk}-transition-toggle <?= $border ?>">

        <?= $thumbnail ?>

        <div class="{wk}-overlay {wk}-overlay-primary {wk}-position-bottom {wk}-transition-<?= $settings['overlay_animation'] ?>">

            <?php if ($buttons) : ?>
            <div class="{wk}-grid-small {wk}-flex-middle" {wk}-grid>

                <?php if (($item['title'] && $settings['title']) || ($item['content'] && $settings['content'])) : ?>
                <div class="{wk}-width-expand">

                    <?php if ($item['title'] && $settings['title']) : ?>
                    <<?= $settings['title_element'] ?> class="<?= $title_size ?> {wk}-margin-remove-bottom"><?= $item['title'] ?></<?= $settings['title_element'] ?>>
                    <?php endif ?>

                    <?php if ($item['content'] && $settings['content']) : ?>
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

                <?php if ($item['content'] && $settings['content']) : ?>
                <div><?= $item['content'] ?></div>
                <?php endif ?>

            <?php endif ?>

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
