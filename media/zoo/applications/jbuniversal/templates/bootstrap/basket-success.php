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

$this->app->jbdebug->mark('template::basket-success::start');
$this->app->jblayout->setView($this);
$this->app->document->setTitle(JText::_('JBZOO_CART_ITEMS'));
$this->app->jbwrapper->start();

?>
<div  class="containerr">

        <h1 class="h2title z9 matit">Заказ успешно создан</h1>
       
		<div class="bc">{module 17}</div>
</div>




<?php
$this->app->jbwrapper->end();
$this->app->jbdebug->mark('template::basket-success::finish');
