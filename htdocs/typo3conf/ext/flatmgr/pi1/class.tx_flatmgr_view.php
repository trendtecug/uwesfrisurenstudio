<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2011 Joachim Ruhs <postmaster@joachim-ruhs.de>
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
require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');

class tx_flatmgr_view extends tslib_pibase implements t3lib_singleton {
    var $prefixId = 'tx_flatmgr_pi1';
    
	public function __construct($conf = array()) {
		parent::__construct($conf);
		$this->setConfiguration($conf);
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $this->cObj = t3lib_div::makeInstance('tslib_cObj');
        } else {
            $this->cObj = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tslib_cObj');
        }
        $this->templateCode = $this->cObj->fileResource($conf["templateFile"]);
        $this->pi_loadLL();
	}

       /* Set Configuration */
    function setConfiguration($conf) {
        $this->conf = $conf;
    }
    /* Set View Input */
    function setInput($input) {
        $this->input = $input;
    }

    /* Set Code */
    function setCode($code) {
        $this->code = $code;
    }

    function setGPVars($gp) {
        $this->_GP = $gp;
    }

       function setLocalLang(&$LOCAL_LANG) {
        $this->LOCAL_LANG = $LOCAL_LANG;
    }

    /* initial call */
    function display() {
        switch ($this->code) {
            case 'SHOWFLATS':
                return $this->showFlats();
                break;
            case 'SHOWUTILISATIONFLATS':
                return $this->showUtilisationFlats();
                break;
            case 'SHOWYEARS':
                return $this->showYears();
                break;
            case 'SHOWUTILISATIONYEARS':
                return $this->showUtilisationYears();
                break;
            case 'SHOWCALENDAR':
                return $this->showCalendar();
                break;
            case 'SHOWAVAILABILITYMASK':
                return $this->showAvailabilityMask();
                break;
            case 'CHECKAVAILABILITY':
                return $this->checkAvailability();
                break;
            case 'SHOWAVAILABILITY':
                return $this->showAvailability();
                break;
            case 'SHOWNEXTAVAILABILITY':
                return $this->getNextVacantPeriod();
                break;
            case 'SHOWUTILISATION':
                return $this->showUtilisation();
                break;
            case 'LISTVIEW':
                return $this->showList();
                break;
            case 'SIMPLELISTVIEW':
                return $this->showSimpleList();
                break;
            case 'SINGLEVIEW':
                return $this->showSingle();
                break;
            default:
                //return $this->displayDefault();
        }
        
    }

    function showSimpleList() {
        $template = $this->cObj->getSubpart($this->templateCode, '###FLATLISTSIMPLEHEADER_TEMPLATE###');
        $marks['###CATEGORYV###'] = $this->pi_getLL($this->input['category']);
        $out .= $this->cObj->substituteMarkerArray($template, $marks);



        $template = $this->cObj->getSubpart($this->templateCode, '###FLATLISTSIMPLE_TEMPLATE###');
//        $subpart = $this->cObj->getSubpart($template, "###FLATLIST###");
        $marks = array();
        for ($i = 0; $i < count($this->input['uid']); $i++) {
            foreach ($this->input['fields'] as $key => $val) {
                $marks['###' . strtoupper($key) . 'V###'] = $this->input[$key][$i];
            }

            // detaillink
            $urlParams = array (
                'tx_flatmgr_pi1[flatUid]' => $this->input['uid'][$i],
                'tx_flatmgr_pi1[action]' => 'singleView',
            );

            if ($this->_GP['flatUid'] == $this->input['uid'][$i]) {
                if ($this->conf['resultPageId']) {
                    $marks['###DETAILLINKV###'] = $this->pi_linkToPage('<span class="actual">' . $this->input['name'][$i] . '</span>', $this->conf['resultPageId'], '_top', $urlParams);
                } else {
                    $marks['###DETAILLINKV###'] = $this->pi_linkToPage('<span class="actual">' . $this->input['name'][$i] , '</span>', $GLOBALS['TSFE']->id, '_top', $urlParams);
                }
            } else {
                if ($this->conf['resultPageId']) {
                    $marks['###DETAILLINKV###'] = $this->pi_linkToPage('<span class="normal">' . $this->input['name'][$i] . '</span>', $this->conf['resultPageId'], '_top', $urlParams);
                 } else {
                    $marks['###DETAILLINKV###'] = $this->pi_linkToPage('<span class="normal">' . $this->input['name'][$i] . '</span>', $GLOBALS['TSFE']->id, '_top', $urlParams);
                }
            }
            $out .= $this->cObj->substituteMarkerArray($template, $marks);
        }
        return $out;

    }

    function showList() {
        $template = $this->cObj->getSubpart($this->templateCode, '###FLATLISTHEADER_TEMPLATE###');
        $marks['###CATEGORYV###'] = $this->pi_getLL($this->input['category']);

		// add hook for processing of extra item markers
		$marksArray = $marks; // needed if no hook is used!!
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flatmgr']['extraListHeaderMarkerHook'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flatmgr']['extraListHeaderMarkerHook'] as $_classRef) {
				$_procObj = & t3lib_div :: getUserObj($_classRef);
				$marksArray = $_procObj->extraListHeaderMarkerProcessor($marksArray, $row, $this->conf, $this);
			}
		}
		$marks = $marksArray;

        $out .= $this->cObj->substituteMarkerArray($template, $marks);

        $outSpecials = $out;
        $template = $this->cObj->getSubpart($this->templateCode, '###FLATLIST_TEMPLATE###');
