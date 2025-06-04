<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

?>

<div>

    <?php foreach ($this->config->get('element', array()) as $element) : ?>
    <div class="row">

        <?php /* <label style="display: block; margin-bottom: 5px"><?php echo $element['name']; ?></label> */ ?>

        <?php if ($element['type'] == 'image') : ?>
            <?php echo $this->renderLayout($this->getLayout('submissionImage.php'), compact('element', 'params')) ?>
        <?php else : ?>
            <?php echo $this->app->html->_('control.' . $element['type'], $this->getControlName($element['name']), $this->get($element['name']), 'size="60" placeholder="'.$element['name'].'"'); ?>
        <?php endif; ?>

    </div>
    <?php endforeach; ?>

</div>
