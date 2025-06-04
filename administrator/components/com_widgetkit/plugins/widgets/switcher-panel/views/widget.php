<?php

// Id
$settings['id'] = substr(uniqid(), -3);

// Panel
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
}

// Image
$image = $settings['image'];

if ($settings['image_hero_width'] != 'auto' || $settings['image_hero_height'] != 'auto') {

    $width  = ($settings['image_hero_width'] != 'auto') ? $settings['image_hero_width'] : '';
    $height = ($settings['image_hero_height'] != 'auto') ? $settings['image_hero_height'] : '';

    $image = $app['image']->thumbnailUrl($settings['image'], $width, $height);

}

?>

<div class="<?= $panel ?> <?= $settings['class'] ?>">

    <?php if ($image) : ?>
    <div class="<?= $settings['panel'] != 'blank' ? '{wk}-card-media-top' : '' ?> {wk}-background-cover {wk}-position-relative" style="background-image: url('<?= $app['url']->to($image) ?>');">

        <img class="{wk}-invisible" style="min-height: <?= $settings['image_min_height'] ?>px;" src="<?= $image ?>" alt="">

        <div class="{wk}-position-bottom {wk}-position-medium <?php if ($settings['contrast']) echo '{wk}-light' ?>">
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name') . '/views/_nav.php', compact('items', 'settings')) ?>
        </div>

    </div>
    <?php else : ?>
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name') . '/views/_nav.php', compact('items', 'settings')) ?>
    <?php endif ?>

    <div class="<?= $settings['panel'] != 'blank' ? '{wk}-card-body' : '{wk}-margin-top' ?>">
    <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_content.php', compact('items', 'settings')) ?>
    </div>

</div>
