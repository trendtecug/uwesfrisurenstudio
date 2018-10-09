<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2015 Joachim Ruhs <postmaster@joachim-ruhs.de>
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

class tx_flatmgr_controller extends tslib_pibase {

    /* Extension Stuff */
    var $prefixId         = 'tx_flatmgr_pi1';
    var $scriptRelPath     = 'pi1/class.tx_flatmgr_controller.php';
    var $extKey         = 'flatmgr';
    

    /* Global Configuration */
    var $conf;
    var $_GP;

    /* Set Code */
    function setCode($code) {
        $this->code = $code;
    }

    function setConfiguration($conf) {
        $this->conf = $conf;
    }

     function setGPVars($GP) {
        $this->_GP = $GP;
    }

    function setLocalLang(&$LOCAL_LANG) {
        $this->LOCAL_LANG = $LOCAL_LANG;
    }

    /* Main Function for processing Admin Controls */ 
    function handle() {
           // Configure caching
           $this->allowCaching = $this->conf["allowCaching"]?1:0;
           if (!$this->allowCaching) {
               $GLOBALS["TSFE"]->set_no_cache();
           }
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $view = t3lib_div::makeInstance('tx_flatmgr_view', $this->conf);
        } else {
            $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_view', $this->conf);
        }
        $view->setLocalLang($this->LOCAL_LANG);

		reset($this->_GP);

