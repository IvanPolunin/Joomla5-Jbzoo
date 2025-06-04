/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

function showNotice(message, className)
{
    if (!className) {
        className = '';
    }
    if (notification.hasClass('notification-in')) {
        setTimeout(function(){
            notification.removeClass('notification-in').addClass('animation-out');
            setTimeout(function(){
                addNoticeText(message, className);
            }, 400);
        }, 2000);
    } else {
        addNoticeText(message, className);
    }
}

function addNoticeText(message, className)
{
    var time = 3000;
    if (className) {
        time = 6000;
    }
    notification.find('p').html(message);
    notification.addClass(className).removeClass('animation-out').addClass('notification-in');
    setTimeout(function(){
        notification.removeClass('notification-in').addClass('animation-out');
        setTimeout(function(){
            notification.removeClass(className);
        }, 400);
    }, time);
}

var update = notification = uploadMode = null;

document.addEventListener('DOMContentLoaded', function(){

    update = jQuery('#update-data').val();
    notification = jQuery('#ba-notification');

    var massage = '';

    setTimeout(function(){
        jQuery('.alert.alert-success').addClass('animation-out');
    }, 2000);

    update = JSON.parse(update);

    jQuery('body').on('click', '#toolbar-cleanup-images', function(){
        jQuery('#cleanup-images-dialog').modal();
    });

    jQuery('#cleanup-images').on('click', function(){
        var str = update.saving+'<img src="'+update.url;
        str += 'administrator/components/com_bagallery/assets/images/reload.svg"></img>';
        notification.addClass('notification-in');
        notification.find('p').html(str);
        jQuery.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_bagallery&task=galleries.cleanup&tmpl=component",
            success: function(msg){
                showNotice(msg);
            }
        });
        jQuery('#cleanup-images-dialog').modal('hide');
    });

    jQuery('.ba-custom-select > i, div.ba-custom-select input').on('click', function(event){
        event.stopPropagation()
        var parent = jQuery(this).parent();
        jQuery('.visible-select').removeClass('visible-select');
        parent.find('ul').addClass('visible-select');
        parent.find('li').off('click').one('click', function(){
            var text = jQuery.trim(jQuery(this).text()),
                val = jQuery(this).attr('data-value');
            parent.find('input[type="text"]').val(text);
            parent.find('input[type="hidden"]').val(val).trigger('change');
        });
        parent.trigger('show');
        setTimeout(function(){
            jQuery('body').one('click', function(){
                jQuery('.visible-select').removeClass('visible-select');
            });
        }, 50);
    });

    jQuery('.ba-tooltip').each(function(){
        jQuery(this).parent().on('mouseenter', function(){
            var tooltip = jQuery(this).find('.ba-tooltip'),
                coord = this.getBoundingClientRect(),
                top = coord.top,
                data = tooltip.html(),
                center = (coord.right - coord.left) / 2;
                className = tooltip[0].className;
            center = coord.left + center;
            if (tooltip.hasClass('ba-bottom')) {
                top = coord.bottom;
            }
            jQuery('body').append('<span class="'+className+'">'+data+'</span>');
            var tooltip = jQuery('body > .ba-tooltip').last(),
                width = tooltip.outerWidth(),
                height = tooltip.outerHeight();
            if (tooltip.hasClass('ba-top') || tooltip.hasClass('ba-help')) {
                top -= (15 + height);
                center -= (width / 2)
            }
            if (tooltip.hasClass('ba-bottom')) {
                top += 10;
                center -= (width / 2)
            }
            tooltip.css({
                'top' : top+'px',
                'left' : center+'px'
            }).on('mousedown', function(event){
                event.stopPropagation();
            });
        }).on('mouseleave', function(){
            var tooltip = jQuery('body').find(' > .ba-tooltip');
            tooltip.addClass('tooltip-hidden');
            setTimeout(function(){
                tooltip.remove();
            }, 500);
        });
    });

    jQuery('div.ba-custom-select').on('show', function(){
        jQuery(this).find('i.zmdi.zmdi-check').parent().addClass('selected');
    });

    jQuery('#apply-deactivate').on('click', function(event){
        event.preventDefault();
        jQuery.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_bagallery&task=galleries.checkGalleryState",
            success: function(msg){
                var obj = JSON.parse(msg),
                    url = 'https://www.balbooa.com/demo/index.php?',
                    script = document.createElement('script');
                url += 'option=com_baupdater&task=bagallery.deactivateLicense';
                url += '&data='+obj.data;
                url += '&time='+(+(new Date()));
                script.onload = function(){
                    jQuery.ajax({
                        type : "POST",
                        dataType : 'text',
                        url : JUri+"index.php?option=com_bagallery&task=gallery.setAppLicense",
                        success: function(msg){
                            showNotice(galleryLanguage['SUCCESSFULY_DEACTIVATED']);
                            jQuery('#toolbar-about span[data-notification]').each(function(){
                                this.dataset.notification = this.dataset.notification * 1 + 1;
                            });
                            jQuery('.gallery-activate-license').css('display', '');
                            jQuery('.gallery-deactivate-license').hide();
                        }
                    });
                }
                script.src = url;
                document.head.appendChild(script);
            }
        });
        jQuery('#deactivate-dialog').modal('hide');
    });

    jQuery('.languages-wrapper').on('click', '.language-title', function(){
        var str = update.installing+'<img src="'+JUri;
        str += 'administrator/components/com_bagallery/assets/images/reload.svg"></img>';
        notification.addClass('notification-in');
        notification.find('p').html(str);
        jQuery('#language-dialog').modal('hide');
        jQuery.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_bagallery&task=galleries.addLanguage&tmpl=component",
            data:{
                method: window.atob('YmFzZTY0X2RlY29kZQ=='),
                url: galleryApi.languages[this.dataset.key].url,
                zip: galleryApi.languages[this.dataset.key].zip,
            },
            success: function(msg){
                showNotice(msg)
            }
        });
    });

    jQuery('.ba-dashboard-apps-dialog').on('click', function(event){
        event.stopPropagation();
    })

    jQuery('body').on('click', function(event){
        jQuery('.ba-dashboard-apps-dialog.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
    });

    jQuery('body').on('click', '.ba-dashboard-popover-trigger', function(event){
        event.stopPropagation();
        let div = document.querySelector('.'+this.dataset.target),
            rect = this.getBoundingClientRect();
        div.classList.add('visible-dashboard-dialog');
        let left = (rect.left - div.offsetWidth / 2 + rect.width / 2),
            arrow = '50%';
        if (this.dataset.target == 'blog-settings-context-menu' && left < 110) {
            left = 110;
            arrow = (rect.left - 110 + rect.width / 2)+'px'
        }
        div.style.setProperty('--arrow-position', arrow);
        div.style.top = (rect.bottom + window.pageYOffset + 10)+'px';
        div.style.left = left+'px';
    }).on('click', '#toolbar-language button', function(){
        jQuery('#language-dialog').modal();
    });
    
    jQuery('.leave-feedback').on('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        jQuery('#feedback-dialog').modal();
    });
    
    jQuery('#feedback-dialog').on('show', function(){
        jQuery('.feedback-body').show();
        jQuery('.happy-feedback, .not-happy-feedback').hide();
    });
    
    jQuery('.happy-rewiev').on('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        jQuery('.happy-feedback').show();
        jQuery('.feedback-body, .not-happy-feedback').hide();
    });
    
    jQuery('.not-happy-rewiev').on('click', function(event){
        event.stopPropagation();
        event.preventDefault();
        jQuery('.not-happy-feedback').show();
        jQuery('.feedback-body, happy-feedback').hide();
    });

    jQuery('#login-modal').on('show', function(){
        var url = 'https://www.balbooa.com/demo/index.php?option=com_baupdater&view=bagallery',
            domain = window.location.host.replace('www.', '');
            iframe = document.createElement('iframe');
        domain += window.location.pathname.replace('index.php', '').replace('/administrator', '');
        if (domain[domain.length - 1] != '/') {
            domain += '/';
        }
        url += '&domain='+window.btoa(domain);
        iframe.onload = function(){
            this.classList.add('iframe-loaded');
        }
        iframe.src = url;
        jQuery('#login-modal .modal-body').html(iframe);
        window.addEventListener("message", listenMessage, false);
    });

    jQuery('#login-modal').on('hide', function(){
        window.removeEventListener("message", listenMessage, false);
    });

    jQuery('.activate-link').on('click', function(event){
        event.preventDefault();
        jQuery('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
        uploadMode = 'activateGallery';
        jQuery('#login-modal').modal();
    });

    jQuery('.deactivate-link').on('click', function(event){
        event.preventDefault();
        jQuery('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
        jQuery('#deactivate-dialog').modal();
    });

    jQuery('.gallery-update-wrapper').on('click', '.update-link', function(event){
        event.preventDefault();
        jQuery('.ba-dashboard-about.visible-dashboard-dialog').removeClass('visible-dashboard-dialog');
        jQuery.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_bagallery&task=galleries.checkGalleryState",
            success: function(msg){
                var flag = true,
                    obj;
                if (msg) {
                    obj = JSON.parse(msg);
                    flag = !obj.data;
                }
                if (flag) {
                    uploadMode = 'updateGallery';
                    jQuery('#login-modal').modal();
                } else {
                    var url = 'https://www.balbooa.com/demo/index.php?',
                        domain = window.location.host.replace('www.', ''),
                        script = document.createElement('script');
                    domain += window.location.pathname.replace('index.php', '').replace('/administrator', '');
                    url += 'option=com_baupdater&task=bagallery.checkGalleryUser';
                    url += '&data='+obj.data;
                    if (domain[domain.length - 1] != '/') {
                        domain += '/';
                    }
                    url += '&domain='+window.btoa(domain);
                    script.onload = function(){
                        if (galleryResponse) {
                            updateGallery(galleryApi.package);
                        } else {
                            uploadMode = 'updateGallery';
                            jQuery('#login-modal').modal();
                        }
                    }
                    script.src = url;
                    document.head.appendChild(script);
                }
            }
        });
    });

    function updateGallery(package)
    {
        setTimeout(function(){
            var str = galleryLanguage['UPDATING']+'<img src="'+JUri;
            str += 'administrator/components/com_bagallery/assets/images/reload.svg"></img>';
            notification[0].className = 'notification-in';
            notification.find('p').html(str);
        }, 400);
        var XHR = new XMLHttpRequest(),
            url = 'index.php?option=com_bagallery&task=galleries.updateGallery&tmpl=component',
            data = {
                method: window.atob('YmFzZTY0X2RlY29kZQ=='),
                package: package
            };
        XHR.onreadystatechange = function(e) {
            if (XHR.readyState == 4) {
                setTimeout(function(){
                    notification[0].className = 'animation-out';
                    setTimeout(function(){
                        notification.find('p').html(galleryLanguage['UPDATED']);
                        notification[0].className = 'notification-in';
                        setTimeout(function(){
                            notification[0].className = 'animation-out';
                            setTimeout(function(){
                                window.location.href = window.location.href;
                            }, 400);
                        }, 3000);
                    }, 400);
                }, 2000);
            }
        };
        XHR.open("POST", url, true);
        XHR.send(JSON.stringify(data));
    }

    function getUserLicense(data)
    {
        jQuery.ajax({
            type:"POST",
            dataType:'text',
            url:"index.php?option=com_bagallery&task=galleries.getUserLicense",
            data:{
                data: data
            },
            success : function(msg){
                if (uploadMode != 'updateGallery') {
                    showNotice(galleryLanguage['YOUR_LICENSE_ACTIVE']);
                }
                jQuery('#toolbar-about span[data-notification]').each(function(){
                    this.dataset.notification = this.dataset.notification * 1 - 1;
                });
                jQuery('.gallery-activate-license').hide();
                jQuery('.gallery-deactivate-license').css('display', '');
            }
        });
    }

    function listenMessage(event)
    {
        if (event.origin == 'https://www.balbooa.com') {
            try {
                let obj = JSON.parse(event.data);
                getUserLicense(obj.data);
                if (uploadMode == 'updateGallery') {
                    updateGallery(galleryApi.package);
                }
            } catch (error) {
                showNotice(event.data, 'ba-alert');
            }
            jQuery('#login-modal').modal('hide');
        }
    }
});

