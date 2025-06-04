/* jce - 2.9.81 | 2024-09-24 | https://www.joomlacontenteditor.net | Copyright (C) 2006 - 2024 Ryan Demmer. All rights reserved | GNU/GPL Version 2 or later - http://www.gnu.org/licenses/gpl-2.0.html */
!function() {
    var DOM = tinymce.DOM, Event = tinymce.dom.Event, each = (tinymce.extend, tinymce.each), Storage = (tinymce.html.SaxParser, 
    tinymce.html.Schema, tinymce.util.Storage);
    var toolbar, statusbar, toolbarHeight = 0, statusbarHeight = 0;
    tinymce.PluginManager.add("source", function(ed, url) {
        var self = this;
        function isEditorActive() {
            return 0 == DOM.hasClass(ed.getElement(), "wf-no-editor");
        }
        ed.plugins.fullscreen && (ed.onFullScreen.add(function(ed, state) {
            var element = ed.getElement(), element = DOM.getPrev(element, ".wf-editor-header"), iframe = DOM.get(ed.id + "_editor_source_iframe");
            toolbar && (toolbarHeight = toolbar.offsetHeight), statusbar && (statusbarHeight = statusbar.offsetHeight), 
            state ? (ed.settings.container_height || (ed.settings.container_height = iframe.clientHeight), 
            state = DOM.getViewPort(), DOM.setStyle(iframe, "height", state.h - element.offsetHeight - toolbarHeight - statusbarHeight), 
            DOM.setStyle(iframe, "max-width", "100%"), DOM.hide(ed.id + "_editor_source_resize")) : (DOM.setStyle(iframe, "height", ed.settings.container_height - toolbarHeight - statusbarHeight), 
            DOM.setStyle(iframe, "max-width", "100%"), DOM.show(ed.id + "_editor_source_resize"));
        }), ed.onFullScreenResize.add(function(ed, vp) {
            var element = ed.getElement(), element = DOM.getPrev(element, ".wf-editor-header"), ed = DOM.get(ed.id + "_editor_source_iframe");
            toolbar && (toolbarHeight = toolbar.offsetHeight), statusbar && (statusbarHeight = statusbar.offsetHeight), 
            DOM.setStyle(ed, "height", vp.h - element.offsetHeight - toolbarHeight - statusbarHeight);
        })), ed.onSetContent.add(function(ed, o) {
            self.setContent(ed.getContent(), !0);
        }), ed.onInit.add(function(ed) {
            0 != isEditorActive() && "wf-editor-source" === (ed.settings.active_tab || "") && (DOM.hide(ed.getContainer()), 
            DOM.hide(ed.getElement()), window.setTimeout(function() {
                self.toggle();
            }, 10));
        });
        var ControlManager = new tinymce.ControlManager(ed);
        function getSourceEditor() {
            var iframe = DOM.get(ed.id + "_editor_source_iframe");
            return iframe && iframe.contentWindow.codemirror || null;
        }
        function getActionState(key, value) {
            if (!1 !== ed.settings.use_state_cookies) {
                key = Storage.get("wf_source_" + key);
                if (tinymce.is(key) && null !== key) return parseInt(key, 10);
            }
            return value;
        }
        function createToolbar(container) {
            toolbar = DOM.add(container, "div", {
                class: "mceToolbar mceToolbarSource"
            });
            var cm, searchBox, replaceBox, fullscreen_btn, container = ControlManager.createToolbar("source_toolbar", {
                name: ed.getLang("advanced.toolbar"),
                tab_focus_toolbar: ed.getParam("theme_advanced_tab_focus_toolbar"),
                class: "mceFlex mceFlexAuto"
            }), toolbarActions = ControlManager.createToolbar("source_toolbar_actions", {
                class: "mceSourceActions"
            }), format_btn = (ed.plugins.fullscreen && ((fullscreen_btn = ControlManager.createButton("source_fullscreen", {
                title: ed.getLang("source.fullscreen", "Fullscreen"),
                onclick: function() {
                    var state = !fullscreen_btn.isActive();
                    return fullscreen_btn.setActive(state), ed.execCommand("mceFullScreen");
                }
            })).setActive(ed.fullscreen_enabled), toolbarActions.add(fullscreen_btn)), 
            tinymce.each([ "undo", "redo" ], function(name) {
                var btn = ControlManager.createButton("source_" + name, {
                    title: ed.getLang("advanced." + name + "_desc", name),
                    onclick: function() {
                        cm = cm || getSourceEditor(), "undo" == name && ControlManager.get("source_redo").setDisabled(!1);
                        var state = cm[name]();
                        btn.setDisabled(!state);
                    }
                });
                btn.onPostRender.add(function(ctrl, el) {
                    ctrl.setDisabled(!0);
                }), toolbarActions.add(btn);
            }), tinymce.each([ "highlight", "linenumbers", "wrap" ], function(name) {
                if ("highlight" === name) {
                    var theme = ed.getParam("source_theme", "");
                    if (theme && !theme.startsWith("codemirror")) return !0;
                }
                var btn = ControlManager.createButton("source_" + name, {
                    title: ed.getLang("source." + name, name),
                    onclick: function() {
                        var key, state = !btn.isActive();
                        return btn.setActive(state), key = name, state = state, 
                        !1 !== ed.settings.use_state_cookies && Storage.set("wf_source_" + key, state ? 1 : 0), 
                        cm = cm || getSourceEditor(), "wrap" == name ? cm.toggleWrap() : "highlight" == name ? cm.toggleHighlight() : "linenumbers" == name ? cm.toggleNumbers() : void 0;
                    }
                });
                btn.onPostRender.add(function() {
                    var state = getActionState(name, ed.getParam("source_" + name, !0));
                    btn.setActive(!!state);
                }), toolbarActions.add(btn);
            }), ControlManager.createButton("source_format", {
                title: ed.getLang("source.format", "Format"),
                onclick: function() {
                    return (cm = cm || getSourceEditor()).format();
                }
            })), toolbarSearch = (toolbarActions.add(format_btn), container.add(toolbarActions), 
            ControlManager.createToolbar("source_toolbar_search", {
                class: "mceSourceSearch"
            }));
            function initSearch() {
                var value = searchBox.value(), replace = replaceBox.value(), regex = regexBtn.isActive(), wholeWord = wholeWordBtn.isActive(), matchCase = matchCaseBtn.isActive();
                (cm = cm || getSourceEditor()).search(value, replace, {
                    wholeWord: !!wholeWord,
                    regexp: !!regex,
                    caseSensitive: !!matchCase
                });
            }
            (searchBox = ControlManager.createTextBox("source_search_value", {
                title: ed.getLang("source.search", "Search"),
                attributes: {
                    placeholder: ed.getLang("source.search_value", "Search")
                }
            })).onChange.add(function() {
                if ("" === searchBox.value()) return (cm = cm || getSourceEditor()).search("");
            }), searchBox.onPostRender.add(function(e, elm) {
                Event.add(elm, "input", function(e) {
                    initSearch();
                }), Event.add(elm, "keydown", function(e) {
                    if (13 === e.keyCode) {
                        if (e.preventDefault(), "" === searchBox.value()) return !1;
                        initSearch();
                    }
                });
            }), toolbarSearch.add(searchBox), tinymce.each({
                previous: "search_prev",
                next: "search"
            }, function(label, name) {
                label = ControlManager.createButton("source_search_" + name, {
                    title: ed.getLang("source." + label, name),
                    onclick: function(e) {
                        return cm = cm || getSourceEditor(), "previous" == name ? cm.searchPrevious() : cm.searchNext();
                    }
                });
                toolbarSearch.add(label);
            }), (replaceBox = ControlManager.createTextBox("source_replace_value", {
                title: ed.getLang("source.replace", "Replace"),
                attributes: {
                    placeholder: ed.getLang("source.replace_value", "Replace")
                }
            })).onPostRender.add(function(e, elm) {
                Event.add(elm, "input", initSearch), Event.add(elm, "keydown", function(e) {
                    13 === e.keyCode && (e.preventDefault(), cm.replaceNext());
                });
            }), toolbarSearch.add(replaceBox), tinymce.each([ "replace", "replace_all" ], function(name) {
                var btn = ControlManager.createButton("source_" + name, {
                    title: ed.getLang("source." + name, name),
                    onclick: function() {
                        cm = cm || getSourceEditor(), "replace" == name ? cm.replaceNext() : cm.replaceAll();
                    }
                });
                toolbarSearch.add(btn);
            });
            var regexBtnState = !1, regexBtn = ControlManager.createButton("source_search_regex", {
                title: ed.getLang("source.search_regex", "Regular Expression"),
                onclick: function() {
                    regexBtnState = !regexBtnState, regexBtn.setActive(regexBtnState), 
                    initSearch();
                }
            }), wholeWordBtnState = !1, wholeWordBtn = ControlManager.createButton("source_search_wholeword", {
                title: ed.getLang("source.search_wholeword", "Whole Word"),
                onclick: function() {
                    wholeWordBtnState = !wholeWordBtnState, wholeWordBtn.setActive(wholeWordBtnState), 
                    initSearch();
                }
            }), matchCaseBtnState = !1, matchCaseBtn = ControlManager.createButton("source_search_matchcase", {
                title: ed.getLang("source.search_matchcase", "Match Case"),
                onclick: function() {
                    matchCaseBtnState = !matchCaseBtnState, matchCaseBtn.setActive(matchCaseBtnState), 
                    initSearch();
                }
            });
            toolbarSearch.add(matchCaseBtn), toolbarSearch.add(wholeWordBtn), toolbarSearch.add(regexBtn), 
            container.add(toolbarSearch), container.renderTo(toolbar), ControlManager.onPostRender.dispatch();
        }
        function resizeTo(width, height) {
            var element = ed.getElement(), container = element.parentNode, element = DOM.getPrev(element, ".wf-editor-header"), ifr = DOM.get(ed.id + "_editor_source_iframe");
            DOM.hasClass(container, "mce-fullscreen") && (container = DOM.getViewPort(), 
            toolbar && (toolbarHeight = toolbar.offsetHeight), statusbar && (statusbarHeight = statusbar.offsetHeight), 
            height = container.h - element.offsetHeight - toolbarHeight - statusbarHeight), 
            height && DOM.setStyle(ifr, "height", parseInt(height, 10) + "px");
        }
        this.setContent = function(v) {
            var editor = getSourceEditor();
            return !!editor && editor.setContent(v);
        }, this.insertContent = function(v) {
            var editor = getSourceEditor();
            return !!editor && editor.insertContent(v);
        }, this.getContent = function() {
            var editor = getSourceEditor();
            return editor && !DOM.isHidden(ed.id + "_editor_source") ? editor.getContent() : null;
        }, this.hide = function() {
            DOM.hide(ed.id + "_editor_source");
        }, this.save = function(content, debounced) {
            var el = ed.getElement(), content = {
                content: content = tinymce.is(content) ? content : this.getContent(),
                no_events: !0,
                format: "raw"
            };
            return !1 !== ed.settings.source_validate_content && ed.onWfEditorSave.dispatch(ed, content), 
            /TEXTAREA|INPUT/i.test(el.nodeName) ? el.value = content.content : el.innerHTML = content.content, 
            debounced && ed.onWfEditorChange.dispatch(ed, content), content.content;
        }, this.getActiveLine = function() {
            var blocks = [], line = 0, node = (tinymce.each(ed.schema.getBlockElements(), function(value, name) {
                if (/\W/.test(name)) return !0;
                blocks.push(name.toLowerCase());
            }), ed.selection.getNode()), nodes = ed.getBody().querySelectorAll(blocks.join(","));
            if (node) {
                1 === node.nodeType && "bookmark" !== node.getAttribute("data-mce-type") || (node = node.parentNode);
                for (var i = 0, len = nodes.length; i < len; i++) if (nodes[i] === node) {
                    line = i;
                    break;
                }
            }
            return line;
        }, this.toggle = function() {
            var s = ed.settings, element = ed.getElement(), container = element.parentNode, div = DOM.get(ed.id + "_editor_source"), iframe = DOM.get(ed.id + "_editor_source_iframe"), ifrHeight = (statusbar = DOM.get(ed.id + "_editor_source_resize"), 
            parseInt(DOM.get(ed.id + "_ifr").style.height, 10) || s.height), o = Storage.getHash("TinyMCE_" + ed.id + "_size");
            o && o.height && (ifrHeight = o.height);
            var iframeContainer, resize, options, content = (tinymce.is(element.value) ? element.value : element.innerHTML).replace(/<br data-mce-bogus="1"([^>]+)>/gi, ""), selection = "", line = this.getActiveLine(), element = (ed.selection.isCollapsed() || (o = ed.selection.getNode()) !== ed.getBody() && (selection = o.outerHTML), 
            ed.settings.container_height || sessionStorage.getItem("wf-editor-container-height") || ifrHeight), o = ed.settings.container_width || sessionStorage.getItem("wf-editor-container-width");
            div ? (DOM.show(div), (ifrHeight = iframe.contentWindow.codemirror).setContent(content, !0), 
            selection && ifrHeight.setSelection(line, selection), ifrHeight.setCursor(line), 
            DOM.removeClass(container, "mce-loading"), resizeTo(0, element)) : (createToolbar(div = DOM.add(container, "div", {
                role: "textbox",
                id: ed.id + "_editor_source",
                class: "wf-editor-source " + s.skin_class || "defaultSkin"
            })), iframeContainer = DOM.add(div, "div", {
                class: "mceIframeContainer"
            }), statusbar = DOM.add(div, "div", {
                id: ed.id + "_editor_source_statusbar",
                class: "mceStatusbar mceLast"
            }, '<div class="mcePathRow"></div><div tabindex="-1" class="mceResize" id="' + ed.id + '_editor_source_resize"><span class="mceIcon mce_resize"></span></div>'), 
            resize = DOM.get(ed.id + "_editor_source_resize"), Event.add(resize, "click", function(e) {
                e.preventDefault();
            }), ifrHeight = ed.getParam("source_theme", "codemirror"), options = {
                theme: {
                    codemirror: "oneLight",
                    "codemirror-dark": "oneDark"
                }[ifrHeight] || ifrHeight,
                format: ed.getParam("source_format", !0),
                tag_closing: ed.getParam("source_tag_closing", !0),
                selection_match: ed.getParam("source_selection_match", !0),
                change: function() {
                    ControlManager.get("source_undo").setDisabled(!1);
                }
            }, each([ "wrap", "linenumbers", "highlight" ], function(key) {
                options[key] = getActionState(key, ed.getParam("source_" + key, !0));
            }), function(container, width, height) {
                var self = this;
                return new Promise(function(resolve, reject) {
                    var ifr = DOM.create("iframe", {
                        id: ed.id + "_editor_source_iframe"
                    }), html = '<html><head xmlns="http://www.w3.org/1999/xhtml">   <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />   <link type="text/css" data-cfasync="false" rel="stylesheet" href="' + url + "/../../vendor/codemirror/css/style.min.css?" + ed.settings.token + '" /></head><body></body>';
                    ifr.onload = function() {
                        ifr.onload = null;
                        var win = this.contentWindow, doc = win.document, style = (doc.open(), 
                        doc.write(html), doc.close(), ed.settings.source_font_size && (style = DOM.create("style", {}, "body .cm-editor { font-size: " + ed.settings.source_font_size + "px; }"), 
                        doc.head.appendChild(style)), DOM.create("script", {
                            src: url + "/../../vendor/codemirror/js/script.min.js?" + ed.settings.token
                        }));
                        style.onload = function() {
                            resolve(win.codemirror);
                        }, doc.body.appendChild(style);
                    }, ifr.src = "", container.appendChild(ifr), self.resizeTo(width, height);
                });
            }(iframeContainer, o, element).then(function(codemirror) {
                codemirror.init(options), codemirror.setContent(content, !0), options.wrap || codemirror.toggleWrap(), 
                options.linenumbers || codemirror.toggleNumbers(), options.highlight || codemirror.toggleHighlight(), 
                selection && codemirror.setSelection(line, selection), codemirror.setCursor(line), 
                DOM.removeClass(container, "mce-loading"), iframe = iframeContainer.firstChild;
            }), resizeTo(0, element), Event.add(resize, "mousedown", function(e) {
                e.preventDefault();
                var mm1, mm2, mu1, mu2, sx, sy, sw, sh, w, h, ifrDoc = iframe.contentWindow.document;
                function resizeTo(w, h) {
                    w = Math.max(w, 300), h = Math.max(h, 200), iframe.style.height = h + "px", 
                    container.style.maxWidth = w + "px", ed.settings.container_width = w, 
                    ed.settings.container_height = h + statusbar.offsetHeight, h -= ed.settings.interface_height || 0, 
                    ed.theme.resizeTo(w, h), resizeTo(w, h);
                }
                function resizeOnMove(e) {
                    e.preventDefault(), w = sw + (e.screenX - sx), h = sh + (e.screenY - sy), 
                    resizeTo(w, h), DOM.addClass(resize, "wf-editor-source-resizing");
                }
                function endResize(e) {
                    e.preventDefault(), Event.remove(DOM.doc, "mousemove", mm1), 
                    Event.remove(DOM.doc, "mouseup", mu1), Event.remove(ifrDoc, "mousemove", mm2), 
                    Event.remove(ifrDoc, "mouseup", mu2), w = sw + (e.screenX - sx), 
                    h = sh + (e.screenY - sy), resizeTo(w, h), DOM.removeClass(resize, "wf-editor-source-resizing");
                }
                if (DOM.hasClass(resize, "wf-editor-source-resizing")) return endResize(e), 
                !1;
                sx = e.screenX, sy = e.screenY, sw = w = container.offsetWidth, 
                sh = h = iframe.clientHeight, mm1 = Event.add(DOM.doc, "mousemove", resizeOnMove), 
                mu1 = Event.add(DOM.doc, "mouseup", endResize), mm2 = Event.add(ifrDoc, "mousemove", resizeOnMove), 
                mu2 = Event.add(ifrDoc, "mouseup", endResize);
            }));
        }, this.getCursorPos = function() {
            var iframe = DOM.get(ed.id + "_editor_source_iframe");
            if (iframe) {
                iframe = iframe.contentWindow.codemirror;
                if (iframe) return iframe.getCursor();
            }
            return 0;
        }, this.getSelection = function() {
            var iframe = DOM.get(ed.id + "_editor_source_iframe");
            if (iframe) {
                iframe = iframe.contentWindow.codemirror;
                if (iframe) return iframe.getSelection();
            }
            return "";
        }, this.isHidden = function() {
            return DOM.isHidden(ed.id + "_editor_source");
        };
    });
}();