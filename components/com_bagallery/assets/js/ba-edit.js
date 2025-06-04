/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/
if (!window.Joomla) {
    var Joomla = {
        submitbutton: function(task) {
            return false;
        }
    };
}

var $g = window.jQuery ? window.jQuery : null,
    categoryId = 1,
    imageId = 1,
    catImages = [],
    allImages = [];

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

(function($){
    $(document).ready(function(){

        if (!$g) {
            $g = window.jQuery;
        }

        Joomla.submitbutton = function(task) {
            Joomla.submitbutton = function(task) {
                return false;
            }
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
                watermarkNames = [],
                thumbnailArray = {},
                id = $('#jform_id').val(),
                allThumb = [],
                allCat = [];
            $('.category-list li').each(function(){
                var key = $(this).attr('id');
                if (catImages[key]) {
                    catImages[key].forEach(function(el, ind){
                        if ($.inArray(el.category, allCat) == -1) {
                            allCat.push(el.category);
                        }
                        if (el.thumbnail_url) {
                            var thumb = el.thumbnail_url.split('/');
                            if (!thumbnailArray[el.category]) {
                                thumbnailArray[el.category] = [];
                            }
                            thumbnailArray[el.category].push(thumb[thumb.length - 1]);
                        }
                        if (el.watermark_name) {
                            watermarkNames.push(el.watermark_name);
                        }
                        array.push(el);
                        sort.push(el.imageId);
                    });
                }
            });
            allThumb = JSON.stringify(thumbnailArray);
            sort = sort.join('-_-');
            $g('#jform_settings').val(sort);
            $('#jform_gallery_items').val('');
            $('#jform_all_sorting').val('');
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
                    el.watermark_name = el.name;
                    watermarkNames.push(el.watermark_name);
                }
            });
            var upload = $g('#saving-media').val(),
                str = '';
            upload = JSON.parse(upload);
            str += upload.const+'<img src="'+upload.url;
            str += 'components/com_bagallery/assets/images/reload.svg"></img>';
            notification.className = 'notification-in';
            notification.children[0].innerHTML = str;
            $('<div/>', {
                'class' : 'saving-backdrop'
            }).appendTo('body');
            clearTimeout(animationOut);
            saveData(array, allThumb, allCat, id, task, items);
        }
        Joomla.submitform = function(task, form){
            form.task.value = task;
            var data = new FormData(form);
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : JUri+"index.php?option=com_bagallery&task=gallery.save",
                data : data,
                complete:function(msg){
                    window.location.href = window.location.href;
                },
                processData: false,
                contentType: false
            });
        }

        setInterval(function(){
            $.ajax({
                type : "POST",
                dataType : 'text',
                url : JUri+"index.php?option=com_bagallery&task=gallery.getSession&tmpl=component",
                success : function(msg){
                }
            });
        }, 600000);

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
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : JUri+"index.php?option=com_bagallery&task=gallery.saveItems&tmpl=component",
                    data : {
                        gallery_items : imgArray,
                        ba_id : id
                    },
                    error: function(msg){
                        console.info(msg.responseText)
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
                $.ajax({
                    type : "POST",
                    dataType : 'text',
                    url : JUri+"index.php?option=com_bagallery&task=gallery.clearOld&tmpl=component",
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
            if ($.inArray(name, array) > -1) {
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
                url : JUri+"index.php?option=com_bagallery&task=gallery.saveItems&tmpl=component",
                data : {
                    gallery_items : array,
                    settings : $('#jform_settings').val(),
                    all_sorting : $('#jform_all_sorting').val(),
                    ba_id : $('#jform_id').val()
                }
            });
        }

        function saveTags()
        {
            var tags = [],
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            $('select.meta_tags option').each(function(){
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

        function saveColors()
        {
            var colors = [],
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            $('select.image_colors option').each(function(){
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
            str += ' zmdi-check"></i></label><img data-src="'+showUrl+encodeURIComponent(el.path);
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

        var animationOut,
            CKE = CKEDITOR.replace('CKE-editor'),
            deleteMode,
            pagLimit = 25,
            images = $('#jform_gallery_items').val(),
            target = $('.category-list ul.root-list'),
            str = '',
            uploadButton = $('#file-upload-form input[type="file"]'),
            settings,
            currentCat,
            currentItem,
            uploadMode = false,
            albumMode = true,
            uri = $('#juri-root').val(),
            notification = document.getElementById('ba-notification'),
            currentContext,
            contextMode = false,
            oldName = '',
            showUrl = uri+'index.php?option=com_bagallery&layout=uploader&task=gallery.showAdminImage&image=',
            clientHeight = document.documentElement.clientHeight;
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

        checkAlbum();
        
        function checkAlbum()
        {
            if ($('#album-mode').val() == 1) {
                albumMode = true;
                $('body').addClass('album-mode');
                $('input.default-category').closest('div').hide();
                $('input.hide-in-category-all').closest('div').hide();
                $('.folders-context-menu .move-to, #move-to-modal li[data-id="root"] > span').show();
                $('#category-options div.img-thumbnail').show();
            } else {
                albumMode = false;
                $('body').removeClass('album-mode');
                $('input.default-category').closest('div').show();
                $('input.hide-in-category-all').closest('div').show();
                $('.folders-context-menu .move-to, #move-to-modal li[data-id="root"] > span').hide();
                $('#category-options div.img-thumbnail').hide();
                $('#root .root-list li').each(function(){
                    var $this = $(this),
                        ul = $this.parent();
                    if (!ul.hasClass('root-list')) {
                        $('#root .root-list').append(this);
                    }
                });
                $('#root .root-list ul, #root .root-list i.zmdi-chevron-right').remove();
                catImages['root'] = [];
            }
            if (currentCat == 'root') {
                $('#root > a').trigger('click');
            }
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

        function getFileSize(size)
        {
            size = size / 1024;
            size = Math.floor(size);
            if (size >= 1024) {
                size = size / 1024;
                size = Math.floor(size);
                size = size+' MB';
            } else {
                size = size+' KB';
            }

            return size;
        }

        function saveImg(obj)
        {
            obj = JSON.stringify(obj);
            var item = currentItem.find('.select-item');
            item.val(obj);
            item = item.attr('data-index');
            catImages[currentCat][item] = JSON.parse(obj);
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

        function sortName(obj1, obj2)
        {
            if(obj1.name.toLowerCase() > obj2.name.toLowerCase()) {
                return 1;
            }
            if(obj1.name.toLowerCase() < obj2.name.toLowerCase()) {
                return -1;
            }
            else {
                return 0;
            }
        }

        function showNotice(message)
        {
            if (notification.className == 'notification-in') {
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

        function drawPaginator()
        {
            $('div.table-body').find('.pagination').remove();
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
                span.className = 'zmdi zmdi-skip-previous';
                div.appendChild(ul);
                ul = appendItems(ul, li, a, span);
                li = document.createElement('li')
                li.className = 'disabled ba-prev';
                a = document.createElement('a');
                span = document.createElement('span');
                span.className = 'zmdi zmdi-play';
                ul = appendItems(ul, li, a, span);
                for (var i = 0; i < pages; i++) {
                    li = document.createElement('li');
                    li.className = 'ba-pages';
                    if (i == 0) {
                        li.className += ' active';
                    }
                    a = document.createElement('a');
                    span = document.createTextNode(i + 1);
                    $(a).attr('data-page', i);
                    ul = appendItems(ul, li, a, span);
                }
                li = document.createElement('li');
                a = document.createElement('a');
                li.className = 'ba-next';
                span = document.createElement('span');
                span.className = 'zmdi zmdi-play';
                $(a).attr('data-page', 1);
                ul = appendItems(ul, li, a, span);
                li = document.createElement('li');
                a = document.createElement('a');
                li.className = 'ba-last-page';
                span = document.createElement('span');
                span.className = 'zmdi zmdi-skip-next';
                $(a).attr('data-page', pages - 1);
                ul = appendItems(ul, li, a, span);
                $(ul).find('a').on('click', function(event){
                    event.preventDefault();
                    var $this = $(this);
                    if ($this.parent().hasClass('active') || $this.parent().hasClass('disabled')) {
                        return false;
                    }
                    var page = $this.attr('data-page') * 1,
                        ul = $this.closest('ul'),
                        first = ul.find('li').first(),
                        last = ul.find('li').last(),
                        max = page*pagLimit + pagLimit,
                        tbody = $('table.ba-items-table tbody').empty();
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
                        str = returnTrHtml(catImages[currentCat][i], i);
                        tbody.append(str);
                        tbody.find('.select-item').last().val(JSON.stringify(catImages[currentCat][i]))
                    }
                    checkPagination();
                    getAllImages();
                });
                $('div.table-body').append(div);
                checkPagination();
            }
        }

        function checkPagination()
        {
            var paginator = $('.pagination-list'),
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

        function reIndexArray()
        {
            if (currentCat) {
                var array = [];
                catImages[currentCat].forEach(function(el, ind){
                    array.push(el);
                })
                catImages[currentCat] = array;
            }
        }

        function showContext(event)
        {
            event.stopPropagation();
            event.preventDefault();
            $('.context-active').removeClass('context-active');
            currentContext.addClass('context-active');
            var deltaX = document.documentElement.clientWidth - event.pageX,
                deltaY = document.documentElement.clientHeight - event.clientY,
                context;
            if (currentContext.hasClass('ba-images')) {
                context = $('.files-context-menu');
            } else {
                if (currentContext[0].localName == 'tr') {
                    var id = currentContext.attr('data-id');
                    currentContext = $('#'+id+' > a');
                }
                context = $('.folders-context-menu');
            }
            setTimeout(function(){
                context.css({
                    'top' : event.pageY,
                    'left' : event.pageX,
                }).show();
                checkContext(context, deltaY, deltaX);
            }, 50);
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
            { name: 'insert',      items : [ 'Image','Table','HorizontalRule'] }
        ];
        CKE.config.toolbar = 'Basic';

        $('.modal').on('hide', function(){
            $(this).addClass('ba-modal-close');
            setTimeout(function(){
                $('.ba-modal-close').removeClass('ba-modal-close');
            }, 500)
        });

        $('i.check-all').on('click', function(){
            $('#check-all').trigger('click');
        });

        $('i.sort-action').closest('.ba-custom-select').on('show', function(){
            var sort = $('#jform_sorting_mode').val();
            $(this).find('li').removeClass('selected').each(function(){
                var $this = $(this);
                $this.find('i.zmdi-check').remove();
                if ($this.attr('data-value') == sort) {
                    $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
                }
            });
        });

        $('.gallery-editor .category-list, .gallery-editor .images-list').on('contextmenu', function(event){
            event.preventDefault();
        });

        $('div.category-list, div.table-body').on('contextmenu', function(event){
            $('.context-active').removeClass('context-active');
            var deltaX = document.documentElement.clientWidth - event.pageX,
                deltaY = document.documentElement.clientHeight - event.clientY,
                context;
            setTimeout(function(){
                context = $('.empty-context-menu');
                context.css({
                    'top' : event.pageY,
                    'left' : event.pageX,
                }).show();
                checkContext(context, deltaY, deltaX);
            }, 50);
        });

        $g('.new-name').on('keyup', function(){
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

        $('.ba-context-menu .rename').on('mousedown', function(event){
            if (event.button > 1) {
                return false;
            }
            var name = '';
            name = currentContext.find('span').text();
            name = $.trim(name);
            oldName = name;
            $('.new-name').val(name);
            $('#apply-rename').removeClass('active-button');
            $('#rename-modal').modal();
        });

        $('table.ba-items-list').on('contextmenu', 'tr', function(event){
            currentContext = $(this);
            showContext(event);
        });

        $('ul.root-list').on('contextmenu', 'a', function(event){
            currentContext = $(this);
            showContext(event);
        });

        $('.ba-sorting-action-wrapper ul li').on('click', function(){
            var value = $(this).attr('data-value');
            if (currentCat) {
                if (value == 'name') {
                    catImages[currentCat].sort(sortName);
                } else if (value == 'newest') {
                    catImages[currentCat].sort(sortDate);
                    catImages[currentCat].reverse();
                } else if (value == 'oldest') {
                    catImages[currentCat].sort(sortDate);
                }
                var tbody = $('table.ba-items-table tbody').empty();
                catImages[currentCat].forEach(function(el, ind){
                    var str = returnTrHtml(el, ind);
                    tbody.append(str);
                    tbody.find('.select-item').last().val(JSON.stringify(el))
                });
                getAllImages();
            }
            $('#jform_sorting_mode').val(value);
        });

        $('#check-all').on('click', function(){
            if ($(this).prop('checked')) {
                $('tr input[type="checkbox"]').each(function(){
                    $(this).attr('checked', true);
                    $('i.move-to, i.delete-selected, i.filename-to-title').removeClass('disabled-item');
                });
            } else {
                $('tr input[type="checkbox"]').each(function(){
                    $(this).removeAttr('checked');
                    $('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
                });
            }
        });

        $('table.ba-items-list ').on('change', 'input[type="checkbox"]', function(){
            if ($(this).prop('checked')) {
                $('i.move-to, i.delete-selected, i.filename-to-title').removeClass('disabled-item');
            } else {
                var flag = true;
                $('tr input[type="checkbox"]').each(function(){
                    if ($(this).prop('checked')) {
                        flag = false;
                    }
                });
                if (flag) {
                    $('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
                }
            }
        });

        $('i.move-to').on('click', function(){
            if ($(this).hasClass('disabled-item')) {
                return false;
            }
            contextMode = false;
            $('#move-to-modal .availible-folders .active').removeClass('active');
            $('#move-to-modal .active-button').removeClass('active-button');
            $('#move-to-modal').modal();
        });

        $('#move-to-modal .availible-folders').on('click', 'li', function(event){
            event.stopPropagation();
            $('#move-to-modal .availible-folders .active').removeClass('active');
            $(this).addClass('active');
            $('.apply-move').addClass('active-button');
        });

        $('#move-to-modal').on('hide', function(){
            $('#move-to-modal .availible-folders > ul > li > ul').remove();
        });

        $('#move-to-modal').on('show', function(){
            $('#move-to-modal .availible-folders > ul > li > ul').remove();
            var ul = $('div.category-list ul.root-list').clone(),
                li,
                text;
            ul.find('.active').removeClass('active');
            ul.find('li').each(function(){
                li = $(this);
                var span = document.createElement('span'),
                    i = document.createElement('i');
                i.className = 'zmdi zmdi-folder';
                text = li.find('> a').text();
                text = $.trim(text),
                text = document.createTextNode(text);
                span.appendChild(i);
                span.appendChild(text);
                li.find('> a').remove();
                if (li.attr('id') == 'category-all') {
                    li.hide();
                }
                li.attr('data-id', li.attr('id')).removeAttr('id').removeClass('ba-category').prepend(span);
            })
            $('#move-to-modal .availible-folders > ul > li').append(ul);
            ul.find('i.zmdi-chevron-right').on('click', function(){
                if ($(this).parent().hasClass('visible-branch')) {
                    $(this).parent().removeClass('visible-branch');
                } else {
                    $(this).parent().addClass('visible-branch');
                }
            });
        });

        $('.apply-move').on('click', function(event){
            event.preventDefault();
            var path = $('#move-to-modal .availible-folders .active').attr('data-id');
            if (!path) {
                return false;
            }
            if (!contextMode) {
                $('table.ba-items-table input.select-item').each(function(){
                    var $this = $(this),
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
                $('table.ba-category-table input[type="checkbox"]').each(function(){
                    var $this = $(this),
                        id = $this.closest('tr').attr('data-id'),
                        parent;
                    if ($this.prop('checked')) {
                        if (id != path) {
                            if ($('#'+path).find('> ul').length == 0) {
                                $('#'+path).append('<i class="zmdi zmdi-chevron-right"></i><ul></ul>');
                            }
                            parent = $('#'+id).parent();
                            $('#'+path+'> ul').append($('#'+id));
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
                        parent = $('#'+id).parent();
                    if (id != path) {
                        if ($('#'+path).find('> ul').length == 0) {
                            $('#'+path).append('<i class="zmdi zmdi-chevron-right"></i><ul></ul>');
                        }
                        $('#'+path+'> ul').append($('#'+id));
                        $('tr[data-id="'+id+'"]').remove();
                        if (parent.find('li').length == 0) {
                            parent.parent().find('i.zmdi-chevron-right').remove();
                            parent.remove();
                        }
                    }
                }
            }
            reIndexArray();
            $('#'+currentCat).find('> a').trigger('click');
            showNotice($('#move-to-const').val());
            $('#move-to-modal').modal('hide');
        });

        $('div.ba-context-menu span.move-to').on('mousedown', function(event){
            if (event.button > 1) {
                return false;
            }
            contextMode = true;
            $('#move-to-modal .availible-folders .active').removeClass('active');
            $('#move-to-modal .active-button').removeClass('active-button');
            $('#move-to-modal').modal();
        });

        $('.upload-images').on('mousedown', function(event){
            if (event.button > 1) {
                return false;
            }
            if (currentCat && !$(this).hasClass('disabled-item')) {
                uploadMode = 'images';
                uploadButton.attr('multiple', true).trigger('click');
            }
        });

        uploadButton.off('change').on('change', function(event){
            if (this.files.length > 0) {
                var files = [],
                    sBackdrop = $g('<div/>', {
                        'class' : 'saving-backdrop'
                    });
                for (var i = 0; i < this.files.length; i++) {
                    files.push(this.files[i]);
                }
                document.getElementsByTagName('body')[0].appendChild(sBackdrop[0]);
                UploadFiles(files);
            }
        });

        function UploadFiles(files)
        {
            if (files.length > 0) {
                var upload = $g('#saving-media').val(),
                    str = '',
                    file = files.pop(),
                    XHR = new XMLHttpRequest(),
                    url = "index.php?option=com_bagallery&task=gallery.uploadAjax&file="+file.name;
                if (XHR.upload && file.size <= 66000000) {
                    upload = JSON.parse(upload);
                    url = upload.url+url;
                    str += upload.uploading+'<img src="'+upload.url;
                    str += 'components/com_bagallery/assets/images/reload.svg"></img>';
                    if (notification.children[0].innerHTML != str) {
                        notification.children[0].innerHTML = str;
                        notification.className = 'notification-in';
                    }
                    XHR.onreadystatechange = function(e) {
                        if (XHR.readyState == 4) {
                            try {
                                var obj = JSON.parse(XHR.responseText);
                                if (obj.size == 0) {
                                    $('#file-upload-form form')[0].submit();
                                    return false;
                                }
                            } catch (error) {
                                $('#file-upload-form form')[0].submit();
                                return false;
                            }
                            if (uploadMode == 'images') {
                                var tbody = $('table.ba-items-table tbody'),
                                    img = new Image(),
                                    canvas = document.createElement('canvas'),
                                    ratio = 1;
                                obj.category = currentCat;
                                obj.imageId = imageId;
                                obj.resave = 1;
                                obj.time = +new Date();
                                obj.target = 'blank';
                                catImages[currentCat].push(obj);
                                imageId++;
                                if (tbody.find('tr').length < pagLimit) {
                                    var ind = catImages[currentCat].length - 1,
                                        str = returnTrHtml(obj, ind);
                                    tbody.append(str);
                                    tbody.find('.select-item').last().val(JSON.stringify(obj));
                                }
                                drawPaginator();
                                getAllImages();
                            } else if (uploadMode == 'alternativeImage') {
                                var img = obj.url,
                                    data = currentItem.find('.select-item').val();
                                data = JSON.parse(data);
                                data.alternative = img;
                                data.resave = 1;
                                $('.alternative-image').val(img);
                                saveImg(data);
                            } else if (uploadMode == 'reselectImage') {
                                var data = currentItem.find('.select-item').val();
                                data = JSON.parse(data);
                                data.name = obj.name;
                                data.path = obj.path;
                                data.size = obj.size;
                                data.thumbnail_url = '';
                                data.url = obj.url;
                                data.resave = 1;
                                currentItem.find('img')[0].src = obj.url;
                                currentItem.find('.select-td').next().text(obj.name).next().text(getFileSize(obj.size));
                                obj.url = obj.url.replace(new RegExp(' ', 'g'), '%20');
                                $('div.images-options div.img-thumbnail').css('background-image', 'url('+uri+obj.url+')');
                                saveImg(data);
                            } else if (uploadMode == 'album') {
                                setCategoryValue(currentItem[0], obj.path, 'image');
                                $g('#category-options div.img-thumbnail img').remove();
                                obj.url = obj.url.replace(/\s/g, '%20');
                                $g('#category-options div.img-thumbnail').css('background-image', 'url('+uri+obj.url+')');
                            } else if (uploadMode == 'watermark') {
                                $('#jform_watermark_upload').val(obj.path);
                            }
                            UploadFiles(files);
                        }
                    };
                    XHR.open("POST", url, true);
                    XHR.send(file);
                } else {
                    showNotice($g('#post-max-error').val());
                    UploadFiles(files);
                }
            } else {
                var sBackdrop = document.getElementsByClassName('saving-backdrop')[0];
                sBackdrop.className += ' animation-out';
                setTimeout(function(){
                    document.getElementsByTagName('body')[0].removeChild(sBackdrop);
                }, 300);
                showNotice($('#upload-const').val());
            }
        }

        window.uploadCallback = function(array){
            if (uploadMode == 'images') {
                for (var i = 0; i < array.length; i++) {
                    var obj = array[i],
                        tbody = $('table.ba-items-table tbody'),
                        img = new Image(),
                        canvas = document.createElement('canvas'),
                        ratio = 1;
                    obj.category = currentCat;
                    obj.imageId = imageId;
                    obj.resave = 1;
                    obj.time = +new Date();
                    obj.target = 'blank';
                    catImages[currentCat].push(obj);
                    imageId++;
                    if (tbody.find('tr').length < pagLimit) {
                        var ind = catImages[currentCat].length - 1,
                            str = returnTrHtml(obj, ind);
                        tbody.append(str);
                        tbody.find('.select-item').last().val(JSON.stringify(obj));
                    }
                    
                }
                drawPaginator();
                getAllImages();
            } else if (uploadMode == 'alternativeImage') {
                var obj = array[0],
                    img = obj.url,
                    data = currentItem.find('.select-item').val();
                data = JSON.parse(obj);
                data.alternative = img;
                data.resave = 1;
                $('.alternative-image').val(img);
                saveImg(data);
            }  else if (uploadMode == 'reselectImage') {
                var obj = array[0],
                    data = currentItem.find('.select-item').val();
                data = JSON.parse(data);
                data.name = obj.name;
                data.path = obj.path;
                data.size = obj.size;
                data.thumbnail_url = '';
                data.url = obj.url;
                data.resave = 1;
                currentItem.find('img')[0].src = obj.url;
                currentItem.find('.select-td').next().text(obj.name).next().text(getFileSize(obj.size));
                obj.url = obj.url.replace(new RegExp(' ', 'g'), '%20');
                $('div.images-options div.img-thumbnail').css('background-image', 'url('+uri+obj.url+')');
                saveImg(data);
            } else if (uploadMode == 'album') {
                var obj = array[0],
                    url = obj.url.replace(/\s/g, '%20');
                setCategoryValue(currentItem[0], obj.path, 'image');
                $('#category-options div.img-thumbnail img').remove();
                $('#category-options div.img-thumbnail').css('background-image', 'url('+uri+url+')');
            } else if (uploadMode == 'watermark') {
                var obj = array[0];
                $('#jform_watermark_upload').val(obj.path);
            }
            var sBackdrop = document.getElementsByClassName('saving-backdrop')[0];
            sBackdrop.className += ' animation-out';
            setTimeout(function(){
                document.getElementsByTagName('body')[0].removeChild(sBackdrop);
            }, 300);
            showNotice($('#upload-const').val());
        }

        $('body').on('mousedown', function(){
            $('.context-active').removeClass('context-active');
            $('.ba-context-menu').hide();
        });

        $('.ba-custom-select').not('.aspect-ratio-select').find('> i, > span > i, input').on('click', function(event){
            event.stopPropagation()
            var $this = $(this),
                parent = $this.closest('.ba-custom-select');
            parent.find('ul').addClass('visible-select');
            parent.find('li').one('click', function(){
                var text = $(this).text(),
                    val = $(this).attr('data-value');
                parent.find('input').val(text).attr('data-value', val);
                parent.trigger('customHide');
            });
            parent.trigger('show');
            setTimeout(function(){
                $('body').one('click', function(){
                    $('.visible-select').removeClass('visible-select');
                });
            }, 50);
        });

        $('.create-categery').on('mousedown', function(event){
            if (event.button > 1 || $(this).hasClass('active-product-tour')) {
                return false;
            }
            event.preventDefault();
            $('input.category-name').val('');
            $('#create-new-category').removeClass('active-button');
            $('#create-category-modal').modal();
        });

        $('#create-new-category').on('click', function(event){
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
            showNotice(document.querySelector('#category-const').value);
            $g('#create-category-modal').modal('hide');
        });

        $('input.category-name').on('keyup', function(){
            if ($.trim($(this).val())) {
                $('#create-new-category').addClass('active-button');
            } else {
                $('#create-new-category').removeClass('active-button');
            }
        });

        $('label.settings').on('click', function(){
            if (!$(this).hasClass('active-product-tour')) {
                $('#global-options').modal()
            }
        });

        target.on('click', 'i.zmdi-chevron-right', function(){
            if ($(this).closest('li').hasClass('visible-branch')) {
                $(this).closest('li').removeClass('visible-branch');
            } else {
                $(this).closest('li').addClass('visible-branch');
            }
        });

        $('li.root > a').on('click', function(){
            $('#check-all').removeAttr('checked');
            $('div.gallery-options > div').hide();
            $('').hide();
            target.find('.active').removeClass('active');
            $(this).closest('li').addClass('active');
            var tbody = $('table.ba-items-table tbody').empty();
            $('table.ba-category-table tbody').empty();
            $('div.gallery-options > img').show();
            if (!albumMode) {
                $('.upload-images').addClass('disabled-item').parent().addClass('disabled-item');
            } else {
                $('.upload-images').removeClass('disabled-item').parent().removeClass('disabled-item');
            }
            currentCat = 'root';
            $('li.root > ul > li').each(function(){
                var $this = $(this),
                    flag = $('#album-mode').val();
                if ((flag == 1 && $this.attr('id') != 'category-all') || flag == 0) {
                    str = '<tr data-id="'+$this.attr('id')+'"><td class="select-td">';
                    str += '<input type="checkbox"><div class="folder-icons">';
                    str += '<a class="zmdi zmdi-folder"></a><i class="zmdi zmdi-circle';
                    str += '-o"></i><i class="zmdi zmdi-check"></i></div></td>';
                    str += '<td class="draggable-handler"><a>';
                    str += $this.find('> a span').text();
                    str += '</a></td><td class="draggable-handler"></td></tr>';
                    $('table.ba-category-table tbody').append(str);
                }
            });
            catImages['root'].forEach(function(el, ind){
                var str = returnTrHtml(el, ind);
                tbody.append(str);
                tbody.find('.select-item').last().val(JSON.stringify(el))
            });
            drawPaginator();
            getAllImages();
        });

        $('table.ba-category-table').on('click', 'div.folder-icons', function(){
            var checkbox = $(this).closest('td.select-td').find('input[type="checkbox"]');
            if (checkbox.prop('checked')) {
                checkbox.removeAttr('checked');
                var flag = true;
                $('tr input[type="checkbox"]').each(function(){
                    if ($(this).prop('checked')) {
                        flag = false;
                    }
                });
                if (flag) {
                    $('i.move-to, i.delete-selected, i.filename-to-title').addClass('disabled-item');
                }
            } else {
                checkbox.attr('checked', true);
                $('i.move-to, i.delete-selected, i.filename-to-title').removeClass('disabled-item');
            }
        });

        $('table.ba-category-table').on('click', 'a', function(event){
            event.preventDefault();
            var id = $(this).closest('tr').attr('data-id');
            $('#'+id+'> a').trigger('click');
        });

        target.on('click', 'a', function(event){
            event.stopPropagation();
            var li = this.closest('li'),
                obj = window.categories[li.dataset.ind],
                id = li.id,
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
                currentCat = '';
                $g('div.table-body div.pagination').remove();
            }
            if (currentCat) {
                $g('.upload-images').removeClass('disabled-item').parent().removeClass('disabled-item');
            } else {
                $g('.upload-images').addClass('disabled-item').parent().addClass('disabled-item');
            }
        });

        $('div.images-options div.img-thumbnail .camera-container').on('click', function(){
            uploadButton.removeAttr('multiple').trigger('click');
            uploadMode = 'reselectImage';
        });

        $('.alternative-image').on('click', function(){
            uploadButton.removeAttr('multiple').trigger('click');
            uploadMode = 'alternativeImage';
        });

        $('.delete-alternative-image').on('click', function(){
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            obj.alternative = '';
            obj.resave = 1;
            $('.alternative-image').val('');
            saveImg(obj);
        });

        $('.image-suffix').on('input', function(){
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            obj.suffix = this.value.trim();
            obj.resave = 1;
            saveImg(obj);
        });

        $('#category-options div.img-thumbnail .camera-container').on('click', function(){
            uploadButton.removeAttr('multiple').trigger('click');
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

        $('input.link-target+i+ul li').on('click', function(){
            $('input.link-target+i+ul li.selected').removeClass('selected');
            $('input.link-target+i+ul i.zmdi-check').remove();
            $(this).addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
        });

        $('i.add-link').on('click', function(){
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            $('input.image-link').val(obj.link);
            $('input.link-target').parent().find('ul li').each(function(){
                var $this = $(this);
                if ($this.attr('data-value') == obj.target) {
                    $('input.link-target').attr('data-value', $this.attr('data-value')).val($this.text());
                    return false;
                }
            });
            $('input.link-target+i+ul li').each(function(){
                var $this = $(this).removeClass('selected');
                $this.find('i.zmdi-check').remove();
                if ($this.attr('data-value') == obj.target) {
                    $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
                }
            });
            $('#add-link-modal').modal();
        });
        $('#add-link').on('click', function(event){
            event.preventDefault();
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            obj.link = $('input.image-link').val();
            obj.target = $('input.link-target').attr('data-value');
            obj.resave = 1;
            saveImg(obj);
            $('#add-link-modal').modal('hide');
        });

        $('i.add-embed-code').on('click', function(){
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            $('.ba-embed').val(obj.video)
            $('#embed-modal').modal();
        });
        
        $('#embed-apply').on('click', function(event){
            event.preventDefault()
            var value = $('.ba-embed').val(),
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            obj.video = value;
            obj.resave = 1;
            saveImg(obj);
            $('#embed-modal').modal('hide');
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

        $('i.filename-to-title').on('click', function(){
            if (!this.classList.contains('disabled-item')) {
                if ($('table.ba-category-table [data-id="category-all"] input[type="checkbox"]').prop('checked')) {
                    $('table.ba-category-table tr:not([data-id="category-all"])').each(function(){
                        catImages[this.dataset.id].forEach(function(el){
                            filenameToTitle(el);
                        });
                    });
                } else {
                    $('table.ba-category-table tr input[type="checkbox"]').each(function(){
                        if (this.checked) {
                            var id = $(this).closest('tr').attr('data-id');
                            catImages[id].forEach(function(el){
                                filenameToTitle(el);
                            });
                            $('#'+id+' ul li').each(function(){
                                catImages[this.id].forEach(function(el){
                                    filenameToTitle(el);
                                });
                            });
                        }
                    });
                    $('td.select-td input.select-item').each(function(){
                        if (this.checked) {
                            filenameToTitle(catImages[currentCat][this.dataset.index]);
                            var obj = JSON.stringify(catImages[currentCat][this.dataset.index]);
                            this.value = obj;
                            if ($(this).closest('tr.ba-images').hasClass('active')) {
                                $(this).trigger('click');
                            }
                        }
                    });
                }
                showNotice(bagalleryLanguage['TITLES_MOVED']);
            }
        });

        $('i.delete-selected').on('click', function(){
            deleteMode = 'array';
            contextMode = false;
            var flag = false;
            $('table.ba-items-list input[type="checkbox"]').each(function(){
                if ($(this).prop('checked')) {
                    flag = true;
                    return false;
                }
            });
            if (flag) {
                if ($('table.ba-category-table [data-id="category-all"] input[type="checkbox"]').prop('checked')) {
                    $('.cannot-delete').show();
                    $('.can-delete, #delete-dialog h3').hide();
                    $('#apply-delete').addClass('disabled-button');
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
                    if (!flag) {
                        $('#deafult-message-dialog').modal();
                        return false;
                    }
                    $('.cannot-delete').hide();
                    $('.can-delete, #delete-dialog h3').show();
                    $('#apply-delete').removeClass('disabled-button');
                }
                $('#delete-dialog').modal();
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

        $('#apply-delete').on('click', function(event){
            event.preventDefault();
            if ($(this).hasClass('disabled-button')) {
                return false;
            }
            if (!contextMode) {
                if (deleteMode == 'single') {
                    if (currentItem.hasClass('ba-category')) {
                        delete(catImages[currentCat]);
                        $('table.ba-items-table tbody').empty();
                        $('.upload-images').addClass('disabled-item').parent().addClass('disabled-item');
                    } else {
                        var ind = currentItem.find('.select-item').attr('data-index');
                        delete(catImages[currentCat][ind]);
                    }
                    var parent = currentItem.parent();
                    currentItem.remove();
                    if (parent[0].localName == 'ul') {
                        if (parent.find('> li').length == 0) {
                            parent = parent.parent();
                            parent.find('i.zmdi-chevron-right').remove();
                            parent.find('> ul').remove();
                        }
                    }
                } else {
                    $('td.select-td input.select-item').each(function(){
                        var $this = $(this);
                        if ($this.prop('checked')) {
                            delete(catImages[currentCat][$this.attr('data-index')]);
                            $this.closest('tr').remove();
                        }
                    });
                    $('table.ba-category-table tr input[type="checkbox"]').each(function(){
                        var $this = $(this),
                            id = $this.closest('tr').attr('data-id'),
                            parent;
                        if ($this.prop('checked')) {
                            delete(catImages[id]);
                            parent = $('#'+id).parent();
                            $('#'+id).remove();
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
                    var id = currentContext.closest('li').attr('id'),
                        parent = $('#'+id).parent();
                    $('tr[data-id="'+id+'"]').remove();
                    var parent = currentContext.closest('li').parent();
                    currentContext.closest('li').remove();
                    if (parent[0].localName == 'ul') {
                        if (parent.find('> li').length == 0) {
                            parent = parent.parent();
                            parent.find('i.zmdi-chevron-right').remove();
                            parent.find('> ul').remove();
                        }
                    }
                }
            }
            if (deleteMode != 'single' || !currentItem.hasClass('ba-category')) {
                reIndexArray();
                $('#'+currentCat).find('> a').trigger('click');
            }
            $('div.table-body').find('.pagination').remove();
            showNotice($('#delete-const').val());
            $('div.gallery-options > div').hide();
            $('#delete-dialog').modal('hide');
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

        $('table.ba-items-table').on('click', 'tr', function(){
            $('tr.active').removeClass('active');
            $(this).addClass('active');
            $('#category-options').hide();
            $('div.images-options, div.gallery-options div.gallery-header').show();
            $('i.add-link').parent().show();
            $('i.add-embed-code').parent().show();
            currentItem = $(this);
            var obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            if (!obj.tags) {
                obj.tags = [];
            }
            if (!obj.colors) {
                obj.colors = [];
            }
            $('.picked-tags .tags-chosen').remove();
            $('select.meta_tags').empty();
            $('.all-tags li').removeClass('selected-tag');
            $('.meta-tags .picked-tags .search-tag input').val('');
            $('.all-tags li').hide();
            obj.tags.forEach(function(el){
                var title = el.title,
                    tagId = el.id,
                    str = '<li class="tags-chosen"><span>';
                $('.all-tags li[data-id="'+tagId+'"]').addClass('selected-tag');
                str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
                $('.picked-tags .search-tag').before(str);
                str = '<option value="'+tagId+'" selected>'+title+'</option>';
                $('select.meta_tags').append(str);
            });
            $('.picked-colors .colors-chosen').remove();
            $('select.image_colors').empty();
            $('.all-colors li').removeClass('selected-colors');
            $('.image-colors .picked-colors .search-colors input').val('');
            $('.all-colors li').hide();
            obj.colors.forEach(function(el){
                var title = el.title,
                    tagId = el.id,
                    str = '<li class="colors-chosen"><span><span class="chosen-color" style="background-color: ';
                $('.all-colors li[data-id="'+tagId+'"]').addClass('selected-colors');
                str += title+';"></span><span>'+title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
                $('.picked-colors .search-colors').before(str);
                str = '<option value="'+tagId+'" selected>'+title+'</option>';
                $('select.image_colors').append(str);
            });
            $('div.gallery-options > img').hide();
            obj.url = obj.url.replace(new RegExp(' ', 'g'), '%20');
            $('div.images-options div.img-thumbnail').css('background-image', 'url('+uri+obj.url+')');
            $('input.image-title').val(obj.title);
            if (obj.lightboxUrl) {
                $('input.image-alias').val(obj.lightboxUrl);
            } else {
                if (obj.title) {
                    $('input.image-alias').val(obj.title.toLowerCase().replace(new RegExp(" ", 'g'), "-"));
                } else {
                    $('input.image-alias').val('');
                }
            }
            $('input.image-short').val(obj.short);
            $('input.image-alt').val(obj.alt);
            $('.alternative-image').val(obj.alternative ? obj.alternative : '');
            $('.image-suffix').val(obj.suffix ? obj.suffix : '');
            if (obj.hideInAll && obj.hideInAll == 1) {
                $('.hide-in-category-all').attr('checked', true);
            } else {
                $('.hide-in-category-all').removeAttr('checked');
            }
        });

        $('.hide-in-category-all').on('click', function(){
            var value = $(this).prop('checked') ? 1 : 0,
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            obj.hideInAll = value;
            obj.resave = 1;
            saveImg(obj);
        });

        $('input.image-title').on('keyup', function(){
            var value = $(this).val(),
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            value = strip_tags(value);
            obj.title = value;
            obj.resave = 1;
            saveImg(obj);
        });
        
        $('input.image-alias').on('keyup', function(){
            var value = $(this).val().toLowerCase(),
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            value = strip_tags(value);
            obj.lightboxUrl = value.replace(new RegExp(" ", 'g'), "-");
            obj.resave = 1;
            saveImg(obj);
        });
        
        $('.image-short').on('keyup', function(){
            var value = $(this).val(),
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            value = strip_tags(value);
            obj.short = value;
            obj.resave = 1;
            saveImg(obj);
        });
        
        $('.image-alt').on('keyup', function(){
            var value = $(this).val(),
                obj = currentItem.find('.select-item').val();
            obj = JSON.parse(obj);
            value = strip_tags(value);
            obj.alt = value;
            obj.resave = 1;
            saveImg(obj);
        });

        $('.meta-tags .picked-tags .search-tag input').on('keydown', function(event){
            var title = $(this).val().trim().toLowerCase();
            if (event.keyCode == 13) {
                if (!title) {
                    this.value = '';
                    return false;
                }
                var str = '<li class="tags-chosen"><span>',
                    tagId = 'new$'+title;
                $('.all-tags li').each(function(){
                    var search = $(this).text().trim().toLowerCase();
                    if (title == search) {
                        this.classList.add('selected-tag');
                        tagId = this.dataset.id;
                        return false;
                    }
                });
                if ($('.picked-tags .tags-chosen i[data-remove="'+tagId+'"]').length > 0) {
                    return false;
                }
                str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
                $('.picked-tags .search-tag').before(str);
                str = '<option value="'+tagId+'" selected>'+title+'</option>';
                $('select.meta_tags').append(str);
                $(this).val('');
                $('.all-tags li').hide();
                saveTags();
                event.stopPropagation();
                event.preventDefault();
                return false;
            } else {
                $('.all-tags li').each(function(){
                    var search = $(this).text().trim().toLowerCase();
                    if (search.indexOf(title) < 0 || title == '') {
                        $(this).hide();
                    } else {
                        $(this).show();
                    }
                });
            }
        });

        $('.all-tags').on('click', 'li', function(){
            if (this.classList.contains('selected-tag')) {
                return false;
            }
            var title = $(this).text().trim(),
                tagId = this.dataset.id;
            var str = '<li class="tags-chosen"><span>';
            str += title+'</span><i class="zmdi zmdi-close" data-remove="'+tagId+'"></i></li>';
            $('.picked-tags .search-tag').before(str);
            str = '<option value="'+tagId+'" selected>'+title+'</option>';
            $('select.meta_tags').append(str);
            $('.meta-tags .picked-tags .search-tag input').val('');
            $('.all-tags li').hide();
            this.classList.add('selected-tag');
            saveTags();
        });

        $('.meta-tags .picked-tags').on('click', '.zmdi.zmdi-close', function(){
            var del = this.dataset.remove;
            $('select.meta_tags option[value="'+del+'"]').remove();
            $(this).closest('li').remove();
            $('.all-tags li[data-id="'+del+'"]').removeClass('selected-tag');
            $('.all-tags li').hide();
            saveTags();
        });

        $('.search-colors input').minicolors({
            theme: 'bootstrap'
        });

        $('.image-colors .picked-colors .search-colors input').on('keydown', function(event){
            var title = $(this).val().trim().toLowerCase();
            if (event.keyCode == 13) {
                if (!title) {
                    this.value = '';
                    return false;
                }
                var str = '<li class="colors-chosen"><span class="chosen-color" style="background-color: ',
                    tagId = 'new$'+title;
                $('.all-colors li').each(function(){
                    var search = $(this).text().trim().toLowerCase();
                    if (title == search) {
                        this.classList.add('selected-colors');
                        tagId = this.dataset.id;
                        return false;
                    }
                });
                if ($('.picked-colors .colors-chosen i[data-remove="'+tagId+'"]').length > 0) {
                    return false;
                }
                str += title+';"></span><span>'+title+'</span><i class="zmdi zmdi-close" data-remove="';
                str += tagId+'"></i></li>';
                $('.picked-colors .search-colors').before(str);
                str = '<option value="'+tagId+'" selected>'+title+'</option>';
                $('select.image_colors').append(str);
                $(this).val('');
                $('.all-colors li').hide();
                saveColors();
                event.stopPropagation();
                event.preventDefault();
                return false;
            }
        });

        $('.image-colors .picked-colors').on('click', '.zmdi.zmdi-close', function(){
            var del = this.dataset.remove;
            $('select.image_colors option[value="'+del+'"]').remove();
            $(this).closest('li').remove();
            $('.all-colors li[data-id="'+del+'"]').removeClass('selected-colors');
            $('.all-colors li').hide();
            saveColors();
        });

        $('table.ba-items-table tbody').sortable({
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
                var page = $('.pagination-list li.ba-pages.active a').attr('data-page');
                if (!page) {
                    page = 0;
                }
                var sItems = $(this).find('tr input.select-item'),
                    max = page * pagLimit + sItems.length,
                    ind = 0;
                for (var i = page * pagLimit; i < max; i++) {
                    var $this = $(sItems[ind]);
                    obj = $this.val();
                    $this.attr('data-index', i);
                    obj = JSON.parse(obj);
                    catImages[currentCat][i] = obj;
                    ind++;
                }
            }
        }).disableSelection();

        $('table.ba-category-table tbody').sortable({
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
                $(this).find('tr').each(function(ind, el){
                    id = $(this).attr('data-id');
                    ul.appendChild($('#'+id)[0]);
                });
                $('div.category-list li.active > ul').html(ul.innerHTML)
            }
        }).disableSelection();

        $('.pagination-limit .ba-custom-select').on('show', function(){
            var value = $(this).find('input').attr('data-value');
            $(this).find('li').each(function(){
                var $this = $(this).removeClass('selected');
                $this.find('i.zmdi-check').remove();
                if ($this.attr('data-value') == value) {
                    $this.addClass('selected').prepend('<i class="zmdi zmdi-check"></i>');
                }
            });
        }).on('customHide', function(){
            pagLimit = $(this).find('input').attr('data-value') * 1;
            var tbody = $('table.ba-items-table tbody').empty();
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
    });
})(jQuery);