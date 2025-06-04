/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

var $g = window.jQuery ? window.jQuery : null,
    categoryId = 1,
    imageId = 1,
    catImages = [],
    allImages = [],
    watermark  = false,
    notification = currentCat = currentItem = currentContext = uploadMode = pagLimit = null,
    app = {
        types: ['jpg', 'png', 'jpeg', 'webp'],
        _: function(key){
            if (galleryLanguage && galleryLanguage[key]) {
                return galleryLanguage[key];
            } else {
                return key;
            }
        },
        fetch: async function(url, data){
            let request = await fetch(url, {
                    method: 'POST',
                    body: app.getFormData(data)
                }),
                text = await request.text();

            return text;
        },
        getFormData: function(data){
            let formData = new FormData();
            if (data) {
                for (let ind in data) {
                    if (Array.isArray(data[ind])) {
                        data[ind].forEach(function(v){
                            formData.append(ind+'[]', v);
                        })
                    } else {
                        formData.append(ind, data[ind]);
                    }
                }
            }

            return formData;
        }
    };

function getCategoryObject(li)
{
    return obj = window.categories[li.dataset.ind];
}

function setCategoryValue(li, value, key)
{
    let obj = getCategoryObject(li)
    obj[key] = value;
}

function getCategoryHTML(category, ind)
{
    let className = 'ba-category' + (category.published == 0 ? ' ba-unpublish': ''),
        category_id = category.category_all == 1 ? 'category-all' : 'category-'+category.category_id;
    if (category.category_all != 1) {
        catImages['category-'+category.category_id] = [];
    }
    if (category.category_id >= categoryId) {
        categoryId = category.category_id;
        categoryId++;
    }

    return `
    <li class="${className}" id="${category_id}" data-ind="${ind}">
        <a>
            <label>
                <i class="zmdi zmdi-folder"></i>
            </label>
            <span>${category.title}</span>
            ${category.default == 1 ? '<i class="zmdi zmdi-star"></i>' : ''}
        </a>
    </li>`;
}

function showNotice(message)
{
    if (notification.classList.contains('notification-in')) {
        setTimeout(function(){
            notification.className = 'animation-out';
            setTimeout(function(){
                addNoticeText(message);
            }, 400);
        }, 2000);
    } else {
        addNoticeText(message);
    }
}

function addNoticeText(message)
{
    notification.children[0].innerText = message;
    notification.className = 'notification-in';
    animationOut = setTimeout(function(){
        notification.className = 'animation-out';
    }, 3000);
}

function saveImg(obj)
{
    obj = JSON.stringify(obj);
    var item = currentItem.find('.select-item');
    item.val(obj);
    item = item.attr('data-index');
    catImages[currentCat][item] = JSON.parse(obj);
}

function saveColors()
{
    var colors = [],
        obj = currentItem.find('.select-item').val();
    obj = JSON.parse(obj);
    $g('select.image_colors option').each(function(){
        var object = {
            id : this.value,
            title : this.textContent.trim()
        }
        colors.push(object);
    });
    obj.colors = colors;
    obj.resave = 1;
    saveImg(obj);
}

function addColorFilter($this)
{
    var input = $g($this),
        title = input.val().trim().toLowerCase();
    if (!title) {
        $this.value = '';
        return false;
    }
    var str = '<li class="colors-chosen" data-value="'+$this.dataset.rgba+'"><span class="chosen-color" style="background-color:',
        tagId = 'new$'+$this.dataset.rgba;
    $g('.all-colors li').each(function(){
        var search = this.dataset.value;
        if ($this.dataset.rgba == search) {
            this.classList.add('selected-colors');
            tagId = this.dataset.id;
            return false;
        }
    });
    if ($g('.picked-colors .colors-chosen i[data-remove="'+tagId+'"]').length > 0) {
        return false;
    }
    str += $this.dataset.rgba+';"></span><span>'+title+'</span><i class="zmdi zmdi-close" data-remove="';
    str += tagId+'"></i></li>';
    $g('.picked-colors .search-colors').before(str);
    str = '<option value="'+tagId+'" selected>'+$this.dataset.rgba+'</option>';
    $g('select.image_colors').append(str);
    $this.value = '';
    $g('.all-colors li').hide();
    saveColors();
}

function checkModule(module)
{
    if (!(module in app)) {
        loadModule(module);
    } else {
        app[module]();
    }
}

function loadModule(module)
{
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'components/com_bagallery/assets/js/'+module+'.js';
    document.head.append(script);
}

function rangeAction(range, callback)
{
    var $this = $g(range),
        max = $this.attr('max') * 1,
        min = $this.attr('min') * 1,
        number = $this.next();
    number.on('input', function(){
        var value = this.value * 1;
        if (max && value > max) {
            this.value = value = max;
        }
        if (min && value < min) {
            value = min;
        }
        $this.val(value);
        setLinearWidth($this);
        callback(number);
    });
    $this.on('input', function(){
        var value = this.value * 1;
        number.val(value).trigger('input');
    });
}

function inputCallback(input)
{
    var callback = input.attr('data-callback');
    if (callback in app) {
        app[callback]();
    }
}

function setLinearWidth(range)
{
    var max = range.attr('max') * 1,
        value = range.val() * 1,
        sx = ((Math.abs(value) * 100) / max) * range.width() / 100,
        linear = range.prev();
    if (value < 0) {
        linear.addClass('ba-mirror-liner');
    } else {
        linear.removeClass('ba-mirror-liner');
    }
    if (linear.hasClass('letter-spacing')) {
        sx = sx / 2;
    }
    linear.width(sx);
}

function setTabsUnderline()
{
    $g('.general-tabs > ul li.active a').each(function(){
        var coord = this.getBoundingClientRect();
        $g(this).closest('.general-tabs').find('div.tabs-underline').css({
            'left' : coord.left,
            'right' : document.documentElement.clientWidth - coord.right,
        }); 
    });
}

function setMinicolorsColor(value)
{
    var rgba = value ? value : 'rgba(255,255,255,0)',
        color = rgba2hex(rgba),
        obj = {
            color : color[0],
            opacity : color[1],
            update: false
        }
    $g('.variables-color-picker').minicolors('value', obj).closest('#color-picker-cell')
        .find('.minicolors-opacity').val(color[1]);
    $g('#color-variables-dialog .active').removeClass('active');
    $g('#color-picker-cell, #color-variables-dialog .nav-tabs li:first-child').addClass('active');
}

function inputColor()
{
    var value = this.value.trim().toLowerCase(),
        parts = value.match(/[^#]\w/g),
        opacity = 1;
    if (parts && parts.length == 3) {
        var rgba = 'rgba(';
        for (var i = 0; i < 3; i++) {
            rgba += parseInt(parts[i], 16);
            rgba += ', ';
        }
        if (!this.dataset.rgba) {
            rgba += '1)';
        } else {
            parts = this.dataset.rgba.toLowerCase().match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/);
            if (!parts) {
                rgba += '1)';
            } else {
                opacity = parts[4];
                rgba += parts[4]+')';
            }
        }
        this.dataset.rgba = rgba;
        $g(this).next().find('.minicolors-swatch-color').css('background-color', rgba);
        $g(this).trigger('minicolorsInput');
        setMinicolorsColor(rgba);
    }
    $g(this).closest('.ba-settings-item').find('.minicolors-opacity').val(opacity).removeAttr('readonly');
}

function updateInput(input, rgba)
{
    var color = rgba2hex(rgba);
    input.attr('data-rgba', rgba).val(color[0]).next().find('.minicolors-swatch-color').css('background-color', rgba);
    input.closest('.minicolors').next().find('.minicolors-opacity').val(color[1]);
}

function rgba2hex(rgb)
{
    var parts = rgb.toLowerCase().match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/),
        hex = '#',
        part,
        color = [];
    if (parts) {
        for (var i = 1; i <= 3; i++) {
            part = parseInt(parts[i]).toString(16);
            if (part.length < 2) {
                part = '0'+part;
            }
            hex += part;
        }
        if (!parts[4]) {
            parts[4] = 1;
        }
        color.push(hex);
        color.push(parts[4] * 1);
        
        return color;
    } else {
        color.push(rgb.trim());
        color.push(1);
        
        return color;
    }
}

function listenMessage(data)
{
    $g('#uploader-modal').modal('hide');
    if (uploadMode == 'images') {
        data.forEach(function(obj){
            let tbody = $g('table.ba-items-table tbody');
            obj.category = currentCat;
            obj.imageId = imageId++;
            obj.resave = 1;
            obj.time = +new Date();
            obj.target = 'blank';
            catImages[currentCat].push(obj);
            if (tbody.find('tr').length < pagLimit) {
                var ind = catImages[currentCat].length - 1,
                    str = returnTrHtml(obj, ind);
                tbody.append(str);
                tbody.find('.select-item').last().val(JSON.stringify(obj));
            }
        });
        drawPaginator();
        getAllImages();
        showNotice(app._('SUCCESS_UPLOAD'));
    } else if (uploadMode == 'alternativeImage') {
        let img = data[0].url,
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        obj.alternative = img;
        obj.resave = 1;
        $g('.alternative-image').val(img);
        saveImg(obj);
    } else if (uploadMode == 'reselectImage') {
        let obj = JSON.parse(currentItem.find('.select-item').val());
        if (obj.type) {
            delete obj.type;
            delete obj.video_id;
            delete obj.video_type;
        }
        obj.name = data[0].name;
        obj.path = data[0].path;
        obj.size = data[0].size;
        obj.resave = 1;
        obj.thumbnail_url = '';
        obj.url = data[0].url;
        currentItem.find('img')[0].src = JUri+obj.url;
        currentItem.find('.select-td').next().text(obj.name).next().text(getFileSize(obj.size));
        obj.url = obj.url.replace(/\s/g, '%20');
        $g('div.images-options div.img-thumbnail').css('background-image', 'url('+JUri+obj.url+')');
        saveImg(obj);
    } else if (uploadMode == 'album') {
        let obj = data[0],
            url = obj.url.replace(/\s/g, '%20');
        setCategoryValue(currentItem[0], obj.path, 'image');
        $g('#category-options div.img-thumbnail img').remove();
        $g('#category-options div.img-thumbnail').css('background-image', 'url('+JUri+url+')');
    } else if (uploadMode == 'watermark') {
        $g('#jform_watermark_upload').val(data[0].path);
        watermark = true;
    } else if (uploadMode == 'CKEImage') {
        $g('.cke-upload-image').val(data[0].url);
        $g('#add-cke-image').addClass('active-button');
    }           
}

function returnTrHtml(el, ind)
{
    if (!el.likes) {
        el.likes = 0;
    }
    var str = '<tr class="ba-images"><td class="select-td"><label ';
    str += 'class="ba-image">';
    str += '<input data-index="'+ind;
    str += '" class="select-item" type="checkbox" value="';
    str += '"><i class="zmdi zmdi-circle-o"></i><i class="zmdi';
    str += ' zmdi-check"></i></label><img data-src="'+JUri;
    str += 'administrator/index.php?option=com_bagallery&task=gallery.showImage&image=';
    str += encodeURIComponent(el.path)+'&time=1';
    str += '"></td><td class="draggable-handler">';
    str += el.name+'</td><td class="draggable-handler">';
    str += getFileSize(el.size)+'</td><td class="likes-container';
    if (el.likes * 1 > 0) {
        str += ' liked';
    }
    str += '">';
    str += '<i class="zmdi zmdi-favorite"></i><span>'+el.likes+'</span></td></tr>';

    return str;
}

function getFileSize(size)
{
    size = Math.floor(size / 1024);
    if (size >= 1024) {
        size = Math.floor(size / 1024)+' MB';
    } else {
        size = size+' KB';
    }

    return size;
}

function checkImages()
{
    var newArray = [];
    clientHeight = document.documentElement.clientHeight
    allImages.forEach(function(el, ind){
        if ($g(el.el).offset().top < clientHeight * 2) {
            el.el.src = el.img;
        } else {
            newArray.push(el)
        }
    });
    allImages = newArray
}

function getAllImages()
{
    allImages = [];
    $g('table.ba-items-table img').each(function(){
        var src = $g(this).attr('data-src');
        this.onload = function(){
            $g(this).closest('td').addClass('loaded');
        }
        var obj = {
            el : this,
            img : src
        }
        allImages.push(obj)                    
    });
    $g('div.table-body').on('scroll',function(){
        checkImages()
        if (allImages.length == 0) {
            $g(this).off('scroll')
        }
    });
    checkImages();
}

function drawPaginator()
{
    $g('div.table-body').find('.pagination').remove();
    if (catImages[currentCat].length > pagLimit && pagLimit != 1) {
        var pages = Math.ceil(catImages[currentCat].length / pagLimit),
            div = document.createElement('div'),
            ul = document.createElement('ul'),
            li = document.createElement('li'),
            a = document.createElement('a'),
            span = document.createElement('span');
        div.className = 'pagination';
        ul.className = 'pagination-list';
        li.className = 'disabled ba-first-page';
        span.className = 'icon-first';
        div.appendChild(ul);
        ul = appendItems(ul, li, a, span);
        li = document.createElement('li')
        li.className = 'disabled ba-prev';
        a = document.createElement('a');
        span = document.createElement('span');
        span.className = 'icon-previous';
        ul = appendItems(ul, li, a, span);
        for (var i = 0; i < pages; i++) {
            li = document.createElement('li');
            li.className = 'ba-pages';
            if (i == 0) {
                li.className += ' active';
            }
            a = document.createElement('a');
            span = document.createTextNode(i + 1);
            $g(a).attr('data-page', i);
            ul = appendItems(ul, li, a, span);
        }
        li = document.createElement('li');
        a = document.createElement('a');
        li.className = 'ba-next';
        span = document.createElement('span');
        span.className = 'icon-next';
        $g(a).attr('data-page', 1);
        ul = appendItems(ul, li, a, span);
        li = document.createElement('li');
        a = document.createElement('a');
        li.className = 'ba-last-page';
        span = document.createElement('span');
        span.className = 'icon-last';
        $g(a).attr('data-page', pages - 1);
        ul = appendItems(ul, li, a, span);
        $g(ul).find('a').on('click', function(event){
            event.preventDefault();
            var $this = $g(this);
            if ($this.parent().hasClass('active') || $this.parent().hasClass('disabled')) {
                return false;
            }
            var page = $this.attr('data-page') * 1,
                ul = $this.closest('ul'),
                first = ul.find('li').first(),
                last = ul.find('li').last(),
                max = page*pagLimit + pagLimit,
                tbody = $g('table.ba-items-table tbody').empty();
            ul.find('li.active').removeClass('active');
            first.removeClass('disabled');
            first.find('a').attr('data-page', 0);
            first.next().removeClass('disabled').find('a').attr('data-page', page - 1);
            last.removeClass('disabled');
            last.find('a').attr('data-page', pages - 1);
            last.prev().removeClass('disabled').find('a').attr('data-page', page + 1);
            ul.find('.ba-pages [data-page="'+page+'"]').parent().addClass('active');
            if (page == 0) {
                first.addClass('disabled');
                first.find('a').removeAttr('data-page');
                first.next().addClass('disabled').find('a').removeAttr('data-page');
            }
            if (page == pages - 1) {
                last.addClass('disabled');
                last.find('a').removeAttr('data-page');
                last.prev().addClass('disabled').find('a').removeAttr('data-page');
            }
            if (catImages[currentCat].length < max) {
                max = catImages[currentCat].length;                        
            }
            for (var i = page * pagLimit; i < max; i++) {
                var str = returnTrHtml(catImages[currentCat][i], i);
                tbody.append(str);
                tbody.find('.select-item').last().val(JSON.stringify(catImages[currentCat][i]))
            }
            checkPagination();
            getAllImages();
        });
        $g('div.table-body').append(div);
        checkPagination();
    }
}

