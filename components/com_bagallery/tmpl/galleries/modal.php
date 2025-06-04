<?php
/**
* @package   BaGallery
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

use Balbooa\Component\Gallery\Site\Helper\GalleryHelper;
use Joomla\CMS\Factory;
use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die;
$about = GalleryHelper::aboutUs();
$v = $about->version;
$language = Factory::getLanguage();
$language->load('com_bagallery', JPATH_ADMINISTRATOR);
?> 
<link rel="stylesheet" href="components/com_bagallery/assets/css/ba-style.css?<?php echo $v; ?>" type="text/css"/>
<input type="hidden" class="constant-all" value="<?php echo $language->_('ALL'); ?>">
<div class="modal-shortcode">
    <form
    action="<?php echo Route::_('index.php?option=com_bagallery&view=galleries&layout=modal&tmpl=component&function=SelectGallery'); ?>"
    method="post" name="adminForm" id="adminForm" class="form-inline">
        <fieldset id="modal-filter">
            <input type="text" name="filter_search" placeholder="Enter gallery name" id="filter_search"
            value="<?php echo $this->escape($this->state->get('filter.search')); ?>"/>
            <i class="zmdi zmdi-search"></i>
            <button type="submit" class="ba-btn"><?php echo Text::_('JSEARCH_FILTER_SUBMIT'); ?></button>    
        </fieldset>
        <div class="gallery-table">
            <table class="gallery-list">
                <thead>
                    <tr>
                        <th><?php echo $language->_('GALLERIES'); ?></th>
                        <th><?php echo $language->_('CATEGORY'); ?></th>
                        <th><?php echo $language->_('ID'); ?></th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($this->items as $i => $item) { ?>
                    <tr>
                        <th class="gallery-title">
                            <a href="#" data-id="<?php echo $item->id; ?>"><?php echo $item->title; ?></a>
                        </th>
                        <td>
                            <a href="#"  class="gallery-category" data-category=""><?php echo $language->_('ALL'); ?></a>
                        </td>
                        <td><?php echo $item->id; ?></td>
                    </tr>
                <?php } ?>
                </tbody>
            </table>
        </div>
    </form>
    <div>
      <input type="hidden" name="task" value="" />
      <input type="hidden" name="boxchecked" value="0" />
      <?php echo HTMLHelper::_('form.token'); ?>
    </div>
</div>
<div id="category-dialog" class="modal hide ba-modal-md" style="display:none">
    <div class="modal-body">
        <table>
            <thead>
                <th></th>
                <th><?php echo $language->_('CATEGORY'); ?></th>
                <th><?php echo $language->_('ID'); ?></th>
            </thead>
            <tbody></tbody>
        </table>
    </div>
</div>
<script type="text/javascript">
document.addEventListener('DOMContentLoaded', function(){
    var category;
    async function fetchRequest(url)
    {
        let request = await fetch(url, {
                method: 'POST'
            }),
            response = await request.text();

        return response;
    }
    document.querySelectorAll('.gallery-title a').forEach(function(btn){
        btn.addEventListener('click', function(event){
            event.preventDefault();
            var id = this.dataset.id,
                cat = this.closest('tr').querySelector('.gallery-category').dataset.category;
            if (window.parent && window.parent.SelectGallery) {
                window.parent.SelectGallery(id+cat);
            } else if (window.parent.Joomla && window.parent.Joomla.editors) {
                for (var ind in window.parent.Joomla.editors.instances) {
                    window.parent.Joomla.editors.instances[ind].replaceSelection('[gallery ID='+id+cat+']');
                    break;
                }
                if (window.parent.Joomla.Modal) {
                    window.parent.Joomla.Modal.getCurrent().close();
                }
            }
        })
    });
    document.querySelectorAll('.gallery-category').forEach(function(btn){
        btn.addEventListener('click', function(event){
            event.preventDefault();
            category = this;
            var id = this.closest('tr').querySelector('.gallery-title a').dataset.id,
                cat = this.dataset.category;
            fetchRequest('index.php?option=com_bagallery&task=gallery.getCategories&gallery='+id).then(function(text){
                var obj = JSON.parse(text),
                    modal = document.querySelector('#category-dialog'),
                    str = '<tr data-id=""><td  class="checkbox"';
                str += '><input type="radio"';
                if (!cat) {
                    str += ' checked';
                }
                str += '><i class="zmdi zmdi-circle-o"></i>';
                str += '<i class="zmdi zmdi-check"></i></td><td class="title">'+document.querySelector('.constant-all').value;
                str += '</td><td></td></tr>';
                obj.forEach(function(el){
                    var settings = el.settings.split(';');
                    if (settings[3] != '*') {
                        str += '<tr data-id=" category ID='+el.id+'"><td';
                        str += ' class="checkbox"><input type="radio"';
                        if (cat == ' category ID='+el.id) {
                            str += ' checked';
                        }
                        str += '><i class="zmdi zmdi-circle-o"></i>';
                        str += '<i class="zmdi zmdi-check"></i></td><td class="title">'+el.title+'</td><td>'+el.id+'</td></tr>';
                    }
                });
                modal.querySelector('tbody').innerHTML = str;
                let backdrop = document.createElement('div');
                modal.querySelectorAll('tr').forEach(function(tr){
                    tr.addEventListener('click', function(){
                        category.dataset.category = this.dataset.id;
                        category.textContent = this.querySelector('.title').textContent;
                        backdrop.click();
                    });
                });
                modal.style.display = '';
                modal.classList.add('in');
                backdrop.className = 'modal-backdrop in';
                backdrop.modal = modal;
                backdrop.addEventListener('click', function(){
                    backdrop.modal.classList.remove('in');
                    backdrop.modal.style.display = 'none';
                    backdrop.remove();
                })
                document.body.append(backdrop);
            });
        });
    });
});
</script>