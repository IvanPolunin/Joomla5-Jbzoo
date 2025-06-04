<?php

/**
 * @author          Tassos.gr
 * @link            https://www.tassos.gr
 * @copyright       Copyright © 2024 Tassos All Rights Reserved
 * @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
*/

defined( '_JEXEC' ) or die( 'Restricted access' );

use Joomla\CMS\Form\FormHelper;

// Registers framework's namespace
JLoader::registerNamespace('NRFramework', __DIR__ . '/NRFramework/', false, false, 'psr4');

// Assignment related class aliases
JLoader::registerAlias('NRFrameworkFunctions',               '\\NRFramework\\Functions');
JLoader::registerAlias('NRAssignment',                       '\\NRFramework\\Conditions\Condition');
JLoader::registerAlias('nrFrameworkAssignmentsHelper',       '\\NRFramework\\Assignments');
JLoader::registerAlias('nrFrameworkAssignmentsAcyMailing',   '\\NRFramework\\Conditions\\AcyMailing');
JLoader::registerAlias('nrFrameworkAssignmentsAkeebaSubs',   '\\NRFramework\\Conditions\\AkeebaSubs');
JLoader::registerAlias('nrFrameworkAssignmentsContent',      '\\NRFramework\\Conditions\\Content');
JLoader::registerAlias('nrFrameworkAssignmentsConvertForms', '\\NRFramework\\Conditions\\ConvertForms');
JLoader::registerAlias('nrFrameworkAssignmentsDateTime',     '\\NRFramework\\Conditions\\DateTime');
JLoader::registerAlias('nrFrameworkAssignmentsDevices',      '\\NRFramework\\Conditions\\Devices');
JLoader::registerAlias('nrFrameworkAssignmentsGeoIP',        '\\NRFramework\\Conditions\\GeoIP');
JLoader::registerAlias('nrFrameworkAssignmentsLanguages',    '\\NRFramework\\Conditions\\Languages');
JLoader::registerAlias('nrFrameworkAssignmentsMenu',         '\\NRFramework\\Conditions\\Menu');
JLoader::registerAlias('nrFrameworkAssignmentsPHP',          '\\NRFramework\\Conditions\\PHP');
JLoader::registerAlias('nrFrameworkAssignmentsURLs',         '\\NRFramework\\Conditions\\URLs');
JLoader::registerAlias('nrFrameworkAssignmentsUsers',        '\\NRFramework\\Conditions\\Users');
JLoader::registerAlias('nrFrameworkAssignmentsOS',           '\\NRFramework\\Conditions\\OS');
JLoader::registerAlias('nrFrameworkAssignmentsBrowsers',     '\\NRFramework\\Conditions\\Browsers');
JLoader::registerAlias('NRCache', 							 '\\NRFramework\\Cache');
JLoader::registerAlias('NRHTML', 							 '\\NRFramework\\HTML');
JLoader::registerAlias('NRUpdateSites', 					 '\\NRFramework\\Updatesites');
JLoader::registerAlias('NRSmartTags', 					     '\\NRFramework\\SmartTags\\SmartTags');
JLoader::registerAlias('NRFramework\\SmartTags',			 '\\NRFramework\\SmartTags\\SmartTags');
JLoader::registerAlias('NREmail', 					         '\\NRFramework\\Email');
JLoader::registerAlias('NRVisitor', 					     '\\NRFramework\\VisitorToken');
JLoader::registerAlias('NRFonts', 					         '\\NRFramework\\Fonts');
JLoader::registerAlias('NR_activecampaign', 				 '\\NRFramework\\Integrations\\ActiveCampaign');
JLoader::registerAlias('NR_campaignmonitor', 				 '\\NRFramework\\Integrations\\CampaignMonitor');
JLoader::registerAlias('NR_convertkit', 				 	 '\\NRFramework\\Integrations\\ConvertKit');
JLoader::registerAlias('NR_drip', 				 			 '\\NRFramework\\Integrations\\Drip');
JLoader::registerAlias('NR_elasticemail', 					 '\\NRFramework\\Integrations\\ElasticEmail');
JLoader::registerAlias('NR_getresponse', 					 '\\NRFramework\\Integrations\\GetResponse');
JLoader::registerAlias('NR_hubspot', 						 '\\NRFramework\\Integrations\\HubSpot');
JLoader::registerAlias('NR_icontact', 						 '\\NRFramework\\Integrations\\IContact');
JLoader::registerAlias('NR_mailchimp', 						 '\\NRFramework\\Integrations\\MailChimp');
JLoader::registerAlias('NR_recaptcha', 						 '\\NRFramework\\Integrations\\ReCaptcha');
JLoader::registerAlias('NR_salesforce', 					 '\\NRFramework\\Integrations\\Salesforce');
JLoader::registerAlias('NR_sendinblue', 					 '\\NRFramework\\Integrations\\SendInBlue');
JLoader::registerAlias('NR_zoho', 							 '\\NRFramework\\Integrations\\Zoho');
JLoader::registerAlias('NR_zohocrm', 						 '\\NRFramework\\Integrations\\ZohoCRM');

