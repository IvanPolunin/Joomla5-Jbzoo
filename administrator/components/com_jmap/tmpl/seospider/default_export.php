<?php 
/** 
 * @package JMAP::SEOSPIDER::administrator::components::com_jmap
 * @subpackage views
 * @subpackage seospider
 * @subpackage tmpl
 * @author Joomla! Extensions Store
 * @copyright (C) 2021 - Joomla! Extensions Store
 * @license GNU/GPLv2 http://www.gnu.org/licenses/gpl-2.0.html  
 */
defined ( '_JEXEC' ) or die ( 'Restricted access' );
use Joomla\CMS\Language\Text;

$delimiter = ';';
$enclosure = '"';

// Clean dirty buffer
ob_end_clean();
// Open buffer
ob_start();
// Open out stream
$outstream = fopen("php://output", "w");
// Funzione di scrittura nell'output stream
function __jmapoutputCSV(&$vals, $index, $userData) {
	// Get model data for this row
	$db = $userData[3];
	
	try {
		$query = "SELECT *" .
				 "\n FROM " . $db->quoteName('#__jmap_headings') .
				 "\n WHERE " . $db->quoteName('linkurl') . " = " . $db->quote($vals->loc);
		$headings = $db->setQuery($query)->loadObject();
		
		$query = "SELECT " . $db->quoteName('canonical') .
				 "\n FROM " . $db->quoteName('#__jmap_canonicals') .
				 "\n WHERE " . $db->quoteName('linkurl') . " = " . $db->quote($vals->loc);
		$canonical = $db->setQuery($query)->loadResult();
		
		// If no headings are assigned to this link skip the export row
		if(is_null($headings) && is_null($canonical)) {
			return;
		}
		
		// Convert object to array
		$headingsArray = (array)$headings;
		
		// Ensure that the csv array is fully populated if no headings are found but a canonical is found
		if(is_null($headings) && !is_null($canonical)) {
			$headingsArray['linkurl'] = $vals->loc;
			$headingsArray['h1'] = null;
			$headingsArray['h2'] = null;
			$headingsArray['h3'] = null;
		}
		
		if($canonical) {
			$headingsArray['canonical'] = $canonical;
		} else {
			$headingsArray['canonical'] = null;
		}
		
		unset($headingsArray['id']);
		fputcsv($userData[0], $headingsArray, $userData[1], $userData[2]);
	} catch (\Exception $e) {
		// Continue
	}
}

// Echo delle intestazioni
fputcsv ( $outstream, array (
		Text::_ ( 'COM_JMAP_SEOSPIDER_CRAWLED_LINK' ),
		Text::_ ( 'COM_JMAP_SEOSPIDER_H1' ),
		Text::_ ( 'COM_JMAP_SEOSPIDER_H2' ),
		Text::_ ( 'COM_JMAP_SEOSPIDER_H3' ),
		Text::_ ( 'COM_JMAP_SEOSPIDER_CANONICAL' )
), $delimiter, $enclosure );

// Output di tutti i records
$db = $this->getModel()->getDbo();
array_walk($this->items, "__jmapoutputCSV", array($outstream, $delimiter, $enclosure, $db));
fclose($outstream);

// Recupero output buffer content
$contents = ob_get_clean();
$size = strlen($contents);

header ( 'Pragma: public' );
header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
header ( 'Expires: ' . gmdate ( 'D, d M Y H:i:s' ) . ' GMT' );
header ( 'Content-Disposition: attachment; filename="seospider_pg' . $this->pagination->pagesCurrent . '.csv"' );
header ( 'Content-Type: text/plain' );
header ( "Content-Length: " . $size );
echo $contents;
	
exit ();