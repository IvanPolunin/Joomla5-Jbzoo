<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

use Balbooa\Component\Forms\Site\Helper\BaformsHelper;

defined('_JEXEC') or die;

ob_start();
if (!$closedPoll) {
    $className = $field->options->suffix;
    if (in_array($field->key, self::$conditionLogic->hidden)) {
        $className .= ' hidden-condition-field';
    }
    $title = $field->options->label;
    $icon = !empty($field->options->{'submit-icon'}) ? '<i class="'.$field->options->{'submit-icon'}.'"></i>' : '';
    $keys = ['background', 'padding', 'border', 'typography', 'icon', 'shadow'];
    if ($field->options->onclick == 'message' && isset($field->options->{'message-type'}) && $field->options->{'message-type'} == 'advanced') {
        $keys[] = 'popup';
    }
    $style = '';
    $animation = !empty($field->options->animation) ? ' '.$field->options->animation : '';
    foreach ($keys as $value) {
        foreach ($field->options->{$value} as $option => $optionValue) {
            if ($option == 'link') {
                continue;
            }
            if ($option == 'font-family' && $optionValue != 'inherit' && !in_array($optionValue, self::$fonts)) {
                self::$fonts[] = $optionValue;
            }
            $style .= self::setDesignCssVariable($value, '', $option, $field->options, 'submit').';';
        }
    }
    if (BaformsHelper::isClosedForm($field->options, $id)) {
        BaformsHelper::$isClosed = true;
?>
    <div class="ba-form-expiration-message">
        <?php echo $field->options->limit->message; ?>
    </div>
<?php
    } else {
    ?>
    <div class="ba-form-field-item ba-form-submit-field <?php echo $className; ?>" data-type="submit">
        <div class="ba-form-submit-wrapper<?php echo $animation; ?>" style="<?php echo $style; ?>">
    <?php
        if (!empty($field->options->recaptcha)) {
    ?>
            <div class="ba-form-submit-recaptcha-wrapper"></div>
    <?php
        }
    ?>
            <div class="ba-form-submit-btn-wrapper">
    <?php
            if ($field->options->honeypot) {
    ?>
                <input type="checkbox" class="ba-honeypot" name="ba-honeypot">
    <?php
            }
                $messageType = $field->options->{'message-type'} ?? '';
                $popupAnimation = $field->options->{'popup-animation'} ?? '';
                $dataAttributes = 'data-captcha="'.$field->options->recaptcha.'"';
                $dataAttributes .= ' data-onclick="'.$field->options->onclick.'"';
                $dataAttributes .= ' data-popup-animation="'.$popupAnimation;
                $dataAttributes .= '" data-id="'.$field->id.'" data-message-type="'.$messageType.'"';
                $dataAttributes .= ' data-payment="'.$field->options->payment.'" data-field-id="'.$field->key.'"';
    ?>
                <a href="#" class="ba-form-submit-btn" <?php echo $dataAttributes; ?>
                    ><?php echo $icon; ?><span class="ba-form-submit-title"><?php echo $title; ?></span></a>
            </div>
        </div>
    </div>
    <?php
    }
}
$out = ob_get_contents();
if (BaformsHelper::$isClosed) {
    BaformsHelper::$closedFormMessage = $out;
}
ob_end_clean();