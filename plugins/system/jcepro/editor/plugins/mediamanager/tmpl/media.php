<?php
/**
 * @package     JCE
 * @subpackage  Editor
 *
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

defined('JPATH_PLATFORM') or die;

foreach (array('flash', 'audio', 'video', 'aggregator') as $tpl) {
    echo $this->loadTemplate($tpl);
}
