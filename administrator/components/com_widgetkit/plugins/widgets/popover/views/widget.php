<?php

// Id
$settings['id'] = substr(uniqid(), -3);

// JS Options
$options = array();
$options[] = 'pos: ' . $settings['position'];
$options[] = 'mode: click,' . $settings['mode'];

$options = implode(';', array_filter($options));

// Panel
$panel = '';
switch ($settings['panel']) {
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

// Link Target
$link_target = ($settings['link_target']) ? ' target="_blank"' : '';

// Image
$image = $settings['image'];

if ($settings['image_hero_width'] != 'auto' || $settings['image_hero_height'] != 'auto') {

    $width  = ($settings['image_hero_width'] != 'auto') ? $settings['image_hero_width'] : '';
    $height = ($settings['image_hero_height'] != 'auto') ? $settings['image_hero_height'] : '';

    $image = $app['image']->thumbnailUrl($settings['image'], $width, $height);

}

?>

<?php if ($settings['image']) : ?>
<div class="<?= $settings['class'] ?>">
    <div class="{wk}-position-relative {wk}-display-inline-block">

        <img src="<?= $image ?>" alt="">

        <?php foreach ($items as $i => $item) :

            // Position
            $left  = isset($item['left']) && $item['left'] ? (float) $item['left'] : '';
            $top   = isset($item['top']) && $item['top'] ? (float) $item['top'] : '';

            if ($left !== 0 && !$left || $top !== 0 && !$top) continue;

            $left .= '%';
            $top  .= '%';

        ?>

        <div class="{wk}-position-absolute {wk}-visible@s" style="left:<?= $left ?>; top:<?= $top ?>;">

            <?php if ($settings['contrast']) echo '<div class="{wk}-light">' ?>

            <a href {wk}-marker></a>

            <?php if ($settings['contrast']) echo '</div>' ?>

            <div <?= $settings['width'] ? 'style="width:' . $settings['width'] . 'px;"': '' ?> {wk}-drop="<?= $options ?>">

               <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_content.php', compact('item', 'settings', 'panel', 'title_size', 'link_style', 'link_target')) ?>

            </div>

        </div>

    <?php endforeach ?>
    </div>

    <div class="{wk}-margin {wk}-hidden@s">
        <ul id="wk-popover-switcher<?= $settings['id'] ?>" class="{wk}-switcher">
            <?php foreach ($items as $i => $item) : ?>
            <li><?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_content.php', compact('item', 'settings', 'panel', 'title_size', 'link_style', 'link_target')) ?></li>
            <?php endforeach ?>
        </ul>
        <ul class="{wk}-dotnav {wk}-flex-center {wk}-margin" {wk}-switcher="connect: #wk-popover-switcher<?= $settings['id'] ?>; animation: {wk}-animation-fade;">
            <?php foreach ($items as $item) : ?>
                <li><a href="#"></a></li>
            <?php endforeach ?>
        </ul>
    </div>

</div>
<?php endif ?>
