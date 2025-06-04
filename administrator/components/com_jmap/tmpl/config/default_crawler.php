<?php 
/** 
 * @package JMAP::CONFIG::administrator::components::com_jmap
 * @subpackage views
 * @subpackage config
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

// Evaluate nonce csp feature
$appNonce = $this->app->get('csp_nonce', null);
$nonce = $appNonce ? ' nonce="' . $appNonce . '"' : '';
?>
<style type="text/css"<?php echo $nonce;?>>
#crawler_results {
	background: #428bca;
	color: #FFF;
	margin-bottom: 20px;
    padding: 0 0 2px 0;
    box-shadow: 1px 8px 15px 1px #CCC;
}

#crawler_main_title {
	text-align: center;
	font-size: 18px;	
    background-color: #FFF;
    color: #428bca;
    border: 1px solid #428bca;
    padding: 5px;
}

span.badge.bg-white,
span.badge.badge-white {
	background-color: #FFF;
    color: #428bca;
    margin-left: 10px;
}

#crawler_results p {
	padding: 2px 10px;
	margin: 2px;
	text-align: left;
}

#crawler_results p span {
	font-size: 12px;
}

</style>
<?php 
if($this->testResults):?>
<div id="crawler_results">
<div id="crawler_main_title"><?php echo Text::_('COM_JMAP_CRAWLER_INFO')?></div>
<p><?php echo Text::sprintf('COM_JMAP_CRAWLER_TEST_HTTPCODE', $this->testResults->code);?></p>
<p><?php echo Text::sprintf('COM_JMAP_CRAWLER_TEST_CONTENT_TYPE', $this->testResults->headers['Content-Type']);?></p>
</div>
<?php 
echo $this->testResults->body;


else:?>



<?php 
endif;