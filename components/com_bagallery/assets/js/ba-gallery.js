/**
* @package   BaGallery
* @author    Balbooa https://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
*/

if (!window.onYouTubeIframeAPIReady) {
    function onYouTubeIframeAPIReady()
    {
        galleryApp.video.loaded.youtube = true;
        galleryApp.video.youtube();
    }
}

var galleryApp = window.galleryApp ? window.galleryApp : {
        $g: jQuery,
        video: {
            loading: {},
            loaded: {},
            youtube: function(){
                document.querySelectorAll('.modal-image iframe[data-video="youtube"]').forEach(function(iframe){
                    if (!iframe.player) {
                        iframe.player = new YT.Player(iframe, {
                            events: {
                                onReady: function(event){
                                    iframe.player.loaded = true;
                                    if (iframe.closest('.ba-modal-body').classList.contains('active')) {
                                        iframe.player.playVideo();
                                    }
                                }
                            }
                        });
                    }
                    if (iframe.player.loaded && iframe.closest('.ba-modal-body').classList.contains('active')) {
                        iframe.player.playVideo();
                    } else if (iframe.player.loaded) {
                        iframe.player.pauseVideo();
                    }
                })
            },
            vimeo: function(){
                document.querySelectorAll('.modal-image iframe[data-video="vimeo"]').forEach(function(iframe){
                    if (!iframe.player) {
                        iframe.player = new Vimeo.Player(iframe, {
                            id: iframe.dataset.id
                        });
                    }
                    if (iframe.closest('.ba-modal-body').classList.contains('active')) {
                        iframe.player.play();
                    } else {
                        iframe.player.pause();
                    }
                })
            },
            load: function(type){
                if (this.loading[type] && !this.loaded[type]) {
                    return;
                } else if (this.loaded[type]) {
                    this[type]();
                    return;
                }
                this.loading[type] = true;
                let tag = document.createElement('script');
                if (type == 'vimeo') {
                    tag.onload = function(){
                        galleryApp.video.loaded.vimeo = true;
                        galleryApp.video.vimeo();
                    }
                    tag.src = "https://player.vimeo.com/api/player.js";
                } else if (window['YT'] && YT.Player) {
                    galleryApp.video.loaded.youtube = true;
                    galleryApp.video.youtube();
                } else if (!window['YT'] || !YT.Player) {
                    if (!window['YT']) {
                        window.YT = {
                            loading: 0,
                            loaded: 0
                        };
                    }
                    if (!window['YTConfig']) {
                        window.YTConfig = {
                            'host': 'http://www.youtube.com'
                        };
                    }
                    if (!YT.loading) {
                        YT.loading = 1;
                        var l = [];
                        YT.ready = function(f) {
                            if (YT.loaded) {
                                f();
                            } else {
                                l.push(f);
                            }
                        };
                        window.onYTReady = function() {
                            YT.loaded = 1;
                            for (var i = 0; i < l.length; i++) {
                                try {
                                    l[i]();
                                }
                                catch (e) {}
                            }
                        };
                        YT.setConfig = function(c) {
                            for (var k in c) {
                                if (c.hasOwnProperty(k)) {
                                    YTConfig[k] = c[k];
                                }
                            }
                        };
                    }
                    tag.id = 'www-widgetapi-script';
                    tag.src = 'https://s.ytimg.com/yts/jsbin/www-widgetapi-vflLM1tGT/www-widgetapi.js';
                    tag.async = true;
                }
                document.body.append(tag);
            }
        },
        disqus: {
            shortname: null,
            set: function(name){
                if (!this.loaded && !this.loading) {
                    this.shortname = name;
                    this.load();
                }
            },
            reset: function(){
                if (this.loaded) {
                    DISQUS.reset({
                        reload: true,
                        config: function(){
                            this.page.url = window.location.href;
                        }
                    });
                }
            },
            load: function(){
                if (!this.loaded) {
                    this.loading = true;
                    let script = document.createElement('script');
                    script.type = 'text/javascript';
                    script.onload = function(){
                        galleryApp.disqus.loaded = true;
                    }
                    script.src = '//'+this.shortname+'.disqus.com/embed.js';
                    document.head.append(script);
                }
            }
        },
        direction: {
            directions: ['top', 'right', 'bottom', 'left'],
            set: function(items){
                items.on('mouseenter', function(event){
                    let $this = galleryApp.$g(this),
                        caption = $this.find('.ba-caption'),
                        dir = 'from-'+galleryApp.direction.get($this, event);
                    caption.addClass(dir);
                    setTimeout(function(){
                        caption.removeClass(dir);
                    }, 300);
                }).on('mouseleave', function(event){
                    var $this = galleryApp.$g(this),
                        caption = $this.find('.ba-caption'),
                        dir = 'to-'+galleryApp.direction.get($this, event);
                    caption.addClass(dir);
                    setTimeout(function(){
                        caption.removeClass(dir);
                    }, 300);
                });
            },
            get: function(el, event){
                var w = el.width(),
                    h = el.height(),
                    x = (event.pageX - el.offset().left - (w / 2)) * (w > h ? (h / w) : 1),
                    y = (event.pageY - el.offset().top  - (h / 2)) * (h > w ? (w / h) : 1),
                    ind = Math.round((((Math.atan2(y, x) * (180 / Math.PI)) + 180) / 90) + 3) % 4;
                
                return this.directions[ind]
            }
        },
        getErrorText: function(text){
            let div = document.createElement('div');
            div.innerHTML = text;
            if (div.querySelector('title')) {
                text = div.querySelector('title').textContent;
            }

            return text;
        },
        fetch: async function(url, data){
            let request = await fetch(url, {
                    method: 'POST',
                    body: galleryApp.getFormData(data)
                }),
                response = null;
            if (request.ok) {
                response = await request.text();
            } else {
                let utf8Decoder = new TextDecoder('utf-8'),
                    reader = request.body.getReader(),
                    textData = await reader.read(),
                    text = utf8Decoder.decode(textData.value);
                console.error(galleryApp.getErrorText(text));
            }

            return response;
        },
        getFormData: function(data){
            let formData = new FormData();
            for (let ind in data) {
                formData.append(ind, data[ind]);
            }

            return formData;
        },
        language: {
            load: function(){
                fetch(JUri+'index.php?option=com_bagallery&task=gallery.getLanguage').then(function(response){
                    return response.json();
                }).then(function(json){
                    galleryApp.language.data = json;
                });
            },
            _: function(key){
                if (galleryApp.language.data && galleryApp.language.data[key]) {
                    return galleryApp.language.data[key];
                } else {
                    return key;
                }
            }
        },
        notification: {
            notice: null,
            delay: null,
            get: function(){
                let div = document.createElement('div');
                div.className = 'gallery-notification';
                div.innerHTML = '<i class="zmdi zmdi-close"></i><span class="ba-alert-title">'+
                    galleryApp.language._('ERROR')+'</span><p></p>';
                document.body.append(div);
                this.notice = galleryApp.$g(div);
                this.notice.find('i.zmdi-close').on('click', function(){
                    galleryApp.notification.notice.removeClass('notification-in').addClass('animation-out');
                });
            },
            text: function(message, className){
                let time = className ? 6000 : 3000;
                this.notice.find('p').html(message);
                this.notice.addClass(className).removeClass('animation-out').addClass('notification-in');
                clearTimeout(galleryApp.notification.delay);
                galleryApp.notification.delay = setTimeout(function(){
                    galleryApp.notification.notice.removeClass('notification-in').addClass('animation-out');
                    setTimeout(function(){
                        galleryApp.notification.notice.removeClass(className);
                    }, 400);
                }, time);
            },
            show: function(message, className){
                if (!className) {
                    className = '';
                }
                if (!galleryApp.notification.notice) {
                    galleryApp.notification.get();
                }
                if (this.notice.hasClass('notification-in')) {
                    clearTimeout(galleryApp.notification.delay);
                    galleryApp.notification.delay = setTimeout(function(){
                        galleryApp.notification.notice.removeClass('notification-in').addClass('animation-out');
                        setTimeout(function(){
                            galleryApp.notification.text(message, className);
                        }, 400);
                    }, 3000);
                } else {
                    galleryApp.notification.text(message, className);
                }
            }
        },
        touches: {
            touch: null,
            setTouch: function(touch){
                galleryApp.touches.touch = {
                    x: touch.screenX,
                    y: touch.screenY
                };
            },
            start: function(event){
                galleryApp.touches.setTouch(event.originalEvent.changedTouches[0]);
            }
        },
        share:{
            servises: {
                twitter: 'https://twitter.com/intent/tweet?url={link}&text={title}',
                facebook: 'http://www.facebook.com/sharer.php?u={link}',
                pinterest: 'http://www.pinterest.com/pin/create/button/?url={link}&media={img}&description={title}',
                linkedin: 'http://www.linkedin.com/shareArticle?url={link}&text={title}',
                vk: 'http://vk.com/share.php?url={link}&text={title}&image={img}',
                ok: 'https://connect.ok.ru/offer?url=${link}&title=${title}'
            },
            execute: function(modal, service){
                let url = this.servises[service],
                    text = modal.find('.modal-title').text() ? modal.find('.modal-title').text() : galleryApp.$g('title').text(),
                    data = {
                        link: encodeURIComponent(window.location.href),
                        img: encodeURIComponent(modal.find('.modal-image img').attr('src')),
                        title: encodeURIComponent(text)
                    };
                for (let ind in data) {
                    url = url.replace('{'+ind+'}', data[ind]);
                }
                window.open(url, 'sharer', 'toolbar=0, status=0, width=626, height=436');
            }
        },
        passwords: {}
    }

function initGalleries()
{
    document.removeEventListener("DOMContentLoaded", initGalleries);
    galleryApp.language.load();
    galleryApp.$g('.ba-gallery').each(function(){
        initGallery(this);
    });
}

