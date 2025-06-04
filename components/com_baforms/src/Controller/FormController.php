<?php
/**
* @package   BaForms
* @author    Balbooa http://www.balbooa.com/
* @copyright Copyright @ Balbooa
* @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
*/

namespace Balbooa\Component\Forms\Site\Controller;

defined('_JEXEC') or die;

use Balbooa\Component\Forms\Site\Helper\BaformsHelper;
use Joomla\CMS\MVC\Controller\FormController as JoomlaFormController;
use Joomla\CMS\Filesystem\File;
use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Date\Date;

class FormController extends JoomlaFormController
{
    public function getModel($name = '', $prefix = '', $config = array('ignore_request' => true))
	{
		return parent::getModel($name, $prefix, array('ignore_request' => false));
	}

    public function readCountries()
    {
        $str = BaformsHelper::readFile(JPATH_ROOT.'/components/com_baforms/libraries/countries/countries.json');
        echo $str;exit;
    }

    public function getPollResults()
    {
        $id = $this->input->get('id', 0, 'int');
        $form_id = $this->input->get('form_id', 0, 'int');
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $model->db = Factory::getDbo();
        $field = $model->getFormField($id, $form_id);
        $str = BaformsHelper::renderPollResults($field);
        echo $str;
        exit;
    }

    public function loadAjaxForm()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $body = '[forms ID='.$id.']';
        $html = BaformsHelper::renderFormHTML($body);
        $design = BaformsHelper::$design;
        if (empty($design) || $design->theme->layout != 'lightbox' || $design->lightbox->trigger->type != '') {
            $html = '';
        }
        echo $html;
        exit;
    }

    public function getPaypalData()
    {
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $data = $model->getServiceData('paypal_sdk');
        $str = json_encode($data);
        echo $str;
        exit();
    }

    public function getStripeData()
    {
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $data = $model->getServiceData('stripe');
        $str = json_encode($data);
        echo $str;
        exit();
    }

    public function getCloudPaymentsData()
    {
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $data = $model->getServiceData('cloudpayments');
        $str = json_encode($data);
        echo $str;
        exit();
    }

    public function checkCoupon()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $coupon = $input->get('coupon', '', 'string');
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $data = $model->checkCoupon($id, $coupon);
        echo $data;
        exit();
    }

    public function getRecaptchaData()
    {
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $data = $model->getRecaptchaData();
        echo $data;
        exit();
    }

    public function getFormsLanguage()
    {
        $language = Factory::getLanguage();
        $language->load('com_baforms', JPATH_ADMINISTRATOR);
        $result = array();
        $path = JPATH_ROOT.'/administrator/components/com_baforms/language/en-GB/en-GB.com_baforms.ini';
        if (File::exists($path)) {
            $contents = BaformsHelper::readFile($path);
            $contents = str_replace('_QQ_', '"\""', $contents);
            $data = parse_ini_string($contents);
            foreach ($data as $ind => $value) {
                $result[$ind] = Text::_($ind);
            }
        }
        $formsLanguage = json_encode($result);
        echo $formsLanguage;
        exit;
    }

    public function setAppLicense()
    {
        BaformsHelper::setAppLicense('');
        header('Content-Type: text/javascript');
        echo 'var domainResponse = true;';
        exit();
    }

    public function setAppLicenseForm()
    {
        BaformsHelper::setAppLicense('');
        header('Location: https://www.balbooa.com/user/downloads/licenses');
        exit();
    }

    public function renderFormsCalendar()
    {
        $input = Factory::getApplication()->input;
        $year = $input->get('year', '0', 'string');
        $month = $input->get('month', '0', 'string');
        $start = $input->get('start', 0, 'int');
        if (strlen($month) == 1) {
            $month = '0'.$month;
        }
        $date = Date::getInstance($year.'-'.$month.'-01');
        $obj = $this->renderFormsCalendarData($date, $month, $year, $start);
        $str = json_encode($obj);
        header('Content-Type: text/javascript');
        echo $str;
        exit;
    }

    public function renderFormsCalendarData($dateObject, $month, $year, $start = 0)
    {
        $end = $start + 6;
        $obj = new \stdClass();
        $dateData = new \stdClass();
        $dateData->days = [Text::_('SUN'), Text::_('MON'), Text::_('TUE'),
            Text::_('WED'), Text::_('THU'), Text::_('FRI'), Text::_('SAT'),
            Text::_('SUN')
        ];
        $today = date('j');
        $now = Date::getInstance();
        $nowDate = new \stdClass();
        $nowDate->date = $now->format('n Y');
        $nowDate->year = $now->format('Y');
        $nowDate->month = $now->format('n');
        $m = strlen($nowDate->month) == 1 ? '0'.$nowDate->month : $nowDate->month;
        $nowDate->time = Date::getInstance($nowDate->year.'-'.$m.'-01')->getTimestamp();
        $todayDate = $dateObject->format('n Y');
        $obj->title = $dateObject->format('F Y');
        $obj->header = '';
        for ($i = $start; $i <= $end; $i++) { 
            $obj->header .= '<div class="ba-event-calendar-day-name">'.$dateData->days[$i].'</div>';
        }
        $obj->body = '';
        $firstDay = $dateObject->format('w');
        if ($firstDay == 0 && $start == 1) {
            $firstDay = 7;
        }
        $daysInMonth = $dateObject->format('t');
        $time = $dateObject->getTimestamp();
        $date = 1;
        for ($i = 0; $i < 6; $i++) {
            if ($date > $daysInMonth) {
                break;
            }
            $obj->body .= '<div class="ba-forms-calendar-row">';
            for ($j = $start; $j <= $end; $j++) {
                if (($i === 0 && $j < $firstDay) || $date > $daysInMonth) {
                    $obj->body .= '<div class="ba-empty-date-cell"></div>';
                } else {
                    $d = $date < 10 ? '0'.(string)$date : (string)$date;
                    $event = Date::getInstance($year.'-'.$month.'-'.$d);
                    $eventDate = $event->format('j F Y');
                    $dayDate = $event->format('Y-m-d');
                    $obj->body .= '<div class="ba-date-cell';
                    $obj->body .= ($date == $today && $nowDate->date == $todayDate ? ' ba-curent-date' : '');
                    $previous = $nowDate->time > $time || ($nowDate->date == $todayDate && $date < $today);
                    $obj->body .= $previous ? ' ba-previous-date' : '';
                    $obj->body .= '" data-date="'.$eventDate.'" data-day-number="'.$j;
                    $obj->body .= '" data-day-date="'.$dayDate.'">'.$date.'</div>';
                    $date++;
                }
            }
            $obj->body .= '</div>';
        }

        return $obj;
    }

    public function uploadAttachmentFile()
    {
        $input = Factory::getApplication()->input;
        $file = $input->files->get('file', array(), 'array');
        $id = $input->post->get('id', 0, 'int');
        $field_id = $input->post->get('field_id', 0, 'int');
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $obj = $model->uploadAttachmentFile($file, $id, $field_id);
        $str = json_encode($obj);
        echo $str;
        exit();
    }

    public function removeTmpAttachment()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel();
        $model->removeTmpAttachment($id);
        exit();
    }

    public function sendMessage()
    {
        exit();
    }

    public function message()
    {
        $input = Factory::getApplication()->input;
        $id = $input->post->get('form-id', 0, 'int');
        $btn = $input->post->get('submit-btn', 0, 'int');
        $honeypot = $input->post->get('ba-honeypot', '', 'string');
        if (!empty($id) && !empty($btn) && empty($honeypot)) {
            $post = $input->post->getArray([]);
            /** @var Balbooa\Component\Forms\Site\Model */
            $model = $this->getModel();
            $model->sendMessage($post, $btn, $id);
        }
        exit();
    }

    public function setAppLicenseActivation()
    {
        BaformsHelper::setAppLicenseActivation('');
        header('Content-Type: text/javascript');
        echo 'var domainResponse = true;';
        exit();
    }

    public function stripeCharges()
    {
        $input = Factory::getApplication()->input;
        $id = $input->get('id', 0, 'int');
        $name = $input->get('name', 0, 'int');
        $str = $input->get('object', '', 'string');
        $object = json_decode($str);
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel('form');
        $model->stripeCharges($id, $name, $object);
    }

    public function payAuthorize()
    {
        $input = Factory::getApplication()->input;
        $total = $input->get('total', 0, 'double');
        $id = $input->get('id', 0, 'int');
        $cardNumber = $input->get('cardNumber', '', 'string');
        $expirationDate = $input->get('expirationDate', '', 'string');
        $cardCode = $input->get('cardCode', '', 'string');
        $cardNumber = str_replace(' ', '', $cardNumber);
        $expArray = explode('/', $expirationDate);
        $expirationDate = '20'.$expArray[1].'-'.$expArray[0];
        /** @var Balbooa\Component\Forms\Site\Model */
        $model = $this->getModel('form');
        $model->payAuthorize($id, $total, $cardNumber, $expirationDate, $cardCode);
    }

    public function save($key = null, $urlVar = null)
    {
               
    }
}