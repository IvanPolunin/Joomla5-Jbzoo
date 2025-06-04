function formsSelectForm(id) {
    if ('jInsertEditorText' in window) {
        jInsertEditorText('[forms ID='+id+']', '$name');
        SqueezeBox.close();
        jModalClose();
    } else {
        for (var ind in Joomla.editors.instances) {
            Joomla.editors.instances[ind].replaceSelection('[forms ID='+id+']', '$name');
            break;
        }
        if (window.jQuery) {
            jQuery(Joomla.currentModal).modal('hide');
        }
    }
    if (window.Joomla.Modal) {
        window.parent.Joomla.Modal.getCurrent().close();
    }
}