<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2006-2010 Joachim Ruhs <postmaster@joachim-ruhs.de>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
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
 * Plugin 'Vacation flat manager' for the 'flatmgr' extension.
 *
 * @author        Joachim Ruhs <postmaster@joachim-ruhs.de>
 */

if (!class_exists('tslib_pibase')) require_once(PATH_tslib . 'class.tslib_pibase.php');

class tx_flatmgr_pi1 extends tslib_pibase {
        var $prefixId = 'tx_flatmgr_pi1';                // Same as class name
        var $scriptRelPath = 'pi1/class.tx_flatmgr_pi1.php';        // Path to this script relative to the extension dir.
        var $extKey = 'flatmgr';        // The extension key.
        var $pi_checkCHash = TRUE;
        
        var $mode ='';
        /**
         * Main method of your PlugIn
         *
         * @param        string                $content: The content of the PlugIn
         * @param        array                $conf: The PlugIn Configuration
         * @return        The content that should be displayed on the website
         */
        function main($content,$conf)        {
            $this->pi_setPiVarDefaults();
			$this->conf = $conf;
			$this->pi_loadLL();

        	// reading plugin parameters
			$this->readExtConf();
			// inserted for _DEFAULT_PI_VARS. given in Typoscript 04.12.2011
			 $this->_GP = array_merge((array)$this->conf['_DEFAULT_PI_VARS.'], (array) $this->_GP);
			if (!$this->conf['pid_list']) {
            $pidList = $this->pi_getPidList($this->cObj->data['pages'],$this->cObj->data['recursive']);
			$this->conf['pid_list'] = $pidList;
			}
            if (version_compare(TYPO3_branch, '6.0', '<')) {
                $this->cObj = t3lib_div::makeInstance('tslib_cObj');
            } else {
                $this->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
            }

			// Including additional CSS Style
			$cssFile = $this->getRelativeFileName($this->conf['cssFile']);
			$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1'] = '<link rel="stylesheet" type="text/css" href="'.$cssFile.'" />';

 			if ($this->piVars['mode'])
				if (! preg_match('/^[A-z]*$/',$this->piVars['mode'])) die("mode input error");

			if ($this->conf['displayMode'] != 'administration')
				$error = $this->checkInputData();
			if ($error != '') return $this->pi_wrapInBaseClass($error);

		    switch($this->conf["displayMode"]) {
				case 'administration':
				case 'monthlyOverview':
					if (version_compare(TYPO3_branch, '6.0', '<')) {
						$controller = t3lib_div::makeInstance('tx_flatmgr_admin_controller');
					} else {
						$controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_controller');
					}
					break;
				default: 
					if (version_compare(TYPO3_branch, '6.0', '<')) {
						$controller = t3lib_div::makeInstance('tx_flatmgr_controller');
					} else {
						$controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_controller');
					}
					break;
			}

		    switch($this->conf["displayMode"]){
				case 'monthMultiRow': {
					$this->_GP['action'] = 'showMonthMultiRow';
//					$this->_GP['flat'] = $this->piVars['flat'];
//					$this->_GP['year'] = $this->piVars['year'];
					$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
				}
				case 'monthSingleRow': {
					$this->_GP['action'] = 'showMonthSingleRow';
//					$this->_GP['flat'] = $this->piVars['flat'];
//					$this->_GP['year'] = $this->piVars['year'];
		  			$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
    			}

				case 'utilisation': {
					$this->_GP['action'] = 'showUtilisation';
//					$this->_GP['flat'] = $this->piVars['flat'];
//					$this->_GP['year'] = $this->piVars['year'];
		  			$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
    			}
				case 'availabilityCheck': {
					if ($this->_GP['action'] != 'availabilityCheck' && $this->_GP['action'] != 'checkAvailability') $this->_GP['action'] = 'availabilityCheck';
		  			$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
    			}
				case 'listView': {
					if (!isset($this->_GP['action'])) $this->_GP['action'] = 'listView';
		  			$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
    			}

				case 'simpleListView': {
					$this->_GP['action'] = 'simpleListView';
		  			$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
    			}

				/* 20150401 infoworxx Start - new Feature - Randomlist */
				case 'randomListView': {
					if (!isset($this->_GP['action'])) $this->_GP['action'] = 'randomListView';
		  			$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
    			}
				/* 20150401 infoworxx End */

				case 'administration': {
					$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
				}
				case 'monthlyOverview': {
					if ($this->_GP['action'] != 'monthlyOverview') $this->_GP['action'] = 'monthlyOverview';

					$controller->setGPVars($this->_GP);
					$controller->setConfiguration($this->conf);
                    $controller->setLocalLang($this->LOCAL_LANG);
					$content = $controller->handle();
					return $this->pi_wrapInBaseClass($content);
					break;
				}
				default:
                    break;
	        }
        }



