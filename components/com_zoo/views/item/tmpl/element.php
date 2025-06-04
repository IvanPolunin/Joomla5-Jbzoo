<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

defined('_JEXEC') or die('Restricted access');

// add js
$this->app->document->addStylesheet('assets:css/ui.css');

// load element
require_once($this->app->path->path('component.admin:views/item/tmpl/element.php'));

?>
<style>
	table.list tfoot td { text-align: center; }

	table.list tfoot td a {
		text-decoration: none;
		cursor: pointer;
	}
</style>