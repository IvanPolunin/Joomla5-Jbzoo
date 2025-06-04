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


$title = 'Купить ' . trim(strip_tags($this->renderPosition('title'))) . ' — ООО ПК «Провоздух»';
$description = 'Комплексные приточно-вытяжные вентиляционные системы - '. trim(strip_tags($this->renderPosition('title'))) . ' с доставкой по Свердловской области и всей России. Звоните +7 (932) 609-10-99 ' ;
JFactory::getDocument()->setTitle($title); 
JFactory::getDocument()->setDescription($description); 
?>

<div  class="containerr">
 <?php if ($this->checkPosition('title')) : ?>
    <h1 class="h2title matit"><?php echo $this->renderPosition('title'); ?></h1>
<?php endif; ?>
       <div class="bc">{module 17}</div>

      


<div class="row">
<div class="col-xxl-8 col-xl-12">


<div class="row">
<div class="col-lg-6">
  <?php if ($this->checkPosition('image')) : ?>
 <div class="styck">
             
				 <div id="sync1" class="owl-carousel owl-theme">
    <?php echo $this->renderPosition('image'); ?>
</div>

<div id="sync2" class="owl-carousel owl-theme">
   <?php echo $this->renderPosition('image'); ?>
</div>
</div>
<?php endif; ?>
</div>

<div class="col-lg-6 ">
<div class="wh">

 <?php if ($this->checkPosition('price')) : ?>
                <div class="full-item-price">
				<h4>Цена</h4>
                     <?php echo $this->renderPosition('price'); ?>
                </div>
        <?php endif; ?>
<?php if ($this->checkPosition('buttons')) : ?>
<div class="harbl"><h4 style="margin-bottom:20px;">Параметры</h4><?php echo $this->renderPosition('buttons' , array('style' => 'list')); ?></div>
<?php endif; ?>

<div class="harbl"><h4>Купить в один клик</h4>[forms ID=5]</div>
		<hr>
	
		<div class="disc z9">Указанные на сайте цены не являются публичной офертой (ст. 435 ГК РФ), носят ознакомительный характер и могут быть изменены в зависимости от объема закупаемой продукции и индивидуальных условий работы.</div>
	
</div></div>

<div class="col-lg-12"><div class="fulltab">


<ul class="nav nav-tabs z9" id="myTab" role="tablist">
<?php if ($this->checkPosition('text')) : ?>
  <li class="nav-item" role="presentation">
    <button class="nav-link active" id="home-tab" data-bs-toggle="tab" data-bs-target="#home" type="button" role="tab" aria-controls="home" aria-selected="true">Описание</button>
  </li>
   <?php endif; ?>
   <?php if ($this->checkPosition('properties')) : ?>
  <li class="nav-item" role="presentation">
    <button class="nav-link" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile" type="button" role="tab" aria-controls="profile" aria-selected="false">Характеристики</button>
  </li>
 <?php endif; ?>
   <?php if ($this->checkPosition('comments')) : ?>
   <li class="nav-item" role="presentation">
    <button class="nav-link" id="kompl-tab" data-bs-toggle="tab" data-bs-target="#kompl" type="button" role="tab" aria-controls="kompl" aria-selected="false">Комплектация</button>
  </li>
 <?php endif; ?>
 
   <?php if ($this->checkPosition('transport')) : ?>
   <li class="nav-item" role="presentation">
    <button class="nav-link" id="transport-tab" data-bs-toggle="tab" data-bs-target="#transport" type="button" role="tab" aria-controls="transport" aria-selected="false">Транспортные данные</button>
  </li>
 <?php endif; ?>
 


</ul>
<div class="tab-content" id="myTabContent">

<?php if ($this->checkPosition('text')) : ?>
  <div class="tab-pane fade show active" id="home" role="tabpanel" aria-labelledby="home-tab"><?php echo $this->renderPosition('text'); ?></div>
  <?php endif; ?>
   <?php if ($this->checkPosition('properties')) : ?>
  <div class="tab-pane fade" id="profile" role="tabpanel" aria-labelledby="profile-tab"><div class="responsive"><?php echo $this->renderPosition('properties'); ?></div></div>
   <?php endif; ?>
     <?php if ($this->checkPosition('comments')) : ?>
  <div class="tab-pane fade" id="kompl" role="tabpanel" aria-labelledby="kompl-tab"><div class="responsive"><?php echo $this->renderPosition('comments'); ?></div></div>
   <?php endif; ?>
   
        <?php if ($this->checkPosition('transport')) : ?>
  <div class="tab-pane fade" id="transport" role="tabpanel" aria-labelledby="transport-tab"><div class="responsive"><?php echo $this->renderPosition('transport'); ?></div></div>
   <?php endif; ?>

