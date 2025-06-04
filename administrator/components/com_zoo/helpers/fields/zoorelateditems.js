/* Copyright (C) YOOtheme GmbH, https://www.gnu.org/licenses/gpl.html GNU/GPL */

function selectRelateditem(e,o,l){jQuery("#"+l).ZooRelatedItems("addItem",e,o),window.bootstrap?Joomla.Modal.current.close():jModalClose()}(function(e){var o=function(){};e.extend(o.prototype,{name:"ZooRelatedItems",options:{variable:null,msgDeleteItem:"Delete Item",msgSortItem:"Sort Item"},initialize:function(a,t){this.options=e.extend({},this.options,t),this.list=a.find("ul").on("click","div.item-delete",function(){e(this).closest("li").fadeOut(200,function(){e(this).remove()})}).sortable({handle:"div.item-sort",placeholder:"dragging",axis:"y",opacity:1,delay:100,tolerance:"pointer",containment:"parent",forcePlaceholderSize:!0,scroll:!1,start:function(i,n){n.helper.addClass("ghost")},stop:function(i,n){n.item.removeClass("ghost")}}),window.bootstrap&&a.on("click",".item-add",function(i){i.preventDefault(),l(i.currentTarget.href)})},addItem:function(a,t){var i=!1;this.list.find("li input").each(function(){e(this).val()==a&&(i=!0)}),i||e('<li><div><div class="item-name">'+t+'</div><div class="item-sort" title="'+this.options.msgSortItem+'"></div><div class="item-delete" title="'+this.options.msgDeleteItem+'"></div><input type="hidden" name="'+this.options.variable+'" value="'+a+'"/></div></li>').appendTo(this.list)}});function l(a){var t=e(`<div tabindex="-1" class="joomla-modal modal fade" aria-modal="true" role="dialog">
<div class="modal-dialog modal-lg jviewport-width80">
<div class="modal-content">
<div class="modal-header">
<h3 class="modal-title">Choose Item</h3>
</div>
<div class="modal-body jviewport-height60"><iframe class="iframe" src="`+a+`" name="Change Image" height="100%" width="100%"></iframe>
</div>
</div>
</div>`).appendTo("body")[0];Joomla.initialiseModal(t,{isJoomla:!0}),t.addEventListener("hidden.bs.modal",function(i){e(t).remove()}),t.open()}e.fn[o.prototype.name]=function(){var a=arguments,t=a[0]?a[0]:null;return this.each(function(){var i=e(this);if(o.prototype[t]&&i.data(o.prototype.name)&&t!="initialize")i.data(o.prototype.name)[t].apply(i.data(o.prototype.name),Array.prototype.slice.call(a,1));else if(!t||e.isPlainObject(t)){var n=new o;o.prototype.initialize&&n.initialize.apply(n,e.merge([i],a)),i.data(o.prototype.name,n)}else e.error("Method "+t+" does not exist on jQuery."+o.name)})}})(jQuery);
