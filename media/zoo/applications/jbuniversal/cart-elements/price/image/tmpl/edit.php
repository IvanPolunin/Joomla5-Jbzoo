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

// Initialize JBZooMedia widget
$this->app->jbassets->widget('#' . $unique, 'JBZooMedia', array(
    'message_open_editor' => Text::_('JBZOO_ELEMENT_PRICE_IMAGE_DESC_IMAGE')
));
?>

<div class="jsMedia jbprice-img-row-file" id="<?php echo $unique; ?>">
    <div style="display: flex; gap: 5px; align-items: center;">
        <?php
        echo $this->_jbhtml->text($this->getControlName('value'), $value, array(
            'class'       => 'jsJBPriceImage jsMediaValue row-file',
            'placeholder' => Text::_('JBZOO_ELEMENT_PRICE_IMAGE_EDIT_PLACEHOLDER'),
            'style'       => 'flex: 1;'
        )); ?>
        
        <button type="button" class="jsMediaButton btn btn-small">
            <i class="icon-plus"></i> <?php echo Text::_('JBZOO_ELEMENT_PRICE_IMAGE_DESC_IMAGE'); ?>
        </button>
        
        <span class="jbmedia-cancel image-cancel jsMediaCancel" style="cursor: pointer;">×</span>
    </div>
</div>
