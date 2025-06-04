(function(e) {
	e.extend(e.fn, {
		swapClass : function(e, t) {
			var n = this.filter("." + e);
			this.filter("." + t).removeClass(t).addClass(e);
			n.removeClass(e).addClass(t);
			return this
		},
		replaceClass : function(e, t) {
			return this.filter("." + e).removeClass(e).addClass(t).end()
		},
		hoverClass : function(t) {
			t = t || "hover";
			return this.hover(function() {
				e(this).addClass(t)
			}, function() {
				e(this).removeClass(t)
			})
		},
		heightToggle : function(e, speed, t) {
			e ? this.animate({
				height : "toggle"
			}, speed, t) : this.each(function() {
				jQuery(this)[jQuery(this).is(":hidden") ? "show" : "hide"]();
				if (t)
					t.apply(this, arguments)
			})
		},
		heightHide : function(e, t) {
			if (e) {
				this.animate({
					height : "hide"
				}, e, t)
			} else {
				this.hide();
				if (t)
					this.each(t)
			}
		},
		prepareBranches : function(e, parentWrappedSet) {
			if (!e.prerendered) {
				this.filter(":last-child:not(ul)").addClass(t.last);

				if(e.collapsed) {
					var branchSelector = '';
					var negativeBranchSelector = new Array();
					jQuery.each(e.contentCollapsed, function(sourceHash, sourceCollapsed){
						if(sourceCollapsed == 2) {
							if(sourceHash.indexOf('com_content') != -1) {
								branchSelector += ":not(ul[data-hash*='" + sourceHash + "'])";
							} else {
								branchSelector += ":not(ul[data-hash='" + sourceHash + "'])";
								negativeBranchSelector.push("ul[data-hash='" + sourceHash + "']");
							}
						}
					});
					if(branchSelector) {
						var filteredUL = parentWrappedSet.filter(branchSelector);
						if(negativeBranchSelector.length) {
							filteredUL.each(function(index, filteredULElement){
								if(!jQuery(filteredULElement).parents(negativeBranchSelector.join()).length) {
									jQuery(filteredULElement).find(">li>ul").hide();			
								}
							});
						} else {
							filteredUL.find(">li>ul,li[data-hash] ul").hide();
						}
					} else {
						this.filter(":not(." + t.open + ")").find(">ul").hide();
					}
				}
				
				if(!e.collapsed) {
					var branchSelector = new Array();
					jQuery.each(e.contentCollapsed, function(sourceHash, sourceCollapsed){
						if(sourceCollapsed == 1) {
							if(sourceHash.indexOf('com_content') != -1) {
								branchSelector.push("ul[data-hash*='" + sourceHash + "']");
							} else {
								branchSelector.push("ul[data-hash='" + sourceHash + "']");
							}
						}
					});
					if(branchSelector.length) {
						var filteredUL = parentWrappedSet.filter(branchSelector.join());
						filteredUL.find("li ul").hide();
					}
				}
			}
			return this.filter(":has(>ul)")
		},
		applyClasses : function(n, r) {
			this.filter(":not(.noexpandable):has(>ul):not(:has(>a))").find(">span").off("click.treeview").on("click.treeview", function(t) {
				if (this == t.target)
					r.apply(e(this).next())
			}).add(e("a", this)).hoverClass();
			if (!n.prerendered) {
				this.filter(":not(.noexpandable):has(>ul:hidden)").addClass(t.expandable).replaceClass(t.last, t.lastExpandable);
				this.filter(":not(.noexpandable)").not(":has(>ul:hidden)").addClass(t.collapsable).replaceClass(t.last, t.lastCollapsable);
				var i = this.not(".noexpandable").find("div." + t.hitarea);
				if (!i.length)
					i = this.not(".noexpandable").prepend('<div class="' + t.hitarea + '"/>').find("div." + t.hitarea);
				i.removeClass().addClass(t.hitarea).each(function() {
					var t = "";
					e.each(e(this).parent().attr("class").split(" "), function() {
						t += this + "-hitarea "
					});
					e(this).addClass(t)
				})
			}
			this.find("div." + t.hitarea).off("click.treeview").on("click.treeview", r);
		},
		treeview : function(n) {
			var parentWrappedSet = this;
			function r(n, r) {
				function s(r) {
					return function() {
						i.apply(e("div." + t.hitarea, n).filter(function() {
							return r ? e(this).parent("." + r).length : true
						}));
						return false
					}
				}
				e("a:eq(0)", r).click(s(t.collapsable));
				e("a:eq(1)", r).click(s(t.expandable));
				e("a:eq(2)", r).click(s())
			}
			function i() {
				e(this).parent().find(">.hitarea").swapClass(t.collapsableHitarea, t.expandableHitarea).swapClass(t.lastCollapsableHitarea, t.lastExpandableHitarea).end()
						.swapClass(t.collapsable, t.expandable).swapClass(t.lastCollapsable, t.lastExpandable).find(">ul").heightToggle(n.animated, n.animateSpeed, n.toggle);
				if (n.unique) {
					e(this).parent().siblings().find(">.hitarea").replaceClass(t.collapsableHitarea, t.expandableHitarea).replaceClass(t.lastCollapsableHitarea, t.lastExpandableHitarea).end()
							.replaceClass(t.collapsable, t.expandable).replaceClass(t.lastCollapsable, t.lastExpandable).find(">ul").heightHide(n.animated, n.toggle)
				}
			}
			function s() {
				function t(e) {
					return e ? 1 : 0
				}
				var r = [];
				a.each(function(t, n) {
					r[t] = e(n).is(":has(>ul:visible)") ? 1 : 0
				});
				e.cookie(n.cookieId, r.join(""), n.cookieOptions)
			}
			function o() {
				var t = e.cookie(n.cookieId);
				if (t) {
					var r = t.split("");
					a.each(function(t, n) {
						e(n).find(">ul")[parseInt(r[t]) ? "show" : "hide"]()
					})
				}
			}
			n = e.extend({
				cookieId : "treeview"
			}, n);
			if (n.toggle) {
				var u = n.toggle;
				n.toggle = function() {
					return u.apply(e(this).parent()[0], arguments)
				}
			}
			this.data("toggler", i);
			this.addClass("treeview");
			var a = this.find("li").prepareBranches(n, parentWrappedSet);
			
			if(e('#jmap_toggler #jmap_toggler_all').length) {
				e('#jmap_toggler #jmap_toggler_all').on('click.treeview', function(){
					e('div.jmapcolumn ul > li.expandable > div.hitarea').trigger('click');
				});
			}
			if(e('#jmap_toggler #jmap_toggler_none').length) {
				e('#jmap_toggler #jmap_toggler_none').on('click.treeview', function(){
					e('div.jmapcolumn ul > li.collapsable > div.hitarea').trigger('click');
				});
			}
			
			switch (n.persist) {
			case "cookie":
				var f = n.toggle;
				n.toggle = function() {
					s();
					if (f) {
						f.apply(this, arguments)
					}
				};
				o();
				break;
			case "location":
				var l = this.find("a").filter(function() {
					return this.href.toLowerCase() == location.href.toLowerCase()
				});
				if (l.length) {
					var c = l.addClass("selected").parents("ul, li").add(l.next()).show();
					if (n.prerendered) {
						c.filter("li").swapClass(t.collapsable, t.expandable).swapClass(t.lastCollapsable, t.lastExpandable).find(">.hitarea").swapClass(t.collapsableHitarea, t.expandableHitarea)
									  .swapClass(t.lastCollapsableHitarea, t.lastExpandableHitarea)
					}
				}
				break;
			case "none":
				break
			}
			a.applyClasses(n, i);
			if (n.control) {
				r(this, n.control);
				e(n.control).show()
			}
			var h = e("li>span.folder");
			e.each(h, function(t, n) {
				var r = e(n).text();
				var i = r.replace(/^\s+|\s+$/g, "");
				if (i.length == 0) {
					e(n).parent("li").css("list-style-type", "none");
					if (e(n).css("background-image") === "none") {
						e(n).hide()
					}
				}
			});
			return this
		}
	});
	e.treeview = {};
	var t = e.treeview.classes = {
		open : "open",
		closed : "closed",
		expandable : "expandable",
		expandableHitarea : "expandable-hitarea",
		lastExpandableHitarea : "lastExpandable-hitarea",
		collapsable : "collapsable",
		collapsableHitarea : "collapsable-hitarea",
		lastCollapsableHitarea : "lastCollapsable-hitarea",
		lastCollapsable : "lastCollapsable",
		lastExpandable : "lastExpandable",
		last : "last",
		hitarea : "hitarea"
	}
})(jQuery);

