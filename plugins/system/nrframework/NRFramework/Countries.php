<?php
/**
 *  @author          Tassos.gr <info@tassos.gr>
 *  @link            https://www.tassos.gr
 *  @copyright       Copyright © 2024 Tassos All Rights Reserved
 *  @license         GNU GPLv3 <http://www.gnu.org/licenses/gpl.html> or later
 */

namespace NRFramework;

use Joomla\CMS\Language\LanguageHelper;
use Joomla\CMS\Language\Text;
use NRFramework\Cache;

defined('_JEXEC') or die('Restricted access');

/**
 *  Helper class to work with country names/codes
 */
class Countries
{
    /**
	 *  Countries List
	 * 
	 *  @deprecated: Use getCountriesData();
	 *
	 *  @const  array
	 */
    public static  $map = [
		'AF' => "Afghanistan",
		'AX' => "Aland Islands",
		'AL' => "Albania",
		'DZ' => "Algeria",
		'AS' => "American Samoa",
		'AD' => "Andorra",
		'AO' => "Angola",
		'AI' => "Anguilla",
		'AQ' => "Antarctica",
		'AG' => "Antigua and Barbuda",
		'AR' => "Argentina",
		'AM' => "Armenia",
		'AW' => "Aruba",
		'AU' => "Australia",
		'AT' => "Austria",
		'AZ' => "Azerbaijan",
		'BS' => "Bahamas",
		'BH' => "Bahrain",
		'BD' => "Bangladesh",
		'BB' => "Barbados",
		'BY' => "Belarus",
		'BE' => "Belgium",
		'BZ' => "Belize",
		'BJ' => "Benin",
		'BM' => "Bermuda",
		'BQ-BO' => "Bonaire",
		'BQ-SA' => "Saba",
		'BQ-SE' => "Sint Eustatius",
		'BT' => "Bhutan",
		'BO' => "Bolivia",
		'BA' => "Bosnia and Herzegovina",
		'BW' => "Botswana",
		'BV' => "Bouvet Island",
		'BR' => "Brazil",
		'IO' => "British Indian Ocean Territory",
		'BN' => "Brunei Darussalam",
		'BG' => "Bulgaria",
		'BF' => "Burkina Faso",
		'BI' => "Burundi",
		'KH' => "Cambodia",
		'CM' => "Cameroon",
		'CA' => "Canada",
		'CV' => "Cape Verde",
		'KY' => "Cayman Islands",
		'CF' => "Central African Republic",
		'TD' => "Chad",
		'CL' => "Chile",
		'CN' => "China",
		'CX' => "Christmas Island",
		'CC' => "Cocos (Keeling) Islands",
		'CO' => "Colombia",
		'KM' => "Comoros",
		'CG' => "Congo",
		'CD' => "Congo, The Democratic Republic of the",
		'CK' => "Cook Islands",
		'CR' => "Costa Rica",
		'CI' => "Cote d'Ivoire",
		'HR' => "Croatia",
		'CU' => "Cuba",
		'CW' => "Curaçao",
		'CY' => "Cyprus",
		'CZ' => "Czech Republic",
		'DK' => "Denmark",
		'DJ' => "Djibouti",
		'DM' => "Dominica",
		'DO' => "Dominican Republic",
		'EC' => "Ecuador",
		'EG' => "Egypt",
		'SV' => "El Salvador",
		'GQ' => "Equatorial Guinea",
		'ER' => "Eritrea",
		'EE' => "Estonia",
		'ET' => "Ethiopia",
		'FK' => "Falkland Islands (Malvinas)",
		'FO' => "Faroe Islands",
		'FJ' => "Fiji",
		'FI' => "Finland",
		'FR' => "France",
		'GF' => "French Guiana",
		'PF' => "French Polynesia",
		'TF' => "French Southern Territories",
		'GA' => "Gabon",
		'GM' => "Gambia",
		'GE' => "Georgia",
		'DE' => "Germany",
		'GH' => "Ghana",
		'GI' => "Gibraltar",
		'GR' => "Greece",
		'GL' => "Greenland",
		'GD' => "Grenada",
		'GP' => "Guadeloupe",
		'GU' => "Guam",
		'GT' => "Guatemala",
		'GG' => "Guernsey",
		'GN' => "Guinea",
		'GW' => "Guinea-Bissau",
		'GY' => "Guyana",
		'HT' => "Haiti",
		'HM' => "Heard Island and McDonald Islands",
		'VA' => "Holy See (Vatican City State)",
		'HN' => "Honduras",
		'HK' => "Hong Kong",
		'HU' => "Hungary",
		'IS' => "Iceland",
		'IN' => "India",
		'ID' => "Indonesia",
		'IR' => "Iran, Islamic Republic of",
		'IQ' => "Iraq",
		'IE' => "Ireland",
		'IM' => "Isle of Man",
		'IL' => "Israel",
		'IT' => "Italy",
		'JM' => "Jamaica",
		'JP' => "Japan",
		'JE' => "Jersey",
		'JO' => "Jordan",
		'KZ' => "Kazakhstan",
		'KE' => "Kenya",
		'KI' => "Kiribati",
		'KP' => "Korea, Democratic People's Republic of",
		'KR' => "Korea, Republic of",
		'KW' => "Kuwait",
		'KG' => "Kyrgyzstan",
		'LA' => "Lao People's Democratic Republic",
		'LV' => "Latvia",
		'LB' => "Lebanon",
		'LS' => "Lesotho",
		'LR' => "Liberia",
		'LY' => "Libyan Arab Jamahiriya",
		'LI' => "Liechtenstein",
		'LT' => "Lithuania",
		'LU' => "Luxembourg",
		'MO' => "Macao",
		'MK' => "Macedonia",
		'MG' => "Madagascar",
		'MW' => "Malawi",
		'MY' => "Malaysia",
		'MV' => "Maldives",
		'ML' => "Mali",
		'MT' => "Malta",
		'MH' => "Marshall Islands",
		'MQ' => "Martinique",
		'MR' => "Mauritania",
		'MU' => "Mauritius",
		'YT' => "Mayotte",
		'MX' => "Mexico",
		'FM' => "Micronesia, Federated States of",
		'MD' => "Moldova, Republic of",
		'MC' => "Monaco",
		'MN' => "Mongolia",
		'ME' => "Montenegro",
		'MS' => "Montserrat",
		'MA' => "Morocco",
		'MZ' => "Mozambique",
		'MM' => "Myanmar",
		'NA' => "Namibia",
		'NR' => "Nauru",
		'NP' => "Nepal",
		'NL' => "Netherlands",
		'AN' => "Netherlands Antilles",
		'NC' => "New Caledonia",
		'NZ' => "New Zealand",
		'NI' => "Nicaragua",
		'NE' => "Niger",
		'NG' => "Nigeria",
		'NU' => "Niue",
		'NF' => "Norfolk Island",
		'NM' => "North Macedonia",
		'MP' => "Northern Mariana Islands",
		'NO' => "Norway",
		'OM' => "Oman",
		'PK' => "Pakistan",
		'PW' => "Palau",
		'PS' => "Palestinian Territory",
		'PA' => "Panama",
		'PG' => "Papua New Guinea",
		'PY' => "Paraguay",
		'PE' => "Peru",
		'PH' => "Philippines",
		'PN' => "Pitcairn",
		'PL' => "Poland",
		'PT' => "Portugal",
		'PR' => "Puerto Rico",
		'QA' => "Qatar",
		'RE' => "Reunion",
		'RO' => "Romania",
		'RU' => "Russian Federation",
		'RW' => "Rwanda",
		'SH' => "Saint Helena",
		'KN' => "Saint Kitts and Nevis",
		'LC' => "Saint Lucia",
		'PM' => "Saint Pierre and Miquelon",
		'VC' => "Saint Vincent and the Grenadines",
		'WS' => "Samoa",
		'SM' => "San Marino",
		'ST' => "Sao Tome and Principe",
		'SA' => "Saudi Arabia",
		'SN' => "Senegal",
		'RS' => "Serbia",
		'SC' => "Seychelles",
		'SL' => "Sierra Leone",
		'SG' => "Singapore",
		'SK' => "Slovakia",
		'SI' => "Slovenia",
		'SB' => "Solomon Islands",
		'SO' => "Somalia",
		'ZA' => "South Africa",
		'GS' => "South Georgia and the South Sandwich Islands",
		'ES' => "Spain",
		'LK' => "Sri Lanka",
		'SD' => "Sudan",
		'SS' => "South Sudan",
		'SR' => "Suriname",
		'SJ' => "Svalbard and Jan Mayen",
		'SZ' => "Swaziland",
		'SE' => "Sweden",
		'CH' => "Switzerland",
		'SY' => "Syrian Arab Republic",
		'TW' => "Taiwan",
		'TJ' => "Tajikistan",
		'TZ' => "Tanzania, United Republic of",
		'TH' => "Thailand",
		'TL' => "Timor-Leste",
		'TG' => "Togo",
		'TK' => "Tokelau",
		'TO' => "Tonga",
		'TT' => "Trinidad and Tobago",
		'TN' => "Tunisia",
		'TR' => "Turkey",
		'TM' => "Turkmenistan",
		'TC' => "Turks and Caicos Islands",
		'TV' => "Tuvalu",
		'UG' => "Uganda",
		'UA' => "Ukraine",
		'AE' => "United Arab Emirates",
		'GB' => "United Kingdom",
		'US' => "United States",
		'UM' => "United States Minor Outlying Islands",
		'UY' => "Uruguay",
		'UZ' => "Uzbekistan",
		'VU' => "Vanuatu",
		'VE' => "Venezuela",
		'VN' => "Vietnam",
		'VG' => "Virgin Islands, British",
		'VI' => "Virgin Islands, U.S.",
		'WF' => "Wallis and Futuna",
		'EH' => "Western Sahara",
		'YE' => "Yemen",
		'ZM' => "Zambia",
		'ZW' => "Zimbabwe",
	];
	
