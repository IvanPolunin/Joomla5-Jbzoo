<?php
namespace JExtstore\Component\JMap\Administrator\View\Wizard;
/**
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage views
 * @subpackage wizard
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;
use Joomla\CMS\Uri\Uri;
use Joomla\Filter\InputFilter;
use Joomla\Filter\OutputFilter;
use JExtstore\Component\JMap\Administrator\Framework\Helpers\Toolbars as ToolbarHelper;
use JExtstore\Component\JMap\Administrator\Framework\View as JMapView;

/**
 * Wizard view class. Only use is to render wizard control panel for supported extensions data source creation 
 * 
 * @package JMAP::WIZARD::administrator::components::com_jmap
 * @subpackage views
 * @subpackage wizard
 * @since 2.0
 */
class HtmlView extends JMapView {
	// Template view variables
	protected $icons;
	protected $customIcon;
	protected $pluginIcon;
	protected $linksIcon;
	
	/**
	 * Render default user defined icon for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function renderCustomDatasourceIcon() {
		$lang = $this->app->getLanguage ();
		$langDirection = $lang->isRTL() ? 'right' : 'left';
		$dataContent = Text::_('COM_JMAP_CREATE_CUSTOM_DATASOURCE_DESC');
		$text = Text::_('COM_JMAP_CREATE_CUSTOM_DATASOURCE');
		$link = OutputFilter::ampReplace('index.php?option=com_jmap&task=sources.editEntity');
		$imageAlt = Text::_('COM_JMAP_CREATE_CUSTOM_DATASOURCE');
		$iconSnippet = <<<HTML
			<div style="float: $langDirection;">
				<div class="icon hasPopover" data-bs-content="$dataContent">
					<a href="$link">
						<img src="components/com_jmap/images/wizard/custom.png" alt="$imageAlt"/>
						<span>$text</span>
					</a>
				</div>
			</div>
HTML;
		return $iconSnippet;
	}

	/**
	 * Render plugin data source type icon for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function renderPluginDatasourceIcon() {
		$lang = $this->app->getLanguage ();
		$langDirection = $lang->isRTL() ? 'right' : 'left';
		$dataContent = Text::_('COM_JMAP_CREATE_PLUGIN_DATASOURCE_DESC');
		$text = Text::_('COM_JMAP_CREATE_PLUGIN_DATASOURCE');
		$link = OutputFilter::ampReplace('index.php?option=com_jmap&task=sources.editEntity&type=plugin');
		$imageAlt = Text::_('COM_JMAP_CREATE_PLUGIN_DATASOURCE');
		$iconSnippet = <<<HTML
			<div style="float: $langDirection;">
				<div class="icon hasPopover" data-bs-content="$dataContent">
					<a href="$link">
						<img src="components/com_jmap/images/wizard/plugin.png" alt="$imageAlt"/>
						<span>$text</span>
					</a>
				</div>
			</div>
HTML;
		return $iconSnippet;
	}

	/**
	 * Render links data source type icon for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return string
	 */
	private function renderLinksDatasourceIcon() {
		$lang = $this->app->getLanguage ();
		$langDirection = $lang->isRTL() ? 'right' : 'left';
		$dataContent = Text::_('COM_JMAP_CREATE_LINKS_DATASOURCE_DESC');
		$text = Text::_('COM_JMAP_CREATE_LINKS_DATASOURCE');
		$link = OutputFilter::ampReplace('index.php?option=com_jmap&task=sources.editEntity&type=links');
		$imageAlt = Text::_('COM_JMAP_CREATE_LINKS_DATASOURCE');
		$iconSnippet = <<<HTML
			<div style="float: $langDirection;">
				<div class="icon hasPopover" data-bs-content="$dataContent">
					<a href="$link">
						<img src="components/com_jmap/images/wizard/links.png" alt="$imageAlt"/>
						<span>$text</span>
					</a>
				</div>
			</div>
HTML;
		return $iconSnippet;
	}

