<?php
/**
 * @package   System - ZOO YOOtheme Pro
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl-2.0.html GNU/GPLv2 only
 */

// only registered users can comment
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use YOOtheme\View;
use function YOOtheme\app;

$view = app(View::class);

$registered = $params->get('registered_users_only');

$this->app->document->addScript('assets:js/placeholder.js');

$props = $this->props;

// Link
$link = (!$props['link_style'] || strpos($props['link_style'], 'link-') !== false) ? 'a' : 'button';
$link = $view->el($link, [

    'class' => [
        'el-link',
        'uk-{link_style: link-(muted|text)}',
        'uk-button uk-button-{!link_style: |link-muted|link-text} [uk-button-{link_size}]',
    ],

    'name' => 'submit',
    'type' => 'submit',
    'accesskey' => 's',

], Text::_('Submit comment'));

$link_container = $view->el('div', [

    'class' => [
        'uk-margin actions',
        'uk-margin[-{link_margin}]-top {@!link_margin: remove}',
    ],

]);

?>

<div id="respond" class="uk-margin">
    <h3><?php echo Text::_('Leave a comment'); ?></h3>

    <form method="post" action="<?php echo $this->app->link(['controller' => 'comment', 'task' => 'save']); ?>">

        <?php if ($active_author instanceof CommentAuthorJoomla) : ?>
            <div class="uk-margin">
                <?php echo Text::_('Logged in as') . ' ' . $active_author->name . ' (' . Text::_('Joomla') . ')'; ?>
            </div>
        <?php elseif ($active_author instanceof CommentAuthorFacebook) : ?>
            <div class="uk-margin">
                <?php echo Text::_('Logged in as') . ' ' . $active_author->name . ' (' . Text::_('Facebook') . ')'; ?>
                - <a class="facebook-logout" href="<?php echo $this->app->link(['controller' => 'comment', 'task' => 'facebooklogout', 'item_id' => $item->id]); ?>"><?php echo Text::_('Logout'); ?></a>
            </div>
        <?php elseif ($active_author instanceof CommentAuthorTwitter) : ?>
            <div class="uk-margin">
                <?php echo Text::_('Logged in as') . ' ' . $active_author->name . ' (' . Text::_('Twitter') . ')'; ?>
                - <a class="twitter-logout" href="<?php echo $this->app->link(['controller' => 'comment', 'task' => 'twitterlogout', 'item_id' => $item->id]); ?>"><?php echo Text::_('Logout'); ?></a>
            </div>
        <?php elseif ($active_author->isGuest()) : ?>

            <?php
            $message = $registered ? Text::_('LOGIN_TO_LEAVE_COMMENT') : Text::_('You are commenting as guest.');
            ?>

            <div class="uk-margin">
                <?php echo $message; ?> <?php if ($params->get('facebook_enable') || $params->get('twitter_enable')) echo Text::_('Optional login below.'); ?>
            </div>

            <?php if ($params->get('facebook_enable') || $params->get('twitter_enable')) : ?>
                <p class="connects">

                    <?php if ($params->get('facebook_enable')) : ?>
                        <a class="facebook-connect" href="<?php echo $this->app->link(['controller' => 'comment', 'item_id' => $item->id, 'task' => 'facebookconnect']); ?>">
                            <img alt="<?php echo Text::_('Facebook'); ?>" src="<?php echo Uri::root() . 'media/zoo/assets/images/connect_facebook.png'; ?>" />
                        </a>
                    <?php endif; ?>

                    <?php if ($params->get('twitter_enable')) : ?>
                        <a class="twitter-connect" href="<?php echo $this->app->link(['controller' => 'comment', 'item_id' => $item->id, 'task' => 'twitterconnect']); ?>">
                            <img alt="<?php echo Text::_('Twitter'); ?>" src="<?php echo Uri::root() . 'media/zoo/assets/images/connect_twitter.png'; ?>" />
                        </a>
                    <?php endif; ?>

                </p>
            <?php endif; ?>

            <?php if (!$registered) : ?>

                <?php $req = $params->get('require_name_and_mail'); ?>

                <div class="uk-margin <?php if($req) echo 'required'; ?>">
                    <input id="comments-author" class="uk-input uk-form-width-large" type="text" name="author" placeholder="<?php echo Text::_('Name'); ?> <?php if ($req) echo '*'; ?>" value="<?php echo $active_author->name; ?>"/>
                </div>

                <div class="uk-margin <?php if($req) echo 'required'; ?>">
                    <input id="comments-email" class="uk-input uk-form-width-large" type="text" name="email" placeholder="<?php echo Text::_('E-mail'); ?> <?php if ($req) echo '*'; ?>" value="<?php echo $active_author->email; ?>"/>
                </div>

                <div class="uk-margin">
                    <input id="comments-url" class="uk-input uk-form-width-large" type="text" name="url" placeholder="<?php echo Text::_('Website'); ?>" value="<?php echo $active_author->url; ?>"/>
                </div>

            <?php endif; ?>

        <?php endif; ?>

        <?php if (!$registered || ($registered && !$active_author->isGuest())) : ?>

            <div class="uk-margin">
                <textarea class="uk-textarea uk-form-width-large" name="content" rows="5" cols="80" ><?php echo $params->get('content'); ?></textarea>
            </div>

            <?php if($captcha): ?>
                <?php $this->app->html->_('behavior.framework'); ?>
                <div class="uk-margin">
                    <?php
                    echo $captcha->display('captcha', 'captcha', 'captcha');
                    ?>
                </div>
            <?php endif; ?>

            <?= $link_container($props) ?>
                <?= $link($props) ?>
            <?= $link_container->end() ?>

            <input type="hidden" name="item_id" value="<?php echo $this->item->id; ?>"/>
            <input type="hidden" name="parent_id" value="0"/>
            <input type="hidden" name="redirect" value="<?php echo $this->redirect; ?>"/>
            <?php echo $this->app->html->_('form.token'); ?>

        <?php endif; ?>

    </form>
</div>
