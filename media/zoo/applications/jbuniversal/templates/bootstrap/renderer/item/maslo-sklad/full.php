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

//закрытие от индексации
$document = JFactory::getDocument(); 
$document->setMetaData('robots', 'noindex, nofollow');


$align      = $this->app->jbitem->getMediaAlign($item, $layout);
$tabsId     = $this->app->jbstring->getId('tabs');
$bootstrap = $this->app->jbbootstrap;
$rowClass   = $bootstrap->getRowClass();
?>

<?php if ($this->checkPosition('title')) : ?>
    <h1 class="item-title"><?php echo $this->renderPosition('title'); ?></h1>
<?php endif; ?>

<div class="clearfix">
   <div class="<?php echo $rowClass; ?>">
      <div class="<?php echo $bootstrap->gridClass(4); ?>">
         <div class="item-image">
            <img src="<?php echo JURI::base().'/images/maslo.png'; ?>" width="400" height="486" alt="Масло" />
         </div>
      </div>
      <div class="<?php echo $bootstrap->gridClass(8); ?>">
         <?php if ($this->checkPosition('manufacturer')) : ?>
            <div class="item-manufacturer">
               <?php echo $this->renderPosition('manufacturer', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
         <?php if ($this->checkPosition('tare')) : ?>
            <div class="item-tare">
               <?php echo $this->renderPosition('tare', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
      </div>
   </div>

   <?php if ($this->checkPosition('description')) : ?>
      <div class="<?php echo $rowClass; ?>">
         <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <div class="item-description">
               <?php echo $this->renderPosition('description', array('style' => 'block')); ?>
            </div>
         </div>
      </div>
   <?php endif; ?>
</div>

<div class="item-tabs">
    <ul id="<?php echo $tabsId; ?>" class="nav nav-tabs">
        <?php if ($this->checkPosition('price')) : ?>
            <li class="active">
                <a href="#item-price" id="price-tab" data-toggle="tab" class="active">
                    <?php echo JText::_('JBZOO_ITEM_TAB_PRICE'); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('adaptation')) : ?>
            <li>
                <a href="#item-adaptation" id="adaptation-tab" data-toggle="tab">
                    <?php echo JText::_('JBZOO_ITEM_TAB_ADAPTATION'); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('specifications')) : ?>
            <li>
                <a href="#item-specifications" id="specifications-tab" data-toggle="tab">
                    <?php echo JText::_('JBZOO_ITEM_TAB_SPECIFICATIONS'); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div id="<?php echo $tabsId; ?>Content" class="tab-content">
        <?php if ($this->checkPosition('price')) : ?>
            <div class="tab-pane fade active in" id="item-price">
                <div class="item-price">
                    <?php echo $this->renderPosition('price', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('adaptation')) : ?>
            <div class="tab-pane fade" id="item-adaptation">
                <div class="item-adaptation">
                    <?php echo $this->renderPosition('adaptation', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('specifications')) : ?>
            <div class="tab-pane fade" id="item-specifications">
                <div class="item-specifications">
                    <?php echo $this->renderPosition('specifications', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>