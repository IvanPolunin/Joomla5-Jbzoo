<?php

// Id
$settings['id'] = substr(uniqid(), -3);

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
}

// Media Width
if ($settings['media_align'] == 'top') {

    $media_width = '{wk}-width-1-1';
    $content_width = '{wk}-width-1-1';

} else {

    $media_width = '{wk}-width-' . $settings['media_width'] . '@' . $settings['media_breakpoint'];

    switch ($settings['media_width']) {
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
    }

    $content_width = '{wk}-width-' . $content_width . '@' . $settings['media_breakpoint'];

    // Align Right
    $media_width .= ($settings['media_align'] == 'right') ? ' {wk}-flex-last@' . $settings['media_breakpoint'] : '';

}

$media_width .= $settings['panel'] != 'blank' ? ' {wk}-card-media-' . $settings['media_align'] : '';

// Content Align
$content_align  = ($settings['content_align'] && $settings['media_align'] != 'top') ? '{wk}-flex-middle' : '';

// Title Size
switch ($settings['title_size']) {
    case 'medium':
        $title_size = '{wk}-heading-medium {wk}-margin-remove-top';
        break;
    default:
        $title_size = '{wk}-' . $settings['title_size'] . ' {wk}-margin-remove-top';
}

// Content Size
switch ($settings['content_size']) {
    case 'large':
        $content_size = '{wk}-text-large';
        break;
    case 'h1':
    case 'h2':
    case 'h3':
    case 'h4':
    case 'h5':
    case 'h6':
        $content_size = '{wk}-' . $settings['content_size'];
        break;
    default:
        $content_size = '';
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

// Custom Class
$class = $settings['class'] ? ' class="' . $settings['class'] . '"' : '';

?>

<div id="wk-<?= $settings['id'] ?>"<?= $class ?>>
    <div class="<?= $panel ?> {wk}-overflow-hidden">

        <?php if ($settings['media']) : ?>
        <div class="{wk}-grid {wk}-grid-collapse <?= $content_align ?>" {wk}-grid>
            <div class="<?= $media_width ?> {wk}-text-center">
                <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_media.php', compact('items', 'settings', 'widget')) ?>
            </div>
            <div class="<?= $content_width ?>">
        <?php endif ?>

        <div class="<?= $settings['panel'] == 'blank' ? '{wk}-padding' : '{wk}-card-body' ?> {wk}-text-<?= $settings['text_align'] ?>">
            <ul id="wk-switcher<?= $settings['id'] ?>" class="{wk}-switcher">
                <?php foreach ($items as $item) : ?>

                <li>

                    <?php if ($item['title'] && $settings['title']) : ?>
                    <<?= $settings['title_element'] ?> class="<?= $title_size ?>">

                        <?php if ($item['link']) : ?>
                            <a class="{wk}-link-reset" href="<?= $item->escape('link') ?>" <?= $link_target ?>><?= $item['title'] ?></a>
                        <?php else : ?>
                            <?= $item['title'] ?>
                        <?php endif ?>

                        <?php if ($item['badge'] && $settings['badge']) : ?>
                        <span class="{wk}-margin-small-left <?= $badge_style ?>"><?= $item['badge'] ?></span>
                        <?php endif ?>

                    </<?= $settings['title_element'] ?>>
                    <?php endif ?>

                    <?php if ($item['content'] && $settings['content']) : ?>
                    <div class="<?= $content_size ?> {wk}-margin-top"><?= $item['content'] ?></div>
                    <?php endif ?>

                    <?php if ($item['link'] && $settings['link']) : ?>
                    <p class="{wk}-margin-remove-bottom"><a<?php if($link_style) echo ' class="' . $link_style . '"' ?> href="<?= $item->escape('link') ?>"<?= $link_target ?>><?= $app['translator']->trans($settings['link_text']) ?></a></p>
                    <?php endif ?>

                </li>

            <?php endforeach ?>
            </ul>

            <?php if (!$settings['nav_overlay'] && ($settings['nav'] != 'none')) : ?>
            <div class="{wk}-margin-top">
                <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
            </div>
            <?php endif ?>

            <?php if ($settings['nav'] == 'none') : ?>
                <div style="display: none">
                    <?= $this->render('plugins/widgets/' . $widget->getConfig('name')  . '/views/_nav.php', compact('items', 'settings')) ?>
                </div>
            <?php endif ?>

        </div>

        <?php if ($settings['media']) : ?>
            </div>
        </div>
        <?php endif ?>

    </div>
</div>
