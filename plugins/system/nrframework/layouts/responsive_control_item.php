<?php

/**
 * @package         Advanced Custom Fields
 * @version         2.8.0 Pro
 * 
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Language\Text;

extract($displayData);
?>
<?php if (!$hide_device_selector): ?>
<div class="nr-responsive-control--item--top">
    <div class="nr-responsive-control--item--top--breakpoint--switcher">
        <div class="nr-responsive-control--item--top--breakpoint--switcher--toggle" title="<?php echo Text::_('NR_SELECT_DEVICE_TO_SET_VALUES'); ?>">
            <?php echo $breakpoint['icon']; ?>
            <svg width="8" height="6" viewBox="0 0 8 6" fill="none" xmlns="http://www.w3.org/2000/svg"><path d="M4 5.46666L0 1.46666L0.933333 0.533325L4 3.59999L7.06667 0.533325L8 1.46666L4 5.46666Z" fill="currentColor"></path></svg>
        </div>
        <div class="nr-responsive-control--item--top--breakpoint--switcher--dropdown">
            <?php
            foreach ($breakpoints as $key => $value)
            {
                ?>
                <div class="nr-responsive-control--item--top--breakpoint--switcher--dropdown--item<?php echo $key === $device ? ' is-active' : ''; ?>" data-type="<?php echo $key; ?>">
                    <div class="nr-responsive-control--item--top--breakpoint--switcher--dropdown--item--text">
                        <?php echo $value['label']; ?>
                        <div class="nr-responsive-control--item--top--breakpoint--switcher--dropdown--item--text--desc"><?php echo $value['desc']; ?></div>
                    </div>
                    <?php echo $value['icon']; ?>
                </div>
                <?php
            }
            ?>
        </div>
    </div>
</div>
<?php endif; ?>
<?php echo $html; ?>