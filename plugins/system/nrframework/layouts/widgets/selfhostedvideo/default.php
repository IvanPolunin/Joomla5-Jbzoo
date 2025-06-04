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

$options = isset($options) ? $options : $displayData;

if (!$options['video'] || !is_array($options['video']))
{
	return;
}

$attributes = array_filter(array(
	isset($options['controls']) && $options['controls'] ? 'controls' : '',
	isset($options['loop']) && $options['loop'] ? 'loop' : '',
	isset($options['mute']) && $options['mute'] ? 'muted' : '',
	isset($options['autoplay']) && $options['autoplay'] ? 'autoplay playsinline' : ''
));
?>
<div class="nrf-widget tf-video selfhostedvideo<?php echo $css_class ? ' ' . $css_class : ''; ?>" id="<?php echo $options['id']; ?>">
	<div class="tf-video-embed" <?php echo $options['atts']; ?>>
		<video
			preload="<?php echo $options['preload']; ?>"
			controlsList="nodownload"
			<?php echo implode(' ', $attributes); ?>
			style="max-width:100%;">
			<source data-src="<?php echo $options['video']['file']; ?>" type="video/<?php echo $options['video']['ext']; ?>" />
			<?php echo Text::sprintf('NR_UNSUPPORTED_TAG', 'video'); ?>
		</video>
	</div>
</div>