	/**
	 * Get information for given country
	 *
	 * @param  string $countryCode
	 *
	 * @return array  An assosiative array with country information
	 */
	public static function getCountry($countryCode)
	{
		$countries = self::getCountriesData();
		$countryCode = \strtoupper($countryCode);

        if (!isset($countries[$countryCode]))
        {
            return;
		}
		
		return array_merge($countries[$countryCode], [
			'code' => $countryCode
		]);
	}

	/**
	 * Attemp to convert a Country Code to a Country Name
	 *
	 * @param	string	$country_code	The country code
	 * 
	 * @return	mixed	String on success, Null on failure
	 */
	public static function toCountryName($country_code)
	{
		$countries = self::getCountriesList();

		if (isset($countries[$country_code]))
		{
			return $countries[$country_code];
		}
	}

	/**
	 * Attemp to convert a Country name to a Country code
	 *
	 * @param	string  $subject	The country name
	 * 
	 * @return	mixed	String on success, Null on failure
	 */
	public static function toCountryCode($subject)
	{
		$subject = strtolower($subject);

		$cacheHash = md5('toCountryCode' . $subject);
		if (Cache::has($cacheHash))
		{
			return Cache::get($cacheHash);
		}

		$countries = array_change_key_case(self::getCountriesList());

		// Sanity check. Check first if we have a country code already.
		if (array_key_exists($subject, $countries))
		{
			return strtoupper($subject);
		}

		// Let's find the country code in the list.
        foreach ($countries as $country_code => $country_name)
        {
			if (strtolower($country_name) == $subject)
			{
				return strtoupper($country_code);
			}
        }

		// Country code still not found. Probably we have a non-english country name. 
		// Let's load one by one all the language files and try to find it there.
		$langFiles = \Joomla\Filesystem\Folder::files(JPATH_PLUGINS . '/system/nrframework/language', '.ini', 1, true);

		foreach ($langFiles as $langFile)
		{
			$strings = LanguageHelper::parseIniFile($langFile);

			foreach ($strings as $key => $label)
			{
				if (strpos($key, 'NR_COUNTRY_') === false)
				{
					continue;
				}

				if (strtolower($label) !== $subject)
				{
					continue;
				}

				// Found!
				return Cache::set($cacheHash, str_replace('NR_COUNTRY_', '', $key));
			}
		}
	}

    /**
     *  Convert a Country Name to Country Code
     *
     *  @param  string $country	The country name
	 * 
     *  @return string|void
     */
    public static function getCode($country)
    {
        $country = strtolower($country);

        foreach (self::getCountriesList() as $key => $value)
        {
			if (strtolower($value) == $country)
			{
				return $key;
			}
        }
    }

	/**
	 * Returns translatable countries list
	 * 
	 * @return  array
	 */
	public static function getCountriesList()
	{
		$countries = [];

		foreach (self::getCountriesData() as $key => $country)
        {
			$countries[$key] = $country['name'];
		}

		return $countries;
	}

	/**
	 * Returns a country's calling code.
	 * 
	 * @param   string  $country_code
	 * 
	 * @return  string
	 */
	public static function getCallingCodeByCountryCode($country_code = '')
	{
		if (!$country_code)
		{
			return;
		}

		$countries = self::getCountriesData();
		
		if (!isset($countries[$country_code]))
		{
			return;
		}

		return $countries[$country_code]['calling_code'];
	}