</div>

</div></div>
</div>
</div>


<div class="col-xxl-4 col-xl-12"><?php if ($this->checkPosition('related')) : ?>

<h3 class="h2title mb-20">Возможно вас заинтересует:</h3>

<div class="mb-40">
<div class="row">
<?php echo $this->renderPosition('related'); ?>
</div>
</div>
<?php endif; ?>

<img class="fulllogo"src="/images/logo.svg" />
<div class="fbtnbl z9">
	
	
	
		<div class="fccon">
		<a class="toptel z9" href="tel:+79326091099 ">+7 (932) 609-10-99  </a>
		<div class="hmail z9"><a href="mailto:info@провоздух.рф">info@провоздух.рф</a></div>
		</div>
	<a class="knopka2 ba-click-lightbox-form-3" href="#">Задать вопрос</a>
	</div>
	
		

</div>
</div>



		

</div>




<script>
jQuery(function ($) {
  $(".fulltab table").addClass("table table-striped");
  $('.fulltab table').wrap('<div class="responsive"></div>');
    if ($('#sync2 img').length == 1) {
        $('#sync2').hide();
    }

});
</script>
<script>

jQuery(document).ready(function($){


    var sync1 = $("#sync1");
    var sync2 = $("#sync2");
    var slidesPerPage = 4; //globaly define number of elements per page
    var syncedSecondary = true;

    sync1.owlCarousel({
        items: 1,
        slideSpeed: 2000,
        nav: true,
        autoplay: false, 
        dots: false,
        loop: true,
        responsiveRefreshRate: 200,
        navText: ['<svg width="100%" height="100%" viewBox="0 0 11 20"><path style="fill:none;stroke-width: 1px;" d="M9.554,1.001l-8.607,8.607l8.607,8.606"/></svg>', '<svg width="100%" height="100%" viewBox="0 0 11 20" version="1.1"><path style="fill:none;stroke-width: 1px;stroke: #000;" d="M1.054,18.214l8.606,-8.606l-8.606,-8.607"/></svg>'],
    }).on('changed.owl.carousel', syncPosition);

    sync2
        .on('initialized.owl.carousel', function() {
            sync2.find(".owl-item").eq(0).addClass("current");
        })
        .owlCarousel({
            items: slidesPerPage,
            dots: false,
            nav: false,
            smartSpeed: 200,
            slideSpeed: 500,
            slideBy: slidesPerPage, //alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
            responsiveRefreshRate: 100
        }).on('changed.owl.carousel', syncPosition2);

    function syncPosition(el) {
        //if you set loop to false, you have to restore this next line
        //var current = el.item.index;

        //if you disable loop you have to comment this block
        var count = el.item.count - 1;
        var current = Math.round(el.item.index - (el.item.count / 2) - .5);

        if (current < 0) {
            current = count;
        }
        if (current > count) {
            current = 0;
        }

        //end block

        sync2
            .find(".owl-item")
            .removeClass("current")
            .eq(current)
            .addClass("current");
        var onscreen = sync2.find('.owl-item.active').length - 1;
        var start = sync2.find('.owl-item.active').first().index();
        var end = sync2.find('.owl-item.active').last().index();

        if (current > end) {
            sync2.data('owl.carousel').to(current, 100, true);
        }
        if (current < start) {
            sync2.data('owl.carousel').to(current - onscreen, 100, true);
        }
    }

    function syncPosition2(el) {
        if (syncedSecondary) {
            var number = el.item.index;
            sync1.data('owl.carousel').to(number, 100, true);
        }
    }

    sync2.on("click", ".owl-item", function(e) {
        e.preventDefault();
        var number = $(this).index();
        sync1.data('owl.carousel').to(number, 300, true);
    });
});


</script>