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

<div class="<?php echo $rowClass; ?>">
   <div class="<?php echo $bootstrap->gridClass(9); ?> col-12 col-sm-12">
      <?php if ($this->checkPosition('title')) : ?>
         <h4 class="item-title"><?php echo $this->renderPosition('title'); ?></h4>
      <?php endif; ?>
      <?php if ($this->checkPosition('properties')) : ?>
         <div class="item-properties">
            <ul class="unstyled">
               <?php echo $this->renderPosition('properties', array('style' => 'list')); ?>
            </ul>
         </div>
      <?php endif; ?>
      <?php if ($this->checkPosition('description')) : ?>
         <div class="item-description">
            <?php echo JHtmlString::truncate(strip_tags($this->renderPosition('description', array('style' => 'block'))), 200); ?>
         </div>
      <?php endif; ?>
      <?php if ($this->checkPosition('image')) : ?>
         <div class="item-image" style="display: none;">
            <?php echo $this->renderPosition('image', array('style' => 'block')); ?>
         </div>
      <?php endif; ?>
   </div>
   <div class="<?php echo $bootstrap->gridClass(3); ?> col-12 col-sm-12">
      <div class="item-button">
         <?php echo $this->renderPosition('button', array('style' => 'block')); ?>
         <!--<div class="element">
            <div class="custom">
               <p><span id="buy-btn" class="ba-click-lightbox-form-5 forms-trigger buy-btn">Купить</span></p></div>
         </div>-->
      </div>
   </div>
</div>