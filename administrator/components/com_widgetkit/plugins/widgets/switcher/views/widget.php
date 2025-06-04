<?php

// Id
$settings['id'] = substr(uniqid(), -3);

// Width
$nav_width = '{wk}-width-' . $settings['width'] . '@m';

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
}

$content_width = '{wk}-width-' . $content_width . '@m';

?>

<?php if ($settings['position'] == 'top' || $settings['position'] == 'bottom') : ?>

<div<?php if ($settings['class']) echo ' class="' . $settings['class'] . '"' ?>>

    <?php if ($settings['position'] == 'top') : ?>
    <?= $this->render('plugins/widgets/' . $widget->getConfig('name') . '/views/_nav.php', compact('items', 'settings')) ?>
    <?php endif ?>

    <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_content.php', compact('items', 'settings')) ?>

    <?php if ($settings['position'] == 'bottom') : ?>
    <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
    <?php endif ?>

</div>

<?php else : ?>

<div class="{wk}-grid {wk}-grid-match <?= $settings['class'] ?>" {wk}-height-match="target: &gt; div &gt; ul" {wk}-grid>
    <div class="<?= $nav_width ?><?php if ($settings['position'] == 'right') echo ' {wk}-flex-last@m' ?>">
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
    </div>
    <div class="<?= $content_width ?>">
        <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_content.php', compact('items', 'settings')) ?>
    </div>
</div>

<?php endif ?>
