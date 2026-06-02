/**
 * JBZoo App is universal Joomla CCK, application for YooTheme Zoo component
 *
 * @package     jbzoo
 * @version     2.x Pro
 * @author      JBZoo App http://jbzoo.com
 * @copyright   Copyright (C) JBZoo.com,  All rights reserved.
 * @license     http://jbzoo.com/license-pro.php JBZoo Licence
 * @coder       Alexander Oganov <t_tapak@yahoo.com>
 */

(function ($, window, document, undefined) {
    function bindTipModalOnce() {
        var $wr = $("#tip-modal-wr");
        if ($wr.length > 1) {
            $wr.not(":first").remove();
            $wr = $wr.first();
        }
        if (!$wr.length) {
            $("body").append('<div id="tip-modal-wr"></div>');
        }

        var $modal = $("#tip-modal");
        if ($modal.length > 1) {
            $modal.not(":first").remove();
            $modal = $modal.first();
        }
        if (!$modal.length) {
            $("body").append('<div id="tip-modal"><div></div><div class="tip-modal-close"></div></div>');
        }

        // Remove any previously attached handlers to avoid duplicate modals.
        $(document).off("click.jbfilterTip", ".checkbox-tip");
        $(".checkbox-tip").off("click");
        $(document).off("click.jbfilterTipClose", "#tip-modal-wr, .tip-modal-close");

        $(document).on("click.jbfilterTip", ".checkbox-tip", function () {
            var $source = $("#" + $(this).parent().find("input").attr("class"));
            var html = $source.length ? $source.html() : "";
            if (!html) {
                return;
            }
            $("#tip-modal div:first").html(html);
            $("#tip-modal-wr, #tip-modal").show();
        });

        $(document).on("click.jbfilterTipClose", "#tip-modal-wr, .tip-modal-close", function () {
            $("#tip-modal-wr, #tip-modal").hide();
        });
    }
    //смазки для фильтра
    function filtrSmazki() {
        //если url содержит поиск по смазкам
        if (document.location.href.indexOf("type=smazka") > 0 || document.location.href.indexOf("type=maslo") > 0) {
            var tupe = "";
            if (document.location.href.indexOf("type=maslo") > 0) {
                tupe = "maslo";
            } else {
                tupe = "smazka";
            }
            //ajax функция
            var getAjax = function (strParams, tupe) {
                console.log("[filter->renderFiltr.php] request", { strParams: strParams, tupe: tupe });
                return $.ajax({
                    type: "POST",
                    /*dataType: 'json',*/
                    url: "/renderFiltr.php",
                    data: { strParams: strParams, tupe: tupe },
                }); /*.done(function( result )
            {
                console.log("Результат: "+result);
                return result;
            });*/
            };

            console.log("Фильтр поиска по смазкам");

            //фильтр для смазок
            let type = "smazka";
            let app_id = "2";

            // Массив для сохранения значений
            let elementsTipSmazkiArray = {};
            var $filterRoot = $(".jbfilter-wrapper").first();
            if (!$filterRoot.length) {
                $filterRoot = $(".d-none div.moduletable_filter div.jbfilter-wrapper").first();
            }

            //задаем категорию
            var $categoryLink = $(".jbzoo.jbcategory-module .category-active div.jbcategory-link").first();
            if (!$categoryLink.length) {
                $categoryLink = $(".d-none .jbzoo.jbcategory-module .category-active div.jbcategory-link").first();
            }
            var categoryId = $categoryLink.attr("id");
            if (!categoryId) {
                console.warn("[filter] category id not found for renderFiltr.php");
                return;
            }
            elementsTipSmazkiArray["_itemcategory"] = categoryId.replace("cat-", "");

            //создаем первый уровень, id элементов фильтра
            $filterRoot.find("div.jbfilter-checkbox label.jbfilter-label").each(function (index, el) {
                // Для каждого элемента сохраняем значение в personsIdsArray,
                let idElement = $(el).attr("for").replace("jbfilter-id-", "");
                //создаем массив
                elementsTipSmazkiArray[idElement] = [];
            });

            // Проход по всем элементам с классом выбранного параметра
            // с помощью jQuery each.
            $filterRoot.find("div.jbfilter-checkbox label.label-checked > input").each(function (index, el) {
                // Для каждого элемента сохраняем значение в personsIdsArray,
                // если значение есть.
                var v = $(el).val();

                let idElement = $(el).attr("name").replace("e[", "");
                idElement = idElement.replace("][]", "");

                if (v) elementsTipSmazkiArray[idElement].push(v);
            });
            console.log(elementsTipSmazkiArray);
            console.log(JSON.stringify(elementsTipSmazkiArray));

            //отправляем ajax запрос
            let resultFiltr = getAjax(JSON.stringify(elementsTipSmazkiArray), tupe);

            resultFiltr
                .done(function (data) {
                    console.log("[renderFiltr.php] response", data);
                    //Успешный ответ делаем подстановку в фильтр
                    /*$(parentDivFiltr + " div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox:nth-child(3) > div").html(data);
                     */
                    console.log("Результат AJAX вернулся");
                    //console.log(data);
                    //console.log($.parseJSON(data));

                    let arr = data;
                    if (typeof data === "string") {
                        try {
                            arr = $.parseJSON(data);
                        } catch (e) {
                            console.log("Failure!", { error: "Invalid JSON", responseText: data });
                            return;
                        }
                    }
                    if (arr && arr.error) {
                        console.log("Failure!", { error: arr.error });
                        return;
                    }

                    //проходим по всем элементам фильтра
                    $("div.jbfilter-checkbox label > input").each(function (index, el) {
                        // если значение есть.
                        var v = $(el).val();
                        var hideDiv = "yes";

                        // переберём массив arr
                        $.each(arr, function (index, value) {
                            //выведем индекс и значение массива в консоль
                            //console.log('Индекс: ' + index + '; Значение: ' + value);
                            if (v == index) {
                                $(el).parent("label").parent("div.checkbox-wrap").show(); // показать

                                $(el)
                                    .parent("label")
                                    .contents()
                                    .filter(function () {
                                        return this.nodeType == 3;
                                    })[1].nodeValue = value;

                                /*$(el).parent("label").contents().filter(function(){ 
                          return this.nodeType === 5; 
                        })[1].nodeValue = value;*/

                                //если параметр фильтра найден в архиве, то не скрываем
                                hideDiv = "no";
                            }
                        });

                        //если параметр фильтра не найден в архиве, то скрываем его
                        if (hideDiv == "yes") {
                            $(el).parent("label").parent("div.checkbox-wrap").hide(); // скрыть селектор
                            //$('.mySelector').show(); // показать
                        }
                    });

                    //let arr = $.parseJSON(data);

                    // переберём массив arr
                    //$.each(arr,function(index,value){
                    //выведем индекс и значение массива в консоль
                    //console.log('Индекс: ' + index + '; Значение: ' + value);
                    //заменяем результаты
                    //$("label[for='jbfilter-id-" + index + "']").parent("div.jbfilter-row.jbfilter-checkbox").children("div.jbfilter-element").html(value);

                    //});

                    //добавляем класс к нажатому элементу
                    $(".jbfilter-checkbox .jbfilter-element .checkbox-lbl input").on("change", function () {
                        if ($(this).is(":checked")) {
                            $(this).parent().addClass("label-checked");
                        } else {
                            $(this).parent().removeClass("label-checked");
                        }
                        //автоотправка формы
                        //$(".d-none div.moduletable_filter div.jbfilter-wrapper form").closest("form").submit();
                        //$("#jbmodule-default-119").JBZooFilter({"url":"\/catalog\/aviacionnaya-smazka?","updateBlock":".mainContent","autosubmit":1,"ajaxPagination":1,"pagination":".pagination-box"}, 0);

                        var autoSubmitFileds = [
                            "select",
                            "input",
                            "input[type=text]",
                            "input[type=radio]",
                            "input[type=checkbox]",
                            // '.jsSlider'
                        ].join(", ");
                    });

                    //запускаем подсказки
                    bindTipModalOnce();

                    //запускаем скрипт по кнопке купить
                    $(".forms-trigger").click(function (event) {
                        $('[data-popup="popup-form-3"]').trigger("click");
                        $('#baform-3 input[name="16"]').val($(this).closest(".row").find(".item-title").text());
                    });

                    //удаляем обертку
                    //$('div.element_slide').detach();
                    $("div.checkbox-wrap").unwrap("div.element_slide");
                    $("div.element_slide_open").remove();

                    //добавляем обертку
                    if (window.jbInitFilterSlide) {
                        window.jbInitFilterSlide();
                    }

                    //добавляем класс к нажатому элементу
                    /*$('.jbfilter-checkbox .jbfilter-element .checkbox-lbl input').on('change', function(){
                if ($(this).is(':checked')) {
                    $(this).parent().addClass('label-checked');
                } else {
                    $(this).parent().removeClass('label-checked');
                }
                //автоотправка формы
                $(".d-none div.moduletable_filter div.jbfilter-wrapper form").closest("form").submit();
            });*/

                    //запускаем подсказки
                    /*$('.checkbox-tip').on('click', function(){
                $('#tip-modal-wr, #tip-modal').show();
                $('#tip-modal div:first').html($('#' + $(this).parent().find('input').attr('class')).html());
            });*/
                })
                .fail(function (xhr, status, error) {
                    console.log("Failure!", {
                        status: status,
                        error: error,
                        responseText: xhr && xhr.responseText,
                    });
                });

            /*var li_obj = $(".jbfilter-checkbox");
    var li_array = $.makeArray(li_obj);

    console.log(li_array);*/
        }
    }

    //maslo для фильтра
    function filtrMaslo() {
        //если url содержит поиск по маслу
        if (document.location.href.indexOf("type=maslo") > 0) {
            console.log("Фильтр поиска по маслу");

            //ajax функция
            var getAjax = function (idElement, idCategory, arrChecked, arrCheckedFiltr2) {
                console.log("[filter->renderFiltr.php] request", {
                    idElement: idElement,
                    idCategory: idCategory,
                    arrChecked: arrChecked,
                    arrCheckedFiltr2: arrCheckedFiltr2
                });
                return $.ajax({
                    type: "POST",
                    url: "/renderFiltr.php",
                    data: { idElement: idElement, idCategory: idCategory, arrChecked: arrChecked, arrCheckedFiltr2: arrCheckedFiltr2 },
                }); /*.done(function( result )
            {
                console.log("Результат: "+result);
                return result;
            });*/
            };

            let parentDivFiltr;
            //если отображается фильтр десктопной версии
            if ($(".row div.d-none").is(":visible") == true) {
                //console.log($(".row div.d-none").is(":visible"));
                parentDivFiltr = ".row div.d-none";
            } else {
                //для мобильной
                parentDivFiltr = ".row div.d-md-none.d-lg-none";
            }

            //Если выбран тип масла
            if ($(".d-none div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox:nth-child(3) label.label-checked").length > 0) {
                // Массив для сохранения значений
                let elementMakerArray = [];

                // Проход по всем элементам с классом выбранного производителя
                // с помощью jQuery each.
                $(".d-none div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox:nth-child(3) label.label-checked > input").each(function (index, el) {
                    // Для каждого элемента сохраняем значение в personsIdsArray,
                    // если значение есть.
                    var v = $(el).val();
                    if (v) elementMakerArray.push(v);
                });

                //console.log('Выбранные производители:');
                //console.log(elementMakerArray);

                //получаем id элемента производителя
                let checkedMakerMasla = $(".d-none div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox:nth-child(3) label.label-checked input");
                let idElementMakerMasla = checkedMakerMasla.attr("name").replace("e[", "");
                idElementMakerMasla = idElementMakerMasla.replace("][]", "");

                //console.log("Выбран производитель: " + checkedMakerMasla.val() + " " + idElementMakerMasla);

                //получаем выбранную категорию
                let checkedCategory = $(".d-none .jbzoo.jbcategory-module .category-active div.jbcategory-link").attr("id").replace("cat-", "");

                //console.log("Категория: " + checkedCategory);

                // Массив для сохранения выбранных типов масел
                let arrElementsTipMasla = [];

                // Проход по всем элементам с классом выбранного масла
                // с помощью jQuery each.
                $(".d-none div.jbfilter-wrapper div.jbfilter-checkbox.last label.label-checked > input").each(function (index, el) {
                    // Для каждого элемента сохраняем значение в personsIdsArray,
                    // если значение есть.
                    var v = $(el).val();
                    if (v) arrElementsTipMasla.push(v);
                });
                //console.log(arrElementsTipMasla);

                //отправляем ajax запрос
                let resultFiltrs = getAjax(idElementMakerMasla, checkedCategory, JSON.stringify(elementMakerArray), JSON.stringify(arrElementsTipMasla));

                resultFiltrs
                .done(function (data) {
                    console.log("[renderFiltr.php] response", data);
                        //Успешный ответ делаем подстановку в фильтр
                        $(parentDivFiltr + " div.jbfilter-wrapper div.jbfilter-checkbox.last > div").html(data);

                        //console.log("Результат AJAX производители:");
                        //console.log(data);

                        //добавляем класс к нажатому элементу
                        $(".jbfilter-checkbox .jbfilter-element .checkbox-lbl input").on("change", function () {
                            if ($(this).is(":checked")) {
                                $(this).parent().addClass("label-checked");
                            } else {
                                $(this).parent().removeClass("label-checked");
                            }
                            //автоотправка формы
                            //$(".d-none div.moduletable_filter div.jbfilter-wrapper form").closest("form").submit();
                        });

                        //запускаем подсказки
                        bindTipModalOnce();
                    })
                    .fail(function (xhr, status, error) {
                        console.log("Failure!", {
                            status: status,
                            error: error,
                            responseText: xhr && xhr.responseText,
                        });
                    });
            }
            //console.log(getAjax("Ntcn"));

            //Если выбран производитель
            if ($(".d-none div.jbfilter-wrapper div.jbfilter-checkbox.last label.label-checked").length > 0) {
                // Массив для сохранения значений
                let elementsTipMaslaArray = [];

                // Проход по всем элементам с классом выбранного масла
                // с помощью jQuery each.
                $(".d-none div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox.last label.label-checked > input").each(function (index, el) {
                    // Для каждого элемента сохраняем значение в personsIdsArray,
                    // если значение есть.
                    var v = $(el).val();
                    if (v) elementsTipMaslaArray.push(v);
                });

                //console.log(elementsTipMaslaArray);

                //получаем id элемента масло
                let checkedTipMasla = $(".d-none div.jbfilter-wrapper div.jbfilter-checkbox.last label.label-checked input");
                let idElementTipMasla = checkedTipMasla.attr("name").replace("e[", "");
                idElementTipMasla = idElementTipMasla.replace("][]", "");

                //console.log("Выбрано масло: " + checkedTipMasla.val() + " " + idElementTipMasla);

                //получаем выбранную категорию
                let checkedCategory = $(".d-none .jbzoo.jbcategory-module .category-active div.jbcategory-link").attr("id").replace("cat-", "");

                //console.log("Категория: " + checkedCategory);

                // Массив для сохранения выбранных производителей
                let elementsProizvodArray = [];

                // Проход по всем элементам с классом выбранного производителя
                // с помощью jQuery each.
                $(".d-none div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox:nth-child(3) label.label-checked > input").each(function (index, el) {
                    // Для каждого элемента сохраняем значение в personsIdsArray,
                    // если значение есть.
                    var v = $(el).val();
                    if (v) elementsProizvodArray.push(v);
                });
                //console.log(elementsProizvodArray);

                //отправляем ajax запрос
                let resultFiltr = getAjax(idElementTipMasla, checkedCategory, JSON.stringify(elementsTipMaslaArray), JSON.stringify(elementsProizvodArray));

                resultFiltr
                    .done(function (data) {
                        //Успешный ответ делаем подстановку в фильтр
                        $(parentDivFiltr + " div.moduletable_filter div.jbfilter-wrapper div.jbfilter-checkbox:nth-child(3) > div").html(data);

                        //console.log("Результат AJAX тип масла:");
                        //console.log(data);

                        //добавляем класс к нажатому элементу
                        $(".jbfilter-checkbox .jbfilter-element .checkbox-lbl input").on("change", function () {
                            if ($(this).is(":checked")) {
                                $(this).parent().addClass("label-checked");
                            } else {
                                $(this).parent().removeClass("label-checked");
                            }
                            //автоотправка формы
                            //$(".d-none div.moduletable_filter div.jbfilter-wrapper form").closest("form").submit();
                        });

                        //запускаем подсказки
                        bindTipModalOnce();
                    })
                    .fail(function (xhr, status, error) {
                        console.log("Failure!", {
                            status: status,
                            error: error,
                            responseText: xhr && xhr.responseText,
                        });
                    });
            }
        }
    }

    var autoSubmitExclude = [".jsMoney", ".jsNoSubmit"].join(", "),
        // field list for auto submit
        autoSubmitFileds = [
            "select",
            "input",
            "input[type=text]",
            "input[type=radio]",
            "input[type=checkbox]",
            // '.jsSlider'
        ].join(", "),
        // filed list that mustn't reset
        resetExclude = [":reset", ":submit", ":button", 'input[type="hidden"]', ".jsMoney"].join(", ");

    // global hack for reset button in filter forms
    jQuery(function ($) {
        $(".jsFilter .jsReset").unbind();
        $(".jsFilter .jsReset").click(function () {
            $(".jbfilter-element label.label-checked input").trigger("click");
        });
    });

    /**
     * Filter form handler
     */
    JBZoo.widget(
        "JBZoo.Filter",
        {
            url: "",
            updateBlock: "#yoo-zoo",
            ajaxPagination: 0,
            pagination: ".uk-pagination-box",
            autosubmit: 0,
            submitTimeOut: 100,
        },
        {
            init: function ($this) {
                $this._initAutoSubmit();
            },

            /**
             * Listen change event
             * @returns {boolean}
             * @private
             */
            _initAutoSubmit: function () {
                var $this = this,
                    $fields = $this.$(autoSubmitFileds).not(autoSubmitExclude),
                    $form = $this.el.is("form") ? $this.el : $this.$("form");

                if (!$this.options.autosubmit) {
                    return false;
                }

                $fields.on("change", function () {
                    var formParams = $form.serialize();
                    $this._submitAjax(formParams);
                });

                $this.$(".jsSlider").on("change.JBZooSlider", function (event, ui) {
                    $this._delay(
                        function () {
                            var formParams = $form.serialize();
                            $this._submitAjax(formParams);
                        },
                        1000,
                        "submitForm"
                    );
                });

                if (!$this.options.ajaxPagination) {
                    return false;
                }

                $(document).on("click", ".jbzoo-view-filter " + $this.options.pagination + " a", function (event) {
                    event.preventDefault();

                    var paginationLink = $(this).attr("href");
                    $this._submitAjaxPagination(paginationLink);
                });
            },

            /**
             * Hack for submit (widgets, browsers, etc)
             * @returns {boolean}
             * @private
             */
            _submitForm: function () {
                var $this = this,
                    $form = $this.el.is("form") ? $this.el : $this.$("form");

                if (!$this.options.autosubmit) {
                    return false;
                }

                $this._delay(
                    function () {
                        $form.trigger("submit").submit();
                    },
                    $this.options.submitTimeOut,
                    "submitForm"
                );

                return true;
            },

            /**
             * Reset button
             * @param e
             * @param $this
             * @returns {boolean}
             */
            /*'click .jsReset': function (e, $this) {

                var $inputList = $this.el.find(':input, .jsSlider').not(resetExclude);

                $inputList.each(function (n, input) {

                    var $input = $(input);

                    if ($input.is('select')) {
                        // any selects
                        $input.JBZooSelect().JBZooSelect('reset');

                    } else if ($input.is('.jbcolor-input')) {
                        // JBColor Widget
                        var $colors = $input.closest('.jbzoo-colors');
                        $colors.JBZooColors('reset');


                    } else if ($input.is('[type=radio]')) {
                        // radio buttons
                        var $group = $input.closest('.jbfilter-row');
                        $('input[type=radio]:eq(0)', $group).attr('checked', 'checked');

                    } else if ($input.is('[type=checkbox]')) {
                        // checkbox buttons
                        $input.removeAttr('checked');

                    } else if ($input.is('.jsSlider') && $input.data('JBZooSlider')) {
                        // advanced slider
                        $input.JBZooSlider('reset');


                    } else if ($input.is('.jsSlider')) {
                        // simple slider
                        var slider = $input.find('.ui-slider').data('slider');
                        slider.values([
                            slider.options.min,
                            slider.options.max
                        ]);

                        $('.slider-value-0', $input).html(JBZoo.numberFormat(slider.options.min, 0, ".", " "));
                        $('.slider-value-1', $input).html(JBZoo.numberFormat(slider.options.max, 0, ".", " "));
                        $('[type=hidden][name*="range"]', $input).val(slider.options.min + '/' + slider.options.max);

                    } else {
                        // default like text input
                        $input.val('');
                    }

                });

                $this._submitForm();

                return false;
            },*/

            /**
             * Submit Ajax
             * @returns {boolean}
             * @private
             */
            _submitAjax: function (params) {
                var $this = this;

                $.ajax({
                    url: $this.options.url + params + "&tmpl=raw",
                    type: "get",
                    dataType: "html",
                    beforeSend: function () {
                        $("body").append('<div class="fl-filter-loading jsFilterLoading"></div>');
                    },
                    success: function (data) {
                        $(".jsFilterLoading").remove();
                        $("#yoo-zoo").addClass("jbzoo-view-filter");
                        $($this.options.updateBlock).html($(data));
                        window.history.pushState("", "", $this.options.url + params);

                        //смазки
                        filtrSmazki();
                        //масло
                        //filtrMaslo();

                        $("html, body").animate(
                            {
                                scrollTop: $($this.options.updateBlock).offset().top,
                            },
                            1000
                        );
                        /*$('body').append('<div class="fl-filter-loading jsFilterLoading"></div>');*/
                    },
                });
            },

            /**
             * Submit Ajax Pagination
             * @returns {boolean}
             * @private
             */
            _submitAjaxPagination: function (url) {
                var $this = this;

                $.ajax({
                    url: url + "&tmpl=raw",
                    type: "get",
                    dataType: "html",
                    beforeSend: function () {
                        $("body").append('<div class="fl-filter-loading jsFilterLoading"></div>');
                    },
                    success: function (data) {
                        $(".jsFilterLoading").remove();
                        $($this.options.updateBlock).html($(data));
                        window.history.pushState("", "", url.replace("&tmpl=raw", ""));

                        //смазки
                        filtrSmazki();
                        //масло
                        //filtrMaslo();

                        $("html, body").animate(
                            {
                                scrollTop: $($this.options.updateBlock).offset().top,
                            },
                            1000
                        );
                        /*$('body').append('<div class="fl-filter-loading jsFilterLoading"></div>');*/
                    },
                });
            },
        }
    );
})(jQuery, window, document);






