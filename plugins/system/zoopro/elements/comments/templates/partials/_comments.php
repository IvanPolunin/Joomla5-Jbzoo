<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

use Joomla\CMS\Language\Text;
use function YOOtheme\app;
use YOOtheme\View;

// add js and css
$this->app->document->addScript('libraries:jquery/plugins/cookie/jquery-cookie.js');
$this->app->document->addScript('assets:js/comment.js');

$view = app(View::class);
$attrs = $this->attrs;
$props = $this->props;

$el = $view->el('div', ['id' => 'comments'] + $attrs);

// Title
$title = $view->el($props['title_element'], [

    'class' => [
        'el-title',
        'uk-{title_style}',
        'uk-heading-{title_decoration}',
        'uk-font-{title_font_family}',
        'uk-text-{title_color} {@!title_color: background}',
    ],

]);

?>

<?= $el($props) ?>

    <?php if ($count = count($comments) - 1) : ?>
        <?= $title($props) ?>
            <?php if ($props['title_color'] == 'background') : ?>
                <span class="uk-text-background"><?php echo Text::_('Comments') . ' <span class="uk-text-muted">(' . $count . ')</span>'; ?></span>
            <?php elseif ($props['title_decoration'] == 'line') : ?>
                <span><?php echo Text::_('Comments') . ' <span class="uk-text-muted">(' . $count . ')</span>'; ?></span>
            <?php else : ?>
            <?php echo Text::_('Comments') . ' <span class="uk-text-muted">(' . $count . ')</span>'; ?>
            <?php endif ?>
        <?= $title->end() ?>

        <ul class="uk-comment-list uk-margin-medium-top">
            <?php
            foreach ($comments[0]->getChildren() as $comment) {
                echo $this->partial('comment', ['level' => 1, 'comment' => $comment, 'author' => $comment->getAuthor(), 'params' => $params]);
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
