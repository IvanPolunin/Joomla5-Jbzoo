<?php
/**
 * @author          Tassos Marinos <info@tassos.gr>
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

defined('_JEXEC') or die('Restricted access');

use Joomla\CMS\Layout\FileLayout;
use Joomla\Registry\Registry;
use Joomla\CMS\Language\Text;

require_once dirname(__DIR__) . '/helpers/field.php';

class JFormFieldNRInlineFileUpload extends NRFormField
{
	/**
	 * Method to get the field input markup.
	 *
	 * @return  string  The field input markup.
	 */
	protected function getInput()
	{
		$layout = new FileLayout('inlinefileupload', JPATH_PLUGINS . '/system/nrframework/layouts');

		$data = [
			'value' => $this->value,
			'name' => $this->name,
			'accept' => $this->get('accept'),
			'upload_folder' => base64_encode($this->get('upload_folder'))
		];

		return $layout->render($data);
	}

    /**
     * Handles the AJAX request
     *
     * @param   array    $options
     *
     * @return  array
     */
    public function onAjax($options)
    {
		$options = new Registry($options);

		if ($options->get('action') == 'remove')
		{
			$this->onRemove($options);
			return;
		}

		$this->onUpload();
	}

	/**
	 * On file upload.
	 * 
	 * @return  string
	 */
	private function onUpload()
	{
		// Make sure we have a valid file passed
        if (!$file = $this->app->input->files->get('file', null, 'cmd'))
        {
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_UPLOAD_ERROR_INVALID_FILE')
			]);
			jexit();
		}

		// ensure an upload folder was given
		if (!$upload_folder = $this->app->input->get('upload_folder', null, 'cmd'))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_UPLOAD_FOLDER_MISSING')
			]);
			jexit();
		}

		// ensure we can decode its value
		if (!$upload_folder = base64_decode($upload_folder))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_UPLOAD_FOLDER_INVALID')
			]);
			jexit();
		}

		$uploaded_file = null;
		$file_size = 0;
		$file_name = null;

		// try to upload the file.
		try {
			$uploaded_file = \NRFramework\File::upload($file, JPATH_ROOT . DIRECTORY_SEPARATOR . $upload_folder, ['text/plain', 'text/csv'], false, true);
			$filePathInfo = NRFramework\File::pathinfo($uploaded_file);
			$file_name = $filePathInfo['basename'];
            $file_size = file_exists($uploaded_file) ? filesize($uploaded_file) : 0;
			$file_size = $file_size ? number_format($file_size / 1024, 2) . ' KB' : $file_size;
		} catch (\Throwable $th)
		{
            echo json_encode([
				'error' => true,
				'response' => $th->getMessage()
			]);
			jexit();
		}
		
		echo json_encode([
			'error' => false,
			'response' => Text::_('NR_FILE_UPLOADED_SUCCESSFULLY'),
			'file_name' => base64_encode($file_name),
			'file' => base64_encode($uploaded_file),
			'file_size' => $file_size
		]);
		jexit();
	}

	/**
	 * On file removal.
	 * 
	 * @return  string
	 */
	private function onRemove($options)
	{
		if (!$file = $options->get('remove_file'))
		{
            echo json_encode([
				'error' => true,
				'response' => Text::_('NR_UPLOAD_ERROR_INVALID_FILE')
			]);
			jexit();
		}

		// If file exists, remove it
		if (file_exists($file))
		{
			unlink($file);
		}

		echo json_encode([
			'error' => false,
			'response' => Text::_('NR_FILE_DELETED')
		]);
		jexit();
	}
}