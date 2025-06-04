<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die('Restricted access');

?>

<?php $edit_link = $this->app->route->submission($item->getApplication()->getItemEditSubmission(), $item->type, null, $item->id, 'itemedit'); ?>

<a href="<?php echo Route::_($edit_link); ?>" title="<?php echo Text::_('Edit Item'); ?>" class="item-icon edit-item"><?php echo Text::_('Edit Item'); ?></a>
