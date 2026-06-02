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

$align     = $this->app->jbitem->getMediaAlign($item, $layout);
$bootstrap = $this->app->jbbootstrap;
$rowClass  = $bootstrap->getRowClass();
?>

<div class="card h-100">
    <?php if ($this->checkPosition('image')) : ?>
        <div class="item-image">
            <?php echo $this->renderPosition('image'); ?>
        </div>
    <?php endif; ?>

    <div class="card-body">
        <?php if ($this->checkPosition('title')) : ?>
            <h5 class="card-title item-title"><?php echo $this->renderPosition('title'); ?></h5>
        <?php endif; ?>

        <?php if ($this->checkPosition('properties')) : ?>
            <div class="item-properties mb-2">
                <ul class="list-unstyled mb-0">
                    <?php echo $this->renderPosition('properties', array('style' => 'list')); ?>
                </ul>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('text')) : ?>
            <div class="item-text card-text">
                <?php echo $this->renderPosition('text', array('style' => 'block')); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($this->checkPosition('price') || $this->checkPosition('buttons')) : ?>
        <div class="card-footer bg-transparent border-top-0">
            <?php if ($this->checkPosition('price')) : ?>
                <div class="item-price mb-2">
                    <?php echo $this->renderPosition('price', array('style' => 'block')); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->checkPosition('quick-view')) : ?>
                <div class="item-quick-view mb-2">
                    <?php echo $this->renderPosition('quick-view', array('style' => 'block')); ?>
                </div>
            <?php endif; ?>

            <?php if ($this->checkPosition('buttons')) : ?>
                <div class="item-buttons">
                    <?php echo $this->renderPosition('buttons'); ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endif; ?>
</div>
