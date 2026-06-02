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

<table class="table">
   <tbody>
      <tr>
         <?php if ($this->checkPosition('title')) : ?>
            <td class="subitem-title"><h2 class="item-title"><?php echo $this->renderPosition('title'); ?></h2></td>
         <?php endif; ?>
         <?php if ($this->checkPosition('price')) : ?>
            <td class="subitem-price">
               <?php echo $this->renderPosition('price'); ?>
            </td>
         <?php endif; ?>
            <td class="subitem-button">
               <div class="custom">
	              <p><span id="buy-btn" class="ba-click-lightbox-form-5 forms-trigger buy-btn">Купить</span></p>
	           </div>
            </td>
      </tr>
   </tbody>
</table>