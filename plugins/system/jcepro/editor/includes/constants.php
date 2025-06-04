<?php

/**
 * @package     JCE
 * @subpackage  Admin
 *
 * @copyright   Copyright (C) 2005 - 2023 Open Source Matters, Inc. All rights reserved.
 * @copyright   Copyright (c) 2009-2024 Ryan Demmer. All rights reserved
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */
defined('JPATH_PLATFORM') or die;

// check if already defined if previous 2.9.6x version is installed over 2.9.7x as the jcepro system plugin would still be active
!defined('WF_EDITOR_PRO') or define('WF_EDITOR_PRO', '1');

define('WF_EDITOR_PRO_PLUGINS', JPATH_PLUGINS . '/system/jcepro/editor/plugins');
define('WF_EDITOR_PRO_MEDIA', JPATH_SITE . '/media/plg_system_jcepro/editor');
define('WF_EDITOR_PRO_LIBRARIES', JPATH_PLUGINS . '/system/jcepro/editor/libraries');