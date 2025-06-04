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

$align = $this->app->jbitem->getMediaAlign($item, $layout);
?>

<div class="relbl">
<div class="row">
<div class="col-lg-4"><?php if ($this->checkPosition('image')) : ?>
    <div class="item-image">
        <?php echo $this->renderPosition('image'); ?>
    </div>
<?php endif; ?></div>
<div class="col-lg-8">
<?php if ($this->checkPosition('title')) : ?>
    <h5 class="item-title"><?php echo $this->renderPosition('title'); ?></h5>
<?php endif; ?>
<div class="relpr">
<?php if ($this->checkPosition('properties')) : ?>
        <?php echo $this->renderPosition('properties'); ?>
<?php endif; ?>

</div>
<a href="<?php echo $this->app->route->item($this->_item); ?>" class="catrm"><span><svg enable-background="new 0 0 64 64" viewBox="0 0 64 64" xmlns="http://www.w3.org/2000/svg"><g id="right"><path d="m58.76 31.256-12.047-10.813c-.412-.37-1.044-.335-1.412.076-.369.411-.335 1.043.076 1.412l10.103 9.069h-49.572c-.552 0-1 .448-1 1s.448 1 1 1h49.572l-10.104 9.068c-.411.369-.445 1.001-.076 1.412.197.22.47.332.745.332.238 0 .477-.084.667-.256l12.048-10.812c.211-.189.332-.46.332-.744s-.121-.555-.332-.744z"></path></g></svg></span>Перейти
 </a>
</div>
</div>
</div>



