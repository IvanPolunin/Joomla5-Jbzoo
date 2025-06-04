<?php

// Filter Nav

switch ($settings['filter']) {
    case 'text':
        $filter_nav = '{wk}-subnav';
        break;
    case 'lines':
        $filter_nav = '{wk}-subnav {wk}-subnav-divider';
        break;
    case 'nav':
        $filter_nav = '{wk}-subnav {wk}-subnav-pill';
        break;
    case 'tabs':
        $filter_nav = '{wk}-tab';
        break;
}

$filter_nav .= ($settings['filter_align'] != 'left') ? ' {wk}-flex-' . $settings['filter_align'] : '';

?>

<ul class="<?= $filter_nav ?>">

    <?php if ($settings['filter_all']) : ?>
    <li class="{wk}-active" {wk}-filter-control><a href="#"><?= $app['translator']->trans('All') ?></a></li>
    <?php endif ?>

    <?php foreach ($tags as $i => $tag) : ?>
    <?php $selector = htmlspecialchars("[data-tag~='" . str_replace(["'", ' '], ["\'", '__'], $tag) . "']", ENT_QUOTES) ?>
    <li <?= !$settings['filter_all'] && $i == '0' ? 'class="{wk}-active"' : '' ?> {wk}-filter-control="<?= $selector ?>"><a href="#"><?= ucwords($tag) ?></a></li>
    <?php endforeach ?>

</ul>

