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

defined('_JEXEC') or die;

use Joomla\CMS\Factory;

extract($displayData);

$options = isset($options) ? $options : $displayData;

if (!$options['value'])
{
	return;
}

if ($options['load_css_vars'])
{
	$atts = [];

	// Add cover image
	if (isset($options['coverImageType']) && isset($options['coverImage']) && in_array($options['coverImageType'], ['auto', 'custom']) && !empty($options['coverImage']))
	{
		$atts['video-cover-image'] = $options['coverImage'];
	}

	$css = \NRFramework\Helpers\CSS::cssVarsToString($atts, '.nrf-widget.tf-video.' . $options['id']);

	Factory::getDocument()->addStyleDeclaration($css);
}
?>
<div class="nrf-widget tf-video<?php echo $css_class ? ' ' . $css_class : ''; ?>" id="<?php echo $options['id']; ?>" data-readonly="<?php echo var_export($options['readonly']); ?>" data-disabled="<?php echo var_export($options['disabled']); ?>">
	<div class="tf-video-embed-wrapper">
		<div class="tf-video-embed" <?php echo $options['atts']; ?>>
			<div id="tf_video_embed_<?php echo $options['id']; ?>"></div>
		</div>

		<?php if (isset($options['coverImageType']) && isset($options['coverImage']) && $options['coverImageType'] !== 'none' && !empty($options['coverImage'])): ?>
			<div class="tf-video-embed-overlay"><div class="play-button"></div></div>
		<?php endif; ?>
	</div>
</div>