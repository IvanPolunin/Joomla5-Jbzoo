<?php

// no direct access
defined('_JEXEC') or die('Restricted access');




// Magic

class ElementSchema  extends Element
{
    
    public function hasValue($params = array())
    {
        // Magic
        return true;
    }
    
    
    public function edit()
    {
        // Magic
        return true;
    }
    
    
    public function render($params = array())
    {
        
        
        $Photo_mode = $this->config->get('photo_mode');
        $PhotoElList = $this->config->get('photoElList');
        $PhotoElementId = $this->config->get('PhotoElementId');
        $defaultphoto = $this->config->get('defaultphoto');
        $Price_mode = $this->config->get('price_mode');
        $Pricejbpriceplain = $this->config->get('jbpriceplain');
        $Pricejbpricecalc = $this->config->get('jbpricecalc');
        $PriceElementId = $this->config->get('PriceElementId');
        $JBprice_kop_enabled = $this->config->get('jbprice_kop_enabled');
        $JBZooPriceWithDiscount_enabled = $this->config->get('JBZooPriceWithDiscount_enabled');
        $Brand_mode = $this->config->get('brand_mode');
        $BrandListElementId = $this->config->get('brandListElementId');
        $BrandElementId = $this->config->get('brandElementId');
        $Brandsimpletext = $this->config->get('brandsimpletext');
        $Manufacturer_mode = $this->config->get('manufacturer_mode');
        $ManufacturerListElementId = $this->config->get('manufacturerListElementId');
        $ManufacturerElementId = $this->config->get('manufacturerElementId');
        $Manufacturersimpletext = $this->config->get('manufacturersimpletext');
        $Textteaser_mode = $this->config->get('textteaser_mode');
        $TeasertextListElementId = $this->config->get('teasertextListElementId');
        $TeasertextElementId = $this->config->get('teasertextElementId');
        $Teasertextsimpletext = $this->config->get('teasertextsimpletext');
        $ogtype_enabled = $this->config->get('ogtype_enabled');
        $ogtype_image_show = $this->config->get('ogtype_image_show');
        $ogtype_type_show = $this->config->get('ogtype_type_show');
        $ogtype_title_show = $this->config->get('ogtype_title_show');
        $ogtype_site_name_show = $this->config->get('ogtype_site_name_show');
        $ogtype_url_show = $this->config->get('ogtype_url_show');
        $ogtype_description_show = $this->config->get('ogtype_description_show');
        $ogtype_description_mode = $this->config->get('ogtype_description_mode');
        $ogtype_text_def = $this->config->get('ogtype_text_def');
        $razmetka_mode = $this->config->get('razmetka_mode');
        $razmetka_tech_mode = $this->config->get('razmetka_tech_mode');
        $mode_generator_tag_joomla = $this->config->get('mode_generator_tag_joomla');
        $JBZoo_el_debug = $this->config->get('jbzoo_el_debug');
        
        // dump($this->config,0,'$this->config');
        
        // ФОТО
        
        if ($Photo_mode == 0) {
            $JBZooElPhoto = $PhotoElList;
        }
        else {
            $JBZooElPhoto = $PhotoElementId;
        }
        
        // Цена
        
        if ($Price_mode == 0) {
            $JBZooElPrice = $Pricejbpriceplain;
        }
        if ($Price_mode == 1) {
            $JBZooElPrice = $Pricejbpricecalc;
        }
        if ($Price_mode == 2) {
            $JBZooElPrice = $PriceElementId;
        }
        
        $CategoryPrimaryId = $this->_item->getParams()->get('config.primary_category');
        $CategoryPrimaryObj = $this->app->table->category->get($CategoryPrimaryId);
        $CategoryPrimaryName = $CategoryPrimaryObj ? $CategoryPrimaryObj->name : '';
        
        // Цена
        
        if ($Brand_mode == 0) {
            $JBZooElBrand = NULL;
        }
        if ($Brand_mode == 1) {
            $JBZooElBrand = $BrandListElementId;
        }
        if ($Brand_mode == 2) {
            $JBZooElBrand = $CategoryPrimaryName;
        }
        if ($Brand_mode == 3) {
            $JBZooElBrand = $BrandElementId;
        }
        if ($Brand_mode == 4) {
            $JBZooElBrand = $Brandsimpletext;
        }
        
        if ($Manufacturer_mode == 0) {
            $JBZooElManufacturer = NULL;
        }
        if ($Manufacturer_mode == 1) {
            $JBZooElManufacturer = $ManufacturerListElementId;
        }
        if ($Manufacturer_mode == 2) {
            $JBZooElManufacturer = $CategoryPrimaryName;
        }
        if ($Manufacturer_mode == 3) {
            $JBZooElManufacturer = $ManufacturerElementId;
        }
        if ($Manufacturer_mode == 4) {
            $JBZooElManufacturer = $Manufacturersimpletext;
        }
        
        // Описание товара
        
        if ($Textteaser_mode == 0) {
            $JBZooElTextteaser = NULL;
        }
        if ($Textteaser_mode == 1) {
            $JBZooElTextteaser = $TeasertextListElementId;
        }
        if ($Textteaser_mode == 2) {
            $JBZooElTextteaser = $TeasertextElementId;
        }
        if ($Textteaser_mode == 3) {
            $JBZooElTextteaser = $Teasertextsimpletext;
        }
        
        // OG разметка товара
        
        $docshema = JFactory::getDocument();
        
        //$JBZooPhoto = $this->_item->getElement($JBZooElPhoto)->data();
//ivp получаем фото

        $JBZooPhoto = '';
        $photoElement = $this->_item->getElement($JBZooElPhoto);

        if ($photoElement) {
            $params = array(
                "display"  => "first",
                "template" => "popup",
                "width"    => "375",
                "height"   => "500",
                "width_popup" => "0",
                "height_popup" => "0",
            );

            $photoHtml = $photoElement->render($params);

            if (preg_match('/<img[^>]+src=["\']([^"\']+)["\']/i', $photoHtml, $matches)) {
                $JBZooPhoto = $matches[1];
            } else {
                $photoFile = (string)$photoElement->get('file');

                if (!empty($photoFile)) {
                    $JBZooPhoto = JUri::root() . ltrim($photoFile, '/');
                } else {
                    preg_match_all('#\b(https?://[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|/)))#ism', $photoHtml, $urls);
                    $urls = array_unique($urls[1]);
                    $JBZooPhoto = !empty($urls) ? reset($urls) : '';
                }
            }
        }

//jbdump($urls[0]);
        
        
        //$JBZooPhoto = explode('" ', $JBZooPhoto);
        
        //jbdump($JBZooPhoto);
//jbdump(substr($JBZooPhoto, 0, strpos($JBZooPhoto, '" width=' )));
        
        //$JBZooPhoto = $this->app->data->create($JBZooPhoto);
        //$JBZooPhoto = $JBZooPhoto->find('0.file', $defaultphoto);
        //$JBZooPhoto = JURI::base().$JBZooPhoto;
        
        //$JBZooPrice = $this->_item->getElement($JBZooElPrice)->data()->variations;
        
//ivp получение цены     
        $getItemType = $this->_item->type;
        
        $idItemsElemRelateds = (array)$this->_item->getElement($this->config->get('skladElList'))->data();
        
        if($idItemsElemRelateds){
            $arrPricesSklad = array();
            
            //перебераем в цикле массив id масел с ценами
            foreach ((array) ($idItemsElemRelateds['item'] ?? []) as $idItem) {
                $itemSklad = $this->app->table->item->get($idItem); //Берем содержимое материала
                $element = $itemSklad->getElement($this->config->get('priceSkladElementId'))->data()->variations;// Подставляем id элемента (его смотрим в админке в списке элементов типа)
                 
                $value = null;
                if (is_array($element) && isset($element[0]['_value']['value'])) {
                    $value = $element[0]['_value']['value'];
                }
                if ($value !== null && $value !== '') {
                    $arrPricesSklad[] = $value;
                }
                
            }
            
            $JBZooPrice = $arrPricesSklad ? min($arrPricesSklad) : '0';
            
            //jbdump($minPricesSklad);
       
        } else {
            
            $JBZooPrice = '0';
            
        }
        
        $relatedElement = $this->_item->getElement($this->config->get('skladElList'));
        $relatedIds = array();

        if ($relatedElement) {
            $relatedIds = (array)$relatedElement->get('item', array());

            if (empty($relatedIds)) {
                $relatedData = (array)$relatedElement->data();
                if (!empty($relatedData['item'])) {
                    $relatedIds = (array)$relatedData['item'];
                }
            }
        }

        if ($relatedIds) {
            $arrPricesSklad = array();

            foreach ($relatedIds as $idItem) {
                $itemSklad = $this->app->table->item->get($idItem);
                if (!$itemSklad) {
                    continue;
                }

                $priceElement = $itemSklad->getElement($this->config->get('priceSkladElementId'));
                if (!$priceElement) {
                    continue;
                }

                foreach ((array)$priceElement->get('variations', array()) as $variation) {
                    $value = null;

                    if (is_array($variation) && isset($variation['_value']['value'])) {
                        $value = $variation['_value']['value'];
                    }

                    if ($value === null || $value === '') {
                        continue;
                    }

                    $value = str_replace(' ', '', (string)$value);
                    $value = str_replace(',', '.', $value);

                    if (!is_numeric($value)) {
                        continue;
                    }

                    $value = (float)$value;
                    if ($value > 0) {
                        $arrPricesSklad[] = $value;
                    }
                }
            }

            if ($arrPricesSklad) {
                $JBZooPrice = min($arrPricesSklad);
            }
        }

        if($JBZooPrice == ''){
            $JBZooPrice = '0';
        }

        $Valuta = "RUB";
        $JBZooSkuItem = '';
        $CB_Balance = 'InStock';
        $schemastock = 'InStock';
        
        
        //$JBZooElPrice = $this->app->data->create($JBZooPrice);
        //$JBZooPrice = $JBZooElPrice->find('0._value.value', 'Уточняйте по телефону');




        /*if (!empty($JBZooPrice)) {
            $CB_Balance = $JBZooElPrice->find('0._balance.value');
        }*/
   /*
        if ($CB_Balance == "-1") { $schemastock = "InStock"; }
        if ($CB_Balance == "-2") { $schemastock = "OutOfStock"; }
        if ($CB_Balance > 0 || NULL == $CB_Balance) { $schemastock = "PreOrder"; }
        
        if ($CB_Balance == "-1") { $CB_Balance = "Есть в наличии"; }
        if ($CB_Balance == "-2") { $CB_Balance = "Под заказ"; }
        if ($CB_Balance > 0 || NULL == $CB_Balance) { $CB_Balance = $CB_Balance; }
        if (empty($CB_Balance)) { $CB_Balance = "Под заказ"; }

        $money = JBCart::val($JBZooPrice);
        $Valuta = $money->cur();
        
        

        if ($Valuta == "rub") { $Valuta = "RUB"; }
        if ($Valuta == "usd") { $Valuta = "USD"; }
        if ($Valuta == "eur") { $Valuta = "EUR"; }


        if ($JBprice_kop_enabled == 1) {
            $JBZooPrice = round($JBZooPrice,0);
        }


        if ($JBZooPriceWithDiscount_enabled == 1) {

            $JBZooPriceWithDiscount = $JBZooElPrice->find('0._discount.value');
            $pregpercent = preg_match('/%/',$JBZooPriceWithDiscount,$pregpercent);

            if ($pregpercent == 1) {
                $JBZooPriceWithDiscount = str_replace('%','',$JBZooPriceWithDiscount);
                $JBZooPrice =  $JBZooPrice - ($JBZooPrice * ($JBZooPriceWithDiscount / 100));
            }

            else {
                $JBZooPrice = $JBZooPrice - $JBZooPriceWithDiscount;
            }
        
        }

        $JBZooSkuItem = $JBZooElPrice->find('0._sku.value', 'Артикул не найден');
*/        
        $CategoryPrimaryId = $this->_item->getParams()->get('config.primary_category');
        $CategoryPrimaryObj = $this->app->table->category->get($CategoryPrimaryId);
        $CategoryPrimaryName = $CategoryPrimaryObj ? $CategoryPrimaryObj->name : '';
        
        $ItemName = $this->_item->name;
        $ItemName = trim(preg_replace('/\s+/u', ' ', strip_tags($ItemName)));
        $ItemName = str_replace('"','',$ItemName);
        $ItemName = str_replace("'","",$ItemName);
        $docshema->setTitle($ItemName . ' | supplym.ru');

        $normalizeMetaText = function ($text) {
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$text)));
            $text = preg_replace('/^Применение[\s:\-–—]*/ui', '', $text);

            return trim($text);
        };

        $normalizeMetaText = function ($text) {
            $text = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$text)));
            $text = preg_replace(
                '/^(?:\x{041F}\x{0440}\x{0438}\x{043C}\x{0435}\x{043D}\x{0435}\x{043D}\x{0438}\x{0435})[\s:\x{2013}\x{2014}-]*/u',
                '',
                $text
            );

            return trim($text);
        };

        $metaDescriptionSource = '';
        $schemaDescription = '';
        foreach ($this->_item->getElements() as $element) {
            if (trim((string)$element->config->get('name')) === 'Применение') {
                $values = (array)$element->getValue(array('display' => 'first'));
                $metaDescriptionSource = $normalizeMetaText(implode(' ', $values));
                break;
            }
        }

        if ($metaDescriptionSource === '') {
            $metaDescriptionSource = (string)$this->_item->getParams()->get('metadata.description');
            $metaDescriptionSource = $normalizeMetaText($metaDescriptionSource);
        }

        if ($metaDescriptionSource !== '') {
            $metaDescriptionPrefix = $ItemName . ' - ';
            $metaDescriptionLimit = 300;
            $hasItemNameInDescription = function_exists('mb_stripos')
                ? mb_stripos($metaDescriptionSource, $ItemName, 0, 'UTF-8') !== false
                : stripos($metaDescriptionSource, $ItemName) !== false;
            $metaDescriptionPrefixLength = function_exists('mb_strlen')
                ? mb_strlen($metaDescriptionPrefix, 'UTF-8')
                : strlen($metaDescriptionPrefix);
            $metaDescriptionTailLimit = $hasItemNameInDescription
                ? $metaDescriptionLimit
                : ($metaDescriptionLimit - $metaDescriptionPrefixLength);
            $truncateText = function ($text, $limit) {
                if ($limit <= 0) {
                    return '';
                }

                $length = function_exists('mb_strlen')
                    ? mb_strlen($text, 'UTF-8')
                    : strlen($text);

                if ($length <= $limit) {
                    return $text;
                }

                $ellipsis = '...';
                $cutLimit = max(0, $limit - strlen($ellipsis));
                $truncated = function_exists('mb_substr')
                    ? mb_substr($text, 0, $cutLimit, 'UTF-8')
                    : substr($text, 0, $cutLimit);

                $truncated = preg_replace('/\s+?(\S+)?$/u', '', $truncated);

                return rtrim($truncated) . $ellipsis;
            };

            if ($metaDescriptionTailLimit > 0) {
                $metaDescriptionText = $truncateText($metaDescriptionSource, $metaDescriptionTailLimit);
                if ($hasItemNameInDescription) {
                    $schemaDescription = $metaDescriptionText;
                    $docshema->setDescription($schemaDescription);
                } else {
                    $schemaDescription = $metaDescriptionPrefix . $metaDescriptionText;
                    $docshema->setDescription($schemaDescription);
                }
            } else {
                $schemaDescription = $truncateText($ItemName, $metaDescriptionLimit);
                $docshema->setDescription($schemaDescription);
            }
        }
        
        $JBZooTeaserText = NULL;
        if ($Textteaser_mode == 0 || $Textteaser_mode == 1 || $Textteaser_mode == 2) {
            $JBZooTeaserText = $this->_item->getElement($JBZooElTextteaser)->data();
            $JBZooTeaserText = $this->app->data->create($JBZooTeaserText);
            $JBZooTeaserText = $JBZooTeaserText->find('0.value', $CategoryPrimaryName.' '.$ItemName);
            $JBZooTeaserText = trim(preg_replace('/\s+/u', ' ', strip_tags($JBZooTeaserText)));
        }
        else {
             $JBZooTeaserText = $Teasertextsimpletext;
        }

        if ($schemaDescription === '') {
            $schemaDescription = $JBZooTeaserText;
        }
        
        
        if($ogtype_enabled == 1):
        
        if(NULL !== $this->_item->getElement($ogtype_text_def)){
            $OGJBZooTeaserText = $this->_item->getElement($ogtype_text_def)->data();
            $OGJBZooTeaserText = $this->app->data->create($OGJBZooTeaserText);
            $OGJBZooTeaserText = $OGJBZooTeaserText->find('0.value', NULL);
            $OGJBZooTeaserText = trim(strip_tags($OGJBZooTeaserText));
        }
        else {
            $OGJBZooTeaserText = NULL;
        }
        
        endif;
        
        if ($Brand_mode != 2 && $Brand_mode != 4 && NULL !== $JBZooElBrand) {
            
            $JBZooBrand = $this->_item->getElement($JBZooElBrand)->data();
            $JBZooBrand = $this->app->data->create($JBZooBrand);
            $JBZooBrand = $JBZooBrand->find('0.value', $CategoryPrimaryName.' '.$ItemName);
            $JBZooBrand = trim(strip_tags($JBZooBrand));
            if (!empty($JBZooBrand)) {
                 $JBZooBrand = $this->_item->getElement($JBZooElBrand)->render();
            }
            
        }
        else {
            $JBZooBrand = $JBZooElBrand;
        }
        
        if ($Manufacturer_mode != 2 && $Manufacturer_mode != 4 && NULL !== $JBZooElManufacturer) {
            
            $JBZooManufacturer = $this->_item->getElement($JBZooElManufacturer)->data();
            $JBZooManufacturer = $this->app->data->create($JBZooManufacturer);
            $JBZooManufacturer = $JBZooManufacturer->find('0.value', $CategoryPrimaryName.' '.$ItemName);
            $JBZooManufacturer = trim(strip_tags($JBZooManufacturer));
             if (!empty($JBZooManufacturer)) {
                 $JBZooManufacturer = $this->_item->getElement($JBZooElManufacturer)->render();
            }
        }
        else {
            $JBZooManufacturer = $JBZooElManufacturer;
        }

        $JBZooBrand = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$JBZooBrand)));
        $JBZooManufacturer = trim(preg_replace('/\s+/u', ' ', strip_tags((string)$JBZooManufacturer)));

        if (empty($JBZooSkuItem) || stripos($JBZooSkuItem, 'Артикул') !== false) {
            $JBZooSkuItem = !empty($this->_item->alias) ? $this->_item->alias : (string)$this->_item->id;
        }

        $JBZooPhoto = trim((string)$JBZooPhoto);
        $itemUrl = $this->app->jbrouter->externalItem($this->_item);
        $priceValidUntil = date('Y-m-d');
        $schemaAvailabilityUrl = 'https://schema.org/' . $schemastock;
        $schemaPrice = preg_replace('/[^\d,\.]/', '', (string)$JBZooPrice);
        $schemaPrice = str_replace(',', '.', $schemaPrice);
        $schemaPriceValue = null;

        if ($schemaPrice !== '' && is_numeric($schemaPrice) && (float)$schemaPrice > 0) {
            $schemaPriceValue = number_format((float)$schemaPrice, 2, '.', '');
            $schemaPriceValue = rtrim(rtrim($schemaPriceValue, '0'), '.');
        }
        
        
        if($ogtype_enabled == 1):
        
        if($ogtype_title_show == 1) {     $docshema->setMetaData('og:title', $docshema->title);  }
        if($ogtype_type_show == 1) {      $docshema->setMetaData('og:type', 'website' );  }
        if($ogtype_url_show == 1) {       $docshema->setMetaData('og:url', $this->app->jbrouter->externalItem($this->_item));  }
        if($ogtype_image_show == 1) {     $docshema->setMetaData('og:image', $JBZooPhoto ); }
        if($ogtype_site_name_show == 1) { $docshema->setMetaData('og:site_name', JFactory::getApplication()->getCfg('sitename') ); }
        $JBZooOgTags = $docshema->_metaTags['name'];
        
        // dump($docshema,0,'docshema');
        
        $JBZooOgTags = implode("\n",$JBZooOgTags);
        
        if($ogtype_description_show == 1) {
            
            if($ogtype_description_mode == 1) {
                $docshema->setMetaData('og:description', $OGJBZooTeaserText );
            }
            
            if($ogtype_description_mode == 0) {
                $docshema->setMetaData('og:description', $docshema->description );
            }
        }
        
        if($mode_generator_tag_joomla == 1) {
            $docshema->setGenerator('');
        }
        
        endif;
        
        if($razmetka_mode == 1):
        
        $schemaProductData = array(
            '@context'    => 'https://schema.org',
            '@type'       => 'Product',
            'name'        => $ItemName,
            'image'       => $JBZooPhoto,
            'description' => $schemaDescription,
            'sku'         => $JBZooSkuItem,
        );

        if (!empty($JBZooManufacturer)) {
            $schemaProductData['manufacturer'] = array(
                '@type' => 'Organization',
                'name'  => $JBZooManufacturer,
            );
        }

        if (!empty($JBZooBrand)) {
            $schemaProductData['brand'] = array(
                '@type' => 'Brand',
                'name'  => $JBZooBrand,
            );
        }

        $schemaProductData['offers'] = array(
            '@type'         => 'Offer',
            'url'           => $itemUrl,
            'priceCurrency' => $Valuta,
            'availability'  => $schemaAvailabilityUrl,
            'seller'        => array(
                '@type' => 'Organization',
                'name'  => 'Ресурс-М',
            ),
        );

        if ($schemaPriceValue !== null) {
            $schemaProductData['offers']['price'] = $schemaPriceValue;
            $schemaProductData['offers']['priceValidUntil'] = $priceValidUntil;
        }

        $schemaproduct =
            '<script type="application/ld+json">' .
            json_encode($schemaProductData, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) .
            '</script>';

        
        $shemamicrodata = "
        
        <div style='display:none'>
        
        <!--Указывается схема Product.-->
        <div itemscope itemtype='http://schema.org/Product'>
        
        <!--В поле name указывается наименование товара.-->
        <span itemprop='name'>{$ItemName}</span>
        
        ";


        if (NULL !== $JBZooElManufacturer) :
        $shemamicrodata .= "<span itemprop='manufacturer'>{$JBZooManufacturer}</span>";
        endif;
            
        if (NULL !== $JBZooElBrand) :
        $shemamicrodata .= "<span itemprop='brand'>{$JBZooBrand}</span>";
        endif;

        $shemamicrodata .= "
        <!--В поле description дается описание товара.-->
        <span itemprop='description'>{$schemaDescription}</span>
        
        <!--В поле image указывается ссылка на картинку товара.-->
        <img src='{$JBZooPhoto}' itemprop='image'>
        
        <!--Указывается схема Offer.-->
        <div itemprop='offers' itemscope itemtype='http://schema.org/Offer'>
        
        <!--В поле price указывается цена товара.-->
        <span itemprop='price'>{$JBZooPrice}</span>
        
        <!--В поле priceCurrency указывается валюта.-->
        <span itemprop='priceCurrency'>{$Valuta}</span>
        
        <div>{$CB_Balance}</div>
        
        <link itemprop='availability' href='http://schema.org/{$schemastock}'>
        
        </div>
        
        </div>
        
        </div>
        
        ";
        
        if($razmetka_tech_mode == 0) {
            
            $docshema->addCustomTag($schemaproduct);
            
        }
        if($razmetka_tech_mode == 1) {
            
            echo $shemamicrodata;
            
        }
        if($razmetka_tech_mode == 2) {
            
            $docshema->addCustomTag($schemaproduct);
            
            echo $shemamicrodata;
        }
        
        
        endif;
        
        if ($JBZoo_el_debug == 1) {
            
            echo '<h2>Debug ON JBZooOgTags</h2>';
            echo '<pre>';
            echo '<textarea style="width: 90%; height: 550px;" rows="100" cols="150">';
            echo  @$JBZooOgTags;
            echo '</textarea>';
            echo '</pre>';
            echo '<h2>Debug ON schemaproduct</h2>';
            echo '<pre>';
            echo '<textarea style="width: 90%; height: 550px;" rows="100" cols="150">';
            echo  @$schemaproduct;
            echo '</textarea>';
            echo '</pre>';
            echo '<h2>Debug ON shemamicrodata</h2>';
            echo '<pre>';
            echo '<textarea style="width: 90%; height: 550px;" rows="100" cols="150">';
            echo  @$shemamicrodata;
            echo '</textarea>';
            echo '</pre>';
            
        }
        
        
    }
    
}