jQuery(function($) {
	if(typeof(jmapExpandContentTree) === 'undefined') { jmapExpandContentTree = '{}'; }
	var defaultOptions = {
			persist : jmapExpandLocation,
			collapsed : !jmapExpandAllTree,
			contentCollapsed : JSON.parse(jmapExpandContentTree),
			unique : false,
			animated: jmapAnimated,
			animateSpeed: jmapAnimateSpeed
		};
	$("ul.jmap_filetree").treeview(defaultOptions);
	
	$(function(){
		var recursiveBackground = function(parentElement) {
			var parentBgColor = $(parentElement).css('background-color');
			if((parentBgColor == 'rgba(0, 0, 0, 0)' || parentBgColor == 'transparent') && parentElement.length) {
				recursiveBackground(parentElement.parent());
			} else {
				$('#jmap_sitemap div.jmapcolumn>ul>li>div.lastCollapsable-hitarea').css('background-color', parentBgColor);
				$('#jmap_sitemap div.jmapcolumn>ul>li>div.lastExpandable-hitarea').css('background-color', parentBgColor);
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child>li:last-child li.last').addClass('jmap_last_before');
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child>li:last-child.last').addClass('jmap_last_before');
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul:last-child>li.expandable:last-child').addClass('jmap_last_before');
				$('#jmap_sitemap div.jmapcolumn>ul.treeview>li>ul>li.expandable:last-child li.expandable').addClass('jmap_last_before');

				$("<style type='text/css'>li.jmap_last_before.expandable:before,li.jmap_last_before.last:before{ background-color:" + parentBgColor +";</style>").appendTo("head");
			}
		}
		if($('#jmap_sitemap').data('template') == 'mindmap') {
			recursiveBackground($('#jmap_sitemap').parent());
			
			if(jmapDraggableSitemap) {
				var tmp_handler = function(){};
				$('div.jmapcolumn>ul').draggable({
					opacity: .8,
					addClasses: false,
					zIndex: 100,
					distance: 10,
					start : function(event,ui){
						try{
							tmp_handler = $._data( $('span', event.target)[0], "events" ).click[0].handler;
						} catch(e){}
						$('span', this).off('.treeview');
					},
					stop : function(event,ui){
						setTimeout(function(){
							$('span', event.target).on("click.treeview", tmp_handler)
						}, 300);
						
						try {
							if(document.elementFromPoint) {
								var elementOnCoordinates = document.elementFromPoint(ui.offset.left - 1, ui.offset.top - 1);
								var parentBgColor = 'rgba(0, 0, 0, 0)';
								var recursiveElementOnCoordinatesBackground = function(parentElement) {
									parentBgColor = parentElement.css('background-color');
									if((parentBgColor == 'rgba(0, 0, 0, 0)' || parentBgColor == 'transparent') && parentElement.length) {
										recursiveElementOnCoordinatesBackground(parentElement.parent());
									}
									return parentBgColor;
								}
								
								var elementOnCoordinatesBgColor = recursiveElementOnCoordinatesBackground($(elementOnCoordinates));
								var uniqueID = 'repositioned' + Math.floor((Math.random() * 100) + 1);
								$(event.target).attr('id', uniqueID);
								$('#' + uniqueID + '>li:first-child>div.lastCollapsable-hitarea').css('background-color', elementOnCoordinatesBgColor);
								$('#' + uniqueID + '>li:first-child>div.lastExpandable-hitarea').css('background-color', elementOnCoordinatesBgColor);
								$("<style type='text/css'>#" + uniqueID + " li.jmap_last_before.expandable:before,#" + uniqueID + " li.jmap_last_before.last:before{ background-color:" + elementOnCoordinatesBgColor +";</style>").appendTo("head");
							}
						} catch(e){}
					}
				});
			}
		}
	});
	
	if(jmapExpandFirstLevel) {
		$('div.jmapcolumn>ul>li.expandable.lastExpandable>div.hitarea').trigger('click');
	}
	
	if(!$.isEmptyObject(jmapLinkableCatsSources)) {
		$.each(jmapLinkableCatsSources, function(linkableList, linkableMode){
			var dataSourcePromise = $.Deferred(function(defer) {
				setTimeout(function(){
					var ulCategoryList = $('ul[data-hash=' + linkableList + ']').get(0);
					if(!ulCategoryList) return;
					var ulCategoryListLinks = $('a', $(ulCategoryList));
					if(!ulCategoryListLinks.length) return;
					defer.resolve(ulCategoryList, ulCategoryListLinks, linkableList, linkableMode);
				}, 0);
			}).promise();
			
			dataSourcePromise.then(function(ulCategoryList, ulCategoryListLinks, linkableList, linkableMode) {
				if(linkableMode == 'yeshide') {
					$('ul[data-hash=' + linkableList + ']').hide();
				}
				var struct = {};
				$.each(ulCategoryListLinks, function(index, link){
					var href = $(link).attr('href');
					var pkey = $(link).text();
					struct[pkey] = href;
				});
				var target = $(ulCategoryListLinks.get(0)).attr('target');
				var targetString = target ? 'target="' + target + '"' : 'target="_self"';
				var ulLinkableLists = $('ul[data-hash="' + linkableList + '\.items"]');
				$.each(ulLinkableLists, function(index, singleLinkableList){
					var spansToReplace = $('ul.jmap_filetree span.folder', singleLinkableList);
					$.each(spansToReplace, function(k, spanElem){
						var spanElemPKey = $(spanElem).text();
						if(struct[spanElemPKey]) {
							$(spanElem).text('');
							$(spanElem).append('<a ' + targetString + ' href="' + struct[spanElemPKey] + '">' + spanElemPKey + '</a>');
						}
					});
				});
			});
		});
	}
	
	if(jmapMergeAliasMenu) {
		var dataSourceMenuAliasPromise = $.Deferred(function(defer) {
			setTimeout(function(){
				var ulMenuMergeAliasList = $('ul.jmap_filetree_menu li[data-merge]');
				if(!ulMenuMergeAliasList.length) return;
				defer.resolve(ulMenuMergeAliasList);
			}, 0);
		}).promise();
		
		dataSourceMenuAliasPromise.then(function(ulMenuMergeAliasList) {
			var allMenuLinksToEvaluate = $('ul.jmap_filetree_menu li:not([data-merge]) a').filter(function(){
				return $(this).parents('li[data-merge]').length ? false : true;
			});
			$.each(allMenuLinksToEvaluate, function(index, menuLinkToEvaluate) {
				var hrefLink = $(menuLinkToEvaluate).attr('href');
				$.each(ulMenuMergeAliasList, function(k, originalMergeSource){
					var $originalMergeSource = $(originalMergeSource);
					var currentDataMerge = $(originalMergeSource).data('merge');
					var currentElementMerge = $('ul', $originalMergeSource).get(0);
					if(currentElementMerge && currentDataMerge == hrefLink) {
						var clonedDomElement = $(currentElementMerge).clone(true, true).css('display', 'block');
						var parentsUL = $originalMergeSource.parents('ul:not(.jmap_filetree_menu )');
						var directLIParent = $($originalMergeSource.parents('li.collapsable').get(0));
						$originalMergeSource.remove()
						if(!$('li', parentsUL).length) {
							parentsUL.parents('ul.jmap_filetree_menu').remove();
						}
						if(!$('li', directLIParent).length) {
							directLIParent.removeClass('collapsable lastCollapsable');
						}
						$(menuLinkToEvaluate).after(clonedDomElement);
						$(menuLinkToEvaluate).before('<div class="hitarea collapsable-hitarea"></div>');
						$($(menuLinkToEvaluate).parents('ul').get(0)).treeview(defaultOptions);
					}
				});
			});
		});
	}
	
	if(!$.isEmptyObject(jmapMergeMenuTree)) {
		$.each(jmapMergeMenuTree, function(mergebleListIdentifier, linkableMode) {
			var dataSourcePromise = $.Deferred(function(defer) {
				if(mergebleListIdentifier == 'com_content') {return;}
				setTimeout(function(){
					var ulCategoryList = $('ul[data-hash="' + mergebleListIdentifier + '"]');
					if(!ulCategoryList.length) return;
					if(linkableMode == 'yeshide') {
						$('ul[data-hash="' + mergebleListIdentifier + '"]').hide();
					}
					
					var ulCategoryListUl = $('ul[data-hash^="' + mergebleListIdentifier + '"]', $(ulCategoryList));
					if(!ulCategoryListUl.length) return;
					
					defer.resolve(ulCategoryListUl, mergebleListIdentifier, linkableMode);
				}, 0);
			}).promise();
			
			dataSourcePromise.then(function(ulCategoryListUl, mergebleListIdentifier, linkableMode) {
				var struct = {};
				$.each(ulCategoryListUl, function(index, listUl){
					var dataHash = $(listUl).data('hash');
					var pkey = $('a', listUl).text();
					struct[pkey] = dataHash;
				});
				
				var ulItemsAnchors = $('ul[data-hash="' + mergebleListIdentifier + '\.items"] span.folder');
				$.each(ulItemsAnchors, function(index, ulItemsAnchor){
					var anchorElemPKey = $(ulItemsAnchor).text();
					if(struct[anchorElemPKey]) {
						var $genericListItem = $('ul.jmap_filetree li[data-hash="' + struct[anchorElemPKey] + '"]');
						var genericListItemExpandable = $genericListItem.hasClass('expandable')
						var displayClass = genericListItemExpandable ? 'none' : 'block';
						var targetUlToAppend = $(ulItemsAnchor).parents('ul.jmap_filetree').get(0);
						var clonedDomElement = $(targetUlToAppend).clone(true, true).css('display', displayClass);
						$genericListItem.append(clonedDomElement);
					}
				});
				
				if(!ulItemsAnchors.length && mergebleListIdentifier == 'com_igallery') {
					var ulItemsAnchors = $('ul[data-hash*="' + mergebleListIdentifier + '\.category"]');
					$.each(ulItemsAnchors, function(index, ulItemsAnchor){
						var anchorElemPKey = $(ulItemsAnchor).text();
						if(struct[anchorElemPKey]) {
							var $genericListItem = $('ul.jmap_filetree li[data-hash="' + struct[anchorElemPKey] + '"]');
							var genericListItemExpandable = $genericListItem.hasClass('expandable')
							var displayClass = genericListItemExpandable ? 'none' : 'block';
							var clonedDomElement = $(ulItemsAnchor).clone(true, true).css('display', displayClass);
							$genericListItem.append(clonedDomElement);
						}
					});
				}
				
				if(linkableMode == 'yeshide') {
					$('ul[data-hash="' + mergebleListIdentifier + '\.items"]').hide();
				}
			});
			
			var dataSourceContentPromise = $.Deferred(function(defer) {
				if(mergebleListIdentifier != 'com_content') {return;}
				setTimeout(function(){
					var ulCategoryContentList = $('ul.jmap_filetree[data-hash^="' + mergebleListIdentifier + '"]');
					if(!ulCategoryContentList.length) return;
					if(linkableMode == 'yeshide') {
						$('ul.jmap_filetree[data-hash^="' + mergebleListIdentifier + '"]').hide();
					}
					
					defer.resolve(ulCategoryContentList, mergebleListIdentifier, linkableMode);
				}, 0);
			}).promise();
			
			dataSourceContentPromise.then(function(ulCategoryContentList, mergebleListIdentifier, linkableMode) {
				$.each(ulCategoryContentList, function(index, ulCategoryContent) {
					var sourceHash = $(ulCategoryContent).data('hash');
					var alternativeSourceHash = sourceHash.replace('category', 'categories');
					var $genericListItems = $('ul.jmap_filetree li[data-hash="' + sourceHash + '"],ul.jmap_filetree li[data-hash="' + alternativeSourceHash + '"]');
					if(!$genericListItems.length) {return true;}
					$.each($genericListItems, function(index, genericListItem) {
						var $genericListItem = $(genericListItem);
						var genericListItemExpandable = $genericListItem.hasClass('expandable')
						var displayClass = genericListItemExpandable ? 'none' : 'block';
						var clonedDomElement = $(ulCategoryContent).clone(true, true).css({'display':'block', 'height':'auto'});
						$genericListItem.append(clonedDomElement).addClass('fulltreepopulated');
					});
				});
			});
			
			if(typeof(jmapMergeMenuTreeLevels) !== 'undefined' && jmapMergeMenuTreeLevels == 'topchildlevels') {
				var dataSourceContentChildPromise = $.Deferred(function(defer) {
					if(mergebleListIdentifier != 'com_content') {return;}
					setTimeout(function(){
						var ulCategoryContentList = $('*[data-hash="com_content.container"] ul.jmap_filetree[data-hash-catid^="' + mergebleListIdentifier + '"]');
						if(!ulCategoryContentList.length) return;
						
						defer.resolve(ulCategoryContentList, mergebleListIdentifier, linkableMode);
					}, 0);
				}).promise();
				
				dataSourceContentChildPromise.then(function(ulCategoryContentList, mergebleListIdentifier, linkableMode) {
					$.each(ulCategoryContentList, function(index, ulCategoryContent) {
						var $ulCategoryContent = $(ulCategoryContent);
						var sourceHash = $ulCategoryContent.data('hash');
						var sourceCatidHash = $ulCategoryContent.data('hash-catid');
						var alternativeSourceCatidHash = sourceCatidHash.replace('category', 'categories');
						var $genericListItems = $('ul.jmap_filetree li[data-hash="' + sourceCatidHash + '"],ul.jmap_filetree li[data-hash="' + alternativeSourceCatidHash + '"]').not('.fulltreepopulated');
						if(!$genericListItems.length) {return true;}
						$.each($genericListItems, function(index, genericListItem) {
							var $genericListItem = $(genericListItem);
							var genericListItemExpandable = $genericListItem.hasClass('expandable')
							var displayClass = genericListItemExpandable ? 'none' : 'block';
							var clonedDomElement = $ulCategoryContent.clone(true, true).css({'display':displayClass, 'height':'auto'});
							$genericListItem.append(clonedDomElement);
							var ulCategoryContentLevel = $ulCategoryContent.data('level');
							var allChildItems = $ulCategoryContent.nextAll('ul.jmap_filetree[data-hash="' + sourceHash + '"]')
																.filter(function(index){
																	var itemLevel = $(this).data('level');
																	return itemLevel >= ulCategoryContentLevel;
																})
																.clone(true, true)
																.css({'display':displayClass, 'height':'auto'});
							$.each(allChildItems, function(index, element){
								if($(element).data('level') == ulCategoryContentLevel) {
									return false;
								}
								genericListItem.append(element);
							});
						});
					});
				});
			}
		});
	}
	
	if(jmapMergeGenericMenuByClass) {
		var dataSourceAllMenusPromise = $.Deferred(function(defer) {
			setTimeout(function(){
				var ulMenuGenericList = $('ul.jmap_filetree[data-hash]').filter(function(){
					var dataHash = $(this).data('hash');
					var isContent = dataHash.search('com_content') == -1 ? true : false;
					return isContent;
				});
				if(!ulMenuGenericList.length) return;
				defer.resolve(ulMenuGenericList);
			}, 0);
		}).promise();
		
		dataSourceAllMenusPromise.then(function(ulMenuGenericList) {
			$.each(ulMenuGenericList, function(index, genericListItem) {
				var $genericListItem = $(genericListItem);
				var genericListItemHash = $genericListItem.attr('data-hash').replace('.', '\\.');
				var matchingGenericMenuItem = $('ul.jmap_filetree_menu li.' + genericListItemHash + ' > a').get(0);
				var $matchingGenericMenuItemLi = $('ul.jmap_filetree_menu li.' + genericListItemHash);
				var genericListItemExpandable = $matchingGenericMenuItemLi.hasClass('expandable')
				var displayClass = genericListItemExpandable ? 'none' : 'block';
				if(matchingGenericMenuItem) {
					var clonedDomElement = $genericListItem.clone(true, true).css('display', displayClass);
					$(matchingGenericMenuItem).after(clonedDomElement);
					$genericListItem.remove();
				}
			});
		});
	}
	
	if(jmapGojsSitemap) {
		(function initGoJsJMapTreeLayout() {
			$('#jmap_sitemap').addClass('gojs_sitemap');
			var gojsPromise = $.Deferred(function(defer) {
				setTimeout(function(){
					defer.resolve();
				}, 0);
			}).promise();
			
			gojsPromise.then(function() {
				var getTextWidth = function(text, font) {
				    var canvas = getTextWidth.canvas || (getTextWidth.canvas = document.createElement("canvas"));
				    var context = canvas.getContext("2d");
				    context.font = font;
				    var metrics = context.measureText(text);
				    return metrics.width;
				}
				if(typeof(go) === 'undefined') {
					$('#jmap_sitemap').removeClass('gojs_sitemap');
					return;
				}
				var gojs = go.GraphObject.make;
				var horizontalOrientation = jmapisRTLLanguage ? 180 : 0;
				var jMapTreeDiagram = gojs(go.Diagram, "gojsjmaptreelayout", {
					initialAutoScale : (jmapGojsAutoScaleCanvas ? go.Diagram.UniformToFill : go.Diagram.None),
					layout : gojs(go.TreeLayout, {
						angle : (jmapGojsTreeOrientation == 'horizontal' ? horizontalOrientation : 90),
						comparer : go.LayoutVertex.smartComparer
					})
				});
				jMapTreeDiagram.addDiagramListener("InitialLayoutCompleted", function(e) {
					if(jmapGojsAutoHeightCanvas) {
						var dia = e.diagram;
						dia.div.style.height = (dia.documentBounds.height + 26) + "px";
					}
					if(!jmapDraggableSitemap) {
						e.diagram.nodes.each(function(n) { n.movable = false; });
					}
				});
				jMapTreeDiagram.nodeTemplate = gojs(go.Node, "Spot", {
					locationSpot : go.Spot.Center
				},  
				gojs(go.Shape, "Ellipse", {
					fill : "lightgray",
					stroke : null
				}, new go.Binding("fill", "fill")), 
				gojs("HyperlinkText",
			          function(node) { 
						return node.data.link; 
					},
			          function(node) { 
						var denominator = node.data.text.length > 1 ? node.data.text.length : 2;
						var ratio = 100 / denominator;
						var textWidth = getTextWidth(node.data.text, "normal 16px arial") + ratio;
						node.width = textWidth >= 50 ? textWidth : 50;
						node.height = 50;
						return node.data.text; 
					},
			          { margin: 10, stroke: jmapGojsNodeColorText }
			        )
				);
				jMapTreeDiagram.linkTemplate = gojs(go.Link, {
					routing : go.Link.Orthogonal,
					selectable : true
				}, gojs(go.Shape, {
					strokeWidth : 3,
					stroke : "#333"
				}));
				var nodeDataArray = [];
				var key = 0;
				var rootsColor = jmapGojsRootColor;
				var childrenColor = jmapGojsChildColor;
				var recursiveDataModelBuilder = function(element, level, parent) {
					var nodeColor = level == 0 ? rootsColor : childrenColor;
					var immediateLiChildren = $(element).children('li');
					$.each(immediateLiChildren, function(index, liElement){
						var $liElement = $(liElement);
						if(jmapHideEmptyCats && $liElement.hasClass('noexpandable last')) {
							return true;
						}
						var nodeText =  $liElement.children('span.folder, a').text();
						if(!nodeText) {
							return true;
						}
						
						var directAnchorChildren = $liElement.children('a');
						var nodeLink = directAnchorChildren.attr('href') || $liElement.children('span.folder').children('a').attr('href') || '';
						if(!nodeLink || (nodeLink && !directAnchorChildren.length)) {
							nodeColor = rootsColor;
						}
						key++;
						nodeDataArray.push({
							"key" : key,
							"text" : nodeText,
							"fill" : nodeColor,
							"link": nodeLink,
							"parent" : parent,
							"__gohashid" : key
						});
						var childUlElement = $liElement.children('ul');
						if(childUlElement.length) {
							level++;
							recursiveDataModelBuilder(childUlElement, level, key);
						}
					});
				}

				if($('div.jmapcolumn > ul[data-hash*=com_content][style*="margin-left"],div.jmapcolumn > ul.jmap_filetree li.collapsable ul[style*="margin-left"],div.jmapcolumn > ul.jmap_filetree li.expandable ul[style*="margin-left"]').length) {
					for(var i=150; i>0; i-=15) {
						$('div.jmapcolumn > ul[data-hash*=com_content][style="margin-left:' + i + 'px"],' +
						  'div.jmapcolumn > ul.jmap_filetree li.collapsable ul[style*="margin-left:' + i + 'px"],' +
						  'div.jmapcolumn > ul.jmap_filetree li.expandable ul[style*="margin-left:' + i + 'px"],'+
						  'div.jmapcolumn > ul.jmap_filetree li.collapsable ul[style*="margin-left: ' + i + 'px"],' +
						  'div.jmapcolumn > ul.jmap_filetree li.expandable ul[style*="margin-left: ' + i + 'px"]').each(function(index, childUlElement){
							var $childUlElement = $(childUlElement);
							var previousParent = $(childUlElement).prev('ul.jmap_filetree,ul[data-hash]');
							$(previousParent).children('li.lastExpandable,li.lastCollapsable,li.last').append(childUlElement);
						});
					}
				}
				
				$('div.jmapcolumn > ul:visible').each(function(index, rootUlElement){
					$(this).hide();
					recursiveDataModelBuilder(rootUlElement, 0, undefined);
				});
				jMapTreeDiagram.model = new go.TreeModel(nodeDataArray);
			});
		})();
	}
});