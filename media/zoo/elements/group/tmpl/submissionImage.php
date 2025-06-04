<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;

defined('_JEXEC') or die('Restricted access');

$this->app->document->addScript('elements:group/submission.js');

$image = $this->get($element['name']);
if (!empty($image)) {
    $image = $this->app->path->url('root:' . $image);
}

$identifier = uniqid('element');

?>

<div id="<?php echo $identifier; ?>">

    <div class="image-select">

        <div class="upload">
            <input type="text" id="filename<?php echo $identifier; ?>" readonly="readonly" />
            <div class="button-container">
                <button class="button-grey search" type="button"><?php echo Text::_('Search'); ?></button>
                <input type="file" name="<?php echo $this->getControlName($element['name']); ?>" onchange="javascript: document.getElementById('filename<?php echo $identifier; ?>').value = this.value.replace(/^.*[\/\\]/g, '');" />
            </div>
        </div>

        <?php if ($params['trusted_mode']) : ?>

            <span class="select"><?php echo Text::_('ALREADY UPLOADED'); ?></span><?php echo $this->getImageSelectList($element['name']); ?>

        <?php else : ?>

            <input type="hidden" class="image" name="<?php echo $this->getControlName($element['name']); ?>" value="<?php echo $image ? 1 : ''; ?>">

        <?php endif; ?>

    </div>

    <div class="image-preview">
        <img src="<?php echo $image; ?>" alt="preview">
        <span class="image-cancel" title="<?php Text::_('Remove image'); ?>"></span>
    </div>

</div>

<script type="text/javascript">
    jQuery(function($) {
        $('#item-submission #<?php echo $identifier; ?>').GroupImageSubmission({ uri: '<?php echo Uri::root(); ?>' });
    });
</script>