		//$this->getMap();
		//debug($this->_GP, 'controller');
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_model');
        }
        $model->setConfiguration($this->conf);

          switch ($this->_GP["action"]) {
            case 'showMonthMultiRow':
                $flats = $model->getFlats();

                // set default flat if none selected
                if (count($flats) == 1 || $this->_GP['flatUid'] == '' ) {
                    $this->_GP['flat'] = $flats['name'][0];
                    $this->_GP['flatUid'] = $flats['uid'][0];
                    $this->_GP['roomUid'] = $model->getFirstRoomUid($this->_GP['flatUid']);
                }
                if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
                if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;
                if (! preg_match('/^20[0-3][0-9]$/',$this->_GP['year']))
                    $errors .= $this->showError('year input error');
                if ($errors == '') {

                for ($i = 0; $i < count($flats['uid']); $i++) {
                    $rooms = $model->getRooms($flats['uid'][$i]);
                    $flats['rooms'][$i] = $rooms;
                }
                if (count(($flats) == 1 || $this->_GP['flatUid'] == '') && $this->_GP['roomUid'] == '' ) {
                    $this->_GP['roomUid'] = $flat['rooms'][0]['roomUid'][0];
                }

                if ($this->_GP['roomUid']) {
                    if($this->_GP['flatUid'] != $model->getFlatUidOfRoom($this->_GP['roomUid']))
                        $errors .= $this->showError('invalid room');

                }
                
                if ($this->_GP['flatUid']) $this->_GP['flat'] = $model->getFlat($this->_GP['flatUid']);

//debug($flats);

                $model->setGPvars($this->_GP);

                $view->setGPvars($this->_GP);
                $view->setCode("SHOWFLATS");
                $view->setInput($flats);
                $out = $view->display();

                $years = $model->getYears();
                $this->_GP['years'] = $years;
                $flatYear = array ('flat' => $this->_GP['flat'],
                                   'year' => $this->_GP['year']);


                if ($this->_GP['roomUid'] > 0) {
                    $this->_GP['room'] = $model->getRoom($this->_GP['roomUid']);
                }

                $view->setGPvars($this->_GP);

                $view->setCode("SHOWYEARS");
                $view->setInput($years);
                }
                $out .= $errors . $view->display();


                $bookingData= array();
                $bookingData = $model->getBooking($this->_GP['flatUid']);
                // because enddates are not ordered correct we need $model->getEndDates()...
                $bookingData['endDates'] = $model->getEndDates();


//debug($this->_GP);
//debug($bookingData);
                $bookingData['categories'] = $model->getCategories();
                $bookingData['capacity'] = $model->getCapacity($this->_GP['flat']);

                $view->setCode("SHOWCALENDAR");
                $view->setInput($bookingData);
                $out .= $view->display();
                return $out;
                break;

            case 'showMonthSingleRow':
                $flats = $model->getFlats();
                // set default flat if none selected
                if (count($flats) == 1 || $this->_GP['flatUid'] == '' ) {
                    $this->_GP['flat'] = $flats['name'][0];
                    $this->_GP['flatUid'] = $flats['uid'][0];
                }

                if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
                if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;
                if (! preg_match('/^20[0-3][0-9]$/',$this->_GP['year']))
                    $errors .= $this->showError('year input error');
                if ($errors == '') {

                for ($i = 0; $i < count($flats['uid']); $i++) {
                    $rooms = $model->getRooms($flats['uid'][$i]);
                $flats['rooms'][$i] = $rooms;
                }
                if (count(($flats) == 1 || $this->_GP['flatUid'] == '') && $this->_GP['roomUid'] == '' ) {
                    $this->_GP['roomUid'] = $flat['rooms'][0]['roomUid'][0];
                }

                if ($this->_GP['roomUid']) {
                    if($this->_GP['flatUid'] != $model->getFlatUidOfRoom($this->_GP['roomUid']))
                        $errors .= $this->showError('invalid room');

                }
                if ($this->_GP['flatUid']) $this->_GP['flat'] = $model->getFlat($this->_GP['flatUid']);


                $model->setGPvars($this->_GP);

                $view->setGPvars($this->_GP);
                $view->setCode("SHOWFLATS");
                $view->setInput($flats);
                $out = $view->display();

                $years = $model->getYears();
                $this->_GP['years'] = $years;
                $flatYear = array ('flat' => $this->_GP['flat'],
                                   'year' => $this->_GP['year']);

                if ($this->_GP['roomUid'] > 0) {
                    $this->_GP['room'] = $model->getRoom($this->_GP['roomUid']);
                }

                $view->setGPvars($this->_GP);
                $view->setCode("SHOWYEARS");
                $view->setInput($years);
                $out .= $view->display();
                $bookingData= array();
                $bookingData = $model->getBooking($this->_GP['flatUid']);
                // because enddates are not ordered correct we need $model->getEndDates()...
                $bookingData['endDates'] = $model->getEndDates();
                $bookingData['categories'] = $model->getCategories();
                $view->setCode("SHOWCALENDAR");
                $view->setInput($bookingData);
                }
                $out .= $errors . $view->display();
                return $out;
                break;

            case 'showUtilisation':
                $flats = $model->getFlats();
                // set default flat if none selected
                if (count($flats['uid']) == 1 || $this->_GP['flat'] == '' ) {
                    $this->_GP['flat'] = $flats['name'][0];
                    $this->_GP['flatUid'] = $flats['uid'][0];
                }
                $model->setGPvars($this->_GP);

                $view->setGPvars($this->_GP);
                $view->setCode("SHOWUTILISATIONFLATS");
                $view->setInput($flats);
                $out = $view->display();

                //$years = $model->getYears();
                if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
                $years = array(    0 => date('Y') - 2,
                        1 => date('Y') - 1,
                        2 => date('Y'),
                        3 => date('Y') + 1,
                        4 => date('Y') + 2);
                $this->_GP['years'] = $years;
                $flatYear = array ('flat' => $this->_GP['flat'],
                                   'year' => $this->_GP['year']);
                $view->setGPvars($this->_GP);
                $view->setCode("SHOWUTILISATIONYEARS");
                $view->setInput($years);
                $out .= $view->display();
                $bookingData= array();
                $bookingData = $model->getUtilisationBooking();
                $bookingData['categories'] = $model->getCategories();
                //$bookingData['capacity'] = $model->getCapacity($this->_GP['flat']);
//debug($bookingData);
                $view->setCode("SHOWUTILISATION");
                $view->setInput($bookingData);
                $out .= $view->display();
                return $out;
                break;

            case 'availabilityCheck':
                if ($this->conf['useXAJAX']){
                // we need some Javascript
                $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_availability'] =
                    '<script type="text/javascript">
                    /*<![CDATA[*/
                    function jumpToUrl(URL, uid)    {    //
                        var roomUid = 0;
                        var room = "";
                        if (document.getElementById("tx_flatmgr_pi1[roomUid]" + uid)) {
                        roomUid = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).value;


                        var i = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).selectedIndex;
                        room = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).options[i].text;

                        }
                        window.location.href = URL + "&tx_flatmgr_pi1[roomUid]=" + roomUid + "&tx_flatmgr_pi1[room]=" + room;
                        return false;
                    }/*]]>*/

                    </script>';
                }

                $flats = $model->getFlats();
                // set default flat if none selected
                if (count($flats) == 1 || $this->_GP['flat'] == '' ) {
                    $this->_GP['flat'] = $flats['name'][0];
                    $this->_GP['flatUid'] = $flats['uid'][0];
                }
                $model->setGPvars($this->_GP);
                $flatCategories = $model->getFlatCategories();
                $flatCapacities = $model->getFlatCapacities();
                $data['categories'] = $flatCategories;
                $data['capacities'] = $flatCapacities;
                $view->setInput($data);
                $view->setGPvars($this->_GP);
                $view->setCode("SHOWAVAILABILITYMASK");
                $out = $view->display();
                return $out;
                break;

            case 'checkAvailability':
                $model->setGPvars($this->_GP);
                $flatsArray = $model->getFlats();
                $flats = $flatsArray['name'];
                $urls  = $flatsArray['url'];
                $flatUids = $flatsArray['uid'];
                $emails = $flatsArray['email'];
                //$years = $model->getYearsOfAllFlats();

                list ($d, $m, $y) = explode('.', $this->_GP['start']);
                $d = strlen($d) < 2 ? '0' . $d : $d;
                $m = strlen($m) < 2 ? '0' . $m : $m;
                $this->_GP['start'] = $d . '.' . $m . '.' . $y;
                list ($d, $m, $y) = explode('.', $this->_GP['end']);
                $d = strlen($d) < 2 ? '0' . $d : $d;
                $m = strlen($m) < 2 ? '0' . $m : $m;
                $this->_GP['end'] = $d . '.' . $m . '.' . $y;

                   if (  !$this->conf['useXAJAX'] ){
                $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_availability'] =
                    '<script type="text/javascript">
                    /*<![CDATA[*/
                    function jumpToUrl(URL, uid)    {    //
                        roomUid = 0;
                        room = "";
                        if (document.getElementById("tx_flatmgr_pi1[roomUid]" + uid)) {
                        roomUid = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).value;

                        var i = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).selectedIndex;
                        room = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).options[i].text;
                        }
                        window.location.href = URL + "&tx_flatmgr_pi1[roomUid]=" + roomUid + "&tx_flatmgr_pi1[room]=" + room;
                        return false;
                    }/*]]>*/

                    </script>';
                 }


                while ( list($id, $flat) = each($flatsArray)) {
                   //    $link = $this->cObj->getTypoLink_URL($flatsArray['url'][$id]);
                //$details = $this->pi_linkToPage($flat, $flatsArray['url'][$id]);
                //debug($details);
                }
                $years = $model->getYears();
                $this->_GP['years'] = $years;
                $view->setCode("CHECKAVAILABILITY");

                while (is_array($flats) && list($id, $flat) = each($flats)) {
                    $avail = '';
                    $this->_GP['flat'] = $flat;
                    $this->_GP['url'] = $urls[$id];
                    $this->_GP['flatUid'] = $flatUids[$id];
                    $this->_GP['email'] = $emails[$id];
                    // get the rooms
                    if ($this->conf['enableContigentSearch'])
                        $rooms = $model->getRooms($this->_GP['flatUid'], 1);
                    else
                        $rooms = $model->getRooms($this->_GP['flatUid'], $this->_GP['flatCapacity']);
                    if (count($rooms['roomUid']) > 0) {
                        $this->_GP['availableFlatCapacity'] = 0;
                        for ($k = 0; $k < count($rooms['roomUid']); $k++) {
                        $this->_GP['roomUid'] = $rooms['roomUid'][$k];
                        $this->_GP['room'] = $rooms['name'][$k];
                        $model->setGPvars($this->_GP);
                        $data['startDates'] = $model->getStartDates();
                        $data['endDates'] = $model->getAllEndDates();
/*
                        if ($k == 0) $this->_GP['firstRoom'] = 1;
                        if ($k == 0 && count($rooms['roomUid']) == 1 ) $this->_GP['firstRoom'] = 3;
                        else if ($k < count($rooms['roomUid']) -1 ) $this->_GP['firstRoom'] = 0;
                        else if ($k == count($rooms['roomUid']) -1 ) $this->_GP['firstRoom'] = 2;
*/

                        if ($k == 0 && count($rooms['roomUid']) != 1) $this->_GP['firstRoom'] = 1;
                        else if ($k < count($rooms['roomUid']) -1 ) $this->_GP['firstRoom'] = 0;
                        else if ($k == count($rooms['roomUid']) -1 ) $this->_GP['firstRoom'] = 2;

                        if ($k == 0 && count($rooms['roomUid']) == 1) $this->_GP['firstRoom'] = 3;

                    //    $this->_GP['room'] = $rooms['name'][$k];
                        $view->setGPvars($this->_GP);
                        $view->setInput($data);
                        $avail .= $view->display();
                        if ( $avail == '' && $this->conf['showNextBookingPossibility']) {
                            $view->setCode("SHOWNEXTAVAILABILITY");
                            $out .= $view->display();
                            $view->setCode("CHECKAVAILABILITY");
                        } else {
                            $this->_GP['availableFlatCapacity'] += $rooms['capacity'][$k];
                        }

                        } // rooms
                        if ($this->conf['enableContigentSearch'] && $this->_GP['availableFlatCapacity'] < $this->_GP['flatCapacity'])
                            $avail = '';
                        
                        if (strpos($avail, 'errorMessage') > 0) {
                            $out = $view->showError('invalid date');
                        }
                        if ($avail && strpos($avail, 'errorMessage') == 0) {
                            if ($this->_GP['url'] < 'A')$target = '_top';
                            else $target = '_blank';

                            $linkedFlat = $view->pi_linkToPage($this->_GP['flat'], $this->_GP['url'], $target);
                            $out  .= '<tr class="availableFlat"><td>';
                            $out .= $linkedFlat;

                            $out .= '</td><td><select class="roomSelector" id="tx_flatmgr_pi1[roomUid]'. $this->_GP['flatUid'] .'">';
                            $out .= $avail . '</select>';

                            $urlParameter = array(
//                                $this->prefixId.'[flat]' => $this->_GP['flat'] ,
                                $this->prefixId.'[flatUid]' => $this->_GP['flatUid'] ,
                                $this->prefixId.'[email]' => $this->_GP['email'] ,
                                $this->prefixId.'[start]' => $this->_GP['start'] ,
                                $this->prefixId.'[end]' => $this->_GP['end'] ,
//                                        $this->prefixId.'[room]' => $this->_GP['room'] ,
                            );
                            $booking = '<span class="availableFlatLink">'. $this->pi_getLL('bookingCalendar'). '</span>';
                            $calendarPageId = $this->conf['calendarPageId'];
                            if( $calendarPageId < 1 )$calendarPageId = $GLOBALS['TSFE']->id;
                            $out .= '</td><td>'.$this->_GP['start'].' - '.$this->_GP['end'] .'</td>';
                            $out .= '<td class="bookingCalendarLink"  onclick="jumpToUrl(\'/' . $view->pi_getPageLink($calendarPageId, '', $urlParameter) .'\',' .
                                $this->_GP['flatUid'] . ');"/>' . $booking . '</td>';

                            if ($this->_GP['email'] && $this->conf['bookingRequestPageId']) {
                            $bookingRequest = '<span class="bookingRequestLink">'. $this->pi_getLL('booking request'). '</span>';
                            $out .= '<td class="bookingRequestLink"  onclick="jumpToUrl(\'/' . $view->pi_getPageLink($this->conf['bookingRequestPageId'], '', $urlParameter) .'\',' .
                                $this->_GP['flatUid'] . ');"/>' . $bookingRequest . '</td>';
                            }
                            else {
                                $out .= '<td></td>';
                            }
                        }
                        $avail = '';
                        //$out .= $view->display();

                    }
                    // now just simple flats without rooms
                    $rooms = $model->getRooms($this->_GP['flatUid']); // query without capacity

                    if (count($rooms['roomUid']) == 0) {
                        $this->_GP['firstRoom'] = -1;
                        $this->_GP['roomUid'] = 0;
                        $this->_GP['room'] = '';
                        $model->setGPvars($this->_GP);
                        $data['startDates'] = $model->getStartDates();
                        $data['endDates'] = $model->getAllEndDates();
                        $view->setGPvars($this->_GP);
                        $view->setInput($data);
                        $avail = $view->display();
                        if ( $avail == '' && $this->conf['showNextBookingPossibility']) {
                            $view->setCode("SHOWNEXTAVAILABILITY");
                            $out .= $view->display();
                            $view->setCode("CHECKAVAILABILITY");
                        }
                        else $out .= $avail;
                        //$out .= $view->display();
                    }

                }

                $view->setCode("SHOWAVAILABILITY");

                // query external typo3 sites; url's have to be set
                // in flexform

                $urls = explode(';',$this->conf['foreignURL']);
                //$out .= '<div class="foreignFlats">'. $this->pi_getLL('foreignFlats'). '</div>';

                // here starts snoopy
	            if (version_compare(TYPO3_branch, '6.0', '<')) {
	                $snoopy = t3lib_div::makeInstance('tx_flatmgr_snoopy');
	            } else {
	                $snoopy = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_snoopy');
	            }
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
                    $out_arr = $this->getStrsBetween($snoopyOut, '<tr class="nextVacantPeriod"><td>','</td></tr>');
                    while (list($key, $value) = each($out_arr)) {
                        $key1 = str_replace('href="', 'href="'.$domain['scheme'].'://'.$domain['host'].'/',$key);
                        $key2 = str_replace('target="_top"', 'target="_blank"',$key1);
                        $out .= '<tr class="nextForeignVacantPeriod"><td>' . $key2 .'</td></tr>';
                    }
                } // foreach end
                $view->setInput($out);
                $view->setGPvars($this->_GP);
                $out = $view->display(); // . $sout;
                return $out;
                break;

            case 'listView':
                $model->setGPvars($this->_GP);
                //$data = $model->getFlats();
                $categoryUids = explode(',', $this->conf['flatListCategories']);
                for ($i = 0; $i < count($categoryUids); $i++) {
                    $data = $model->getFlatList($categoryUids[$i]);
                    $data['fields'] = $model->getFlatFields();
                    $data['category'] = $model->getFlatCategory($categoryUids[$i]);
                    $view->setInput($data);
                    $view->setGPvars($this->_GP);
                    $view->setCode("LISTVIEW");
                    $out .= $view->display();
                }
                return $out;
                break;

            case 'simpleListView':
                //$data = $model->getFlats();
                $categoryUids = explode(',', $this->conf['flatListCategories']);
                for ($i = 0; $i < count($categoryUids); $i++) {
                    $data = $model->getFlatList($categoryUids[$i]);
                    $data['fields'] = $model->getFlatFields();
                    $data['category'] = $model->getFlatCategory($categoryUids[$i]);
                    $view->setInput($data);
                    $view->setGPvars($this->_GP);
                    $view->setCode("SIMPLELISTVIEW");
                    $out .= $view->display();
                }
                return $out;
                break;

			/* 20150401 infoworxx Start - new Feature - Randomlist */
            case 'randomListView':
                $model->setGPvars($this->_GP);
                //$data = $model->getFlats();
                $categoryUids = explode(',', $this->conf['flatListCategories']);
                for ($i = 0; $i < count($categoryUids); $i++) {
                    $data = $model->getRandomFlatList($categoryUids[$i]);
                    $data['fields'] = $model->getFlatFields();
                    $data['category'] = $model->getFlatCategory($categoryUids[$i]);
                    $view->setInput($data);
                    $view->setGPvars($this->_GP);
                    $view->setCode("LISTVIEW");
                    $out .= $view->display();
                }
                return $out;
				break;

			/* 20150401 infoworxx End */

            case 'singleView':

                if (!$this->_GP['year']) $this->_GP['year'] = date('Y', time());
                $model->setGPvars($this->_GP);
                $data = $model->getSingleFlat($this->_GP['flatUid']);
                $data['fields'] = $model->getFlatFields();
                if ($this->conf['showMap'] && $data['showMap'][0]) $data['map']= $this->getMap();
                $view->setInput($data);
                $view->setGPvars($this->_GP);
                $view->setCode("SINGLEVIEW");
                $out .= $view->display();

                $years = $model->getYears();
                $this->_GP['years'] = $years;

                $flatYear = array ('flat' => $this->_GP['flat'],
                                   'year' => $this->_GP['year']);
                if ($this->_GP['roomUid'] > 0) {
                    $this->_GP['room'] = $model->getRoom($this->_GP['roomUid']);
                }
                $view->setGPvars($this->_GP);
                $view->setCode("SHOWYEARS");
                //$view->setInput($years);
                $view->setInput(array('showFutureYearsInCalendar' => $data['showFutureYearsInCalendar']));
                //$out .= $errors . $view->display();
                //$out .= $errors . $view->display();
                $outYears = $view->display();
                if ($this->conf['showCalendar'] && $data['showCalendar'][0])  // div for lightbox
                    $out .= $errors . '<div class="flatmgrCalendar">' . $outYears;
                if ($this->_GP['flatUid']) $this->_GP['flat'] = $model->getFlat($this->_GP['flatUid']);

                $model->setGPvars($this->_GP);
                $bookingData= array();
                $bookingData = $model->getBooking($this->_GP['flatUid']);
                // because enddates are not ordered correct we need $model->getEndDates()...
                $bookingData['endDates'] = $model->getEndDates();

                $bookingData['categories'] = $model->getCategories();
                $bookingData['capacity'] = $model->getCapacity($this->_GP['flat']);
                $this->conf['displayMode'] = $this->conf['listViewDisplayMode'];
                $view->conf = $this->conf;
                $view->setGPvars($this->_GP);
                $view->setCode("SHOWCALENDAR");
                $view->setInput($bookingData);
                if ($this->conf['showCalendar'] && $data['showCalendar'][0])
                    $out .= $view->display() . '</div>';              ;