function checkPagination()
{
    var paginator = $g('.pagination-list'),
        current,
        curInd = 0,
        pagButtons = paginator.find('li').not('.ba-first-page, .ba-last-page, .ba-prev, .ba-next');
    if (pagButtons.length >= 5) {
        pagButtons.each(function(ind, el){
            if ($g(this).hasClass('active')) {
                current = $g(this);
                curInd = ind;
                return false;
            }
        });
        if (curInd <= 2) {
            pagButtons.each(function(ind, el){
                if (ind < 5) {
                    $g(this).show();
                } else {
                    $g(this).hide();
                }
            });
        } else if (curInd + 1 > pagButtons.length - 3) {
            for (var i = pagButtons.length - 1; i >= 0; i--) {
                if (i >= pagButtons.length - 5) {
                    $g(pagButtons[i]).show();
                } else {
                    $g(pagButtons[i]).hide();
                }
            }
        } else {
            pagButtons.hide();
            current.show().prev().show().prev().show();
            current.next().show().next().show();
        }
    }
}

function appendItems(ul, li, a, span)
{
    a.appendChild(span);
    li.appendChild(a);
    ul.appendChild(li);

    return ul;
}


function showDataTagsDialog(dialog)
{
    var rect = fontBtn.getBoundingClientRect(),
        modal = $g('#'+dialog),
        width = modal.innerWidth(),
        height = modal.innerHeight(),
        top = rect.bottom - height / 2 - rect.height / 2,
        bottom = '50%';
    if (window.innerHeight - top < height) {
        top = window.innerHeight - height;
        bottom = (window.innerHeight - rect.bottom + rect.height / 2)+'px';
    } else if (top < 0) {
        top = 0;
        bottom = (height - rect.bottom + rect.height / 2)+'px';
    }
    modal.css({
        left: rect.left - width - 10,
        top: top
    }).modal()[0].style.setProperty('--picker-arrow-bottom', bottom);
}

