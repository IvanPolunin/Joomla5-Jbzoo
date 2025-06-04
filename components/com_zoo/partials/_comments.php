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

// add js and css
$this->app->document->addScript('libraries:jquery/plugins/cookie/jquery-cookie.js');
$this->app->document->addScript('assets:js/comment.js');
$this->app->document->addStylesheet('assets:css/comments.css');

// css classes
$css[] = 'level1';
$css[] = $params->get('max_depth', 5) > 1 ? 'nested' : null;
$css[] = $params->get('registered_users_only') && $active_author->isGuest() ? 'no-response' : null;

?>

<div id="comments">

    <?php if ($count = count($comments)-1) : ?>
	<h3 class="comments-meta"><?php echo Text::_('Comments').' ('.$count.')'; ?></h3>

    <ul class="<?php echo implode("\n", $css); ?>">
        <?php
        foreach ($comments[0]->getChildren() as $comment) {
            echo $this->partial('comment', array('level' => 1, 'comment' => $comment, 'author' => $comment->getAuthor(), 'params' => $params));
        }
        ?>
    </ul>
    <?php endif; ?>
	<?php
		if($item->isCommentsEnabled()) :
			echo $this->partial('respond', compact('active_author', 'params', 'item', 'captcha'));
		endif;

        if($item->canManageComments()) :
            echo $this->partial('edit');
        endif;
	?>

</div>

<script type="text/javascript">
	jQuery(function($) {
		$('#comments').Comment({
			cookiePrefix: '<?php echo CommentHelper::COOKIE_PREFIX; ?>',
			cookieLifetime: '<?php echo CommentHelper::COOKIE_LIFETIME; ?>',
			msgCancel: '<?php echo Text::_('Cancel'); ?>'
		});
	});
</script>