//                    $out .= '<div id="mb_flatmgrCalendar"><div class="tx-flatmgr-pi1">' . $outYears . $view->display() . '</div></div>';

                return $out;
                break;

            default:

        }
        
    }
    
    function getMap() {
//debug($this->conf);
        require_once(t3lib_extMgm::extPath('locator')."pi1/class.tx_locator_controller.php");
        require_once(t3lib_extMgm::extPath('locator')."pi1/class.tx_locator_view.php");
        $this->conf['templateFile'] = 'EXT:flatmgr/pi1/locatortemplate.html';
		// ToDo: 
		// in tx_locator_view die Funktion "makeInstance" durch constructor
		// ersetzen und hier die Typo3 6.x Konvention einbauen
        $view = tx_locator_view::makeInstance("tx_locator_view",
                                                     $this->conf);
        $this->conf['externalLocationTable'] = 'tx_flatmgr_flat';
        $this->conf['googleApiVersion'] = '3.1';
        $this->conf['uidOfSingleView'] = $this->_GP['flatUid'];
        $this->conf['useFeUserData'] = 2;
        $this->conf['enableStreetView'] = 1;
        $this->conf['enableStreetViewOverlay'] = 1;
        $this->conf['showResultTable'] = 1;

        $controller = new tx_locator_controller();
        $this->_GP['mode'] = 'singleView';
        $controller->setGPVars($this->_GP);
        $controller->setConfiguration($this->conf);
        $controller->setLocalLang($this->LOCAL_LANG);
        $content = $controller->handle();
        $content = $this->pi_wrapInBaseClass($content);

        $content .= '<div id="map" class="tx_locator_map" style="width:' . $this->conf['mapWidth'] . 'px; height:' . $this->conf['mapHeight'] . 'px;"></div>';
        $content .= '<script type="text/javascript">
            /*<![CDATA[*/
                load();
            /*]]>*/
             </script>';
        return $content;
    }


    function createSysFolder($parent, $title) {
        $time = time();
                            
        $fields = array();
        $fields["pid"]         = $parent;
        $fields["tstamp"]    = $time;
        $fields["deleted"]    = 0;
        $fields["doktype"]    = 254;
        $fields["title"]    = $title;
        $fields["urltype"]    = 1;
        $fields["crdate"]    = $time;
                            
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("pages", $fields); 
        return $GLOBALS['TYPO3_DB']->sql_insert_id();
    }
    
    function checkDirectory($directory) {
        if ( false === (@opendir($directory)) ) {
            mkdir( $directory );
            chmod( $directory, 0777);
        }
    }


    /*
     * Returns the message wrapped in <div class="errorMessage>...</div>
     *
     * @params  $msg    String
     */
    function showError($msg) {
        return '<div class="errorMessage">' . 'flatmgr admin message: '.$this->errorMessages[$msg] . '</div>';
    }
       var $errorMessages = array (
        'year input error' => 'You entered an invalid year',
        'invalid room' => 'The roomUid is incorrect!',
    );



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


    
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_controller.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_controller.php']);
}
 ?>