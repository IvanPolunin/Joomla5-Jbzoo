<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

?>

<div>

    <div class="row">
        <?php echo $this->app->html->_('control.text', $this->getControlName('value'), $this->get('value', ''), 'maxlength="255" title="'.Text::_('Tags').'" placeholder="'.Text::_('Tags').'"'); ?>
    </div>

    <div class="row">
        <?php echo $this->app->html->_('control.text', $this->getControlName('flickrid'), $this->get('flickrid', ''), 'maxlength="255" title="'.Text::_('Flickr ID').'" placeholder="'.Text::_('Flickr ID').'"'); ?>
    </div>

</div>
