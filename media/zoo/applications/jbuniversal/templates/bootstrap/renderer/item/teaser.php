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

$bootstrap       = $this->app->jbbootstrap;
$rowClass        = $bootstrap->getRowClass();
$titleHtml       = $this->renderPosition('title');
$propertiesHtml  = $this->renderPosition('properties', array('style' => 'list'));
$priceHtml       = $this->renderPosition('price', array('style' => 'block'));
$textHtml        = $this->renderPosition('text', array('style' => 'block'));
$quickViewHtml   = $this->renderPosition('quick-view', array('style' => 'block'));
$buttonsHtml     = $this->renderPosition('buttons');
?>

<?php if (trim($titleHtml) !== '') : ?>
    <h4 class="item-title"><?php echo $titleHtml; ?></h4>
<?php endif; ?>

<div class="<?php echo $rowClass; ?>">
    <?php if (trim($propertiesHtml) !== '') : ?>
        <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <div class="item-properties">
                <ul class="unstyled">
                    <?php echo $propertiesHtml; ?>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php if (trim($priceHtml) !== '') : ?>
    <div class="<?php echo $rowClass; ?>">
        <div class="<?php echo $bootstrap->gridClass(6); ?>">
            <div class="item-price">
                <?php echo $priceHtml; ?>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php if (trim($textHtml) !== '') : ?>
    <div class="item-text <?php echo $rowClass; ?>">
        <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <?php echo $textHtml; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (trim($quickViewHtml) !== '') : ?>
    <div class="item-quick-view <?php echo $rowClass; ?>">
        <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <?php echo $quickViewHtml; ?>
        </div>
    </div>
<?php endif; ?>

<?php if (trim($buttonsHtml) !== '') : ?>
    <div class="<?php echo $rowClass; ?> item-buttons clearfix">
        <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <?php echo $buttonsHtml; ?>
        </div>
    </div>
<?php endif; ?>
