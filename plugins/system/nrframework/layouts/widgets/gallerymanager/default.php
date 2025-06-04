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

extract($displayData);

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Layout\LayoutHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;

if (!$item_id && $context === 'module')
{
	echo Text::_('NR_GALLERY_MANAGER_PLEASE_SAVE_ITEM_FIRST');
	return;
}

if (!$disabled)
{
	HTMLHelper::_('bootstrap.modal');

	if (strpos($css_class, 'ordering-default') !== false)
	{
		HTMLHelper::script('plg_system_nrframework/vendor/sortable.min.js', ['relative' => true, 'version' => 'auto']);
	}

	// Required in the front-end for the media manager to work
	if (!defined('nrJ4'))
	{
		HTMLHelper::_('behavior.modal');
		// Front-end editing: The below script is required for front-end media library selection to work as its missing from parent window when called
		if (Factory::getApplication()->isClient('site'))
		{
			?>
			<script>
			function jInsertFieldValue(value, id) {
				var old_id = document.id(id).value;
				if (old_id != id) {
					var elem = document.id(id)
					elem.value = value;
					elem.fireEvent("change");
				}
			}
			</script>
			<?php
		}
	}
	else
	{
		HTMLHelper::_('bootstrap.dropdown', '.dropdown-toggle');
		
		$doc = Factory::getApplication()->getDocument();
		$doc->addScriptOptions('media-picker', [
			'images' => array_map(
				'trim',
				explode(
					',',
					ComponentHelper::getParams('com_media')->get(
						'image_extensions',
						'bmp,gif,jpg,jpeg,png'
					)
				)
			)
		]);

		$wam = $doc->getWebAssetManager();
		$wam->useScript('webcomponent.media-select');
		
		Text::script('JFIELD_MEDIA_LAZY_LABEL');
		Text::script('JFIELD_MEDIA_ALT_LABEL');
		Text::script('JFIELD_MEDIA_ALT_CHECK_LABEL');
		Text::script('JFIELD_MEDIA_ALT_CHECK_DESC_LABEL');
		Text::script('JFIELD_MEDIA_CLASS_LABEL');
		Text::script('JFIELD_MEDIA_FIGURE_CLASS_LABEL');
		Text::script('JFIELD_MEDIA_FIGURE_CAPTION_LABEL');
		Text::script('JFIELD_MEDIA_LAZY_LABEL');
		Text::script('JFIELD_MEDIA_SUMMARY_LABEL');
	}
}

// Use admin gallery manager path if browsing via backend
$gallery_manager_path = Factory::getApplication()->isClient('administrator') ? 'administrator/' : '';