	/**
	* Holds the following data for each country:
	* - Name
	* - Code
	* - Calling Code
	* - Currency Code
	* - Curency Name
	* - Currency Symbol
	* 
	* @return  array
	*/
	public static function getCountriesData()
	{
		$list = [
			'AF' => [ 'name' => Text::_('NR_COUNTRY_AF'), 'calling_code' => '93', 'currency_code' => 'AFN', 'currency_name' => 'Afghan Afghani', 'currency_symbol' => '؋' ],
			'AX' => [ 'name' => Text::_('NR_COUNTRY_AX'), 'calling_code' => '358', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'AL' => [ 'name' => Text::_('NR_COUNTRY_AL'), 'calling_code' => '355', 'currency_code' => 'ALL', 'currency_name' => 'Lek', 'currency_symbol' => 'Lek' ],
			'DZ' => [ 'name' => Text::_('NR_COUNTRY_DZ'), 'calling_code' => '213', 'currency_code' => 'DZD', 'currency_name' => 'Dinar', 'currency_symbol' => 'دج' ],
			'AS' => [ 'name' => Text::_('NR_COUNTRY_AS'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'AD' => [ 'name' => Text::_('NR_COUNTRY_AD'), 'calling_code' => '376', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'AO' => [ 'name' => Text::_('NR_COUNTRY_AO'), 'calling_code' => '244', 'currency_code' => 'AOA', 'currency_name' => 'Kwanza', 'currency_symbol' => 'Kz' ],
			'AI' => [ 'name' => Text::_('NR_COUNTRY_AI'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'AQ' => [ 'name' => Text::_('NR_COUNTRY_AQ'), 'calling_code' => '672', 'currency_code' => '', 'currency_name' => '', 'currency_symbol' => '' ],
			'AG' => [ 'name' => Text::_('NR_COUNTRY_AG'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'AR' => [ 'name' => Text::_('NR_COUNTRY_AR'), 'calling_code' => '54', 'currency_code' => 'ARS', 'currency_name' => 'Peso', 'currency_symbol' => '$' ],
			'AM' => [ 'name' => Text::_('NR_COUNTRY_AM'), 'calling_code' => '374', 'currency_code' => 'AMD', 'currency_name' => 'Dram', 'currency_symbol' => '֏' ],
			'AW' => [ 'name' => Text::_('NR_COUNTRY_AW'), 'calling_code' => '297', 'currency_code' => 'AWG', 'currency_name' => 'Guilder', 'currency_symbol' => 'ƒ' ],
			'AU' => [ 'name' => Text::_('NR_COUNTRY_AU'), 'calling_code' => '61', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'AT' => [ 'name' => Text::_('NR_COUNTRY_AT'), 'calling_code' => '43', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'AZ' => [ 'name' => Text::_('NR_COUNTRY_AZ'), 'calling_code' => '994', 'currency_code' => 'AZN', 'currency_name' => 'Manat', 'currency_symbol' => 'ман' ],
			'BS' => [ 'name' => Text::_('NR_COUNTRY_BS'), 'calling_code' => '1', 'currency_code' => 'BSD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BH' => [ 'name' => Text::_('NR_COUNTRY_BH'), 'calling_code' => '973', 'currency_code' => 'BHD', 'currency_name' => 'Dinar', 'currency_symbol' => 'د.ب' ],
			'BD' => [ 'name' => Text::_('NR_COUNTRY_BD'), 'calling_code' => '880', 'currency_code' => 'BDT', 'currency_name' => 'Taka', 'currency_symbol' => '৳' ],
			'BB' => [ 'name' => Text::_('NR_COUNTRY_BB'), 'calling_code' => '1', 'currency_code' => 'BBD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BY' => [ 'name' => Text::_('NR_COUNTRY_BY'), 'calling_code' => '375', 'currency_code' => 'BYR', 'currency_name' => 'Ruble', 'currency_symbol' => 'p.' ],
			'BE' => [ 'name' => Text::_('NR_COUNTRY_BE'), 'calling_code' => '32', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'BZ' => [ 'name' => Text::_('NR_COUNTRY_BZ'), 'calling_code' => '501', 'currency_code' => 'BZD', 'currency_name' => 'Dollar', 'currency_symbol' => 'BZ$' ],
			'BJ' => [ 'name' => Text::_('NR_COUNTRY_BJ'), 'calling_code' => '229', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'BM' => [ 'name' => Text::_('NR_COUNTRY_BM'), 'calling_code' => '1', 'currency_code' => 'BMD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BQ-BO' => [ 'name' => Text::_('NR_COUNTRY_BQ_BO'), 'calling_code' => '599', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BQ-SA' => [ 'name' => Text::_('NR_COUNTRY_BQ_SA'), 'calling_code' => '599', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BQ-SE' => [ 'name' => Text::_('NR_COUNTRY_BQ_SE'), 'calling_code' => '599', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BT' => [ 'name' => Text::_('NR_COUNTRY_BT'), 'calling_code' => '975', 'currency_code' => 'BTN', 'currency_name' => 'Ngultrum', 'currency_symbol' => 'Nu.' ],
			'BO' => [ 'name' => Text::_('NR_COUNTRY_BO'), 'calling_code' => '591', 'currency_code' => 'BOB', 'currency_name' => 'Boliviano', 'currency_symbol' => '$b' ],
			'BA' => [ 'name' => Text::_('NR_COUNTRY_BA'), 'calling_code' => '387', 'currency_code' => 'BAM', 'currency_name' => 'Marka', 'currency_symbol' => 'KM' ],
			'BW' => [ 'name' => Text::_('NR_COUNTRY_BW'), 'calling_code' => '267', 'currency_code' => 'BWP', 'currency_name' => 'Pula', 'currency_symbol' => 'P' ],
			'BV' => [ 'name' => Text::_('NR_COUNTRY_BV'), 'calling_code' => '47', 'currency_code' => 'NOK', 'currency_name' => 'Krone', 'currency_symbol' => 'kr' ],
			'BR' => [ 'name' => Text::_('NR_COUNTRY_BR'), 'calling_code' => '55', 'currency_code' => 'BRL', 'currency_name' => 'Real', 'currency_symbol' => 'R$' ],
			'IO' => [ 'name' => Text::_('NR_COUNTRY_IO'), 'calling_code' => '246', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'VG' => [ 'name' => Text::_('NR_COUNTRY_VG'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BN' => [ 'name' => Text::_('NR_COUNTRY_BN'), 'calling_code' => '673', 'currency_code' => 'BND', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'BG' => [ 'name' => Text::_('NR_COUNTRY_BG'), 'calling_code' => '359', 'currency_code' => 'BGN', 'currency_name' => 'Lev', 'currency_symbol' => 'лв' ],
			'BF' => [ 'name' => Text::_('NR_COUNTRY_BF'), 'calling_code' => '226', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'BI' => [ 'name' => Text::_('NR_COUNTRY_BI'), 'calling_code' => '257', 'currency_code' => 'BIF', 'currency_name' => 'Franc', 'currency_symbol' => 'FBu' ],
			'KH' => [ 'name' => Text::_('NR_COUNTRY_KH'), 'calling_code' => '855', 'currency_code' => 'KHR', 'currency_name' => 'Riels', 'currency_symbol' => '៛' ],
			'CM' => [ 'name' => Text::_('NR_COUNTRY_CM'), 'calling_code' => '237', 'currency_code' => 'XAF', 'currency_name' => 'Franc', 'currency_symbol' => 'FCF' ],
			'CA' => [ 'name' => Text::_('NR_COUNTRY_CA'), 'calling_code' => '1', 'currency_code' => 'CAD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'CV' => [ 'name' => Text::_('NR_COUNTRY_CV'), 'calling_code' => '238', 'currency_code' => 'CVE', 'currency_name' => 'Escudo', 'currency_symbol' => '$' ],
			'KY' => [ 'name' => Text::_('NR_COUNTRY_KY'), 'calling_code' => '1', 'currency_code' => 'KYD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'CF' => [ 'name' => Text::_('NR_COUNTRY_CF'), 'calling_code' => '236', 'currency_code' => 'XAF', 'currency_name' => 'Franc', 'currency_symbol' => 'FCF' ],
			'TD' => [ 'name' => Text::_('NR_COUNTRY_TD'), 'calling_code' => '235', 'currency_code' => 'XAF', 'currency_name' => 'Franc', 'currency_symbol' => 'FCFA' ],
			'CL' => [ 'name' => Text::_('NR_COUNTRY_CL'), 'calling_code' => '56', 'currency_code' => 'CLP', 'currency_name' => 'Peso', 'currency_symbol' => '$' ],
			'CN' => [ 'name' => Text::_('NR_COUNTRY_CN'), 'calling_code' => '86', 'currency_code' => 'CNY', 'currency_name' => 'YuanRenminbi', 'currency_symbol' => '¥' ],
			'CX' => [ 'name' => Text::_('NR_COUNTRY_CX'), 'calling_code' => '61', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'CC' => [ 'name' => Text::_('NR_COUNTRY_CC'), 'calling_code' => '61', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'CO' => [ 'name' => Text::_('NR_COUNTRY_CO'), 'calling_code' => '57', 'currency_code' => 'COP', 'currency_name' => 'Peso', 'currency_symbol' => '$' ],
			'KM' => [ 'name' => Text::_('NR_COUNTRY_KM'), 'calling_code' => '269', 'currency_code' => 'KMF', 'currency_name' => 'Franc', 'currency_symbol' => 'CF' ],
			'CK' => [ 'name' => Text::_('NR_COUNTRY_CK'), 'calling_code' => '682', 'currency_code' => 'NZD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'CR' => [ 'name' => Text::_('NR_COUNTRY_CR'), 'calling_code' => '506', 'currency_code' => 'CRC', 'currency_name' => 'Colon', 'currency_symbol' => '₡' ],
			'HR' => [ 'name' => Text::_('NR_COUNTRY_HR'), 'calling_code' => '385', 'currency_code' => 'HRK', 'currency_name' => 'Kuna', 'currency_symbol' => 'kn' ],
			'CU' => [ 'name' => Text::_('NR_COUNTRY_CU'), 'calling_code' => '53', 'currency_code' => 'CUP', 'currency_name' => 'Peso', 'currency_symbol' => '₱' ],
			'CW' => [ 'name' => Text::_('NR_COUNTRY_CW'), 'calling_code' => '599', 'currency_code' => 'ANG', 'currency_name' => 'Guilder', 'currency_symbol' => 'ƒ' ],
			'CY' => [ 'name' => Text::_('NR_COUNTRY_CY'), 'calling_code' => '357', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'CZ' => [ 'name' => Text::_('NR_COUNTRY_CZ'), 'calling_code' => '420', 'currency_code' => 'CZK', 'currency_name' => 'Koruna', 'currency_symbol' => 'Kč' ],
			'CD' => [ 'name' => Text::_('NR_COUNTRY_CD'), 'calling_code' => '243', 'currency_code' => 'CDF', 'currency_name' => 'Franc', 'currency_symbol' => 'FC' ],
			'DK' => [ 'name' => Text::_('NR_COUNTRY_DK'), 'calling_code' => '45', 'currency_code' => 'DKK', 'currency_name' => 'Krone', 'currency_symbol' => 'kr' ],
			'DJ' => [ 'name' => Text::_('NR_COUNTRY_DJ'), 'calling_code' => '253', 'currency_code' => 'DJF', 'currency_name' => 'Franc', 'currency_symbol' => 'Fdj' ],
			'DM' => [ 'name' => Text::_('NR_COUNTRY_DM'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'DO' => [ 'name' => Text::_('NR_COUNTRY_DO'), 'calling_code' => '1', 'currency_code' => 'DOP', 'currency_name' => 'Peso', 'currency_symbol' => 'RD$' ],
			'TL' => [ 'name' => Text::_('NR_COUNTRY_TL'), 'calling_code' => '670', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'EC' => [ 'name' => Text::_('NR_COUNTRY_EC'), 'calling_code' => '593', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'EG' => [ 'name' => Text::_('NR_COUNTRY_EG'), 'calling_code' => '20', 'currency_code' => 'EGP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'SV' => [ 'name' => Text::_('NR_COUNTRY_SV'), 'calling_code' => '503', 'currency_code' => 'SVC', 'currency_name' => 'Colone', 'currency_symbol' => '$' ],
			'GQ' => [ 'name' => Text::_('NR_COUNTRY_GQ'), 'calling_code' => '240', 'currency_code' => 'XAF', 'currency_name' => 'Franc', 'currency_symbol' => 'FCF' ],
			'ER' => [ 'name' => Text::_('NR_COUNTRY_ER'), 'calling_code' => '291', 'currency_code' => 'ERN', 'currency_name' => 'Nakfa', 'currency_symbol' => 'Nfk' ],
			'EE' => [ 'name' => Text::_('NR_COUNTRY_EE'), 'calling_code' => '372', 'currency_code' => 'EEK', 'currency_name' => 'Kroon', 'currency_symbol' => 'kr' ],
			'ET' => [ 'name' => Text::_('NR_COUNTRY_ET'), 'calling_code' => '251', 'currency_code' => 'ETB', 'currency_name' => 'Birr', 'currency_symbol' => 'Br' ],
			'FK' => [ 'name' => Text::_('NR_COUNTRY_FK'), 'calling_code' => '500', 'currency_code' => 'FKP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'FO' => [ 'name' => Text::_('NR_COUNTRY_FO'), 'calling_code' => '298', 'currency_code' => 'DKK', 'currency_name' => 'Krone', 'currency_symbol' => 'kr' ],
			'FJ' => [ 'name' => Text::_('NR_COUNTRY_FJ'), 'calling_code' => '679', 'currency_code' => 'FJD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'FI' => [ 'name' => Text::_('NR_COUNTRY_FI'), 'calling_code' => '358', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'FR' => [ 'name' => Text::_('NR_COUNTRY_FR'), 'calling_code' => '33', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'GF' => [ 'name' => Text::_('NR_COUNTRY_GF'), 'calling_code' => '594', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'PF' => [ 'name' => Text::_('NR_COUNTRY_PF'), 'calling_code' => '689', 'currency_code' => 'XPF', 'currency_name' => 'Franc', 'currency_symbol' => 'F' ],
			'TF' => [ 'name' => Text::_('NR_COUNTRY_TF'), 'calling_code' => '262', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'GA' => [ 'name' => Text::_('NR_COUNTRY_GA'), 'calling_code' => '241', 'currency_code' => 'XAF', 'currency_name' => 'Franc', 'currency_symbol' => 'FCF' ],
			'GM' => [ 'name' => Text::_('NR_COUNTRY_GM'), 'calling_code' => '220', 'currency_code' => 'GMD', 'currency_name' => 'Dalasi', 'currency_symbol' => 'D' ],
			'GE' => [ 'name' => Text::_('NR_COUNTRY_GE'), 'calling_code' => '995', 'currency_code' => 'GEL', 'currency_name' => 'Lari', 'currency_symbol' => '₾' ],
			'DE' => [ 'name' => Text::_('NR_COUNTRY_DE'), 'calling_code' => '49', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'GH' => [ 'name' => Text::_('NR_COUNTRY_GH'), 'calling_code' => '233', 'currency_code' => 'GHC', 'currency_name' => 'Cedi', 'currency_symbol' => '¢' ],
			'GI' => [ 'name' => Text::_('NR_COUNTRY_GI'), 'calling_code' => '350', 'currency_code' => 'GIP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'GR' => [ 'name' => Text::_('NR_COUNTRY_GR'), 'calling_code' => '30', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'GL' => [ 'name' => Text::_('NR_COUNTRY_GL'), 'calling_code' => '299', 'currency_code' => 'DKK', 'currency_name' => 'Krone', 'currency_symbol' => 'kr' ],
			'GD' => [ 'name' => Text::_('NR_COUNTRY_GD'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'GP' => [ 'name' => Text::_('NR_COUNTRY_GP'), 'calling_code' => '590', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'GU' => [ 'name' => Text::_('NR_COUNTRY_GU'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'GT' => [ 'name' => Text::_('NR_COUNTRY_GT'), 'calling_code' => '502', 'currency_code' => 'GTQ', 'currency_name' => 'Quetzal', 'currency_symbol' => 'Q' ],
			'GN' => [ 'name' => Text::_('NR_COUNTRY_GN'), 'calling_code' => '224', 'currency_code' => 'GNF', 'currency_name' => 'Franc', 'currency_symbol' => 'FG' ],
			'GW' => [ 'name' => Text::_('NR_COUNTRY_GW'), 'calling_code' => '245', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'GY' => [ 'name' => Text::_('NR_COUNTRY_GY'), 'calling_code' => '592', 'currency_code' => 'GYD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'HT' => [ 'name' => Text::_('NR_COUNTRY_HT'), 'calling_code' => '509', 'currency_code' => 'HTG', 'currency_name' => 'Gourde', 'currency_symbol' => 'G' ],
			'HM' => [ 'name' => Text::_('NR_COUNTRY_HM'), 'calling_code' => '0', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'HN' => [ 'name' => Text::_('NR_COUNTRY_HN'), 'calling_code' => '504', 'currency_code' => 'HNL', 'currency_name' => 'Lempira', 'currency_symbol' => 'L' ],
			'HK' => [ 'name' => Text::_('NR_COUNTRY_HK'), 'calling_code' => '852', 'currency_code' => 'HKD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'HU' => [ 'name' => Text::_('NR_COUNTRY_HU'), 'calling_code' => '36', 'currency_code' => 'HUF', 'currency_name' => 'Forint', 'currency_symbol' => 'Ft' ],
			'IS' => [ 'name' => Text::_('NR_COUNTRY_IS'), 'calling_code' => '354', 'currency_code' => 'ISK', 'currency_name' => 'Krona', 'currency_symbol' => 'kr' ],
			'IN' => [ 'name' => Text::_('NR_COUNTRY_IN'), 'calling_code' => '91', 'currency_code' => 'INR', 'currency_name' => 'Rupee', 'currency_symbol' => '₹' ],
			'ID' => [ 'name' => Text::_('NR_COUNTRY_ID'), 'calling_code' => '62', 'currency_code' => 'IDR', 'currency_name' => 'Rupiah', 'currency_symbol' => 'Rp' ],
			'IR' => [ 'name' => Text::_('NR_COUNTRY_IR'), 'calling_code' => '98', 'currency_code' => 'IRR', 'currency_name' => 'Rial', 'currency_symbol' => '﷼' ],
			'IQ' => [ 'name' => Text::_('NR_COUNTRY_IQ'), 'calling_code' => '964', 'currency_code' => 'IQD', 'currency_name' => 'Dinar', 'currency_symbol' => 'د.ع' ],
			'IE' => [ 'name' => Text::_('NR_COUNTRY_IE'), 'calling_code' => '353', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'IM' => [ 'name' => Text::_('NR_COUNTRY_IM'), 'calling_code' => '44', 'currency_code' => 'GBP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'IL' => [ 'name' => Text::_('NR_COUNTRY_IL'), 'calling_code' => '972', 'currency_code' => 'ILS', 'currency_name' => 'Shekel', 'currency_symbol' => '₪' ],
			'IT' => [ 'name' => Text::_('NR_COUNTRY_IT'), 'calling_code' => '39', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'CI' => [ 'name' => Text::_('NR_COUNTRY_CI'), 'calling_code' => '225', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'JM' => [ 'name' => Text::_('NR_COUNTRY_JM'), 'calling_code' => '1', 'currency_code' => 'JMD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'JP' => [ 'name' => Text::_('NR_COUNTRY_JP'), 'calling_code' => '81', 'currency_code' => 'JPY', 'currency_name' => 'Yen', 'currency_symbol' => '¥' ],
			'JO' => [ 'name' => Text::_('NR_COUNTRY_JO'), 'calling_code' => '962', 'currency_code' => 'JOD', 'currency_name' => 'Dinar', 'currency_symbol' => 'د.أ' ],
			'KZ' => [ 'name' => Text::_('NR_COUNTRY_KZ'), 'calling_code' => '7', 'currency_code' => 'KZT', 'currency_name' => 'Tenge', 'currency_symbol' => 'лв' ],
			'KE' => [ 'name' => Text::_('NR_COUNTRY_KE'), 'calling_code' => '254', 'currency_code' => 'KES', 'currency_name' => 'Shilling', 'currency_symbol' => 'KSh' ],
			'KI' => [ 'name' => Text::_('NR_COUNTRY_KI'), 'calling_code' => '686', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'KW' => [ 'name' => Text::_('NR_COUNTRY_KW'), 'calling_code' => '965', 'currency_code' => 'KWD', 'currency_name' => 'Dinar', 'currency_symbol' => 'د.ك' ],
			'KG' => [ 'name' => Text::_('NR_COUNTRY_KG'), 'calling_code' => '996', 'currency_code' => 'KGS', 'currency_name' => 'Som', 'currency_symbol' => 'лв' ],
			'LA' => [ 'name' => Text::_('NR_COUNTRY_LA'), 'calling_code' => '856', 'currency_code' => 'LAK', 'currency_name' => 'Kip', 'currency_symbol' => '₭' ],
			'LV' => [ 'name' => Text::_('NR_COUNTRY_LV'), 'calling_code' => '371', 'currency_code' => 'LVL', 'currency_name' => 'Lat', 'currency_symbol' => 'Ls' ],
			'LB' => [ 'name' => Text::_('NR_COUNTRY_LB'), 'calling_code' => '961', 'currency_code' => 'LBP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'LS' => [ 'name' => Text::_('NR_COUNTRY_LS'), 'calling_code' => '266', 'currency_code' => 'LSL', 'currency_name' => 'Loti', 'currency_symbol' => 'L' ],
			'LR' => [ 'name' => Text::_('NR_COUNTRY_LR'), 'calling_code' => '231', 'currency_code' => 'LRD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'LY' => [ 'name' => Text::_('NR_COUNTRY_LY'), 'calling_code' => '218', 'currency_code' => 'LYD', 'currency_name' => 'Dinar', 'currency_symbol' => 'ل.د' ],
			'LI' => [ 'name' => Text::_('NR_COUNTRY_LI'), 'calling_code' => '423', 'currency_code' => 'CHF', 'currency_name' => 'Franc', 'currency_symbol' => 'CHF' ],
			'LT' => [ 'name' => Text::_('NR_COUNTRY_LT'), 'calling_code' => '370', 'currency_code' => 'LTL', 'currency_name' => 'Litas', 'currency_symbol' => 'Lt' ],
			'LU' => [ 'name' => Text::_('NR_COUNTRY_LU'), 'calling_code' => '352', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'MO' => [ 'name' => Text::_('NR_COUNTRY_MO'), 'calling_code' => '853', 'currency_code' => 'MOP', 'currency_name' => 'Pataca', 'currency_symbol' => 'MOP' ],
			'MK' => [ 'name' => Text::_('NR_COUNTRY_MK'), 'calling_code' => '389', 'currency_code' => 'MKD', 'currency_name' => 'Denar', 'currency_symbol' => 'ден' ],
			'MG' => [ 'name' => Text::_('NR_COUNTRY_MG'), 'calling_code' => '261', 'currency_code' => 'MGA', 'currency_name' => 'Ariary', 'currency_symbol' => 'Ar' ],
			'MW' => [ 'name' => Text::_('NR_COUNTRY_MW'), 'calling_code' => '265', 'currency_code' => 'MWK', 'currency_name' => 'Kwacha', 'currency_symbol' => 'MK' ],
			'MY' => [ 'name' => Text::_('NR_COUNTRY_MY'), 'calling_code' => '60', 'currency_code' => 'MYR', 'currency_name' => 'Ringgit', 'currency_symbol' => 'RM' ],
			'MV' => [ 'name' => Text::_('NR_COUNTRY_MV'), 'calling_code' => '960', 'currency_code' => 'MVR', 'currency_name' => 'Rufiyaa', 'currency_symbol' => 'Rf' ],
			'ML' => [ 'name' => Text::_('NR_COUNTRY_ML'), 'calling_code' => '223', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'MT' => [ 'name' => Text::_('NR_COUNTRY_MT'), 'calling_code' => '356', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'MH' => [ 'name' => Text::_('NR_COUNTRY_MH'), 'calling_code' => '692', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'MQ' => [ 'name' => Text::_('NR_COUNTRY_MQ'), 'calling_code' => '596', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'MR' => [ 'name' => Text::_('NR_COUNTRY_MR'), 'calling_code' => '222', 'currency_code' => 'MRO', 'currency_name' => 'Ouguiya', 'currency_symbol' => 'UM' ],
			'MU' => [ 'name' => Text::_('NR_COUNTRY_MU'), 'calling_code' => '230', 'currency_code' => 'MUR', 'currency_name' => 'Rupee', 'currency_symbol' => '₨' ],
			'YT' => [ 'name' => Text::_('NR_COUNTRY_YT'), 'calling_code' => '262', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'MX' => [ 'name' => Text::_('NR_COUNTRY_MX'), 'calling_code' => '52', 'currency_code' => 'MXN', 'currency_name' => 'Peso', 'currency_symbol' => '$' ],
			'FM' => [ 'name' => Text::_('NR_COUNTRY_FM'), 'calling_code' => '691', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'MD' => [ 'name' => Text::_('NR_COUNTRY_MD'), 'calling_code' => '373', 'currency_code' => 'MDL', 'currency_name' => 'Leu', 'currency_symbol' => 'L' ],
			'MC' => [ 'name' => Text::_('NR_COUNTRY_MC'), 'calling_code' => '377', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'MN' => [ 'name' => Text::_('NR_COUNTRY_MN'), 'calling_code' => '976', 'currency_code' => 'MNT', 'currency_name' => 'Tugrik', 'currency_symbol' => '₮' ],
			'ME' => [ 'name' => Text::_('NR_COUNTRY_ME'), 'calling_code' => '382', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'MS' => [ 'name' => Text::_('NR_COUNTRY_MS'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'MA' => [ 'name' => Text::_('NR_COUNTRY_MA'), 'calling_code' => '212', 'currency_code' => 'MAD', 'currency_name' => 'Dirham', 'currency_symbol' => 'DH' ],
			'MZ' => [ 'name' => Text::_('NR_COUNTRY_MZ'), 'calling_code' => '258', 'currency_code' => 'MZN', 'currency_name' => 'Meticail', 'currency_symbol' => 'MT' ],
			'MM' => [ 'name' => Text::_('NR_COUNTRY_MM'), 'calling_code' => '95', 'currency_code' => 'MMK', 'currency_name' => 'Kyat', 'currency_symbol' => 'K' ],
			'NA' => [ 'name' => Text::_('NR_COUNTRY_NA'), 'calling_code' => '264', 'currency_code' => 'NAD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'NR' => [ 'name' => Text::_('NR_COUNTRY_NR'), 'calling_code' => '674', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'NP' => [ 'name' => Text::_('NR_COUNTRY_NP'), 'calling_code' => '977', 'currency_code' => 'NPR', 'currency_name' => 'Rupee', 'currency_symbol' => '₨' ],
			'NL' => [ 'name' => Text::_('NR_COUNTRY_NL'), 'calling_code' => '31', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'NC' => [ 'name' => Text::_('NR_COUNTRY_NC'), 'calling_code' => '687', 'currency_code' => 'XPF', 'currency_name' => 'Franc', 'currency_symbol' => 'F' ],
			'NZ' => [ 'name' => Text::_('NR_COUNTRY_NZ'), 'calling_code' => '64', 'currency_code' => 'NZD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'NI' => [ 'name' => Text::_('NR_COUNTRY_NI'), 'calling_code' => '505', 'currency_code' => 'NIO', 'currency_name' => 'Cordoba', 'currency_symbol' => 'C$' ],
			'NE' => [ 'name' => Text::_('NR_COUNTRY_NE'), 'calling_code' => '227', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'NG' => [ 'name' => Text::_('NR_COUNTRY_NG'), 'calling_code' => '234', 'currency_code' => 'NGN', 'currency_name' => 'Naira', 'currency_symbol' => '₦' ],
			'NU' => [ 'name' => Text::_('NR_COUNTRY_NU'), 'calling_code' => '683', 'currency_code' => 'NZD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'NF' => [ 'name' => Text::_('NR_COUNTRY_NF'), 'calling_code' => '672', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'KP' => [ 'name' => Text::_('NR_COUNTRY_KP'), 'calling_code' => '850', 'currency_code' => 'KPW', 'currency_name' => 'Won', 'currency_symbol' => '₩' ],
			'MP' => [ 'name' => Text::_('NR_COUNTRY_MP'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'NO' => [ 'name' => Text::_('NR_COUNTRY_NO'), 'calling_code' => '47', 'currency_code' => 'NOK', 'currency_name' => 'Krone', 'currency_symbol' => 'kr' ],
			'OM' => [ 'name' => Text::_('NR_COUNTRY_OM'), 'calling_code' => '968', 'currency_code' => 'OMR', 'currency_name' => 'Rial', 'currency_symbol' => '﷼' ],
			'PK' => [ 'name' => Text::_('NR_COUNTRY_PK'), 'calling_code' => '92', 'currency_code' => 'PKR', 'currency_name' => 'Rupee', 'currency_symbol' => '₨' ],
			'PW' => [ 'name' => Text::_('NR_COUNTRY_PW'), 'calling_code' => '680', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'PS' => [ 'name' => Text::_('NR_COUNTRY_PS'), 'calling_code' => '970', 'currency_code' => 'ILS', 'currency_name' => 'Shekel', 'currency_symbol' => '₪' ],
			'PA' => [ 'name' => Text::_('NR_COUNTRY_PA'), 'calling_code' => '507', 'currency_code' => 'PAB', 'currency_name' => 'Balboa', 'currency_symbol' => 'B/.' ],
			'PG' => [ 'name' => Text::_('NR_COUNTRY_PG'), 'calling_code' => '675', 'currency_code' => 'PGK', 'currency_name' => 'Kina', 'currency_symbol' => 'K' ],
			'PY' => [ 'name' => Text::_('NR_COUNTRY_PY'), 'calling_code' => '595', 'currency_code' => 'PYG', 'currency_name' => 'Guarani', 'currency_symbol' => 'Gs' ],
			'PE' => [ 'name' => Text::_('NR_COUNTRY_PE'), 'calling_code' => '51', 'currency_code' => 'PEN', 'currency_name' => 'Sol', 'currency_symbol' => 'S/.' ],
			'PH' => [ 'name' => Text::_('NR_COUNTRY_PH'), 'calling_code' => '63', 'currency_code' => 'PHP', 'currency_name' => 'Peso', 'currency_symbol' => 'Php' ],
			'PN' => [ 'name' => Text::_('NR_COUNTRY_PN'), 'calling_code' => '870', 'currency_code' => 'NZD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'PL' => [ 'name' => Text::_('NR_COUNTRY_PL'), 'calling_code' => '48', 'currency_code' => 'PLN', 'currency_name' => 'Zloty', 'currency_symbol' => 'zł' ],
			'PT' => [ 'name' => Text::_('NR_COUNTRY_PT'), 'calling_code' => '351', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'PR' => [ 'name' => Text::_('NR_COUNTRY_PR'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'QA' => [ 'name' => Text::_('NR_COUNTRY_QA'), 'calling_code' => '974', 'currency_code' => 'QAR', 'currency_name' => 'Rial', 'currency_symbol' => '﷼' ],
			'CG' => [ 'name' => Text::_('NR_COUNTRY_CG'), 'calling_code' => '242', 'currency_code' => 'XAF', 'currency_name' => 'Franc', 'currency_symbol' => 'FCF' ],
			'RE' => [ 'name' => Text::_('NR_COUNTRY_RE'), 'calling_code' => '262', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'RO' => [ 'name' => Text::_('NR_COUNTRY_RO'), 'calling_code' => '40', 'currency_code' => 'RON', 'currency_name' => 'Leu', 'currency_symbol' => 'lei' ],
			'RU' => [ 'name' => Text::_('NR_COUNTRY_RU'), 'calling_code' => '7', 'currency_code' => 'RUB', 'currency_name' => 'Ruble', 'currency_symbol' => 'руб' ],
			'RW' => [ 'name' => Text::_('NR_COUNTRY_RW'), 'calling_code' => '250', 'currency_code' => 'RWF', 'currency_name' => 'Franc', 'currency_symbol' => 'FRw' ],
			'SH' => [ 'name' => Text::_('NR_COUNTRY_SH'), 'calling_code' => '290', 'currency_code' => 'SHP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'KN' => [ 'name' => Text::_('NR_COUNTRY_KN'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'LC' => [ 'name' => Text::_('NR_COUNTRY_LC'), 'calling_code' => '1758', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'PM' => [ 'name' => Text::_('NR_COUNTRY_PM'), 'calling_code' => '508', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'VC' => [ 'name' => Text::_('NR_COUNTRY_VC'), 'calling_code' => '1', 'currency_code' => 'XCD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'WS' => [ 'name' => Text::_('NR_COUNTRY_WS'), 'calling_code' => '685', 'currency_code' => 'WST', 'currency_name' => 'Tala', 'currency_symbol' => 'WS$' ],
			'SM' => [ 'name' => Text::_('NR_COUNTRY_SM'), 'calling_code' => '378', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'ST' => [ 'name' => Text::_('NR_COUNTRY_ST'), 'calling_code' => '239', 'currency_code' => 'STD', 'currency_name' => 'Dobra', 'currency_symbol' => 'Db' ],
			'SA' => [ 'name' => Text::_('NR_COUNTRY_SA'), 'calling_code' => '966', 'currency_code' => 'SAR', 'currency_name' => 'Rial', 'currency_symbol' => '﷼' ],
			'SN' => [ 'name' => Text::_('NR_COUNTRY_SN'), 'calling_code' => '221', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'RS' => [ 'name' => Text::_('NR_COUNTRY_RS'), 'calling_code' => '381', 'currency_code' => 'RSD', 'currency_name' => 'Dinar', 'currency_symbol' => 'Дин' ],
			'SC' => [ 'name' => Text::_('NR_COUNTRY_SC'), 'calling_code' => '248', 'currency_code' => 'SCR', 'currency_name' => 'Rupee', 'currency_symbol' => '₨' ],
			'SL' => [ 'name' => Text::_('NR_COUNTRY_SL'), 'calling_code' => '232', 'currency_code' => 'SLL', 'currency_name' => 'Leone', 'currency_symbol' => 'Le' ],
			'SG' => [ 'name' => Text::_('NR_COUNTRY_SG'), 'calling_code' => '65', 'currency_code' => 'SGD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'SK' => [ 'name' => Text::_('NR_COUNTRY_SK'), 'calling_code' => '421', 'currency_code' => 'SKK', 'currency_name' => 'Koruna', 'currency_symbol' => 'Sk' ],
			'SI' => [ 'name' => Text::_('NR_COUNTRY_SI'), 'calling_code' => '386', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'SB' => [ 'name' => Text::_('NR_COUNTRY_SB'), 'calling_code' => '677', 'currency_code' => 'SBD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'SO' => [ 'name' => Text::_('NR_COUNTRY_SO'), 'calling_code' => '252', 'currency_code' => 'SOS', 'currency_name' => 'Shilling', 'currency_symbol' => 'S' ],
			'ZA' => [ 'name' => Text::_('NR_COUNTRY_ZA'), 'calling_code' => '27', 'currency_code' => 'ZAR', 'currency_name' => 'Rand', 'currency_symbol' => 'R' ],
			'GS' => [ 'name' => Text::_('NR_COUNTRY_GS'), 'calling_code' => '500', 'currency_code' => 'GBP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'KR' => [ 'name' => Text::_('NR_COUNTRY_KR'), 'calling_code' => '82', 'currency_code' => 'KRW', 'currency_name' => 'Won', 'currency_symbol' => '₩' ],
			'ES' => [ 'name' => Text::_('NR_COUNTRY_ES'), 'calling_code' => '34', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'LK' => [ 'name' => Text::_('NR_COUNTRY_LK'), 'calling_code' => '94', 'currency_code' => 'LKR', 'currency_name' => 'Rupee', 'currency_symbol' => '₨' ],
			'SD' => [ 'name' => Text::_('NR_COUNTRY_SD'), 'calling_code' => '249', 'currency_code' => 'SDD', 'currency_name' => 'Dinar', 'currency_symbol' => 'ج.س' ],
			'SS' => [ 'name' => Text::_('NR_COUNTRY_SS'), 'calling_code' => '211', 'currency_code' => 'SSP', 'currency_name' => 'Pound', 'currency_symbol' => 'SS£' ],
			'SR' => [ 'name' => Text::_('NR_COUNTRY_SR'), 'calling_code' => '597', 'currency_code' => 'SRD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'SJ' => [ 'name' => Text::_('NR_COUNTRY_SJ'), 'calling_code' => '47', 'currency_code' => 'NOK', 'currency_name' => 'Krone', 'currency_symbol' => 'kr' ],
			'SZ' => [ 'name' => Text::_('NR_COUNTRY_SZ'), 'calling_code' => '268', 'currency_code' => 'SZL', 'currency_name' => 'Lilangeni', 'currency_symbol' => 'L' ],
			'SE' => [ 'name' => Text::_('NR_COUNTRY_SE'), 'calling_code' => '46', 'currency_code' => 'SEK', 'currency_name' => 'Krona', 'currency_symbol' => 'kr' ],
			'CH' => [ 'name' => Text::_('NR_COUNTRY_CH'), 'calling_code' => '41', 'currency_code' => 'CHF', 'currency_name' => 'Franc', 'currency_symbol' => 'CHF' ],
			'SY' => [ 'name' => Text::_('NR_COUNTRY_SY'), 'calling_code' => '963', 'currency_code' => 'SYP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'TW' => [ 'name' => Text::_('NR_COUNTRY_TW'), 'calling_code' => '886', 'currency_code' => 'TWD', 'currency_name' => 'Dollar', 'currency_symbol' => 'NT$' ],
			'TJ' => [ 'name' => Text::_('NR_COUNTRY_TJ'), 'calling_code' => '992', 'currency_code' => 'TJS', 'currency_name' => 'Somoni', 'currency_symbol' => 'SM' ],
			'TZ' => [ 'name' => Text::_('NR_COUNTRY_TZ'), 'calling_code' => '255', 'currency_code' => 'TZS', 'currency_name' => 'Shilling', 'currency_symbol' => 'TSh' ],
			'TH' => [ 'name' => Text::_('NR_COUNTRY_TH'), 'calling_code' => '66', 'currency_code' => 'THB', 'currency_name' => 'Baht', 'currency_symbol' => '฿' ],
			'TG' => [ 'name' => Text::_('NR_COUNTRY_TG'), 'calling_code' => '228', 'currency_code' => 'XOF', 'currency_name' => 'Franc', 'currency_symbol' => 'CFA' ],
			'TK' => [ 'name' => Text::_('NR_COUNTRY_TK'), 'calling_code' => '690', 'currency_code' => 'NZD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'TO' => [ 'name' => Text::_('NR_COUNTRY_TO'), 'calling_code' => '676', 'currency_code' => 'TOP', 'currency_name' => 'Paanga', 'currency_symbol' => 'T$' ],
			'TT' => [ 'name' => Text::_('NR_COUNTRY_TT'), 'calling_code' => '1', 'currency_code' => 'TTD', 'currency_name' => 'Dollar', 'currency_symbol' => 'TT$' ],
			'TN' => [ 'name' => Text::_('NR_COUNTRY_TN'), 'calling_code' => '216', 'currency_code' => 'TND', 'currency_name' => 'Dinar', 'currency_symbol' => 'د.ت' ],
			'TR' => [ 'name' => Text::_('NR_COUNTRY_TR'), 'calling_code' => '90', 'currency_code' => 'TRY', 'currency_name' => 'Lira', 'currency_symbol' => 'YTL' ],
			'TM' => [ 'name' => Text::_('NR_COUNTRY_TM'), 'calling_code' => '993', 'currency_code' => 'TMM', 'currency_name' => 'Manat', 'currency_symbol' => 'm' ],
			'TC' => [ 'name' => Text::_('NR_COUNTRY_TC'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'TV' => [ 'name' => Text::_('NR_COUNTRY_TV'), 'calling_code' => '688', 'currency_code' => 'AUD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'VI' => [ 'name' => Text::_('NR_COUNTRY_VI'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'UG' => [ 'name' => Text::_('NR_COUNTRY_UG'), 'calling_code' => '256', 'currency_code' => 'UGX', 'currency_name' => 'Shilling', 'currency_symbol' => 'USh' ],
			'UA' => [ 'name' => Text::_('NR_COUNTRY_UA'), 'calling_code' => '380', 'currency_code' => 'UAH', 'currency_name' => 'Hryvnia', 'currency_symbol' => '₴' ],
			'AE' => [ 'name' => Text::_('NR_COUNTRY_AE'), 'calling_code' => '971', 'currency_code' => 'AED', 'currency_name' => 'Dirham', 'currency_symbol' => 'د.إ' ],
			'GB' => [ 'name' => Text::_('NR_COUNTRY_GB'), 'calling_code' => '44', 'currency_code' => 'GBP', 'currency_name' => 'Pound', 'currency_symbol' => '£' ],
			'US' => [ 'name' => Text::_('NR_COUNTRY_US'), 'calling_code' => '1', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'UM' => [ 'name' => Text::_('NR_COUNTRY_UM'), 'calling_code' => '246', 'currency_code' => 'USD', 'currency_name' => 'Dollar', 'currency_symbol' => '$' ],
			'UY' => [ 'name' => Text::_('NR_COUNTRY_UY'), 'calling_code' => '598', 'currency_code' => 'UYU', 'currency_name' => 'Peso', 'currency_symbol' => '$U' ],
			'UZ' => [ 'name' => Text::_('NR_COUNTRY_UZ'), 'calling_code' => '998', 'currency_code' => 'UZS', 'currency_name' => 'Som', 'currency_symbol' => 'лв' ],
			'VU' => [ 'name' => Text::_('NR_COUNTRY_VU'), 'calling_code' => '678', 'currency_code' => 'VUV', 'currency_name' => 'Vatu', 'currency_symbol' => 'Vt' ],
			'VA' => [ 'name' => Text::_('NR_COUNTRY_VA'), 'calling_code' => '39', 'currency_code' => 'EUR', 'currency_name' => 'Euro', 'currency_symbol' => '€' ],
			'VE' => [ 'name' => Text::_('NR_COUNTRY_VE'), 'calling_code' => '58', 'currency_code' => 'VEF', 'currency_name' => 'Bolivar', 'currency_symbol' => 'Bs' ],
			'VN' => [ 'name' => Text::_('NR_COUNTRY_VN'), 'calling_code' => '84', 'currency_code' => 'VND', 'currency_name' => 'Dong', 'currency_symbol' => '₫' ],
			'WF' => [ 'name' => Text::_('NR_COUNTRY_WF'), 'calling_code' => '681', 'currency_code' => 'XPF', 'currency_name' => 'Franc', 'currency_symbol' => 'F' ],
			'EH' => [ 'name' => Text::_('NR_COUNTRY_EH'), 'calling_code' => '212', 'currency_code' => 'MAD', 'currency_name' => 'Dirham', 'currency_symbol' => 'DH' ],
			'YE' => [ 'name' => Text::_('NR_COUNTRY_YE'), 'calling_code' => '967', 'currency_code' => 'YER', 'currency_name' => 'Rial', 'currency_symbol' => '﷼' ],
			'ZM' => [ 'name' => Text::_('NR_COUNTRY_ZM'), 'calling_code' => '260', 'currency_code' => 'ZMK', 'currency_name' => 'Kwacha', 'currency_symbol' => 'ZK' ],
			'ZW' => [ 'name' => Text::_('NR_COUNTRY_ZW'), 'calling_code' => '263', 'currency_code' => 'ZWD', 'currency_name' => 'Dollar', 'currency_symbol' => 'Z$' ]
		];

		// Sort by name
		uasort($list, function ($item1, $item2)
		{
			return $item1['name'] <=> $item2['name'];
		});

		return $list;
	}
}