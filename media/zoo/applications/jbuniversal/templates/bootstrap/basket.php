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

$this->app->jbdebug->mark('template::basket::start');
$this->app->jblayout->setView($this);
$this->app->jbwrapper->start();

?>
<div  class="catzag containerr">

        <h1 class="h2title z9 matit">Корзина</h1>
       
		<div class="bc">{module 17}</div>
</div>


<?php

echo $this->app->jblayout->renderIndex('basket');

$this->app->jbwrapper->end();
$this->app->jbdebug->mark('template::basket::finish');
