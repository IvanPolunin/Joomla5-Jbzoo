function SelectGallery(id) {
    if ('jInsertEditorText' in window) {
        jInsertEditorText('[gallery ID='+id+']', '$name');
        if (window.SqueezeBox) {
            SqueezeBox.close();
        }
        if (window.jModalClose) {
            jModalClose();
        }
    } else {
        for (var ind in Joomla.editors.instances) {
            Joomla.editors.instances[ind].replaceSelection('[gallery ID='+id+']', '$name');
            break;
        }
        if (window.jQuery) {
            jQuery(Joomla.currentModal).modal('hide');
        }
    }
    if (window.parent.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
    }
}