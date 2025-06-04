<?php
/**
 * @version    CVS: 1.0.0
 * @package    Com_Selectcity
 * @author     Ivan Polynin <ipolynin@gmail.com>
 * @copyright  2024 Ivan Polynin
 * @license    GNU General Public License версии 2 или более поздней; Смотрите LICENSE.txt
 */

namespace Selectcity\Component\Selectcity\Administrator\Controller;

\defined('_JEXEC') or die;

use Joomla\CMS\Application\SiteApplication;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Multilanguage;
use Joomla\CMS\Language\Text;
use Joomla\CMS\MVC\Controller\AdminController;
use Joomla\CMS\Router\Route;
use Joomla\CMS\Uri\Uri;
use Joomla\Utilities\ArrayHelper;

/**
 * Selectcitys list controller class.
 *
 * @since  1.0.0
 */
class SelectcitysController extends AdminController
{
	/**
	 * Method to clone existing Selectcitys
	 *
	 * @return  void
	 *
	 * @throws  Exception
	 */
	public function duplicate()
	{
		// Check for request forgeries
		$this->checkToken();

		// Get id(s)
		$pks = $this->input->post->get('cid', array(), 'array');

		try
		{
			if (empty($pks))
			{
				throw new \Exception(Text::_('COM_SELECTCITY_NO_ELEMENT_SELECTED'));
			}

			ArrayHelper::toInteger($pks);
			$model = $this->getModel();
			$model->duplicate($pks);
			$this->setMessage(Text::_('COM_SELECTCITY_ITEMS_SUCCESS_DUPLICATED'));
		}
		catch (\Exception $e)
		{
			Factory::getApplication()->enqueueMessage($e->getMessage(), 'warning');
		}

		$this->setRedirect('index.php?option=com_selectcity&view=selectcitys');
	}

	/**
	 * Proxy for getModel.
	 *
	 * @param   string  $name    Optional. Model name
	 * @param   string  $prefix  Optional. Class prefix
	 * @param   array   $config  Optional. Configuration array for model
	 *
	 * @return  object	The Model
	 *
	 * @since   1.0.0
	 */
	public function getModel($name = 'Selectcity', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}
	
	public function getModels($name = 'Selectcitys', $prefix = 'Administrator', $config = array())
	{
		return parent::getModel($name, $prefix, array('ignore_request' => true));
	}

	

	/**
	 * Method to save the submitted ordering values for records via AJAX.
	 *
	 * @return  void
	 *
	 * @since   1.0.0
	 *
	 * @throws  Exception
	 */
	public function saveOrderAjax()
	{
		// Get the input
		$pks   = $this->input->post->get('cid', array(), 'array');
		$order = $this->input->post->get('order', array(), 'array');

		// Sanitize the input
		ArrayHelper::toInteger($pks);
		ArrayHelper::toInteger($order);

		// Get the model
		$model = $this->getModel();

		// Save the ordering
		$return = $model->saveorder($pks, $order);

		if ($return)
		{
			echo "1";
		}

		// Close the application
		Factory::getApplication()->close();
	}
	
	public function exportCSV()
    {
        
        $model = $this->getModels();
        $model->exportCSV();
        
    }
    
    public function uploadcsv()
    {
        $model = $this->getModels();

        // Проверка, что файл загружен
        if (!empty($_FILES['csvFile']['name'])) {
            $csvFile = JPATH_COMPONENT . '/uploads/' . $_FILES['csvFile']['name'];

            // Перемещение файла в директорию компонента
            move_uploaded_file($_FILES['csvFile']['tmp_name'], $csvFile);

            // Импорт данных из CSV файла
            $model->importCSV($csvFile);

            // Удаление загруженного файла (если нужно)
            unlink($csvFile);
            
            // Добавьте код для перенаправления после завершения загрузки
            $this->setRedirect('index.php?option=com_selectcity&view=selectcitys');
        } else {
            // Если файл не был загружен, обработайте это по вашему усмотрению
        }
    }
	
}