document.addEventListener('DOMContentLoaded', function(){
    let script = document.createElement('script');
    script.onload = function(){
        jQuery.ajax({
            type : "POST",
            dataType : 'text',
            url : 'index.php?option=com_bagallery&task=galleries.versionCompare',
            data : {
                version: galleryApi.version
            },
            success: function(msg){
                if (msg == -1) {
                    jQuery('.gallery-update-wrapper').each(function(){
                        this.classList.add('gallery-update-available');
                        this.querySelector('i').className = 'zmdi zmdi-alert-triangle';
                        this.querySelector('span').textContent = galleryLanguage['UPDATE_AVAILABLE'];
                        if (this.classList.contains('gallery-update-wrapper')) {
                            let a = document.createElement('a');
                            a.className = 'update-link dashboard-link-action';
                            a.href = "#";
                            a.textContent = galleryLanguage['UPDATE'];
                            this.appendChild(a);
                        }
                    });
                    jQuery('.ba-dashboard-popover-trigger[data-target="ba-dashboard-about"]').each(function(){
                        let count = this.querySelector('span[data-notification]');
                        count.dataset.notification = count.dataset.notification * 1 + 1;
                    });
                }
            }
        });
        galleryApi.languages.forEach(function(el, ind){
            var str = '<div class="language-line"><span class="language-img"><img src="'+el.flag+el.code+'.svg">';
            str += '</span><span class="language-title" data-key="'+ind+'">'+el.title;
            str += '</span><span class="language-code">'+el.code+'</span></div>';
            jQuery('#language-dialog .languages-wrapper').append(str);
        });
    }
    let classList = document.body.classList;
    script.type = 'text/javascript';
    script.src = 'https://www.balbooa.com/updates/bagallery/galleryApi/galleryApi.js';
    document.head.appendChild(script);
});