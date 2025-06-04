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
use Joomla\CMS\Language\Text;

extract($displayData);

$options = isset($options) ? $options : $displayData;

if ($options['load_css_vars'] && !empty($options['custom_css']))
{
	Factory::getDocument()->addStyleDeclaration($options['custom_css']);
}
?>
<div class="nrf-widget openstreetmap map-widget map-address-editor-view nr-address-component<?php echo $options['css_class']; ?>" id="<?php echo $options['id']; ?>" data-options="<?php echo htmlspecialchars(json_encode($options)); ?>">
	<?php if ($options['required']) { ?>
		<!-- Make Joomla client-side form validator happy by adding a fake hidden input field when the field is required. -->
		<input type="hidden" required class="required" id="<?php echo $options['id']; ?>" />
	<?php } ?>
	<?php if (!$options['required']): ?>
	<div class="tf-map-editor-clear-wrapper"><a href="#" class="tf-map-editor-clear"><?php echo Text::_('NR_CLEAR'); ?></a></div>
	<?php endif; ?>
	<?php
	if ($options['show_address'] === 'before_map')
	{
		require 'settings.php';
	}
	?>
	<?php if ($options['show_map']): ?><div class="map-item"></div><?php endif; ?>
	<input type="hidden" class="map-markers" name="<?php echo $options['name']; ?>[markers]" value="<?php echo $options['markers'] ? htmlspecialchars(json_encode($options['markers'])) : ''; ?>" />
	<?php
	if ($options['show_address'] || $options['show_address'] === 'after_map')
	{
		echo '<div class="tf-mapeditor-settings">';
		require 'settings.php';
		echo '</div>';
	}
	if ($options['show_markers_list'])
	{
		?>
		<div class="tf-map-markers" data-no-markers="<?php echo Text::_('NR_CREATE_YOUR_FIRST_MARKER'); ?>">
			<strong class="tf-map-markers--title"><?php echo Text::_('NR_MARKERS'); ?></strong>
			<div class="tf-map-markers--list">
				<?php
				if (count($options['markers']))
				{
					foreach ($options['markers'] as $index => $marker)
					{
						?>
						<div class="tf-map-markers--list--item" data-id="<?php echo $marker['id']; ?>">
							<div class="tf-map-markers--list--item--label--wrapper">
								<span class="tf-map-markers--list--item--label--wrapper--counter"><?php echo $index + 1; ?>.</span>
								<div class="tf-map-markers--list--item--label--wrapper--label"><?php echo !empty($marker['label']) ? $marker['label'] : $marker['address']; ?></div>
							</div>
							<div class="tf-map-markers--list--item--actions">
								<a href="#" class="tf-map-markers--list--item--actions--edit" data-bs-toggle="modal" data-bs-target="#tfMapEditorMarkerEditModal" data-toggle="modal" data-target="#tfMapEditorMarkerEditModal" title="<?php echo Text::_('NR_MAP_EDITOR_MARKER_EDIT'); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20"><path d="M9 39h2.2l22.15-22.15-2.2-2.2L9 36.8Zm30.7-24.3-6.4-6.4 2.1-2.1q.85-.85 2.1-.85t2.1.85l2.2 2.2q.85.85.85 2.1t-.85 2.1Zm-2.1 2.1L12.4 42H6v-6.4l25.2-25.2Zm-5.35-1.05-1.1-1.1 2.2 2.2Z"/></svg>
								</a>
								<a href="#" class="tf-map-markers--list--item--actions--delete" title="<?php echo Text::_('NR_MAP_EDITOR_MARKER_DELETE'); ?>">
									<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20"><path d="M13.05 42q-1.2 0-2.1-.9-.9-.9-.9-2.1V10.5H8v-3h9.4V6h13.2v1.5H40v3h-2.05V39q0 1.2-.9 2.1-.9.9-2.1.9Zm21.9-31.5h-21.9V39h21.9Zm-16.6 24.2h3V14.75h-3Zm8.3 0h3V14.75h-3Zm-13.6-24.2V39Z"/></svg>
								</a>
							</div>
						</div>
						<?php
					}
				}
				else
				{
					echo Text::_('NR_CREATE_YOUR_FIRST_MARKER');
				}
				?>
			</div>
			<div class="tf-map-markers--list--item is-template is-hidden">
				<div class="tf-map-markers--list--item--label--wrapper">
					<span class="tf-map-markers--list--item--label--wrapper--counter"></span>
					<div class="tf-map-markers--list--item--label--wrapper--label">
						<svg height="12" viewBox="0 0 120 30" xmlns="http://www.w3.org/2000/svg" fill="#dedede">
							<circle cx="15" cy="15" r="15">
								<animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="12;9;12" calcMode="linear" repeatCount="indefinite"></animate>
								<animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite"></animate>
							</circle>
							<circle cx="60" cy="15" r="9" fill-opacity="0.3">
								<animate attributeName="r" from="9" to="9" begin="0s" dur="0.8s" values="9;12;9" calcMode="linear" repeatCount="indefinite"></animate>
								<animate attributeName="fill-opacity" from="0.5" to="0.5" begin="0s" dur="0.8s" values=".5;1;.5" calcMode="linear" repeatCount="indefinite"></animate>
							</circle>
							<circle cx="105" cy="15" r="15">
								<animate attributeName="r" from="15" to="15" begin="0s" dur="0.8s" values="12;9;12" calcMode="linear" repeatCount="indefinite"></animate>
								<animate attributeName="fill-opacity" from="1" to="1" begin="0s" dur="0.8s" values="1;.5;1" calcMode="linear" repeatCount="indefinite"></animate>
							</circle>
						</svg>
					</div>
				</div>
				<div class="tf-map-markers--list--item--actions">
					<a href="#" class="tf-map-markers--list--item--actions--edit" data-bs-toggle="modal" data-bs-target="#tfMapEditorMarkerEditModal" data-toggle="modal" data-target="#tfMapEditorMarkerEditModal" title="<?php echo Text::_('NR_MAP_EDITOR_MARKER_EDIT'); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20"><path d="M9 39h2.2l22.15-22.15-2.2-2.2L9 36.8Zm30.7-24.3-6.4-6.4 2.1-2.1q.85-.85 2.1-.85t2.1.85l2.2 2.2q.85.85.85 2.1t-.85 2.1Zm-2.1 2.1L12.4 42H6v-6.4l25.2-25.2Zm-5.35-1.05-1.1-1.1 2.2 2.2Z"/></svg>
					</a>
					<a href="#" class="tf-map-markers--list--item--actions--delete" title="<?php echo Text::_('NR_MAP_EDITOR_MARKER_DELETE'); ?>">
						<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 48 48" width="20"><path d="M13.05 42q-1.2 0-2.1-.9-.9-.9-.9-2.1V10.5H8v-3h9.4V6h13.2v1.5H40v3h-2.05V39q0 1.2-.9 2.1-.9.9-2.1.9Zm21.9-31.5h-21.9V39h21.9Zm-16.6 24.2h3V14.75h-3Zm8.3 0h3V14.75h-3Zm-13.6-24.2V39Z"/></svg>
					</a>
				</div>
			</div>
			<a href="#" class="btn btn-primary tf-mapeditor-add-new-marker" data-bs-toggle="modal" data-toggle="modal" data-bs-target="#tfMapEditorMarkerAddModal" data-target="#tfMapEditorMarkerAddModal"><?php echo Text::_('NR_ADD_MARKER'); ?></a>
			<?php
			if (!$options['pro'])
			{
				\NRFramework\HTML::renderProOnlyModal($options['extension']);
				?>
				<div class="tf-mapeditor-add-unlimited-markers is-hidden">
					<a href="#" class="btn btn-danger" data-pro-only="<?php echo Text::_('NR_UNLIMITED_MARKERS'); ?>">
						<?php echo Text::_('NR_ADD_MARKER'); ?>
						<svg xmlns="http://www.w3.org/2000/svg" width="20" class="lock-icon" viewBox="0 96 960 960" fill="#fff"><path d="M222.152 981.5q-28.104 0-48.117-20.013-20.013-20.014-20.013-48.117V483.674q0-28.203 20.013-48.286 20.013-20.084 48.117-20.084h65.218v-90.739q0-80.366 56.265-136.857 56.266-56.491 136.414-56.491 80.147 0 136.364 56.491t56.217 136.857v90.739h65.218q28.202 0 48.286 20.084 20.084 20.083 20.084 48.286V913.37q0 28.103-20.084 48.117Q766.05 981.5 737.848 981.5H222.152Zm0-68.13h515.696V483.674H222.152V913.37Zm258.016-137.848q31.832 0 54.332-22.032 22.5-22.031 22.5-52.968 0-30-22.668-54.5t-54.5-24.5q-31.832 0-54.332 24.5t-22.5 55q0 30.5 22.668 52.5t54.5 22ZM355.5 415.304h249V324.62q0-52.388-36.152-88.711-36.152-36.322-88.174-36.322-52.022 0-88.348 36.322Q355.5 272.232 355.5 324.62v90.684ZM222.152 913.37V483.674 913.37Z"/></svg>
						<svg xmlns="http://www.w3.org/2000/svg" width="20" class="unlock-icon" viewBox="0 96 960 960" fill="#fff"><path d="M222.152 415.304H604.5V324.62q0-52.388-36.152-88.711-36.152-36.322-88.174-36.322-52.022 0-88.348 36.322-36.326 36.323-36.326 88.656h-68.13q0-80.516 56.265-136.932 56.266-56.416 136.414-56.416 80.147 0 136.364 56.491t56.217 136.857v90.739h65.218q28.202 0 48.286 20.084 20.084 20.083 20.084 48.286V913.37q0 28.103-20.084 48.117Q766.05 981.5 737.848 981.5H222.152q-28.104 0-48.117-20.013-20.013-20.014-20.013-48.117V483.674q0-28.203 20.013-48.286 20.013-20.084 48.117-20.084Zm0 498.066h515.696V483.674H222.152V913.37Zm258.016-137.848q31.832 0 54.332-22.032 22.5-22.031 22.5-52.968 0-30-22.668-54.5t-54.5-24.5q-31.832 0-54.332 24.5t-22.5 55q0 30.5 22.668 52.5t54.5 22ZM222.152 913.37V483.674 913.37Z"/></svg>
					</a>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		// Load edit marker modal
		require 'edit_marker_modal.php';
		// Load add marker modal
		require 'add_marker_modal.php';
	}
	?>
</div>