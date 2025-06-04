<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Filter\OutputFilter;
use Joomla\CMS\Language\Text;

defined('_JEXEC') or die('Restricted access');

// filter content
OutputFilter::objectHTMLSafe($this->comment->content);

?>

<tr id="edit-comment-editor">
	<td colspan="4">
		<div class="head">
			<label for="author"><?php echo Text::_('Name'); ?></label>
			<input id="author" type="text" name="author" value="<?php echo $this->comment->author; ?>" />
			<label for="email"><?php echo Text::_('E-Mail'); ?></label>
			<input id="email" type="text" name="email" value="<?php echo $this->comment->email; ?>" />
			<label for="url"><?php echo Text::_('URL'); ?></label>
			<input id="url" type="text" name="url" value="<?php echo $this->comment->url; ?>" />
		</div>
		<div class="content">
			<textarea name="content" cols="1" rows="1"><?php echo $this->comment->content; ?></textarea>
		</div>
		<div class="actions">
			<button class="btn btn-small save" type="button"><?php echo Text::_('Update Comment'); ?></button>
			<a href="#" class="cancel"><?php echo Text::_('Cancel'); ?></a>
		</div>
		<input type="hidden" name="cid" value="<?php echo $this->comment->id; ?>" />
	</td>
</tr>
