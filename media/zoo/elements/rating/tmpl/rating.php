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

// include assets js/css
$this->app->document->addScript('elements:rating/assets/js/rating.js');
$this->app->document->addStylesheet('elements:rating/assets/css/rating.css');

$id = $this->identifier.'-'.uniqid();

?>
<div id="<?php echo $id; ?>" class="yoo-zoo rating">

	<div class="rating-container star<?php echo $stars; ?>">
		<div class="previous-rating" style="width: <?php echo intval($rating / $stars * 100); ?>%;"></div>

		<?php if (!$disabled) : ?>
		<div class="current-rating">

			<?php for($i = $stars; $i > 0; $i--) : ?>
			<div class="stars star<?php echo $i; ?>" title="<?php echo $i.' '.Text::_('out of').' '.$stars; ?>"></div>
			<?php endfor ?>

		</div>
		<?php endif; ?>
	</div>

	<?php if ($show_message) : ?>
	<div class="vote-message">
		<?php echo $rating.'/<strong>'.$stars.'</strong> '.Text::sprintf($votes == 1 ? 'rating %s vote' : 'rating %s votes', $votes); ?>
	</div>
	<?php endif; ?>

	<?php if ($show_microdata) : ?>
	<div itemscope itemtype="http://data-vocabulary.org/Review-aggregate">
		<meta itemprop="itemreviewed" content="<?php echo $this->getItem()->name; ?>" />
		<div itemprop="rating" itemscope itemtype="http://data-vocabulary.org/Rating">
			<meta itemprop="average" content="<?php echo number_format($rating, 1); ?>" />
			<meta itemprop="best" content="<?php echo $stars; ?>" />
		</div>
		<meta itemprop="votes" content="<?php echo $votes; ?>"/>
	</div>
	<?php endif; ?>

</div>
<?php if (!$disabled) : ?>
	<script type="text/javascript">
		jQuery(function($) {
			$('#<?php echo $id; ?>').ElementRating({ url: '<?php echo $link; ?>' });
		});
	</script>
<?php endif;