//        $subpart = $this->cObj->getSubpart($template, "###FLATLIST###");
        $marks = array();
        for ($i = 0; $i < count($this->input['uid']); $i++) {
            foreach ($this->input['fields'] as $key => $val) {
                $marks['###' . strtoupper($key) . 'V###'] = $this->input[$key][$i];
            }

			$marks['###BOOKINGMINDAYS###'] = $this->pi_getLL('bookingMinDays');
			$marks['###BOOKINGMINDAYSWHITSUN###'] = $this->pi_getLL('bookingMinDaysWhitsun');
			$marks['###BOOKINGMINDAYSSUMMER###'] = $this->pi_getLL('bookingMinDaysSummer');



            $marks['###NAME###'] = $this->pi_getll('name');
            $marks['###LIVINGSPACEV###'] .= $this->pi_getLL('areaUnit');
            $marks['###LIVINGSPACE###'] = $this->pi_getLL('livingSpace');
            if($this->input['landarea'][$i] > 0) {
                 $marks['###LANDAREAV###'] .= $this->pi_getLL('areaUnit');
                $marks['###LANDAREA###'] = $this->pi_getLL('landArea');
            }
            else {
                $marks['###LANDAREA###'] = '';
                $marks['###LANDAREAV###'] = '';
            }

            $marks['###CAPACITYTEXT###'] = $this->pi_getll('capacityText');
            $marks['###MINPRICE###'] = $this->pi_getll('minPrice');
            $marks['###MINPRICEUNIT###'] = $this->pi_getll('minPriceUnit');

            // detaillink
            $urlParams = array (
                'tx_flatmgr_pi1[flatUid]' => $this->input['uid'][$i],
                'tx_flatmgr_pi1[action]' => 'singleView',
            );

            $this->conf['list.']['image.']['file'] = $this->input['pic'][$i];
            $marks['###PICV###'] = $this->cObj->IMAGE($this->conf['list.']['image.']);

            if ($this->conf['resultPageId']) {
                if ($this->input['pic'][$i]) $marks['###PICV###'] = $this->pi_linkToPage($marks['###PICV###'],$this->conf['resultPageId'], '_top', $urlParams);
                $marks['###DETAILLINKV###'] = $this->pi_linkToPage($this->pi_getLL('flatDetails'),$this->conf['resultPageId'], '_top', $urlParams);
            }
            else {
                if ($this->input['pic'][$i]) $marks['###PICV###'] = $this->pi_linkToPage($marks['###PICV###'], $GLOBALS['TSFE']->id, '_top', $urlParams);
                $marks['###DETAILLINKV###'] = $this->pi_linkToPage($this->pi_getLL('flatDetails'), $GLOBALS['TSFE']->id, '_top', $urlParams);
            }

            // now the attributes
            $marks['###ATTRIBUTESV###'] = '';
			if (is_array($this->input['attributes'][$i])) {
				foreach ($this->input['attributes'][$i] as $key => $val) {
	                if ($key == 'name') {
	                    $titles = $val;
	                }
	                if ($key == 'icon') {
	                    for ($j = 0; $j < count($val); $j++) {
	                        $marks['###ATTRIBUTESV###'] .= '<img src="uploads/tx_flatmgr/' . $val[$j] .
	                            '" alt="'. $this->pi_getLL($titles[$j]) .'" title="'. $this->pi_getLL($titles[$j]) . '"/>';
	                    }
	                }
	            }
            }
            // special
            if ($this->input['special'][$i] > '') {
                $marks['###SPECIALDISPLAYV###'] = 'block';
                $marks['###SPECIALV###'] = nl2br($this->input['special'][$i]);
            }
            else $marks['###SPECIALDISPLAYV###'] = 'none';

            $out .= $this->cObj->substituteMarkerArray($template, $marks);
        }

        if (count($this->input['uid']) == 0 && $this->conf['showOnlySpecials']) {
            $out = $outSpecials . '<div class="noSpecials">' . $this->pi_getLL('noSpecials') . '</div>';
            $out .= '<div class="backToList">' . $this->pi_linkToPage($this->pi_getLL('backToList'),
                $this->conf['listPageUid']). '</div>';
        }
        return $out;
    }

    function showSingle() {
		$i = 0;
		if (!$this->conf['singleView.']['usejQuery']) {

           $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_showSingle'] =
            '<script type="text/javascript">
            /*<![CDATA[*/
            /*]]>*/
            </script>
            <link rel="stylesheet" href="typo3conf/ext/flatmgr/pi1/scripts/mediaboxAdvBlack.css" type="text/css" media="screen" />
            <script type="text/javascript" src="typo3conf/ext/flatmgr/pi1/scripts/mootools-1.4.1.js"></script>
            <script type="text/javascript" src="typo3conf/ext/flatmgr/pi1/scripts/mediaboxAdv-1.2.0.js"></script>';
		}
		if ($this->conf['singleView.']['usejQuery'] && $this->conf['singleView.']['includejQuery']) {
           $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_showSingle'] =
            '<script type="text/javascript">
            /*<![CDATA[*/
            /*]]>*/
            </script>
            <script type="text/javascript" src="typo3conf/ext/flatmgr/pi1/scripts/jquery-1.7.1.min.js"></script>
            <link rel="stylesheet" href="typo3conf/ext/flatmgr/pi1/scripts/slimbox2.css" type="text/css" media="screen" />
            <script type="text/javascript" src="typo3conf/ext/flatmgr/pi1/scripts/slimbox2.js"></script>';
		}


        $template = $this->cObj->getSubpart($this->templateCode, '###FLATSINGLE_TEMPLATE###');
        $marks = array();


        $marks['###MAPV###'] = $this->input['map'];
/*
        // to use this set listType in localconf.php to 1
          $tags = explode ('"', $GLOBALS['TSFE']->pSetup['bodyTag']);
        $GLOBALS['TSFE']->pSetup['bodyTag']='';
        $GLOBALS['TSFE']->JSeventFuncCalls['onload']['tx_flatmgr_pi1'] = 'load();'. $tags[1];
        $GLOBALS['TSFE']->JSeventFuncCalls['onunload']['tx_flatmgr_pi1'] = 'GUnload();';
*/

        foreach ($this->input['fields'] as $key => $val) {
            $marks['###' . strtoupper($key) . 'V###'] = $this->input[$key][0];
        }
        $marks['###PRICEPEAKSEASONV###'] = nl2br($this->input['pricepeakseason'][0]);
        $marks['###PRICELOWSEASONV###'] = nl2br($this->input['pricelowseason'][0]);
        $marks['###PRICESAVINGSEASONV###'] = nl2br($this->input['pricesavingseason'][0]);

        $marks['###NAME###'] = $this->pi_getll('name');
        $marks['###LIVINGSPACE###'] = $this->pi_getll('livingSpace');
        $marks['###LIVINGSPACEV###'] .= $this->pi_getll('areaUnit');
        if($this->input['landarea'][0] > 0) {
            $marks['###LANDAREA###'] = $this->pi_getll('landArea');
            $marks['###LANDAREAV###'] .= $this->pi_getll('areaUnit');
        }
        else {
            $marks['###LANDAREA###'] = '';
            $marks['###LANDAREAV###'] = '';
        }
        $marks['###MINPRICE###'] = $this->pi_getll('minPrice');
        $marks['###MINPRICEUNIT###'] = $this->pi_getll('minPriceUnit');

        $marks['###WEEKPRICES###'] = $this->pi_getll('weekPrices');
        $marks['###YEARV###'] = $this->_GP['year'];
        $marks['###PEAKSEASON###'] = $this->pi_getll('peakseason');
        $marks['###LOWSEASON###'] = $this->pi_getll('lowseason');
        $marks['###SAVINGSEASON###'] = $this->pi_getll('savingseason');

        $marks['###PEAKSEASONDATESV###'] = nl2br($this->input['seasonDates']['peakseason']);
        $marks['###LOWSEASONDATESV###'] = nl2br($this->input['seasonDates']['lowseason']);
        $marks['###SAVINGSEASONDATESV###'] = nl2br($this->input['seasonDates']['savingseason']);

        if ($this->input['video'][0] == '')
            $marks['###VIDEOCLASS###'] = 'hide';
        else $marks['###VIDEOCLASS###'] = '';

        if ($this->input['showMap'][0] == 0)
            $marks['###MAPCLASS###'] = 'hide';
        else $marks['###MAPCLASS###'] = '';
        if ($this->input['showCalendar'][0] == 0)
            $marks['###CALENDARCLASS###'] = 'hide';
        else $marks['###CALENDARCLASS###'] = '';

        if ($this->input['pricepeakseason'][0] == '')
            $marks['###PEAKSEASONCLASS###'] = 'hide';
        else $marks['###PEAKSEASONCLASS###'] = '';

        if ($this->input['pricelowseason'][0] == '')
            $marks['###LOWSEASONCLASS###'] = 'hide';
        else $marks['###LOWSEASONCLASS###'] = '';

        if ($this->input['pricesavingseason'][0] == '')
            $marks['###SAVINGSEASONCLASS###'] = 'hide';
        else $marks['###SAVINGSEASONCLASS###'] = '';



        $marks['###ARRIVALDAY###'] = $this->pi_getll('arrivalDay');
        if ($this->input['dayofarrival'][0] == 0)
            $marks['###ARRIVALDAYV###'] = $this->pi_getll('Fr');
        if ($this->input['dayofarrival'][0] == 1)
            $marks['###ARRIVALDAYV###'] = $this->pi_getll('Sa');
        if ($this->input['dayofarrival'][0] == 2)
            $marks['###ARRIVALDAYV###'] = $this->pi_getll('Su');

        $marks['###CAUTION###'] = $this->pi_getll('caution');
        $marks['###ADDITIONAL###'] = $this->pi_getll('additional');
        $marks['###ENDCLEANING###'] = $this->pi_getll('endCleaning');

        $marks['###SINGLEVIEWFOOTER###'] = $this->pi_getll('singleViewFooter');

        $marks['###CAPACITYTEXT###'] = $this->pi_getll('capacityText');

        // 20140527 infoworxx - Start
        // Assign URL with validation
        $url=""; $urlDisplay="";
		if ( $this->input['url'][0]!="" ) {
			$url=$this->input['url'][0];
			if (!preg_match("/^http:\/\/|^https:\/\//", $url)) $url="http://".$url;
			$urlDisplay=preg_replace("/^http:\/\/|^https:\/\//", "", $url);
			// validate URL
			if (filter_var($url, FILTER_VALIDATE_URL)===FALSE) {
				$url="";
			$urlDisplay="";
			}
		}
		if ($url!="") 	$marks['###URL###'] = '<a href="'. $url .'" target="_blank" title="&Ouml;ffnet die Seite in einem neuen Fenster" class="flat_ext_url">'. $urlDisplay .'</a>';
        else 			$marks['###URL###'] = '';        
        // End infoworxx


        // now the attributes
        $marks['###PETSALLOWED###'] = '';
        $marks['###ATTRIBUTESV###'] = '';
        if (is_array($this->input['attributes'][0])) {
            foreach ($this->input['attributes'][0] as $key => $val) {
                if ($key == 'name') {
                    $titles = $val;
                }
                if ($key == 'icon') {
                    for ($j = 0; $j < count($val); $j++) {
                        $marks['###ATTRIBUTESV###'] .= '<img src="uploads/tx_flatmgr/' . $val[$j] .
                            '" alt="'. $this->pi_getLL($titles[$j]) .'" title="'. $this->pi_getLL($titles[$j]) . '"/>';
                        if ($titles[$j] == 'PetsAllowed')
                            $marks['###PETSALLOWED###'] = $this->pi_getLL($titles[$j]);
                    }
                }
            }
        }
        // and at least the gallery
        if ($this->input['gallery'][0] > '') {
	        $openDir = @opendir($this->input['gallery'][0]);
	        $marks['###GALLERYV###'] = '<table class="galleryTable"><tr>';
	        $i = 0;
	        $files = array();
	        // --- infoworxx 2015-04-01
	        // filecheck added - important!
	        // perhaps configurable with Typoscript?
	        // so the extra check against verifyFilenameAgainstDenyPattern and '.' / '..' makes sense!
	        $allowedFileExtensions = array('jpg','jpeg','gif','png');
	        while($file = readdir($openDir)) {
	        	$fileinfo = pathinfo($file);
		        if (($file!='.') && ($file!='..') &&
		        	in_array(strtolower($fileinfo['extension']), $allowedFileExtensions) && 
		        	t3lib_div::verifyFilenameAgainstDenyPattern($file)) { // if current fileextension is allowed
		                $files[] = $file;
	            }
	        }
	        // --- END infoworxx
	        array_multisort($files, SORT_ASC);
	        for ($j = 0; $j < count($files); $j++) {
	                if ($j % $this->conf['galleryColumns'] == 0) $marks['###GALLERYV###'] .= '</tr><tr>';
	                $this->conf['gallery.']['image.']['file'] = $this->input['gallery'][0] . $files[$j];
	                $marks['###GALLERYV###'] .= '<td><a href="' . $this->input['gallery'][0] . $files[$j] . '" rel="lightbox[lb1]">' .
	                    $this->cObj->IMAGE($this->conf['gallery.']['image.']) . '</a></td>';

	        }
	           $marks['###GALLERYV###'] .= '</tr></table>';
		}
        // special
        if ($this->input['special'][$i] > '') {
            $marks['###SPECIALDISPLAYV###'] = 'block';
            $marks['###SPECIALV###'] = nl2br($this->input['special'][$i]);
        }
        else $marks['###SPECIALDISPLAYV###'] = 'none';

		$marks['###FLEXDATAV###'] = $this->input['flexdata'][$i];
		// add hook for processing of extra item markers
		$marksArray = $marks; // needed if no hook is used!!
		if (is_array($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flatmgr']['extraItemMarkerHook'])) {
			foreach ($GLOBALS['TYPO3_CONF_VARS']['EXTCONF']['flatmgr']['extraItemMarkerHook'] as $_classRef) {
				$_procObj = & t3lib_div :: getUserObj($_classRef);
				$marksArray = $_procObj->extraItemMarkerProcessor($marksArray, $row, $this->conf, $this);
			}
		}
		$marks = $marksArray;



        $out .= $this->cObj->substituteMarkerArray($template, $marks);
        return $out;
    }


    /* Returns
     *
     * @param
     * @return
     *
     */
    function showAvailabilityMask() {
        tx_rlmpdateselectlib::includeLib();
        $dateSelectorConf = array (
            'calConf.' => array (
                   'dateTimeFormat' => 'dd.mm.y',
                   'inputFieldDateTimeFormat' => '%d.%m.%Y'
            )
        );

/*
        $out = '<div class="availabilityInput"><div class="availabilityInputHeader">' . $this->pi_getLL('availability check') . '</div>' .
            '<form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).
            '?tx_flatmgr_pi1[action]=checkAvailability" method="post">'.
               '<input type="hidden" name="'.$this->prefixId.'[action]" value="checkAvailability">'.
            $this->pi_getLL('startOfBooking').'<input type="text" name="'.$this->prefixId.'[start]" id="'.$this->prefixId.'[start]" '.
            'value="'.$this->piVars['start'].'" size="10" />'.
            tx_rlmpdateselectlib::getInputButton ($this->prefixId.'[start]',$dateSelectorConf).
            $this->pi_getLL('endOfBooking').
               '<input type="text" name="'.$this->prefixId.'[end]" id="'.$this->prefixId.'[end]" '.
            'value="'.$this->piVars['end'].'" size="10" />'.
            tx_rlmpdateselectlib::getInputButton ($this->prefixId.'[end]',$dateSelectorConf).
               '<input type="submit" name="submit" value="Check" />'.
            '</form></div>';
*/


        // xajax starts here

           // Make the plugin not cachable
        //$this->pi_USER_INT_obj = 1;
        $GLOBALS['TSFE']->set_no_cache();

        // didn't work here - have to be set in ext_localconf.php
        //if (  $this->conf['useXAJAX'] )
        //    t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_flatmgr_pi1.php','_pi1','list_type',0);

        // Initialise the return variable
        $content = '';
        $sForm = '';
        $sFormResult = '';
           // Include xaJax
                 require_once (t3lib_extMgm::extPath('xajax') . 'class.tx_xajax.php');
                // Make the instance
	            if (version_compare(TYPO3_branch, '6.0', '<')) {
	                $this->xajax = t3lib_div::makeInstance('tx_xajax');
	            } else {
	                $this->xajax = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_xajax');
	            }
                // nothing to set, we send to the same URI
                # $this->xajax->setRequestURI('xxx');
                // Decode form vars from utf8 ???
                $this->xajax->decodeUTF8InputOn();
                // Encode of the response to utf-8 ???
                $this->xajax->setCharEncoding('utf-8');
                // To prevent conflicts, prepend the extension prefix
                $this->xajax->setWrapperPrefix($this->prefixId);
                // Do you wnat messages in the status bar?
                $this->xajax->statusMessagesOn();
                // Turn only on during testing
                //$this->xajax->debugOn();
                // Register the names of the PHP functions you want to be able to call through xajax
                // $xajax->registerFunction(array('functionNameInJavascript', &$object, 'methodName'));
                $this->xajax->registerFunction(array('processFormData', &$this, 'processFormData'));
                // If this is an xajax request, call our registered function, send output and exit
                $this->xajax->processRequests();
                // Else create javascript and add it to the header output
                $GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'xajax'] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
                // The form goes here
                $content .= $this->sGetForm();

                // The result box goes here
                if (!t3lib_div::_GP('xajax')) {
                    // We make an empty result box on the first call to send our xajax responses to
                    $content .= '<div id="formResult"></div>';
                } else {
                    // This fallback will only be used if JavaScript doesn't work
                    // Responses of xajax exit before this
                    $content .= sprintf(
                        '<div id="formResult">%s</div>',
                        $this->sGetFormResult()
                    );
                } // if (!t3lib_div::_GP('xajax'))


        // xajax ends here
//debug ($GLOBALS['TSFE']->additionalHeaderData);
        return $content;
    }
    /**************************************************************************
     * Your registerd xajax response functions go here
     **************************************************************************/

    function processFormData($data) {
        // We put our incomming data to the regular piVars
        $this->piVars = $data[$this->prefixId];

        //and proceed as a normal controller ....

        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_model');
        }
        $model->setConfiguration($this->conf);

        $this->_GP['flatCategoryUid'] = intval($this->piVars['flatCategoryUid']);
        $this->_GP['flatCapacity'] = intval($this->piVars['flatCapacity']);
        $model->setGPVars($this->_GP);
        $flatsArray = $model->getFlats();

        $flats = $flatsArray['name'];
        $flatUids = $flatsArray['uid'];
        $urls  = $flatsArray['url'];
        $emails  = $flatsArray['email'];
        // set default flat if none selected
