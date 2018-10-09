<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2005 Robert Lemke (robert@typo3.org)
*  All rights reserved
*
*  This script is part of the Typo3 project. The Typo3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
*  (at your option) any later version.
*
*  The GNU General Public License can be found at
*  http://www.gnu.org/copyleft/gpl.html.
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
 * Plugin 'rlmp_dateselectlib' for the 'rlmp_dateselectlib' extension.
 *
 * @author	Robert Lemke <robert@typo3.org>
 */

/**
 * [CLASS/FUNCTION INDEX of SCRIPT]
 *
 *
 *
 *   49: class tx_rlmpdateselectlib
 *   59:     function main($content,$conf)
 *   68:     function includeLib()
 *  196:     function tx_rlmpdateselectlib_selected(cal, date)
 *  204:     function tx_rlmpdateselectlib_closeHandler(cal)
 *  211:     function tx_rlmpdateselectlib_showCalendar (id, format)
 *  249:     function getInputButton ($id,$conf)
 *
 * TOTAL FUNCTIONS: 6
 * (This index is automatically created/updated by the extension "extdeveval")
 *
 */
require_once (PATH_t3lib.'class.t3lib_tsparser.php');

class tx_rlmpdateselectlib {


	/**
	 * Dummy function
	 *
	 * @param	string		$content: Dummy
	 * @param	array		$conf: Dummy
	 * @return	void
	 */
	function main($content,$conf)	{
	}


