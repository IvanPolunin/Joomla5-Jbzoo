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
    <?php if ($this->checkPosition('media')) : ?>
        <div class="item-media">
            <?php echo $this->renderPosition('media'); ?>
        </div>
    <?php endif; ?>

    <div class="card-body">
        <?php if ($this->checkPosition('title')) : ?>
            <h5 class="card-title item-title"><?php echo $this->renderPosition('title'); ?></h5>
        <?php endif; ?>

        <?php if ($this->checkPosition('meta')) : ?>
            <div class="item-meta mb-2">
                <?php echo $this->renderPosition('meta'); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('description')) : ?>
            <div class="item-text card-text">
                <?php echo $this->renderPosition('description', array('style' => 'block')); ?>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($this->checkPosition('links')) : ?>
        <div class="card-footer bg-transparent border-top-0">
            <div class="item-links">
                <?php echo $this->renderPosition('links', array('style' => 'pipe')); ?>
            </div>
        </div>
    <?php endif; ?>
</div>