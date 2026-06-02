<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$align = $this->app->jbitem->getMediaAlign($item, $layout);
?>

<div class="card h-100">
    <?php if ($this->checkPosition('image')) : ?>
        <div class="item-image">
            <?php echo $this->renderPosition('image'); ?>
        </div>
    <?php endif; ?>

    <div class="card-body">
        <?php if ($this->checkPosition('title')) : ?>
            <h6 class="card-title item-title"><?php echo $this->renderPosition('title'); ?></h6>
        <?php endif; ?>

        <?php if ($this->checkPosition('properties')) : ?>
            <ul class="item-properties list-unstyled mb-2 small">
                <?php echo $this->renderPosition('properties', array('style' => 'list')); ?>
            </ul>
        <?php endif; ?>

        <?php if ($this->checkPosition('text')) : ?>
            <div class="item-text card-text small">
                <?php echo $this->renderPosition('text', array('style' => 'block')); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($this->checkPosition('meta')) : ?>
        <div class="card-footer bg-transparent border-top-0 pt-0">
            <ul class="item-metadata list-unstyled mb-0 small text-muted">
                <?php echo $this->renderPosition('meta', array('style' => 'list')); ?>
            </ul>
        </div>
    <?php endif; ?>
</div>
