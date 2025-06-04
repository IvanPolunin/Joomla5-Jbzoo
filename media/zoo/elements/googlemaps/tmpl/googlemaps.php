<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$src = "https://maps.google.com/maps/api/js?language=$locale&key=$key&callback=Function.prototype";

// add js, encode src in Joomla 3
if (version_compare(JVERSION, '4.0', '<')) {
    $src = htmlspecialchars($src);
}
$this->app->system->document->addScript($src);
$this->app->document->addScript('elements:googlemaps/googlemaps.js');

?>
<div class="googlemaps" style="<?php echo $css_module_width ?>">

	<?php if ($information) : ?>
	<p class="mapinfo"><?php echo $information; ?></p>
	<?php endif; ?>

	<div id="<?php echo $maps_id ?>" style="<?php echo $css_module_width . $css_module_height ?>"></div>

</div>
<?php echo "<script type=\"text/javascript\">\n// <!--\n$javascript\n// -->\n</script>\n";