//        if (count($flats) == 1 || $this->_GP['flat'] == '' ) $this->_GP['flat'] = $flats[0];
        //$years = $model->getYearsOfAllFlats();
        $years = $model->getYears();
        $this->_GP['years'] = $years;
        $this->_GP['start'] = $this->piVars['start'];
        $this->_GP['end'] = $this->piVars['end'];

        $this->_GP['flatCapacity'] = $this->piVars['flatCapacity'];

        $content .= $this->getAvailableFlatsLegend();
        /*
        '<table class="availableFlatsLegend"><tr>';
        $content .= '<td class="availableFlat">&nbsp;</td><td class="legendText">'.$this->pi_getLL('availableFlat').'</td>';
           if ( $this->conf['showNextBookingPossibility']) {
            $content .= '<td class="nextVacantPeriod">&nbsp;</td><td class="legendText">'.$this->pi_getLL('nextVacantPeriod').'</td>';
        }
        if ($this->conf['foreignURL'] != '') {
            $content .= '<td class="availableForeignFlat">&nbsp;</td><td class="legendText">'.$this->pi_getLL('availableForeignFlat').'</td>';
        }
           if ( $this->conf['showNextBookingPossibility']) {
            $content .= '<td class="nextForeignVacantPeriod">&nbsp;</td><td class="legendText">'.$this->pi_getLL('nextForeignVacantPeriod').'</td>';
        }
        $content .= '</tr></table>';
        */

        $content .= '<div class="availableFlatsHeader">'.$this->pi_getLL('availableFlatsHeader');
        $content .= '&nbsp;'.$this->pi_getLL('availableFrom') .'&nbsp;'. $this->piVars['start'] .'&nbsp;'.
             $this->pi_getLL('availableTo').'&nbsp;';
        $content .= $this->piVars['end'] . '</div>';
          $content .= '<table class="availabilityTable">';

        while ( list($id, $flat) = each($flats)) {
            $this->_GP['flat'] = $flat;
            $this->_GP['url'] = $urls[$id];
            $this->_GP['flatUid'] = $flatUids[$id];
            $this->_GP['email'] = $emails[$id];
            $rooms = $model->getRooms($this->_GP['flatUid']);
            $this->_GP['availableFlatCapacity'] = 0;
            if (count($rooms['roomUid']) > 0) {
                for ($k = 0; $k < count($rooms['roomUid']); $k++) {
                    $this->_GP['roomUid'] = $rooms['roomUid'][$k];
                    $this->_GP['room'] = $rooms['name'][$k];
                    $this->_GP['firstRoom'] = 0;


                    $model->setGPvars($this->_GP);
                    $data['startDates'] = $model->getStartDates();
                    $data['endDates'] = $model->getAllEndDates();
                    $this->setInput($data);

                    $avail .= $this->checkAvailability();
                    if ( $avail == '' && $this->conf['showNextBookingPossibility']) {
                        $content .= $this->getNextVacantPeriod();
                    }
                    else {
                        $this->_GP['availableFlatCapacity'] += $rooms['capacity'][$k];
                    }
                }
            } // rooms
            if ($this->conf['enableContigentSearch'] && $this->_GP['availableFlatCapacity'] < $this->_GP['flatCapacity'])
                $avail = '';

            if ($avail) {
                if ($this->_GP['url'] < 'A')$target = '_top';
                else $target = '_blank';

                $linkedFlat = $this->pi_linkToPage($this->_GP['flat'], $this->_GP['url'], $target);
                $out  = '<tr class="availableFlat"><td>';

                $out .= $linkedFlat;

                $out .= '</td><td><select class="roomSelector" id="tx_flatmgr_pi1[roomUid]'. $this->_GP['flatUid'] .'">';
                $out .= $avail . '</select>';
                $urlParameter = array(
                    $this->prefixId.'[flat]' => $this->_GP['flat'] ,
                    $this->prefixId.'[flatUid]' => $this->_GP['flatUid'] ,
                //    $this->prefixId.'[roomUid]' => $this->_GP['roomUid'] ,
                    $this->prefixId.'[email]' => $this->_GP['email'] ,
                    $this->prefixId.'[start]' => $this->_GP['start'] ,
                    $this->prefixId.'[end]' => $this->_GP['end'] ,
                );
                $booking = '<span class="availableFlatLink">'. $this->pi_getLL('bookingCalendar'). '</span>';
                $calendarPageId = $this->conf['calendarPageId'];
                if( $calendarPageId < 1 )$calendarPageId = $GLOBALS['TSFE']->id;


                $out .= '</td><td>'.$this->_GP['start'].' - '.$this->_GP['end'] .'</td>';
                $out .= '<td class="bookingCalendarLink" onclick="jumpToUrl(\'/' . $this->pi_getPageLink($calendarPageId, '', $urlParameter) .'\',' .
                    $this->_GP['flatUid'] . ');"/>' . $booking . '</td>';

                if ($this->_GP['email'] && $this->conf['bookingRequestPageId']) {
                $bookingRequest = '<span class="bookingRequestLink">'. $this->pi_getLL('booking request'). '</span>';
                $out .= '<td class="bookingRequestLink"  onclick="jumpToUrl(\'/' . $this->pi_getPageLink($this->conf['bookingRequestPageId'], '', $urlParameter) .'\',' .
                    $this->_GP['flatUid'] . ');"/>' . $bookingRequest . '</td>';
                }
                else {
                    $out .= '<td></td>';
                }


                $out .= '</tr>';

                $content .= $out;
            }

            if (count($rooms['roomUid']) == 0) {
                    $this->_GP['roomUid'] = 0;
                    $this->_GP['room'] = '';
                    $this->_GP['firstRoom'] = -1;
                    $model->setGPvars($this->_GP);
                    $data['startDates'] = $model->getStartDates();
                    $data['endDates'] = $model->getAllEndDates();
                    $this->setInput($data);

                    $avail = $this->checkAvailability();
                    if ( $avail == '' && $this->conf['showNextBookingPossibility']) {
                        $content .= $this->getNextVacantPeriod();
                    }
                    else $content .= $avail;
            }
            $avail = '';


        }
        $content .= $this->checkExternalSites();
        $content .= '</table>';

        // We want to update the display for the part sGetFormResult
        //$content = $this->sGetFormResult();

        // Once having prepared the content we still need to send it to the browser ....

        // Instantiate the tx_xajax_response object
        $objResponse = new tx_xajax_response();


        // set the charset encoding
        if ($GLOBALS['TSFE']->localeCharset != NULL)
            $objResponse->setCharEncoding($GLOBALS['TSFE']->localeCharset); //
        else
            $objResponse->setCharEncoding('utf-8');


        // Add the content to or Result Box: #formResult
        $objResponse->addAssign("formResult", "innerHTML", $content);

        //return the XML response
        return $objResponse->getXML();

        // The $xajax->processRequests will send it and exit hereafter.

        // To learn about 17 kinds of tx_xajax_response()-functions
        // have a look at the tx_xajax_response.inc.php
    }

    /*********************************************************************************/

    function sGetForm()    {

        /**************************************************************************
         * Call the xajax function we have registerd
         **************************************************************************/

/*        $sReturn .= sprintf(
            '<form %s action="%s" method="POST" enctype="multipart/form-data" id="xajax_form">
                <fieldset>
                    <legend><strong>%s:</strong>&nbsp;</legend>
                    <label for="%s">%s</label>
                    <br />
                    <textarea id="%s" name="%s[%s]" rows="5" cols="30"></textarea>
                    <br />
                    <input type="hidden" name="no_cache" value="1" />
                    <input %s type="submit" name="%s[submit_button]" value="%s" />
                    <input type="reset" />
                </fieldset>
            </from>
            <br />',
            // Set the form here.
            $onSubmit,
            $this->pi_getPageLink(
            $GLOBALS['TSFE']->id,
                '',
                array('L' => $GLOBALS['TSFE']->config['config']['sys_language_uid'])
            ),
            $this->pi_getLL('testform', '', TRUE),
            'mytext',
            $this->pi_getLL('textarea_label', '', TRUE),
            'mytext',
            $this->prefixId,
            'mytext',
            // Set the submit button here.
            $onClick,
            $this->prefixId,
            $this->pi_getLL('submit_button', '', TRUE)
        );
  */
        $dateSelectorConf = array (
            'calConf.' => array (
                   'dateTimeFormat' => 'dd.mm.y',
                   'inputFieldDateTimeFormat' => '%d.%m.%Y'
            )
        );
        $action = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',
                array(
                    'L' => $GLOBALS['TSFE']->config['config']['sys_language_uid'],
                    'tx_flatmgr_pi1[action]' => 'checkAvailability'));

           $onSubmit = '';
        $onClick = '';

        if (  $this->conf['useXAJAX'] ){
            // Form: should not be send if xajax is on.
            // (If javascript is disabled it works the normal way.)
            $onSubmit = ' onsubmit="return false;" ';

            // Submit: should call xajax instead.
//            $onClick = ' onClick="' . $this->prefixId . 'processFormData(xajax.getFormValues(\'xajax_form\'))" ';
            $onClick = ' onClick="document.getElementById(\'formResult\').innerHTML = \'<img src=/typo3conf/ext/flatmgr/pi1/icons/working.gif />\';' . $this->prefixId . 'processFormData(xajax.getFormValues(\'xajax_form\'))" ';
        }
        $sReturn = '';
        $sReturn = '<div class="availabilityInput"><div class="availabilityInputHeader">' .
                    $this->pi_getLL('availabilityCheck') . '</div>';
        $sReturn .=  sprintf('<form %s action="%s" method="POST" enctype="multipart/form-data" id="xajax_form">', $onSubmit, $action);


        $sReturn .= '<div class="flatCategory">' . $this->pi_getLL('flatCategory') . '<select class="flatCategory" name="tx_flatmgr_pi1[flatCategoryUid]">';
        $sReturn .= '<option value="0">' . $this->pi_getLL('allFlatCategories') . '</option>';
        for ($i = 0; $i < count($this->input['categories']['uid']); $i++) {
            $sReturn .= '<option value="' . $this->input['categories']['uid'][$i] . '">' . $this->input['categories']['category'][$i] . '</option>';
        }
        $sReturn .= '</select>';

        // now the flat capacities
        if (!$this->conf['enableContigentSearch']) {
            $sReturn .= '<span class="flatCapacity">' . $this->pi_getLL('flatCapacity') . '<select class="flatCapacity" name="tx_flatmgr_pi1[flatCapacity]">';
            for ($i = 0; $i < count($this->input['capacities']['capacity']); $i++) {
                $sReturn .= '<option value="' . $this->input['capacities']['capacity'][$i] . '">' . $this->input['capacities']['capacity'][$i] . '</option>';
            }
            $sReturn .= '</select></span></div>';
        }
        else {
            $sReturn .= '<span class="flatCapacity">' .
                $this->pi_getLL('flatCapacity') . '<input value="1" size="3" maxlength="3" type="text" class="flatCapacity" name="tx_flatmgr_pi1[flatCapacity]"/>';
            $sReturn .= '</span></div>';

        }


        $sReturn .= '<input type="hidden" name="'.$this->prefixId.'[action]" value="checkAvailability">'.
                    '<input type="hidden" name="no_cache" value="1" />'.
                    $this->pi_getLL('startOfBooking').
                    '<input type="text" name="'.$this->prefixId.'[start]" id="'.$this->prefixId.'[start]" '.
                    'value="'.$this->piVars['start'].'" size="10" />'.
                    tx_rlmpdateselectlib::getInputButton ($this->prefixId.'[start]',$dateSelectorConf).
                    $this->pi_getLL('endOfBooking').
                       '<input type="text" name="'.$this->prefixId.'[end]" id="'.$this->prefixId.'[end]" '.
                    'value="'.$this->piVars['end'].'" size="10" />'.
                    tx_rlmpdateselectlib::getInputButton ($this->prefixId.'[end]',$dateSelectorConf);
        // now the flat categories

		// "onclick" nach hinten verlagert - der Ausgabestring wird in einer anderen Extension 
		// per XClass modifiziert - und wird erst durch diese Umstellung eindeutig identifizierbar
		// Danke ;-)
        $sReturn .=    '<input type="submit" name="submit" value="Check" '. $onClick . '/>'.
                    '</form></div>';

        return $sReturn;
    } // function sGetForm()

    function sGetFormResult()    {
        $out = '<strong>' . $this->pi_getLL('form_result', '', TRUE) . '</strong>';
        $out .= t3lib_utility_Debug::viewArray($this->piVars);
        return $out;
    } // function sGetFormResult()

    function getAvailableFlatsLegend() {
        $content = '<table class="availableFlatsLegend"><tr>';
        $content .= '<td class="availableFlat">&nbsp;</td><td class="legendText">'.$this->pi_getLL('availableFlat').'</td>';
           if ( $this->conf['showNextBookingPossibility']) {
            $content .= '<td class="nextVacantPeriod">&nbsp;</td><td class="legendText">'.$this->pi_getLL('nextVacantPeriod').'</td>';
        }
        if ($this->conf['foreignURL'] != '') {
            $content .= '<td class="availableForeignFlat">&nbsp;</td><td class="legendText">'.$this->pi_getLL('availableForeignFlat').'</td>';
        }
           if ( $this->conf['showNextBookingPossibility'] && $this->conf['foreignURL'] != '') {
            $content .= '<td class="nextForeignVacantPeriod">&nbsp;</td><td class="legendText">'.$this->pi_getLL('nextForeignVacantPeriod').'</td>';
        }
        $content .= '</tr></table>';
        return $content;
    }
    


    /* Returns
     *
     * @param
     * @return
     *
     */
    function checkExternalSites() {
        // query external typo3 sites; url's have to be set
        // in flexform
        $urls = explode(';',$this->conf['foreignURL']);

        // here starts snoopy
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $snoopy = t3lib_div::makeInstance('tx_flatmgr_snoopy');
        } else {
            $snoopy = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_snoopy');
        }
        $out = '';
        foreach($urls as $url) {
            $domain = parse_url($url);
            $submit_url = $url . "?tx_flatmgr_pi1[action]=checkAvailability";
            $submit_vars["tx_flatmgr_pi1[start]"] = $this->_GP['start'];
            $submit_vars["tx_flatmgr_pi1[end]"] = $this->_GP['end'];
            $submit_vars["tx_flatmgr_pi1[action]"] = "checkAvailability";

            $snoopy->submit($submit_url,$submit_vars);
            $snoopyOut = $snoopy->results;

            // get foreign available flats
            $out_arr = $this->getStrsBetween($snoopyOut, '<tr class="availableFlat"><td>','</td></tr>');
            // here ends snoopy

            while (list($key, $value) = each($out_arr)) {
                $key1 = str_replace('href="', 'href="'.$domain['scheme'].'://'.$domain['host'].'/',$key);
                $key2 = str_replace('target="_top"', 'target="_blank"',$key1);
                $out .= '<tr class="availableForeignFlat"><td><div>' . $key2 .'</div></td></tr>';
            }

            // get foreign next available periods
            if ( $this->conf['showNextBookingPossibility']) {
                $out_arr = $this->getStrsBetween($snoopyOut, '<tr class="nextVacantPeriod"><td>','</td></tr>');
                while (list($key, $value) = each($out_arr)) {
                    $key1 = str_replace('href="', 'href="'.$domain['scheme'].'://'.$domain['host'].'/',$key);
                    $key2 = str_replace('target="_top"', 'target="_blank"',$key1);
                    $out .= '<tr class="nextForeignVacantPeriod"><td>' . $key2 .'</td></tr>';
                }
            }
        }
        return $out;
    }

        

    /* Returns
     *
     * @param
     * @return
     *
     */
    function showFlats() {
        $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_flatlist'] =
            '<script type="text/javascript">
            /*<![CDATA[*/
            function jumpToUrl(URL, uid)    {    //
                if (document.getElementById("tx_flatmgr_pi1[roomUid]" + uid)) {
                    var roomUid = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).value;
                }
                window.location.href = URL + "&tx_flatmgr_pi1[roomUid]=" + roomUid;
                return false;
            }/*]]>*/

            </script>';


        $i = 0;
        if (! is_array($this->input['name'])) {
            return $this->showError('insertFlat');
        }
