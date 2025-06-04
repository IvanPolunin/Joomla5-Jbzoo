<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

echo $this->app->html->_('zoo.countryselectlist', $this->app->country->getIsoToNameMapping(), $control_name.'[selectable_country][]', $parent->element->config->get('selectable_country', array()), true);