// Define a helper constant to indicate whether we are on a Joomla 4 installation
if (version_compare(JVERSION, '4.0', 'ge') && !defined('nrJ4'))
{
	define('nrJ4', true);
}

// The Tassos.gr Site URL
if (!defined('TF_TEMPLATES_SITE_URL'))
{
	define('TF_TEMPLATES_SITE_URL', 'https://templates.tassos.gr/');
}

// URL to retrieve templates
if (!defined('TF_TEMPLATES_GET_URL'))
{
	define('TF_TEMPLATES_GET_URL', TF_TEMPLATES_SITE_URL . '{{PROJECT}}/list.doc');
}

// URL to retrieve a template
if (!defined('TF_TEMPLATE_GET_URL'))
{
	define('TF_TEMPLATE_GET_URL', TF_TEMPLATES_SITE_URL . 'tower/template/{{PROJECT}}/{{TEMPLATE}}/{{DOWNLOAD_KEY}}');
}

// URL to check the license
if (!defined('TF_CHECK_LICENSE'))
{
	define('TF_CHECK_LICENSE', 'https://www.tassos.gr/tower/license/{{DOWNLOAD_KEY}}.doc');
}

/**
 * Joomla 3 backward compatibility aliases.
 * 
 * TODO: Remove this file when Joomla 3 support is dropped.
 */
if (version_compare(JVERSION, 4, '<'))
{
	// Fields Aliases
	$tf_aliases = [
		'Text',
		'Textarea',
		'GroupedList',
		'Media',
		'List',
		'Hidden',
		'Number',
		'Checkbox',
		'Password',
		'Subform'
	];
	foreach ($tf_aliases as $name)
	{
		if (class_exists('\\Joomla\\CMS\\Form\\Field\\' . $name . 'Field', true))
		{
			continue;
		}
	
		FormHelper::loadFieldClass(strtolower($name));
		class_alias('JFormField' . $name, '\\Joomla\\CMS\\Form\\Field\\' . $name . 'Field');
	}

	// Extra Aliases
	$extra_aliases = [
		'JHtmlSidebar' => '\\Joomla\\CMS\\HTML\\Helpers\\Sidebar'
	];
	foreach ($extra_aliases as $alias => $class)
	{
		if (class_exists($class, true))
		{
			continue;
		}

		class_alias($alias, $class);
	}

	JLoader::import('components.com_fields.libraries.fieldslistplugin', JPATH_ADMINISTRATOR);
	JLoader::import('components.com_fields.libraries.fieldsplugin', JPATH_ADMINISTRATOR);
	FormHelper::loadFieldClass('Checkboxes');
}
else
{
	// Once Joomla 3 support is dropped, find where the following classes are used and load them using "use" statements.
	JLoader::registerAlias('FieldsPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsPlugin');
	JLoader::registerAlias('FieldsListPlugin', '\\Joomla\\Component\\Fields\\Administrator\\Plugin\\FieldsListPlugin');
	JLoader::registerAlias('JFormFieldCheckboxes', '\\Joomla\\CMS\\Form\\Field\\CheckboxesField');
}