        /**
		 *  safe sql-funcions
		 *
		 */
		function sqlSafeString($param, $table) {
			return $GLOBALS['TYPO3_DB']->fullQuoteStr($param, $table, true);
		}

  		function sqlSafeInt($param) {
    		return (NULL === $param ? "NULL" : intVal ($param));
  		}

        function readExtConf() {
			// get the configuration from typoscript.
			// if there are no typoscript-values get the values from flexform
			$this->pi_initPIflexForm();
	   		if (empty($this->conf['cssFile'])) $this->conf['cssFile'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'cssFile','sheetTemplateOptions');
	   		if (empty($this->conf['templateFile'])) $this->conf['templateFile'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'templateFile','sheetTemplateOptions');


			if (empty($this->conf['displayMode'])) $this->conf['displayMode'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayMode','sheetDisplayMode');
			if (empty($this->conf['resultPageId'])) $this->conf['resultPageId'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'resultPageId','sheetListView');
			if (empty($this->conf['flatListCategories'])) $this->conf['flatListCategories'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'flatListCategories','sheetListView');
			if (empty($this->conf['galleryColumns'])) $this->conf['galleryColumns'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'galleryColumns','sheetListView');
			if (empty($this->conf['showMap'])) $this->conf['showMap'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showMap','sheetListView');
			if (empty($this->conf['showCalendar'])) $this->conf['showCalendar'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showCalendar','sheetListView');
			if (empty($this->conf['listViewDisplayMode'])) $this->conf['listViewDisplayMode'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayMode','sheetListView');
			if (empty($this->conf['markWeekends'])) $this->conf['markWeekends'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'markWeekends','sheetMultiRow');
			if (empty($this->conf['calendarColumns'])) $this->conf['calendarColumns'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'calendarColumns','sheetMultiRow');
			if (empty($this->conf['yearsBeforeActualYear'])) $this->conf['yearsBeforeActualYear'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'yearsBeforeActualYear','sheetMultiRow');
			if (empty($this->conf['yearsAfterActualYear'])) $this->conf['yearsAfterActualYear'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'yearsAfterActualYear','sheetMultiRow');
			if (empty($this->conf['showOldBookingData'])) $this->conf['showOldBookingData'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showOldBookingData','sheetMultiRow');
            if (empty($this->conf['showOverbookedLegend']))$this->conf['showOverbookedLegend'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showOverbookedLegend','sheetMultiRow');
            if (empty($this->conf['showOnRequestLegend']))$this->conf['showOnRequestLegend'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showOnRequestLegend','sheetMultiRow');
            if (empty($this->conf['showDaysShortcuts'])) $this->conf['showDaysShortcuts'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showDaysShortcuts','sheetMultiRow');
            if (empty($this->conf['showExtendedTooltips'])) $this->conf['showExtendedTooltips'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showExtendedTooltips','sheetMultiRow');
            if (empty($this->conf['calendarPageId'])) $this->conf['calendarPageId'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'calendarPageId','sheetAvailabilityCheck');
            if (empty($this->conf['enableContigentSearch'])) $this->conf['enableContigentSearch'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'enableContigentSearch','sheetAvailabilityCheck');
            if (empty($this->conf['showNextBookingPossibility'])) $this->conf['showNextBookingPossibility'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showNextBookingPossibility','sheetAvailabilityCheck');
            if (empty($this->conf['foreignURL'])) $this->conf['foreignURL'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'foreignURL','sheetAvailabilityCheck');
            if (empty($this->conf['bookingRequestPageId'])) $this->conf['bookingRequestPageId'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'bookingRequestPageId','sheetAvailabilityCheck');
            if (empty($this->conf['bookingPageId'])) $this->conf['bookingPageId'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'bookingPageId','sheetBookingOptions');

            if (empty($this->conf['showBookedBy'])) $this->conf['showBookedBy'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showBookedBy','sheetMultiRow');
			/* 20150401 infoworxx Start */
    		/* - new Feature - interne Links in monthly view */
	        if (empty($this->conf['internalLinksMonthlyView']))$this->conf['internalLinksMonthlyView'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'internalLinksMonthlyView','sheetMultiRow');
	        if (empty($this->conf['internalLinksMonthlyViewTarget']))$this->conf['internalLinksMonthlyViewTarget'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'internalLinksMonthlyViewTarget','sheetMultiRow');
			/* 20150401 infoworxx End */
            if (empty($this->conf['showWarnings'])) $this->conf['showWarnings'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'showWarnings','sheetDisplayMode');
            if (empty($this->conf['useXAJAX'])) $this->conf['useXAJAX'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'useXAJAX','sheetAvailabilityCheck');
            if (empty($this->conf['startOfWeek'])) $this->conf['startOfWeek'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'startOfWeek','sheetMultiRow');
			if (empty($this->conf['displayAdditionalFields'])) $this->conf['displayAdditionalFields'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayAdditionalFields','sheetBookingOptions');
			if (empty($this->conf['displayAdditionalFieldsWithXAJAX'])) $this->conf['displayAdditionalFieldsWithXAJAX'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'displayAdditionalFieldsWithXAJAX','sheetBookingOptions');
			if (empty($this->conf['useTtaddresses'])) $this->conf['useTtaddresses'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'useTtaddresses','sheetBookingOptions');
			if (empty($this->conf['ttaddressGroup'])) $this->conf['ttaddressGroup'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'ttaddressGroup','sheetBookingOptions');
			if (empty($this->conf['enableFeUser'])) $this->conf['enableFeUser'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'enableFeUser','sheetBookingOptions');

			if (empty($this->conf['mapWidth'])) $this->conf['mapWidth'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mapWidth','sheetListView');
			if (empty($this->conf['mapHeight'])) $this->conf['mapHeight'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'mapHeight','sheetListView');
			if (empty($this->conf['defaultMapType'])) $this->conf['defaultMapType'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'defaultMapType','sheetListView');
			if (empty($this->conf['defaultZoomLevel'])) $this->conf['defaultZoomLevel'] = $this->pi_getFFvalue($this->cObj->data['pi_flexform'],'defaultZoomLevel','sheetListView');

			if ($this->conf['startOfWeek'] == NULL) $this->conf['startOfWeek'] = 'monday';

			// allowed chars for flatname, bookedBy and date string
			// using multiline option
			// attention: file was edited in utf-8 format to get special german chars working
			if ($GLOBALS['TSFE']->renderCharset == 'utf-8')
				$this->conf['allowedChars'] = '%^[Ã¡Ã Ã©Ã¨Ä„Ä…Ä†Ä‡Ä˜Ä™ÅÅ‚ÅƒÅ„Ã“Ã³ÅšÅ›Å¹ÅºA-z0-9* Ã„Ã–ÃœÃ¤Ã¶Ã¼ÃŸ@.,:;\%\[\]/&?=Ã˜()+\r\n-]*$%u';
//			$this->conf['allowedChars'] = '%(?m)^[A-z0-9 Ã„Ã–ÃœÃ¤Ã¶Ã¼ÃŸ@.,:;/&?=Ã˜()+-]*$%';
			else $this->conf['allowedChars'] = '%^[A-z0-9*@äöüÄÖÜß \%\[\].,:;/&?=()+\r\n-]*$%';
			$this->conf['allowedDateChars'] = '/^[0-3]*[0-9]\.[0-1][0-9]\.20[0-9][0-9]$/';
			// to read config from ext_conf_template.txt
            //$this->_extConfig = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['flatmgr']);
/*            $piFlexForm = $this->cObj->data['pi_flexform'];
       		if (!empty($piFlexForm))
				foreach ( $piFlexForm['data'] as $sheet => $data )
				 	foreach ( $data as $lang => $value )
		 				foreach ( $value as $key => $val ) {
							debug($key);
							//if ( empty($this->conf[$key])) $this->conf[$key] = $this->pi_getFFvalue($piFlexForm, $key, $sheet);
						}
*/
			// Get/Post Variables
			$this->_GP = t3lib_div::_GP('tx_flatmgr_pi1');
			// Template
			$template_file = !empty($this->conf['template'])  ? 'uploads/tx_flatmgr/'.$this->conf['template'] : $this->conf['templateFile'];
			$this->templateCode = $this->cObj->fileResource($template_file);
        	// Including CSS
			$cssFile = $this->getRelativeFileName($this->conf['cssFile']);
			$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1'] = '<link rel="stylesheet" type="text/css" href="'.$cssFile.'" />';
			return;
        }


	/*
	 *  Check the incoming data for valid characters
	 *  and returns errors
	 */
	function checkInputData(){
		while(list($k,$item) = @each($this->_GP)) {
			if (! is_array($item) && preg_match('/.*\\\\.*/',$item)) {
				$this->_GP[(string)$k] = (string) $item;
				if ($this->pi_getLL($k))
					$errors .= '<div>'.$this->pi_getLL('illegaleCharacters') . $this->pi_getLL($k) . '</div>';
				else $errors .= '<div>'.$this->pi_getLL('illegaleCharacters') . $k . '</div>';
			}
			if (! is_array($item) && ! preg_match($this->conf['allowedChars'],$item)) {
				$this->_GP[(string)$k] = (string) $item;
				if ($this->pi_getLL($k))
					$errors .= '<div>'.$this->pi_getLL('illegaleCharacters') . $this->pi_getLL($k) . '</div>';
				else $errors .= '<div>'.$this->pi_getLL('illegaleCharacters') . $k . '</div>';
			}
            if (is_array($item)) {
                while(list($j,$value) = (string)@each($item)) {
				$item[$j] = (string) $value;
                	if(! preg_match($this->conf['allowedChars'],$value))
						$errors .= '<div>'.$this->pi_getLL('illegaleCharacters') . $value . '</div>';
                }
            }

		}
        if (! preg_match('/^[0-9,]*$/', $this->_GP['flatUid']))
			$errors .= 'flat uid error';  // , is used for rebooking rooms!
		if ($this->_GP['year']) $this->_GP['year'] = (int) $this->_GP['year'];
		if ($errors != '') return '<div class="error">'.$errors.'</div>';
		else return '';
	}

	function getRelativeFileName($filename)	{
		if (substr($filename,0,4)=='EXT:')	{
			list($extKey,$local) = explode('/',substr($filename,4),2);
			$filename = '';
			if (strcmp($extKey,'') && t3lib_extMgm::isLoaded($extKey) && strcmp($local,''))	{
				return t3lib_extMgm::siteRelPath($extKey).$local;
			}
		} else return $filename;
	}

}

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_pi1.php'])        {
        include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_pi1.php']);
}

?>