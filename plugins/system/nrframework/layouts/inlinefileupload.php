<?php

/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

HTMLHelper::stylesheet('plg_system_nrframework/inlinefileupload.css', ['relative' => true, 'version' => 'auto']);
HTMLHelper::script('plg_system_nrframework/inlinefileupload.js', ['relative' => true, 'version' => 'auto']);

extract($displayData);
?>
<div class="nr-inline-file-upload" data-name="<?php echo $name; ?>" data-upload-folder="<?php echo $upload_folder; ?>" data-base-url="<?php echo Uri::base(); ?>">
	<div class="loader"><?php echo Text::_('NR_LOADING'); ?></div>
	<div class="upload-area<?php echo $value ? ' hidden' : ''; ?>">
		<button class="file-selector-opener btn"><?php echo Text::_('NR_SELECT_A_FILE'); ?></button>
		<input
			type="file"
			class="file-selector"
			<?php
			if (!empty($accept))
			{
				?>
				accept="<?php echo $accept; ?>"
				<?php
			}
			?>
		/>
	</div>
	<div class="uploaded-files">
		<?php
		if ($value)
		{
			if (file_exists($value))
			{
				$filePathInfo = NRFramework\File::pathinfo($value);
				$file_name = $filePathInfo['basename'];
				$file_size = file_exists($value) ? filesize($value) : 0;
				$file_size = $file_size ? number_format($file_size / 1024, 2) . ' KB' : $file_size;
				?>
				<div class="nr-inline-file-upload-item">
					<span class="icon icon-file-2"></span>
					<span class="file-name"><?php echo $file_name; ?></span>
					<span class="size"><?php echo $file_size; ?></span>
					<a href="#" class="remove icon-cancel-circle nr-inline-file-upload-item-remove" data-confirm="<?php echo Text::_('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_ITEM') ?>"></a>
					<input type="hidden" value="<?php echo $value; ?>" name="<?php echo $name; ?>">
				</div>
				<?php
			}
			else
			{
				?>
				<div class="error visible">
					<?php echo Text::_('NR_UPLOADED_FILE_NO_LONGER_EXISTS'); ?>
					<div class="actions">
						<button class="nr-inline-file-upload-item-clear btn"><?php echo Text::_('NR_CLEAR'); ?></button>
					</div>
					<input type="hidden" value="" name="<?php echo $name; ?>">
				</div>
				<?php
			}
		}
		?>
	</div>
	<div class="error"></div>
</div>
<template class="nr-inline-file-upload-item">
	<span class="icon icon-file-2"></span>
	<span class="file-name"></span>
	<span class="size"></span>
	<a href="#" class="remove icon-cancel-circle nr-inline-file-upload-item-remove" data-confirm="<?php echo Text::_('NR_ARE_YOU_SURE_YOU_WANT_TO_DELETE_THIS_ITEM') ?>"></a>
</template>