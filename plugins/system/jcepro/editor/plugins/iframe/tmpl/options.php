<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

use Joomla\CMS\Language\Text;

echo '<div class="options_description">' . Text::sprintf('WF_IFRAME_OPTIONS_DESC', implode(', ', $this->plugin->getAggregatorOptions())) . '</div>';
echo $this->plugin->getAggregatorTemplate();
