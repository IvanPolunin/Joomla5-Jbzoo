<?php
use Joomla\CMS\Language\Text;
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

$align      = $this->app->jbitem->getMediaAlign($item, $layout);
$tabsId     = $this->app->jbstring->getId('tabs');
$bootstrap = $this->app->jbbootstrap;
$rowClass   = $bootstrap->getRowClass();
?>

<?php if ($this->checkPosition('title')) : ?>
    <h1 class="item-title"><?php echo $this->renderPosition('title'); ?></h1>
<?php endif; ?>

<div class="card mb-4">
    <div class="card-body">
        <div class="<?php echo $rowClass; ?>">
            <div class="<?php echo $bootstrap->gridClass(6); ?>">
                <?php if ($this->checkPosition('image')) : ?>
                    <div class="item-image mb-3">
                        <?php echo $this->renderPosition('image'); ?>
                    </div>
                <?php endif; ?>

                <?php if ($this->checkPosition('meta')) : ?>
                    <div class="<?php echo $rowClass; ?> item-metadata">
                        <div class="<?php echo $bootstrap->gridClass(12); ?>">
                            <ul class="list-unstyled mb-0">
                                <?php echo $this->renderPosition('meta', array('style' => 'list')); ?>
                            </ul>
                        </div>
                    </div>
                <?php endif; ?>

                <?php if ($this->checkPosition('buttons')) : ?>
                    <div class="<?php echo $rowClass; ?> item-buttons">
                        <div class="<?php echo $bootstrap->gridClass(12); ?>">
                            <?php echo $this->renderPosition('buttons', array('style' => 'block')); ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($this->checkPosition('price')) : ?>
                <div class="<?php echo $bootstrap->gridClass(6); ?>">
                    <div class="item-price">
                        <?php echo $this->renderPosition('price'); ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <?php if ($this->checkPosition('social')) : ?>
            <div class="<?php echo $rowClass; ?> item-social mt-3">
                <div class="<?php echo $bootstrap->gridClass(12); ?>">
                    <?php echo $this->renderPosition('social', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<!-- Bootstrap 5 Tabs -->
<div class="item-tabs">
    <ul class="nav nav-tabs" id="<?php echo $tabsId; ?>" role="tablist">
        <?php if ($this->checkPosition('text')) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc" type="button" role="tab" aria-controls="desc" aria-selected="true">
                    <?php echo Text::_('JBZOO_ITEM_TAB_DESCRIPTION'); ?>
                </button>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('properties')) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="props-tab" data-bs-toggle="tab" data-bs-target="#props" type="button" role="tab" aria-controls="props" aria-selected="false">
                    <?php echo Text::_('JBZOO_ITEM_TAB_PROPS'); ?>
                </button>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('gallery')) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="gallery-tab" data-bs-toggle="tab" data-bs-target="#gallery" type="button" role="tab" aria-controls="gallery" aria-selected="false">
                    <?php echo Text::_('JBZOO_ITEM_TAB_GALLERY'); ?>
                </button>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('comments')) : ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments" type="button" role="tab" aria-controls="comments" aria-selected="false">
                    <?php echo Text::_('JBZOO_ITEM_TAB_COMMENTS'); ?>
                    <span class="badge bg-secondary ms-1"><?php echo $item->getCommentsCount(); ?></span>
                </button>
            </li>
        <?php endif; ?>
    </ul>
    
    <div class="tab-content" id="<?php echo $tabsId; ?>Content">
        <?php if ($this->checkPosition('text')) : ?>
            <div class="tab-pane fade show active" id="desc" role="tabpanel" aria-labelledby="desc-tab">
                <div class="item-text">
                    <?php echo $this->renderPosition('text', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('properties')) : ?>
            <div class="tab-pane fade" id="props" role="tabpanel" aria-labelledby="props-tab">
                <table class="table table-hover">
                    <?php echo $this->renderPosition('properties', array(
                        'tooltip' => true,
                        'style'   => 'jbtable',
                    )); ?>
                </table>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('gallery')) : ?>
            <div class="tab-pane fade" id="gallery" role="tabpanel" aria-labelledby="gallery-tab">
                <?php echo $this->renderPosition('gallery', array(
                    'labelTag' => 'h4',
                    'style'    => 'jbblock',
                )); ?>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('comments')) : ?>
            <div class="tab-pane fade" id="comments" role="tabpanel" aria-labelledby="comments-tab">
                <?php echo $this->renderPosition('comments'); ?>
            </div>
        <?php endif; ?>
    </div>
</div>

<?php if ($this->checkPosition('related')) : ?>
    <div class="<?php echo $rowClass; ?> item-related mt-4">
        <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <?php echo $this->renderPosition('related', array(
                'labelTag' => 'h4',
                'style'    => 'jbblock',
            )); ?>
        </div>
    </div>
<?php endif; ?>

<article class="clearfix">

<?php if ($this->checkPosition('title')) : ?>
    <h1 class="item-title"><?php echo $this->renderPosition('title'); ?></h1>
<?php endif; ?>

<?php if ($this->checkPosition('meta')) : ?>
    <div class="iteminfo">
                        <ul class="unstyled">
                            <?php echo $this->renderPosition('meta', array('style' => 'list')); ?>
                        </ul>
    </div>
<?php endif; ?>


<?php if ($this->checkPosition('image')) : ?>
                <div class="item-image">
                    <?php  echo $this->renderPosition('image');?>
                </div>
<?php endif; ?>



<?php if ($this->checkPosition('buttons')) : ?>
                        <?php echo $this->renderPosition('buttons', array('style' => 'block')); ?>
<?php endif; ?>
  

<?php if ($this->checkPosition('pretext')) : ?>
                <div class="pretext">
                    <?php echo $this->renderPosition('pretext'); ?>
                </div>
<?php endif; ?>

<?php if ($this->checkPosition('fulltext')) : ?>
                <div class="fulltext">
                    <?php echo $this->renderPosition('fulltext'); ?>
                </div>
<?php endif; ?>

<?php if ($this->checkPosition('images')) : ?>
                <div class="images">
                    <?php echo $this->renderPosition('images'); ?>
                </div>
<?php endif; ?>

<?php if ($this->checkPosition('tags')) : ?>
                <div class="tags">
                <ul>   <?php echo $this->renderPosition('tags', array('style' => 'list')); ?> </ul>
                </div>
<?php endif; ?>


<?php if ($this->checkPosition('video')) : ?>
                <div class="oldvideo">
                <?php echo $this->renderPosition('video'); ?>
                </div>
<?php endif; ?>

<?php // dd($item); ?>


<?php if ($this->checkPosition('from')) : ?>
                <div class="from">
             <ul>   <?php echo $this->renderPosition('from', array('style' => 'list')); ?> </ul>
                </div>
<?php endif; ?>


<?php if ($this->checkPosition('related')) : ?>
                <div class="from">
             <ul>   <?php echo $this->renderPosition('from', array('style' => 'list')); ?> </ul>
                </div>
<?php endif; ?>


<?php if ($this->checkPosition('social')) : ?>
                <?php echo $this->renderPosition('social', array('style' => 'block')); ?>
<?php endif; ?>
    
</article>