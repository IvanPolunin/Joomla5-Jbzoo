<?php
/**
 * @package   com_zoo
 * @author    YOOtheme https://yootheme.com
 * @copyright Copyright (C) YOOtheme GmbH
 * @license   https://www.gnu.org/licenses/gpl.html GNU/GPL
 */

// no direct access
use Joomla\CMS\Language\Text;
use Joomla\CMS\Router\Route;

defined('_JEXEC') or die('Restricted access');

// include assets css
$this->app->document->addStylesheet('elements:download/assets/css/download.css');

switch ($display) {
	case 'download_limit':
		$download_limit = ($download_limit) ? $download_limit : '-';
		echo $download_limit;
		break;

	case 'filesize':
		echo $size;
		break;

	case 'filehits':
		echo $hits;
		break;

	case 'buttonlink':
		if ($limit_reached) {
			echo '<a class="yoo-zoo element-download-button" href="javascript:alert(\''.Text::_('Download limit reached').'\');" title="'.Text::_('Download limit reached').'"><span><span>'.Text::_('Download').'</span></span></a>';
		} else {
			echo '<a class="yoo-zoo element-download-button" href="'.Route::_($download_link).'" title="'.$download_name.'"><span><span>'.Text::_('Download').'</span></span></a>';
		}
		break;

	case 'imagelink':
		if ($limit_reached) {
			echo '<div class="yoo-zoo element-download-type element-download-type-'.$filetype.'" title="'.Text::_('Download limit reached').'"></div>';
		} else {
			echo '<a class="yoo-zoo element-download-type element-download-type-'.$filetype.'" href="'.Route::_($download_link).'" title="'.$download_name.'"></a>';
		}
		break;

	default:
		if ($limit_reached) {
			echo Text::_('Download limit reached');
		} else {
			echo '<a href="'.Route::_($download_link).'" title="'.$download_name.'">'.$download_name.'</a>';
		}
}
