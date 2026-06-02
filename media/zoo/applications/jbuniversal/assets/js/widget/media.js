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
 */

;
(function ($, window, document, undefined) {

    // SqueezeBox polyfill for Joomla 4/5/6 using Bootstrap modal
    if (typeof window.SqueezeBox === 'undefined') {
        window.SqueezeBox = {
            _modal: null,

            fromElement: function (el, options) {
                options = options || {};

                var url = options.url || '';
                var size = options.size || {};
                var height = size.y || 500;

                var modalId = 'jbzoo-squeezebox-modal';
                var $modal = jQuery('#' + modalId);

                if (!$modal.length) {
                    $modal = jQuery('<div>', {
                        id: modalId,
                        'class': 'modal fade',
                        tabindex: -1,
                        role: 'dialog'
                    }).append(
                        jQuery('<div>', {
                            'class': 'modal-dialog modal-lg',
                            role: 'document'
                        }).append(
                            jQuery('<div>', {'class': 'modal-content'}).append(
                                jQuery('<div>', {'class': 'modal-header'}).append(
                                    jQuery('<h5>', {'class': 'modal-title'}).text('Select Image')
                                ).append(
                                    jQuery('<button>', {
                                        type: 'button',
                                        'class': 'btn-close',
                                        'data-bs-dismiss': 'modal',
                                        'aria-label': 'Close'
                                    })
                                )
                            ).append(
                                jQuery('<div>', {'class': 'modal-body p-0'}).append(
                                    jQuery('<iframe>', {
                                        src: '',
                                        width: '100%',
                                        height: height,
                                        frameborder: 0
                                    })
                                )
                            )
                        )
                    ).appendTo('body');
                }

                $modal.find('iframe')
                    .attr('src', url)
                    .attr('height', height);

                if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var instance = new bootstrap.Modal($modal[0]);
                    this._modal = instance;
                    instance.show();
                } else {
                    window.open(url, 'mediaManager', 'width=850,height=' + height + ',resizable=yes,scrollbars=yes');
                }
            },

            close: function () {
                if (this._modal && typeof this._modal.hide === 'function') {
                    this._modal.hide();
                    this._modal = null;
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var $modal = jQuery('#jbzoo-squeezebox-modal');
                    if ($modal.length) {
                        var inst = bootstrap.Modal.getInstance($modal[0]);
                        if (inst) {
                            inst.hide();
                        }
                    }
                }
            }
        };
    }

    JBZoo.widget('JBZoo.Media', {
            'folder'             : '',
            'author'             : '',
            'message_open_editor': 'Open editor',

            // TODO optional preview image on hover
            'preview'            : true
        },
        {
            unique: {},
            url   : '',

            init: function ($this) {

                // Determine base URL for both frontend and backend
                var href = location.href;
                var match = href.match(/^(.+?)administrator\/index\.php/i);
                if (match) {
                    // Backend: extract base URL before administrator/
                    this.url = match[1];
                } else {
                    // Frontend: use current site base URL
                    this.url = href.replace(/index\.php.*$/, '').replace(/\/$/, '') + '/';
                }

                this.unique = this._name + '_' + this._id;

                this.cancel();
                this.button();

                if (this.options.preview) {
                    this.preview();
                }

                if ($.isFunction(window.jInsertEditorText)) {
                    window[this.unique] = window.jInsertEditorText;
                }

                window.jInsertEditorText = function (img, id) {
                    try {
                        if ($this.unique === id) {
                            var match = img.match(/src="([^\"]*)"/);
                            if (match && match[1]) {
                                var value = match[1];

                                $this.el
                                    .find('.jsMediaPreview')
                                    .html(img)
                                    .find('img')
                                    .attr('src', $this.url + value);

                                $this.$('.jsMediaValue').val(value);

                                // Close SqueezeBox (polyfilled via Bootstrap modal)
                                if (typeof SqueezeBox !== 'undefined' && SqueezeBox.close) {
                                    SqueezeBox.close();
                                }
                            }
                        } else {
                            $.isFunction(window[$this.unique]) &&
                            window[$this.unique](img, id);
                        }
                    } catch (e) {
                        console.error('Error in jInsertEditorText:', e);
                    }
                };
            },

            'click .jsMediaButton': function (e, $this) {
                e.preventDefault();

                if (typeof Joomla === 'undefined' || typeof Joomla.initialiseModal !== 'function') {
                    console.error('Joomla media modal is not available');
                    alert('Media manager is not available. Please check your Joomla installation.');
                    return;
                }

                // URL for Joomla 4/5/6 media manager
                var mediaUrlBase = 'index.php?option=com_media&view=media&tmpl=component&mediatypes=0&asset=com_content&path=';

                // Hidden input just to satisfy Joomla.getMedia API
                var $hiddenInput = $('<input>', {
                    type: 'text',
                    style: 'display:none;'
                }).appendTo('body');

                var modalHtml =
                    '<div tabindex="-1" class="joomla-modal modal fade" aria-modal="true" role="dialog">' +
                    '  <div class="modal-dialog modal-lg jviewport-width80">' +
                    '    <div class="modal-content">' +
                    '      <div class="modal-header">' +
                    '        <h3 class="modal-title">Choose Image</h3>' +
                    '      </div>' +
                    '      <div class="modal-body jviewport-height60">' +
                    '        <iframe class="iframe" src="' + mediaUrlBase + '" name="Change Image" height="100%" width="100%"></iframe>' +
                    '      </div>' +
                    '      <div class="modal-footer">' +
                    '        <button type="button" class="btn btn-success button-save-selected">Select</button>' +
                    '        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>' +
                    '      </div>' +
                    '    </div>' +
                    '  </div>' +
                    '</div>';

                var a = $(modalHtml).insertBefore($hiddenInput)[0];

                Joomla.initialiseModal(a, { isJoomla: true });

                a.querySelector('.button-save-selected').addEventListener('click', function () {
                    Joomla.getMedia(Joomla.selectedMediaFile, $hiddenInput[0], {
                        updatePreview: function () {},
                        markValid: function () {},
                        setValue: function (value) {
                            // value is like "images/sampledata/...#..." - strip anchor
                            var clean = value.replace(/#.*/, '');

                            // Store relative path in input
                            $this.$('.jsMediaValue').val(clean);

                            // Update preview
                            $this.el
                                .find('.jsMediaPreview')
                                .html($('<img>', {
                                    src: $this.url + clean
                                }));
                        }
                    }).then(function () {
                        if (typeof a.close === 'function') {
                            a.close();
                        }
                    });
                });

                a.addEventListener('hidden.bs.modal', function () {
                    $(a).remove();
                    $hiddenInput.remove();
                });

                Joomla.selectedMediaFile = {};
                if (typeof a.open === 'function') {
                    a.open();
                } else if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
                    var modalInstance = new bootstrap.Modal(a);
                    modalInstance.show();
                }
            },

            'click .jsMediaCancel': function (e, $this) {
                $(this).prev().val("");
                $this.$('.jsMediaPreview').empty();
            },

            preview: function () {

                this.el.append($('<div />', {
                        'class': 'jsMediaPreview image-preview'
                    }).append($('<img />', {
                        'class': 'jsMediaImgPreview',
                        'src'  : this.value()
                    }))
                );
            },

            button: function () {
                // Button is now created in the template, so we don't create it here automatically
                // This allows for better customization and styling
            },

            cancel: function () {
                // Cancel button is now created in the template
            },

            value: function () {
                var value = this.$('.jsMediaValue').val();

                return value ? this.url + value : '';
            }
        }
    );

})(jQuery, window, document);