	/**
	 * Render iconset for cpanel
	 *
	 * @param $link string
	 * @param $image string
	 * @access private
	 * @return void
	 */
	private function renderIcon($link, $dataSourceName, $dataSourceUserViewName, $extensionName) {
		$lang = $this->app->getLanguage ();
		?>
		<div style="float:<?php echo ($lang->isRTL()) ? 'right' : 'left'; ?>;">
			<div class="icon hasPopover" data-bs-content="<?php echo sprintf(Text::_('COM_JMAP_CREATE_DATASOURCE'), $dataSourceUserViewName);?>">
				<a data-role="start_create_process" data-extension="<?php echo $extensionName; ?>" href="<?php echo OutputFilter::ampReplace($link); ?>">
					<img src="components/com_jmap/images/wizard/<?php echo $dataSourceName;?>/icon.png" alt="<?php echo $dataSourceUserViewName;?>"/>
					<span><?php echo $dataSourceUserViewName; ?></span>
				</a>
			</div>
		</div>
		<?php
	}
	
	/**
	 * Add the page title and toolbar.
	 *
	 * @since	1.6
	 */
	protected function addDisplayToolbar() {
		ToolbarHelper::title( Text::_('COM_JMAP_JMAPWIZARD' ), 'jmap' );
		ToolbarHelper::custom('sources.display', 'list', 'list', 'COM_JMAP_DATA_SOURCES', false);
		ToolbarHelper::custom('cpanel.display', 'home', 'home', 'COM_JMAP_CPANEL', false);
	}
	
	/**
	 * Default display that renders wizard icons control panel and inject JS APP
	 *        	
	 * @access public
	 * @param string $tpl
	 * @return void
	 */
	public function display($tpl = null) {
		$doc = $this->document;
		$this->loadJQuery($doc);
		$this->loadBootstrap($doc);
		$base = Uri::base();
		$doc->getWebAssetManager()->registerAndUseStyle ( 'jmap.wizard', 'administrator/components/com_jmap/css/wizard.css');
		$doc->getWebAssetManager()->registerAndUseScript ('jmap.wizard', 'administrator/components/com_jmap/js/wizard.js', [], [], ['jquery'] );
		
		$doc->getWebAssetManager()->addInlineScript("var jmap_baseURI='$base';");
		
		// Inject js translations
		$translations = array(	'COM_JMAP_PROGRESSINFOTITLE1', 
								'COM_JMAP_PROGRESSINFOSUBTITLE1',
								'COM_JMAP_PROGRESSINFOSUBTITLE1_2',
								'COM_JMAP_PROGRESSINFOSUBTITLE1_2ERROR',
								'COM_JMAP_PROGRESSINFOTITLE2',
								'COM_JMAP_PROGRESSINFOSUBTITLE2',
								'COM_JMAP_PROGRESSINFOSUBTITLE2_2');
		$this->injectJsTranslations($translations, $doc);
		
		// get Filter Input to mor security safe
		$filterInput = new InputFilter();
		$discoveredExtensions = $this->getModel()->getData(JPATH_COMPONENT . '/images/wizard');
		
		// Buffer delle icons
		ob_start ();
		if(!empty($discoveredExtensions)) {
			foreach ($discoveredExtensions as $discoveredExtension) {
				$dataSourceName = $discoveredExtension['dataSourceName'];
				$extensionName = $discoveredExtension['extensionName']; 
				$dataSourceUserViewName = ucfirst(str_replace('_', ' ', $dataSourceName));
				$this->renderIcon ( 'index.php?option=com_jmap&task=wizard.createEntity&extension=' . $filterInput->clean($dataSourceName, 'CMD'), $dataSourceName, $dataSourceUserViewName, $extensionName);
			}
		}
		$contents = ob_get_clean ();
		 
		// Assign reference variables
		$this->icons = $contents;
		$this->customIcon = $this->renderCustomDatasourceIcon();
		$this->pluginIcon = $this->renderPluginDatasourceIcon();
		$this->linksIcon = $this->renderLinksDatasourceIcon();
		
		// Aggiunta toolbar
		$this->addDisplayToolbar();
		
		// Output del template
		parent::display ();
	}
}