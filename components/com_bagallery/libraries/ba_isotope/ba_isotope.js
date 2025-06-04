/**
* @package   BaGrid
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

!function ($) {
    
    var ba_isotope = function (element, options) {
        this.parent = $(element);
        this.options = options;
        this.total = new Array();
        this.empty = new Array();
    };
    
    ba_isotope.prototype = {
        init : function(){
            var parent = this.parent,
                selector = this.options.selector,
                filter = this.options.filter,
                count = this.options.count * 1,
                mode = this.options.mode,
                offsetX = 0,
                parWidth = parent.width(),
                margin = this.options.margin * 1,
                winSize = $(window).width(),
                offsetY = 0,
                n;
            if (filter) {
                selector = filter;
            }
            n = count;
            parent.children().not(selector).hide();
            var childrens = parent.find(selector);
            if (mode == 'justified') {
                var flag = true,
                    start = 0,
                    rowWidth = 0,
                    ratio = 1,
                    lastHeight = 250;
                while (flag) {
                    var r = 0
                    for (var i = start; i < n; i++) {
                        if (childrens[i]) {
                            var height = $(childrens[i]).find('img').attr('data-height'),
                                width = $(childrens[i]).find('img').attr('data-width');
                            ratio = width / height;
                            width = ratio * 50;
                            rowWidth += width * 1;
                            r++
                        }
                    }
                    if (rowWidth != parWidth) {
                        ratio = (parWidth - r * margin + margin) / rowWidth;
                    }
                    for (var i = start; i < n; i++) {
                        if (childrens[i]) {
                            var width = $(childrens[i]).find('img').attr('data-width'),
                                height = $(childrens[i]).find('img').attr('data-height');
                            var r = width / height;
                            height = ratio * 50;
                            if (n > childrens.length && height > lastHeight) {
                                height = lastHeight;
                            }
                            width = height * r;
                            $(childrens[i]).css({
                                'width' : width,
                                'height' : height,
                                'left' : offsetX,
                                'position' : 'absolute',
                                'display' : 'block',
                                'top' : offsetY
                            });
                            lastHeight = height;
                            offsetX += width + margin;
                        } else {
                            flag = false;
                        }
                    }
                    if (childrens[start]) {
                        offsetY += $(childrens[start]).height() * 1 + margin * 1;
                        start = n;
                        n += count * 1;
                        offsetX = 0;
                        rowWidth = 0;
                    }
                }
            } else if (mode == 'random') {
                var array = {};
                childrens.each(function(){
                    var $t = $(this);
                    if (offsetX + $t.width() > parWidth) {
                        offsetX = 0;
                    }
                    if (!array[offsetX]) {
                        array[offsetX] = 0;
                    }
                    offsetY = array[offsetX];
                    $t.css({
                        'left' : offsetX,
                        'position' : 'absolute',
                        'display' : 'block',
                        'top' : offsetY
                    });
                    array[offsetX] += $t.height() + margin;
                    offsetX += $t.width() + margin;
                });
            } else if (winSize <= 480) {
                var array = {};
                childrens.each(function(){
                    if (offsetX + $(this).width() > parWidth) {
                        offsetX = 0;
                    }
                    for (key in array) {
                        if (array[key][offsetX]) {
                            offsetY = array[key][offsetX].y + array[key][offsetX].h;
                        }
                    }
                    $(this).css({
                        'left' : offsetX,
                        'position' : 'absolute',
                        'display' : 'block',
                        'top' : offsetY
                    });
                    if (!array[offsetY]) {
                        array[offsetY] = {}
                    }
                    array[offsetY][offsetX] = {
                        h : $(this).height() + margin,
                        y : offsetY
                    }
                    offsetX += $(this).width() + margin;
                });
            } else {
                var width = Math.floor((parWidth - (margin *  (n - 1))) / n),
                    imgC = childrens.length,
                    imgW2 = parent.find(selector+'.width2').not('.height2').length,
                    imgH2 = parent.find(selector+'.height2').not('.width2').length,
                    imgW2H2 = parent.find(selector+'.height2.width2').length;
                imgC += imgW2 * 2 + imgH2 * 2 * n + imgW2H2 * 4 * n;
                for (var i = 0; i < imgC; i++) {
                    if (offsetX + width > parWidth) {
                        offsetX = 0;
                        offsetY += width + margin
                    }
                    this.total.push(offsetX+':'+offsetY)
                    offsetX += width + margin;
                }
                var $this = this;
                childrens.each(function(){
                    var type = '';
                    if ($(this).hasClass('width2') && !$(this).hasClass('height2')) {
                        type = 'w2';
                    } else if ($(this).hasClass('width2') && $(this).hasClass('height2')) {
                        type = 'w2h2';
                    } else if (!$(this).hasClass('width2') && $(this).hasClass('height2')) {
                        type = 'h2';
                    }
                    var obj = $this.checkEmpty(type, width * 2 + margin, parWidth);
                    offsetY = obj[1];
                    $(this).css({
                        'top' : obj[1]+'px',
                        'left' : obj[0]+'px',
                        'position' : 'absolute',
                        'display' : 'block'
                    });
                });
            }
            setTimeout(function(){
                parent.trigger('show_isotope');
            }, 1);
            setTimeout(function(){
                $(window).trigger('scroll');
            }, 100);
            var max = offsetY;
            childrens.each(function(){
                var top = this.style.top.replace('px', '') * 1
                top += $(this).height() + margin;
                if (top > max) {
                    max = top;
                }
            });
            parent.css({
                'position' : 'relative',
                'height' : max
            });
        },
        checkEmpty : function(type, width, row){
            var el;
            for (var key in this.total) {
                key *= 1;
                el = this.total[key].split(':')
                break;
            }
            delete this.total[key];
            if (type == 'w2') {
                if (el[0] * 1 + width > row || !this.total[key + 1]) {
                    this.empty[key] = el[0]+':'+el[1];
                    el = this.checkEmpty(type, width, row);
                } else {
                    delete this.total[key + 1];
                    for (var i in this.empty) {
                        this.total[i] = this.empty[i];
                    }
                    this.empty = new Array();
                }
            } else if (type == 'h2') {
                if (!this.total[key + this.options.count * 1]) {
                    this.empty[key] = el[0]+':'+el[1];
                    el = this.checkEmpty(type, width, row);
                } else {
                    delete this.total[key + this.options.count * 1];
                    for (var i in this.empty) {
                        this.total[i] = this.empty[i];
                    }
                    this.empty = new Array();
                }
            } else if (type == 'w2h2') {
                if (el[0] * 1 + width > row || !this.total[key + 1] ||
                    !this.total[key + this.options.count * 1] || !this.total[key + 1 + this.options.count * 1]) {
                    this.empty[key] = el[0]+':'+el[1];
                    el = this.checkEmpty(type, width, row);
                } else {
                    delete this.total[key + 1];
                    delete this.total[key + this.options.count * 1];
                    delete this.total[key + 1 + this.options.count * 1];
                    for (var i in this.empty) {
                        this.total[i] = this.empty[i];
                    }
                    this.empty = new Array();
                }
            }   
            return el;
        },
        shuffle : function(){
            var childrens = this.parent.children().not('input'),
                n = childrens.length,
                parent = this.parent;
            var i, j, temp;
            for (i = n - 1; i > 0; i--) {
              j = Math.floor(Math.random() * (i + 1));
              temp = childrens[i];
              childrens[i] = childrens[j];
              childrens[j] = temp;
            }
            childrens.each( function () {
                $(this).appendTo(parent);
            });
        }
    }
    
    $.fn.ba_isotope = function (option) {
        return this.each(function () {
            var $this = $(this),
                data = $this.data('ba_isotope'),
                options = $.extend({}, $.fn.ba_isotope.defaults, typeof option == 'object' && option);
            if (typeof(option) == 'object') {
                $this.data('ba_isotope', (data = new ba_isotope(this, options)));
                data.init();
            } else {
                $this.data('ba_isotope', (data = new ba_isotope(this, $.fn.ba_isotope.defaults)));
                data[option]();
            }
        });
    }
    
    $.fn.ba_isotope.defaults = {
        selector : '> div',
        filter : '',
        margin : 10,
        count : 4,
        mode : 'grid'
    }
    
}(window.jQuery);