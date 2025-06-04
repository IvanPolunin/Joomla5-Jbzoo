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

?>
<tr id="edit-comment-editor">
	<td colspan="4">
		<div class="head">Reply to Comment</div>
		<div class="content">
			<textarea name="content" cols="1" rows="1"></textarea>
		</div>
		<div class="actions">
			<button class="btn btn-small save" type="button"><?php echo Text::_('Submit Reply'); ?></button>
			<a href="#" class="cancel"><?php echo Text::_('Cancel'); ?></a>
		</div>
		<input type="hidden" name="cid" value="0" />
		<input type="hidden" name="parent_id" value="<?php echo $this->cid; ?>" />
	</td>
</tr>
