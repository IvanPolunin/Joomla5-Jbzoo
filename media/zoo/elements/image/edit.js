/* Copyright (C) YOOtheme GmbH, https://www.gnu.org/licenses/gpl.html GNU/GPL */

jQuery(function(o){var l=!Joomla.initialiseModal,d=l?location.href.match(/^(.+)administrator\/index\.php.*/i)[1]:Joomla.getOptions("system.paths").rootFull;o("input.image-select").each(function(t){var e=o(this),a="image-select-"+t;e.attr("id",a),n(e)}),o(document).on("click",".image-cancel",function(){o("input",this.parentNode).val(""),o(".image-preview",this.parentNode).empty()}),o(document).on("click","button.image-select",function(t){t.preventDefault();var e=o("input",this.parentNode);e.attr("id")||e.attr("id","image-select-"+Math.round(Math.random()*1e5)),Joomla.initialiseModal?r(e):SqueezeBox.fromElement(this,{handler:"iframe",url:"index.php?option=com_media&view=images&tmpl=component&e_name="+e.attr("id"),size:{x:800,y:500}})});function n(t){var e=o(".image-preview",t.parent());e.html(""),t.val()&&o("<img>").attr("src",d+t.val()).appendTo(e)}o.isFunction(window.jInsertEditorText)&&(window.insertTextOld=window.jInsertEditorText),window.jInsertEditorText=function(t,e){if(e.match(/^image-select-/)){var a=o("#"+e),i=t.match(/src="([^\"]*)"/)[1];a.val(i),n(a)}else o.isFunction(window.insertTextOld)&&window.insertTextOld(t,e)},document.addEventListener("onMediaFileSelected",function(){const e=Joomla.Modal.getCurrent().querySelector(".modal-body");if(!e)return;const a=e.querySelector("joomla-field-mediamore");a&&a.parentNode.removeChild(a)});function r(t){var e="index.php?option=com_media&amp;view=media&amp;tmpl=component&amp;mediatypes=0&amp;asset=com_content&amp;path=",a=o(`<div tabindex="-1" class="joomla-modal modal fade" aria-modal="true" role="dialog">
<div class="modal-dialog modal-lg jviewport-width80">
<div class="modal-content">
<div class="modal-header">
<h3 class="modal-title">Choose Image</h3>
</div>
<div class="modal-body jviewport-height60"><iframe class="iframe" src="`+e+`" name="Change Image" height="100%" width="100%"></iframe>
</div>
<div class="modal-footer">
<button type="button" class="btn btn-success button-save-selected">Select</button><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button></div>
</div>
</div>
</div>`).insertBefore(t)[0];Joomla.initialiseModal(a,{isJoomla:!0}),a.querySelector(".button-save-selected").addEventListener("click",function(){Joomla.getMedia(Joomla.selectedMediaFile,t[0],{updatePreview:function(){},markValid:function(){},setValue:function(i){t.val(i)}}).then(function(){t.val(t.val().replace(/#.*/,"")),n(t),a.close()})}),a.addEventListener("hidden.bs.modal",function(i){o(a).remove()}),Joomla.selectedMediaFile={},a.open()}});
