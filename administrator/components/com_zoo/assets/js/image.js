/* Copyright (C) YOOtheme GmbH, https://www.gnu.org/licenses/gpl.html GNU/GPL */

jQuery(function(i){var s=!Joomla.initialiseModal,d=s?location.href.match(/^(.+)administrator\/index\.php.*/i)[1]:Joomla.getOptions("system.paths").rootFull;i("input.image-select").each(function(t){var e=i(this),a="image-select-"+t,o=i('<button type="button">').text("Select Image").insertAfter(e),n=i("<span>").addClass("image-cancel").insertAfter(e),l=i("<div>").addClass("image-preview").insertAfter(o);e.attr("id",a),r(e),n.click(function(){e.val(""),l.empty()}),o.click(function(c){c.preventDefault(),Joomla.initialiseModal?m(e):SqueezeBox.fromElement(this,{handler:"iframe",url:"index.php?option=com_media&view=images&tmpl=component&e_name="+e.attr("id"),size:{x:800,y:500}})})});function r(t){var e=i(".image-preview",t.parent());e.html(""),t.val()&&i("<img>").attr("src",d+t.val()).appendTo(e)}i.isFunction(window.jInsertEditorText)&&(window.insertTextOld=window.jInsertEditorText),window.jInsertEditorText=function(t,e){if(e.match(/^image-select-/)){var a=i("#"+e),o=t.match(/src="([^\"]*)"/)[1],n=a.parent().find("div.image-preview").html(t),l=n.find("img");l.attr("src",d+o),a.val(o)}else i.isFunction(window.insertTextOld)&&window.insertTextOld(t,e)},document.addEventListener("onMediaFileSelected",function(){const e=Joomla.Modal.getCurrent().querySelector(".modal-body");if(!e)return;const a=e.querySelector("joomla-field-mediamore");a&&a.parentNode.removeChild(a)});function m(t){var e="index.php?option=com_media&amp;view=media&amp;tmpl=component&amp;mediatypes=0&amp;asset=com_content&amp;path=",a=i(`<div tabindex="-1" class="joomla-modal modal fade" aria-modal="true" role="dialog">
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
</div>`).insertBefore(t)[0];Joomla.initialiseModal(a,{isJoomla:!0}),a.querySelector(".button-save-selected").addEventListener("click",function(){Joomla.getMedia(Joomla.selectedMediaFile,t[0],{updatePreview:function(){},markValid:function(){},setValue:function(o){t.val(o)}}).then(function(){t.val(t.val().replace(/#.*/,"")),r(t),a.close()})}),a.addEventListener("hidden.bs.modal",function(o){i(a).remove()}),Joomla.selectedMediaFile={},a.open()}});
