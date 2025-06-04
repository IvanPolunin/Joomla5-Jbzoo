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


?>




    <?php if ($this->checkPosition('image')) : ?>
              <div class="item-images">  <?php echo $this->renderPosition('image'); ?></div>
    <?php endif; ?>
	<?php if ($this->checkPosition('title')) : ?>
    <h3 class="jbcategory-titles"><?php echo $this->renderPosition('title'); ?></h3>
<?php endif; ?>
<?php if ($this->checkPosition('price')) : ?>

            <div class="item-price">
                <?php echo $this->renderPosition('price', array('style' => 'block')); ?>
            </div>
<?php endif; ?>





<?php if ($this->checkPosition('buttons')) : ?>
<div class="nal"><?php echo $this->renderPosition('buttons'); ?></div>
 
<?php endif; ?>
