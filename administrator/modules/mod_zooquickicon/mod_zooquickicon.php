<?php
/**
 * @package   ZOO Quick Icons
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Router\Route;

// no direct access
defined('_JEXEC') or die('Restricted access');

// load config
if (!file_exists(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php')) {
	return;
}
require_once(JPATH_ADMINISTRATOR.'/components/com_zoo/config.php');
if (!ComponentHelper::getComponent('com_zoo', true)->enabled) {
    return;
}

// make sure App class exists
if (!class_exists('App')) {
	return;
}

$zoo = App::getInstance('zoo');

$applications = $zoo->table->application->all(array('order' => 'name'));

if (empty($applications)) {
	return;
}

?>

<div class="sidebar-nav quick-icons">
	<h2 class="nav-header">ZOO</h2>
	<ul class="nav nav-list">
	<?php foreach ($applications as $application) : ?>
	<li>
		<a href="<?php echo Route::_('index.php?option='.$zoo->component->self->name.'&changeapp='.$application->id); ?>">
			<img style="width:24px; height:24px;" alt="<?php echo $application->name; ?>" src="<?php echo $application->getIcon(); ?>" />
			<span><?php echo $application->name; ?></span>
		</a>
	</li>
	<?php endforeach; ?>
	</ul>
</div>
