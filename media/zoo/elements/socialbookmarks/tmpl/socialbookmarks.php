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

// include assets css
$this->app->document->addStylesheet('elements:socialbookmarks/assets/css/socialbookmarks.css');

?>

<div class="yoo-zoo socialbookmarks">

	<?php foreach ($bookmarks as $name => $data) : ?>
		<?php $title = ($name == "email") ? Text::_('Recommend this Page') : Text::_('Add this Page to') . ' ' . ucfirst($name); ?>
		<a class="<?php echo $name ?>" onclick="<?php echo $data['click']; ?>" href="<?php echo $data['link']; ?>" title="<?php echo $title; ?>"></a>
	<?php endforeach; ?>

</div>
