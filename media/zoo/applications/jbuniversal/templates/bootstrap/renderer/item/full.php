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
$document = JFactory::getDocument();
//$document->setMetadata('description', strip_tags(trim($this->renderPosition('title')." в Санкт-Петербурге с доставкой по России от официального дистрибьютора с гарантией качества. Масла и смазки в наличии на складе и под заказ по отличным ценам с качественным сервисом.")));




$align      = $this->app->jbitem->getMediaAlign($item, $layout);
$tabsId     = $this->app->jbstring->getId('tabs');
$bootstrap = $this->app->jbbootstrap;
$rowClass   = $bootstrap->getRowClass();
?>

<?php if ($this->checkPosition('title')) : ?>
    <h1 class="item-title"><?php echo $this->renderPosition('title'); ?></h1>
<?php endif; ?>

<div class="clearfix">
   <div class="<?php echo $rowClass; ?>">
      <div class="<?php echo $bootstrap->gridClass(3); ?>">
         <div class="item-image">
            <!--<img src="<?php echo JURI::base().'/images/smazka.png'; ?>" width="400" height="219" alt="Смазка" />-->
            <?php echo $this->renderPosition('image', array('style' => 'block')); ?>
         </div>
      </div>
      <div class="<?php echo $bootstrap->gridClass(9); ?>">
         <?php if ($this->checkPosition('manufacturer')) : ?>
            <div class="item-manufacturer">
               <?php echo $this->renderPosition('manufacturer', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
         <?php if ($this->checkPosition('tare')) : ?>
            <div class="item-tare">
               <?php echo $this->renderPosition('tare', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
         <?php if ($this->checkPosition('type-application')) : ?>
            <div class="item-type-application">
               <?php echo $this->renderPosition('type-application', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
         <?php if ($this->checkPosition('properties')) : ?>
            <div class="item-properties">
               <?php echo $this->renderPosition('properties', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
         <?php if ($this->checkPosition('analogues')) : ?>
            <div class="item-analogues">
               <?php echo $this->renderPosition('analogues', array(
                  'style' => 'jbblock',
                  'wrapperTag' => 'span'
               )); ?>
            </div>
         <?php endif; ?>
      </div>
   </div>

   <?php if ($this->checkPosition('description')) : ?>
      <div class="<?php echo $rowClass; ?>">
         <div class="<?php echo $bootstrap->gridClass(12); ?>">
            <div class="item-description">
               <?php echo $this->renderPosition('description', array('style' => 'block')); ?>
            </div>
         </div>
      </div>
   <?php endif; ?>
</div>

<div class="item-tabs">
    <ul id="<?php echo $tabsId; ?>" class="nav nav-tabs">
        <?php if ($this->checkPosition('price')) : ?>
            <li class="active">
                <a href="#item-price" id="price-tab" data-toggle="tab" class="active">
                    <?php echo JText::_('JBZOO_ITEM_TAB_PRICE'); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('adaptation')) : ?>
            <li>
                <a href="#item-adaptation" id="adaptation-tab" data-toggle="tab">
                    <?php echo JText::_('JBZOO_ITEM_TAB_ADAPTATION'); ?>
                </a>
            </li>
        <?php endif; ?>

        <?php if ($this->checkPosition('specifications')) : ?>
            <li>
                <a href="#item-specifications" id="specifications-tab" data-toggle="tab">
                    <?php echo JText::_('JBZOO_ITEM_TAB_SPECIFICATIONS'); ?>
                </a>
            </li>
        <?php endif; ?>
    </ul>
    <div id="<?php echo $tabsId; ?>Content" class="tab-content">
        <?php if ($this->checkPosition('price')) : ?>
            <div class="tab-pane fade active in" id="item-price">
                <div class="item-price">
                    <?php echo $this->renderPosition('price', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('adaptation')) : ?>
            <div class="tab-pane fade" id="item-adaptation">
                <div class="item-adaptation">
                    <?php echo $this->renderPosition('adaptation', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>

        <?php if ($this->checkPosition('specifications')) : ?>
            <div class="tab-pane fade" id="item-specifications">
                <div class="item-specifications">
                    <?php echo $this->renderPosition('specifications', array('style' => 'block')); ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<style>
    .delivery { margin-top: 40px; border-top: 1px solid #000; padding-top: 15px;}
    .master h3{ font-size: 18px; }
    .master:hover{ cursor: pointer; }
    .deliverycity { padding-top: 20px; }
    .deliverycity p { display: inline-block;
        margin-right: 10px; }
    .slave{ display: none; }
    .open { display: block;  }
</style>
<script>
    let delivery = document.querySelector('.delivery');
    let master = delivery.querySelector('.master');
    let slave = delivery.querySelector('.slave');
    master.addEventListener('click', function (event) {
        slave.classList.toggle('open');
    })
</script>
<div class="delivery">
    <div style="" class="master" id="m2">
        <h3>Куда мы доставляем масла и смазки</h3></div>
    <div class="slave" id="">
        <div class="slavecontent">
            <div class="deliverycity">
                <p>Москва</p><p>Санкт-Петербург</p><p>Новосибирск</p><p>Екатеринбург</p><p>Нижний Новгород</p><p>Казань</p><p>Самара</p><p>Омск</p><p>Челябинск</p><p>Ростов-на-Дону</p><p>Уфа</p><p>Волгоград</p><p>Красноярск</p><p>Пермь</p><p>Воронеж</p><p>Саратов</p><p>Краснодар</p><p>Тольятти</p><p>Тюмень</p><p>Ижевск</p><p>Барнаул</p><p>Ульяновск</p><p>Иркутск</p><p>Владивосток</p><p>Ярославль</p><p>Хабаровск</p><p>Махачкала</p><p>Оренбург</p><p>Новокузнецк</p><p>Томск</p><p>Кемерово</p><p>Рязань</p><p>Астрахань</p><p>Пенза</p><p>Набережные Челны</p><p>Липецк</p><p>Тула</p><p>Киров</p><p>Чебоксары</p><p>Калининград</p><p>Курск</p><p>Улан-Удэ</p><p>Магнитогорск</p><p>Ставрополь</p><p>Брянск</p><p>Иваново</p><p>Тверь</p><p>Белгород</p><p>Сочи</p><p>Нижний Тагил</p><p>Архангельск</p><p>Владимир</p><p>Калуга</p><p>Смоленск</p><p>Чита</p><p>Волжский</p><p>Курган</p><p>Сургут</p><p>Орел</p><p>Череповец</p><p>Владикавказ</p><p>Вологда</p><p>Мурманск</p><p>Саранск</p><p>Якутск</p><p>Тамбов</p><p>Грозный</p><p>Стерлитамак</p><p>Кострома</p><p>Петрозаводск</p><p>Нижневартовск</p><p>Комсомольск-на-Амуре</p><p>Йошкар-Ола</p><p>Таганрог</p><p>Новороссийск</p><p>Братск</p><p>Сыктывкар</p><p>Нальчик</p><p>Дзержинск</p><p>Шахты</p><p>Орск</p><p>Балашиха</p><p>Нижнекамск</p><p>Ангарск</p><p>Старый Оскол</p><p>Химки</p><p>Великий Новгород</p><p>Благовещенск</p><p>Энгельс</p><p>Подольск</p><p>Псков</p><p>Бийск</p><p>Прокопьевск</p><p>Рыбинск</p><p>Балаково</p><p>Армавир</p><p>Южно-Сахалинск</p><p>Северодвинск</p><p>Королев</p><p>Петропавловск-Камчатский</p><p>Люберцы</p><p>Мытищи</p><p>Норильск</p><p>Сызрань</p><p>Новочеркасск</p><p>Златоуст</p><p>Каменск-Уральский</p><p>Абакан</p><p>Волгодонск</p><p>Уссурийск</p><p>Находка</p><p>Электросталь</p><p>Салават</p><p>Березники</p><p>Миасс</p><p>Альметьевск</p><p>Рубцовск</p><p>Коломна</p><p>Майкоп</p><p>Пятигорск</p><p>Железнодорожный</p><p>Ковров</p><p>Копейск</p><p>Одинцово</p><p>Хасавюрт</p><p>Кисловодск</p><p>Новомосковск</p><p>Красногорск</p><p>Серпухов</p><p>Нефтеюганск</p><p>Черкесск</p><p>Первоуральск</p><p>Нефтекамск</p><p>Новочебоксарск</p><p>Орехово-Зуево</p><p>Дербент</p><p>Димитровград</p><p>Невинномысск</p><p>Камышин</p><p>Батайск</p><p>Новый Уренгой</p><p>Кызыл</p><p>Муром</p><p>Щелково</p><p>Октябрьский</p><p>Новошахтинск</p><p>Северск</p><p>Ачинск</p><p>Ноябрьск</p><p>Сергиев Посад</p><p>Елец</p><p>Жуковский</p><p>Новокуйбышевск</p><p>Обнинск</p><p>Арзамас</p><p>Домодедово</p><p>Пушкино</p><p>Элиста</p><p>Каспийск</p><p>Артем</p><p>Ессентуки</p><p>Назрань</p><p>Ногинск</p><p>Раменское</p><p>Бердск</p><p>Сарапул</p>
            </div>
        </div>
    </div>
</div>