app.setMinicolors = function(){
    $g('input[data-type="color"]').each(function(){
        var div = document.createElement('div');
        div.className = 'minicolors minicolors-theme-bootstrap';
        this.classList.add('minicolors-input');
        $g(this).nextAll('.minicolors-opacity-wrapper').find('input').attr('data-callback', '');
        $g(this).wrap(div);
        $g(this).after('<span class="minicolors-swatch"><span class="minicolors-swatch-color"></span></span>');
    }).on('click', function(){
        fontBtn = this;
        setMinicolorsColor(this.dataset.rgba);
        showDataTagsDialog('color-variables-dialog');
    }).on('input', inputColor).next().on('click', function(){
        $g(this).prev().trigger('click');
    });
    $g('.custom-minicolors-trigger').each(function(){
        let color = document.querySelector('#jform_'+this.dataset.colorInput).value,
            opacity = document.querySelector('#jform_'+this.dataset.opacityInput).value,
            hex = parseInt(((color.indexOf('#') > -1) ? color.substring(1) : color), 16),
            rgb = {
                r: hex >> 16,
                g: (hex & 0x00FF00) >> 8,
                b: (hex & 0x0000FF)
            };
        updateInput($g(this), 'rgba('+rgb.r+', '+rgb.g+', '+rgb.b+', '+parseFloat(opacity)+')');
    }).on('minicolorsInput', function(){
        let color = rgba2hex(this.dataset.rgba);
        document.querySelector('#jform_'+this.dataset.colorInput).value = color[0];
        document.querySelector('#jform_'+this.dataset.opacityInput).value = color[1];
    });
    $g('.minicolors-trigger').each(function(){
        let rgba = document.querySelector('#jform_'+this.id).value;
        updateInput($g(this), rgba);
    }).on('minicolorsInput', function(){
        $g('#jform_'+this.id).val(this.dataset.rgba);
    });
    $g('.variables-color-picker').minicolors({
        opacity: true,
        theme: 'bootstrap',
        change: function(hex, opacity) {
            var rgba = $g(this).minicolors('rgbaString');
            fontBtn.value = hex;
            $g('.variables-color-picker').closest('#color-picker-cell')
                .find('.minicolors-opacity').val(opacity * 1);
            fontBtn.dataset.rgba = rgba;
            $g(fontBtn).trigger('minicolorsInput').next().find('.minicolors-swatch-color')
                .css('background-color', rgba).closest('.minicolors').next()
                .find('.minicolors-opacity').val(opacity * 1).removeAttr('readonly');
        }
    });
    $g('#color-variables-dialog').on('hide', function(){
        if (fontBtn.closest('.search-colors')) {
            addColorFilter(fontBtn)
        }
    });
    $g('#color-variables-dialog .minicolors-opacity').on('input', function(){
        var obj = {
            color: $g('.variables-color-picker').val(),
            opacity: this.value * 1,
            update: false
        }
        $g('.variables-color-picker').minicolors('value', obj);
        fontBtn.dataset.rgba = $g('.variables-color-picker').minicolors('rgbaString');
        $g(fontBtn).trigger('minicolorsInput');
        if (fontBtn.localName == 'input') {
            $g(fontBtn).next().find('.minicolors-swatch-color').css('background-color', fontBtn.dataset.rgba)
                .closest('.minicolors').next().find('.minicolors-opacity').val(this.value);
        }
    });
    $g('.minicolors-opacity[data-callback]').on('input', function(){
        var input = $g(this).parent().prev().find('.minicolors-input')[0],
            opacity = this.value * 1
            value = input.dataset.rgba;
        if (this.value) {
            var parts = value.match(/^rgba?\((\d+),\s*(\d+),\s*(\d+)(?:,\s*(\d+(?:\.\d+)?))?\)$/),
                rgba = 'rgba(';
            if (parts) {
                for (var i = 1; i < 4; i++) {
                    rgba += parts[i]+', ';
                }
            } else {
                parts = value.match(/[^#]\w/g);
                for (var i = 0; i < 3; i++) {
                    rgba += parseInt(parts[i], 16);
                    rgba += ', ';
                }
                
            }
            rgba += this.value+')';
            input.dataset.rgba = rgba;
            $g(input).next().find('.minicolors-swatch-color').css('background-color', rgba);
            $g(input).trigger('minicolorsInput');
        }
    });
}

document.addEventListener('DOMContentLoaded', function(){
    if (!$g) {
        $g = window.jQuery;
    }

    pagLimit = $g('.pagination-limit .ba-custom-select input').attr('data-value') * 1;

    app.setMinicolors();

    $g('body .modal').on('shown', function(){
        let backdrop = $g('.modal-backdrop').last().addClass(this.id+'-backdrop');
    });

    if ($g('#jform_id').val() * 1) {
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : "index.php?option=com_bagallery&task=gallery.checkProductTour&tmpl=component",
            success : function(msg){
                if (msg == 'false') {
                    $g('.quick-view').trigger('mousedown');
                }
            }
        });
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : "index.php?option=com_bagallery&task=gallery.checkRate&tmpl=component",
            success : function(msg){
                if (msg == 'true') {
                    $g('#love-gallery-modal').modal();
                }
            }
        });
    }
    notification = document.getElementById('ba-notification');

    setTimeout(function(){
        $g('.alert.alert-success, joomla-alert[type="success"]').addClass('animation-out');
    }, 2000);

    $g('.ba-range-wrapper input[type="range"]').each(function(){
        rangeAction(this, inputCallback);
    });

    setInterval(function(){
        app.fetch("index.php?option=com_bagallery&task=gallery.getSession&tmpl=component");
    }, 600000);

    Joomla.submitbutton = function(task) {
        if (task == "gallery.cancel" || document.formvalidator.isValid(document.getElementById("adminForm"))) {
            Joomla.submitbutton = function(task){
                return false;
            }
            $g(window).on('keydown', function(e){
                if (e.keyCode == 116 || e.keyCode == 82) {
                    e.preventDefault();
                    e.stopPropagation();
                }
            });
            if (task != "gallery.cancel") {
                $g('.alert.alert-success, joomla-alert[type="success"]').addClass('animation-out');
                $g('input[name="categories[]"]').remove();
                document.querySelectorAll('li.ba-category').forEach(function(li){
                    let category = getCategoryObject(li),
                        input = document.createElement('input');
                    category.parent = '';
                    if (!li.closest('ul').classList.contains('root-list')) {
                        category.parent = 'category-' + getCategoryObject(li.parentNode.closest('li')).category_id;
                    }
                    input.type = "hidden"
                    input.value = JSON.stringify(category);
                    input.name = 'categories[]';
                    document.forms.adminForm.append(input);
                })
                var items = [],
                    array = [],
                    sort = [],
                    flag = false,
                    album_flag = false,
                    watermarkNames = [],
                    thumbnailArray = {},
                    id = $g('#jform_id').val(),
                    allThumb = [],
                    allCat = [];
                if (width != $g('#jform_image_width').val()
                    || quality != $g('#jform_image_quality').val() ||
                    layout != $g('#jform_gallery_layout').val()) {
                    flag = true;
                }
                if (album_width != $g('#jform_album_width').val()
                    || album_quality != $g('#jform_album_quality').val() ||
                    album_layout != $g('#jform_album_layout').val()) {
                    album_flag = true;
                }
                $g('.category-list li').each(function(){
                    var key = $g(this).attr('id');
                    if (catImages[key]) {
                        catImages[key].forEach(function(el, ind){
                            if (flag) {
                                el.thumbnail_url = '';
                            }
                            if ($g.inArray(el.category, allCat) == -1) {
                                allCat.push(el.category);
                            }
                            if (el.thumbnail_url) {
                                var thumb = el.thumbnail_url.split('/');
                                if (!thumbnailArray[el.category]) {
                                    thumbnailArray[el.category] = [];
                                }
                                thumbnailArray[el.category].push(thumb[thumb.length - 1]);
                            }
                            if (el.watermark_name && watermarkNames.indexOf(el.watermark_name) == -1) {
                                watermarkNames.push(el.watermark_name);
                            } else if (watermarkNames.indexOf(el.watermark_name) != -1) {
                                el.resave = 1;
                                el.watermark_name = '';
                            }
                            array.push(el);
                            sort.push(el.imageId);
                        });
                    }
                });
                allThumb = JSON.stringify(thumbnailArray);
                if (flag) {
                    app.fetch("index.php?option=com_bagallery&task=gallery.emptyThumbnails", {
                        ba_id : id
                    })
                }
                if (album_flag) {
                    app.fetch("index.php?option=com_bagallery&task=gallery.emptyAlbums", {
                        ba_id : id
                    })
                }
                if (watermark) {
                    app.fetch("index.php?option=com_bagallery&task=gallery.removeWatermark", {
                        ba_id : id
                    });
                }
                if (compWidth != $g('#jform_compression_width').val() ||
                    compQuality != $g('#jform_compression_quality').val()) {
                    app.fetch("index.php?option=com_bagallery&task=gallery.removeCompression", {
                        ba_id : id
                    });
                }
                sort = sort.join('-_-');
                $g('#jform_settings').val(sort);
                $g('#jform_gallery_items').val('');
                array.forEach(function(el){
                    var name;
                    if (el.id) {
                        items.push(el.id);
                    }
                    if (!el.thumbnail_url) {
                        name = checkName(thumbnailArray[el.category], el.name);
                        if (!thumbnailArray[el.category]) {
                            thumbnailArray[el.category] = [];
                        }
                        thumbnailArray[el.category].push(name);
                        el.thumbnail_url = '/images/bagallery/gallery-'+id+'/thumbnail/'+el.category+'/'+name;
                    }
                    if (!el.watermark_name) {
                        name = checkName(watermarkNames, el.name);
                        el.watermark_name = name;
                        watermarkNames.push(el.watermark_name);
                    }
                });
                var str = app._('SAVING')+'<img src="'+JUri;
                str += 'administrator/components/com_bagallery/assets/images/reload.svg"></img>';
                notification.className = 'notification-in';
                notification.children[0].innerHTML = str;
                clearTimeout(animationOut);
                saveData(array, allThumb, allCat, id, task, items)
            } else {
                Joomla.submitform(task, document.getElementById("adminForm"));
            }
        }
    }

    function saveData(array, allThumb, allCat, id, task, items)
    {
        var length = array.length,
            max = length > 50 ? 50 : length;
        if (length > 0) {
            var imgArray = [],
                medium = [];
            imgArray = array.splice(0, max);
            for (var i = 0; i < max; i++) {
                if (imgArray[i].resave == 1) {
                    medium.push(imgArray[i]);
                }
            }
            if (medium.length == 0) {
                saveData(array, allThumb, allCat, id, task, items);
                return false;
            }
            imgArray = JSON.stringify(medium);
            $g.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_bagallery&task=gallery.saveItems&tmpl=component",
                data : {
                    gallery_items : imgArray,
                    ba_id : id
                },
                success: function(msg) {
                    msg = JSON.parse(msg);
                    msg = JSON.parse(msg.message);
                    for (var i = 0; i < msg.length; i++) {
                        items.push(msg[i]);
                    }
                    saveData(array, allThumb, allCat, id, task, items);
                }
            });
        } else {
            items = JSON.stringify(items);
            allCat = JSON.stringify(allCat);
            $g.ajax({
                type : "POST",
                dataType : 'text',
                url : "index.php?option=com_bagallery&task=gallery.clearOld&tmpl=component",
                data : {
                    'gallery_items' : items,
                    'allThumb' : allThumb,
                    'allCat' : allCat,
                    ba_id : id
                }
            });
            Joomla.submitform(task, document.getElementById("adminForm"));
        }
    }

    function checkName(array, name)
    {
        if ($g.inArray(name, array) > -1) {
            name = getRandomInt(0, 999999999)+'-'+name;
            name = checkName(array, name);
        }

        return name;
    }

    function getRandomInt(min, max) {
        return Math.floor(Math.random() * (max - min)) + min;
    }

    function sendData(obj)
    {
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : "index.php?option=com_bagallery&task=gallery.saveItems&tmpl=component",
            data : {
                gallery_items : array,
                ba_id : $g('#jform_id').val()
            }
        });
    }

    function saveTags()
    {
        var tags = [],
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        $g('select.meta_tags option').each(function(){
            var object = {
                id : this.value,
                title : this.textContent.trim()
            }
            tags.push(object);
        });
        obj.tags = tags;
        obj.resave = 1;
        saveImg(obj);
    }

    $g('#create-gallery-modal input.gallery-name').on('input', function(){
        if (this.value.trim()) {
            $g('#create-gallery').addClass('active-button');
        } else {
            $g('#create-gallery').removeClass('active-button');
        }            
    });

    $g('#create-gallery').on('click', function(event){
        event.preventDefault();
        if (this.classList.contains('active-button')) {
            var name = $g('#create-gallery-modal input.gallery-name').val();
            $g('#jform_title').val(name);
            $g('#create-gallery-modal').removeClass('in').addClass('hide');
            Joomla.submitbutton('gallery.apply');
        }
    });

    var animationOut,
        CKE = CKEDITOR.replace('CKE-editor'),
        ckeImage = '',
        deleteMode,
        compWidth = $g('#jform_compression_width').val(),
        compQuality = $g('#jform_compression_quality').val(),
        width = $g('#jform_image_width').val(),
        quality = $g('#jform_image_quality').val(),
        layout = $g('#jform_gallery_layout').val(),
        album_width = $g('#jform_album_width').val(),
        album_quality = $g('#jform_album_quality').val(),
        album_layout = $g('#jform_album_layout').val(),
        target = $g('.category-list ul.root-list'),
        str = '',
        albumMode = true,
        contextMode = false,
        oldName = '',
        clientHeight = document.documentElement.clientHeight,
        UpType = $g('#select-upload-type');

    catImages['root'] = [];

    window.categories.forEach(function(category, ind){
        str = getCategoryHTML(category, ind);
        if (!category.parent) {
            target.append(str);
        } else {
            if ($g('#'+category.parent).find('> ul').length == 0) {
                $g('#'+category.parent).append('<i class="zmdi zmdi-chevron-right"></i><ul></ul>');
            }
            $g('#'+category.parent).find('> ul').append(str)
        }
    });
    
    if (window.images.length > 0) {
        var newArrayI = [],
            sort = $g('#jform_settings').val();
        sort = sort.split('-_-');
        window.images.forEach(function(el, ind){
            var settings = JSON.parse(el.settings);
            if (catImages[settings.category]) {
                settings.resave = 0;
                newArrayI[settings.imageId] = settings;
                if (imageId <= settings.imageId) {
                    imageId = settings.imageId;
                    imageId++;
                }
            }
        });
        
        sort.forEach(function(set) {
            var settings = newArrayI[set];
            if (settings) {
                catImages[settings.category].push(settings);
            }
        });
    }

    $g('#jform_album_mode').on('click', function(){
        checkAlbum();
    });
    checkAlbum();
    checkComments();

    function checkComments()
    {
        var comments = $g('#jform_enable_disqus').val(),
            vk = $g('.vk-options'),
            disqus = $g('.disqus-options');
        if (comments == 1) {
            disqus.show();
            vk.hide()
        } else if (comments == 'vkontakte') {
            disqus.hide();
            vk.show()
        } else {
            disqus.hide();
            vk.hide()
        }
    }

    function checkCompression()
    {
        if (document.getElementById('jform_enable_compression').checked) {
            $g('.compression-options').css('display', '');
        } else {
            $g('.compression-options').hide();
        }
    }

    checkCompression();
    $g('#jform_enable_compression').on('change', checkCompression);

    function checkPageRefresh()
    {
        var pagT = $g('#jform_pagination_type'),
            value = pagT.val(),
            pagType = pagT.prev(),
            def = pagType.find('li[data-value="default"]').text().trim();
        if ($g('#jform_page_refresh').prop('checked')) {
            pagType.find('li[data-value="infinity"], li[data-value="load"]').hide();
            if (value == 'infinity' || value == 'load') {
                pagT.val('default');
                pagType.find('.ba-form-trigger').attr('data-value', 'default').val(def);
            }
        } else {
            pagType.find('li[data-value="infinity"], li[data-value="load"]').css('display', '');
        }
    }

    checkPageRefresh();
    $g('#jform_page_refresh').on('click', function(){
        checkPageRefresh();
    });

    function checkAlbum()
    {
        if ($g('#jform_album_mode').prop('checked')) {
            albumMode = true;
            var obj = getCategoryObject($g('.root-list .zmdi.zmdi-star').closest('.ba-category')[0]);
            obj.default = 0;
            $g('div.meta-tags').hide().prev().hide().prev().hide();
            $g('div.image-colors').hide().prev().hide().prev().hide();
            $g('.root-list .zmdi.zmdi-star').remove();
            obj = getCategoryObject($g('#category-all')[0]);
            obj.published = 1;
            $g('#category-all').removeClass('ba-unpublish');
            checkDefault();
            $g('body').addClass('album-mode');
            $g('a[href="#filter-options"]').hide();
            $g('a[href="#album-options"]').show();
            $g('input.default-category').closest('div').hide();
            $g('input.hide-in-category-all').closest('div').hide();
            $g('.folders-context-menu .move-to, #move-to-modal li[data-id="root"] > span').show();
            $g('#category-options div.img-thumbnail').show();
        } else {
            albumMode = false;
            $g('body').removeClass('album-mode');
            $g('div.meta-tags').css('display', '').prev().css('display', '').prev().css('display', '');
            $g('div.image-colors').css('display', '').prev().css('display', '').prev().css('display', '');
            $g('a[href="#filter-options"]').show();
            $g('a[href="#album-options"]').hide();
            $g('input.default-category').closest('div').show();
            $g('input.hide-in-category-all').closest('div').show();
            $g('.folders-context-menu .move-to, #move-to-modal li[data-id="root"] > span').hide();
            $g('#category-options div.img-thumbnail').hide();
            $g('#root .root-list li').each(function(){
                var $this = $g(this),
                    ul = $this.parent();
                if (!ul.hasClass('root-list')) {
                    $g('#root .root-list').append(this);
                }
            });
            $g('#root .root-list ul, #root .root-list i.zmdi-chevron-right').remove();
            catImages['root'] = [];
        }
        if (currentCat == 'root') {
            $g('#root > a').trigger('click');
        }
    }

    function checkAutoResize()
    {
        if ($g('#jform_auto_resize').prop('checked')) {
            $g('.lightbox-width').hide();
        } else {
            $g('.lightbox-width').show();
        }
    }

    function checkHeader()
    {
        var childrens = $g('#lightbox-header-options').children();
        if ($g("#jform_display_header").prop('checked')) {
            childrens.show();
        } else {
            childrens.hide();
            childrens.first().show();
        }
        if (!$g('#jform_enable_alias').prop('checked')) {
            $g('.share-group').hide().prev().hide();
        }
    }

    function strip_tags(str)
    {
        return str.replace(/<\/?[^>]+>/gi, '');
    }

    function checkDefault()
    {
        let obj = getCategoryObject(document.querySelector('#category-all'));
        obj.default = 1;
        $g('#category-all > a').append('<i class="zmdi zmdi-star"></i>');
    }

    function addRangeWidth(input)
    {
        var max = input.attr('max') * 1,
            value = input.val(),
            sx = ((value * 100) / max) * input.width() / 100;
        input.prev().width(sx);
    }

    function checkLightbox()
    {
        if ($g('#enable-lightbox').prop('checked')) {
            $g('#lightbox-options .left-tabs > ul > li:not(:first-child)').show();
            $g('a[href="#copyright-watermark-options"]').parent().show();
            if (!$g('#jform_enable_alias').prop('checked')) {
                $g('a[href="#lightbox-comments-options"]').parent().hide();
            }
            $g('#lightbox-general-options > *').show();
            checkAutoResize();
        } else {
            $g('#lightbox-options .left-tabs > ul > li:not(:first-child)').hide();
            $g('a[href="#copyright-watermark-options"]').parent().hide();
            $g('#lightbox-general-options > *').hide();
            $g('#lightbox-general-options > .ba-options-group').first().show();
        }
    }

    function checkAlias()
    {
        if ($g('#jform_enable_alias').prop('checked')) {
            $g('.image-alias').show().parent().prev().show();
            if ($g('#enable-lightbox').prop('checked')) {
                $g('a[href="#lightbox-comments-options"]').parent().show();
            }
            if ($g('#jform_display_header').prop('checked')) {
                $g('.share-group').show().prev().show();
            }
        } else {
            $g('.share-group').hide().prev().hide();
            $g('.image-alias').hide().parent().prev().hide();
            $g('a[href="#lightbox-comments-options"]').parent().hide();
        }
    }

    $g('.regenerate-thumbnails').on('change', function(){
        let $this = this;
        if (this.dataset.queue != 'queue') {
            this.dataset.queue = 'queue';
            let str = '<span>'+app._('REGENERATE_THUMBNAILS_WAIT');
            str += '</span><img src="'+JUri+'administrator/components/com_bagallery/assets/images/reload.svg"></img>';
            notification.querySelector('p').innerHTML = str;
            notification.classList.remove('animation-out');
            notification.classList.add('notification-in');
            app.fetch('index.php?option=com_bagallery&task=uploader.regenerateThumbnails', {
                id: document.querySelector('#jform_id').value
            }).then(function(){
                setTimeout(function(){
                    $this.dataset.queue = '';
                    notification.classList.add('animation-out');
                    notification.classList.remove('notification-in');
                }, 3000);
            });
        }
        setTimeout(function(){
            $this.checked = false;
        }, 200);
    })

    $g('.cke-image-alt, .cke-image-width, .cke-image-height').on('input', function(){
        if ($g('.cke-upload-image').val()) {
            $g('#add-cke-image').addClass('active-button');
        }
    });

    $g('.cke-image-select').on('customHide', function(){
        if ($g('.cke-upload-image').val()) {
            $g('#add-cke-image').addClass('active-button');
        }
    });

    $g('#add-cke-image').on('click', function(event){
        event.preventDefault();
        if ($g(this).hasClass('active-button')) {
            var url = $g('.cke-upload-image').val(),
                alt = $g('.cke-image-alt').val().trim(),
                width = $g('.cke-image-width').val(),
                height = $g('.cke-image-height').val(),
                align = $g('.cke-image-align').val(),
                img = '',
                doc = $g('#html-editor iframe')[0].contentDocument;
            if (width) {
                width += 'px';
            }
            if (height) {
                height += 'px';
            }
            if (ckeImage) {
                ckeImage.src = url;
                ckeImage.alt = alt;
                ckeImage.style.width = width;
                ckeImage.style.height = height;
                ckeImage.style.float = align;
            } else {
                img = document.createElement('img');
                img.src = url;
                img.alt = alt;
                img.style.width = width;
                img.style.height = height;
                img.style.float = align;
                if (doc.getSelection().rangeCount > 0) {
                    var range = doc.getSelection().getRangeAt(0);
                    range.insertNode(img);
                } else {
                    var data = CKE.getData();
                    data += img.outerHTML;
                    CKE.setData(data);
                }
            }
            $g('#cke-image-modal').modal('hide');
        }
    });

    function sortName(obj1, obj2)
    {
        if (obj1.name.toLowerCase() > obj2.name.toLowerCase()) {
            return 1;
        }
        if (obj1.name.toLowerCase() < obj2.name.toLowerCase()) {
            return -1;
        } else {
            return 0;
        }
    }

    function checkFilter()
    {
        var childrens = $g('#filter-categories-options').children();
        if ($g('#jform_category_list').prop('checked')) {
            childrens.show();
        } else {
            childrens.not(childrens[0]).hide();
        }
    }

    function checkTags()
    {
        var childrens = $g('#filter-tags-options').children();
        if ($g('#jform_enable_tags').prop('checked')) {
            childrens.show();
        } else {
            childrens.not(childrens[0]).hide();
        }
    }

    function checkColors()
    {
        var childrens = $g('#filter-colors-options').children();
        if ($g('#jform_enable_colors').prop('checked')) {
            childrens.show();
        } else {
            childrens.not(childrens[0]).hide();
        }
    }

    function checkPaginator()
    {
        if ($g('#jform_pagination').prop('checked')) {
            $g('#pagination-options').children().show();
        } else {
            $g('#pagination-options').children().hide();
            $g('#pagination-options').children().first().show();
        }
    }
    
    function sortDate(obj1, obj2)
    {
        return obj1.time - obj2.time;
    }

    function checkContext(context, deltaY, deltaX)
    {
        if (deltaX - context.width() < 0) {
            context.addClass('ba-left');
        } else {
            context.removeClass('ba-left');
        }
        if (deltaY - context.height() < 0) {
            context.addClass('ba-top');
        } else {
            context.removeClass('ba-top');
        }
    }

    function checkCaptionOptions()
    {
        if (!$g('#jform_disable_caption').prop('checked')) {
            $g('a[href="#thumbnail-typography-options"]').parent().show();
            $g('.caption-group').show();
            var value = document.getElementById('jform_thumbnail_layout').value;
            if (value == '11' || value == '13') {
                $g('#jform_caption_bg').closest('.ba-group-element').hide();
            }
        } else {
            $g('a[href="#thumbnail-typography-options"]').parent().hide();
            $g('.caption-group').hide();
        }
    }

    function checkAlbumCaptionOptions()
    {
        if (!$g('#jform_album_disable_caption').prop('checked')) {
            $g('a[href="#album-typography-options"]').parent().show();
            $g('.album-caption-group').show();
            var value = document.getElementById('jform_album_thumbnail_layout').value;
            if (value == '11' || value == '13') {
                $g('#jform_album_caption_bg').closest('.ba-group-element').hide();
            }
        } else {
            $g('a[href="#album-typography-options"]').parent().hide();
            $g('.album-caption-group').hide();
        }
    }

    function reIndexArray()
    {
        if (currentCat) {
            var array = [];
            catImages[currentCat].forEach(function(el, ind){
                array.push(el);
            });
            catImages[currentCat] = array;
        }
    }

    function checkRandomGrid()
    {
        var value =  $g('#jform_gallery_layout').val(),
            thumb = $g('#jform_thumbnail_layout'),
            effect = thumb.prev();
        if (value == 'random') {
            effect.find('li[data-value="10"]').removeClass('disabled-hover-effect');
        } else {
            effect.find('li[data-value="10"]').addClass('disabled-hover-effect');
            if (thumb.val() == '10') {
                thumb.val(10);
                var name = effect.find('li').first().text().trim();
                effect.find('input').attr('data-value', 1).val(name);
                $g('#jform_thumbnail_layout').val(1);
            }
        }
    }

    function checkAlbumRandomGrid()
    {
        var value =  $g('#jform_album_layout').val(),
            thumb = $g('#jform_album_thumbnail_layout'),
            effect = thumb.prev();
        if (value == 'random') {
            effect.find('li[data-value="10"]').removeClass('disabled-hover-effect')
        } else {
            effect.find('li[data-value="10"]').addClass('disabled-hover-effect')
            if (thumb.val() == '10') {
                thumb.val(10);
                var name = effect.find('li').first().text().trim();
                effect.find('input').attr('data-value', 1).val(name);
                $g('#jform_album_thumbnail_layout').val(1);
            }
        }
    }

    function checkEffect()
    {
        var value = document.getElementById('jform_thumbnail_layout').value;
        if (value == '11' || value == '13') {
            $g('#jform_caption_bg').closest('.ba-group-element').hide();
            $g('#title_color, #category_color, #description_color').closest('.ba-group-element').hide();
        } else {
            $g('#jform_caption_bg').closest('.ba-group-element').show();
            $g('#title_color, #category_color, #description_color').closest('.ba-group-element').show();
        }
    }

    function checkAlbumEffect()
    {
        var value = document.getElementById('jform_album_thumbnail_layout').value;
        if (value == '11' || value == '13') {
            $g('#jform_album_caption_bg').closest('.ba-group-element').hide();
            $g('#album_title_color, #album_img_count_color').closest('.ba-group-element').hide();
        } else {
            $g('#jform_album_caption_bg').closest('.ba-group-element').show();
            $g('#album_title_color, #album_img_count_color').closest('.ba-group-element').show();
        }
    }

    function showContext(event)
    {
        event.stopPropagation();
        event.preventDefault();
        $g('.context-active').removeClass('context-active');
        currentContext.addClass('context-active');
        var deltaX = document.documentElement.clientWidth - event.pageX,
            deltaY = document.documentElement.clientHeight - event.clientY,
            context;
        if (currentContext.hasClass('ba-images')) {
            context = $g('.files-context-menu');
        } else {
            if (currentContext[0].localName == 'tr') {
                var id = currentContext.attr('data-id');
                currentContext = $g('#'+id+' > a');
            }
            context = $g('.folders-context-menu');
        }
        setTimeout(function(){
            context.css({
                'top' : event.pageY,
                'left' : event.pageX,
            }).show();
            checkContext(context, deltaY, deltaX);
        }, 50);
    }

    if ($g('html').attr('dir') == 'rtl') {
        CKEDITOR.config.contentsLangDirection = 'rtl';
    }
    CKE.setUiColor('#fafafa');
    CKE.config.allowedContent = true;
    CKEDITOR.dtd.$removeEmpty.span = 0;
    CKEDITOR.dtd.$removeEmpty.i = 0;
    CKE.config.toolbar_Basic =
    [
        { name: 'document',    items : [ 'Source' ] },
        { name: 'styles',      items : [ 'Styles','Format' ] },
        { name: 'colors',      items : [ 'TextColor' ] },
        { name: 'clipboard',   items : [ 'Undo','Redo' ] },            
        { name: 'basicstyles', items : [ 'Bold','Italic','Underline'] },
        { name: 'paragraph',   items : [ 'NumberedList','BulletedList','-','Outdent',
                                        'Indent','-','Blockquote','-','JustifyLeft',
                                        'JustifyCenter','JustifyRight','JustifyBlock','-' ] },
        { name: 'links',       items : [ 'Link','Unlink','Anchor' ] },
        { name: 'insert',      items : [ 'myImage','Table','HorizontalRule'] }
    ];
    CKE.config.toolbar = 'Basic';
    CKEDITOR.config.removePlugins = 'image';
    CKE.addCommand("imgComand", {
        exec: function(edt) {
            $g('#add-cke-image').removeClass('active-button');
            var align = src = w = h = alt = label = '',
                selected = CKE.getSelection().getSelectedElement()
            if (selected && selected.$g.localName == 'img') {
                ckeImage = selected.$;
                src = ckeImage.src;
                alt = ckeImage.alt;
                w = ckeImage.style.width.replace('px', '');
                h = ckeImage.style.height.replace('px', '');
                align = ckeImage.style.float;
                label = $g('.cke-image-align').parent().find('li[data-value="'+align+'"]').text().trim();
            } else {
                ckeImage = '';
            }
            $g('.cke-upload-image').val(src);
            $g('.cke-image-alt').val(alt);
            $g('.cke-image-width').val(w);
            $g('.cke-image-height').val(h);
            $g('.cke-image-align').attr('data-value', align);
            $g('.cke-image-align').val(label);
            $g('#cke-image-modal').modal();
        }
    });
    CKE.ui.addButton('myImage', {
        label: "Image",
        command: 'imgComand',
        toolbar: 'insert',
        icon: 'image'
    });

    $g('.cke-upload-image').on('mousedown', function(){
        $g('#uploader-modal').attr('data-check', 'single').modal();
        uploadMode = 'CKEImage';
    });

    $g('.modal').on('hide', function(){
        $g(this).addClass('ba-modal-close');
        setTimeout(function(){
            $g('.ba-modal-close').removeClass('ba-modal-close');
        }, 500)
    });

    $g('i.check-all').on('click', function(){
        $g('#check-all').trigger('click');
    });

    $g('i.sort-action').closest('.ba-custom-select').on('show', function(){
        var sort = $g('#jform_sorting_mode').val();
        $g(this).find('li').removeClass('selected').each(function(){
            var $this = $g(this);
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == sort) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
    });

    $g('.aspect-ratio-select').on('show', function(){
        var $this = $g(this),
            ul = $this.find('ul'),
            value = $this.find('input[type="hidden"]').val();
        ul.find('i').remove();
        ul.find('.selected').removeClass('selected');
        ul.find('li[data-value="'+value+'"]').addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
    });

    $g('label.ba-help').on('click', function(event){
        var coor = this.getBoundingClientRect();
        $g('div.help-context-menu').css({
            'top' : coor.bottom,
            'left' : coor.right,
        }).show();
    })

    $g('.gallery-editor .category-list, .gallery-editor .images-list').on('contextmenu', function(event){
        event.preventDefault();
    });

    $g('div.category-list, div.table-body').on('contextmenu', function(event){
        $g('.context-active').removeClass('context-active');
        var deltaX = document.documentElement.clientWidth - event.pageX,
            deltaY = document.documentElement.clientHeight - event.clientY,
            context;
        setTimeout(function(){
            context = $g('.empty-context-menu');
            context.css({
                'top' : event.pageY,
                'left' : event.pageX,
            }).show();
            checkContext(context, deltaY, deltaX);
        }, 50);
    });

    $g('.new-name').on('input', function(){
        var name = $g(this).val();
        if ($g.trim(name) && name != oldName) {
            $g('#apply-rename').addClass('active-button');
        } else {
            $g('#apply-rename').removeClass('active-button');
        }
    });

    $g('#apply-rename').on('click', function(event){
        event.preventDefault();
        if (!$g(this).hasClass('active-button')) {
            return false;
        }
        const name = $g('.new-name').val().trim(),
            id = currentContext.closest('li').attr('id');
        $g('tr[data-id="'+id+'"] td.draggable-handler a').text(name)
        setCategoryValue(currentContext.closest('li')[0], name, 'title');
        currentContext.find('span').text(name);
        if (currentItem && currentItem[0].id == currentContext.parent()[0].id) {
            $g('#category-name').val(name);
        }            
        $g('#rename-modal').modal('hide');
    });

    $g('.ba-context-menu .documentation, .ba-context-menu .support').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        event.stopPropagation();
        setTimeout(function(){
            $g('div.help-context-menu').hide();
        }, 150);
    });

    $g('.ba-context-menu .love-gallery').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        $g('#love-gallery-modal').modal();
    });

    $g('.ba-context-menu .quick-view').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        let sBackdrop = $g('<div/>', {
            'class' : 'saving-backdrop'
        });
        document.body.append(sBackdrop[0]);
        $g('.product-tour.step-1').addClass('visible');
        $g('.category-list .create-categery').addClass('active-product-tour');
    });

    $g('.product-tour.step-1 .next').on('click', function(){
        $g('.product-tour.step-1').removeClass('visible');
        $g('.category-list .create-categery').removeClass('active-product-tour');
        $g('.product-tour.step-2').addClass('visible');
        $g('.images-list .camera-container').addClass('active-product-tour');
    });

    $g('.product-tour.step-2 .next').on('click', function(){
        $g('.product-tour.step-2').removeClass('visible');
        $g('.images-list .camera-container').removeClass('active-product-tour');
        $g('.product-tour.step-3').addClass('visible');
        $g('.ba-toolbar-icons .settings').addClass('active-product-tour');
    });

    $g('.product-tour.step-3 .close').on('click', function(){
        $g('.product-tour.step-3').removeClass('visible');
        $g('.ba-toolbar-icons .settings').removeClass('active-product-tour');
        let sBackdrop = document.querySelector('.saving-backdrop');
        sBackdrop.className += ' animation-out';
        setTimeout(function(){
            document.body.removeChild(sBackdrop);
        }, 300);
    });

    $g('.product-tour .zmdi.zmdi-close').on('click', function(){
        $g('.product-tour.step-1').removeClass('visible');
        $g('.category-list .create-categery').removeClass('active-product-tour');
        $g('.product-tour.step-2').removeClass('visible');
        $g('.images-list .camera-container').removeClass('active-product-tour');
        $g('.product-tour.step-3').removeClass('visible');
        $g('.ba-toolbar-icons .settings').removeClass('active-product-tour');
        let sBackdrop = document.querySelector('.saving-backdrop');
        sBackdrop.className += ' animation-out';
        setTimeout(function(){
            document.body.removeChild(sBackdrop);
        }, 300);
    });

    $g('.ba-context-menu .rename').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        if (currentContext.parent().hasClass('ba-category')) {
            oldName = currentContext.find('> span').text().trim();
        } else {
            oldName = currentContext.text().trim();
        }
        $g('.new-name').val(oldName);
        $g('#apply-rename').removeClass('active-button');
        $g('#rename-modal').modal();
    });

    $g('table.ba-items-list').on('contextmenu', 'tr', function(event){
        currentContext = $g(this);
        showContext(event);
    });

    $g('ul.root-list').on('contextmenu', 'a', function(event){
        currentContext = $g(this);
        showContext(event);
    });

    $g('.ba-sorting-action-wrapper ul li').on('click', function(){
        var value = $g(this).attr('data-value');
        if (currentCat) {
            if (value == 'name') {
                catImages[currentCat].sort(sortName);
            } else if (value == 'newest') {
                catImages[currentCat].sort(sortDate);
                catImages[currentCat].reverse();
            } else if (value == 'oldest') {
                catImages[currentCat].sort(sortDate);
            }
            var tbody = $g('table.ba-items-table tbody').empty();
            catImages[currentCat].forEach(function(el, ind){
                var str = returnTrHtml(el, ind);
                tbody.append(str);
                tbody.find('.select-item').last().val(JSON.stringify(el))
            });
            getAllImages();
        }
        $g('#jform_sorting_mode').val(value);
    });

    $g('#check-all').on('click', function(){
        if ($g(this).prop('checked')) {
            var tr = $g('tr');
            if (albumMode) {
                tr = $g('tr').not('.category-all');
            }
            tr.find('input[type="checkbox"]').each(function(){
                $g(this).prop('checked', true);
                $g('i.delete-selected, i.filename-to-title').removeClass('disabled-item');
                if (!albumMode && currentCat == 'root') {

                } else {
                    $g('i.move-to').removeClass('disabled-item');
                }
            });
        } else {
            $g('tr input[type="checkbox"]').each(function(){
                $g(this).prop('checked', false);
                $g('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
            });
        }
    });

    $g('table.ba-items-list ').on('change', 'input[type="checkbox"]', function(){
        if ($g(this).prop('checked')) {
            $g('i.move-to, i.delete-selected, i.filename-to-title').removeClass('disabled-item');
        } else {
            var flag = true;
            $g('tr input[type="checkbox"]').each(function(){
                if ($g(this).prop('checked')) {
                    flag = false;
                }
            });
            if (flag) {
                $g('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
            }
        }
    });

    $g('i.move-to').on('click', function(){
        if ($g(this).hasClass('disabled-item')) {
            return false;
        }
        contextMode = false;
        $g('#move-to-modal .availible-folders .active').removeClass('active');
        $g('#move-to-modal .active-button').removeClass('active-button');
        $g('#move-to-modal').modal();
    });

    $g('#move-to-modal .availible-folders').on('click', 'li', function(event){
        event.stopPropagation();
        $g('#move-to-modal .availible-folders .active').removeClass('active');
        $g(this).addClass('active');
        $g('.apply-move').addClass('active-button');
    });

    $g('#move-to-modal').on('hide', function(){
        $g('#move-to-modal .availible-folders > ul > li > ul').remove();
    });

    $g('#move-to-modal').on('show', function(){
        $g('#move-to-modal .availible-folders > ul > li > ul').remove();
        var ul = $g('div.category-list ul.root-list').clone(),
            li,
            text;
        ul.find('.active').removeClass('active');
        ul.find('li').each(function(){
            li = $g(this);
            var span = document.createElement('span'),
                i = document.createElement('i');
            i.className = 'zmdi zmdi-folder';
            text = li.find('> a').text().trim();
            text = document.createTextNode(text);
            span.appendChild(i);
            span.appendChild(text);
            li.find('> a').remove();
            if (li.attr('id') == 'category-all') {
                li.hide();
            }
            li.attr('data-id', li.attr('id')).removeAttr('id').removeClass('ba-category').prepend(span);
        });
        if (!contextMode) {
            $g('table.ba-category-table input[type="checkbox"]').each(function(){
                var $this = $g(this),
                    id = $this.closest('tr').attr('data-id');
                if ($this.prop('checked')) {
                    ul.find('[data-id="'+id+'"]').hide();
                }
            });
        } else {
            if (!currentContext.hasClass('ba-images')) {
                var id = currentContext.closest('li').attr('id');
                ul.find('[data-id="'+id+'"]').hide();
            }
        }
        $g('#move-to-modal .availible-folders > ul > li').append(ul);
        ul.find('i.zmdi-chevron-right').on('click', function(){
            if ($g(this).parent().hasClass('visible-branch')) {
                $g(this).parent().removeClass('visible-branch');
            } else {
                $g(this).parent().addClass('visible-branch');
            }
        });
    });

    $g('.apply-move').on('click', function(event){
        event.preventDefault();
        var path = $g('#move-to-modal .availible-folders .active').attr('data-id');
        if (!path) {
            return false;
        }
        if (!contextMode) {
            $g('table.ba-items-table input.select-item').each(function(){
                var $this = $g(this),
                    ind,
                    obj;
                if ($this.prop('checked')) {
                    ind = $this.attr('data-index');
                    obj = catImages[currentCat][ind];
                    obj.resave = 1;
                    obj.category = path;
                    delete(catImages[currentCat][ind]);
                    catImages[path].push(obj);
                    $this.closest('tr').remove();
                }
            });
            $g('table.ba-category-table input[type="checkbox"]').each(function(){
                var $this = $g(this),
                    id = $this.closest('tr').attr('data-id'),
                    parent;
                if ($this.prop('checked')) {
                    if (id != path) {
                        if ($g('#'+path).find('> ul').length == 0) {
                            $g('#'+path).append('<i class="zmdi zmdi-chevron-right"></i><ul></ul>');
                        }
                        parent = $g('#'+id).parent();
                        $g('#'+path+'> ul').append($g('#'+id));
                        $this.closest('tr').remove();
                        if (parent.find('li').length == 0) {
                            parent.parent().find('i.zmdi-chevron-right').remove();
                            parent.remove();
                        }
                    }
                }
            });
        } else {
            if (currentContext.hasClass('ba-images')) {
                var ind = currentContext.find('input.select-item').attr('data-index'),
                    obj = catImages[currentCat][ind];
                delete(catImages[currentCat][ind]);
                obj.category = path;
                obj.resave = 1;
                catImages[path].push(obj);
                currentContext.remove();
            } else {
                var id = currentContext.closest('li').attr('id'),
                    parent = $g('#'+id).parent();
                if (id != path) {
                    if ($g('#'+path).find('> ul').length == 0) {
                        $g('#'+path).append('<i class="zmdi zmdi-chevron-right"></i><ul></ul>');
                    }
                    $g('#'+path+'> ul').append($g('#'+id));
                    $g('tr[data-id="'+id+'"]').remove();
                    if (parent.find('li').length == 0) {
                        parent.parent().find('i.zmdi-chevron-right').remove();
                        parent.remove();
                    }
                }
            }
        }
        reIndexArray();
        $g('#'+currentCat).find('> a').trigger('click');
        showNotice(app._('SUCCESS_MOVED'));
        $g('#move-to-modal').modal('hide');
    });

    $g('div.ba-context-menu span.edit-image').on('mousedown', function(event){
        let ind = currentContext.find('input.select-item').attr('data-index')
            obj = $g.extend(true, {}, catImages[currentCat][ind]);
        obj.currentCat = currentCat;
        obj.currentInd = ind;
        itemDelete = obj;
        checkModule('photoEditor');
    });

    $g('div.ba-context-menu span.move-to').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        contextMode = true;
        $g('#move-to-modal .availible-folders .active').removeClass('active');
        $g('#move-to-modal .active-button').removeClass('active-button');
        $g('#move-to-modal').modal();
    });

    function UploadType(client, target)
    {
        setTimeout(function(){
            UpType[0].dataset.target = target;
            if (UpType.hasClass('visible-upload-type')) {
                UpType.addClass('upload-type-out');
                setTimeout(function(){
                    UpType.removeClass('upload-type-out visible-upload-type');
                    showType(client);
                }, 300);
            } else {
                showType(client);
            }
        }, 100);
    }

    function showType(client)
    {
        $g('#select-upload-type').css({
            top : client.top,
            left : client.left,
        }).addClass('visible-upload-type');
    }

    $g(document).on('click', function(){
        if (UpType.hasClass('visible-upload-type')) {
            UpType.addClass('upload-type-out');
            setTimeout(function(){
                UpType.removeClass('upload-type-out visible-upload-type');
            }, 300);
        }
    });

    $g('.ba-context-menu .upload-images').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        if (currentCat && !$g(this).hasClass('disabled-item')) {
            uploadMode = 'images';
            $g('#uploader-modal').attr('data-check', '').modal();
        }
    });

    $g('.camera-container .upload-images').on('mousedown', function(event){
        if (event.button > 1 || this.closest('.active-product-tour')) {
            return false;
        }
        if (currentCat && !this.classList.contains('disabled-item')) {
            uploadMode = 'images';
            let client = this.getBoundingClientRect();
            $g('#uploader-modal').attr('data-check', '');
            createUploadBtn(true);
            UploadType(client, 'images');
        }
    });

    $g('#apply-video').on('click', function(event){
        event.preventDefault();
        if (!this.classList.contains('active-button')) {
            return false;
        }
        let modal = $g('#insert-video-modal'),
            data = {
                id: modal.find('input[data-key="id"]')[0].value,
                type: modal.find('input[data-key="type"]')[0].dataset.value
            }
        app.fetch('index.php?option=com_bagallery&task=uploader.getVideoImage', data).then(function(text){
            let obj = JSON.parse(text);
            if (uploadMode == 'images') {
                let tbody = $g('table.ba-items-table tbody');
                obj.type = 'video';
                obj.video_id = data.id;
                obj.video_type = data.type;
                obj.category = currentCat;
                obj.imageId =  imageId++;
                obj.resave = 1;
                obj.time = +new Date();
                obj.target = 'blank';
                catImages[currentCat].push(obj);
                if (tbody.find('tr').length < pagLimit) {
                    let ind = catImages[currentCat].length - 1,
                        str = returnTrHtml(obj, ind);
                    tbody.append(str);
                    tbody.find('.select-item').last().val(JSON.stringify(obj));
                }
                drawPaginator();
                getAllImages();
            } else if (uploadMode == 'reselectImage') {
                let object = JSON.parse(currentItem.find('.select-item').val());
                object.type = 'video';
                object.video_id = data.id;
                object.video_type = data.type;
                object.name = obj.name;
                object.path = obj.path;
                object.size = obj.size;
                object.thumbnail_url = '';
                object.url = obj.url;
                object.resave = 1;
                currentItem.find('img')[0].src = JUri+obj.url;
                currentItem.find('.select-td').next().text(obj.name).next().text(getFileSize(obj.size));
                obj.url = obj.url.replace(/\s/g, '%20');
                $g('div.images-options div.img-thumbnail').css('background-image', 'url('+JUri+obj.url+')');
                saveImg(object);
            }
        });
        modal.modal('hide');
    });

    $g('#insert-video-modal input[data-key="id"]').on('input', function(){
        let $this = this,
            btn = $g('#apply-video');
        clearTimeout(this.delay);
        this.delay = setTimeout(function(){
            btn[$this.value.trim() ? 'addClass' : 'removeClass']('active-button');
        }, 300);
    })

    $g('.upload-type.video').on('click', function(){
        $g('#insert-video-modal').modal().find('input').each(function(){
            this.dataset.key == 'type' ? this.value = 'Youtube' : this.value = '';
            this.dataset.key == 'type' ? this.dataset.value = 'youtube' : '';
        });
        document.querySelector('#apply-video').classList.remove('active-button');
        $g('.upload-type').trigger('mouseleave');
    });

    $g('#insert-video-modal .ba-custom-select').on('show', function(){
        var $this = $g(this),
            value = $this.find('input[type="text"]')[0].dataset.value;
        $this.find('ul i').remove();
        $this.find('.selected').removeClass('selected');
        $this.find('li[data-value="'+value+'"]').addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
    });

    $g('.upload-type.folder').on('click', function(){
        $g('#uploader-modal').modal();
        $g('.upload-type').trigger('mouseleave');
    });

    $g('.upload-type.desktop').on('click', function(){
        document.querySelector('#file-upload-form [type="file"]').click();
        $g('.upload-type').trigger('mouseleave');
    });

    $g('#file-upload-form').off('change').on('change', function(event){
        let btn = event.target.closest('input[type="file"]');
        if (!btn) {
            return;
        }
        let files = [],
            str = app._('UPLOADING_PLEASE_WAIT');
        for (let i = 0; i < btn.files.length; i++) {
            let name = btn.files[i].name.split('.'),
                ext = name[name.length - 1].toLowerCase();
            if (app.types.indexOf(ext) != -1) {
                files.push(btn.files[i]);
            }
        }
        if (files.length > 0) {
            str += ' <span class="upload-image-count">0</span> / '+files.length;
            str +='</span><img src="'+JUri+'administrator/components/com_bagallery/assets/images/reload.svg"></img>'
            notification.querySelector('p').innerHTML = str;
            notification.className = 'notification-in';
            uploadFiles(files);
        }
    });

    function createUploadBtn(multiple)
    {
        let form = document.querySelector('#file-upload-form form');
        if (form) {
            form.querySelector('[type="file"]').remove();
            form.innerHTML = '<input type="file" '+(multiple ? 'multiple':'')+'>';
        }
    }

    function uploadFiles(files)
    {
        if (files.length > 0) {
            app.fetch('index.php?option=com_bagallery&task=uploader.uploadOriginal', {
                file: files.pop()
            }).then(function(text){
                let obj = JSON.parse(text);
                    count = notification.querySelector('.upload-image-count'),
                    n = count.textContent * 1;
                count.textContent = ++n;
                uploadFiles(files);
                if (uploadMode == 'images') {
                    let tbody = $g('table.ba-items-table tbody');
                    obj.category = currentCat;
                    obj.imageId = imageId++;
                    obj.resave = 1;
                    obj.time = +new Date();
                    obj.target = 'blank';
                    catImages[currentCat].push(obj);
                    if (tbody.find('tr').length < pagLimit) {
                        var ind = catImages[currentCat].length - 1,
                            str = returnTrHtml(obj, ind);
                        tbody.append(str);
                        tbody.find('.select-item').last().val(JSON.stringify(obj));
                    }
                    drawPaginator();
                    getAllImages();
                } else if (uploadMode == 'reselectImage') {
                    let data = JSON.parse(currentItem.find('.select-item').val());
                    if (data.type) {
                        delete data.type;
                        delete data.video_id;
                        delete data.video_type;
                    }
                    data.name = obj.name;
                    data.path = obj.path;
                    data.size = obj.size;
                    data.thumbnail_url = '';
                    data.url = obj.url;
                    data.resave = 1;
                    currentItem.find('img')[0].src = JUri+obj.url;
                    currentItem.find('.select-td').next().text(obj.name).next().text(getFileSize(obj.size));
                    obj.url = obj.url.replace(/\s/g, '%20');
                    $g('div.images-options div.img-thumbnail').css('background-image', 'url('+JUri+obj.url+')');
                    saveImg(data);
                } else if (uploadMode == 'album') {
                    setCategoryValue(currentItem[0], obj.path, 'image');
                    $g('#category-options div.img-thumbnail img').remove();
                    obj.url = obj.url.replace(/\s/g, '%20');
                    $g('#category-options div.img-thumbnail').css('background-image', 'url('+JUri+obj.url+')');
                } else if (uploadMode == 'watermark') {
                    $g('#jform_watermark_upload').val(obj.path);
                    watermark = true;
                }
            });
        } else {
            showNotice(app._('SUCCESS_UPLOAD'));
        }
    }

    $g('body').on('mousedown', function(){
        $g('.context-active').removeClass('context-active');
        $g('.ba-context-menu').hide();
    });

    $g('.ba-custom-select').not('.aspect-ratio-select').find('> i, > span > i, input').on('click', function(event){
        event.stopPropagation();
        var $this = $g(this),
            parent = $this.closest('.ba-custom-select');
        parent.find('ul').addClass('visible-select');
        parent.find('li').off('click.custom-select').one('click.custom-select', function(){
            if (!$g(this).hasClass('disabled-hover-effect')) {
                var text = this.textContent.trim(),
                    val = this.dataset.value;
                parent.find('input').val(text).attr('data-value', val);
                parent.trigger('customHide');
            }
        });
        parent.trigger('show');
        setTimeout(function(){
            $g('body').one('click', function(){
                $g('.visible-select').removeClass('visible-select');
            });
        }, 50);
    });

    $g('.aspect-ratio-select').find(' > i, input').on('click', function(event){
        var $this = $g(this),
            parent = $this.parent();
        if (!parent.find('ul').hasClass('visible-select')) {
            event.stopPropagation();
            $g('.visible-select').removeClass('visible-select');
            parent.find('ul').addClass('visible-select');
            parent.find('li').off('click').one('click', function(){
                var text = this.textContent.trim(),
                    val = this.dataset.value;
                parent.find('input[type="text"]').val(text);
                parent.find('input[type="hidden"]').val(val).trigger('change');
                parent.trigger('customAction');
            });
            parent.trigger('show');
            setTimeout(function(){
                $g('body').one('click', function(){
                    $g('.visible-select').parent().trigger('customHide');
                    $g('.visible-select').removeClass('visible-select');
                });
            }, 50);
        }
    });

    $g('.create-categery').on('mousedown', function(event){
        if (event.button > 1 || $g(this).hasClass('active-product-tour')) {
            return false;
        }
        event.preventDefault();
        $g('input.category-name').val('');
        $g('#create-new-category').removeClass('active-button');
        $g('#create-category-modal').modal();
    });

    $g('#create-new-category').on('click', function(event){
        event.preventDefault();
        if (!this.classList.contains('active-button')) {
            return;
        }
        var category = {
                access: 1,
                title: $g('input.category-name').val().trim(),
                alias: '',
                published: 1,
                default: 0,
                category_id: categoryId++,
                category_all: 0,
                description: '',
                image: ''
            };
        str = getCategoryHTML(category, window.categories.length);
        window.categories.push(category)
        catImages['category-'+category.category_id] = [];
        if (!albumMode || currentCat == 'root' || !currentCat) {
            target.append(str);
        } else {
            if ($g('#'+currentCat).find('> ul').length == 0) {
                $g('#'+currentCat).append('<i class="zmdi zmdi-chevron-right"></i><ul></ul>');
            }
            $g('#'+currentCat).find('> ul').append(str);
        }
        if ((albumMode && currentCat) || currentCat == 'root') {
            str = `
            <tr data-id="category-${category.category_id}">
                <td class="select-td">
                    <input type="checkbox">
                    <div class="folder-icons">
                        <a class="zmdi zmdi-folder"></a>
                        <i class="zmdi zmdi-circle-o"></i>
                        <i class="zmdi zmdi-check"></i>
                    </div>
                </td>
                <td class="draggable-handler">
                    <a>${category.title}</a>
                </td>
                <td class="draggable-handler"></td>
            </tr>`;
            $g('table.ba-category-table tbody').append(str);
        }
        $g('#category-'+category.category_id+' > a').trigger('click');
        showNotice(app._('CATEGORY_IS_CREATED'));
        $g('#create-category-modal').modal('hide');
    });

    $g('input.category-name').on('input', function(){
        if (this.value.trim()) {
            $g('#create-new-category').addClass('active-button');
        } else {
            $g('#create-new-category').removeClass('active-button');
        }
    });

    $g('label.settings').on('click', function(){
        if (!$g(this).hasClass('active-product-tour')) {
            $g('#global-options').modal()
        }
    });

    target.on('click', 'i.zmdi-chevron-right', function(){
        if ($g(this).closest('li').hasClass('visible-branch')) {
            $g(this).closest('li').removeClass('visible-branch');
        } else {
            $g(this).closest('li').addClass('visible-branch');
        }
    });

    $g('li.root > a').on('click', function(){
        $g('#check-all').prop('checked', false);
        $g('div.gallery-options > div').hide();
        $g('').hide();
        target.find('.active').removeClass('active');
        $g(this).closest('li').addClass('active');
        var tbody = $g('table.ba-items-table tbody').empty();
        $g('table.ba-category-table tbody').empty();
        $g('div.gallery-options > img').show();
        if (!albumMode) {
            $g('.upload-images').addClass('disabled-item').parent().addClass('disabled-item');
        } else {
            $g('.upload-images').removeClass('disabled-item').parent().removeClass('disabled-item');
        }
        currentCat = 'root';
        $g('li.root > ul > li').each(function(){
            var $this = $g(this);
                str = '<tr data-id="'+$this.attr('id')+'"';
                if ($this.attr('id') == 'category-all') {
                    str += ' class="category-all"';
                }
                str += '><td class="select-td">';
                str += '<input type="checkbox"><div class="folder-icons">';
                str += '<a class="zmdi zmdi-folder"></a><i class="zmdi zmdi-circle';
                str += '-o"></i><i class="zmdi zmdi-check"></i></div></td>';
                str += '<td class="draggable-handler"><a>';
                str += $this.find('> a span').text();
                str += '</a></td><td class="draggable-handler"></td></tr>';
                $g('table.ba-category-table tbody').append(str);
        });
        catImages['root'].forEach(function(el, ind){
            var str = returnTrHtml(el, ind);
            tbody.append(str);
            tbody.find('.select-item').last().val(JSON.stringify(el))
        });
        drawPaginator();
        getAllImages();
    });

    $g('table.ba-category-table').on('click', 'div.folder-icons', function(){
        var checkbox = $g(this).closest('td.select-td').find('input[type="checkbox"]');
        if (checkbox.prop('checked')) {
            checkbox.prop('checked', false);
            var flag = true;
            $g('tr input[type="checkbox"]').each(function(){
                if ($g(this).prop('checked')) {
                    flag = false;
                }
            });
            if (flag) {
                $g('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
            }
        } else {
            checkbox.prop('checked', true);
            $g('i.delete-selected, i.filename-to-title').removeClass('disabled-item');
            if (albumMode) {
                $g('i.move-to').addClass('disabled-item');
            }
        }
    });

    $g('table.ba-category-table').on('click', 'a', function(event){
        event.preventDefault();
        var id = $g(this).closest('tr').attr('data-id');
        $g('#'+id+'> a').trigger('click');
    });

    target.on('click', 'a', function(event){
        event.stopPropagation();
        var li = this.closest('li'),
            obj = window.categories[li.dataset.ind],
            id = li.id,
            value = '',
            tbody = $g('table.ba-items-table tbody').empty(),
            catTable = $g('table.ba-category-table tbody').empty(),
            parent = $g(this).closest('li').parent();
        if (!parent.hasClass('root-list')) {
            parent.parentsUntil('ul.root-list').each(function(){
                $g(this).addClass('visible-branch');
            })
        }
        $g('#check-all').prop('checked', false);
        $g('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
        $g('div.category-list li.active').removeClass('active');
        li.classList.add('active');
        currentItem = $g(this).closest('li');
        $g('#category-options div.img-thumbnail').hide();
        $g('#access')[0].dataset.value = obj.access;
        value = $g('.access-select li[data-value="'+obj.access+'"]').text().trim();
        $g('#access').val(value);
        if (id != 'category-all') {
            if (albumMode) {
                $g('#category-options div.img-thumbnail').show();
            }
            if (!obj.image) {
                obj.image = '/components/com_bagallery/assets/images/gallery-logo-category.svg';
                $g('#category-options div.img-thumbnail').css('background-image', '');
            }
            $g('#category-options div.img-thumbnail img').remove();
            obj.image = obj.image.replace(/\s/g, '%20');
            $g('#category-options div.img-thumbnail').css('background-image', 'url('+JUri+obj.image+')');
        }
        $g('div.gallery-options > img').hide();
        $g('i.add-link').parent().hide();
        $g('i.add-embed-code').parent().hide();
        $g('#category-name').val(obj.title);
        obj.alias = obj.alias ?? obj.title.toLowerCase().replace(/\s/g, '-')
        $g('input.category-alias').val(obj.alias);
        $g('#category-options, div.gallery-options div.gallery-header').show();
        $g('div.images-options').hide();
        $g('.default-category').prop('checked', obj.default == 1);
        $g('.unpublish-category').prop('checked', obj.published == 0);
        
        if (id != 'category-all') {
            $g('.category-password').parent().css('display', '').prev().css('display', '');
            $g('.category-password').val(obj.password ?? '');
            if (obj.password) {
                $g('.default-category').closest('div').hide();
            } else if (!albumMode) {
                $g('.default-category').closest('div').css('display', '');
            }
            if (obj.default == 1) {
                $g('.category-password').parent().hide().prev().hide();
            } else {
                $g('.category-password').parent().css('display', '').prev().css('display', '');
            }
            currentCat = id;
            catImages[id].forEach(function(el, ind){
                if (ind >= pagLimit && pagLimit != 1) {
                    return false;
                }
                tbody.append(returnTrHtml(el, ind));
                tbody.find('.select-item').last().val(JSON.stringify(el))
            });
            drawPaginator();
            getAllImages();
            $g(this).closest('li').find('> ul > li').each(function(){
                var $this = $g(this);
                str = '<tr data-id="'+$this.attr('id')+'"><td class="select-td">';
                str += '<input type="checkbox"><div class="folder-icons">';
                str += '<a class="zmdi zmdi-folder"></a><i class="zmdi zmdi-circle';
                str += '-o"></i><i class="zmdi zmdi-check"></i></div></td>';
                str += '<td class="draggable-handler"><a>';
                str += $this.find('> a span').text();
                str += '</a></td><td class="draggable-handler"></td></tr>';
                catTable.append(str);
            });
        } else {
            $g('.category-password').parent().hide().prev().hide();
            currentCat = '';
            $g('div.table-body div.pagination').remove();
        }
        if (currentCat) {
            $g('.upload-images').removeClass('disabled-item').parent().removeClass('disabled-item');
        } else {
            $g('.upload-images').addClass('disabled-item').parent().addClass('disabled-item');
        }
    });

    $g('div.images-options div.img-thumbnail .camera-container').on('click', function(){
        let client = this.querySelector('i').getBoundingClientRect();
        createUploadBtn();
        UploadType(client, 'images');
        $g('#uploader-modal').attr('data-check', 'single');
        uploadMode = 'reselectImage';
    });

    $g('.alternative-image').on('click', function(){
        $g('#uploader-modal').attr('data-check', 'single').modal();
        uploadMode = 'alternativeImage';
    });

    $g('.delete-alternative-image').on('click', function(){
        var obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        obj.alternative = '';
        obj.resave = 1;
        $g('.alternative-image').val('');
        saveImg(obj);
    });

    $g('.image-suffix').on('input', function(){
        var obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        obj.suffix = this.value.trim();
        obj.resave = 1;
        saveImg(obj);
    });

    $g('#category-options div.img-thumbnail .camera-container').on('click', function(){
        let client = this.querySelector('i').getBoundingClientRect();
        createUploadBtn()
        UploadType(client, 'category');
        $g('#uploader-modal').attr('data-check', 'single');
        uploadMode = 'album';
    });

    $g('#category-name').on('input', function(){
        const value = this.value.trim();
        setCategoryValue(currentItem[0], value, 'title');
        currentItem.find('> a span').text(value);
    });

    $g('.category-alias').on('input', function(){
        const value = this.value.toLowerCase().replace(/\s/g, '-');
        setCategoryValue(currentItem[0], value, 'alias');
    });

    $g('.category-password').on('input', function(){
        const value = this.value.trim();
        setCategoryValue(currentItem[0], value, 'password');
        $g('.default-category').closest('div').css('display', value ? 'none' : '');
    });

    $g('.hidden-password').on('click', function(){
        this.style.display = 'none';
        $g('.visible-password').css('display', '');
        $g('.category-password').attr('type', 'text');
    });

    $g('.visible-password').on('click', function(){
        this.style.display = 'none';
        $g('.hidden-password').css('display', '');
        $g('.category-password').attr('type', 'password');
    });

    $g('.access-select').on('customHide', function(){
        const value = $g('#access')[0].dataset.value;
        setCategoryValue(currentItem[0], value, 'access');
    }).on('show', function(){
        var value = $g(this).find('#access').attr('data-value');
        $g(this).find('li').each(function(){
            var $this = $g(this).removeClass('selected');
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == value) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
    });

    $g('.default-category').on('change', function(){
        let obj = getCategoryObject(currentItem[0]),
            all = getCategoryObject($g('#category-all')[0]);
        if (!this.checked && all.published == 0) {
            this.checked = true;
            $g('#message-dialog').modal();
            $g('#message-dialog .cannot-unpublish').show();
            $g('#message-dialog .cannot-default').hide();
            return false;
        }
        if (!this.checked && obj.category_all == 1) {
            this.checked = true;
            return false;
        }
        if (obj.published != 0 && currentItem.closest('li.ba-unpublish').length == 0) {
            $g('.ba-category').each(function(){
                let object = getCategoryObject(this);
                object.default = 0;
                $g(this).find('> a > .zmdi-star').remove();
            });
            if (!this.checked) {
                obj.default = 0;
                currentItem.find('> a > .zmdi-star').remove();
                checkDefault();
            } else {
                obj.default = 1;
                currentItem.find('> a').append('<i class="zmdi zmdi-star"></i>');
            }
            console.info(obj.default)
            if (obj.default == 1) {
                $g('.category-password').parent().hide().prev().hide();
            } else {
                $g('.category-password').parent().css('display', '').prev().css('display', '');
            }
        } else {
            this.checked = false;
            $g('#message-dialog').modal();
            $g('#message-dialog .cannot-unpublish').hide();
            $g('#message-dialog .cannot-default').show();
        }
    });

    $g('.unpublish-category').on('click', function(){
        let obj = getCategoryObject(currentItem[0]);
        if (obj.default != 1 && currentItem.find('.zmdi.zmdi-star').length == 0) {
            obj.published = Number(!obj.published);
            currentItem[obj.published ? 'removeClass' : 'addClass']('ba-unpublish');
        } else {
            this.checked = false;
            $g('#message-dialog').modal();
            $g('#message-dialog .cannot-unpublish').show();
            $g('#message-dialog .cannot-default').hide();
        }
    });

    $g('.general-tabs ul.uploader-nav').on('show', function(event){
        var ind = [],
            id = $g(event.relatedTarget).attr('href'),
            aId = $g(event.target).attr('href');
        $g(this).find('li a').each(function(i){
            if (this == event.target) {
                ind[0] = i;
            }
            if (this == event.relatedTarget) {
                ind[1] = i;
            }
        });
        if (ind[0] > ind[1]) {
            $g(id).addClass('out-left');
            $g(aId).addClass('right');
            setTimeout(function(){
                $g(id).removeClass('out-left');
                $g(aId).removeClass('right');
            }, 500);
        } else {
            $g(id).addClass('out-right');
            $g(aId).addClass('left');
            setTimeout(function(){
                $g(id).removeClass('out-right');
                $g(aId).removeClass('left');
            }, 500);
        }
    }).on('shown', function(event){
        setTabsUnderline();
    });

    $g('#global-options').one('shown', function(){
        setTabsUnderline();
    });

    $g('#photo-editor-dialog').on('shown', function(){
        setTabsUnderline();
    });

    $g('input.link-target+i+ul li').on('click', function(){
        $g('input.link-target+i+ul li.selected').removeClass('selected');
        $g('input.link-target+i+ul i.zmdi-check').remove();
        $g(this).addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
    });

    $g('i.add-link').on('click', function(){
        var obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        $g('input.image-link').val(obj.link);
        $g('input.link-target').parent().find('ul li').each(function(){
            var $this = $g(this);
            if ($this.attr('data-value') == obj.target) {
                $g('input.link-target').attr('data-value', $this.attr('data-value')).val($this.text());
                return false;
            }
        });
        $g('input.link-target+i+ul li').each(function(){
            var $this = $g(this).removeClass('selected');
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == obj.target) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
        $g('#add-link-modal').modal();
    });

    $g('.select-link').on('click', function(){
        $g.ajax({
            type: "POST",
            dataType: 'text',
            url: "index.php?option=com_bagallery&task=gallery.getLinksString",
            complete: function(msg){
                $g('.apply-link').removeClass('active-button');
                $g('#link-select-modal .availible-folders').html(msg.responseText);
                if ($g('#menu-item-add-modal').hasClass('in')) {
                    $g('#link-select-modal .availible-folders > ul > li').last().hide();
                }
                $g('#link-select-modal').modal();
            }
        });
    });

    $g('#link-select-modal .availible-folders').on('click', 'i.zmdi-chevron-right', function(event){
        event.stopPropagation();
        if ($g(this).parent().hasClass('visible-branch')) {
            $g(this).parent().removeClass('visible-branch');
        } else {
            $g(this).parent().addClass('visible-branch');
        }
    });

    $g('#link-select-modal .availible-folders').on('click', 'li[data-url]', function(event){
        event.stopPropagation();
        if (this.dataset.url) {
            $g('#link-select-modal .availible-folders .active').removeClass('active');
            this.classList.add('active');
            $g('.apply-link').addClass('active-button');
        }
    });

    $g('.apply-link').on('click', function(){
        if (this.classList.contains('active-button')) {
            $g('.image-link').val($g('#link-select-modal .availible-folders li.active')[0].dataset.url);
            $g('#link-select-modal').modal('hide');
        }
    })

    $g('#add-link').on('click', function(event){
        event.preventDefault();
        var obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        obj.link = $g('input.image-link').val();
        obj.target = $g('input.link-target').attr('data-value');
        obj.resave = 1;
        saveImg(obj);
        $g('#add-link-modal').modal('hide');
    });

    $g('i.add-embed-code').on('click', function(){
        var obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        $g('.ba-embed').val(obj.video)
        $g('#embed-modal').modal();
    });
    
    $g('#embed-apply').on('click', function(){
        var value = $g('.ba-embed').val(),
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        obj.video = value;
        obj.resave = 1;
        saveImg(obj);
        $g('#embed-modal').modal('hide');
    });

    $g('i.edit-description').on('click', function(){
        if (currentItem.hasClass('ba-category')) {
            let obj = getCategoryObject(currentItem[0]);
            obj.description = obj.description ?? '';
            CKE.setData(obj.description);
        } else {
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            CKE.setData(obj.description);
        }
        $g('#html-editor').modal();
    });

    function filenameToTitle(el)
    {
        var name = el.name.split('.'),
            title = '';
        for (var i = 0; i < name.length - 1; i++) {
            title += name[i];
        }
        title = title.replace(/-/g, ' ').replace(/_/g, ' ');
        el.title = el.alt = title;
        el.lightboxUrl = '';
        el.resave = 1;
    }

    $g('i.filename-to-title').on('click', function(){
        if (!this.classList.contains('disabled-item')) {
            if ($g('table.ba-category-table [data-id="category-all"] input[type="checkbox"]').prop('checked')) {
                $g('table.ba-category-table tr:not([data-id="category-all"])').each(function(){
                    catImages[this.dataset.id].forEach(function(el){
                        filenameToTitle(el);
                    });
                });
            } else {
                $g('table.ba-category-table tr input[type="checkbox"]').each(function(){
                    if (!this.checked) {
                        return;
                    }
                    var id = $g(this).closest('tr').attr('data-id');
                    catImages[id].forEach(function(el){
                        filenameToTitle(el);
                    });
                    $g('#'+id+' ul li').each(function(){
                        catImages[this.id].forEach(function(el){
                            filenameToTitle(el);
                        });
                    });
                });
                $g('td.select-td input.select-item').each(function(){
                    if (this.checked) {
                        filenameToTitle(catImages[currentCat][this.dataset.index]);
                        var obj = JSON.stringify(catImages[currentCat][this.dataset.index]);
                        this.value = obj;
                        if ($g(this).closest('tr.ba-images').hasClass('active')) {
                            $g(this).trigger('click');
                        }
                    }
                });
            }
            showNotice(app._('TITLES_MOVED'));
        }
    });

    $g('i.delete-selected').on('click', function(){
        deleteMode = 'array';
        contextMode = false;
        var flag = false;
        $g('table.ba-items-list input[type="checkbox"]').each(function(){
            if ($g(this).prop('checked')) {
                flag = true;
                return false;
            }
        });
        if (flag) {
            if ($g('table.ba-category-table [data-id="category-all"] input[type="checkbox"]').prop('checked')) {
                $g('.cannot-delete').show();
                $g('.can-delete, #delete-dialog h3').hide();
                $g('#apply-delete').addClass('disabled-button');
            } else {
                $g('table.ba-category-table tr input[type="checkbox"]').each(function(){
                    if (!this.checked) {
                        return;
                    }
                    let id = this.closest('tr').dataset.id,
                        obj = getCategoryObject(document.getElementById(id));
                    if (obj.default == 1) {
                        flag = false;
                        return false;
                    }
                });
                if (!flag) {
                    $g('#deafult-message-dialog').modal();
                    return false;
                }
                $g('.cannot-delete').hide();
                $g('.can-delete, #delete-dialog h3').show();
                $g('#apply-delete').removeClass('disabled-button');
            }
            $g('#delete-dialog').modal();
        }
    });

    $g('i.delete-item').on('click', function(){
        deleteMode = 'single';
        contextMode = false;
        if (currentItem.attr('id') == 'category-all') {
            $g('.cannot-delete').show();
            $g('.can-delete, #delete-dialog h3').hide();
            $g('#apply-delete').addClass('disabled-button');
        } else {
            if (currentItem.hasClass('ba-category')) {
                const obj = getCategoryObject(currentItem[0])
                if (obj.default == 1) {
                    $g('#deafult-message-dialog').modal();
                    return false;
                }
            }
            $g('.cannot-delete').hide();
            $g('.can-delete, #delete-dialog h3').show();
            $g('#apply-delete').removeClass('disabled-button');
        }
        $g('#delete-dialog').modal();
    });

    $g('div.ba-context-menu span.delete').on('mousedown', function(event){
        if (event.button > 1) {
            return false;
        }
        contextMode = true;
        if (currentContext.closest('li').attr('id') == 'category-all') {
            $g('.cannot-delete').show();
            $g('.can-delete, #delete-dialog h3').hide();
            $g('#apply-delete').addClass('disabled-button');
        } else {
            if (currentContext.closest('li').hasClass('ba-category')) {
                let obj = getCategoryObject(currentContext.closest('li')[0]);
                if (obj.default == 1) {
                    $g('#deafult-message-dialog').modal();
                    $g('div.ba-context-menu').hide();
                    return false;
                }
            }                
            $g('.cannot-delete').hide();
            $g('.can-delete, #delete-dialog h3').show();
            $g('#apply-delete').removeClass('disabled-button');
        }
        $g('#delete-dialog').modal();
    });

    $g('#apply-delete').on('click', function(event){
        event.preventDefault();
        if ($g(this).hasClass('disabled-button')) {
            return false;
        }
        if (!contextMode) {
            if (deleteMode == 'single') {
                if (currentItem.hasClass('ba-category')) {
                    delete(catImages[currentCat]);
                    $g('table.ba-items-table tbody').empty();
                    $g('.upload-images').addClass('disabled-item').parent().addClass('disabled-item');
                } else {
                    let ind = currentItem.find('.select-item').attr('data-index');
                    delete(catImages[currentCat][ind]);
                }
                var parent = currentItem.parent();
                currentItem.remove();
                if (currentItem.hasClass('ba-category')) {
                    let array = [];
                    $g('.ba-category').each(function(){
                        array.push(window.categories[this.dataset.ind]);
                        this.dataset.ind = array.length - 1;
                    })
                    window.categories = array;
                }
                if (parent[0].localName == 'ul' && parent.find('> li').length == 0) {
                    parent = parent.parent();
                    parent.find('i.zmdi-chevron-right').remove();
                    parent.find('> ul').remove();
                }
            } else {
                $g('td.select-td input.select-item').each(function(){
                    var $this = $g(this);
                    if ($this.prop('checked')) {
                        delete(catImages[currentCat][$this.attr('data-index')]);
                        $this.closest('tr').remove();
                    }
                });
                $g('table.ba-category-table tr input[type="checkbox"]').each(function(){
                    var $this = $g(this),
                        id = $this.closest('tr').attr('data-id'),
                        parent;
                    if ($this.prop('checked')) {
                        delete(catImages[id]);
                        parent = $g('#'+id).parent();
                        $g('#'+id).remove();
                        if (!parent.hasClass('root-list')) {
                            if (parent.find('> li').length == 0) {
                                parent = parent.parent();
                                parent.find('i.zmdi-chevron-right').remove();
                                parent.find('> ul').remove();
                            }
                        }
                        $this.closest('tr').remove();
                    }
                });
            }
        } else {
            if (currentContext.hasClass('ba-images')) {
                var ind = currentContext.find('input.select-item').attr('data-index');
                delete(catImages[currentCat][ind]);
                currentContext.remove();
            } else {
                var li = currentContext.closest('li'),
                    id = li.attr('id'),
                    parent = $g('#'+id).parent();
                $g('tr[data-id="'+id+'"]').remove();
                var parent = li.parent();
                li.remove();
                let array = [];
                $g('.ba-category').each(function(){
                    array.push(window.categories[this.dataset.ind]);
                    this.dataset.ind = array.length - 1;
                })
                window.categories = array;
                if (parent[0].localName == 'ul') {
                    if (parent.find('> li').length == 0) {
                        parent = parent.parent();
                        parent.find('i.zmdi-chevron-right').remove();
                        parent.find('> ul').remove();
                    }
                }
            }
        }
        $g('div.table-body').find('.pagination').remove();
        if (deleteMode != 'single' || !currentItem.hasClass('ba-category')) {
            reIndexArray();
            $g('#'+currentCat).find('> a').trigger('click');
        }
        showNotice(app._('SUCCESS_DELETE'));
        $g('div.gallery-options > div').hide();
        $g('#delete-dialog').modal('hide');
    });

    $g('#apply-html').on('click', function(event){
        event.preventDefault();
        if (currentItem.hasClass('ba-category')) {
            let obj = getCategoryObject(currentItem[0]);
            obj.description = CKE.getData();
        } else {
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            obj.description = CKE.getData();
            obj.resave = 1;
            saveImg(obj);
        }
        $g('#html-editor').modal('hide');
    });

    $g('table.ba-items-table').on('click', 'tr', function(){
        $g('tr.active').removeClass('active');
        $g(this).addClass('active');
        $g('#category-options').hide();
        $g('div.images-options, div.gallery-options div.gallery-header').show();
        $g('i.add-link').parent().show();
        $g('i.add-embed-code').parent().show();
        currentItem = $g(this);
        var obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        if (!obj.tags) {
            obj.tags = [];
        }
        if (!obj.colors) {
            obj.colors = [];
        }
        $g('.picked-tags .tags-chosen').remove();
        $g('select.meta_tags').empty();
        $g('.all-tags li').removeClass('selected-tag');
        $g('.meta-tags .picked-tags .search-tag input').val('');
        $g('.all-tags li').hide();
        obj.tags.forEach(function(el){
            var title = el.title,
                tagId = el.id,
                str = '<li class="tags-chosen"><span>';
            $g('.all-tags li[data-id="'+tagId+'"]').addClass('selected-tag');
            str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
            $g('.picked-tags .search-tag').before(str);
            str = '<option value="'+tagId+'" selected>'+title+'</option>';
            $g('select.meta_tags').append(str);
        });
        $g('.picked-colors .colors-chosen').remove();
        $g('select.image_colors').empty();
        $g('.all-colors li').removeClass('selected-colors');
        $g('.image-colors .picked-colors .search-colors input').val('');
        $g('.all-colors li').hide();
        obj.colors.forEach(function(el){
            var color = rgba2hex(el.title),
                tagId = el.id,
                str = '<li class="colors-chosen" data-value="'+el.title+'"><span><span class="chosen-color" style="background-color: ';
            $g('.all-colors li[data-id="'+tagId+'"]').addClass('selected-colors');
            str += el.title+';"></span><span>'+color[0]+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
            $g('.picked-colors .search-colors').before(str);
            str = '<option value="'+tagId+'" selected>'+el.title+'</option>';
            $g('select.image_colors').append(str);
        });
        $g('div.gallery-options > img').hide();
        obj.url = obj.url.replace(new RegExp(' ', 'g'), '%20');
        $g('div.images-options div.img-thumbnail').css('background-image', 'url('+JUri+obj.url+'?time=1)');
        $g('input.image-title').val(obj.title);
        if (obj.lightboxUrl) {
            $g('input.image-alias').val(obj.lightboxUrl);
        } else {
            if (obj.title) {
                $g('input.image-alias').val(obj.title.toLowerCase().replace(/\s+/g, '-'));
            } else {
                $g('input.image-alias').val('');
            }
        }
        $g('input.image-short').val(obj.short);
        $g('input.image-alt').val(obj.alt);
        $g('.alternative-image').val(obj.alternative ? obj.alternative : '');
        $g('.image-suffix').val(obj.suffix ? obj.suffix : '');
        if (obj.hideInAll && obj.hideInAll == 1) {
            $g('.hide-in-category-all').prop('checked', true);
        } else {
            $g('.hide-in-category-all').prop('checked', false);
        }
    });

    $g('.hide-in-category-all').on('click', function(){
        var value = $g(this).prop('checked') ? 1 : 0,
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        obj.hideInAll = value;
        obj.resave = 1;
        saveImg(obj);
    });

    $g('input.image-title').on('input', function(){
        var value = $g(this).val(),
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        value = strip_tags(value);
        obj.title = value;
        obj.resave = 1;
        saveImg(obj);
    });
    
    $g('input.image-alias').on('input', function(){
        var value = $g(this).val().toLowerCase(),
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        value = strip_tags(value);
        obj.lightboxUrl = value.replace(new RegExp(" ", 'g'), "-");
        obj.resave = 1;
        saveImg(obj);
    });
    
    $g('.image-short').on('input', function(){
        var value = $g(this).val(),
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        value = strip_tags(value);
        obj.short = value;
        obj.resave = 1;
        saveImg(obj);
    });
    
    $g('.image-alt').on('input', function(){
        var value = $g(this).val(),
            obj = currentItem.find('.select-item').val();
        obj = JSON.parse(obj);
        value = strip_tags(value);
        obj.alt = value;
        obj.resave = 1;
        saveImg(obj);
    });

    $g('.meta-tags .picked-tags .search-tag input').on('keydown', function(event){
        var title = $g(this).val().trim().toLowerCase();
        if (event.keyCode == 13) {
            if (!title) {
                this.value = '';
                return false;
            }
            var str = '<li class="tags-chosen"><span>',
                tagId = 'new$'+title;
            $g('.all-tags li').each(function(){
                var search = $g(this).text().trim().toLowerCase();
                if (title == search) {
                    this.classList.add('selected-tag');
                    tagId = this.dataset.id;
                    return false;
                }
            });
            if ($g('.picked-tags .tags-chosen i[data-remove="'+tagId+'"]').length > 0) {
                return false;
            }
            str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
            $g('.picked-tags .search-tag').before(str);
            str = '<option value="'+tagId+'" selected>'+title+'</option>';
            $g('select.meta_tags').append(str);
            $g(this).val('');
            $g('.all-tags li').hide();
            saveTags();
            event.stopPropagation();
            event.preventDefault();
            return false;
        } else {
            $g('.all-tags li').each(function(){
                var search = $g(this).text().trim().toLowerCase();
                if (search.indexOf(title) < 0 || title == '') {
                    $g(this).hide();
                } else {
                    $g(this).show();
                }
            });
        }
    });

    $g('.all-tags').on('click', 'li', function(){
        if (this.classList.contains('selected-tag')) {
            return false;
        }
        var title = $g(this).text().trim(),
            tagId = this.dataset.id;
        var str = '<li class="tags-chosen"><span>';
        str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
        $g('.picked-tags .search-tag').before(str);
        str = '<option value="'+tagId+'" selected>'+title+'</option>';
        $g('select.meta_tags').append(str);
        $g('.meta-tags .picked-tags .search-tag input').val('');
        $g('.all-tags li').hide();
        this.classList.add('selected-tag');
        saveTags();
    });

    $g('.meta-tags .picked-tags').on('click', '.zmdi.zmdi-close', function(){
        var del = this.dataset.remove;
        $g('select.meta_tags option[value="'+del+'"]').remove();
        $g(this).closest('li').remove();
        $g('.all-tags li[data-id="'+del+'"]').removeClass('selected-tag');
        $g('.all-tags li').hide();
        saveTags();
    });

    $g('.image-colors .picked-colors .search-colors input').on('keydown', function(event){
        if (event.keyCode == 13) {
            addColorFilter(this);
            event.stopPropagation();
            event.preventDefault();
            return false;
        }
    });

    $g('.image-colors .picked-colors').on('click', '.zmdi.zmdi-close', function(){
        var del = this.dataset.remove;
        $g('select.image_colors option[value="'+del+'"]').remove();
        $g(this).closest('li').remove();
        $g('.all-colors li[data-id="'+del+'"]').removeClass('selected-colors');
        $g('.all-colors li').hide();
        saveColors();
    });

    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = 'https://ajax.googleapis.com/ajax/libs/jqueryui/1.11.2/jquery-ui.min.js';
    script.onload = function(){
        $g('table.ba-items-table tbody').sortable({
            cancel: null,
            cursorAt: {
                left: 90,
                top: 20
            },
            handle : '.draggable-handler',
            tolerance: 'pointer',
            stop : function(event, ui){
                ui.item.addClass('ba-dropping-helper');
                setTimeout(function(){
                    ui.item.removeClass('ba-dropping-helper');
                }, 500);
                var page = $g('.pagination-list li.ba-pages.active a').attr('data-page');
                if (!page) {
                    page = 0;
                }
                var sItems = $g(this).find('tr input.select-item'),
                    max = page * pagLimit + sItems.length,
                    ind = 0;
                for (var i = page * pagLimit; i < max; i++) {
                    var $this = $g(sItems[ind]);
                    obj = $this.val();
                    $this.attr('data-index', i);
                    obj = JSON.parse(obj);
                    catImages[currentCat][i] = obj;
                    ind++;
                }
            }
        }).disableSelection();

        $g('table.ba-category-table tbody').sortable({
            cancel: null,
            cursorAt: {
                left: 90,
                top: 20
            },
            handle : '.draggable-handler',
            tolerance: 'pointer',
            stop : function(event, ui){
                ui.item.addClass('ba-dropping-helper');
                setTimeout(function(){
                    ui.item.removeClass('ba-dropping-helper');
                }, 500);
                var id,
                    items,
                    ul = document.createElement('ul');
                $g(this).find('tr').each(function(ind, el){
                    id = $g(this).attr('data-id');
                    ul.appendChild($g('#'+id)[0]);
                });
                $g('div.category-list li.active > ul').html(ul.innerHTML)
            }
        }).disableSelection();
    }
    document.head.append(script);

    $g('.ba-tooltip').each(function(){
        $g(this).parent().on('mouseenter', function(){
            if (this.dataset.value == 10 && !this.classList.contains('disabled-hover-effect')) {
                return false;
            }
            var tooltip = $g(this).find('.ba-tooltip'),
                coord = this.getBoundingClientRect(),
                top = coord.top,
                data = tooltip.html(),
                center = (coord.right - coord.left) / 2,
                className = tooltip[0].className;
            center = coord.left + center;
            if (tooltip.hasClass('ba-bottom')) {
                top = coord.bottom;
            }
            $g('body').append('<span class="'+className+'">'+data+'</span>');
            var tooltip = $g('body > .ba-tooltip').last(),
                width = tooltip.outerWidth(),
                height = tooltip.outerHeight();
            if (tooltip.hasClass('ba-top') || tooltip.hasClass('ba-help')) {
                top -= (15 + height);
                center -= (width / 2)
            } else if (tooltip.hasClass('ba-bottom')) {
                top += 10;
                center -= (width / 2)
            } else if (tooltip.hasClass('ba-left')) {
                center -= width + (coord.right - coord.left) / 2;
            }
            var screen = document.documentElement.clientWidth;
            if (center + width > screen) {
                center = coord.right - width;
                tooltip.addClass('offset-left');
            }
            tooltip.css({
                'top' : top+'px',
                'left' : center+'px'
            }).on('mousedown', function(event){
                event.stopPropagation();
            });
        }).on('mouseleave', function(){
            var tooltip = $g('body').find(' > .ba-tooltip');
            tooltip.addClass('tooltip-hidden');
            setTimeout(function(){
                tooltip.remove();
            }, 500);
        });
    });

    $g('#jform_watermark_upload').on('click', function(){
        $g('#uploader-modal').modal().attr('data-check', 'single');
        uploadMode = 'watermark';
    });

    $g('#remove-watermark').on('click', function(event){
        event.preventDefault();
        $g('#jform_watermark_upload').val('');
        watermark = true;
    });

    $g('#jform_watermark_position').prev().on('customHide', function(){
        watermark = true;
    });

    $g('#jform_watermark_opacity').prev().on('change', function(){
        watermark = true;
    });

    $g('#jform_scale_watermark').on('change', function(){
        watermark = true;
    });

    $g('#jform_watermark_opacity').on('input', function(){
        watermark = true;
    });

    $g('.thumbnail-typography-select').parent().on('show', function(){
        var value = $g(this).find('input.thumbnail-typography-select').attr('data-value');
        $g(this).find('li').each(function(){
            var $this = $g(this).removeClass('selected');
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == value) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
    }).on('customHide', function(){
        var value = $g(this).find('input.thumbnail-typography-select').attr('data-value');
        $g(this).closest('.ba-options-group').find('.option-border').hide();
        $g(this).closest('.ba-options-group').find('div.'+value+'-options').show();
    });

    checkRandomGrid();
    checkAlbumRandomGrid()

    $g('#jform_gallery_layout').prev().on('customHide', function(){
        setTimeout(function(){
           checkRandomGrid();
        }, 100);
    });

    $g('#jform_album_layout').prev().on('customHide', function(){
        setTimeout(function(){
           checkAlbumRandomGrid();
        }, 100);
    });

    checkEffect();
    checkAlbumEffect();

    $g('#jform_thumbnail_layout').prev().on('customHide', function(){
        setTimeout(function(){
           checkEffect();
        }, 100);
    });

    $g('#jform_album_thumbnail_layout').prev().on('customHide', function(){
        setTimeout(function(){
           checkAlbumEffect();
        }, 100);
    });

    $g('.ba-form-trigger').parent().each(function(){
        var $this = $g(this),
            val = $g(this).next().val(),
            value;
        $this.find('ul li').each(function(){
            value = $g(this).attr('data-value')
            if (value == val) {
                $this.find('input').attr('data-value', value).val($g(this).text());
                return false;
            }
        });
    }).on('show', function(){
        var value = $g(this).find('input').attr('data-value');
        $g(this).find('li').each(function(){
            var $this = $g(this).removeClass('selected');
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == value) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
    }).on('customHide', function(){
        var $this = $g(this),
            value = $this.find('input').attr('data-value');
        $this.next().val(value);
    });

    $g('#filter-options .ba-custom-select, .cke-image-select').on('show', function(){
        var value = $g(this).find('input[data-value]').attr('data-value');
        $g(this).find('li').each(function(){
            var $this = $g(this).removeClass('selected');
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == value) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
    });

    $g('.ba-gallery-range').each(function(){
        var input = $g(this),
            max = input.attr('max') * 1,
            min = input.attr('min') * 1,
            number = input.next();
        input.val(number.val());
        addRangeWidth(input);
        number.on('input', function(){
            var value = this.value * 1;
            if (!this.value) {
                value = 0
            }
            if (max && value > max) {
                this.value = value = max;
            }
            if (min && value < min) {
                value = min;
            }
            input.val(value);
            addRangeWidth(input);
        })
    });
    $g('.ba-gallery-range').on('mousedown', function(){
        var input = $g(this),
            interval = setInterval(function(){
                addRangeWidth(input);
                input.next().val(input.val());
        }, 20);
        $g(this).one('mouseup', function(){
            clearInterval(interval);
        });
    });

    $g('.ba-gallery-settings-toolbar label[data-trigger]').each(function(){
        let active = this.dataset.value == document.querySelector('#jform_'+this.dataset.option).value;
        this.classList[active ? 'add' : 'remove']('active');
    });

    if ($g('#jform_disable_lightbox').val() == 0) {
        $g('#enable-lightbox').prop('checked', true);
    }

    $g('#enable-lightbox').on('click', function(){
        if (this.checked) {
            $g('#jform_disable_lightbox').val(0);
        } else {
            $g('#jform_disable_lightbox').val(1);
        }
        checkLightbox();
    });

    $g('#jform_enable_alias').on('change', checkAlias);

    $g('#lightbox-comments-options .ba-custom-select').on('customHide', function(){
        checkComments();
    });

    $g('#jform_disable_caption').on('click', function(){
        checkCaptionOptions();
    });

    $g('#jform_album_disable_caption').on('click', function(){
        checkAlbumCaptionOptions();
    });

    $g('#jform_category_list').on('click', function(){
        checkFilter();
    });

    $g('#jform_enable_tags').on('click', function(){
        checkTags();
    });

    $g('#jform_enable_colors').on('click', function(){
        checkColors();
    });

    $g('#jform_pagination').on('click', function(){
        checkPaginator();
    });

    $g('.ba-gallery-settings-toolbar label').on('click', function(){
        let value = this.dataset.value;
        if (value == 'bold' || value == '1') {
            this.classList.toggle('active');
        } else {
            $g(this).closest('div').find('.active').removeClass('active');
            this.classList.add('active');
        }
        if (value == '1' && !this.classList.contains('active')) {
            value = '0'
        } else if (value == 'bold' && !this.classList.contains('active')) {
            value = 'normal'
        }
        if (this.dataset.option == 'device') {
            let group = $g(this).closest('.ba-options-group');
            group.find('.option-border').hide();
            group.find('.'+value+'-options').show();
        } else if (this.dataset.trigger == '1') {
            this.closest('div').querySelector('input#jform_'+this.dataset.option).value = value;
        }
        if (this.dataset.equal) {
            let equal = this.dataset.equal;
            $g('.ba-gallery-settings-toolbar label[data-option="'+equal+'"]').removeClass('active');
            $g('.ba-gallery-settings-toolbar label[data-option="'+equal+'"][data-value="'+value+'"]').addClass('active');
            document.querySelector('#jform_'+equal).value = value;
        }
    });

    $g('#jform_auto_resize').on('click', function(){
        checkAutoResize();
    });

    $g('#jform_display_header').on('click', function(){
        checkHeader();
    });

    $g('.pagination-limit .ba-custom-select').on('show', function(){
        var value = $g(this).find('input').attr('data-value');
        $g(this).find('li').each(function(){
            var $this = $g(this).removeClass('selected');
            $this.find('i.zmdi-check').remove();
            if ($this.attr('data-value') == value) {
                $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
            }
        });
    }).on('customHide', function(){
        pagLimit = $g(this).find('input').attr('data-value') * 1;
        $g.ajax({
            type : "POST",
            dataType : 'text',
            url : "index.php?option=com_bagallery&task=gallery.setPagLimit&tmpl=component",
            data : {
                key : 'bagallery-edit',
                value : pagLimit
            }
        });
        var tbody = $g('table.ba-items-table tbody').empty();
        if (currentCat) {
            catImages[currentCat].forEach(function(el, ind){
                if (ind >= pagLimit && pagLimit != 1) {
                    return false;
                }
                var str = returnTrHtml(el, ind);
                tbody.append(str);
                tbody.find('.select-item').last().val(JSON.stringify(el))
            });
            drawPaginator();
            getAllImages();
        }
    });

    checkHeader();
    checkLightbox();
    checkAutoResize();
    checkFilter();
    checkTags();
    checkColors();
    checkPaginator();
    checkCaptionOptions();
    checkAlbumCaptionOptions();
    checkAlias();
})