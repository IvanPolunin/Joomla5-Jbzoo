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
   </div>
   <div class="<?php echo $bootstrap->gridClass(3); ?> col-12 col-sm-12">
      <div class="item-button">
         <?php echo $this->renderPosition('button', array('style' => 'block')); ?>
      </div>
   </div>
</div>