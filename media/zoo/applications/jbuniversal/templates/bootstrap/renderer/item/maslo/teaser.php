<?php
/**
 * JBZoo Application
 *
 * This file is part of the JBZoo CCK package.
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * @package    Application
 * @license    GPL-2.0
 * @copyright  Copyright (C) JBZoo.com, All rights reserved.
 * @link       https://github.com/JBZoo/JBZoo
 * @author     Denis Smetannikov <denis@jbzoo.com>
 */

// no direct access
defined('_JEXEC') or die('Restricted access');

$bootstrap      = $this->app->jbbootstrap;
$rowClass       = $bootstrap->getRowClass();
$titleHtml      = $this->renderPosition('title');
$propertiesHtml = $this->renderPosition('properties', array('style' => 'list'));
$descriptionText = '';
$descriptionElements = $this->getPositionElements('description');
if (!empty($descriptionElements) && method_exists($descriptionElements[0], 'getValue')) {
   $descriptionValue = $descriptionElements[0]->getValue(array('display' => 'first'));
   if (is_array($descriptionValue)) {
      $descriptionValue = implode(' ', array_filter($descriptionValue, 'strlen'));
   }
   $descriptionText = trim(strip_tags((string) $descriptionValue));
}
if ($descriptionText === '') {
   $descriptionText = trim(strip_tags($this->renderPosition('description', array('style' => 'block'))));
}
$priceHtml      = $this->renderPosition('price', array('style' => 'block'));
$buttonHtml     = $this->renderPosition('button', array('style' => 'block'));
?>

<div class="<?php echo $rowClass; ?>">
   <div class="<?php echo $bootstrap->gridClass(8); ?> col-12 col-sm-12">
      <?php if (trim($titleHtml) !== '') : ?>
         <h4 class="item-title"><?php echo $titleHtml; ?></h4>
      <?php endif; ?>
      <?php if (trim($propertiesHtml) !== '') : ?>
         <div class="item-properties">
            <ul class="unstyled">
               <?php echo $propertiesHtml; ?>
            </ul>
         </div>
      <?php endif; ?>
      <?php if ($descriptionText !== '') : ?>
         <div class="item-description">
            <?php echo JHtmlString::truncate($descriptionText, 200); ?>
         </div>
      <?php endif; ?>
   </div>
   <div class="<?php echo $bootstrap->gridClass(4); ?> col-12 col-sm-12">
      <?php if (trim($priceHtml) !== '') : ?>
         <?php
         $priceText = trim(strip_tags($priceHtml));
         $priceZero = ($priceHtml === '' || $priceText === '');
         if (!$priceZero && preg_match('/jbcurrency-value[^>]*>\\s*0([\\s,.]*0+)?\\s*</iu', $priceHtml)) {
            $priceZero = true;
         }
         if (!$priceZero && preg_match('/data-value="0(\\.0+)?"/i', $priceHtml)) {
            $priceZero = true;
         }
         if (!$priceZero) {
            $normalized = preg_replace('/[^0-9.,]/', '', $priceText);
            if ($normalized === '' || preg_match('/^0([,.]0+)?$/', $normalized)) {
               $priceZero = true;
            }
         }
         ?>
         <div class="item-price">
            <?php if ($priceZero) : ?>
               <span class="price-request">Р¦РµРЅР° РїРѕ Р·Р°РїСЂРѕСЃСѓ</span>
            <?php else : ?>
               <?php echo $priceHtml; ?>
            <?php endif; ?>
         </div>
      <?php endif; ?>
      <div class="item-button">
        <?php echo $buttonHtml; ?>
         <!--<div class="element">
            <div class="custom">
               <p><span id="buy-btn" class="ba-click-lightbox-form-5 forms-trigger buy-btn">РљСѓРїРёС‚СЊ</span></p></div>
         </div>-->
      </div>
   </div>
</div>
