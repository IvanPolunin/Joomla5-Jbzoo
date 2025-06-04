<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

use \Joomla\CMS\Plugin\PluginHelper;

/**
 * Deals with comment events.
 *
 * @package Component.Events
 */
class CommentEvent {

	/**
	 * Placeholder for the init event
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function init($event) {

		$comment = $event->getSubject();

	}

	/**
	 * Subscribes authors to the comments, sends notifications emails and
	 * triggers joomla content plugins on the comment
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function saved($event) {

		// init vars
		$comment = $event->getSubject();
		$app	 = $comment->app;
		$new	 = (bool) @$event['new'];
		$params  = $app->parameter->create($comment->getItem()->getApplication()->getParams()->get('global.comments.'));

		// send email to admins
		if ($new && $comment->state != Comment::STATE_SPAM && ($recipients = $params->get('email_notification', ''))) {
			$app->comment->sendNotificationMail($comment, array_flip(explode(',', $recipients)), 'mail.comment.admin.php');
		}

		// send email notification to subscribers
		if ((($new && $comment->state == Comment::STATE_APPROVED) || ($comment->state != $event['old_state'] && $comment->state == Comment::STATE_APPROVED)) && $params->get('email_reply_notification', false)) {

			// subscribe author to item
			$app->table->item->save($comment->getItem()->subscribe($comment->getAuthor()->email, $comment->getAuthor()->name));

			$app->comment->sendNotificationMail($comment, $comment->getItem()->getSubscribers(), 'mail.comment.reply.php');

		} elseif ($comment->state == Comment::STATE_SPAM) {

			// unsubscribe author from item
			$app->table->item->save($comment->getItem()->unsubscribe($comment->getAuthor()->email));

		}

		PluginHelper::importPlugin('content');
		$app->system->application->triggerEvent('onContentAfterSave', array(
            $comment->app->component->self->name.'.comment',
            &$comment,
            $new)
        );

	}

	/**
	 * Triggers joomla content plugins on the comment
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function deleted($event) {

		$comment = $event->getSubject();

		PluginHelper::importPlugin('content');
        $comment->app->system->application->triggerEvent('onContentAfterDelete', array(
            $comment->app->component->self->name.'.comment',
            &$comment)
        );

	}

	/**
	 * Triggers joomla content plugins on the comment
	 *
	 * @param  AppEvent $event The event triggered
	 */
	public static function stateChanged($event) {

		$comment = $event->getSubject();

		PluginHelper::importPlugin('content');
        $comment->app->system->application->triggerEvent('onContentChangeState', array(
            $comment->app->component->self->name.'.comment',
            array($comment->id),
            $comment->state)
        );

	}

}