function initGallery(bagallery)
{
    if (!bagallery) {
        initGalleries();
        return false;
    }
    var infinity = null,
        likeFlag = true,
        imgC,
        imagesArray = [],
        loadedImages = {},
        aimgC,
        originalLocation = '',
        catNames = new Array(),
        galleryId = bagallery.dataset.gallery,
        vk_api = galleryApp.$g('#vk-api-id-'+galleryId).val(),
        goodHeight = window.innerHeight - 100,
        goodWidth = goodHeight * 1.6,
        scroll = jQuery(window).scrollTop(),
        gallery = galleryApp.$g(bagallery),
        disqus = gallery.find('.disqus-subdomen').val(),
        galleryModal = gallery.find('.gallery-modal'),
        vkFlag = false,
        pageRefresh = gallery.find('.page-refresh').val(),
        albumMode = gallery.find('.album-mode').val(),
        album = gallery.find('.ba-album'),
        albumOptions = gallery.find('.albums-options').val(),
        galleryOptions = JSON.parse(gallery.find('.gallery-options').val()),
        $container = gallery.find('.ba-gallery-grid'),
        category = bagallery.dataset.category ? bagallery.dataset.category : gallery.find('.ba-filter-active').attr('data-filter'),
        defaultCat = category,
        winSize = window.innerWidth,
        albumWidth = 0,
        widthContent = 0,
        pagination = gallery.find('.ba-pagination-options').val(),
        copyright = gallery.find('.copyright-options').val(),
        lazyloadOptions = {},
        paginationConst = gallery.find('.ba-pagination-constant').val(),
        catModal = gallery.find('.category-password-modal'),
        fullscreen = true,
        imgCounts = null;
    if (disqus) {
        galleryApp.disqus.set(disqus);
    }
    if (pageRefresh == 1) {
        var refreshData = JSON.parse(gallery.find('.refresh-data').val());
    }
    gallery.find('.category-filter a[data-password], .ba-album-items[data-password]').each(function(){
        if (!galleryApp.passwords[this.dataset.id]) {
            galleryApp.passwords[this.dataset.id] = false;
        }
    });
    if (pagination && galleryOptions.pagination_type == 'infinity') {
        var infinityLoading = false;
        infinity = JSON.parse(gallery.find('.infinity-data').val());

        function getInfinityImages()
        {
            var match = category.match(/category-\d+/),
                tags = '',
                colors = '',
                newPage = currentPage.replace('.page-', ''),
                newCat = category == '.root' ? 'root' : match[0],
                method = 'append';
            gallery.find('.gallery-color.active').each(function(){
                if (colors) {
                    colors += ',';
                }
                colors += this.dataset.id;
            });
            gallery.find('.gallery-tag.active').each(function(){
                if (tags) {
                    tags += ',';
                }
                tags += this.dataset.id;
            });
            $container.ba_isotope({
                filter: category,
                margin : galleryOptions.image_spacing,
                count : imgC,
                mode : layout
            });
            if (infinity.page != newPage || infinity.category != newCat || infinity.tags != tags || infinity.colors != colors){
                if (infinity.category != newCat || infinity.tags != tags || infinity.colors != colors) {
                    method = 'html';
                }
                infinity.page = newPage;
                infinity.category = newCat;
                infinity.tags = tags;
                infinity.colors = colors;
                jQuery.ajax({
                    url: JUri+'index.php?option=com_bagallery&task=gallery.getGalleryImages',
                    type: "POST",
                    dataType: 'text',
                    data: infinity,
                    success: function(msg) {
                        infinityLoading = false;
                        var scrollTarrget = jQuery(document);
                        gallery.find('.ba-pagination').addClass('ba-empty').empty();
                        if (albumMode && albumOptions.album_enable_lightbox == 1 && infinity.category != 'root') {
                            scrollTarrget = gallery;
                        }
                        if (method == 'html') {
                            currentPage = '.page-1';
                            scrollTarrget.off('scroll.infinity');
                        }
                        if (msg) {
                            scrollTarrget.off('scroll.infinity').on('scroll.infinity', function(event) {
                                var paginatorY = gallery.find('.ba-pagination').parent().offset().top - window.innerHeight;
                                if (paginatorY < scroll && !infinityLoading) {
                                    infinityLoading = true;
                                    var next = currentPage.substr(6) * 1 + 1;
                                    currentPage = '.page-'+next;
                                    scrollTarrget.off('scroll.infinity');
                                    getInfinityImages();
                                    $container.ba_isotope({
                                        filter: category,
                                        margin : galleryOptions.image_spacing,
                                        count : imgC,
                                        mode : layout
                                    });
                                }
                                if (gallery.hasClass('album-in-lightbox')) {
                                    scroll = gallery.scrollTop();
                                } else {
                                    scroll = jQuery(window).scrollTop();
                                }
                            });
                            if (galleryOptions.random_sorting == 1) {
                                var div = document.createElement('div');
                                div.innerHTML = msg;
                                jQuery(div).ba_isotope('shuffle');
                                msg = div.innerHTML;
                            }
                            gallery.find('.ba-gallery-grid')[method](msg);
                            setSize();
                            addCaptionStyle();
                            createThumbnails();
                            $container.ba_isotope({
                                filter: category,
                                margin : galleryOptions.image_spacing,
                                count : imgC,
                                mode : layout
                            });
                        }
                        elements = getData();
                        if (infinity.activeImage) {
                            delete(infinity.activeImage);
                            locationImage();
                            currentPage = '.page-'+Math.ceil(elements.length / pagination.images_per_page);
                        }
                        checkImgCounts();
                        if (imgCounts[infinity.category] == elements.length && (albumOptions.album_enable_lightbox != 1 ||
                                (albumOptions.album_enable_lightbox == 1 && gallery.hasClass('album-in-lightbox')))) {
                            scrollTarrget.off('scroll.infinity');
                            if (elements.length > pagination.images_per_page * 1) {
                                var str = '<a class="ba-btn scroll-to-top">'+paginationConst[3]+'</a>';
                                gallery.find('.ba-pagination').removeClass('ba-empty').html(str);
                                var position = gallery.offset().top,
                                    target = jQuery('html, body');
                                if (gallery.hasClass('album-in-lightbox')) {
                                    target = gallery;
                                    position = 0;
                                }
                                gallery.find('.ba-pagination a').on('click', function(){
                                    target.stop().animate({
                                        scrollTop: position
                                    }, 'slow');
                                });
                            }
                        }
                    }
                });
            }
        }
    }
    if (albumMode) {
        albumOptions = JSON.parse(albumOptions);
        category = '.root';
        album.find('.ba-album-items').each(function(){
            catNames.push(this.dataset.filter);
        });
    } else {
        albumOptions = {}
    }
    if (paginationConst) {
        paginationConst = paginationConst.split('-_-');
    }
    var style = JSON.parse(gallery.find('.lightbox-options').val()),
        layout = gallery.find('.gallery-layout').val(),
        currentPage = '.page-1',
        paginationPages = 0,
        image = '',
        imageIndex = '',
        elements = getData(),
        titleSize = gallery.find('.modal-title').length,
        categories = bagallery.querySelector('.categories') ?
            JSON.parse(bagallery.querySelector('.categories').value) : null;
    
    var thumbnails = [],
        thumbnailStack = {
            count: 0
        },
        thumbnailc = 0;

    function createThumbnail(src)
    {
        fetch(src).then(function(response){
            return response.text()
        }).then(function(text){
            var img = document.createElement('img');
            img.onload = function(){
                var image = thumbnailStack[this.dataset.old];
                image.src = this.src;
                image.dataset.width = this.width;
                image.dataset.height = this.height;
                thumbnailc++;
                if (thumbnailc == thumbnailStack.count) {
                    resizeIsotope();
                }
            }
            img.src = text;
            img.dataset.old = src;
        });
    }

    function createThumbnails()
    {
        gallery.find('.ba-image img').each(function(){
            var src = this.src;
            if (src.indexOf('default-lazy-load.webp') != -1) {
                src = this.dataset.original;
            }
            if (src.indexOf('option=com_bagallery') !== -1 && !thumbnailStack[src]) {
                thumbnailStack[src] = this;
                thumbnailStack.count++;
                thumbnails.push(src);
            }
        });
        while (thumbnails.length > 0) {
            var src = thumbnails.pop();
            createThumbnail(src);
        }
    }
    
    createThumbnails()

    copyright = JSON.parse(copyright);
    if (copyright.disable_right_clk == '1') {
        gallery.off('contextmenu.gallery').on('contextmenu.gallery', function(e){
            e.preventDefault();
            e.stopPropagation();
        });
        galleryModal.parent().off('contextmenu.gallery').on('contextmenu.gallery', function(e){
            e.preventDefault();
            e.stopPropagation();
        });
    }
    if (copyright.disable_shortcuts == '1') {
        jQuery(window).on('keydown', function(e){
            if ((e.ctrlKey || e.metaKey) && (e.keyCode == 88 || e.keyCode == 65
                || e.keyCode == 67 || e.keyCode == 86 || e.keyCode == 83)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
    }
    if (copyright.disable_dev_console == '1') {
        function checkDevConsole(e)
        {
            if ((e.keyCode == 123 && e.originalEvent && e.originalEvent.code == 'F12') ||
                (e.keyCode == 73 && e.ctrlKey && e.shiftKey) ||
                (e.keyCode == 67 && e.ctrlKey && e.shiftKey) ||
                (e.keyCode == 75 && e.ctrlKey && e.shiftKey) ||
                (e.keyCode == 83 && e.ctrlKey && e.shiftKey) ||
                (e.keyCode == 81 && e.ctrlKey && e.shiftKey) ||
                (e.keyCode == 116 && e.shiftKey && e.originalEvent.code == 'F5') ||
                (e.keyCode == 118 && e.shiftKey && e.originalEvent.code == 'F7')) {
                return true;
            } else {
                return false;
            }
        }
        jQuery(window).on('keydown', function(e){
            if (checkDevConsole(e)) {
                e.preventDefault();
                e.stopPropagation();
            }
        });
        jQuery(document).off('contextmenu').on('contextmenu', function(e){
            e.preventDefault();
            e.stopPropagation();
        });
    }

    function createVK(body)
    {
        body.find('.vk-comments-wrapper').each(function(){
            this.id = body.hasClass('previous-slide') || body.hasClass('next-slide') ? '' : "ba-vk-"+galleryId;
        });
        if (body.hasClass('previous-slide') || body.hasClass('next-slide')) {
            return false;
        }
        let options = {
            redesign : 1,
            limit : 10,
            attach : "*",
            pageUrl : window.location.href
        }
        if (vk_api && vkFlag) {
            VK.Widgets.Comments("ba-vk-"+galleryId, options);
        } else if (vk_api) {
            let script = document.createElement('script');
            script.src = '//vk.com/js/api/openapi.js?125';
            script.onload = function(){
                VK.init({
                    apiId: vk_api,
                    onlyWidgets: true
                });
                VK.Widgets.Comments("ba-vk-"+galleryId, options);
                vkFlag = true;
            }
            document.head.appendChild(script);
        }
    }

    if (galleryOptions.random_sorting == 1) {
        $container.ba_isotope('shuffle');
    }
    if (pagination) {
        pagination = JSON.parse(pagination);
    }

    function setImgCount(obj, desktop, tablet, pl, pt)
    {
        let count = obj[desktop];
        if (winSize < 1024 && winSize >= 768) {
            count = obj[tablet];
        } else if (winSize <= 767 && winSize >= 480) {
            count = obj[pl];
        } else if (winSize < 480) {
            count = obj[pt];
        }

        return count * 1;
    }

    function setWidthContent(count, wrapper, s)
    {
        return Math.floor((wrapper.width() * 1 - (s * (count - 1))) / count);
    }
    
    function getWidthContent()
    {
        imgC = setImgCount(galleryOptions, 'column_number', 'tablet_numb', 'phone_land_numb', 'phone_port_numb');
        widthContent = setWidthContent(imgC, $container, galleryOptions.image_spacing);
        aimgC = setImgCount(albumOptions, 'album_column_number', 'album_tablet_numb', 'album_phone_land_numb', 'album_phone_port_numb');
        albumWidth = setWidthContent(aimgC, album, albumOptions.album_image_spacing);
    }

    function checkHash()
    {
        if (window.location.href.indexOf('#') > 0) {
            window.history.replaceState(null, null, window.location.href.replace('#'+window.location.hash, ''))
        }
    }

    async function chechAlbumItems(a)
    {
        var title = a.find('h3').text(),
            alias = a.find('a').attr('data-href'),
            oldCategory = category,
            filter = a.attr('data-filter');
        if (checkPassword(a.attr('data-id'), alias)) {
            return false;
        }
        if (albumOptions.album_enable_lightbox == 1 && a.hasClass('root')) {
            gallery.find('.ba-goback a').hide();
        } else {
            gallery.find('.ba-goback a').css('display', '');
        }
        gallery.find('.ba-goback h2').text(title);
        await setCategoryDescription(filter);
        category = filter;
        if (category != '.root' && albumOptions.album_enable_lightbox == 1 && oldCategory == '.root' && pageRefresh != 1) {
            gallery.next().height(gallery.height());
        }
        if (pagination) {
            currentPage = '.page-1'
            drawPagination();
        }
        if (!pagination || (pagination && galleryOptions.pagination_type != 'infinity')) {
            gallery.trigger('scroll');
        }
        if (albumOptions.album_enable_lightbox != 1 && galleryOptions.disable_auto_scroll != 1) {
            var position = gallery.offset().top;
            jQuery('html, body').stop().animate({
                scrollTop: position
            }, 'slow');
        }
        if (albumOptions.album_enable_lightbox == 1) {
            if (category == '.root') {
                gallery.removeClass('album-in-lightbox');
                document.body.classList.remove('album-in-lightbox-open');
            } else {
                gallery.addClass('album-in-lightbox');
                document.body.classList.add('album-in-lightbox-open');
                gallery[0].scrollTop = 0;
            }
        }
    }

    async function albumItemsAction(item)
    {
        var alias = item.find('a').attr('data-href');
        if (checkPassword(item[0].dataset.id, alias)) {
            return false;
        }
        checkHash();
        if (pageRefresh == 1) {
            refreshPage(alias)
            gallery.find('.ba-pagination').hide();
        } else {
            window.history.replaceState(null, null, alias);
            await chechAlbumItems(item);
            resizeIsotope();
        }
    }

    gallery.find('.ba-album-items').on('click', function(){
        albumItemsAction(jQuery(this));
    });

    gallery.find('.ba-album-items.root').on('click', function(){
        if (albumOptions.album_enable_lightbox == 1 && pageRefresh == 1) {
            gallery.next().height(gallery.height());
        }
    });

    gallery.find('.ba-goback a').on('click', function(){
        checkHash();
        if (category == '.root') {
            var flag = false;
        } else {
            var catName = album.find('div[data-filter="'+category+'"]')[0].className,
                array = catName.split(' ');
                flag = false;
            for (var i = 0; i < array.length; i++) {
                if (array[i].indexOf('category-') != -1) {
                    catName = array[i];
                }
            }
            for (var i = 0; i < catNames.length; i ++) {
                if (catName == catNames[i].replace('.', '')) {
                    album.find('div[data-filter="'+catNames[i]+'"]').trigger('click');
                    flag = true;
                    break;
                }
            }
        }
        if (!flag) {
            category = '.root';
            var alias = gallery.find('.current-root').val();
            if (pageRefresh == 1) {
                if (alias != window.location.href) {
                    refreshPage(alias)
                    gallery.find('.ba-pagination').hide();
                }
            } else {
                window.history.replaceState(null, null, alias);
                if (pagination) {
                    currentPage = '.page-1';
                    addPages();
                    drawPagination();
                }
                resizeIsotope();
            }
        }
    });

    function removeFilterPasswordsImages(str)
    {
        if (str == '.category-0') {
            for (var ind in galleryApp.passwords) {
                if (!galleryApp.passwords[ind]) {
                    str += ':not('+gallery.find('.category-filter a[data-id="'+ind+'"]').attr('data-filter')+')';
                }
            }
        }

        return str;
    }
    
    async function filterAction(a)
    {
        category = removeFilterPasswordsImages(category);
        var oldActive = gallery.find('.ba-filter-active'),
            newActive = a,
            filter = a.attr('data-filter'),
            alias = a.attr('data-href');
        if (checkPassword(a.attr('data-id'), alias)) {
            return false;
        }
        oldActive.removeClass('ba-filter-active');
        oldActive.addClass('ba-filter');
        newActive.removeClass('ba-filter');
        newActive.addClass('ba-filter-active');
        gallery.find('.ba-select-filter option').each(function(){
            if (galleryApp.$g(this).val() == filter) {
                galleryApp.$g(this).attr('selected', true);
            } else {
                galleryApp.$g(this).removeAttr('selected');
            }
        });
        gallery.find('.gallery-tag.active').removeClass('active');
        gallery.find('.gallery-color.active').removeClass('active');
        $container.find('.ba-gallery-items').hide();
        await setCategoryDescription(filter);
        category = filter;
        category = removeFilterPasswordsImages(category);
        if (pagination) {
            currentPage = '.page-1'
            addPages();
            drawPagination();
        }
    }

    function tagsAction(a)
    {
        if (!a.classList.contains('active')) {
            galleryApp.fetch(JUri+'index.php?option=com_bagallery&task=gallery.setTagHit', {
                id: a.dataset.id
            });
            a.classList.add('active');
        } else {
            a.classList.remove('active');
        }
        setColorsTagsFilter();
        $container.find('.ba-gallery-items').hide();
        if (pagination) {
            currentPage = '.page-1'
            addPages();
            drawPagination();
        }
    }

    function includeFilter(items, str, search, sub)
    {
        items.each(function(){
            str += (str ? ', ' : '')+removeFilterPasswordsImages(search)+sub+this.dataset.id;
        });

        return str;
    }

    function excludeFilter(items, str, search, sub)
    {
        items.each(function(){
            str += (!str ? removeFilterPasswordsImages(search) : '')+sub+this.dataset.id;
        });

        return str;
    }

    function setColorsTagsFilter()
    {
        var match = category.match(/category-\d+/),
            cat = category == '.root' ? 'root' : match[0];
        cat = '.'+cat;
        if (galleryOptions.tags_method == 'include' && galleryOptions.colors_method == 'include') {
            category = includeFilter(gallery.find('.gallery-tag.active'), '', cat, '.ba-tag-');
            category = includeFilter(gallery.find('.gallery-color.active'), category, cat, '.ba-color-');
        } else if (galleryOptions.colors_method == 'include') {
            category = excludeFilter(gallery.find('.gallery-tag.active'), '', cat, '.ba-tag-');
            category = includeFilter(gallery.find('.gallery-color.active'), category, cat, '.ba-color-');
        } else if (galleryOptions.tags_method == 'include') {
            category = excludeFilter(gallery.find('.gallery-color.active'), '', cat, '.ba-color-');
            category = includeFilter(gallery.find('.gallery-tag.active'), category, cat, '.ba-tag-');
        } else {
            category = excludeFilter(gallery.find('.gallery-color.active'), '', cat, '.ba-color-');
            category = excludeFilter(gallery.find('.gallery-tag.active'), category, cat, '.ba-tag-');
        }
        if (!category) {
            category = removeFilterPasswordsImages(cat);
        }
    }

    function colorsAction(a)
    {
        if (!a.classList.contains('active')) {
            galleryApp.fetch(JUri+"index.php?option=com_bagallery&task=gallery.setColorHit", {
                id : a.dataset.id
            });
            a.classList.add('active');
        } else {
            a.classList.remove('active');
        }
        setColorsTagsFilter();
        $container.find('.ba-gallery-items').hide();
        if (pagination) {
            currentPage = '.page-1'
            addPages();
            drawPagination();
        }
    }

    gallery.find('a.gallery-tag').on('click', function(event){
        event.preventDefault();
        var alias = this.dataset.href,
            href = window.location.href,
            search = this.dataset.alias,
            pos = href.indexOf(search);
        if (pos != -1) {
            var symbol = href[pos - 1],
                start = href.substring(0, pos - 1),
                end = href.substring(pos + search.length);
            if (symbol == '?' && end) {
                end = '?'+end.substring(1);
            }
            alias = start+end;
        } else {
            if (href.indexOf('?') != -1) {
                alias = href += '&'+this.dataset.alias;
            } else {
                alias = href += '?'+this.dataset.alias;
            }
        }
        if (alias.indexOf('ba-page=') != -1) {
            var match = alias.match(/\Wba-page=\d+/);
            alias = alias.replace(match[0], '');
        }
        if (pageRefresh == 1) {
            if (alias != window.location.href) {
                refreshPage(alias)
            }
        } else {
            window.history.replaceState(null, null, alias);
            tagsAction(this);
            resizeIsotope();
        }
    });

    gallery.find('a.gallery-color').on('click', function(event){
        event.preventDefault();
        var alias = this.dataset.href,
            href = window.location.href,
            search = this.dataset.alias,
            pos = href.indexOf(search);
        if (pos != -1) {
            var symbol = href[pos - 1],
                start = href.substring(0, pos - 1),
                end = href.substring(pos + search.length);
            if (symbol == '?' && end) {
                end = '?'+end.substring(1);
            }
            alias = start+end;
        } else {
            if (href.indexOf('?') != -1) {
                alias = href += '&'+this.dataset.alias;
            } else {
                alias = href += '?'+this.dataset.alias;
            }
        }
        if (alias.indexOf('ba-page=') != -1) {
            var match = alias.match(/\Wba-page=\d+/);
            alias = alias.replace(match[0], '');
        }
        if (pageRefresh == 1) {
            if (alias != window.location.href) {
                refreshPage(alias)
            }
        } else {
            window.history.replaceState(null, null, alias);
            colorsAction(this);
            resizeIsotope();
        }
    });

    gallery.find('.ba-reset-filter a').on('click', function(event){
        event.preventDefault();
        var href = window.location.href;
        gallery.find('.active.gallery-tag').each(function(ind){
            var search = this.dataset.alias,
                pos = href.indexOf(search),
                start = href.substring(0, pos - 1),
                end = href.substring(pos + search.length);
            href = start+end;
            if (pageRefresh != 1) {
                tagsAction(this);
            }
        });
        gallery.find('.active.gallery-color').each(function(ind){
            var search = this.dataset.alias,
                pos = href.indexOf(search),
                start = href.substring(0, pos - 1),
                end = href.substring(pos + search.length);
            href = start+end;
            if (pageRefresh != 1) {
                colorsAction(this);
            }
        });
        if (pageRefresh != 1 && href != window.location.href) {
            resizeIsotope();
            window.history.replaceState(null, null, href);
        } else if (href != window.location.href) {
            refreshPage(href)
        }
    });

    gallery.find('.show-filter-modal').on('click', function(event){
        event.preventDefault();
        var div =  gallery.find('.equal-positions-tags').addClass('visible-filter-modal'),
            height = div.height();
        div.css('margin-top', (window.innerHeight - height) / 2);
        document.body.classList.add('filter-modal-open');
    });

    gallery.find('.close-filter-modal').on('click', function(event){
        event.preventDefault();
        gallery.find('.ba-reset-filter a').trigger('click');
        gallery.find('.apply-filter-modal').trigger('click');
    });

    gallery.find('.apply-filter-modal').on('click', function(event){
        event.preventDefault();
        gallery.find('.equal-positions-tags').removeClass('visible-filter-modal').css('margin-top', '');
        setTimeout(function(){
            document.body.classList.remove('filter-modal-open');
        }, 300);
    });

    catModal.find('.category-password').on('input', function(){
        if (this.value.trim()) {
            catModal.find('.apply-category-password').removeClass('disable-button').addClass('active-button');
        } else {
            catModal.find('.apply-category-password').addClass('disable-button').removeClass('active-button');
        }
    });
    
    catModal.find('[data-dismiss="modal"]').on('click', function(event){
        event.preventDefault();
        catModal.ba_modal('hide');
    });

    catModal.find('.apply-category-password').on('click', function(event){
        event.preventDefault();
        if (this.classList.contains('active-button')) {
            var id = this.dataset.id,
                alias = this.dataset.alias,
                password = catModal.find('.category-password').val().trim();
            galleryApp.fetch(JUri+'index.php?option=com_bagallery&task=gallery.matchCategoryPassword', {
                id : id,
                password: password
            }).then(function(text){
                if (text == 'match') {
                    galleryApp.passwords[id] = true;
                    catModal.ba_modal('hide');
                    if (gallery.find('.ba-album-items').length > 0) {
                        gallery.find('.ba-album-items a[data-href="'+alias+'"]').closest('.ba-album-items').trigger('click');
                    } else {
                        gallery.find('[data-password][data-id="'+id+'"]').trigger('click');
                    }
                } else {
                    galleryApp.notification.show(galleryApp._('INCORRECT_PASSWORD'), 'ba-alert');
                }
            });
        }
    });

    catModal.on('hide', function(){
        var id = catModal.find('.apply-category-password').attr('data-id'),
            url = gallery.find('.current-root').val();
        catModal.parent().addClass('modal-scrollable-out');
        document.body.classList.remove('category-password-modal-open');
        setTimeout(function(){
            catModal.parent().removeClass('modal-scrollable-out');
        }, 500);
        if (!galleryApp.passwords[id] && window.location.href != url) {
            window.history.replaceState(null, null, url);
        }
    });

    function checkPassword(id, alias)
    {
        $flag = false;
        if (id && typeof(galleryApp.passwords[id]) != 'undefined' && !galleryApp.passwords[id]) {
            catModal.find('.category-password').val('');
            catModal.find('.apply-category-password').addClass('disable-button')
                .removeClass('active-button').attr('data-id', id).attr('data-alias', alias);
            catModal.ba_modal();
            document.body.classList.add('category-password-modal-open');
            $flag = true;
        }

        return $flag;
    }

    async function categoryFilterAction(event)
    {
        event.preventDefault();
        var $this = jQuery(this),
            alias = $this.attr('data-href');
        if (checkPassword(this.dataset.id, alias)) {
            return false;
        }
        checkHash();
        if (pageRefresh == 1) {
            refreshPage(alias);
        } else {
            window.history.replaceState(null, null, alias);
            await filterAction($this);
            resizeIsotope();
        }
    }

    gallery.find('.category-filter a:not(.show-filter-modal)').off('click').on('click', categoryFilterAction);

    if (pagination && galleryOptions.pagination_type == 'infinity' && gallery.find('.active-category-image').length > 0) {
        infinity.activeImage = window.location.href;
    } else {
        locationImage();
    }
    checkFilter();

    async function checkFilter()
    {
        var filterFlag = false,
            search = location.href,
            pos = search.indexOf('ba-page'),
            albumItems = gallery.find('.ba-album-items'),
            filterItems = gallery.find('.category-filter a');
        if (pos != -1) {
            search = search.substr(0, pos - 1);
        } else {
            if (search.indexOf('?') > 0) {
                search = search.split('?');
                search = search[0]+'?'+search[1];
            }
        }
        if (!location.search) {
            if (albumItems.length > 0 ) {
                category = '.root';
                if (gallery.hasClass('album-in-lightbox')) {
                    if (pageRefresh == 1) {
                        var alias = gallery.find('.current-root').val();
                        refreshPage(alias);
                        gallery.find('.ba-pagination').hide();
                    }
                }
            } else if (filterItems.length > 0) {
                await filterAction(gallery.find('.category-filter [data-filter="'+defaultCat+'"]'));
            }
        } else {
            if (gallery.find('.active-category-image').length > 0) {
                search = gallery.find('.active-category-image').val();
            }
            if (albumItems.length > 0 ) {
                var a = albumItems.find('a[data-href="'+search+'"]');
                if (a.length > 0) {
                    await chechAlbumItems(a.closest('.ba-album-items'));
                    filterFlag = true;
                }
                if (!filterFlag) {
                    category = '.root';
                }
            } else if (filterItems.length > 0) {
                var a = gallery.find('.category-filter a[data-href="'+search+'"]');
                if (a.length > 0) {
                    await filterAction(a);
                    filterFlag = true;
                }
                if (!filterFlag) {
                    category = defaultCat;
                }
            }
            if (!category) {
                category = '.category-0';
            }
            var array = search.split('?');
            if (array[1]) {
                array = array[1].split('&');
                gallery.find('a.gallery-tag').each(function(){
                    if (array.indexOf(this.dataset.alias) != -1) {
                        tagsAction(this);
                    }
                });
                gallery.find('a.gallery-color').each(function(){
                    if (array.indexOf(this.dataset.alias) != -1) {
                        colorsAction(this);
                    }
                });
            }
        }
    }        

    async function setCategoryDescription(filter)
    {
        if (!categories) {
            return;
        }
        const category_id = filter.substring(10) * 1;
        let description = '';
        categories.forEach((category) => {
            if (category.category_id != category_id) {
                return;
            }
            description = category.description;
        });
        description = await checkForms(description);
        gallery.find('.categories-description').html(description);
    }

    async function checkForms(data)
    {
        let div = document.createElement('div'),
            src;
        div.innerHTML = data;
        div.querySelectorAll('img').forEach(function(img){
            src = img.getAttribute('src');
            if (src.indexOf(gallery_image_path) == 0) {
                img.src = JUri+src;
            } else if (src.indexOf('/'+gallery_image_path) == 0) {
                img.src = JUri+src.substr(1);
            }
        });
        data = div.innerHTML;
        if (data.indexOf('baforms ID=') > 0) {
            await galleryApp.fetch(JUri+"index.php?option=com_bagallery&view=gallery&task=gallery.checkForms", {
                data : data,
            }).then(function(text){
                data = text;
            });
        }

        return data;
    }
    
    gallery.find('.ba-select-filter').on('change', function(){
        var filter = galleryApp.$g(this).val(),
            newActive = gallery.find('.category-filter a[data-filter="'+filter+'"]');
        newActive.trigger('click');
    });
    
    function addCaptionStyle()
    {
        if (!category) {
            category = '.category-0';
        }
        if (gallery.find('.ba-gallery-grid').hasClass('css-style-12')) {
            galleryApp.direction.set(gallery.find('.ba-gallery-items'));
        }
        if (album.hasClass('css-style-12')) {
            galleryApp.direction.set(album.find('.ba-album-items'));
        }
    }
    
    function hexToRgb(hex)
    {
        var result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
        return result ? {
            r: parseInt(result[1], 16),
            g: parseInt(result[2], 16),
            b: parseInt(result[3], 16)
        } : null;
    }

    function refreshPage(href)
    {
        var div = document.createElement('div'),
            sBackdrop = jQuery('<div/>', {
                'class' : 'saving-backdrop'
            }),
            img = document.createElement('img');
        img.src = JUri+'components/com_bagallery/assets/images/reload.svg';
        document.body.appendChild(sBackdrop[0]);
        document.body.appendChild(img);
        if (href != window.location.href) {
            window.history.replaceState(null, null, href);
        }
        jQuery(div).load(href+' .ba-gallery[data-gallery="'+gallery.attr('data-gallery')+'"]', {
            baPasswords : galleryApp.passwords
        }, function(){
            setTimeout(function(){
                document.body.classList.remove('filter-modal-open');
            }, 300);
            sBackdrop.className += ' animation-out';
            setTimeout(function(){
                document.body.removeChild(sBackdrop[0]);
                document.body.removeChild(img);
            }, 300);
            galleryModal.parent().remove();
            if (!albumMode && galleryOptions.disable_auto_scroll != 1) {
                var position = $container.offset().top;
                galleryApp.$g('html, body').stop().animate({
                    scrollTop: position
                }, 'slow');
            }
            if (gallery.hasClass('album-in-lightbox')) {
                var height = gallery.next().height();
                jQuery(div).find('.ba-gallery').height(height);
            }
            jQuery('body').removeClass('album-in-lightbox-open');
            gallery.replaceWith(div.innerHTML);
            initGallery(jQuery('.ba-gallery[data-gallery="'+gallery.attr('data-gallery')+'"]')[0]);
        });
    }
    
    function drawPagination()
    {
        if (pageRefresh == 1) {
            gallery.find('.ba-pagination a').off('click').on('click', function(event){
                if (this.classList.contains('ba-dissabled') || this.classList.contains('ba-current')) {
                    event.preventDefault();
                } else if (!this.classList.contains('scroll-to-top')) {
                    event.preventDefault();
                    refreshPage(this.dataset.href);
                }
            });
            return false;
        }
        var page = 1,
            n = 0;
        addPages();
        gallery.find('.ba-gallery-items'+category).each(function(){
            if (n == pagination.images_per_page) {
                n = 1;
                page++;
            } else {
                n++;
            }
        });
        paginationPages = page;
        let paginator = gallery.find('.ba-pagination'),
            str = '';
        paginator.empty();
        if (page == 1 || gallery.find('.ba-gallery-items'+category).length == 0) {
            resizeIsotope();
            return false;
        }
        if (pagination.pagination_type != 'infinity') {
            if (pagination.pagination_type != 'load') {
                str = '<a class="ba-btn ba-first-page ba-dissabled"';
                str += ' style="display:none;"';
                str += '><span class="zmdi zmdi-skip-previous"></span></a>';
                str += '<a class="ba-btn ba-prev';
                if (pagination.pagination_type != 'slider') {
                    str += ' ba-dissabled';
                }
                str += '" style="display:none;"><span class="zmdi zmdi-play"></span></a>';
                for (var i = 0; i < page; i++) {
                    str += '<a class="ba-btn';
                    if (i == 0) {
                        str += ' ba-current';
                    }
                    str += '" style="display:none;">'+(i + 1)+'</a>';
                }
                str += '<a class="ba-btn ba-next" style="display:none;"><span class="zmdi zmdi-play"></span></a>';
                str += '<a class="ba-btn ba-last-page"';
                str += ' style="display:none"';
                str += '><span class="zmdi zmdi-skip-next"></span></a>';
            } else {
                str = '<a class="ba-btn load-more" style="display:none;">'+paginationConst[2]+'</a>';
            }
        }
        paginator.html(str);
        addPaginationFilter();
        gallery.find('.ba-pagination a').on('click', function(event){
            event.preventDefault();
            if (jQuery(this).hasClass('ba-dissabled')) {
                return false;
            }
            var button = galleryApp.$g(this);
            paginationAction(button);
            checkPaginator();
            gallery.trigger('scroll');
        });
    }

    function checkPaginator()
    {
        var paginator = gallery.find('.ba-pagination');
        if (paginator.find('a').length == 0) {
            paginator.addClass('ba-empty');
        } else {
            paginator.removeClass('ba-empty');
        }
        if (pagination.pagination_type == 'default') {
            var current,
                curInd = 0,
                pagButtons = paginator.find('a').not('.ba-first-page, .ba-last-page, .ba-prev, .ba-next');
            paginator.find('.ba-first-page, .ba-last-page, .ba-prev, .ba-next').css('display', 'inline-block');
            if (pagButtons.length >= 5) {
                pagButtons.each(function(ind, el){
                    if (jQuery(this).hasClass('ba-current')) {
                        current = jQuery(this);
                        curInd = ind;
                        return false;
                    }
                });
                if (curInd <= 2) {
                    pagButtons.each(function(ind, el){
                        if (ind < 5) {
                            jQuery(this).css('display', 'inline-block');
                        } else {
                            jQuery(this).hide();
                        }
                    });
                } else if (curInd + 1 > pagButtons.length - 3) {
                    for (var i = pagButtons.length - 1; i >= 0; i--) {
                        if (i >= pagButtons.length - 5) {
                            jQuery(pagButtons[i]).css('display', 'inline-block');
                        } else {
                            jQuery(pagButtons[i]).hide();
                        }
                    }
                } else {
                    pagButtons.hide();
                    current.css('display', 'inline-block').prev().css('display', 'inline-block')
                        .prev().css('display', 'inline-block');
                    current.next().css('display', 'inline-block').next().css('display', 'inline-block');
                }
            } else {
                pagButtons.css('display', 'inline-block');
            }
        } else if (pagination.pagination_type == 'load') {
            paginator.find('a').css('display', 'inline-block');
        } else if (pagination.pagination_type == 'slider') {
            paginator.find('.ba-prev, .ba-next').css('display', 'inline-block');
        }
    }

    function setThumbnailsSizes(wrapper, selector, width, space, layout)
    {
        if (layout != 'justified') {
            wrapper.find(selector).width(width);
            wrapper.find(selector).height(width);
        }
        if (layout == 'metro') {
            wrapper.find('.width2').css('width', width * 2 + (space * 1)+'px');
            wrapper.find('.height2').css('height', width * 2 + (space * 1)+'px');
            wrapper.find('.height2 img').css('height', width * 2 + (space * 1)+'px');
            wrapper.find('.width2:not(.height2) img').css('height', width+'px');
        } else if (layout == 'masonry') {
            wrapper.find('.height2').css('height', width * 2 + (space * 1)+'px');
            wrapper.find('.height2 img').css('height', width * 2 + (space * 1)+'px');
        } else if (layout == 'square') {
            wrapper.find('.width2').css('width', width * 2 + (space * 1)+'px');
            wrapper.find('.height2').css('height', width * 2 + (space * 1)+'px');
            wrapper.find('.height2 img').css('height', width * 2 + (space * 1)+'px');
        } else if (layout == 'random') {
            wrapper.find(selector).height('auto');
            wrapper.find(selector+', '+selector+' img').width(width);
            wrapper.find(selector+' img').each(function(){
                let ratio = this.dataset.width / this.dataset.height;
                this.style.height = Math.round(width / ratio)+'px';
            });
        }
        if (winSize <= 480) {
            wrapper.find('.width2.height2').width(width).height(width);
            wrapper.find('.width2.height2 img').height(width);
            wrapper.find('.width2').not('.height2').width(width).height(Math.round(width / 2));
            wrapper.find('.width2').not('.height2').find('img').height(Math.round(width / 2));
        }
    }
    
    function setSize()
    {
        setThumbnailsSizes($container, '.ba-gallery-items', widthContent, galleryOptions.image_spacing, layout);
        setThumbnailsSizes(album, '.ba-album-items', albumWidth, albumOptions.album_image_spacing, albumOptions.album_layout);
    }
    
    function resizeIsotope()
    {
        winSize = window.innerWidth;
        getWidthContent();
        setSize();
        if (pageRefresh == 1 && pagination && pagination.pagination_type != 'infinity') {
            currentPage = '';
        }
        if (albumMode) {
            album.ba_isotope({
                filter: category,
                margin : albumOptions.album_image_spacing,
                count : aimgC,
                mode: albumOptions.album_layout
            });
            if (category == '.root') {
                gallery.find('.ba-goback').hide();
            }
        }
        if (pagination && pagination.pagination_type != 'infinity') {
            if (pagination.pagination_type != 'load') {
                var array = category.split(', '),
                    str = '';
                for (var i = 0; i < array.length; i++) {
                    array[i] += currentPage;
                }
                str = array.join(', ');
                $container.ba_isotope({
                    filter: str,
                    margin : galleryOptions.image_spacing,
                    count : imgC,
                    mode : layout
                });
            } else {
                var array = category.split(', '),
                    page = currentPage.replace(new RegExp('.page-', 'g'), ''),
                    current = '';
                for (var j = 0; j < array.length; j++) {
                    if (current) {
                        current += ', ';
                    }
                    for (var i = 1; i <= page; i++) {
                        current += array[j]+'.page-'+i;
                        if (i != page) {
                            current += ', ';
                        }
                    }
                }
                $container.ba_isotope({
                    filter: current,
                    margin : galleryOptions.image_spacing,
                    count : imgC,
                    mode : layout
                });
            }
        } else {
            if (pagination && galleryOptions.pagination_type == 'infinity') {
                getInfinityImages();
            } else {
                $container.ba_isotope({
                    filter: category,
                    margin : galleryOptions.image_spacing,
                    count : imgC,
                    mode : layout
                });
            }
        }
        setTimeout(function(){
            gallery.css('height', '');
        }, 500);
        if (!pagination || (pagination && galleryOptions.pagination_type != 'infinity')) {
            gallery.trigger('scroll');
        }
    }

    $container.on('show_isotope', function(){
        gallery.find('.category-filter').show();
        if (pagination) {
            checkPaginator();
        }
        if (category != '.root') {
            gallery.find('.ba-goback').show();
        }
    });
    
    galleryApp.$g('a[data-toggle="tab"], [data-uk-tab]').on('shown shown.bs.tab change.uk.tab', function(){
        setTimeout(function(){
            resizeIsotope();
            jQuery(window).trigger('resize');
        }, 100);
    });

    jQuery('a[data-toggle="tab"]').on('click', function(){
        setTimeout(function(){
            resizeIsotope();
        }, 1);
    });

    jQuery('body').on('click.tablist', 'div[role="tablist"]', () => {
        setTimeout(function(){
            resizeIsotope();
        }, 300);
    });

    jQuery('ul[data-uk-switcher] li a, .accordion .slide, div[role="tab"][data-rlta-state], div[role="button"][data-rlta-state]').on('click', function(){
        setTimeout(function(){
            resizeIsotope();
        }, 300);
    });

    jQuery('a.uk-accordion-title').on('click', function(){
        setTimeout(function(){
            resizeIsotope();
        }, 1);
    });

    jQuery('.sppb-nav li a').on('click', function(){
        setTimeout(function(){
            resizeIsotope();
        }, 200);
    });
    
    galleryApp.$g(window).on('resize.isotope', function(){
        clearTimeout(bagallery.resizeDelay);
        bagallery.resizeDelay = setTimeout(function(){
            if (winSize != window.innerWidth) {
                resizeIsotope();
                if (galleryModal.find('.header-icons').length == 0) {
                    return false;
                }
            }
        }, 100);
    });
    
    function paginationAction(button)
    {
        if (pagination.pagination_type != 'load') {
            var next = button.attr('data-filter');
            if (currentPage == next) {
                return false;
            }
            currentPage = next;
            gallery.find('.ba-current').removeClass('ba-current');
            gallery.find('.ba-pagination [data-filter="'+next+'"]').each(function(){
                if (!galleryApp.$g(this).hasClass('ba-prev') && !galleryApp.$g(this).hasClass('ba-next')
                    && !galleryApp.$g(this).hasClass('ba-first-page') && !galleryApp.$g(this).hasClass('ba-last-page')) {
                    galleryApp.$g(this).addClass('ba-current');
                }
            });
            var prev = next.substr(6) - 1;
            if (prev == 0) {
                prev = 1;
                if (pagination.pagination_type == 'slider') {
                    prev = paginationPages;
                } else {
                    gallery.find('.ba-prev').addClass('ba-dissabled');
                    gallery.find('.ba-first-page').addClass('ba-dissabled');
                }
            } else {
                gallery.find('.ba-prev').removeClass('ba-dissabled');
                gallery.find('.ba-first-page').removeClass('ba-dissabled');
            }
            next = next.substr(6);
            next = next * 1 + 1;
            if (next > paginationPages) {
                next = next - 1;
                if (pagination.pagination_type == 'slider') {
                    next = 1;
                } else {
                    gallery.find('.ba-next').addClass('ba-dissabled');
                    gallery.find('.ba-last-page').addClass('ba-dissabled');
                }
            } else {
                gallery.find('.ba-next').removeClass('ba-dissabled');
                gallery.find('.ba-last-page').removeClass('ba-dissabled');
            }
            gallery.find('.ba-prev').attr('data-filter', '.page-'+prev);
            gallery.find('.ba-next').attr('data-filter', '.page-'+next);
            if (galleryOptions.disable_auto_scroll != 1) {
                var position = $container.offset().top,
                    target = jQuery('html, body');
                if (gallery.hasClass('album-in-lightbox')) {
                    target = gallery;
                    position = 0;
                }
                target.stop().animate({
                    scrollTop: position
                }, 'slow');
            }
        } else {
            var next = button.attr('data-filter');
            currentPage = next;
            next = next.substr(6);
            if (next < paginationPages) {
                next = next * 1 + 1;
                button.attr('data-filter', '.page-'+next);
            } else {
                button.removeClass('load-more').addClass('scroll-to-top');
                button.text(paginationConst[3]);
                var position = gallery.offset().top,
                    target = jQuery('html, body');
                if (gallery.hasClass('album-in-lightbox')) {
                    target = gallery;
                    position = 0;
                }
                button.on('click', function(){
                    target.stop().animate({
                        scrollTop: position
                    }, 'slow');
                });
            }
        }
        resizeIsotope();
    }
    
    function addPaginationFilter()
    {
        var n = 1;
        if (pagination.pagination_type != 'load' && pagination.pagination_type != 'infinity') {
            gallery.find('.ba-pagination a').not('.ba-first-page, .ba-prev, .ba-next, .ba-last-page').each(function(){
                galleryApp.$g(this).attr('data-filter', '.page-'+n);
                n++;
            });
            n--;
            gallery.find('.ba-prev').attr('data-filter', '.page-1');
            gallery.find('.ba-first-page').attr('data-filter', '.page-1');
            gallery.find('.ba-last-page').attr('data-filter', '.page-'+n);
            if (paginationPages != 1) {
                gallery.find('.ba-next').attr('data-filter', '.page-2');
            } else {
                gallery.find('.ba-next').attr('data-filter', '.page-1');
            }
        } else {
            if (paginationPages != 1) {
                gallery.find('.ba-pagination a').attr('data-filter', '.page-2');
            } else {
                gallery.find('.ba-pagination a').attr('data-filter', '.page-1');
            }
        }
    }
    
    function addPages()
    {
        removePages();
        var page = 1,
            items = gallery.find('.ba-gallery-grid '+category)
            n = 0;
        if (pageRefresh == 1) {
            items.addClass('page-'+page);
            return false;
        }
        items.each(function(ind, elem){
            if (n < pagination.images_per_page) {
                galleryApp.$g(this).addClass('page-'+page);
                n++;
            } else {
                n = 0;
                page++;
                galleryApp.$g(this).addClass('page-'+page);
                n++;
            }
        });
    }
    
    function removePages()
    {
        var len = gallery.find('.ba-gallery-items').length,
            n = Math.ceil(len / pagination.images_per_page) + 1;
        for (var i = 1; i <= n; i++) {
            gallery.find('.ba-gallery-items').removeClass('page-'+i);
        }
    }

    if (style.disable_lightbox == 0) {
        gallery.find('.ba-gallery-grid').on('click', '.ba-gallery-items', function(){
            image = galleryApp.$g(this).find('.image-id').val();
            var item = JSON.parse(image);
            if (item.link == '') {
                elements = getData();
                showOptions();
                galleryModal.ba_modal();
                originalLocation = window.location.href;
                addModalEvents(this);
            }
        });
    }
    galleryModal.on('hide', function(){
        galleryModal.parent().addClass('hide-animation');
        setTimeout(function(){
            galleryModal.parent().removeClass('hide-animation');
            hideOptions();
            galleryModal.removeClass('ba-description-left ba-description-right');
        }, 500);
    });

    function resizeModal(body)
    {
        if (body.find('.modal-image').hasClass('embed')) {
            setVideoSize(body, null, false);
        } else if (style.auto_resize == 1 || window.innerWidth <= 1024) {
            let id = body.attr('data-id'),
                animate = style.auto_resize == 1;
            setAutoSize(body, null, loadedImages[id], animate, false, true);
            body.addClass('ba-resize');
        } else {
            setNotAutoSize(body, null, false);
            body.removeClass('ba-resize');
        }
    }
    
    function addModalEvents($this)
    {
        imageIndex = elements.indexOf(image);
        galleryModal.parent().find('.modal-nav').show();
        setImage(image, imagesArray[imageIndex]);
        galleryModal.parent().find('.modal-nav .ba-left-action').on('click', function(){
            getPrev();
        });
        galleryModal.on('mousedown', function(event){
            if (galleryApp.$g(event.srcElement).hasClass('gallery-modal')) {
                galleryModal.ba_modal('hide');
            }                
        });
        galleryModal.parent().find('.modal-nav .ba-right-action').on('click', function(){
            getNext();
        });
        galleryModal.find('.ba-icon-close').on('click', function(event){
            event.preventDefault();
            event.stopPropagation();
            galleryModal.ba_modal('hide');
        });        
        galleryModal.find('.ba-modal-header .ba-like-wrapper').on('click', function(event){
            event.stopPropagation();
            this.classList.add('likes-animation');
            setTimeout(function(){
                galleryModal.find('.ba-modal-header .ba-like-wrapper').removeClass('likes-animation');
            }, 300);
            likeImage();
        });
        galleryModal.find('.zmdi.zmdi-share').on('click', function(event){
            event.stopPropagation();
            event.preventDefault();
            var aimDelay = 0;
            galleryModal.find('.ba-share-icons').addClass('visible-sharing').one('click', function(){
                setTimeout(function(){
                    galleryModal.find('.ba-share-icons').addClass('sharing-out');
                    setTimeout(function(){
                        galleryModal.find('.ba-share-icons').removeClass('sharing-out visible-sharing');
                    }, 500);
                }, 100);
            }).find('i').each(function(){
                jQuery(this).css('animation-delay', aimDelay+'s');
                aimDelay += 0.1;
            });
            return false;
        });
        galleryApp.$g(window).on('keyup', function(event) {
            event.preventDefault();
            event.stopPropagation();
            if (event.keyCode === 37) {
                getPrev();
            } else if (event.keyCode === 39) {
                getNext();
            } else if (event.keyCode === 27) {
                galleryModal.ba_modal('hide');
                galleryModal.find('.ba-share-icons').removeClass('visible-sharing')
            }
        });
        galleryApp.$g('body').on('touchstart.bagallery', galleryApp.touches.start).on('touchend.bagallery', function(event){
            if (window.visualViewport.scale == 1 && zoomClk == 1) {
                let touch = event.originalEvent.changedTouches[0],
                    x = galleryApp.touches.touch.x - touch.screenX,
                    y = galleryApp.touches.touch.y - touch.screenY,
                    xabs = Math.abs(x),
                    yabs = Math.abs(y);
                if (x >= 100 && xabs >= yabs * 2) {
                    getNext();
                } else if (x <= -100 && xabs >= yabs * 2) {
                    getPrev();
                }
            }
        });
        galleryApp.$g(window).on('resize.bagallery', function(){
            bagallery.delay = setTimeout(function(){
                galleryModal.find('.ba-modal-body').each(function(){
                    resizeModal(galleryApp.$g(this));
                });
            }, 300);
        });
    }
    
    function showOptions()
    {
        let scroll = window.innerWidth - document.documentElement.offsetWidth;
        document.body.parentNode.style.setProperty('--gallery-scroll-width', scroll+'px');
        galleryApp.$g('body').addClass('modal-open');
        galleryModal.parent().addClass('ba-scrollable');
        goodHeight = window.innerHeight - 100;
        goodWidth = goodHeight * 1.6;
        if (style.auto_resize == 1) {
            setStartPosition();
        }
    }

    function setProperties(el, properties)
    {
        for (let ind in properties) {
            el.style.setProperty(ind, properties[ind]);
        }
    }

    function setStartPosition()
    {
        galleryModal.find('.ba-modal-body').each(function(){
            setProperties(this, {
                '--modal-body-width' : goodWidth+'px',
                '--modal-body-height' : goodHeight+'px',
                '--modal-body-padding-top': ((window.innerHeight - goodHeight) / 2)+'px'
            });
        });
    }
    
    function hideOptions()
    {
        checkHash();
        let parent = galleryModal.parent();
        parent.find('.modal-nav').hide().find('.ba-left-action, .ba-right-action').off('click');
        galleryApp.$g('body').off('touchstart.bagallery touchend.bagallery');
        galleryApp.$g(window).off('orientationchange.bagallery resize.bagallery keyup');
        galleryModal.find('.ba-icon-close, .zmdi-share').off('click touchend');
        galleryModal.find('.ba-modal-header .ba-like-wrapper').off('click touchend');
        if (style.enable_alias == 1 && originalLocation && window.location.href != originalLocation) {
            window.history.replaceState(null, null, originalLocation);
        }
        parent.removeClass('ba-scrollable');
        galleryApp.$g('body').removeClass('modal-open');
        galleryModal.find('.modal-image').empty();
        if (!fullscreen) {
            galleryModal.find('.display-lightbox-fullscreen').trigger('click');
        }
    }
    
    function getData()
    {
        var items = [];
        imagesArray = [];
        gallery.find((category ? category : '.ba-gallery-items')).find('.image-id').each(function(){
            var elem = this.value,
                item = JSON.parse(elem),
                alias;
            if (item.link == '') {
                alias = this.closest('.ba-gallery-items').querySelector('.ba-gallery-image-link').dataset.href;
                imagesArray.push(alias);
                items.push(elem);
            }
        });

        return items;
    }

    function checkImgCounts()
    {
        if (!imgCounts) {
            imgCounts = {
                'category-0': 0
            };
            for (ind in infinity.catImageCount) {
                if (infinity.unpublishCats.indexOf(ind.replace('category-', '')) == -1) {
                    imgCounts['category-0'] += infinity.catImageCount[ind] * 1;
                    imgCounts[ind] = infinity.catImageCount[ind] * 1;
                }
            }
        }
    }

    async function getGalleryImage(data, url)
    {
        let response = await fetch(url, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json;charset=utf-8'
                },
                body: JSON.stringify(data)
            }),
            result = await response.text(),
            div = document.createElement('div');
        div.innerHTML = result;
        jQuery(div).find('.ba-gallery-items').each(function(){
            imagesArray[data.imageIndex] = this.querySelector('.ba-gallery-image-link').dataset.href;
            elements[data.imageIndex] = this.querySelector('.image-id').value;
        });
    }

    async function checkIssetNextImage(ind)
    {
        let data = url = null;
        if (infinity && infinity.category && !elements[ind] &&
            infinity.page * pagination.images_per_page - (pagination.images_per_page - ind) < imgCounts[infinity.category]) {
            data = jQuery.extend(true, {}, infinity);
            url = JUri+'index.php?option=com_bagallery&task=gallery.getGalleryImageInfinity';
        } else if (!elements[ind] && pageRefresh == 1 && pagination &&
            refreshData.currentPage * pagination.images_per_page - (pagination.images_per_page - ind) < refreshData.order.length) {
            data = jQuery.extend(true, {}, refreshData);
            url = JUri+'index.php?option=com_bagallery&task=gallery.getGalleryImageRefresh';
        }
        if (data) {
            data.imageIndex = ind;
            await getGalleryImage(data, url);
        }
    }

    async function checkIssetPrevImage(ind)
    {
        let data = url = null;
        if (infinity && infinity.category && !elements[ind]) {
            data = jQuery.extend(true, {}, infinity);
            url = JUri+'index.php?option=com_bagallery&task=gallery.getGalleryImageInfinity';
        } else if (pageRefresh == 1 && !elements[ind]) {
            data = jQuery.extend(true, {}, refreshData);
            url = JUri+'index.php?option=com_bagallery&task=gallery.getGalleryImageRefresh';
        }
        if (data) {
            data.imageIndex = ind;
            await getGalleryImage(data, url);
        }
    }

    function getNextImageIndex(ind)
    {
        ind++;
        if (ind == elements.length && infinity && infinity.category) {
            checkImgCounts();
        }
        if (ind >= elements.length) {
            ind = 0;
        }

        return ind;
    }

    function getPrevImageIndex(ind)
    {
        ind--;
        if (ind < 0 && infinity && infinity.category) {
            checkImgCounts();
            ind = imgCounts[infinity.category] - 1;
        } else if (ind < 0 && pageRefresh == 1 && pagination) {
            ind = refreshData.currentPage * pagination.images_per_page - pagination.images_per_page;
            ind = ind == 0 ? refreshData.order.length - 1 : ind;
        }
        if (ind < 0) {
            ind = elements.length - 1;
        }

        return ind
    }

    function getNext()
    {
        imageIndex = getNextImageIndex(imageIndex);
        checkIssetNextImage(imageIndex).then(function(){
            image = elements[imageIndex];
            let div = galleryModal.find('.ba-modal-body.active').next();
            if (window.innerWidth > 1024 && div.hasClass('ba-description-'+style.description_position)) {
                galleryModal.addClass('ba-description-'+style.description_position);
            } else {
                galleryModal.removeClass('ba-description-'+style.description_position);
            }
            galleryModal.addClass('ba-slide-animation-started');
            setImage(image, imagesArray[imageIndex], div, 'next');
            clearTimeout(bagallery.delay);
            bagallery.delay = setTimeout(function(){
                galleryModal.removeClass('ba-slide-animation-started');
            }, 600);
        });
    }
    
    function getPrev()
    {
        imageIndex = getPrevImageIndex(imageIndex);
        checkIssetPrevImage(imageIndex).then(function(){
            image = elements[imageIndex];
            let div = galleryModal.find('.ba-modal-body.active').prev();
            if (window.innerWidth > 1024 && div.hasClass('ba-description-'+style.description_position)) {
                galleryModal.addClass('ba-description-'+style.description_position);
            } else {
                galleryModal.removeClass('ba-description-'+style.description_position);
            }
            galleryModal.addClass('ba-slide-animation-started');
            setImage(image, imagesArray[imageIndex], div, 'prev');
            clearTimeout(bagallery.delay);
            bagallery.delay = setTimeout(function(){
                galleryModal.removeClass('ba-slide-animation-started');
            }, 600);
        });
    }

    function showLocationImage($this)
    {
        elements = getData();
        showOptions();
        galleryModal.ba_modal();
        originalLocation = gallery.find('.active-category-image').val();
        addModalEvents($this);
        return true;
    }

    function locationImage()
    {
        if (catModal.hasClass('in') && !catModal.parent().hasClass('modal-scrollable-out')) {
            return false;
        }
        var imageFlag = false;
        if (window.location.search) {
            var search = decodeURIComponent(window.location.href);
            gallery.find('.ba-gallery-image-link[data-href="'+search+'"]').each(function(){
                image = jQuery(this).parent().find('.image-id').val();
                let item = JSON.parse(image),
                    a = jQuery('[data-filter=".'+item.category+'"]');
                if (albumMode && a.length > 0) {
                    chechAlbumItems(a).then(function(){
                        imageFlag = showLocationImage(this);
                    });
                } else if (a.length > 0) {
                    filterAction(a).then(function(){
                        imageFlag = showLocationImage(this);
                    });
                } else {
                    imageFlag = showLocationImage(this);
                }
                return false;
            });
            if (!imageFlag && galleryModal.hasClass('in')) {
                galleryModal.ba_modal('hide');
            }
        } else {
            hideOptions();
        }

        return imageFlag;
    }

    async function getSliderImage(item, body, indFunction, issetFunction, action)
    {
        let ind = indFunction.call(this, imageIndex),
            obj = null,
            clone = body.clone().removeClass('active');
        await issetFunction.call(this, ind);
        obj = JSON.parse(elements[ind]);
        body[action](clone);
        setModalImage(obj, clone);
    }

    async function getSliderImages(item, body, direction)
    {
        if (direction == 'prev' || !direction) {
            await getSliderImage(item, body, getPrevImageIndex, checkIssetPrevImage, 'before');
        }
        if (direction == 'next' || !direction) {
            await getSliderImage(item, body, getNextImageIndex, checkIssetNextImage, 'after');
        }
    }

    function setPreSizeActions(body)
    {
        let position = style.description_position == 'left' || style.description_position == 'right',
            slide = galleryModal.hasClass('ba-gallery-slide-animation'),
            description = body.hasClass('ba-filled-description'),
            data = {
                width: window.innerWidth,
                height: window.innerHeight,
                offset: window.innerWidth > 1024 && description && position
            };
        if (data.offset) {
            data.width -= 400;
        }
        data.slide = slide;
        if (window.innerWidth > 1024 && description) {
            body.addClass('ba-description-'+style.description_position);
        } else {
            body.removeClass('ba-description-'+style.description_position);
        }

        return data;
    }

    async function setAfterSizeAction(body, item, slide)
    {
        body.find('.modal-description').remove();
        if (item.description) {
            item.description = await checkForms(item.description);
            body.find('.description-wrapper').prepend('<div class="modal-description"></div>');
            body.find('.modal-description').html(item.description);
        }
        body.find('.disqus-container').show();
        createVK(body);
    }

    function setVideoSize(body, item, after)
    {
        let data = setPreSizeActions(body),
            height = data.height - 200,
            percent = height / data.height,
            width = data.width * percent,
            top = data.height * 0.1,
            css = {
                '--modal-body-width': window.innerWidth <= 1024 ? data.width+'px' : Math.floor(width)+'px',
                '--modal-body-padding-top': Math.floor(top)+'px',
                '--modal-body-height': ''
            };
        setProperties(body[0], css)
        setTimeout(function(){
            height = 0
            body.find('.modal-image').each(function(){
                height += this.offsetHeight;
            });
            top = (data.height - height) / 2;
            if (top < 0) {
                top = data.height * 0.1;
            }
            setProperties(body[0], {
                '--modal-body-padding-top': Math.floor(top)+'px'
            });
        }, 1);
        if (after) {
            setAfterSizeAction(body, item, data.slide);
        }
    }

    function setAutoSize(body, item, img, animate, after, resize)
    {
        let data = setPreSizeActions(body),
            width = img.width,
            height = img.height,
            css = {},
            percent = 0;
        if (width > data.width || height > data.height) {
            percent = width / height;
            if (width > height) {
                width = data.width;
                height = width / percent;
            } else {
                height = data.height;
                width = percent * height;
            }
            if (height > data.height) {
                height = data.height;
                width = percent * height;
            }
            if (width > data.width) {
                width = data.width;
                height = width / percent;
            }
            if (height == data.height && body.hasClass('ba-filled-description') && !data.offset) {
                data.height = data.height * 0.9;
                height = data.height;
                width = percent * height;
            }
        }
        css['--modal-body-width'] = Math.floor(width)+'px';
        css['--modal-body-padding-top'] = Math.floor((data.height - height) / 2)+'px';
        css['--modal-body-height'] = 'auto';
        setProperties(body[0], css);
        goodWidth = width;
        goodHeight = height;
        if (after) {
            body.find('.modal-image img').attr('src', item.url).attr('alt', item.alt);
            setAfterSizeAction(body, item, data.slide);
        }
    }

    function setNotAutoSize(body, item, after)
    {
        let data = setPreSizeActions(body),
            css = {
                '--modal-body-width': Math.floor(data.width) * (style.lightbox_width / 100)+'px',
                '--modal-body-padding-top': '',
                '--modal-body-height': ''
            }
        setProperties(body[0], css)
        if (after) {
            setAfterSizeAction(body, item, data.slide);
        }
    }

    async function setModalImage(item, body)
    {
        if (body.hasClass('previous-slide') || body.hasClass('next-slide')) {
            body.find('.disqus-container').attr('id', '')
        }
        let str = null,
            slide = galleryModal.hasClass('ba-gallery-slide-animation');
        body.attr('data-id', item.id).find('.modal-image').removeClass('embed');
        if (item.description || disqus || vk_api) {
            body.addClass('ba-filled-description');
        } else {
            body.removeClass('ba-filled-description');
        }
        if (item.video) {
            body.find('.modal-image').addClass('embed');
            str = item.video;
            str = await checkForms(str);
            setVideoSize(body, item, true);
            body.addClass('ba-resize');
        } else if (item.type == 'video') {
            body.find('.modal-image').addClass('embed');
            if (item.video_type == 'youtube') {
                str = '<iframe src="https://www.youtube.com/embed/'+item.video_id+'?enablejsapi=1';
                //str += /*+(body.hasClass('active') ? '?autoplay=1' : '')*/;
                str += '" data-video="youtube" frameborder="0" allow="autoplay;" allowfullscreen></iframe>';
            } else {
                str = '<iframe data-id="'+item.video_id+'" src="https://player.vimeo.com/video/';
                str += item.video_id/*+(body.hasClass('active') ? '?autoplay=1' : '')*/;
                str += '" data-video="vimeo" frameborder="0" allow="autoplay;" allowfullscreen></iframe>';
            }
            setVideoSize(body, item, true);
            body.addClass('ba-resize');
        } else if (style.auto_resize == 1 && !loadedImages[item.id]) {
            str = '<img src="'+item.url+'" alt="'+item.alt+'">';
            body.find('.disqus-container').hide();
            var image = new Image();
            setProperties(body[0], {
                '--modal-body-height': goodHeight+'px'
            });
            image.onload = function(){
                loadedImages[item.id] = {
                    width: this.width,
                    height: this.height
                }
                setAutoSize(body, item, loadedImages[item.id], true, true);
            }
            image.src = item.url;
            body.addClass('ba-resize');
        } else if (style.auto_resize == 1 && loadedImages[item.id]) {
            body.find('.disqus-container').hide();
            setAutoSize(body, item, loadedImages[item.id], false, true);
            str = '<img src="'+item.url+'" alt="'+item.alt+'">';
            body.addClass('ba-resize');
        } else {
            var image = new Image();
            image.onload = function(){
                loadedImages[item.id] = {
                    width: this.width,
                    height: this.height
                }
                if (window.innerWidth <= 1024) {
                    setAutoSize(body, item, loadedImages[item.id], false, true);
                }
            }
            image.src = item.url;
            str = '<img src="'+item.url+'" alt="'+item.alt+'">';
            if (window.innerWidth > 1024) {
                body.removeClass('ba-resize');
                setNotAutoSize(body, item, true);
            } else {
                body.addClass('ba-resize');
            }
        }
        body.find('.modal-image').html(str);

        return str;
    }

    async function setImage(image, imageUrl, loaded, direction)
    {
        checkHash();
        galleryModal.find('.disqus-container').attr('id', '');
        galleryModal.find('.ba-zoom-out').addClass('disabled-item');
        galleryModal.find('.ba-zoom-in').removeClass('disabled-item');
        if (galleryModal.hasClass('hidden-description')) {
            galleryModal.find('.ba-zoom-out').trigger('click');
        }
        galleryModal.parent().css('overflow', '');
        var body = galleryModal.find('.ba-modal-body.active'),
            item = JSON.parse(image);
        if (loaded) {
            galleryModal.find('.ba-modal-body').not(body).not(loaded).remove();
            galleryModal.find('.ba-modal-body').removeClass('ba-next-out ba-next-in ba-prev-out ba-prev-in');
            await getSliderImages(item, loaded, direction);
            body.addClass('ba-'+direction+'-out');
            loaded.addClass('ba-'+direction+'-in');
            body.removeClass('active');
            body = loaded.addClass('active');
        } else {
            galleryModal.find('.ba-modal-body').not('.active').remove();
            galleryModal.find('.ba-modal-body').removeClass('ba-next-out ba-next-in ba-prev-out ba-prev-in');
            await getSliderImages(item, body);
        }
        if (style.enable_alias == 1 && window.location.href != imageUrl) {
            window.history.replaceState(null, null, imageUrl);
        }
        body.find('.disqus-container').each(function(){
            this.innerHTML = '';
            this.id = 'disqus_thread';
            galleryApp.disqus.reset();
        });
        jQuery("#ba-vk-"+galleryId).empty();
        galleryModal.find('.ba-download-img').attr('href', item.url);
        if (!loaded) {
            body.find('.modal-description').remove();
            setModalImage(item, body);
        } else {
            createVK(body);
        }
        if (document.querySelector('iframe[data-video="vimeo"]')) {
            galleryApp.video.load('vimeo');
        }
        if (document.querySelector('iframe[data-video="youtube"]')) {
            galleryApp.video.load('youtube');
        }
        if (window.innerWidth > 1024 && body.hasClass('ba-filled-description')) {
            galleryModal.addClass('ba-description-'+style.description_position);
        } else {
            galleryModal.removeClass('ba-description-'+style.description_position);
        }
        var descHeight = body.find('.modal-description').height();
        if (!descHeight || galleryModal.hasClass('ba-description-left') || galleryModal.hasClass('ba-description-right')) {
            descHeight = 0;
        }
        goodHeight += descHeight;
        galleryModal.find('.ba-download-img, .ba-zoom-out, .ba-zoom-in').show();
        if (item.video || item.type == 'video') {
            galleryModal.find('.ba-download-img, .ba-zoom-out, .ba-zoom-in').addClass('ba-hidden-icons');
            setTimeout(function(){
                galleryModal.find('.ba-download-img, .ba-zoom-out, .ba-zoom-in').removeClass('ba-hidden-icons').hide();
            }, 300);
        }
        galleryModal.find('.modal-title').remove();
        if (titleSize > 0 && item.title) {
            var title = galleryApp.$g('<h3/>', {
                    class: 'modal-title',
                    style: 'color: '+style.header_icons_color
                }).html(item.title);
            galleryModal.find('.ba-modal-header .ba-modal-title').html(title);
        }
        galleryModal.find('.ba-like').attr('data-count', item.likes);
        zoomClk = 1;
    }

    var zoomClk = 1,
        maxZoom = 10,
        zoomW,
        zoomH,
        zoomT,
        zoomL;

    galleryModal.find('.ba-zoom-out').on('click', function(){
        if (zoomClk == 1) {
            return false;
        }
        this.classList.add('disabled-item');
        galleryModal.find('.ba-zoom-in').removeClass('disabled-item');
        galleryModal.find('.modal-image img').css({
            position : '',
            width : '',
            height : '',
            left: '',
            top : '',
            'max-width' : '',
            'max-height' : '',
            'cursor' : ''
        }).off('mousedown.zoom mouseup.zoom touchstart.zoom touchend.zoom');
        galleryModal.parent().css('overflow', '');
        galleryModal.removeClass('hidden-description');
        zoomClk = 1;
    });

    galleryModal.on('click', '.description-wrapper, .modal-image > *', function(event){
        event.stopPropagation();
        galleryModal.trigger(event);
    }).on('click', '.ba-modal-body', function(){
        galleryModal.ba_modal('hide');
    });
    galleryModal.find('.ba-zoom-in').on('click', function(){
        if (galleryModal.parent().scrollTop() > 0) {
            galleryModal.parent().stop().animate({
                scrollTop: 0
            }, 150, function(){
                galleryModal.find('.ba-zoom-in').trigger('click');
            });
            return false;
        }
        if (zoomClk > maxZoom) {
            jQuery(this).addClass('disabled-item');
            return false;
        }
        galleryModal.addClass('hidden-description');
        galleryModal.find('.ba-zoom-out').removeClass('disabled-item');
        var img = galleryModal.find('.ba-modal-body.active').find('.modal-image img'),
            position = img.position(),
            width = img.width() * 1.2,
            height = img.height() * 1.2,
            w = window.innerWidth,
            h = window.innerHeight;
        if (galleryModal.hasClass('ba-description-left') || galleryModal.hasClass('ba-description-right')) {
            w -= 400;
        }
        var left = (w - width) / 2,
            top = (h - img.height() * 1.2) / 2;
        if (galleryModal.hasClass('ba-description-left')) {
            left += 400;
        }
        if (zoomClk == 1) {
            zoomW = img.width();
            zoomH = img.height();
            zoomT = position.top;
            zoomL = position.left;
            img.css({
                width : zoomW,
                height : zoomH,
                top : zoomT,
                left : zoomL,
                position : 'absolute'
            });
        }
        zoomClk++;
        if (img.length == 0) {
            return false;
        }
        img.css({
            position : 'absolute',
            width : width,
            height : height,
            left: left,
            top : top,
            cursor : 'move',
            maxWidth : 'none',
            maxHeight : 'none'
        });
        galleryModal.parent().css('overflow', 'hidden');
        galleryModal.off('mousedown.zoom').on('mousedown.zoom', function(){
            return false;
        }).off('mouseup.zoom').on('mouseup.zoom', function(){
            img.off('mousemove.zoom mouseup.zoom');
        });
        img.off('mousedown.zoom touchstart.zoom').on('mousedown.zoom touchstart.zoom', function(e){
            e.stopPropagation();
            var x = e.clientX,
                y = e.clientY;
            if (e.type == 'touchstart') {
                x = e.originalEvent.targetTouches[0].pageX;
                y = e.originalEvent.targetTouches[0].pageY;
            }
            jQuery(this).on('mousemove.zoom touchmove.zoom', function(event){
                var deltaX = x - event.clientX,
                    deltaY = y - event.clientY,
                    w = document.documentElement.clientWidth,
                    h = document.documentElement.clientHeight;
                if (e.type == 'touchstart') {
                    deltaX = x - event.originalEvent.targetTouches[0].pageX;
                    deltaY = y - event.originalEvent.targetTouches[0].pageY;
                }
                if (galleryModal.hasClass('ba-description-left') || galleryModal.hasClass('ba-description-right')) {
                    w -= 400;
                }
                var maxX = (width - w) * -1,
                    maxY = (height - h) * -1,
                    minX = 0,
                    minY = 0;
                if (galleryModal.hasClass('ba-description-left')) {
                    minX = 400;
                    maxX += 400;
                }
                x = event.clientX;
                y = event.clientY;
                if (e.type == 'touchstart') {
                    x = event.originalEvent.targetTouches[0].pageX;
                    y = event.originalEvent.targetTouches[0].pageY;
                }
                if (width > w) {
                    if (deltaX > 0 && left > maxX) {
                        left -= Math.abs(deltaX);
                        this.style.left = (left < maxX ? maxX : left)+'px';
                    } else if (deltaX < 0 && left < minX) {
                        left += Math.abs(deltaX);
                        this.style.left = (left > minX ? minX : left)+'px';
                    }
                }
                if (height > h) {
                    if (deltaY > 0 && top > maxY) {
                        top -= Math.abs(deltaY);
                        this.style.top = (top < maxY ? maxY : top)+'px';
                    } else if (deltaY < 0 && top < minY) {
                        top += Math.abs(deltaY);
                        this.style.top = (top > minY ? minY : top)+'px';
                    }
                }                        
                return false;
            });
            return false;
        }).off('mouseup.zoom touchend.zoom').on('mouseup.zoom touchend.zoom', function(){
            jQuery(this).off('mousemove.zoom touchmove.zoom');
        });
    });

    function checkFullscreen()
    {
        if (document.fullscreenElement || document.webkitIsFullScreen
            || document.mozFullScreen || document.msFullscreenElement) {
            galleryModal.find('.display-lightbox-fullscreen').removeClass('zmdi-fullscreen').addClass('zmdi-fullscreen-exit');
            fullscreen = false;
        } else {
            galleryModal.find('.display-lightbox-fullscreen').removeClass('zmdi-fullscreen-exit').addClass('zmdi-fullscreen');
            fullscreen = true;
        }
    }

    document.addEventListener('fullscreenchange', checkFullscreen, false);
    document.addEventListener('webkitfullscreenchange', checkFullscreen, false);
    document.addEventListener('mozfullscreenchange', checkFullscreen, false);
    document.addEventListener('msfullscreenchange', checkFullscreen, false);

    galleryModal.find('.display-lightbox-fullscreen').on('click', function(){
        if (fullscreen) {
            var docElm = document.documentElement;
            if (docElm.requestFullscreen) {
                docElm.requestFullscreen();
            } else if (docElm.mozRequestFullScreen) {
                docElm.mozRequestFullScreen();
            } else if (docElm.webkitRequestFullScreen) {
                docElm.webkitRequestFullScreen();
            } else if (docElm.msRequestFullscreen) {
                docElm.msRequestFullscreen();
            }                
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
            } else if (document.mozCancelFullScreen) {
                document.mozCancelFullScreen();
            } else if (document.webkitCancelFullScreen) {
                document.webkitCancelFullScreen();
            } else if (document.msExitFullscreen) {
                document.msExitFullscreen();
            }
            this.classList.add('zmdi-fullscreen');
            this.classList.remove('zmdi-fullscreen-exit');
            fullscreen = true;
        }
    });
    
    galleryModal.find('.ba-share-icons [data-service]').on('click', function(event){
        event.stopPropagation();
        galleryApp.share.execute(galleryModal, this.dataset.service);
    });

    gallery.find('.albums-backdrop, .albums-backdrop-close').on('click', function(){
        category = '.root';
        var alias = gallery.find('.current-root').val();
        if (pageRefresh == 1) {
            if (alias != window.location.href) {
                refreshPage(alias);
                gallery.find('.ba-pagination').hide();
            }
        } else {
            gallery.removeClass('album-in-lightbox');
            jQuery('body').removeClass('album-in-lightbox-open');
            window.history.replaceState(null, null, alias);
            if (pagination) {
                currentPage = '.page-1';
                addPages();
                drawPagination();
            }
            resizeIsotope();
        }
    });
    
    function likeImage()
    {
        if (likeFlag) {
            likeFlag = false;
            let item = JSON.parse(image);
            galleryApp.fetch(JUri+"index.php?option=com_bagallery&view=gallery&task=gallery.likeIt&image_id="+item.id, {
                image_id : item.id
            }).then(function(text){
                item.likes = text;
                let str = JSON.stringify(item);
                gallery.find('input[data-id="ba-image-'+item.id+'"]').val(str);
                elements[imageIndex] = str;
                galleryModal.find('.ba-like').attr('data-count', text);
                likeFlag = true;
            });
        }
    }
    
    addCaptionStyle();
    if (pagination) {
        drawPagination();
    }
    setTimeout(function(){
        resizeIsotope();
    }, 100);
    if (albumMode) {
        lazyloadOptions.lightbox = albumOptions.album_enable_lightbox
    }
    gallery.find('.ba-gallery-items img').lazyload(lazyloadOptions);
}

document.addEventListener("DOMContentLoaded", initGalleries);
if (document.readyState == 'complete') {
    initGalleries();
}