	/**
	 * Include the javascript library and other data for page rendering
	 *
	 * @return	void
	 */
	function includeLib()	{

			// Read ext_typoscript_setup.txt for this extension and parse it into an array

		$tconf = $GLOBALS['TYPO3_LOADED_EXT']['rlmp_dateselectlib']['ext_typoscript_setup.txt']?t3lib_div::getUrl($GLOBALS['TYPO3_LOADED_EXT']['rlmp_dateselectlib']['ext_typoscript_setup.txt']):'';

		$infoParser = new t3lib_TSparser;
		$infoParser->parse($tconf);
		$conf = $infoParser->setup['plugin.']['tx_rlmpdateselectlib.'];

			// Get configuration from TypoScript
		if (is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_rlmpdateselectlib.'])) {
			$conf = t3lib_div::array_merge_recursive_overrule ($conf,$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_rlmpdateselectlib.']);
		}

		$GLOBALS['tx_rlmpdateselectlib.'] = $conf;

			// Check if another language than 'default' was selected
		$LLkey = 'default';
		if ($GLOBALS['TSFE']->config['config']['language'] && $GLOBALS["TSFE"]->config["config"]["language"] != 'en') {
			$LLkey = $GLOBALS['TSFE']->config['config']['language'];
		}


		$stylesheet = $conf['calConf.']['stylesheet'];
		if (substr ($stylesheet,0,4) == 'EXT:')	{		// extension
			list($extKey,$local) = explode('/',substr($stylesheet,4),2);
			$stylesheet='';
			if (strcmp($extKey,'') && t3lib_extMgm::isLoaded($extKey) && strcmp($local,''))	{
				$stylesheet = t3lib_div::getIndpEnv ('TYPO3_SITE_URL').$GLOBALS['TYPO3_LOADED_EXT'][$extKey]['siteRelPath'].$local;
			}
		}

		if (!$GLOBALS['tx_rlmpdateselectlib']['tx_rlmpdateselectlib_includeonce']) {

				// Add the calendar JavaScripts to page header
			$GLOBALS['TSFE']->additionalHeaderData['tx_rlmpdateselectlib_includeonce'] = '

			<!-- import the calendar script -->
				<script type="text/javascript" src="'.t3lib_extMgm::siteRelPath('rlmp_dateselectlib').'calendar-typo3.js"></script>
				<link rel="stylesheet" type="text/css" media="all" href="'.$stylesheet.'" />

				<script type="text/javascript">
			/*<![CDATA[*/

					tx_rlmpdateselectlib_calendar._SDN = new Array
					("'.tx_rlmpdateselectlib::get_LL('shortweekdays_sunday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_monday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_tuesday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_wednesday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_thursday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_friday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_saturday').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortweekdays_sunday').'")

					tx_rlmpdateselectlib_calendar._DN = new Array
					("'.tx_rlmpdateselectlib::get_LL('weekdays_sunday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_monday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_tuesday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_wednesday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_thursday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_friday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_saturday').'",
					 "'.tx_rlmpdateselectlib::get_LL('weekdays_sunday').'")

					tx_rlmpdateselectlib_calendar._MN = new Array
					("'.tx_rlmpdateselectlib::get_LL('months_january').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_february').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_march').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_april').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_may').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_june').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_july').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_august').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_september').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_october').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_november').'",
					 "'.tx_rlmpdateselectlib::get_LL('months_december').'")

					tx_rlmpdateselectlib_calendar._SMN = new Array
					("'.tx_rlmpdateselectlib::get_LL('shortmonths_january').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_february').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_march').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_april').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_may').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_june').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_july').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_august').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_september').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_october').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_november').'",
					 "'.tx_rlmpdateselectlib::get_LL('shortmonths_december').'")

						// tooltips
					tx_rlmpdateselectlib_calendar._TT = {};
					tx_rlmpdateselectlib_calendar._TT["ABOUT"] = "'.tx_rlmpdateselectlib::get_LL('tt_about').'";
					tx_rlmpdateselectlib_calendar._TT["ABOUT_TIME"] = "'.tx_rlmpdateselectlib::get_LL('tt_about_time').'";
					tx_rlmpdateselectlib_calendar._TT["TOGGLE"] = "'.tx_rlmpdateselectlib::get_LL('tt_toggle_first_day_of_week').'";
					tx_rlmpdateselectlib_calendar._TT["PREV_YEAR"] = "'.tx_rlmpdateselectlib::get_LL('tt_previous_year').'";
					tx_rlmpdateselectlib_calendar._TT["PREV_MONTH"] = "'.tx_rlmpdateselectlib::get_LL('tt_previous_month').'";
					tx_rlmpdateselectlib_calendar._TT["GO_TODAY"] = "'.tx_rlmpdateselectlib::get_LL('tt_go_today').'";
					tx_rlmpdateselectlib_calendar._TT["NEXT_MONTH"] = "'.tx_rlmpdateselectlib::get_LL('tt_next_month').'";
					tx_rlmpdateselectlib_calendar._TT["NEXT_YEAR"] = "'.tx_rlmpdateselectlib::get_LL('tt_next_year').'";
					tx_rlmpdateselectlib_calendar._TT["SEL_DATE"] = "'.tx_rlmpdateselectlib::get_LL('tt_select_date').'";
					tx_rlmpdateselectlib_calendar._TT["DRAG_TO_MOVE"] = "'.tx_rlmpdateselectlib::get_LL('tt_drag_to_move').'";
					tx_rlmpdateselectlib_calendar._TT["PART_TODAY"] = " ('.tx_rlmpdateselectlib::get_LL('tt_part_today').')";
					tx_rlmpdateselectlib_calendar._TT["MON_FIRST"] = "'.tx_rlmpdateselectlib::get_LL('tt_display_monday_first').'";
					tx_rlmpdateselectlib_calendar._TT["SUN_FIRST"] = "'.tx_rlmpdateselectlib::get_LL('tt_display_sunday_first').'";
					tx_rlmpdateselectlib_calendar._TT["DAY_FIRST"] = "'.tx_rlmpdateselectlib::get_LL('tt_display_day_first').'";

					tx_rlmpdateselectlib_calendar._TT["CLOSE"] = "'.tx_rlmpdateselectlib::get_LL('tt_close').'";
					tx_rlmpdateselectlib_calendar._TT["TODAY"] = "'.tx_rlmpdateselectlib::get_LL('tt_today').'";

						// date formats
					tx_rlmpdateselectlib_calendar._TT["DEF_DATE_FORMAT"] = "'.($conf['calConf.']['dateTimeFormat'] ? $conf['calConf.']['dateTimeFormat'] : tx_rlmpdateselectlib::get_LL('dateTimeFormat')).'";
					tx_rlmpdateselectlib_calendar._TT["TT_DATE_FORMAT"] = "'.($conf['calConf.']['toolTipDateTimeFormat']?$conf['calConf.']['toolTipDateTimeFormat']:tx_rlmpdateselectlib::get_LL('toolTipDateTimeFormat')).'";
					tx_rlmpdateselectlib_calendar._TT["WEEKEND"] = "'.($conf['calConf.']['weekend'] ? $conf['calConf.']['weekend'] : '6,0').'";

					tx_rlmpdateselectlib_calendar._TT["WK"] = "'.tx_rlmpdateselectlib::get_LL('week').'";

						// This function gets called when the end-user clicks on some date.
					function tx_rlmpdateselectlib_selected(cal, date) {
						cal.sel.value = date; // just update the date in the input field.
			//			cal.sel.onchange();
						cal.callCloseHandler();
					}

						// And this gets called when the end-user clicks on the _selected_ date,
						// or clicks on the "Close" button.  It just hides the calendar without
						// destroying it.
					function tx_rlmpdateselectlib_closeHandler(cal) {
						cal.hide();                        // hide the calendar
					}

						// This function shows the calendar under the element having the given id.
						// It takes care of catching "mousedown" signals on document and hiding the
						// calendar if the click was outside.
					function tx_rlmpdateselectlib_showCalendar (id, format) {
						var el = document.getElementById(id);
						if (calendar != null) {				// we already have some calendar created
							calendar.hide();				// so we hide it first.
						} else {
								// first-time call, create the calendar.
							var cal = new tx_rlmpdateselectlib_calendar('. ($conf['calConf.']['weekStartsMonday'] ? $conf['calConf.']['weekStartsMonday'] : 'false') .', null, tx_rlmpdateselectlib_selected, tx_rlmpdateselectlib_closeHandler);
							cal.weekNumbers = '.($conf['calConf.']['displayWeekNumbers']?$conf['calConf.']['displayWeekNumbers']:'true').';
							calendar = cal;                  // remember it in the global var
							cal.setRange('.($conf['calConf.']['allowedYearMin']?$conf['calConf.']['allowedYearMin']:'1900').', '.($conf['calConf.']['allowedYearMax']?$conf['calConf.']['allowedYearMax']:'2070').');        // min/max year allowed.
							cal.create();
						}
						calendar.setDateFormat(format);    // set the specified date format
						calendar.parseDate(el.value);      // try to parse the text in field
						calendar.sel = el;                 // inform it what input field we use
					  '.
	($conf['calConf.']['showMethod'] == 'absolute' ?
		('calendar.showAt ('.$conf['calConf.']['showPositionAbsolute'].');') :
		('calendar.showAtElement(el);')
	).'
					  return false;
					}
			/*]]>*/
				</script>
			';

			$GLOBALS['tx_rlmpdateselectlib']['tx_rlmpdateselectlib_includeonce'] = TRUE;
		}
	}


	/**
	 * Returns an input button which contains an onClick handler for opening the calendar
	 *
	 * @param	string		$id: HTML id of your input button
	 * @param	array		$conf: Configuration for creating the dynamic calendar (see manual). Overrides TypoScript configuration.
	 * @return	string		HTML code (input button) for the date selector
	 */
	function getInputButton ($id,$conf) {

			// Get configuration from TypoScript
		if (is_array($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_rlmpdateselectlib.']) && is_array ($conf)) {
			$conf = t3lib_div::array_merge_recursive_overrule ($GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_rlmpdateselectlib.'], $conf);
		}
			// Get configuration from global variable
		if (is_array($GLOBALS['tx_rlmpdateselectlib.']) && is_array ($conf)) {
			$conf = t3lib_div::array_merge_recursive_overrule ($GLOBALS['tx_rlmpdateselectlib.'],$conf);
		}

		$out =  '<input type="reset" value=" '.$conf['calConf.']['inputFieldLabel'].' " onclick="return tx_rlmpdateselectlib_showCalendar('."'".$id."'".', '."'". ($conf['calConf.']['inputFieldDateTimeFormat']?$conf['calConf.']['inputFieldDateTimeFormat']:'%y-%m-%d') ."'".');">';
		return $out;
	}


	# Locallang function taken from pi_base, modified to work without instance

	/**
	 * Returns the localized label of the LOCAL_LANG key, $key
	 * Notice that for debugging purposes prefixes for the output values can be set with the internal vars ->LLtestPrefixAlt and ->LLtestPrefix
	 *
	 * @param	string		The key from the LOCAL_LANG array for which to return the value.
	 * @param	string		Alternative string to return IF no value is found set for the key, neither for the local language nor the default.
	 * @param	boolean		If true, the output label is passed through htmlspecialchars()
	 * @return	string		The value from LOCAL_LANG.
	 */
	function get_LL($key,$alt='',$hsc=FALSE)	{
			// Load translations
			// This is done kind of manually here, because we use this class
			// without making an instance. That's why I don't use pi_loadLL etc.
			// because we don't know in which scope we're currently in.
			//	Do you know a better way? Tell me ... ;-)

		$basePath = t3lib_extMgm::siteRelPath('rlmp_dateselectlib').'/locallang.php';
		if (@is_file($basePath))	{
			include('./'.$basePath);
		}

		if (isset($LOCAL_LANG[$this->LLkey][$key]))	{
			$word = $GLOBALS['TSFE']->csConv($LOCAL_LANG[$this->LLkey][$key], $this->LOCAL_LANG_charset[$this->LLkey][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif ($this->altLLkey && isset($LOCAL_LANG[$this->altLLkey][$key]))	{
			$word = $GLOBALS['TSFE']->csConv($LOCAL_LANG[$this->altLLkey][$key], $this->LOCAL_LANG_charset[$this->altLLkey][$key]);	// The "from" charset is normally empty and thus it will convert from the charset of the system language, but if it is set (see ->pi_loadLL()) it will be used.
		} elseif (isset($LOCAL_LANG['default'][$key]))	{
			$word = $LOCAL_LANG['default'][$key];	// No charset conversion because default is english and thereby ASCII
		} else {
			$word = $this->LLtestPrefixAlt.$alt;
		}

		$output = $this->LLtestPrefix.$word;
		if ($hsc)	$output = htmlspecialchars($output);

		return $output;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rlmp_dateselectlib/class.tx_rlmpdateselectlib.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/rlmp_dateselectlib/class.tx_rlmpdateselectlib.php']);
}

?>