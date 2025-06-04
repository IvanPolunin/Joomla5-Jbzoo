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

$this->app->jbdebug->mark('template::filter::start');

$this->app->jblayout->setView($this);

if (!$this->app->jbcache->start()) {
    $this->app->jbwrapper->start();

?>
<div  class="catzag containerr">

            <h1 class="h2title z9 matit"><?php echo $this->title ? $this->title : JText::_('JBZOO_SEARCH_RESULT'); ?></h1>
			  
          
       
		<div class="bc">{module 17}</div


<?php if ($this->description) : ?>
    <div class="description z9">
        <?php echo $this->description; ?>
    </div>
<?php endif; ?>

<?php

    if ($this->items) {

        if ($this->count) {
            echo '<p class="z9">' . JText::_('JBZOO_FILTER_TOTAL_RESULT') . ': ' . $this->itemsCount . '</p>';
        }

        // items
        echo $this->app->jblayout->render('items', $this->items);

        // pagination render
        echo $this->app->jblayout->render('pagination', $this->pagination, array('link' => $this->pagination_link));

    } else {
        ?><p class="z9"><?php echo JText::_('JBZOO_FILTER_ITEMS_NOT_FOUND'); ?></p><?php
    }

    $this->app->jbwrapper->end();
    $this->app->jbcache->stop();
}
?>
</div>

<?php $this->app->jbdebug->mark('template::filter::finish'); ?>