// Javascript files should always load as they are used to populate the Gallery Manager via Dropzone
HTMLHelper::script('plg_system_nrframework/dropzone.min.js', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script('plg_system_nrframework/widgets/gallery/manager_init.js', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script('plg_system_nrframework/widgets/gallery/manager.js', ['relative' => true, 'version' => 'auto']);

if ($load_stylesheet)
{
	HTMLHelper::stylesheet('plg_system_nrframework/widgets/gallerymanager.css', ['relative' => true, 'version' => 'auto']);
}

$tags = isset($tags) ? $tags : [];
?>
<!-- Gallery Manager -->
<div
	class="nrf-widget tf-gallery-manager<?php echo $css_class; ?>"
	data-context="<?php echo $context; ?>"
	data-field-id="<?php echo $field_id; ?>"
	data-item-id="<?php echo $item_id; ?>"
>
	<?php if ($required) { ?>
		<!-- Make Joomla client-side form validator happy by adding a fake hidden input field when the Gallery is required. -->
		<input type="hidden" required class="required" id="<?php echo $id; ?>"/>
	<?php } ?>

	<!-- Actions -->
	<div class="tf-gallery-actions">
		<div class="btn-group tf-gallery-actions-dropdown<?php echo !defined('nrJ4') ? ' dropdown' : ''; ?> " title="<?php echo Text::_('NR_GALLERY_MANAGER_SELECT_UNSELECT_IMAGES'); ?>">
			<button class="btn btn-secondary add tf-gallery-actions-dropdown-current tf-gallery-actions-dropdown-action select" onclick="return false;"><i class="me-2 icon-checkbox-unchecked"></i></button>
			<button class="btn btn-secondary add dropdown-toggle dropdown-toggle-split" data-<?php echo defined('nrJ4') ? 'bs-' : ''; ?>toggle="dropdown" title="<?php echo Text::_('NR_GALLERY_MANAGER_ADD_DROPDOWN'); ?>">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				<li><a href="#" class="dropdown-item tf-gallery-actions-dropdown-action select"><?php echo Text::_('NR_GALLERY_MANAGER_SELECT_ALL_ITEMS'); ?></a></li>
				<li><a href="#" class="dropdown-item tf-gallery-actions-dropdown-action unselect is-hidden"><?php echo Text::_('NR_GALLERY_MANAGER_UNSELECT_ALL_ITEMS'); ?></a></li>
			</ul>
		</div>
		<a class="tf-gallery-regenerate-images-button icon-button" title="<?php echo Text::_('NR_GALLERY_MANAGER_REGENERATE_IMAGES_TITLE'); ?>">
			<i class="icon-refresh"></i>
			<div class="message"></div>
		</a>
		<a class="tf-gallery-remove-selected-items-button icon-button" title="<?php echo Text::_('NR_GALLERY_MANAGER_REMOVE_SELECTED_IMAGES'); ?>">
			<i class="icon-trash"></i>
		</a>
		<div class="btn-group add-button<?php echo !defined('nrJ4') ? ' dropdown' : ''; ?>">
			<button class="btn btn-success add tf-gallery-add-item-button" onclick="return false;" title="<?php echo Text::_('NR_GALLERY_MANAGER_ADD_IMAGES'); ?>"><i class="me-2 icon-pictures"></i><?php echo Text::_('NR_GALLERY_MANAGER_ADD_IMAGES'); ?></button>
			<button class="btn btn-success add dropdown-toggle dropdown-toggle-split" data-<?php echo defined('nrJ4') ? 'bs-' : ''; ?>toggle="dropdown" title="<?php echo Text::_('NR_GALLERY_MANAGER_ADD_DROPDOWN'); ?>">
				<span class="caret"></span>
			</button>
			<ul class="dropdown-menu">
				<li>
					<a <?php echo !defined('nrJ4') ? 'rel="{handler: \'iframe\', size: {x: 1000, y: 750}}" href="' . Uri::root() . $gallery_manager_path . '?option=com_media&view=images&tmpl=component&fieldid=' . $id . '_uploaded_file"' : 'href="#" data-bs-toggle="modal" data-bs-target="#tf-GalleryMediaManager-' . $id . '"'; ?> class="dropdown-item tf-gallery-browse-item-button<?php echo !defined('nrJ4') ? ' modal' : ''; ?> popup" title="<?php echo Text::_('NR_GALLERY_MANAGER_BROWSE_MEDIA_LIBRARY'); ?>"><i class="me-2 icon-folder-open"></i><?php echo Text::_('NR_GALLERY_MANAGER_BROWSE_MEDIA_LIBRARY'); ?></a>
				</li>
			</ul>
		</div>
		<input type="hidden" class="media_uploader_file" id="<?php echo $id; ?>_uploaded_file" />
	</div>
	<!-- /Actions -->

	<!-- Dropzone -->
	<div
		data-inputname="<?php echo $name; ?>"
		data-maxfilesize="<?php echo $max_file_size; ?>"
		data-maxfiles="<?php echo $limit_files; ?>"
		data-acceptedfiles="<?php echo $allowed_file_types; ?>"
		data-value='<?php echo $gallery_items ? json_encode($gallery_items, JSON_HEX_APOS) : ''; ?>'
		data-baseurl="<?php echo Uri::base(); ?>"
		data-rooturl="<?php echo Uri::root(); ?>"
		class="tf-gallery-dz">
		<!-- DZ Message Wrapper -->
		<div class="dz-message">
			<!-- Message -->
			<div class="dz-message-center">
				<span class="text"><?php echo Text::_('NR_GALLERY_MANAGER_DRAG_AND_DROP_TEXT'); ?></span>
				<span class="browse"><?php echo Text::_('NR_GALLERY_MANAGER_BROWSE'); ?></span>
			</div>
			<!-- /Message -->
		</div>
		<!-- /DZ Message Wrapper -->
	</div>
	<!-- /Dropzone -->

	<!-- Dropzone Preview Template -->
	<template class="previewTemplate">
		<div class="tf-gallery-preview-item template" data-item-id="">
			<div class="checkmark-edited-icon"><?php echo Text::_('NR_UPDATED'); ?></div>
			<div class="select-item-checkbox" title="<?php echo Text::_('NR_GALLERY_MANAGER_CHECK_TO_DELETE_ITEMS'); ?>">
				<input type="checkbox" id="<?php echo $name; ?>[select-item]" />
				<label for="<?php echo $name; ?>[select-item]">
					<svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_279_439" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20"><rect width="20" height="20" fill="#D9D9D9"/></mask><g mask="url(#mask0_279_439)"><path d="M8.83342 13.8333L14.7084 7.95829L13.5417 6.79163L8.83342 11.5L6.45841 9.12496L5.29175 10.2916L8.83342 13.8333ZM10.0001 18.3333C8.8473 18.3333 7.76397 18.1145 6.75008 17.677C5.73619 17.2395 4.85425 16.6458 4.10425 15.8958C3.35425 15.1458 2.7605 14.2638 2.323 13.25C1.8855 12.2361 1.66675 11.1527 1.66675 9.99996C1.66675 8.84718 1.8855 7.76385 2.323 6.74996C2.7605 5.73607 3.35425 4.85413 4.10425 4.10413C4.85425 3.35413 5.73619 2.76038 6.75008 2.32288C7.76397 1.88538 8.8473 1.66663 10.0001 1.66663C11.1529 1.66663 12.2362 1.88538 13.2501 2.32288C14.264 2.76038 15.1459 3.35413 15.8959 4.10413C16.6459 4.85413 17.2397 5.73607 17.6772 6.74996C18.1147 7.76385 18.3334 8.84718 18.3334 9.99996C18.3334 11.1527 18.1147 12.2361 17.6772 13.25C17.2397 14.2638 16.6459 15.1458 15.8959 15.8958C15.1459 16.6458 14.264 17.2395 13.2501 17.677C12.2362 18.1145 11.1529 18.3333 10.0001 18.3333ZM10.0001 16.6666C11.8612 16.6666 13.4376 16.0208 14.7292 14.7291C16.0209 13.4375 16.6667 11.8611 16.6667 9.99996C16.6667 8.13885 16.0209 6.56246 14.7292 5.27079C13.4376 3.97913 11.8612 3.33329 10.0001 3.33329C8.13897 3.33329 6.56258 3.97913 5.27091 5.27079C3.97925 6.56246 3.33341 8.13885 3.33341 9.99996C3.33341 11.8611 3.97925 13.4375 5.27091 14.7291C6.56258 16.0208 8.13897 16.6666 10.0001 16.6666Z" fill="currentColor"/></g></svg>
				</label>
			</div>
			<div class="tf-gallery-preview-item--actions">
				<a href="#" class="tf-gallery-preview-edit-item" title="<?php echo Text::_('NR_GALLERY_MANAGER_CLICK_TO_EDIT_ITEM'); ?>" data-toggle="modal" data-target="#tf-GalleryEditItem-<?php echo $id; ?>" data-bs-toggle="modal" data-bs-target="#tf-GalleryEditItem-<?php echo $id; ?>"><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_279_182" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20"><rect width="20" height="20" fill="#D9D9D9"/></mask><g mask="url(#mask0_279_182)"><path d="M4.16667 15.8333H5.35417L13.5 7.6875L12.3125 6.5L4.16667 14.6458V15.8333ZM2.5 17.5V13.9583L13.5 2.97917C13.6667 2.82639 13.8507 2.70833 14.0521 2.625C14.2535 2.54167 14.4653 2.5 14.6875 2.5C14.9097 2.5 15.125 2.54167 15.3333 2.625C15.5417 2.70833 15.7222 2.83333 15.875 3L17.0208 4.16667C17.1875 4.31944 17.309 4.5 17.3854 4.70833C17.4618 4.91667 17.5 5.125 17.5 5.33333C17.5 5.55556 17.4618 5.76736 17.3854 5.96875C17.309 6.17014 17.1875 6.35417 17.0208 6.52083L6.04167 17.5H2.5ZM12.8958 7.10417L12.3125 6.5L13.5 7.6875L12.8958 7.10417Z" fill="currentColor"/></g></svg></a>
				<a href="#" class="tf-gallery-preview-remove-item" title="<?php echo Text::_('NR_GALLERY_MANAGER_CLICK_TO_DELETE_ITEM'); ?>" data-dz-remove><svg width="20" height="20" viewBox="0 0 20 20" fill="none" xmlns="http://www.w3.org/2000/svg"><mask id="mask0_279_185" style="mask-type:alpha" maskUnits="userSpaceOnUse" x="0" y="0" width="20" height="20"><rect width="20" height="20" fill="#D9D9D9"/></mask><g mask="url(#mask0_279_185)"><path d="M5.83301 17.5C5.37467 17.5 4.98231 17.3368 4.65592 17.0104C4.32954 16.684 4.16634 16.2917 4.16634 15.8333V5H3.33301V3.33333H7.49967V2.5H12.4997V3.33333H16.6663V5H15.833V15.8333C15.833 16.2917 15.6698 16.684 15.3434 17.0104C15.017 17.3368 14.6247 17.5 14.1663 17.5H5.83301ZM14.1663 5H5.83301V15.8333H14.1663V5ZM7.49967 14.1667H9.16634V6.66667H7.49967V14.1667ZM10.833 14.1667H12.4997V6.66667H10.833V14.1667Z" fill="currentColor"/></g></svg></a>
			</div>
			<div class="dz-status"></div>
			<div class="dz-thumb">
				<div class="dz-progress"><span class="text"><?php echo Text::_('NR_GALLERY_MANAGER_UPLOADING'); ?></span><span class="dz-upload" data-dz-uploadprogress></span></div>
				<div class="tf-gallery-preview-in-queue"><?php echo Text::_('NR_GALLERY_MANAGER_IN_QUEUE'); ?></div>
				<img data-dz-thumbnail />
			</div>
			<textarea name="<?php echo $name; ?>[alt]" class="item-alt" placeholder="<?php echo Text::_('NR_GALLERY_MANAGER_ALT_HINT'); ?>" title="<?php echo Text::_('NR_GALLERY_MANAGER_ALT_HINT'); ?>" rows="2"></textarea>
			<div class="tf-gallery-preview-error"><div data-dz-errormessage></div></div>
			<input type="hidden" value="" class="item-source" name="<?php echo $name; ?>[source]" />
			<input type="hidden" value="" class="item-thumbnail" name="<?php echo $name; ?>[thumbnail]" />
			<input type="hidden" value="" class="item-caption" name="<?php echo $name; ?>[caption]" />
			<input type="hidden" value="" class="item-tags" name="<?php echo $name; ?>[tags]" />
		</div>
	</template>
	<!-- /Dropzone Preview Template -->

	<?php
	if (!$disabled)
	{
		// Print Joomla 4 Media Manager modal only if Gallery is not disabled
		if (defined('nrJ4'))
		{
			$opts = [
				'title'       => Text::_('NR_GALLERY_MANAGER_SELECT_ITEM'),
				'url' 		  => Route::_(Uri::root() . $gallery_manager_path . '?option=com_media&view=media&tmpl=component'),
				'height'      => '400px',
				'width'       => '800px',
				'bodyHeight'  => 80,
				'modalWidth'  => 80,
				'backdrop' 	  => 'static',
				'footer'      => '<button type="button" class="btn btn-primary tf-gallery-button-save-selected" data-bs-dismiss="modal">' . Text::_('JSELECT') . '</button>' . '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">' . Text::_('JCANCEL') . '</button>',
				'isJoomla' 	  => true
			];
			HTMLHelper::_('bootstrap.modal', '#tf-GalleryMediaManager-' . $id, $opts);
	
			$layoutData = [
				'selector' => 'tf-GalleryMediaManager-' . $id,
				'params'   => $opts,
				'body'     => ''
			];
	
			echo LayoutHelper::render('libraries.html.bootstrap.modal.main', $layoutData);
		}

		if (!$readonly)
		{
			// Print Edit Modal
			$opts = [
				'title'       => Text::_('NR_GALLERY_MANAGER_EDIT_ITEM'),
                'height'      => '100%',
                'width'       => '100%',
                'modalWidth'  => '50',
				'backdrop' 	  => 'static',
				'footer'      => '<button type="button" class="btn btn-primary tf-gallery-button-save-edited-item" data-bs-dismiss="modal" data-dismiss="modal">' . Text::_('NR_SAVE') . '</button>' .
								 '<button type="button" class="btn btn-secondary" data-bs-dismiss="modal" data-dismiss="modal">' . Text::_('JCANCEL') . '</button>'
			];
	
			$content = LayoutHelper::render('edit', [
				'tags' => $tags
			], __DIR__);
	
			echo HTMLHelper::_('bootstrap.renderModal', 'tf-GalleryEditItem-' . $id, $opts, $content);
		}
	}
	?>
</div>
<!-- /Gallery Manager -->