//debug($this->input);
        $out= '<table class="listFlat">';
        while ( list ($id, $flat) = each($this->input['name'])) {

//debug(count($this->input['rooms'][$id]['roomUid']));

            if ($this->input['rooms'][$id]['name'][0] > '') {
                // rooms
                for ($j = 0; $j < count($this->input['rooms'][$id]['roomUid']); $j++) {
                    $flatUid = $this->input['uid'][$id];
                    $roomUid = $this->input['rooms'][$id]['roomUid'][$j];
                    if ($j == 0) $roomSelector = '<div class="roomSelector"><select id="tx_flatmgr_pi1[roomUid]' . $flatUid . '">';
                    $roomSelector .= '<option value="'. $this->input['rooms'][$id]['roomUid'][$j] . '">' . $this->input['rooms'][$id]['name'][$j] . '</option>';
                }
                $roomSelector .= '</div></select>';

                $arr[$this->prefixId.'[flat]'] = $flat;
                $arr[$this->prefixId.'[flatUid]'] = $flatUid;
                $book = $this->pi_getLL('bookingCalendar');
                $link = $this->cObj->getTypoLink_URL($this->input['url'][$id]);
                $details = $this->pi_linkToPage($this->pi_getLL('details'), $link);

                $flat = $this->pi_linkToPage($flat, $link);
                $bookingLink = $this->pi_getPageLink($GLOBALS['TSFE']->id,'',$arr);
                $booking = '<span class="bookingLink" onclick="jumpToUrl(\'' . $bookingLink . '\','. $flatUid. ')">' .
                $book . '</span>';
                $booked = '<tr><td class="flatName">' . $flat . ' ' . $roomSelector . '</td><td>' . $booking . '</td>';
                $booked .= ($this->input['url'][$id] > '') ? '<td class="flatDetails">' . $details . '</td></tr>': '</tr>';
                $out .= $booked;
                $i++;
            }
            else {
                $arr = array();
                $flatUid = $this->input['uid'][$id];
                $arr[$this->prefixId.'[flat]'] = $flat;
                $arr[$this->prefixId.'[flatUid]'] = $flatUid;
                $booking = $this->pi_getLL('bookingCalendar');
                $link = $this->cObj->getTypoLink_URL($this->input['url'][$id]);
                $details = $this->pi_linkToPage($this->pi_getLL('details'), $link);

                $flat = $this->pi_linkToPage($flat, $link);
                //$booking = $flat;
                //$booking = $this->pi_linkTP($booking,$arr,1,0);
                $booking = ($flatUid == $this->_GP['flatUid'] || ($this->_GP['flat'] == '' ))?'': $this->pi_linkTP($booking,$arr,1,0);
                $booked = '<tr><td class="flatName">' . $flat . '</td><td>' . $booking . '</td>';
                $booked .= ($this->input['url'][$id] > '') ? '<td class="flatDetails">' . $details . '</td></tr>': '</tr>';
                $out .= $booked;
                $i++;
            }
            }
            $out = $out . '</table>';
            // returns no flatlist if there is only one flat
            if ( $i == 1) return '';
            return $out;
    }
    /* Returns
     *
     * @param
     * @return
     *
     */
    function showUtilisationFlats() {
        $i = 0;
        if (count($this->input['name']) > 1) {
            $out= '<table class="listFlat">';
            while ( list ($id, $flat) = each($this->input['name'])) {
                $flatUid = $this->input['uid'][$id];
                $arr[$this->prefixId.'[flat]'] = $flat;
                $booking = $this->pi_getLL('utilisation calendar');

                //$booking = $flat;
                $booking = $this->pi_linkTP($booking,$arr,1,0);
                $booking = ($flat == $this->_GP['flat'] || ($this->_GP['flat'] =='' ))?'': $this->pi_linkTP($booking,$arr,1,0);

                $booked = '<tr><td class="flatName">' . $flat . '</td><td>' . $booking . '</td></tr>';
                $i++;
                $out .= $booked;
            }
            if ( $i != 1) {
                   $arr[$this->prefixId.'[flat]'] = 'all';
                $booking = $this->pi_getLL('utilisation calendar');
                $booking = $booking = ('all' == $this->_GP['flat'])?'':$this->pi_linkTP($booking,$arr,1,0);
                $booked = '<tr><td class="flatName">' . $this->pi_getLL('allFlats') . '</td><td>' . $booking . '</td></tr>';
                $out .= $booked;
            }
            $out .= '</table>';
        }
        return $out;
    }
    /* Returns a list of available years
     *
     * @return     String        Generated HTML Code
     */
    function showYears() {
             if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
            // hacker saving
/*
            if (isset($this->_GP['flat']))
                  if (! preg_match($this->conf['allowedChars'],$this->_GP['flat'])) return $this->showError('flat input error');
*/
            if (isset($this->_GP['year']))
                if (! preg_match('/^2[0-9]{3}$/',$this->_GP['year'])) return $this->showError('year input error');

            $out = '<div class="listYearTitle">' . $this->pi_getLL('bookingCalendar') . " " . $this->_GP['flat'] .' ' . $this->_GP['room'] . '</div>';
            $out .= '<table class="calendarLegend"><tr><td class="vacantDay">&nbsp;</td><td class="legend">';
            $out .= $this->pi_getLL('vacantDay') .'</td>';
            $out .= ($this->conf['markWeekends'])? '<td class="vacantWeekend">&nbsp;</td><td class="legend">'.$this->pi_getLL('vacant weekend').'</td>': '';
            $out .= '<td class="bookedDay">&nbsp;</td><td class="legend">';
            $out .= $this->pi_getLL('bookedDay').' </td>';
            $out .= ($this->conf['markWeekends'])? '<td class="bookedWeekend">&nbsp;</td><td class="legend bookedWeekendLegend">'.$this->pi_getLL('booked weekend').'</td>': '';
            $out .= ($this->conf['showOverbookedLegend'])?'<td class="overbookedDay">&nbsp;</td><td class="legend">'.$this->pi_getLL('overbooked').'</td>':'';
            $out .=  ($this->conf['showOnRequestLegend'])?'<td class="onRequestDay">&nbsp;</td><td class="legend">'.$this->pi_getLL('onRequest').'</td>':'';
            $out .= '</tr></table>';
            $arr = array ($this->prefixId.'[mode]' => 'listYear',
                          $this->prefixId.'[action]' => $this->_GP['action'],
                          $this->prefixId.'[flat]' => $this->_GP['flat'],
                          $this->prefixId.'[flatUid]' => intval($this->_GP['flatUid']),
                          $this->prefixId.'[roomUid]' => intval($this->_GP['roomUid']),
                          $this->prefixId.'[year]' => '0');

            $years = array();
            $x = 0;

            if ($this->conf['showOldBookingData']) {
                for ($y = $this->conf['yearsBeforeActualYear']; $y > 0; $y--) {
                    $years[$x] = date('Y') - $y;
                    $x++;
                }
            }

            if (($this->input['showFutureYearsInCalendar'][0] && $this->_GP['action'] == 'singleView') || !$this->input['showFutureYearsInCalendar'][0]) {
                for ($y = 0; $y <= $this->conf['yearsAfterActualYear']; $y++) {
                    $years[$x] = date('Y') + $y;
                    $x++;
                }
            } else $years[$x] = date('Y') + $y;

            $out .= '<table class="theYears"><tr>';
            while (list ($id, $y) = each($years)) {
                $arr[$this->prefixId.'[year]'] = $y;
                if ( $y == $this->_GP['year'] )
                    $out .= '<td class="selected">' . $y . '</td>';
                else $out .= '<td class="year' . $id . '">' . $this->pi_linkTP($y,$arr,1,0) . '</td>';
            }
            $out .= '</tr></table>';
            $out .= $year;
            return $out;
    }

    function showUtilisationYears() {

            if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
            // hacker saving
//            if (isset($this->_GP['flat']))
//                  if (! preg_match($this->conf['allowedChars'],$this->_GP['flat'])) return $this->showError('flat input error');
            if (isset($this->_GP['year']))
                if (! preg_match('/^2[0-9]{3}$/',$this->_GP['year'])) return $this->showError('year input error');

            if ( $this->_GP['flat'] == 'all' )
                $out = '<div class="listYearTitle">' . $this->pi_getLL('utilisation calendar') . " " . $this->pi_getLL('allFlats'). '</div>';
            else
                $out = '<div class="listYearTitle">' . $this->pi_getLL('utilisation calendar') . " " . $this->_GP['flat']. '</div>';
            $out .= '<table class="utilisationLegend"><tr>';
            $out .= '<td><div class="util0">&nbsp;</div>'.'0%' .'</td>';
            $out .= '<td><div class="util24">&nbsp;</div>'.'1-24%' .'</td>';
            $out .= '<td><div class="util25">&nbsp;</div>'.'25%' .'</td>';
            $out .= '<td><div class="util49">&nbsp;</div>'.'26%-49%' .'</td>';
            $out .= '<td><div class="util50">&nbsp;</div>'.'50%' .'</td>';
            $out .= '<td><div class="util74">&nbsp;</div>'.'51%-74%' .'</td>';
            $out .= '<td><div class="util75">&nbsp;</div>'.'75%' .'</td>';
            $out .= '<td><div class="util99">&nbsp;</div>'.'76%-99%' .'</td>';
            $out .= '<td><div class="util100">&nbsp;</div>'.'100%' .'</td>';

            
/*            $out .= ($this->conf['markWeekends'])? '<td class="vacantWeekend">&nbsp;</td><td class="legend">'.$this->pi_getLL('vacant weekend').'</td>': '';
            $out .= '<td class="bookedDay">&nbsp;</td><td class="legend">';
            $out .= $this->pi_getLL('bookedDay').' </td>';
            $out .= ($this->conf['markWeekends'])? '<td class="bookedWeekend">&nbsp;</td><td class="legend">'.$this->pi_getLL('booked weekend').'</td>': '';
            $out .= ($this->conf['showOverbookedLegend'])?'<td class="overbookedDay">&nbsp;</td><td class="legend">'.$this->pi_getLL('overbooked').'</td>':'';
*/

            $out .= '</tr></table>';

            $arr = array ($this->prefixId.'[mode]' => 'listYear',
                          $this->prefixId.'[flat]' => $this->_GP['flat'],
                          $this->prefixId.'[year]' => '0');
            $out .= '<table class="theYears"><tr>';
            while ($this->input != NULL && list ($id, $y) = each($this->input)) {
                $arr[$this->prefixId.'[year]'] = $y;
                if ( $y == $this->_GP['year'] )
                    $out .= '<td class="selected">' . $y . '</td>';
                else $out .= '<td>' . $this->pi_linkTP($y,$arr,1,0) . '</td>';
            }
            $out .= '</tr></table>';
            $out .= $year;
            return $out;
    }

    function checkAvailability() {
        $lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $startDates = array();
        $endDates = array();
        $years = array();
//        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start'])) return $this->showError('invalid start date');
//        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end'])) return $this->showError('invalid end date');
/*
        if (strlen($this->_GP['start']) != 10 ||strlen($this->_GP['end']) != 10) {
            return $this->showError('invalid date');
        }
*/
        list($d, $m, $y) = explode('.', $this->_GP['start']);
        if ( (int) $d == 0 || (int) $d > 31 || (int) $m == 0 || (int) $m > 12 ||
              (int) $y < 2000 || (int) $y > 2038) return $this->showError('invalid start date');
        list($d, $m, $y) = explode('.', $this->_GP['end']);
        if ( (int) $d == 0 || (int) $d > 31 || (int) $m == 0 || (int) $m > 12 ||
              (int) $y < 2000 || (int) $y > 2038) return $this->showError('invalid start date');

        
        
        // check startdate < enddate
        list($d, $m, $y) =    explode(".", $this->_GP['start']);
        $d1 = mktime(0,0,0,$m,$d,$y);
        list($d, $m, $y) =    explode(".", $this->_GP['end']);
        // we have to decrease end with one day
        $endSave = $this->_GP['end'];
        $d2 = mktime(12,0,0,$m,$d,$y) - 86400; // 12 hour because otherwise on day booking starting today -> error
        $this->_GP['end'] = date('d.m.Y', $d2);
        if ( $d2 <= $d1) return $this->showError('invalid date');
        // to disable checks in the past
        //if ( $d1 < time()) return $this->showError('startdate in the past');
        $theYear = substr($this->_GP['start'],-4);
        $years = $this->_GP['years'];

        $startDates = $this->input['startDates'];
        $endDates = $this->input['endDates'];

        // leap year calculating....
        if ( date("L", mktime(0,0,0,1,1,$theYear)) == 1) {
            $lengthOfMonth[2] = 29;
        }
        $booked = 0;
        $p = 0;   // index of startDates
        $q = 0;   // index of endDates
        $weekend = 0;
           $bookingEnd = 0;
        $bookingStart = 0;
        $available = 1;

        if ($years != NULL) {
            reset($years);
            // test if bookingStart occurs in years before
            while ( list(,$y) = each($years) && count($startDates) > 0) {
                while ( (list($key,) = each($startDates)) && $y < $theYear) {
                     if ( substr($startDates[$key],-4) != substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
                        $p++;
                    }
                     if ( substr($startDates[$key],-4) == substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
                        $p++;
                        $q++;
                    }
                }
            }
        }
        if ( $p - $q > 0) {
            $booked = 1;
            $q = $p - 1;
        }
        // we have also to iterate over the years if booking starts in one
        // year and booking end in next year
        for ($theYear; $theYear <= substr($this->_GP['end'],-4) && $available == 1; $theYear++) {
        for ($m=1; $m<13 && $available == 1;$m++) {
            // adding leading zero
            $mon = ($m < 10) ? '0'. $m : $m ;
            for ($d=1; $d <= $lengthOfMonth[$m] && $available == 1; $d++){
                // adding leading zero
                $day = ($d < 10) ? '0'. $d : $d ;

                // booking start
                if ( $startDates[$p] == $day. "." .$mon. ".".$theYear  ){
                    $booked++;
                    $p++;
                    $bookingStart = 1;
                    // if more than one start occurs
                    while ($startDates[$p] == $day. "." .$mon. ".".$theYear) {
                        $booked++;
                        $p++;
                    }
                }
                // compare as long until $this->_GP['end'] is reached
                if ($day. "." .$mon. ".".$theYear == $this->_GP['start']) $compareStart = 1;
                if ( $booked > 0 && $compareStart == 1 ) $available = 0;
                if ($day. "." .$mon. ".".$theYear == $this->_GP['end']) $compareStart = 0;

                // booking end
                if ( $endDates[$q] == $day. "." .$mon. ".".$theYear && $booked > 0 ){
                    $booked--;
                    $q++;
                    $bookingEnd = 1;
                }
            } // days loop
        } // months loop
        } // years loop


        // get the page id's where the flatmgr calendar is located
        // if non is selected in flexform take the actual page
        $calendarPageId = $this->conf['calendarPageId'];
        if( $calendarPageId < 1 )$calendarPageId = $GLOBALS['TSFE']->id;

/*
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $treeDat = t3lib_div::xml2array($row['pi_flexform']);
            $treeDat = t3lib_div::resolveAllSheetsInDS($treeDat);

            // reading flexform array
            foreach ( $treeDat as $key => $value) {
                if ( is_array($treeDat[$key])) foreach( $treeDat[$key] as $key1 => $value1) {
                    if ( is_array($treeDat[$key][$key1])) foreach( $treeDat[$key][$key1] as $key2 => $value2) {
                        foreach( $treeDat[$key][$key1][$key2] as $key3 => $value3) {
                            foreach( $treeDat[$key][$key1][$key2][$key3] as $key4 => $value4) {
                                foreach( $treeDat[$key][$key1][$key2][$key3][$key4] as $key5 => $value5) {
                                    foreach( $treeDat[$key][$key1][$key2][$key3][$key4][$key5] as $key6 => $value6) {
                                        if ($treeDat[$key][$key1][$key2][$key3][$key4][$key5][$key6]=='monthMultiRow' ||
                                            $treeDat[$key][$key1][$key2][$key3][$key4][$key5][$key6]=='monthSingleRow') {
                                            $flatmgrCalendarPage[] .=$row['pid'];
                                        }
                                    }
                                 }
                            }
                         }
                    }
                }
            }
        }
*/
        if ($available) {
            $urlParameter = array(
                $this->prefixId.'[flat]' => $this->_GP['flat'] ,  // may be used for powermail
                $this->prefixId.'[flatUid]' => $this->_GP['flatUid'],
                $this->prefixId.'[email]' => $this->_GP['email'],
                $this->prefixId.'[start]' => $this->_GP['start'] ,
                $this->prefixId.'[end]' => $this->_GP['end'] ,

            //    $this->prefixId.'[roomUid]' => $this->_GP['roomUid'] ,
            );
            //$link = $this->cObj->getTypoLink_URL($this->_GP['url']);
            if ($this->_GP['url'] < 'A')$target = '_top';
            else $target = '_blank';

                $linkedFlat = $this->pi_linkToPage($this->_GP['flat'] . ' ' . $this->_GP['room'], $this->_GP['url'], $target);
                $out .= '<option value="'. $this->_GP['roomUid'] . '">' . $this->_GP['room'] . '</option>';


            if ($this->_GP['firstRoom'] == -1) {
            // no rooms
            $linkedFlat = $this->pi_linkToPage($this->_GP['flat'] . ' ' . $this->_GP['room'], $this->_GP['url'], $target);
            $booking = '<span class="availableFlatLink">'. $this->pi_getLL('bookingCalendar'). '</span>';
            $out  = '<tr class="availableFlat"><td>';
            $out .= $linkedFlat.'</td><td></td><td>'.$this->_GP['start'].' - '.$endSave .'</td><td class="bookingCalendarLink">';
            $out .= $this->pi_linkToPage($booking, $calendarPageId, '', $urlParameter). '</td>';

            if ($this->_GP['email'] && $this->conf['bookingRequestPageId']) {
            $bookingRequest = '<span class="bookingRequestLink">'. $this->pi_getLL('booking request'). '</span>';
            $out .= '<td class="bookingRequestLink"  onclick="jumpToUrl(\'/' . $this->pi_getPageLink($this->conf['bookingRequestPageId'], '', $urlParameter) .'\',' .
                $this->_GP['flatUid'] . ');"/>' . $bookingRequest . '</td>';
            }
            else {
                $out .= '<td></td>';
            }

            $out .= '</tr>';

            }

        }

        $this->_GP['end'] = $endSave;
        return $out;
    }

    function showAvailability() {
        $out = $this->getAvailableFlatsLegend();
        $out  .= '<div class="availableFlatsHeader">'.$this->pi_getLL('availableFlatsHeader');
        $out .= '&nbsp;'.$this->pi_getLL('availableFrom') . '&nbsp;'. $this->_GP['start'] . '&nbsp;'. $this->pi_getLL('availableTo');
        $out .= '&nbsp;'.$this->_GP['end'].'</div>';

        $out .= '<table class="availabilityTable">';

        if ( $this->input != '' )
            $out .=  $this->input . '</table>'; //<div class="availableFlatsFooter"></div>';
        else
               $out  .= '<tr><td><div class="noAvailableFlat">'.$this->pi_getLL('noAvailableFlat').'</div></td></tr></table>';
        return $out;
    }


    function getNextVacantPeriod() {
        $lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $startDates = array();
        $endDates = array();
        $years = array();

/*        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start'])) return $this->showError('invalid start date');
        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end'])) return $this->showError('invalid end date');
        if (strlen($this->_GP['start']) != 10 ||strlen($this->_GP['end']) != 10) {
            return $this->showError('invalid date');
        }
*/
        // check startdate < enddate
        list($d, $m, $y) =    explode(".", $this->_GP['start']);
        $d1 = mktime(0,0,0,$m,$d,$y);
        list($d, $m, $y) =    explode(".", $this->_GP['end']);
        $d2 = mktime(0,0,0,$m,$d,$y);
        if ( $d2 <= $d1) return $this->showError('invalid date');

        $theYear = substr($this->_GP['start'],-4);
        $years = $this->_GP['years'];

        $startDates = $this->input['startDates'];
        $endDates = $this->input['endDates'];

        // leap year calculating....
        if ( date("L", mktime(0,0,0,1,1,$theYear)) == 1) {
            $lengthOfMonth[2] = 29;
        }
        $booked = 0;
        $p = 0;   // index of startDates
        $q = 0;   // index of endDates
        $weekend = 0;
           $bookingEnd = 0;
        $bookingStart = 0;
        $available = 1;

        $vacantStart = '';
        $vacantEnd = '';
        if ($years != NULL) {
            reset($years);
            // test if bookingStart occurs in years before
            while ( list(,$y) = each($years) && count($startDates) > 0) {
                while ( (list($key,) = each($startDates)) && $y < $theYear) {
                     if ( substr($startDates[$key],-4) != substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
                        $p++;
                    }
                     if ( substr($startDates[$key],-4) == substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
                        $p++;
                        $q++;
                    }
                }
            }
        }
        if ( $p - $q > 0) {
            $booked = 1;
            $q = $p - 1;
        }
           list($d, $m, $y) =    explode(".", $this->_GP['start']);
        $startTime = mktime(0,0,0,$m,$d,$y);
        // we have also to iterate over the years if booking starts in one
        // year and booking end in next year
        for ($theYear; $theYear <= substr($this->_GP['end'],-4); $theYear++) {
        for ($m=1; $m<13; $m++) {
            // adding leading zero
            $mon = ($m < 10) ? '0'. $m : $m ;
            for ($d=1; $d <= $lengthOfMonth[$m]; $d++){
                // adding leading zero
                $day = ($d < 10) ? '0'. $d : $d ;


                // booking start
                if ( $startDates[$p] == $day. "." .$mon. ".".$theYear  ){
                    $booked++;
                    $p++;
                    $bookingStart = 1;
                    if ($vacantEnd == '' && $vacantStart != '') {
                           $vacantEnd = $this->getDateBefore($day. "." .$mon. ".".$theYear);
                    }
                }

                if ($available == 1 && $booked == 0 && $vacantStart == ''
                    &&  mktime(0,0,0,$m,$d,$theYear) >= $startTime) {
                   $vacantStart = $day. "." .$mon. ".".$theYear;
                }

                // compare as long until $this->_GP['end'] is reached
                if ($day. "." .$mon. ".".$theYear == $this->_GP['start']) $compareStart = 1;
                if ( $booked > 0 && $compareStart == 1 ) $available = 0;
                if ($day. "." .$mon. ".".$theYear == $this->_GP['end']) $compareStart = 0;

                // booking end
                if ( $endDates[$q] == $day. "." .$mon. ".".$theYear && $booked > 0 ){
                    $booked--;
                    $q++;
                    $bookingEnd = 1;
                    $available = 1;
                    // if more than one booking end appears
                    while ($endDates[$q] == $day. "." .$mon. ".".$theYear) {
                        $q++;
                        $booked--;
                    }
                }
            } // days loop
        } // months loop
        } // years loop
        if ($vacantEnd == '') {
            $vacantEnd = '31.12.'.(string)($theYear-1);
          }
          else {
              // calculate next day for departure
              // because enddate in database is the last night
               list($d, $m, $y) = explode(".", $vacantEnd);
            $vacantEnd = date('d.m.Y',mktime(0,0,0,$m,$d,$y) + 3600 * 24);
          }
        if ($vacantStart == '') $vacantStart = '01.01.'.(string)($theYear-1);

        $out = '<tr class="nextVacantPeriod"><td><div>' . /* $this->pi_getLL('nextVacantPeriod') . */ $this->_GP['flat'] . ' ' . $this->_GP['room'] .
            '</div></td><td></td><td><div>' /*$this->pi_getLL('date from')*/ . $vacantStart . ' - '. /*$this->pi_getLL('date to') .*/
            $vacantEnd .'</div></td>';

        $urlParameter = array(
            $this->prefixId.'[flat]' => $this->_GP['flat'] ,
            $this->prefixId.'[flatUid]' => $this->_GP['flatUid'] ,
            $this->prefixId.'[roomUid]' => $this->_GP['roomUid'] ,
            $this->prefixId.'[start]' => $this->_GP['start'] ,
            $this->prefixId.'[end]' => $this->_GP['end'] ,
        );
        $calendarPageId = $this->conf['calendarPageId'];
        if( $calendarPageId < 1 )$calendarPageId = $GLOBALS['TSFE']->id;
        $booking = '<span class="availableFlatLink">'. $this->pi_getLL('bookingCalendar'). '</span>';
        $out .= '<td class="bookingCalendarLink" >'.$this->pi_linkToPage($booking, $calendarPageId,'', $urlParameter) . '</td></tr>';
        return $out;
    }

    /* Returns the date of one day before $date
     *
     * @param   $date       date in format dd.mm.yyyy
     * @return     String        date in format dd.mm.yyyy
     *
     */
    function getDateBefore($date){
           list($d, $m, $y) =    explode(".", $date);
        $dayDate = mktime(0,0,0,$m,$d,$y);
        return date('d.m.Y',$dayDate - 3600 * 24);
    }

    /* Returns the date of one day after $date
     *
     * @param   $date       date in format dd.mm.yyyy
     * @return     String        date in format dd.mm.yyyy
     *
     */
    function getDateAfter($date){
           list($d, $m, $y) =    explode(".", $date);
        $dayDate = mktime(0,0,0,$m,$d,$y);
        return date('d.m.Y',$dayDate + 3600 * 24);
    }
    /* Returns the whole booking calendar for a flat,
     * configuration is done in flexforms
     *
     * @return     String        Generated HTML Code
     */
    function showCalendar() {
    		// --- infoworxx 2015-04-01
    		// [BUG] fixed: URL of Typ3Conf changed from absoulte to relative
           $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_calendar_view'] =
            '<script src="typo3conf/ext/flatmgr/pi1/tooltiphelper.js" type="text/javascript"></script>';
        $lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $startDates = array();
        $endDates = array();
        $years = array();

        $theYear = $this->_GP['year'];
        $years = $this->_GP['years'];

        if (! preg_match('/^2[0-9]{3}$/',$this->_GP['year'])) return $this->showError('year input error');

        $startDates = $this->input['startDates'];
        $endDates = $this->input['endDates'];

//debug($startDates);
//debug($endDates);


        // leap year calculating....
        if ( date("L", mktime(0,0,0,1,1,$theYear)) == 1) {
            $lengthOfMonth[2] = 29;
        }
            $booked = 0;
            $p = 0;   // index of startDates
            $q = 0;   // index of endDates
            $weekend = 0;
            $bookingEnd = 0;
            $bookingStart = 0;

            if ($years != NULL) {
                reset($years);
            // test if bookingStart occurs in years before
            // no test for overbooking here
            while ( list(,$y) = each($years) && count($startDates) > 0) {
                while ( (list($key,) = each($startDates)) && $y < $theYear) {
                     if ( substr($startDates[$key],-4) != substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
						if ($this->input['onRequest'][$p]) {
							$onRequest = ' onRequestDay';
							$onRequestStart = ' onRequestStartDay';
							$onRequestEnd = ' onRequestEndDay';
							$onRequestWeekend = ' onRequestWeekend';
							$onRequestStartWeekend = ' onRequestStartWeekend';
							$onRequestEndWeekend = ' onRequestEndWeekend';
							$onRequestBookerChangedDay = ' onRequestBookerChangedDay';
							$onRequestBookerChangedWeekend21 = ' onRequestBookerChangedWeekend21';
						}
                        $p++;
                    }
                     if (substr($startDates[$key],-4) == substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
						$p++;
                        $q++;
                    }
                }
            }
            }

            if ( $p - $q > 0) {
                $booked = 1;
                $q = $p - 1;
            }
            
            if ( $this->conf['showBookedBy'] == 0 ){
                while ( list($k,$bookedBy) = each($this->input['bookedBy'])) {
                    $this->input['bookedBy'][$k] = '';
                }
            }
            $displayFields = array();
            if ($this->conf['displayAdditionalFields'] > '') $displayFields = explode(',',$this->conf['displayAdditionalFields']);

            $out .= '<div id="flatmgrOrigin"></div><div id="flatmgrTooltip" onclick="hideTooltip();"></div><table class="listYear">';
            $column=1;


            // month loop
            for ($m=1; $m<13;$m++) {
                // adding leading zero
                $mon = ($m < 10) ? '0'. $m : $m ;
/*************/
        /* used for showOldBookings; mail Armin 30.09.2010 */
        if (!$this->conf['showOldBookingData']) {
                if (strtotime(date('Y-m',time())) > strtotime($theYear."-".$m)) {
                for ($d=1; $d <= $lengthOfMonth[$m]; $d++){
                        if (date("w", strtotime($theYear."-".$mon."-".$d))== 0 || date("w", strtotime($theYear."-".$mon."-".$d))== 6 ){
                            $weekend = 1;
                        }
                        else $weekend =0;

                        // adding leading zero
                        $day = ($d < 10) ? '0'. $d : $d ;


                        // test one day booking 1st of january
                        if ( $startDates[$p] == $day. "." .$mon. ".".$theYear  && $endDates[$q] ==  "31.12.".($theYear-1)){
                            $booked = -1;
                            $bookingEnd = 1;
                            $q++;
                        }
                        // test one day booking
                        $startAndEnd = 0;

                        if ( $startDates[$p] == $day. "." .$mon. ".".$theYear  && $booked == -1){
                            //$booked = -1;
                            //$p++;
                            //$bookingStart = 1;
                            $startAndEnd = 1;

                            // if more than one start occurs
                            while ($startDates[$p] == $day. "." .$mon. ".".$theYear) {
                                $booked++;
                                $p++;
                            }

                        }

                        // booking start
                        if ( $startDates[$p] == $day. "." .$mon. ".".$theYear ){
                    /************************/
                    if ($this->input['onRequest'][$p]) {
                        $onRequest = ' onRequestDay';
                        $onRequestStart = ' onRequestStartDay';
                        $onRequestEnd = ' onRequestEndDay';
                        $onRequestWeekend = ' onRequestWeekend';
                        $onRequestStartWeekend = ' onRequestStartWeekend';
                        $onRequestEndWeekend = ' onRequestEndWeekend';
                        $onRequestBookerChangedDay = ' onRequestBookerChangedDay';
                        $onRequestBookerChangedWeekend21 = ' onRequestBookerChangedWeekend21';
                    }
                    else {
                        $onRequest = '';
                        $onRequestStart = '';
                        $onRequestEnd = '';
                        $onRequestWeekend = '';
                        $onRequestStartWeekend = '';
                        $onRequestEndWeekend = '';
                        $onRequestBookerChangedDay = '';
                        $onRequestBookerChangedWeekend21 = '';
                    }
                    if ($this->input['onRequest'][$p - 1] && !$this->input['onRequest'][$p]) {
                        $onRequestBookerChangedWeekend12 = ' onRequestBookerChangedWeekend12';
                        $onRequestBookerChangedDay = ' onRequestBookerChangedDay';
                    }
                    else {
                        $onRequestBookerChangedWeekend12 = '';
                        $onRequestBookerChangedDay = '';
                    }
                    if (!$this->input['onRequest'][$p - 1] && $this->input['onRequest'][$p]) {
                        $onRequestBookerChangedDay = ' onRequestBookerChangedWeekend21';
                    }

                    /************************/



                            $booked++;
                            $p++;
                            $bookingStart = 1;
                            // if more than one start occurs
                            while ($startDates[$p] == $day. "." .$mon. ".".$theYear) {
                                $booked++;
                                $p++;
                            }

                        }

                        $bookingEnd = 0;
                        $bookingStart = 0;

                        $startAndEnd = 0; // for one day booking
                        // booking end
                        if ( $endDates[$q] == $day. "." .$mon. ".".$theYear){
                            $booked--;
                            $q++;
                            $bookingEnd = 1;
                            // if more booking end happens on one day
                            while ($endDates[$q] == $day. "." .$mon. ".".$theYear) {
                                $booked--;
                                $q++;
                                $bookingEnd++;
                            }
                        }

                    }

                    continue;
                    }
            }
/*************/

                if ( $this->conf['displayMode'] == 'monthMultiRow' ) {
                	// [BUG] fixed: column-1 must be in brackets!
                    if ((($column-1) % $this->conf['calendarColumns']) == 0) $out.= '<tr>';
                    $out .= '<td class="monthMultiRow" valign="top"><table class="tableMultiRow"><tr><td class="monthNameMultiRow" colspan="7">'. $this->pi_getLL(date("M", strtotime($theYear."-".$mon."-01"))).'</td></tr>';
                    if ( $this->conf['showDaysShortcuts'] == 1 ) {
                        // display the daynames
                        $out .= '<tr>';
                        $out .= ($this->conf['startOfWeek'] == 'sunday')? '<td class="dayNames">'.$this->pi_getLL('Sun').'</td>':'';
                        $out .=
                        '<td class="dayNames">'.$this->pi_getLL('Mon').'</td><td class="dayNames">'.$this->pi_getLL('Tue').
                        '</td><td class="dayNames">'.$this->pi_getLL('Wed').'</td><td class="dayNames">'.$this->pi_getLL('Thu').
                        '</td><td class="dayNames">'.$this->pi_getLL('Fri').'</td><td class="dayNames">'.$this->pi_getLL('Sat');
                        $out .= ($this->conf['startOfWeek'] == 'monday')?'</td><td class="dayNames">'.$this->pi_getLL('Sun').'</td></tr>':'</td></tr>';
                    }
                }

                if ( $this->conf['displayMode'] == 'monthSingleRow' ) {
                    if ( $this->conf['showDaysShortcuts'] == 1 && $m == 1) {
                    $week  = ($this->conf['startOfWeek'] == 'sunday')? '<td class="dayNames">'.$this->pi_getLL('Sun').'</td>':'';
                    $week .= '<td class="dayNames">'.$this->pi_getLL('Mon').'</td><td class="dayNames">'.$this->pi_getLL('Tue').'</td>';
                    $week .= '<td class="dayNames">'.$this->pi_getLL('Wed').'</td><td class="dayNames">'.$this->pi_getLL('Thu').'</td>';
                    $week .= '<td class="dayNames">'.$this->pi_getLL('Fri').'</td><td class="dayNames">'.$this->pi_getLL('Sat').'</td>';
                    $week .= ($this->conf['startOfWeek'] == 'monday')?'<td class="dayNames">'.$this->pi_getLL('Sun').'</td>':'';

                    $out .= '<tr><td class="monthNoDisplay"></td>'.str_repeat($week, 5);
                    if ( $this->conf['startOfWeek'] == 'monday')
                        $out .= '<td class="dayNames">'.$this->pi_getLL('Mon').'</td><td class="dayNames">'.$this->pi_getLL('Tue').'</td></tr>';
                    else
                        $out .= '<td class="dayNames">'.$this->pi_getLL('Sun').'</td><td class="dayNames">'.$this->pi_getLL('Mon').'</td></tr>';

                    }
                    $out .= '<tr><td class="month">'. $this->pi_getLL(date("M", strtotime($theYear."-".$mon."-01"))).'</td>';
                }

                // calculating the left spaces to get the layout right
                if ( $this->conf['displayMode'] != 'monthSingleRow' )
                    $out .= '<tr>';
                $wd = date('w', strtotime($theYear."-".$m."-"."1"));
                if ($this->conf['startOfWeek'] == 'monday') {
                    $wd = ($wd == 0)? 7 : $wd;
                    if ($wd != 1 ) {
                        for ( $s = 1; $s <  $wd ; $s++){
                               $out .= '<td class="noDay">&nbsp;</td>';
                            }
                    }
                }
                else { // sunday
                    for ( $s = 0; $s <  $wd ; $s++){
                           $out .= '<td class="noDay">&nbsp;</td>';
                         }
                }

            // day loop
            for ($d=1; $d <= $lengthOfMonth[$m]; $d++){
                if (date("w", strtotime($theYear."-".$mon."-".$d))== 0 || date("w", strtotime($theYear."-".$mon."-".$d))== 6 ){
                    $weekend = 1;
                }
                else $weekend =0;

                // adding leading zero
                $day = ($d < 10) ? '0'. $d : $d ;




// test one day booking 1st of january
if ( $startDates[$p] == $day. "." .$mon. ".".$theYear  && $endDates[$q] ==  "31.12.".($theYear-1)){
    $booked = -1;
    $bookingEnd = 1;
    $q++;
}
// test one day booking
$startAndEnd = 0;

if ( $startDates[$p] == $day. "." .$mon. ".".$theYear  && $booked == -1){
    //$booked = -1;
    //$p++;
    //$bookingStart = 1;
    $startAndEnd = 1;

    // if more than one start occurs
    while ($startDates[$p] == $day. "." .$mon. ".".$theYear) {
        $booked++;
        $p++;
    }

}
				// infoworxx start
				// when booking end on 01.01. then endDates is 31.12.
				if ( $day==1 && $mon==1 && $endDates[$q - 1] ==  "31.12.".($theYear - 1)) {
					$bookingEnd = 1;
					$bookingStart = 0;
					$booked = 0;
				}
				// infoworxx end

                // booking start
                if ( $startDates[$p] == $day. "." .$mon. ".".$theYear ){
                    /************************/
                    if ($this->input['onRequest'][$p]) {
                        $onRequest = ' onRequestDay';
                        $onRequestStart = ' onRequestStartDay';
                        $onRequestEnd = ' onRequestEndDay';
                        $onRequestWeekend = ' onRequestWeekend';
                        $onRequestStartWeekend = ' onRequestStartWeekend';
                        $onRequestEndWeekend = ' onRequestEndWeekend';
                        $onRequestBookerChangedDay = ' onRequestBookerChangedDay';
                        $onRequestBookerChangedWeekend21 = ' onRequestBookerChangedWeekend21';
                    }
                    else {
                        $onRequest = '';
                        $onRequestStart = '';
                        $onRequestEnd = '';
                        $onRequestWeekend = '';
                        $onRequestStartWeekend = '';
                        $onRequestEndWeekend = '';
                        $onRequestBookerChangedDay = '';
                        $onRequestBookerChangedWeekend21 = '';
                    }
                    if ($this->input['onRequest'][$p - 1] && !$this->input['onRequest'][$p]) {
                        $onRequestBookerChangedWeekend12 = ' onRequestBookerChangedWeekend12';
                        $onRequestBookerChangedDay = ' onRequestBookerChangedDay';
                    }
                    else {
                        $onRequestBookerChangedWeekend12 = '';
                        $onRequestBookerChangedDay = '';
                    }
                    if (!$this->input['onRequest'][$p - 1] && $this->input['onRequest'][$p]) {
                        $onRequestBookerChangedDay = ' onRequestBookerChangedWeekend21';
                    }

                    /************************/

                    $booked++;
                    $p++;
                    $bookingStart = 1;



                    // if more than one start occurs
                    while ($startDates[$p] == $day. "." .$mon. ".".$theYear) {
                        $booked++;
                        $p++;
                    }

                }

/*
                $theDay = $day. "." .$mon. ".".$theYear; // for utilisation

                // the utilisation only for normal booking - no overbooked
                if ( $booked >= 1 && ! $bookingStart) {
                        if ( $this->input['capacity'] != 0 )
                            $utilisation[$theDay] = $this->input['grownups'][$p-1]/$this->input['capacity'] ;
                }
                else $utilisation[$theDay] = 0;
                if ( $bookingStart == 1) {
                        if ( $this->input['capacity'] != 0 )
                            $utilisation[$theDay] = $this->input['grownups'][$p-1]/$this->input['capacity']/2 ;
                }
                if ( $bookingEnd == 1 && !$bookingStart) {
                        if ( $this->input['capacity'] != 0 )
                            $utilisation[$theDay] += $this->input['grownups'][$p-1]/$this->input['capacity']/2 ;
                }
                if ( $bookingEnd == 1 && $bookingStart) {
                        if ( $this->input['capacity'] != 0 )
                            $utilisation[$theDay] = $this->input['grownups'][$p-2]/$this->input['capacity']/2 +
                                $this->input['grownups'][$p-1]/$this->input['capacity']/2;
                }
*/


                 $titleChange = '';
                $title = '';
                if ( $this->conf['showBookedBy']) {
                    $title = 'title="'.$this->input['bookedBy'][$p-1]; // .'"';

                    if ( $this->conf['showExtendedTooltips'] == 1 && $p != $pOld) {
                        $pOld = $p; // used as a switch
                        reset($displayFields);
                        $ttwinText = $this->pi_getLL('bookedBy').'#'.$this->input['bookedBy'][$p-1].'#';
                        $ttwinText .= $this->pi_getLL('bookedFrom'). $startDates[$p-1].'#';
                        $ttwinText .= $this->pi_getLL('bookedTo').$this->getDateAfter($endDates[$p-1]).'#';
                        while ( list ($key, $fieldName) = each($displayFields)) {
                            switch ($fieldName) {
                                case 'agent':
                                    $ttwinText .= $this->pi_getLL('agent').'#'.
                                        $this->input['agent'][$p-1].'#';
                                    break;
                                case 'categories':
                                    $ttwinText .= $this->pi_getLL('category').'#';
                                    $catFound = 0;
                                    for( $j = 0;$j < count($this->input['categories']['uid']); $j++) {
                                        if ($this->input['categoryId'][$p-1] == $this->input['categories']['uid'][$j]) {
                                            $ttwinText .= $this->input['categories']['category'][$j].'#';
                                            $catFound = 1;
                                        }
                                    }
                                    if ($catFound == 0)     $ttwinText .= '#';

                                    break;
                                case 'customernumber':
                                    $ttwinText .= $this->pi_getLL('customerNumber').'#'.
                                        $this->input['customerNumber'][$p-1].'#';
                                    break;
                                case 'grownups':
                                    $ttwinText .= $this->pi_getLL('grownups').'#'.
                                        $this->input['grownups'][$p-1].'#';
                                    break;
                                case 'childs':
                                    $ttwinText .= $this->pi_getLL('childs').'#'.
                                        $this->input['childs'][$p-1].'#';
                                    break;
                                case 'memo':
                                    $ttwinText .= $this->pi_getLL('memo').'#'.
                                        str_replace(chr(10),'<br />',$this->input['memo'][$p-1]).'#';
                                    break;
                            }
                        }
                        $onClick = ' onclick="ttwin(\''.$ttwinText.'\',event);"';

                    }
                    $title .= '"';
                    if ( $p > 1 && ($this->input['bookedBy'][$p-1] != '' || $this->input['bookedBy'][$p-2] != ''))
                        $titleChange = 'title="'.$this->input['bookedBy'][$p-2].' - '.$this->input['bookedBy'][$p-1].'"';
                }
                else {
                    $title = '';
                    $titleChange = '';
                    $onClick = '';
                }


                // display the day with correct class, check for overbooking
                if ( $weekend == 1){
                    if ( ($booked == 1 && $bookingStart == 0 && $bookingEnd == 0)  ) {
                        $out .= ($this->conf['markWeekends'])? '<td class="bookedWeekend'. $onRequestWeekend . '" '. $title.$onClick .'>' .
                                 '<div>'.$d .'</div></td>':'<td class="bookedDay'. $onRequestWeekend . '" '. $title . $onClick .'><div>'.$d.'</div></td>';
                    }
                    if ( ($booked == 1 && $bookingStart == 1 && $bookingEnd == 1) ) {
                        $out .= ($this->conf['markWeekends'])? '<td class="bookerChangedWeekend' . $onRequestBookerChangedWeekend21 . $onRequestBookerChangedWeekend12 .'" '.
                                $titleChange .$onClick .'><div>' . $d . '</div></td>':'<td class="bookerChangedDay'. $onRequestWeekend . '" '.
                                $titleChange .$onClick .'><div>'.$d.'</div></td>';
                    }
                    if ( ($booked > 1 && $bookingStart == 1 && $bookingEnd == 1) ) {
                        $out .= '<td class="overbookedDay" ';
                        $out .= $titleChange.$onClick .'><div>';
                        $out .= $d . '</div></td>';
                    }
                    if ( ($booked > 1 && $bookingStart == 0 && $bookingEnd == 1) )  {
                        $out .= '<td class="overbookedWeekend" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }
                     if ( $booked == 0 && $bookingStart == 0 && $bookingEnd == 0 ){
                        $out .= ($this->conf['markWeekends'])? '<td class="vacantWeekend"><div>' . $d . '</div></td>':'<td class="vacantDay"><div>'.$d.'</div></td>';
                     }
                    if ( ($booked > 1 && $bookingStart == 1 && $bookingEnd == 0) )  {
                        $out .= '<td class="overbookedStartWeekend" ' ;
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ( ($booked == 1 && $bookingStart == 0 && $bookingEnd >= 1) )  {
                        $out .= '<td class="overbookedEndWeekend" ';
                        $out .= $titleChange.$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ( ($booked == 0 && $bookingStart == 0 && $bookingEnd > 1) )  {
                        $out .= '<td class="overbookedAndBookedEndWeekend" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ( $booked > 1 && $bookingStart == 0 && $bookingEnd == 0 ) {
                        $out .= '<td class="overbookedDay" ';
                        $out .= $titleChange.$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ($bookingStart == 1 && $bookingEnd == 0 && $booked == 1) {
                        $out .= ($this->conf['markWeekends'])?'<td class="startWeekend' . $onRequestStartWeekend . '" '.
                                $title.$onClick .'><div>' . $d . "</div></td>":'<td class="startDay' . $onRequestStart . '" '.
                                $title.$onClick .'><div>' . $d . "</div></td>";
                    }
                    if ( $bookingEnd == 1 && $bookingStart == 0 && $booked == 0 && !$startAndEnd) {
                        $out .= ($this->conf['markWeekends'])?'<td class="endWeekend' . $onRequestEndWeekend . '" '.
                                $title.$onClick .'><div>' . $d . "</div></td>" :'<td class="endDay'. $onRequestEnd . '" '.
                                $ttitle.$onClick .'><div>' . $d . "</div></td>";
                    }
                    // one day booking
                    if ( $bookingEnd == 1 && $bookingStart == 0 && $booked == 0 && $startAndEnd) {
                        $out .= '<td class="bookedWeekend" '.$title.$onClick .'><div>' . $d . '</div></td>';
                    }


                }
                if ( $weekend == 0 ) {
                     if ( ($booked == 1 && $bookingStart == 0 && $bookingEnd == 0) ) {
                        $out .= '<td class="bookedDay' . $onRequest .'" '.$title . $onClick.'><div>' . $d . '</div></td>';
                    }
                     if ( $booked == 1 && $bookingStart == 1 && $bookingEnd == 1)  {
                        $out .= '<td class="bookerChangedDay' . $onRequestBookerChangedDay . '" ';
                           $out .= $titleChange . $onClick .'><div>';
                        $out .= $d . '</div></td>';
                    }
                    if ( ($booked > 1 && $bookingStart == 1 && $bookingEnd == 1) ) {
                        $out .= '<td class="overbookedDay" '.$title.$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ( ($booked > 1 && $bookingStart == 1 && $bookingEnd == 0) )  {
                        $out .= '<td class="overbookedStart" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }

                    if ( ($booked > 1 && $bookingStart == 0 && $bookingEnd == 1) )  {
                        $out .= '<td class="overbookedDay" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ( ($booked == 1 && $bookingStart == 0 && $bookingEnd >= 1) )  {
                        $out .= '<td class="overbookedEnd" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }

                    if ( ($booked == 0 && $bookingStart == 0 && $bookingEnd > 1) )  {
                        $out .= '<td class="overbookedAndBookedEnd" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }




                    if ( $booked == 0 && $bookingStart == 0 && $bookingEnd == 0 ){
                        $out .= '<td class="vacantDay"><div>' . $d . '</div></td>';
                    }
                    if ( $booked > 1 && $bookingStart == 0 && $bookingEnd == 0 ) {
                        $out .= '<td class="overbookedDay" ';
                        $out .= $titleChange .$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ($bookingStart == 1 && $bookingEnd == 0 && $booked == 1) {
                        $out .= '<td class="startDay'  . $onRequestStart . '" '.$title.$onClick .'><div>' . $d . '</div></td>';
                    }
                    if ( $bookingEnd == 1 && $bookingStart == 0 && $booked == 0 && !$startAndEnd) {
                        $out .= '<td class="endDay' . $onRequestEnd .'" '.$title.$onClick .'><div>' . $d . '</div></td>';
                    }

                    // one day booking
                    if ( $bookingEnd == 1 && $bookingStart == 0 && $booked == 0 && $startAndEnd) {
                        $out .= '<td class="bookedDay" '.$title.$onClick .'><div>' . $d . '</div></td>';
                    }

                }



                $bookingEnd = 0;
                $bookingStart = 0;

                $startAndEnd = 0; // for one day booking
                // booking end
//                if ( $endDates[$q] == $day. "." .$mon. ".".$theYear && $booked > 0 ){
                if ( $endDates[$q] == $day. "." .$mon. ".".$theYear){
                    $booked--;
                    $q++;
                    $bookingEnd = 1;
                    // if more booking end happens on one day
                    while ($endDates[$q] == $day. "." .$mon. ".".$theYear) {
                        $booked--;
                        $q++;
                        $bookingEnd++;
                    }
                }

                if ($this->conf['startOfWeek'] == 'monday')
                if ( ( date('w', strtotime($theYear."-".$m."-".$d)) ) == 0 && $this->conf['displayMode'] == 'monthMultiRow') {
                    $out .= "</tr><tr>";
                }
                if ($this->conf['startOfWeek'] == 'sunday')
                if ( ( date('w', strtotime($theYear."-".$m."-".$d)) ) == 6 && $this->conf['displayMode'] == 'monthMultiRow') {
                    $out .= "</tr><tr>";
                }




            } //($d=1; $d <= $lengthOfMonth[$m]; $d++) day-loop

          // *** [hte] fix start ***
          // *** adjust number of cells in row ***
          if (($this->conf['displayMode'] == 'monthMultiRow') && (($lengthOfMonth[$m] + $wd - 1 ) % 7)) {
            for ( $s = 0; $s < 7 - (($lengthOfMonth[$m] + $wd - 1) % 7) ; $s++){
               $out .= '<td class="noDay">&nbsp;</td>';
            }
          }
          if ( $this->conf['displayMode'] == 'monthSingleRow') {
            for ( $s = 0; $s < (37 - $lengthOfMonth[$m] - $wd) ; $s++){
               $out .= '<td class="noDay">&nbsp;</td>';
            }
          }
          // *** [hte] fix done ***


            if ( $this->conf['displayMode'] == 'monthMultiRow') {
                $out .= '</tr></table></td>';
        /*******/
        /* used for showOldBookingData */
        if ($m == 12) {
            for (;$column % $this->conf['calendarColumns'] != 0; $column++) {
                $out .= '<td></td>';
            }
        }
        /*******/

                if ($column % $this->conf['calendarColumns'] == 0) {
                    $out .= '</tr>';
                }
            }
            if ( $this->conf['displayMode'] != 'monthMultiRow') {
                $out .= '</tr>';
            }
            $column++;
        } // month-loop
        $out .= '</table>';
        return $out;
    }


    function showUtilisation() {
        $lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $startDates = array();
        $endDates = array();
        $years = array();
        // the utilisation array
        $utilisation = array();

        $theYear = $this->_GP['year'];
        $years = $this->_GP['years'];

        if (! preg_match('/^2[0-9]{3}$/',$this->_GP['year'])) return $this->showError('year input error');

        $startDates = $this->input['startDates'];
        $endDates = $this->input['endDates'];

        // leap year calculating....
        if ( date("L", mktime(0,0,0,1,1,$theYear)) == 1) {
            $lengthOfMonth[2] = 29;
        }

            /*
            $p = 0;   // index of startDates
            $q = 0;   // index of endDates


            if ($years != NULL) {
                reset($years);
            // test if bookingStart occurs in years before


            while ( list(,$y) = each($years) && count($startDates) > 0) {
                while ( (list($key,) = each($startDates)) && $y < $theYear) {
                     if ( substr($startDates[$key],-4) != substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
                        $p++;
                    }
                     if ( substr($startDates[$key],-4) == substr($endDates[$key],-4) && substr($startDates[$key],-4) < $theYear) {
                        $p++;
                        $q++;
                    }
                }
            }
            }
            if ( $p - $q > 0) {
                $q = $p - 1;
            }
            */

            
            $out .= '<table class="listYear">';

            // here if all utilisation is selected loop over the flats
            if ($this->_GP['flat'] == 'all') {
	            if (version_compare(TYPO3_branch, '6.0', '<')) {
	                $model = t3lib_div::makeInstance('tx_flatmgr_model');
	            } else {
	                $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_model');
	            }
                $model->setConfiguration($this->conf);
                $flats = $model->getFlats();

                for ( $j = 0; $j < count($flats['name']); $j++) {
                $capacity += $model->getCapacity($flats['name'][$j]);
                }
            }
            else $flats['name'][0] = $this->_GP['flat'];

            $utilisationEff += 0;



            $column=1;
            for ($m=1; $m<13;$m++) {
                // adding leading zero
                $mon = ($m < 10) ? '0'. $m : $m ;

                if ( $this->conf['displayMode'] == 'monthMultiRow' ) {
                	// [BUG] fixed: column-1 must be in brackets!
                    if ((($column-1) % $this->conf['calendarColumns']) == 0) $out.= '<tr>';
                    $out .= '<td class="monthMultiRow" valign="top"><table class="tableMultiRow"><tr><td class="monthNameMultiRow" colspan="7">'. $this->pi_getLL(date("M", strtotime($theYear."-".$mon."-01"))).'</td></tr>';
                    if ( $this->conf['showDaysShortcuts'] == 1 ) {
                        // display the daynames
                        $out .= '<tr>';
                        $out .= ($this->conf['startOfWeek'] == 'sunday')? '</td><td class="dayNames">'.$this->pi_getLL('Sun').'</td>':'';
                        $out .=
                        '</td><td class="dayNames">'.$this->pi_getLL('Mon').'</td><td class="dayNames">'.$this->pi_getLL('Tue').
                        '</td><td class="dayNames">'.$this->pi_getLL('Wed').'</td><td class="dayNames">'.$this->pi_getLL('Thu').
                        '</td><td class="dayNames">'.$this->pi_getLL('Fri').'</td><td class="dayNames">'.$this->pi_getLL('Sat');
                        $out .= ($this->conf['startOfWeek'] == 'monday')?'</td><td class="dayNames">'.$this->pi_getLL('Sun').'</td></tr>':'</td></tr>';
                    }
                }

                // utilisation in monthSingleRow
                $this->conf['displayMode'] = 'monthSingleRow';
                if ( $this->conf['displayMode'] == 'monthSingleRow' ) {
                    if ( $this->conf['showDaysShortcuts'] == 1 && $m == 1) {
                    $week  = ($this->conf['startOfWeek'] == 'sunday')? '</td><td class="dayNames">'.$this->pi_getLL('Sun').'</td>':'</td>';
                    $week .= '<td class="dayNames">'.$this->pi_getLL('Mon').'</td><td class="dayNames">'.$this->pi_getLL('Tue').'</td>';
                    $week .= '<td class="dayNames">'.$this->pi_getLL('Wed').'</td><td class="dayNames">'.$this->pi_getLL('Thu').'</td>';
                    $week .= '<td class="dayNames">'.$this->pi_getLL('Fri').'<td class="dayNames">'.$this->pi_getLL('Sat').'</td>';
                    $week .= ($this->conf['startOfWeek'] == 'monday')?'<td class="dayNames">'.$this->pi_getLL('Sun').'</td>':'';

                    $out .= '<tr><td class="monthNoDisplay"></td>'.str_repeat($week, 5);
                    if ( $this->conf['startOfWeek'] == 'monday')
                        $out .= '<td class="dayNames">'.$this->pi_getLL('Mon').'</td><td class="dayNames">'.$this->pi_getLL('Tue').'</td></tr>';
                    else
                        $out .= '<td class="dayNames">'.$this->pi_getLL('Sun').'</td><td class="dayNames">'.$this->pi_getLL('Mon').'</td></tr>';

                    }
                    $out .= '<tr><td class="month">'. $this->pi_getLL(date("M", strtotime($theYear."-".$mon."-01"))).'</td>';
                }

                // calculating the left spaces to get the layout right
                $wd = date('w', strtotime($theYear."-".$m."-"."1"));
                if ($this->conf['startOfWeek'] == 'monday') {
                    $wd = ($wd == 0)? 7 : $wd;
                    if ($wd != 1 ) {
                        for ( $s = 1; $s <  $wd ; $s++){
                               $out .= '<td class="noDay">&nbsp;</td>';
                            }
                    }
                }
                else { // sunday
                    for ( $s = 0; $s <  $wd ; $s++){
                           $out .= '<td class="noDay">&nbsp;</td>';
                         }
                }

            for ($d=1; $d <= $lengthOfMonth[$m]; $d++){
                if (date("w", strtotime($theYear."-".$mon."-".$d))== 0 || date("w", strtotime($theYear."-".$mon."-".$d))== 6 ){
                    $weekend = 1;
                }
                else $weekend =0;

                // adding leading zero
                $day = ($d < 10) ? '0'. $d : $d ;

                $theDay = $day. "." .$mon. ".".$theYear; // for utilisation

                $time = strtotime($theYear."-".$mon."-".$d);
                
                
                // loop over the start- and endDates

                   $utilisation[$theDay] += 0;

//                for ( $j = 0; $j < count($flats['name']); $j++) {
//                    $flat = $flats['name'][$j];
                for ( $j = 0; $j < count($flats['name']); $j++) {
                    $flat = $flats['name'][$j];

                for ($i = 0; $i < count($this->input['startDates']); $i++) {
                      if ( $this->input['flat'][$i] == $flat) {
                    // the utilisation only for normal booking - no overbooked
                    if ( $time > (int)$this->input['startDates'][$i] && $time < (int)$this->input['endDates'][$i] ) {
                        if ( $this->input['capacity'][$i] != 0 )
                            if ($this->_GP['flat'] == 'all' && $capacity != 0) {
                                $utilisation[$theDay] += $this->input['grownups'][$i]/$capacity ;
                            }
                            else
                            $utilisation[$theDay] += $this->input['grownups'][$i]/$this->input['capacity'][$i] ;
                    }
                    if ( $time == (int)$this->input['startDates'][$i] ) {
                        if ( $this->input['capacity'][$j] != 0 ){
                            if ($this->_GP['flat'] == 'all' && $capacity != 0) {
                                $utilisation[$theDay] += $this->input['grownups'][$i]/$capacity ;
                            }
                            else {
                                if ($this->input['capacity'][$i] != 0)
                                    $utilisation[$theDay] += $this->input['grownups'][$i]/$this->input['capacity'][$i] ;
                            }
                        }
                    }

                  } // if ( $this->input['flat'][$j] == $flat)

                }
                } // flat-loop
                $utilisationEff += $utilisation[$theDay];
                 $titleChange = '';
                $title = '';
                    $perCent = (int)($utilisation[$theDay] * 100).'%';
                    if ( $utilisation[$theDay] == 0){
                        $out .= '<td class="util0" title="'.$perCent .'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] > 0 && $utilisation[$theDay] < 0.25){
                        $out .= '<td class="util24" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] == .25){
                        $out .= '<td class="util25" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] > .25 && $utilisation[$theDay] < 0.5){
                        $out .= '<td class="util49" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] == .5){
                        $out .= '<td class="util50" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] > .5 && $utilisation[$theDay] < 0.75){
                        $out .= '<td class="util74" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] == .75){
                        $out .= '<td class="util75" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] > .75 && $utilisation[$theDay] < 1.0){
                        $out .= '<td class="util99" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }
                    if ( $utilisation[$theDay] == 1.0){
                        $out .= '<td class="util100" title="'.$perCent.'"><div>' . $d . '</div></td>';
                    }

                if ($this->conf['startOfWeek'] == 'monday')
                if ( ( date('w', strtotime($theYear."-".$m."-".$d)) ) == 0 && $this->conf['displayMode'] == 'monthMultiRow') {
                    $out .= "</tr><tr>";
                }
                if ($this->conf['startOfWeek'] == 'sunday')
                if ( ( date('w', strtotime($theYear."-".$m."-".$d)) ) == 6 && $this->conf['displayMode'] == 'monthMultiRow') {
                    $out .= "</tr><tr>";
                }

            } //($d=1; $d <= $lengthOfMonth[$m]; $d++) day-loop
            if ( $this->conf['displayMode'] == 'monthMultiRow') {
                $out .= '</tr></table></td>';
                if ($column % $this->conf['calendarColumns'] == 0) {
                    $out .= '</tr>';
                }
            }
            if ( $this->conf['displayMode'] != 'monthMultiRow') {
                $out .= '</tr>';
            }
            $column++;
        } // month-loop
        $out .= '</table>';

        if ( date("L", mktime(0,0,0,1,1,$theYear)) == 1)
            $utilisationEff /= 365;
        else $utilisationEff /= 364;
        $utilisationEff = ((int)($utilisationEff *10000))/100;
        $out .= '<div class="'.'effectiveUtilisation">'.$this->pi_getLL('effectiveUtilisation') .
            '<span class="value">'.$utilisationEff.'%</span></div>';
        return $out;
    }



    function getStrsBetween($s,$s1,$s2=false,$offset=0) {
    /*====================================================================
    Function to scan a string for items encapsulated within a pair of tags

    getStrsBetween(string, tag1, <tag2>, <offset>

    If no second tag is specified, then match between identical tags

    Returns an array indexed with the encapsulated text, which is in turn
    a sub-array, containing the position of each item.

    Notes:
    strpos($needle,$haystack,$offset)
    substr($string,$start,$length)

    ====================================================================*/

    if( $s2 === false ) { $s2 = $s1; }
    $result = array();
    $L1 = strlen($s1);
    $L2 = strlen($s2);

    if( $L1==0 || $L2==0 ) {
        return false;
    }

    do {
        $pos1 = strpos($s,$s1,$offset);

        if( $pos1 !== false ) {
            $pos1 += $L1;

            $pos2 = strpos($s,$s2,$pos1);

            if( $pos2 !== false ) {
                $key_len = $pos2 - $pos1;

                $this_key = substr($s,$pos1,$key_len);

                if( !array_key_exists($this_key,$result) ) {
                    $result[$this_key] = array();
                }

                $result[$this_key][] = $pos1;

                $offset = $pos2 + $L2;
            } else {
                $pos1 = false;
            }
        }
    } while($pos1 !== false );

    return $result;
    }

    /* Returns the $msg in error div-tags
     *
     * @param   $msg        string, messagestring
     * @return     String        HTML wrapped error message
     *
     */
    function showError($msg) {
        return '<div class="errorMessage">' . 'flatmgr_view message: '. $this->errorMessages[$msg] . '</div>';
    }

    var $errorMessages = array (
        'invalid date' => 'You entered an invalid date, proper dates are in the format dd.mm.yyyy',
        'invalid start date' => 'You entered an invalid start date, proper dates are in the format dd.mm.yyyy',
        'invalid end date' => 'You entered an invalid end date, proper dates are in the format dd.mm.yyyy',
        'flat input error' => 'You entered an invalid flat name',
        'year input error' => 'You entered an invalid year',
        'startdate in the past' => 'Startdate is in the past',
        'insertFlat' => 'Please insert some flats first!',
    );
}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_view.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_view.php']);
}
 ?>