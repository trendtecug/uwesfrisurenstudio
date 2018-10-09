<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2007-2012 Joachim Ruhs <postmaster@joachim-ruhs.de>
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
// @todo: Wird wahrscheinlich unter 7 nicht mehr funktionieren (?)
require_once(t3lib_extMgm::extPath('rlmp_dateselectlib').'class.tx_rlmpdateselectlib.php');

class tx_flatmgr_admin_view extends tslib_pibase implements t3lib_singleton {
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

    function setController(&$controller) {
		$this->controller = $controller;
	}

	/* initial call */
	function display() {
		switch ($this->code) {
			case 'SHOWYEARS':
				return $this->displayYears();
			    break;
			case 'SHOWBOOKINGYEARS':
				return $this->displayBookingYears();
			    break;
			case 'SHOWREBOOKING':
				return $this->displayRebooking();
			    break;
			case 'BOOKING':
				return $this->displayBooking();
				break;
			case 'MONTHLYBOOKING':
				return $this->displayMonthlyBooking();
				break;
			case 'EDITROOMS':
				return $this->editRooms();
				break;
			default:
				return $this->displayDefault();
		}
		
	}
	
	/* Returns a list of available flats with
	 * links to select the booking data,
	 * link to update the flatname
	 * and a link to delete each flat
	 * with confirmation
	 *
	 * @return 	String		Generated HTML Code
	 */
	function displayYears() {

        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $controller = t3lib_div::makeInstance('tx_flatmgr_admin_controller');
        } else {
            $controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_controller');
        }

		if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
		// hacker saving
//		if (isset($this->_GP['flat']))
//        	if (! preg_match($this->conf['allowedChars'],$this->_GP['flat'])) return $controller->showError('flat input error');
		if (isset($this->_GP['year']))
			if (! preg_match('/^2[0-9]{3}$/',$this->_GP['year'])) return $controller->showError('year input error');

		$arr = array ($this->prefixId.'[action]' => 'showBooking',
//			  $this->prefixId.'[flat]' => $this->_GP['flat'],
			  $this->prefixId.'[flatUid]' => $this->_GP['flatUid'],
			  $this->prefixId.'[year]' => '0',
			  $this->prefixId.'[roomUid]' => $this->_GP['roomUid']);
		$out .= '<table class="theAdminYears"><tr>';

		$years = array(	0 => $this->_GP['year'] - 2,
	                1 => $this->_GP['year'] - 1,
	                2 => $this->_GP['year'],
                    3 => $this->_GP['year'] + 1,
                    4 => $this->_GP['year'] + 2);



//		while ($this->input != NULL && list ($id, $y) = each($this->input)) {
		while (list ($id, $y) = each($years)) {
           	$arr[$this->prefixId.'[year]'] = $y;
			if ( $y == $this->_GP['year'] )
				$out .= '<td class="selected">' . $y . '</td>';
			else $out .= '<td>' . $this->pi_linkTP($y,$arr,1,0) . '</td>';
		}
		$out .= '</tr></table>';
		$out .= $year;
		return $out;
	}

	/* Returns a list of available booking years with
	 * links to select the year
	 *
	 * @return 	String		Generated HTML Code
	 */
	function displayBookingYears() {
        $out .= '<table class="monthlyBookingLegend"><tr><td class="vacantDay"><div>&nbsp;</div></td><td class="legend">';
		$out .= $this->pi_getLL('vacantDay') .'</td>';
		$out .= ($this->conf['markWeekends'])? '<td class="vacantWeekend"><div>&nbsp;</div></td><td class="legend">'.$this->pi_getLL('vacant weekend').'</td>': '';
		$out .= '<td class="bookedDay1"><div>&nbsp;</div></td><td class="bookedDay2"><div>&nbsp;</div></td><td class="legend">';
		$out .= $this->pi_getLL('bookedDay').'</td>';
//      $out .= ($this->conf['markWeekends'])? '<td class="bookedWeekend">&nbsp;</td><td class="legend">'.$this->pi_getLL('booked weekend').'</td>': '';
//		$out .= ($this->conf['showOverbookedLegend'])?'<td class="overbookedDay">&nbsp;</td><td class="legend">'.$this->pi_getLL('overbooked').'</td>':'';
		$out .= '</tr></table>';


        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $controller = t3lib_div::makeInstance('tx_flatmgr_admin_controller');
        } else {
            $controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_controller');
        }

		if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
		// hacker saving
		if (isset($this->_GP['year']))
			if (! preg_match('/^2[0-9]{3}$/',$this->_GP['year'])) return $controller->showError('year input error');

		$arr = array ($this->prefixId.'[action]' => 'monthlyOverview',
			  $this->prefixId.'[flatUid]' => $this->_GP['flatUid'],
			  $this->prefixId.'[month]' => $this->_GP['month'],
			  $this->prefixId.'[category]' => $this->_GP['category'],
			  $this->prefixId.'[year]' => '0');
		$out .= '<table class="monthlyBookingYears"><tr>';

		// use this to enable year selection of flexform
		if ($this->conf['useFlexformYearSelection']) {
	        for ($y = $this->conf['yearsBeforeActualYear']; $y > 0; $y--) {
	            $years[$x] = date('Y') - $y;
	            $x++;
	        }
	
	        if (($this->input['showFutureYearsInCalendar'][0] && $this->_GP['action'] == 'singleView') || !$this->input['showFutureYearsInCalendar'][0] || $this->conf['displayMode'] == 'monthlyOverview') {
	            for ($y = 0; $y <= $this->conf['yearsAfterActualYear']; $y++) {
	                $years[$x] = date('Y') + $y;
	                $x++;
	            }
	        } else $years[$x] = date('Y') + $y;
			$this->input = $years;
		}

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



	/* Returns a list of available flats for
	 * rebooking
	 *
	 * @return 	String		Generated HTML Code
	 */
	function displayRebooking() {
		//debug($this->input);
		$template = $this->cObj->getSubpart($this->templateCode, '###ADMIN_TEMPLATE###');
		$subpart = $this->cObj->getSubpart($template, "###REBOOKING###");

		$out = '<table class="rebooking"><tr>';
		$out .= '<form method="post" action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).
				'?tx_flatmgr_pi1[action]=rebook">';
		$out .='<input type="hidden" name=tx_flatmgr_pi1[bookingUid]" value="'. intval($this->_GP['uid']).'" />';
		$out .='<input type="hidden" name=tx_flatmgr_pi1[action]" value="rebook" />';
		$out .= '<td><select name="tx_flatmgr_pi1[flatUid]">';
		while ( list($id, $flat) = each($this->input['name'])) {
			if ( $this->input['possible'][$id] )
				$out .= '<option value="'. $this->input['uid'][$id].'">'.$flat.'</option>';
		}
		reset($this->input['name']);
		while ( list($id, $flat) = each($this->input['name'])) {
			$flatUid = $this->input['uid'][$id];
			// rooms
			if (count($this->input['rooms'][$flatUid]['roomUid'])) {
				for ($i = 0; $i < count($this->input['rooms'][$flatUid]['roomUid']); $i++) {
					if ( $this->input['rooms'][$flatUid]['possible'][$i]) {
						$out .= '<option value="'. $this->input['uid'][$id].
						',' . $this->input['rooms'][$flatUid]['roomUid'][$i] .'">'.$flat.
							' ' . $this->input['rooms'][$flatUid]['name'][$i] .'</option>';
					}
				}
    		}
		}
		
		
		$out .= '</select></td>';
	    $out .= '<td><input type="submit" value="'.$this->pi_getLL('rebook').'"></td>';
	    $out .= '</form></tr></table>';

		$marks['###REBOOKINGHEADER###'] = $this->pi_getLL('rebooking header');

		$marks['###CUSTOMER###'] = $this->pi_getLL('customer');
		$marks['###CUSTOMERV###'] = $this->input['booked']['bookedby'];
		$marks['###AGENT###'] = $this->pi_getLL('agent');
		$marks['###AGENTV###'] = $this->input['booked']['agent'];
		$marks['###FROM###'] = $this->pi_getLL('from');
		$marks['###SDATE###'] = $this->_GP['start'];
		$marks['###TO###'] = $this->pi_getLL('to');
		$marks['###EDATE###'] = $this->_GP['end'];
		$marks['###ONTO###'] = $this->pi_getLL('on');
		$marks['###REBOOKINGFORM###'] = $out;
		return $this->cObj->substituteMarkerArray($subpart, $marks);
	}

	/* Returns a list of available flats with
	 * links to select the booking data,
	 * link to update the flatname
	 * and a link to delete each flat
	 * with confirmation
	 *
	 * @return 	String		Generated HTML Code
	 */
	function displayDefault() {
		$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_admin'] =
			'<script type="text/javascript">
			/*<![CDATA[*/
			function jumpToUrl(URL, uid)	{	//
				roomUid = 0;
				if (document.getElementById("tx_flatmgr_pi1[roomUid]" + uid)) {
			    roomUid = document.getElementById("tx_flatmgr_pi1[roomUid]" + uid).value;
			    }
				window.location.href = URL + "&tx_flatmgr_pi1[roomUid]=" + roomUid;
				return false;
			}/*]]>*/

			</script>';

		$template = $this->cObj->getSubpart($this->templateCode,
					'###ADMIN_TEMPLATE###');
					

		$i = 0;
		$extPath = t3lib_extMgm::siteRelPath('flatmgr');
		while ( is_array($this->input['name']) && list ($id, $elem) = each($this->input["name"]) ) {
			$flat = $elem;
			$uid = $this->input['uid'][$id];
			$capacity = $this->input['capacity'][$id];
			$editRoomsLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'editRooms',
				'tx_flatmgr_pi1[flat]' => $flat,
				'tx_flatmgr_pi1[flatUid]' => $uid,
				);
			$bookingLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'showBooking',
//				'tx_flatmgr_pi1[flat]' => $flat,
				'tx_flatmgr_pi1[flatUid]' => $uid,
				);
   			$flatLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'deleteFlat',
				'tx_flatmgr_pi1[flat]' => $flat,
				);
            $flatUpdateLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'updateFlat',
				'tx_flatmgr_pi1[flatUid]' => $uid,
				);
			if (strpos($this->pi_getPageLink($GLOBALS['TSFE']->id), '?') > 0)
				$deleteFlatUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id) .
			    '&tx_flatmgr_pi1[action]=deleteFlat'.
				'&amp;tx_flatmgr_pi1[flat]='. $flat.
				'&amp;tx_flatmgr_pi1[flatUid]='. $uid;

			else
				$deleteFlatUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id) .
			    '?tx_flatmgr_pi1[action]=deleteFlat'.
				'&amp;tx_flatmgr_pi1[flat]='. $flat.
				'&amp;tx_flatmgr_pi1[flatUid]='. $uid;

			$marks = array();
			// here starts the row
			if (strpos($this->pi_getPageLink($GLOBALS['TSFE']->id), '?') > 0)
			    $html =	'<tr><form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).
				'&tx_flatmgr_pi1[action]=updateFlat" name="f'.$uid.'" method="post">';
			else
			    $html =	'<tr><form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).
				'?tx_flatmgr_pi1[action]=updateFlat" name="f'.$uid.'" method="post">';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

            $html .=	'<input type="hidden" id="tx_flatmgr_pi1[roomUid]" value="">';

            $html .=	'<input type="hidden" name="tx_flatmgr_pi1[flatUid]" value="'.$uid.'" maxlength="255">';
            $html .=	'<input type="hidden" name="tx_flatmgr_pi1[action]" value="updateFlat">';
            $html .=	'<input type="text" name="tx_flatmgr_pi1[flatName]" value="'
				.$flat.'" title="'.$this->pi_getLL('nameOfTheFlat').'">';

			if (count($this->input['rooms'][$uid]) != 0) {

            $html .=	'<select title="' . $this->pi_getLL('room') . '" alt="' . $this->pi_getLL('room') . '" id="tx_flatmgr_pi1[roomUid]' . $uid .'" name="tx_flatmgr_pi1[roomUid]">';
			for ($j = 0; $j < count($this->input['rooms'][$uid]['roomUid']); $j++) {
			    $html .= '<option value="' . $this->input['rooms'][$uid]['roomUid'][$j] . '">' .
			        $this->input['rooms'][$uid]['name'][$j] . '</option>';
			}
            $html .=	'</select>';
			} else {
	            $html .=	'<select style="display:none;" id="tx_flatmgr_pi1[roomUid]' . $uid .'" name="tx_flatmgr_pi1[roomUid]">';
				for ($j = 0; $j < count($this->input['rooms'][$uid]); $j++) {
				    $html .= '<option value="0">' .
				        '---' . '</option>';
				}
	            $html .=	'</select>';
			}
			$html .= '</td>';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$html .=
				'<img src="'.$extPath.'pi1/icons/edit_rooms.gif" title="'.$this->pi_getLL('editRooms').'" '.
				'onclick="jumpToUrl(\'/' . $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $editRoomsLinkParams) .'\',' .$uid . ');"/></td>';
//debug($this->pi_getPageLink($GLOBALS['TSFE']->id, '', $editRoomsLinkParams));

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
            $html .=	'<input type="text" name="tx_flatmgr_pi1[capacity]" value="'.
				$capacity.'" size="2" maxlength="2" style="width:15px;" title="'.$this->pi_getLL('flatCapacity').'"></td>';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

//			$html .= '<a href="'.$this->pi_getPageLink($GLOBALS['TSFE']->id, '', $bookingLinkParams).'">'.
//				'<img src="'.$extPath.'pi1/icons/edit_record.gif" title="'.$this->pi_getLL('to the bookings').'"></a></td>';

			$html .=
				'<img src="'.$extPath.'pi1/icons/edit_record.gif" title="'.$this->pi_getLL('to the bookings').'" '.
				'onclick="jumpToUrl(\'/' . $this->pi_getPageLink($GLOBALS['TSFE']->id, '', $bookingLinkParams) .'\',' .$uid . ');"/></td>';


//			$html .= '<a href="'.$this->pi_getPageLink($GLOBALS['TSFE']->id, '', $bookingLinkParams).'">'.
//				'<img src="/typo3conf/ext/flatmgr/pi1/icons/edit_record.gif" title="'.$this->pi_getLL('to the bookings').'"/img></a></td>';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

			$html .=
				'<img src="'.$extPath.'pi1/icons/save_record.gif" title="'.$this->pi_getLL('save record').
				'" onclick="document.f'.$uid.'.submit();"></a></td></form>';
//			$html .=
//				'<img src="/typo3conf/ext/flatmgr/pi1/icons/save_record.gif" title="'.$this->pi_getLL('save record').
//				'" onclick="document.f'.$uid.'.submit();"/img></a></td></form>';

			$confirmMsg = '\''.$this->pi_getLL('deleteFlatConfirmation'). ' ' .$flat . '\'';

			$html .= ($i % 2 == 0)?'<td class="evenRow deleteFlat">':'<td class="oddRow deleteFlat">';
			$html .=	'<img src="'.$extPath.'pi1/icons/delete_record.gif" title="'.
				$this->pi_getLL('delete record').
				'"/img onclick="if (confirm('.$confirmMsg.')){jumpToUrl(\''.$deleteFlatUrl .'\')}" ></td></tr>';
//			$html .=	'<img src="/typo3conf/ext/flatmgr/pi1/icons/delete_record.gif" title="'.
//				$this->pi_getLL('delete record').
//				'"/img onclick="if (confirm('.$confirmMsg.')){jumpToUrl(\''.$deleteFlatUrl .'\')}" ></td></tr>';

			$subpart = $this->cObj->getSubpart($template, "###FLATLIST_DATA###");
			$marks["###BOOKING_LINK###"] .= $html;
			$final .= $this->cObj->substituteMarkerArray($subpart, $marks);
			$i++;
		}
			$final .= '</table>';

		/* TODO: check if this is really cool */
		$dummy = $this->pi_getPageLink($GLOBALS['TSFE']->id);

		$header = $this->cObj->getSubpart($this->templateCode, '###FLATLIST_HEADER###');
		$header = $this->cObj->substituteMarker ($header, '###FLATS###', $this->pi_getLL('availableFlats')).
			'<table class="adminFlatList">';

		for ($i = 0; $i < count($this->input['categories']['name']); $i++) {
			$categories .= '<option value="' . $this->input['categories']['uid'][$i] . '">' . $this->input['categories']['name'][$i] . '</option>';
		}

		$marks = array();
		$marks['###PAGE_LINK###'] = $dummy;
		$marks['###INSERT_FLAT###'] =  $this->pi_getLL('insert flat');
		$marks['###NAME_OF_THE_FLAT###'] = $this->pi_getLL('nameOfTheFlat');
		$marks['###FLAT_CAPACITY###'] = $this->pi_getLL('flatCapacity');
		$marks['###FLAT_CATEGORIES###'] = $categories;

		$template = $this->cObj->getSubpart($this->templateCode, '###NEW_FLAT###');
/*
		$template = $this->cObj->substituteMarker ($template, '###PAGE_LINK###', $dummy);
		$template = $this->cObj->substituteMarker ($template, '###INSERT_FLAT###', $this->pi_getLL('insert flat'));
		$template = $this->cObj->substituteMarker ($template, '###NAME_OF_THE_FLAT###', $this->pi_getLL('nameOfTheFlat'));
		$template = $this->cObj->substituteMarker ($template, '###FLAT_CAPACITY###', $this->pi_getLL('flatCapacity'));
		$template = $this->cObj->substituteMarker ($template, '###FLAT_CATEGORIES###', $categories);
*/
		return $header . $final . $this->cObj->substituteMarkerArray($template, $marks);

		return $header . $this->cObj->substituteMarkerArray($final, $marks) . $template;
	}


	function editRooms() {
		$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_admin'] =
			'<script type="text/javascript">
			/*<![CDATA[*/
			function jumpToUrl(URL, uid)	{
				window.location.href = URL;
				return false;
			}/*]]>*/

			</script>';

		$template = $this->cObj->getSubpart($this->templateCode,
					'###ADMIN_TEMPLATE###');


		$i = 0;
		$extPath = t3lib_extMgm::siteRelPath('flatmgr');

		while ( is_array($this->input['name']) && list ($id, $elem) = each($this->input["name"]) ) {
			$flat = $elem;
			$uid = $this->input['roomUid'][$id];
			$capacity = $this->input['capacity'][$id];
/*
			$editRoomsLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'editRooms',
				'tx_flatmgr_pi1[flat]' => $flat,
				'tx_flatmgr_pi1[flatUid]' => $uid,
				);
			$bookingLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'showBooking',
				'tx_flatmgr_pi1[flat]' => $flat,
				);
   			$roomLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'deleteRoom',
				'tx_flatmgr_pi1[flat]' => $flat,
				'tx_flatmgr_pi1[flatUid]' => $this->input['flatUid'],
				);
*/
/*            $roomUpdateLinkParams = array(
				'tx_flatmgr_pi1[action]' => 'updateRoom',
				'tx_flatmgr_pi1[flatUid]' => $uid,
				);
*/
			if (strpos($this->pi_getPageLink($GLOBALS['TSFE']->id), '?') > 0)
				$deleteRoomUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id) .
			    '&tx_flatmgr_pi1[action]=deleteRoom'.
				'&amp;tx_flatmgr_pi1[roomUid]='. $uid.
				'&amp;tx_flatmgr_pi1[flat]='. $this->_GP['flat'].
                '&amp;tx_flatmgr_pi1[flatUid]='. $this->input['flatUid'];
			else
				$deleteRoomUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id) .
			    '?tx_flatmgr_pi1[action]=deleteRoom'.
				'&amp;tx_flatmgr_pi1[roomUid]='. $uid.
				'&amp;tx_flatmgr_pi1[flat]='. $this->_GP['flat'].
                '&amp;tx_flatmgr_pi1[flatUid]='. $this->input['flatUid'];
			$marks = array();
			// here starts the row
			if (strpos($this->pi_getPageLink($GLOBALS['TSFE']->id), '?') > 0)
			    $html =	'<tr><form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).
				'&tx_flatmgr_pi1[action]=updateRoom" name="f'.$uid.'" method="post">';
			else
			    $html =	'<tr><form action="'.$this->pi_getPageLink($GLOBALS['TSFE']->id).
				'?tx_flatmgr_pi1[action]=updateRoom" name="f'.$uid.'" method="post">';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

            $html .=	'<input type="hidden" name="tx_flatmgr_pi1[roomUid]" value="'.$uid.'" maxlength="255">';
            $html .=	'<input type="hidden" name="tx_flatmgr_pi1[flatUid]" value="'.$this->input['flatUid'].'" maxlength="255">';
//            $html .=	'<input type="hidden" name="tx_flatmgr_pi1[flat]" value="'.$this->_GP['flat'].'" maxlength="255">';

            $html .=	'<input type="hidden" name="tx_flatmgr_pi1[action]" value="updateRoom">';
            $html .=	'<input type="text" name="tx_flatmgr_pi1[roomName]" value="'
				.$flat.'" title="'.$this->pi_getLL('room').'">';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
            $html .=	'<input type="text" name="tx_flatmgr_pi1[capacity]" value="'.
				$capacity.'" size="2" maxlength="2" style="width:15px;" title="'.$this->pi_getLL('flatCapacity').'"></td>';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

			$html .=
				'<img src="'.$extPath.'pi1/icons/save_record.gif" title="'.$this->pi_getLL('save record').
				'" onclick="document.f'.$uid.'.submit();"></a></td></form>';

			$confirmMsg = '\''.$this->pi_getLL('deleteRoomConfirmation'). ' ' .$flat . '\'';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$html .=	'<img src="'.$extPath.'pi1/icons/delete_record.gif" title="'.
				$this->pi_getLL('delete record').
				'"/img onclick="if (confirm('.$confirmMsg.')){jumpToUrl(\''.$deleteRoomUrl .'\')}" ></td></tr>';

			$subpart = $this->cObj->getSubpart($template, "###ROOMLIST_DATA###");
			$marks["###BOOKING_LINK###"] .= $html;
			$final .= $this->cObj->substituteMarkerArray($subpart, $marks);
			$i++;
		}
			$final .= '</table>';

		/* TODO: check if this is really cool */
		$dummy = $this->pi_getPageLink($GLOBALS['TSFE']->id);
		$header = $this->cObj->getSubpart($this->templateCode, '###ROOMLIST_HEADER###');
		$header = $this->cObj->substituteMarker ($header, '###FLAT###', htmlspecialchars($this->_GP['flat']));
		$header = $this->cObj->substituteMarker ($header, '###ROOMSOF###', $this->pi_getLL('roomsOf')).
			'<table class="adminFlatList">';

		for ($i = 0; $i < count($this->input['categories']['name']); $i++) {
			$categories .= '<option value="' . $this->input['categories']['uid'][$i] . '">' . $this->input['categories']['name'][$i] . '</option>';
		}

		$marks = array();
		$marks['###PAGE_LINK###'] = $dummy;
		$marks['###INSERT_ROOM###'] =  $this->pi_getLL('insert room');
		$marks['###NAME_OF_THE_ROOM###'] = $this->pi_getLL('nameOfTheRoom');
		$marks['###ROOM_CAPACITY###'] = $this->pi_getLL('roomCapacity');
		$marks['###FLATUID###'] = $this->input['flatUid'];

		$template = $this->cObj->getSubpart($this->templateCode, '###NEW_ROOM###');

/*
		$template = $this->cObj->substituteMarker ($template, '###PAGE_LINK###', $dummy);
		$template = $this->cObj->substituteMarker ($template, '###INSERT_FLAT###', $this->pi_getLL('insert flat'));
		$template = $this->cObj->substituteMarker ($template, '###NAME_OF_THE_FLAT###', $this->pi_getLL('nameOfTheFlat'));
		$template = $this->cObj->substituteMarker ($template, '###FLAT_CAPACITY###', $this->pi_getLL('flatCapacity'));

debug($categories);
		$template = $this->cObj->substituteMarker ($template, '###FLAT_CATEGORIES###', $categories);
*/
		return $header . $final . $this->cObj->substituteMarkerArray($template, $marks);

		return $header . $this->cObj->substituteMarkerArray($final, $marks) . $template;
	}



	/* Returns a list of the bookings for a flats
	 * with the possibility to update the booking
	 * data, show the additional fields if set in
	 * flexforms and to delete the booking
	 *
	 * @return 	String		Generated HTML Code
	 */
	function displayBooking() {
		// loading calendar library
		// and define output format
		tx_rlmpdateselectlib::includeLib();
        $dateSelectorConf = array (
			        'calConf.' => array (
                    	'dateTimeFormat' => 'dd.mm.y',
	         			'inputFieldDateTimeFormat' => '%d.%m.%Y'
						)
    	);

   		$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_admin'] =
			'<script type="text/javascript">
			/*<![CDATA[*/
			function jumpToUrl(URL)	{	//
				window.location.href = URL;
				return false;
			}
			function toggleVisibility(id) {
			  	if ( navigator.appVersion.indexOf("MSIE") > 0)
					if ( document.getElementById(id).style.display == "block")
						document.getElementById(id).style.display = "none";
					else document.getElementById(id).style.display = "block";
			 	else
			 		if ( document.getElementById(id).style.display == "table-cell")
						document.getElementById(id).style.display = "none";
					else document.getElementById(id).style.display = "table-cell";
			}
			function tx_flatmgr_pi1toggleXAJAXVisibility(uid) {
				formId = "update" + uid;
				hideId = "formResult" + uid;
				n = document.getElementById(formId).length;
				// more than 9 elements -> hide
				if (n <= 13)	{tx_flatmgr_pi1processFormData(xajax.getFormValues("update"+uid));}
				else {
				    // hiding
				    document.getElementById(hideId).innerHTML="";
				}
			}
			function tx_flatmgr_pi1SetFields() {
			    address = document.getElementById(\'tx_flatmgr_pi1_ttaddress\').value;
				data = address.split(":");
				document.newRecord.elements[\'tx_flatmgr_pi1_bookedBy\'].value = data[0];
				if (document.newRecord.elements[\'tx_flatmgr_pi1_customerNumber\'] == null)
				    alert("You have to enable the customernumber in flexforms!");
				else document.newRecord.elements[\'tx_flatmgr_pi1_customerNumber\'].value = data[1];
			}
			/*]]>*/
			</script>';

//debug($this->conf);
		if ($this->conf['adminView.']['usejQuery'] && $this->conf['adminView.']['includejQuery']) {
           $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_showSingle'] .=
            '<script type="text/javascript">
            /*<![CDATA[*/
            /*]]>*/
            </script>
				<link href="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/themes/base/jquery-ui.css" rel="stylesheet" type="text/css"/>
<!--
				<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.5/jquery.min.js"></script>
-->
	            <script type="text/javascript" src="typo3conf/ext/flatmgr/pi1/scripts/jquery-1.7.1.min.js"></script>
				<script src="http://ajax.googleapis.com/ajax/libs/jqueryui/1.8/jquery-ui.min.js"></script>

			<script type="text/javascript">
            /*<![CDATA[*/
            /*]]>*/
			</script>';

		}

		if ($this->conf['adminView.']['usejQuery']) {
           $GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_showSingle'] .=
            '<script type="text/javascript">
            /*<![CDATA[*/
                var $j = jQuery.noConflict();

  	$j(document).ready(function(){
		$j("#tx_flatmgr_pi1_customerNumber").val("");
		$j("#tx_flatmgr_pi1_ttaddress").autocomplete({
			source: "index.php?eID=tx_flatmgr_eID&tx_flatmgr_pi1[action]=searchAddress&tx_flatmgr_pi1[pid]=' . $this->conf['pid_list'] .'",
			minLength: 1,
//			autoFocus: true,
			search: function(event, ui)  {
				$j("#tx_flatmgr_pi1_customerNumber").val(function(index, value) {
					  return "";
				})
			},
			select: function( event, ui ) {
				$j("#tx_flatmgr_pi1_customerNumber").val(function(index, value) {
					  return ui.item.uid;
				});

   			}
		});

		var formChildren = $j("form > *");
//		alert (formChildren.length);
		$j.each($j("form"), function() {
			var name = $j(this).attr("name");
			if (name) {
//			alert(name);
			if (name != "newRecord") {
				var theId = $j(this).attr("name").match(/[0-9]+/)[0];
//				alert(theId);

//				addAutocompleter(theId);
			}
		}
		
		
		})

	});
	
	
	function addAutocompleter(id) {
		var form = 		$j("#update" + id + " :input");
		$j.each(form, function() {
			if ($j(this).attr("name") == "tx_flatmgr_pi1[bookedBy]") {
				//alert($j(this).attr("name"));
				$j(this).removeAttr("readonly");

				$j(this).autocomplete({
					source: "index.php?eID=tx_flatmgr_eID&tx_flatmgr_pi1[action]=searchAddress&tx_flatmgr_pi1[pid]=' . $this->conf['pid_list'] .'",
					minLength: 1,
		//			autoFocus: true,
					search: function(event, ui)  {
						$j("#tx_flatmgr_pi1_customerNumber" + id).val(function(index, value) {
							  return "";
						})

					},
					select: function( event, ui ) {
						$j("#tx_flatmgr_pi1_customerNumber" + id).val(function(index, value) {
							  return ui.item.uid;
						});
						$j("#tx_flatmgr_pi1_customerNumber_" + id).val(function(index, value) {
							  return ui.item.uid;
						});

		   			}
				});

			}

		});
		

	
	}

	// used for CSV export
	function jumpToUrl(URL)	{
		window.location.href = URL;
		return false;
	}

            /*]]>*/
			</script>';

		}



		if($this->conf['displayAdditionalFieldsWithXAJAX']) {
   		// xajax starts here
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
			$this->xajax->registerFunction(array('processSaveFormData', &$this, 'processSaveFormData'));
			$this->xajax->registerFunction(array('processNewFormData', &$this, 'processNewFormData'));
			$this->xajax->registerFunction(array('processDeleteFormData', &$this, 'processDeleteFormData'));
			// If this is an xajax request, call our registered function, send output and exit
			$this->xajax->processRequests();
			// Else create javascript and add it to the header output
			$GLOBALS['TSFE']->additionalHeaderData[$this->prefixId.'xajax'] = $this->xajax->getJavascript(t3lib_extMgm::siteRelPath('xajax'));
		} // if($this->conf['displayAdditionalFieldsWithXAJAX'])

		$template = $this->cObj->getSubpart($this->templateCode, '###ADMIN_TEMPLATE###');

		$displayFields = array();
		if ($this->conf['displayAdditionalFields'] > '') $displayFields = explode(',',$this->conf['displayAdditionalFields']);

		$i = 0; // switch used for background-color
	    $extPath = t3lib_extMgm::siteRelPath('flatmgr');


		while ( list ($id, $date) = each($this->input['starts']) ) {
			$theStarts .=  date('d.m.Y',$date);
			$sDate   =  date('d.m.Y',$date);
			$uid     = $this->input['uid'][$i];
			$bookedBy = $this->input['bookedby'][$i];
			$eDate   =  date('d.m.Y',$this->input['ends'][$i]);
			$customerNumber = $this->input['customerNumber'][$i];
			$grownups = $this->input['grownups'][$i];
			$childs = $this->input['childs'][$i];
			$memo = $this->input['memo'][$i];
			$category = $this->input['category'][$i];
			$agent = $this->input['agent'][$i];
			$onRequest = $this->input['onRequest'][$i];
			$i++;

			$linkparams = array(
				'tx_flatmgr_pi1[action]' => "deleteBooking",
				'tx_flatmgr_pi1[flat]' => $this->_GP['flat'],
				'tx_flatmgr_pi1[uid]' => $uid,
				'tx_flatmgr_pi1[roomUid]' => $this->_GP['roomUid'],
				);
			$marks = array();
			$action = $this->pi_getPageLink($GLOBALS['TSFE']->id).
				'?tx_flatmgr_pi1[action]=updateBooking';
			$urlParams = array (
			    'tx_flatmgr_pi1[action]' => 'updateBooking',
			);
			$action = $this->pi_getPageLink($GLOBALS['TSFE']->id, '_top', $urlParams);


			// here starts the form
			$html = '<form name="update'.$uid.'" id="update'.$uid.'" action="'.$action.'" method="post" enctype="multipart/form-data" >';
            $html .= '<table class="adminBookingListTable"><tr>';
			// header
			if ($i == 1){
				$arr = array (
				    'tx_flatmgr_pi1[action]' => 'downloadCsv',
				    'tx_flatmgr_pi1[flatUid]' =>$this->_GP['flatUid'],
				    'tx_flatmgr_pi1[year]' =>$this->_GP['year'],
				);
				$downloadCsvUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id, '_top', $arr);

				$html .= ($i % 2 == 0)?'<td colspan="13" class="evenRow">':'<td colspan="13" class="oddRow">';
				if ($this->conf['useTtaddresses']) {
            	$html .= '<div class="bookingDataHeader">'.$this->pi_getLL('bookingDataOf').
					'&nbsp;'.$this->_GP['flat'] . ' ' . $this->input['room'].'' .
					'<img src="/typo3conf/ext/flatmgr/pi1/icons/csv.gif" alt="CSV Export" title="CSV Export" class="csvImage" onclick="jumpToUrl(\''.$downloadCsvUrl .'\')"/></div></td></tr><tr>';
				} else {
            	$html .= '<div class="bookingDataHeader">'.$this->pi_getLL('bookingDataOf').
					'&nbsp;'.$this->_GP['flat'] . ' ' . $this->input['room'].'' .
					'</div></td></tr><tr>';
				}
			}
			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

   			$html .= $this->pi_getLL('date from').
				'<input type="hidden" name="'.$this->prefixId.'[action]" value="updateBooking"/>'.
                '<input type="hidden" name="tx_flatmgr_pi1[bookingId]" value="'.$uid.'"/>'.
                '<input type="hidden" name="tx_flatmgr_pi1[flat]" value="'.$this->_GP['flat'].'"/>'.
                '<input type="hidden" name="tx_flatmgr_pi1[flatUid]" value="'.intval($this->_GP['flatUid']).'"/>'.
                '<input type="hidden" name="tx_flatmgr_pi1[uid]" value="'.intval($uid).'"/>';
			if ( $this->conf['useTtaddresses'])
                $html .= '<input type="hidden" name="tx_flatmgr_pi1[customerNumber]" value="'.$customerNumber.'" id="tx_flatmgr_pi1_customerNumber_' . $uid .'"/>' ;
			$html .= '<input type="hidden" name="tx_flatmgr_pi1[roomUid]" value="'.intval($this->_GP['roomUid']).'"/></td>';
			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$html .= '<input type="text" name="'.$this->prefixId.'[start]'.'" id="'.$this->prefixId.'_start'.$uid.'" '.
					'value="'.$sDate.'" size="10"  maxlength="10"/></td>';
            $html .= ($i % 2 == 0)?'<td class="calendarEvenRow">':'<td class="calendarOddRow">';
			$html .= tx_rlmpdateselectlib::getInputButton ($this->prefixId.'_start'.$uid,$dateSelectorConf).'</td>';
            $html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
            $html .= $this->pi_getLL('date to').'</td>';
            $html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
            $html .= '<input type="text" name="'.$this->prefixId.'[end]'.'" id="'.$this->prefixId.'_end'.$uid.'" '.
					'value="'.$eDate.'" size="10" maxlength="10"/></td>';
            $html .= ($i % 2 == 0)?'<td class="calendarEvenRow">':'<td class="calendarOddRow">';
			$html .= tx_rlmpdateselectlib::getInputButton ($this->prefixId.'_end'.$uid,$dateSelectorConf).'</td>';

			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

			if ($onRequest)
			$html .= $this->pi_getLL('onRequest').
				'<input type="checkbox" name="'.$this->prefixId.'[onRequest]" value="1" checked="checked"/></td>';
			else
			$html .= $this->pi_getLL('onRequest').
				'<input type="checkbox" name="'.$this->prefixId.'[onRequest]" value="1"/></td>';


			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$html .= $this->pi_getLL('booked from').'</td>';
            $html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';



			if ( $this->conf['useTtaddresses'])
				$html .= '<input type="text" readonly="readonly" name="tx_flatmgr_pi1[bookedBy]" value="'.$bookedBy.'" size="20" maxlength="100"/></td>';
			else $html .= '<input type="text" name="tx_flatmgr_pi1[bookedBy]" value="'.$bookedBy.'" size="20" maxlength="100"/></td>';

			if ($this->conf['displayAdditionalFieldsWithXAJAX']) {
   	        //  should call xajax
    	    	$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			   	$html .= '<img src="'.$extPath.'/pi1/icons/options.gif" alt="'.$this->pi_getLL('toggle additional fields').
					'" onclick="' . $this->prefixId . 'toggleXAJAXVisibility('.$uid.');"/></td>';
//			   	$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/options.gif" alt="'.$this->pi_getLL('toggle additional fields').
//					'" onclick="' . $this->prefixId . 'toggleXAJAXVisibility('.$uid.');" /img></a></td>';
			}
			else {
				if (count($displayFields) != 0 ){
					$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			   		$html .= '<img src="'.$extPath.'pi1/icons/options.gif" alt="'.$this->pi_getLL('toggle additional fields').
						'" onclick="toggleVisibility(\'additionalFieldsTable'.$uid.'\');"/></td>';
//			   		$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/options.gif" alt="'.$this->pi_getLL('toggle additional fields').
//						'" onclick="toggleVisibility(\'additionalFieldsTable'.$uid.'\');" /img></a></td>';
				}
			}
			$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

			$confirmMsg = '\''.$this->pi_getLL('deleteBookingConfirmation'). ' '.$sDate .' - ' . $eDate .
				' '.$bookedBy. '\'';
			if ($this->conf['displayAdditionalFieldsWithXAJAX']) {
   	        	// should call xajax
				// save button
		   		$html .= '<img src="'.$extPath.'pi1/icons/save_record.gif" alt="'.$this->pi_getLL('save record').
   					'" onclick="' . $this->prefixId . 'processSaveFormData(xajax.getFormValues(\'update'.$uid.'\'));"/></td>';
//		   		$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/save_record.gif" alt="'.$this->pi_getLL('save record').
//   					'" onclick="' . $this->prefixId . 'processSaveFormData(xajax.getFormValues(\'update'.$uid.'\'));" /img></a></td>';

				// rebook button
				$rebookBookingUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id) .
			    	'?tx_flatmgr_pi1[action]=rebookBooking'.
					'&amp;tx_flatmgr_pi1[uid]='. $uid.
					'&amp;tx_flatmgr_pi1[start]='.$sDate.
					'&amp;tx_flatmgr_pi1[end]='.$eDate.
					'&amp;tx_flatmgr_pi1[grownups]='.$grownups;
           		$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
				$html .= '<img src="'.$extPath.'pi1/icons/rebook.gif" alt="'.$this->pi_getLL('rebook record').
					'" onclick="jumpToUrl(\''.$rebookBookingUrl .'\')"/></td>';
//				$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/rebook.gif" alt="'.$this->pi_getLL('rebook record').
//					'" onclick="jumpToUrl(\''.$rebookBookingUrl .'\')"/img></a></td>';

				// delete button
           		$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
				$html .= '<img src="'.$extPath.'pi1/icons/delete_record.gif" alt="'.$this->pi_getLL('delete record').
					'" onclick="if (confirm('.$confirmMsg.')) {'. $this->prefixId . 'processDeleteFormData(xajax.getFormValues(\'update'.$uid.'\'));}"/></td></tr>';
//				$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/delete_record.gif" alt="'.$this->pi_getLL('delete record').
//					'" onclick="if (confirm('.$confirmMsg.')) {'. $this->prefixId . 'processDeleteFormData(xajax.getFormValues(\'update'.$uid.'\'));}"/img></a></td>';
			}
			else {
		   		$html .= '<img src="'.$extPath.'pi1/icons/save_record.gif" alt="'.$this->pi_getLL('save record').
					'" onclick="document.update'.$uid.'.submit();"/></td>';
//		   		$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/save_record.gif" alt="'.$this->pi_getLL('save record').
//					'" onclick="document.update'.$uid.'.submit();"/img></a></td>';

   				// rebook button
				$rebookBookingUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id);
				if (strpos($this->pi_getPageLink($GLOBALS['TSFE']->id),'?') > 0)
				$rebookBookingUrl .= '&tx_flatmgr_pi1[action]=rebookBooking';
				else
				$rebookBookingUrl .= '?tx_flatmgr_pi1[action]=rebookBooking';
				$rebookBookingUrl .= '&amp;tx_flatmgr_pi1[uid]='. $uid.
					'&amp;tx_flatmgr_pi1[start]='.$sDate.
					'&amp;tx_flatmgr_pi1[end]='.$eDate.
					'&amp;tx_flatmgr_pi1[grownups]='.$grownups;
           		$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
				$html .= '<img src="'.$extPath.'pi1/icons/rebook.gif" alt="'.$this->pi_getLL('rebook record').
					'" onclick="jumpToUrl(\''.$rebookBookingUrl .'\')"/></td>';
//				$html .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/rebook.gif" alt="'.$this->pi_getLL('rebook record').
//					'" onclick="jumpToUrl(\''.$rebookBookingUrl .'\')"/img></a></td>';

				$html .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';


				$deleteBookingUrl = '/'.$this->pi_getPageLink($GLOBALS['TSFE']->id);
				if (strpos($this->pi_getPageLink($GLOBALS['TSFE']->id),'?') > 0)
					$deleteBookingUrl .= '&tx_flatmgr_pi1[action]=deleteBooking';
				else
			    	$deleteBookingUrl .= '?tx_flatmgr_pi1[action]=deleteBooking';
		    	$deleteBookingUrl .=  '&amp;tx_flatmgr_pi1[flatUid]='. intval($this->_GP['flatUid']).
					'&amp;tx_flatmgr_pi1[uid]='.$uid.'&amp;tx_flatmgr_pi1[roomUid]=' . intval($this->_GP['roomUid']);
	   			$html .=	'<img src="'.$extPath.'pi1/icons/delete_record.gif" alt="'.
					$this->pi_getLL('delete record').
					'" onclick="if (confirm('.$confirmMsg.')){jumpToUrl(\''.$deleteBookingUrl .'\')}" /></td></tr>';
//	   			$html .=	'<img src="/typo3conf/ext/flatmgr/pi1/icons/delete_record.gif" alt="'.
//					$this->pi_getLL('delete record').
//					'"/img onclick="if (confirm('.$confirmMsg.')){jumpToUrl(\''.$deleteBookingUrl .'\')}" ></td></tr>';
			}



			if (!$this->conf['displayAdditionalFieldsWithXAJAX']) {

			// now the additional fields
			// in a extra table
			// display with style display:none
			
			if (count($displayFields) > 0)
				$html .= ($i % 2 == 0)?'<tr><td colspan="12" class="displayAdditionalFieldsEvenRow" id="additionalFieldsTable'.
				$uid.'" style="display: none;">'.'<table class="additionalFields">':'<tr><td colspan="12" class="displayAdditionalFieldsOddRow" id="additionalFieldsTable'.
				$uid.'" style="display: none;">'.'<table class="additionalFields">';

			reset($displayFields);
			while ( list ($key, $fieldName) = each($displayFields)) {
				switch ($fieldName) {
					case 'customernumber':
						if ( $this->conf['useTtaddresses']){
						    $html .= '<tr><td class="customerNumber">'.$this->pi_getLL('customerNumber').'</td><td>'.
							'<input type="text" readonly="readonly" value="'.$customerNumber.'" id="tx_flatmgr_pi1_customerNumber' . $uid .'" name="tx_flatmgr_pi1[customerNumberOld]" maxlength="20"></td></tr>';
						}
						else
						    $html .= '<tr><td class="customerNumber">'.$this->pi_getLL('customerNumber').'</td><td>'.
							'<input type="text" value="'.$customerNumber.'" name="tx_flatmgr_pi1[customerNumber]" maxlength="20"></td></tr>';
				        break;
					case 'categories':
				        $html .= '<tr><td colspan="1" class="category">'.$this->pi_getLL('category'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[category]">';
							$o = '';
							for( $j = 0;$j < count($this->input['categories']['uid']); $j++){ $o .= ($this->input['categories']['uid'][$j] == $category)?
								'<option selected="selected" value="'.$category.'">'.
								$this->input['categories']['category'][$j].'</option>':
								'<option value="'.$this->input['categories']['uid'][$j].'">'.$this->input['categories']['category'][$j].'</option>';
							}
							$o .= '</select></td></tr>';
						 $html .= $o;
				        break;
				    case 'grownups':
				        $html .= '<tr><td class="grownups">'.$this->pi_getLL('grownups'). '</td><td>'.
							'<select name="tx_flatmgr_pi1[grownups]">';
							$o = '';
							for( $j = 0;$j <= $this->input['capacity']; $j++){ $o .= ($j == $grownups)?'<option selected="selected" value="'.$j.'">'.$j.'</option>':'<option value="'.$j.'">'.$j.'</option>';
							}
							$o .= '</select></td>';
                        $html .= $o;
				        break;
					case 'childs':
				        $html .= '<tr><td class="childs">'.$this->pi_getLL('childs'). '</td><td>'.
							'<select name="tx_flatmgr_pi1[childs]">';
							$o = '';
							for( $j = 0;$j <10; $j++){ $o .= ($j == $childs)?'<option selected="selected" value="'.$j.'">'.$j.'</option>':'<option value="'.$j.'">'.$j.'</option>';
							}
							$o .= '</select></td>';
                        $html .= $o;
				        break;
					case 'memo':
				        $html .= '<tr><td class="customerNumber">'.$this->pi_getLL('memo').'</td><td>'.
							'<textarea cols="35" rows="6" name="tx_flatmgr_pi1[memo]" maxlength="255">'.$memo.'</textarea></td></tr>';
				        break;
					case 'agent':
				        $html .= '<tr><td class="agent">'.$this->pi_getLL('agent').'</td><td>'.
							'<input type="text" name="tx_flatmgr_pi1[agent]" maxlength="50" value="'.$agent.'"/></td></tr>';
				        break;
				}
			} // while

			if (count($displayFields) > 0)
           		$html .= '</table>';
			} // if (!$this->conf['displayAdditionalFieldsWithXAJAX'])
			// end of the additional fields

			if($this->conf['displayAdditionalFieldsWithXAJAX']) {
	   			// xajax starts here

   				// Make the plugin not cachable
				//$this->pi_USER_INT_obj = 1;
	        	$GLOBALS['TSFE']->set_no_cache();

				// Initialise the return variable
				$content = '';
				$sForm = '';
				$sFormResult = '';
    	   		// Include xaJax

				// The form goes here
				$content = $this->sGetForm($uid);

				// The result box goes here
				if (!t3lib_div::_GP('xajax')) {
					// We make an empty result box on the first call to send our xajax responses to
					$content .= '<div id="formResult'.$uid.'"></div>';
				} else {
					// This fallback will only be used if JavaScript doesn't work
					// Responses of xajax exit before this
					$content .= sprintf(
						'<div id="formResult%s">%s</div>',
						$uid,$this->sGetFormResult()
					);
				} // if (!t3lib_div::_GP('xajax'))
   	            $html .= ($i % 2 == 0)?'<tr><td colspan="13" class="evenRow">':'<tr><td colspan="13" class="oddRow">';
   	            $html .= 	$content.'</td></tr>';
   			} // if($this->conf['displayAdditionalFieldsWithXAJAX'])

//			if (count($displayFields) > 0)
//          changed 21.01.2010 because when no additional fields are selected,
//          the form wasn't closed correctely.
            $html .= '</table></form>';

			$subpart = $this->cObj->getSubpart($template, "###BOOKING_DATA###");
			$marks["###BOOKING###"] = $html;
			$final .= $this->cObj->substituteMarkerArray($subpart, $marks);
		} // while ( list ($id, $date) = each($this->input['starts']) )

			$header = $this->cObj->getSubpart($template, "###BOOKING_HEADER###");

            $headerMarks = array();
			/*$headerMarks= array();
			$headerMarks['###FLATNAME###']= $this->_GP["flat"];
			$headerMarks['###BOOKING_DATA_OF###']= $this->pi_getLL('booking data of');
			*/
			$footer = $this->cObj->getSubpart($template, "###BOOKING_FOOTER###");
			$backLink = '<a href="'. $this->pi_getPageLink($GLOBALS['TSFE']->id).'">' .
				$this->pi_getLL('backToTheFlats').'</a>' ;
			$footer = $this->cObj->substituteMarker ($footer, '###FOOTER###', $backLink);

			$headerSubpart = $this->cObj->getSubpart($template, "###BOOKING_HEADER###");
			$headerFinal .= $this->cObj->substituteMarkerArray($headerSubpart, $headerMarks);

   			$newBooking = $this->cObj->getSubpart($template, "###NEW_BOOKING###");
			$linkparams = array(
				'tx_flatmgr_pi1[action]' => "insertBooking",
				'tx_flatmgr_pi1[flatUid]' => intval($this->_GP['flatUid'])
				);

			$newMarks = array();
			// clear the fields
			$extPath = t3lib_extMgm::siteRelPath('flatmgr');
			$customerNumber='';
			$grownups = 0;
			$childs = 0;
			$memo = '';
			$i++; // other background-color for new data
            $new .= ($i % 2 == 0)?'<table class="newBookingData"><tr><td class="displayAdditionalFieldsEvenRow"'.
				'>'.'<table class="additionalFields">':'<table class="newBookingData"><tr><td class="displayAdditionalFieldsOddRow">'.
				 '<table class="additionalFields">';
			$new .='<tr>';
			$new .=	($i % 2 == 0)?'<td colspan="11" class="evenRow">':'<td colspan="11" class="oddRow">';
			
			$new .= '<div class="newBookingHeader"><span>'.$this->pi_getLL('newBookingHeader'). '</span>';
			$new .= '<span class="newBookingFlatHeader">'.$this->_GP['flat']. ' ' . $this->input['room'] . '</span></div></td>';
			$new .='</tr>';

			$new .='<tr>';
			$new .=	($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$new .= 	$this->pi_getLL('date from'). '</td>';
			$new .=	($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
            $new .= '<input type="hidden" name="tx_flatmgr_pi1[flatUid]" value="'.intval($this->_GP['flatUid']).'"/>';
            $new .= '<input type="hidden" name="tx_flatmgr_pi1[roomUid]" value="'.intval($this->_GP['roomUid']).'"/>';

			$new .= '<input type="text" name="'.$this->prefixId.'[start]" id="'.$this->prefixId.'_newStart" '.
					'value="'.$this->_GP['newStart'].'" size="10"  maxlength="10"/></td>';
            $new .= ($i % 2 == 0)?'<td class="calendarEvenRow">':'<td class="calendarOddRow">';
			$new .= tx_rlmpdateselectlib::getInputButton ($this->prefixId.'_newStart',$dateSelectorConf).'</td>';
            $new .=	($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$new .=  $this->pi_getLL('date to').'</td>';
            $new .=	($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';

			$new .= '<input type="text" name="'.$this->prefixId.'[end]" id="'.$this->prefixId.'_newEnd" '.
					'value="" size="10"  maxlength="10"/></td>';
			$new .= ($i % 2 == 0)?'<td class="calendarEvenRow">':'<td class="calendarOddRow">';
			$new .= tx_rlmpdateselectlib::getInputButton ($this->prefixId.'_newEnd',$dateSelectorConf).'</td>';

			$new .= ($i % 2 == 0)?'<td class="calendarEvenRow">':'<td class="calendarOddRow">';
			$new .= $this->pi_getLL('onRequest').
				'<input type="checkbox" name="'.$this->prefixId.'[onRequest]" value="1"/></td>';



			$new .=	($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			$new .= 	$this->pi_getLL('booked from').'</td>';
			$new .=	($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			// if useTtaddresses is set the name field is a select
			if ( $this->conf['useTtaddresses']) {
				// use this with jQuery autocompleter
				if ($this->conf['adminView.']['usejQuery'] && $this->conf['adminView.']['includejQuery']) {
					$new .= '<input type="hidden" value="" id="tx_flatmgr_pi1_bookedBy" name="tx_flatmgr_pi1[bookedBy]" />'.
						'<input name="'.$this->prefixId.'[bookedBy]" '.
						'id="'.$this->prefixId.'_ttaddress" size="10" '.
						' >';
				} else {
					$new .= '<input type="hidden" value="" id="tx_flatmgr_pi1_bookedBy" name="tx_flatmgr_pi1[bookedBy]" />'.
						'<select name="'.$this->prefixId.'[ttaddress]" '.
						'id="'.$this->prefixId.'_ttaddress" size="1" '.
						' onchange="tx_flatmgr_pi1SetFields();">';
					$new .= '<option value=""></option>';
					for ( $j = 0; $j < count($this->input['ttaddress']['uid']); $j++) {
						$new .= '<option value="'.$this->input['ttaddress']['firstName'][$j].
							' '.$this->input['ttaddress']['lastName'][$j].' : '.$this->input['ttaddress']['uid'][$j].'">'.
					        $this->input['ttaddress']['lastName'][$j].' '.$this->input['ttaddress']['firstName'][$j].'</option>';

					}
					$new .= '</select></td>';
				}
			}
			else
            	$new .= 	'<input type="text" name="tx_flatmgr_pi1[bookedBy]" value="" size="10" maxlength="20"/></td>';
            $new .= ($i % 2 == 0)?'<td class="evenRow"></td>':'<td class="oddRow"></td>';
            $new .= ($i % 2 == 0)?'<td class="evenRow"></td>':'<td class="oddRow"></td>';


            $new .= ($i % 2 == 0)?'<td class="evenRow">':'<td class="oddRow">';
			if ($this->conf['displayAdditionalFieldsWithXAJAX']) {
	   	        //  should call xajax to save data
			   	$new .= '<img src="'.$extPath.'pi1/icons/save_record.gif" alt="'.$this->pi_getLL('save record').
   					'" onclick="' . $this->prefixId . 'processNewFormData(xajax.getFormValues(\'newRecord\'));" /></td></tr>';
//			   	$new .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/save_record.gif" alt="'.$this->pi_getLL('save record').
//   					'" onclick="' . $this->prefixId . 'processNewFormData(xajax.getFormValues(\'newRecord\'));" /img></a></td>';
			}
			else {
	   			$new .= '<img src="'.$extPath.'pi1/icons/savedok.gif" alt="'.$this->pi_getLL('save record').
					'" onclick="document.newRecord.submit();"/></td></tr>';
//	   			$new .= '<img src="/typo3conf/ext/flatmgr/pi1/icons/savedok.gif" alt="'.$this->pi_getLL('save record').
//					'" onclick="document.newRecord.submit();"/img></a></td>';
			}
			if(count($displayFields) > 0) {
            $new .= ($i % 2 == 0)?'<tr><td colspan="11" class="displayAdditionalFieldsEvenRow" id="additionalFieldsTableNew'.
				'">'.'<table class="additionalFields">':'<tr><td colspan="11" class="displayAdditionalFieldsOddRow" id="additionalFieldsTableNew'.
				'">'.'<table class="additionalFields">';
			reset($displayFields);
			while ( list ($key, $fieldName) = each($displayFields)) {
				switch ($fieldName) {
					case 'customernumber':
						if ($this->conf['useTtaddresses']) {
							$new .= '<tr><td colspan="2" class="customerNumberReadOnly">'.$this->pi_getLL('customerNumber').'</td>'.
								'<td colspan="7"><input type="text" readonly="readonly" value="" name="tx_flatmgr_pi1[customerNumber]" '.
								'id="tx_flatmgr_pi1_customerNumber" maxlength="20" size="10"/></td></tr>';

						}
						else {
							$new .= '<tr><td colspan="2" class="customerNumber">'.$this->pi_getLL('customerNumber').'</td>'.
								'<td colspan="7"> <input type="text" value="'.$customerNumber.'" name="tx_flatmgr_pi1[customerNumber]" maxlength="20"/></td></tr>';
						}
						break;

  					case 'categories':
				        $new .= '<tr><td colspan="2" class="category">'.$this->pi_getLL('category'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[category]">';
							$o = '';
							for( $j = 0;$j < count($this->input['categories']['uid']); $j++){ $o .= ($this->input['categories']['uid'][$j] == $category)?
								'<option selected="selected" value="'.$category.'">'.
								$this->input['categories']['category'][$j].'</option>':
								'<option value="'.$this->input['categories']['uid'][$j].'">'.$this->input['categories']['category'][$j].'</option>';
							}
							$o .= '</select></td></tr>';
						 $new .= $o;
				        break;

				    case 'grownups':
				        $new .= '<tr><td colspan="2" class="grownups">'.$this->pi_getLL('grownups'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[grownups]">';
							$o = '';
							for( $j = 0;$j <= $this->input['capacity']; $j++){ $o .= ($j == 2)?'<option selected="selected" value="'.$j.'">'.$j.'</option>':'<option value="'.$j.'">'.$j.'</option>';
							}
							$o .= '</select></td></tr>';
						 $new .= $o;
				        break;
					case 'childs':
				        $new .= '<tr><td colspan="2" class="childs">'.$this->pi_getLL('childs'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[childs]">'.
							$o = '';
							for( $j = 0;$j <10; $j++){ $o .= ($j == 0)?'<option selected="selected" value="'.$j.'">'.$j.'</option>':'<option value="'.$j.'">'.$j.'</option>';
							}
							$o .= '</select></td></tr>';
						 $new .= $o;
				        break;
					case 'memo':
				        $new .= '<tr><td colspan="2" class="memo">'.$this->pi_getLL('memo').'</td>'.
							'<td colspan="7"><textarea cols="35" rows="6"  name="tx_flatmgr_pi1[memo]">'.$memo.'</textarea></td></tr>';
				        break;
					case 'agent':
				        $new .= '<tr><td colspan="2" class="agent">'.$this->pi_getLL('agent').'</td><td>'.
							'<input type="text" name="tx_flatmgr_pi1[agent]" maxlength="50" value="'.$agent.'"/></td></tr>';
				        break;
				}
			}
			$new .= '</table>';
			} // if(count($displayFields) > 0)
//   			$newMarks["###NEW_BOOKING_DATA###"] = $new . '</td></tr></table>';
   			$newMarks["###NEW_BOOKING_DATA###"] = $new . '</td></tr></table></td></tr></table>';

		/* replace some other markers */

		/* TODO: check if this is really cool */
		$urlParams = array (
		    'action' => 'insertBooking',
		);
		    
		$dummy = $this->pi_getPageLink($GLOBALS['TSFE']->id,'_top', $urlParams);
		$newBooking = $this->cObj->substituteMarker ($newBooking, '###PAGE_LINK###', $dummy);
		$newBooking = $this->cObj->substituteMarker ($newBooking, '###FLAT###', $this->_GP['flat']);
		$newBooking = $this->cObj->substituteMarker ($newBooking, '###INSERT_BOOKING###', $this->pi_getLL('insert booking'));

		// here goes the messages of new booking
        $messageBox = '<div id="formResult"></div>';

		$messagePart = $this->cObj->getSubpart($template, "###MESSAGES_DATA###");

		$marks = array();
		return $this->cObj->substituteMarkerArray($headerFinal, $headerMarks) .
			   $this->cObj->substituteMarkerArray($final, $marks).
			   $this->cObj->substituteMarker($messagePart, '###MESSAGES###',$messageBox).
			   $this->cObj->substituteMarkerArray($newBooking, $newMarks).
			   $footer;
	}

    function sGetForm($uid)	{
    	return '';
    } // function sGetForm()

	function processFormData($data) {

        // We put our incomming data to the regular piVars
        $this->piVars = $data[$this->prefixId];
		$uid = $this->piVars['uid'];
        //and proceed as a normal controller ....

        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
        }
		$model->setConfiguration($this->conf);
		$fields = $model->getAdditionalFieldsOfBooking($uid);
		$categories = $model->getCategories();
		$customerNumber = $fields['customerNumber'];
		$grownups = $fields['grownups'];
		$childs = $fields['childs'];
		$memo = $fields['memo'];
		$category = $model->getCategory($uid);
		$agent = $fields['agent'];
        $content = ($i % 2 == 0)?'<table id="additionalFields'.$uid.'" class="additionalFields">':'<table id="additionalFields'.$uid.'" class="additionalFields">';
		$displayFields = array();
		if ($this->conf['displayAdditionalFields'] > '') $displayFields = explode(',',$this->conf['displayAdditionalFields']);

			reset($displayFields);
			while ( list ($key, $fieldName) = each($displayFields)) {
				switch ($fieldName) {
					case 'customernumber':
				        $content .= '<tr>';
   						if ( $this->conf['useTtaddresses']){
							$content .= '<td colspan="2" class="customerNumber">'.$this->pi_getLL('customerNumber').'</td>'.
								'<td colspan="7"> <input type="text" readonly value="'.$customerNumber.'" name="tx_flatmgr_pi1[customerNumberOld]" id="tx_flatmgr_pi1_customerNumberOld' . $uid . '"></td></tr>';
						}
						else
							$content .= '<td colspan="2" class="customerNumber">'.$this->pi_getLL('customerNumber').'</td>'.
								'<td colspan="7"> <input type="text" value="'.$customerNumber.'" name="tx_flatmgr_pi1[customerNumber]" id="tx_flatmgr_pi1_customerNumber' . $uid . '"></td></tr>';
				        break;

					case 'categories':
				        $content .= '<tr><td colspan="2" class="category">'.$this->pi_getLL('category'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[category]">';
							$o = '';
							for( $j = 0;$j < count($categories['uid']); $j++){ $o .= ($categories['uid'][$j] == $category)?'<option selected value="'.$category.'">'.
								$categories['category'][$j].'</option>':'<option value="'.$categories['uid'][$j].'">'.$categories['category'][$j].'</option>';
							}
							$o .= '</select></td></tr>';
						 $content .= $o;
				        break;


				    case 'grownups':
				        $content .= '<tr><td colspan="2" class="grownups">'.$this->pi_getLL('grownups'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[grownups]">';
							$o = '';
							for( $j = 0;$j <= $this->input['capacity']; $j++){ $o .= ($j == $grownups)?'<option selected value="'.$j.'">'.$j.'</option>':'<option value="'.$j.'">'.$j.'</option>';
							}
							$o .= '</select></td></tr>';
						 $content .= $o;
				        break;
					case 'childs':
				        $content .= '<tr><td colspan="2" class="childs">'.$this->pi_getLL('childs'). '</td>'.
							'<td><select name="tx_flatmgr_pi1[childs]">'.
							$o = '';
							for( $j = 0;$j <10; $j++){ $o .= ($j == $childs)?'<option selected value="'.$j.'">'.$j.'</option>':'<option value="'.$j.'">'.$j.'</option>';
							}
							$o .= '</select></td></tr>';
						 $content .= $o;
				        break;
					case 'memo':
				        $content .= '<tr><td colspan="2" class="customerNumber">'.$this->pi_getLL('memo').'</td>'.
							'<td colspan="7"><textarea cols="35" rows="6" name="tx_flatmgr_pi1[memo]">'.$memo.'</textarea></td></tr>';
				        break;
					case 'agent':
				        $content .= '<tr><td colspan="2" class="agent">'.$this->pi_getLL('agent').'</td><td>'.
							'<input type="text" name="tx_flatmgr_pi1[agent]" maxlength="50" value="'.$agent.'"/></td></tr>';
				        break;
				}
			}
//			$content .= '</table>';

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
        $objResponse->addAssign("formResult".$uid, "innerHTML", $content);
		// clear earlier messages
        $objResponse->addAssign('formResult', 'innerHTML', '');

        //return the XML response
        return $objResponse->getXML();

        // The $xajax->processRequests will send it and exit hereafter.

        // To learn about 17 kinds of tx_xajax_response()-functions
        // have a look at the tx_xajax_response.inc.php
    }

    function processSaveFormData($data) {

        // We put our incomming data to the _GP
        $this->_GP = $data[$this->prefixId];
        //and proceed as a normal controller ....
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $controller = t3lib_div::makeInstance('tx_flatmgr_admin_controller');
        } else {
            $controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_controller');
        }
		$uid = intval($this->_GP['uid']);
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
        }
		$model->setConfiguration($this->conf);

		// now testing the data to correctness
		$this->_GP['bookingId'] = $this->_GP['uid'];
		$content = $this->isDataOk($model);
		//$content = $this->_GP['start'];

		if ($content == '') {
			list($d, $m, $y) =	explode(".", $this->_GP['start']);
			$d1 = mktime(0,0,0,$m,$d,$y);
			list($d, $m, $y) =	explode(".", $this->_GP['end']);
			$d2 = mktime(0,0,0,$m,$d,$y);

			$model->updateBooking($this->_GP['uid'],$d1,$d2,
				$this->_GP['bookedBy'],
				$this->_GP['customerNumber'],$this->_GP['grownups'],$this->_GP['childs'],$this->_GP['memo'],
				$this->_GP['category'], $this->_GP['agent'], $this->_GP['onRequest']);
			if ( $controller->isBeUser() != 1 && $this->conf['showWarnings'])
				$content .= $controller->showWarning('backendUserRequired');
			// everything allright
			$content .= $controller->showMessage('dataRecordSaved');
		}

        // Instantiate the tx_xajax_response object
        $objResponse = new tx_xajax_response();

		// set the charset encoding
		if ($GLOBALS['TSFE']->localeCharset != NULL)
        	$objResponse->setCharEncoding($GLOBALS['TSFE']->localeCharset); //
        else
            $objResponse->setCharEncoding('utf-8');

		// Add the content to or Result Box: #formResult
		//$content = "saved...";
        $objResponse->addAssign('formResult'.$uid, 'innerHTML', $content);

		// clear earlier messages
        $objResponse->addAssign('formResult', 'innerHTML', '');

        //return the XML response
        return $objResponse->getXML();


	}

    function processNewFormData($data) {

        // We put our incomming data to the regular piVars
        $this->_GP = $data[$this->prefixId];
        //and proceed as a normal controller ....
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $controller = t3lib_div::makeInstance('tx_flatmgr_admin_controller');
        } else {
            $controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_controller');
        }
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
        }
		$model->setConfiguration($this->conf);
		$model->setGPvars($this->_GP);
		$content = $this->isDataOk($model);


		if ($this->_GP['bookedBy'] == '' || $this->_GP['bookedBy'] == 'noData')
			$content .= $controller->showError('pleaseSelectCustomer');
		if ($this->conf['useTtaddresses'] && $this->_GP['customerNumber'] == '')
			$content .= $controller->showError('pleaseSelectCustomer');

		if ($content == '') {
			$model->insertBooking($this->_GP['flatUid']);
			$content = $controller->showMessage('newBookingInserted');
		}


         // Instantiate the tx_xajax_response object
        $objResponse = new tx_xajax_response();
		// set the charset encoding
		if ($GLOBALS['TSFE']->localeCharset != NULL)
        	$objResponse->setCharEncoding($GLOBALS['TSFE']->localeCharset); //
        else
            $objResponse->setCharEncoding('utf-8');
		// Add the content to or Result Box: #formResult
        $objResponse->addAssign('formResult', 'innerHTML', $content);
        //return the XML response
        return $objResponse->getXML();
	}

    function processDeleteFormData($data) {

        // We put our incomming data to the regular piVars
        $this->_GP = $data[$this->prefixId];
        //and proceed as a normal controller ....
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $controller = t3lib_div::makeInstance('tx_flatmgr_admin_controller');
        } else {
            $controller = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_controller');
        }
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
        }
		$model->setConfiguration($this->conf);
		$model->setGPvars($this->_GP);
		$model->deleteBooking($this->_GP['uid']);
		$content = $controller->showMessage('bookingRecordDeleted');

		 // Instantiate the tx_xajax_response object
        $objResponse = new tx_xajax_response();
		// set the charset encoding
		if ($GLOBALS['TSFE']->localeCharset != NULL)
        	$objResponse->setCharEncoding($GLOBALS['TSFE']->localeCharset); //
        else
            $objResponse->setCharEncoding('utf-8');
		// Add the content to or Result Box: #formResult
//        $objResponse->addAssign('formResult'.$this->_GP['uid'], 'innerHTML', $content);
		// remove row from the display
        $objResponse->addAssign('update'.$this->_GP['uid'], 'innerHTML', '');

		// enable for debug
		$objResponse->addAssign('formResult', 'innerHTML', $content);
		// clear earlier messages
		return $objResponse->getXML();
	}

	/* Returns html of bookings in a month
	 * for all flats
	 *
	 *
	 *
	 * @return 	String		Generated HTML Code
	 */
	function displayMonthlyBooking() {
		// get the years of booking
		// display month-table
		$extPath = t3lib_extMgm::siteRelPath('flatmgr');
		$urlArray = array (
		    'tx_flatmgr_pi1[action]' => 'monthlyOverview',
            'tx_flatmgr_pi1[flatUid]' => $this->_GP['flatUid'],
            'tx_flatmgr_pi1[year]' => $this->_GP['year'],
            'tx_flatmgr_pi1[month]' => $this->_GP['month'],
		);
   		$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_monthlyOverview'] =
			'<script src="'.$extPath.'pi1/tooltiphelper.js" type="text/javascript"></script>';
   		$GLOBALS["TSFE"]->additionalHeaderData['tx_flatmgr_pi1_monthlyOverview'] .=
			'<script type="text/javascript">
			/*<![CDATA[*/

			function reload(category) {
            window.location = "' . $this->pi_getPageLink($GLOBALS['TSFE']->id, "", $urlArray) . '" + "&tx_flatmgr_pi1[category] =" + category;
			//alert(category);
			}

			function showRooms(uid) {
				arr = document.getElementsByTagName("tr");
				for (i = 0; i < arr.length; i++) {
					if (arr[i].className == "roomsOfUid" + uid) {
						arr[i].style.display = "";
						document.getElementById("flatUid" + uid).innerHTML = "<img src=\"/typo3conf/ext/flatmgr/pi1/icons/hideRooms.gif\" alt=\"' . $this->pi_getLL("hideRooms") . '\" title=\"' . $this->pi_getLL("hideRooms") . '\" onclick=\"hideRooms(" + uid + ");\"/>";
					}
				}
				
			}
			function hideRooms(uid) {
				arr = document.getElementsByTagName("tr");
				for (i = 0; i < arr.length; i++) {
					if (arr[i].className == "roomsOfUid" + uid) {
						arr[i].style.display = "none";
						document.getElementById("flatUid" + uid).innerHTML = "<img src=\"/typo3conf/ext/flatmgr/pi1/icons/showRooms.gif\" alt=\"' . $this->pi_getLL("showRooms") . '\" title=\"' . $this->pi_getLL("showRooms") . '\" onclick=\"showRooms(" + uid + ");\"/>";
					}
				}
			}

			/*]]>*/
			</script>';
		$out .= '<div id="flatmgrTooltip"></div>';
		$out .= '<div id="flatmgrOrigin"></div>';
		$out .= '<div class="monthlyBooking">';
		$out .= '<table class="monthsLegend"><tr>';
		$arr = array ($this->prefixId.'[action]' => 'monthlyOverview',
			$this->prefixId.'[year]' => $this->_GP['year'],
			$this->prefixId.'[flatUid]' => $this->_GP['flatUid'],
            $this->prefixId.'[category]' => $this->_GP['category'],

		);

		for ( $m = 1; $m <= 12; $m++) {
		    if ( (int)$this->_GP['month'] != $m ){
				$arr[$this->prefixId.'[month]'] = $m;

				$mon = $this->pi_getLL(date("M", mktime(0,0,0,$m,1,$theYear)));
				$out .= '<td>'. $this->pi_linkTP($mon,$arr,1,0) .'</td>';
			}
			else
				$out .= '<td class="monthsLegendSelected">'.$this->pi_getLL(date("M", mktime(0,0,0,$m,1,$theYear))).'</td>';
		}
		$out .= '</tr></table>';
		// display booking of the month for all flats
		$lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        if ( date("L", mktime(0,0,0,1,1,$this->_GP['year'])) == 1) {
            $lengthOfMonth[2] = 29;
        }

		$out .= '<table>';
		// display the daynames
        // start of month weekday
        $w = date("w", mktime(0,0,0,(int)$this->_GP['month'],1,$this->_GP['year']));

		$weekdays = array (	0 => $this->pi_getLL('Sun'),
		                    $this->pi_getLL('Mon'),
		                    $this->pi_getLL('Tue'),
		                    $this->pi_getLL('Wed'),
		                    $this->pi_getLL('Thu'),
		                    $this->pi_getLL('Fri'),
		                    $this->pi_getLL('Sat'));
		$out .= '<tr><td></td>';
		if ($GLOBALS['TSFE']->fe_user->user['uid'])	$out .= '<td></td>';
		for ($d = 1; $d <= $lengthOfMonth[(int)$this->_GP['month']]; $d++) {
            $out .= '<td class="dayNames">'.$weekdays[$w].'</td>';
            $w++;
            if ($w == 7) $w = 0;
		}
		$out .= '</tr>';
		
		$displayFields = array();
		if ($this->conf['displayAdditionalFields'] > '') $displayFields = explode(',',$this->conf['displayAdditionalFields']);

		$bookingPageId = $this->conf['bookingPageId'];

		$f = 0;
		$bookingStart = 0;
		$bookingEnd = 0;

		while($this->input['flat']['name'][$f]) {

//debug($this->input['flat']['rooms'][$this->input['flat']['uid'][$f]]);
//		if (count($this->input['flat']['rooms'][$this->input['flat']['uid'][$f]]['roomUid']) ) { //flat with rooms
		$rooms = count($this->input['flat']['rooms'][$this->input['flat']['uid'][$f]]['roomUid']);

		if (!$rooms) $rooms = 1;
		for ($r = 0; $r < $rooms; $r++) {
		    $roomUid = $this->input['flat']['rooms'][$this->input['flat']['uid'][$f]]['roomUid'][$r];
		    $room = $this->input['flat']['rooms'][$this->input['flat']['uid'][$f]]['name'][$r];

			// $arr for the booking link
			$arr = array ($this->prefixId.'[action]' => 'showBooking',
				$this->prefixId.'[flat]' => $this->input['flat']['name'][$f]);

			$booked = 0;
			/* 20150401 infoworxx Start - new Feature - interne Links in monthly view */
			if ($this->conf['internalLinksMonthlyView']) {
				$flatLink= $this->cObj->getTypoLink($this->input['flat']['name'][$f] . ' ' . $room, 'index.php?id='. $this->conf['internalLinksMonthlyViewTarget'] .'&tx_flatmgr_pi1[flatUid]='. $this->input['flat']['uid'][$f] .'&tx_flatmgr_pi1[action]=singleView');
			} else {
				$flatLink= $this->cObj->getTypoLink($this->input['flat']['name'][$f] . ' ' . $room, $this->input['flat']['url'][$f]);
			}
			/* 20150401 infoworxx Ende */
			
			if(!$this->conf['enableContigentSearch']) {

			if ($r == 0) {
				$out .= '<tr><td class="monthlyBookingFlat"><span class="roomSwitch" id="flatUid' . $this->input['flat']['uid'][$f] . '" class="rooms">';
				$out .= ($rooms > 1 && !$GLOBALS['TSFE']->fe_user->user['uid']) ? '<img src="/typo3conf/ext/flatmgr/pi1/icons/showRooms.gif" alt="' .
					$this->pi_getLL("showRooms") . '" title="' . $this->pi_getLL("showRooms") .
					'" onclick="showRooms(' . $this->input['flat']['uid'][$f] . ');"/></span>' .
					$flatLink . '</td>' : $flatLink . '</td>';
			}

			else {
		     	if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid']) {
				$out .= '<tr class="roomsOfUid' . $this->input['flat']['uid'][$f] . '"' .
				'><td class="monthlyBookingFlat">'.$flatLink.'</td>';
				}
				else {
				$out .= '<tr class="roomsOfUid' . $this->input['flat']['uid'][$f] . '"' .
				' style="display: none;"><td class="monthlyBookingFlat">'.$flatLink.'</td>';
				}
			}
			} else {
		     	if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid']) {
				$out .= '<tr class="roomsOfUid' . $this->input['flat']['uid'][$f] . '"' .
				'><td class="monthlyBookingFlat">'.$flatLink.'</td>';
				}
				else {
				$out .= '<tr class="roomsOfUid' . $this->input['flat']['uid'][$f] . '"' .
				' ><td class="monthlyBookingFlat">'.$flatLink.'</td>';
				}
			}




			// link for booking page
			// show only when fe user is logged in
	     	if ( ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid']) || $this->conf['monthView.']['showBookingLink']) {

			$urlParams = array (
			    'tx_flatmgr_pi1[action]' => 'showBooking',
			    'tx_flatmgr_pi1[flatUid]' => $this->input['flat']['uid'][$f],
			    'tx_flatmgr_pi1[flat]' => $this->input['flat']['name'][$f],
			    'tx_flatmgr_pi1[roomUid]' => $roomUid,
			);

			$link = $this->pi_getPageLink($this->conf['bookingPageId'], '_top', $urlParams);


			if (!$this->conf['enableContigentSearch']) {
			$out .= '<td><a href="' . $link . '"><img src="/typo3conf/ext/flatmgr/pi1/icons/edit_rooms.gif" alt="' .
					$this->pi_getLL("to the bookings") . '" title="' . $this->pi_getLL("to the bookings") .
					'" /></a></td>';
			}
			else $out .= '<td></td>';
			}
			// test booking starts in earlier months
			reset($this->input['starts']);
			while (list ($id, $starts) = each($this->input['starts'])) {
                if ( $this->input['flatId'][$id] == $this->input['flat']['uid'][$f]) {
					$ends = $this->input['ends'][$id];
                    if ( $starts < mktime(0,0,0,(int)$this->_GP['month'],1,$this->_GP['year']) &&
						$ends >= mktime(0,0,0,(int)$this->_GP['month'],1,$this->_GP['year']) &&
						($this->input['roomUid'][$id] == $roomUid  || $rooms == 1)) {
    	                $booked = 1;
						$p = $id;
                    }
				}
			}
			// now the loop over the days
			$color = 0;
			$nm = ($this->_GP['month'] < 10)? '0'.(int)$this->_GP['month']:$this->_GP['month'];


			for( $day = 1; $day <=$lengthOfMonth[(int)$this->_GP['month']]; $day++) {

			reset($this->input['starts']);
			while (list ($id, $starts) = each($this->input['starts'])) {
			if ( $this->input['flatId'][$id] == $this->input['flat']['uid'][$f] &&
				$starts == mktime(0,0,0,(int)$this->_GP['month'],$day,$this->_GP['year']) &&
				($this->input['roomUid'][$id] == $roomUid || $rooms == 1)) {
			    $bookingStart = 1;
			    $booked++;
			    $color++;
				$p = $id;
			}
			}
			reset($this->input['ends']);
			while (list ($id, $ends) = each($this->input['ends'])) {
			if ( $this->input['flatId'][$id] == $this->input['flat']['uid'][$f] &&
				$ends == mktime(0,0,0,(int)$this->_GP['month'],$day,$this->_GP['year']) &&
				($this->input['roomUid'][$id] == $roomUid  || $rooms == 1)) {
			    $bookingEnd = 1;
			    $booked--;
			}
			}


     			$titleChange = '';
				$title = '';
				if ( $this->conf['showBookedBy']) {
	                $title = 'title="'.$this->input['bookedBy'][$p]; // .'"';

					if ( $this->conf['showExtendedTooltips'] == 1 /*&& $p != $pOld */) {
					    $pOld = $p; // used as a switch
						reset($displayFields);
						$ttwinText = $this->pi_getLL('bookedBy').'#'.$this->input['bookedBy'][$p].'#';
						$ttwinText .= $this->pi_getLL('bookedFrom'). date('d.m.Y',$this->input['starts'][$p]).'#';
						$ttwinText .= $this->pi_getLL('bookedTo').date('d.m.Y',$this->input['ends'][$p]).'#';
						while ( list ($key, $fieldName) = each($displayFields)) {
							switch ($fieldName) {
								case 'agent':
									$ttwinText .= $this->pi_getLL('agent').'#'.
										$this->input['agent'][$p].'#';
									break;
								case 'categories':
									$ttwinText .= $this->pi_getLL('category').'#';
									$catFound = 0;
									for( $j = 0;$j < count($this->input['categories']['uid']); $j++) {
                                        if ($this->input['category'][$p] == $this->input['categories']['uid'][$j]) {
                                        	$ttwinText .= $this->input['categories']['category'][$j].'#';
                                        	$catFound = 1;
										}
									}
									if ($catFound == 0)	 $ttwinText .= '#';

									break;
                                case 'customernumber':
									$ttwinText .= $this->pi_getLL('customerNumber').'#'.
										$this->input['customerNumber'][$p].'#';
									break;
                                case 'grownups':
									$ttwinText .= $this->pi_getLL('grownups').'#'.
										$this->input['grownups'][$p].'#';
									break;
								case 'childs':
									$ttwinText .= $this->pi_getLL('childs').'#'.
										$this->input['childs'][$p].'#';
									break;
								case 'memo':
	                				$ttwinText .= $this->pi_getLL('memo').'#'.
										str_replace(chr(10),'<br />',$this->input['memo'][$p]).'#';
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

				$nd = ($day < 10)? '0'.$day:$day;

				if (date("w", strtotime($this->_GP['year']."-".$nm."-".$nd))== 0 || date("w", strtotime($this->_GP['year']."-".$nm."-".$nd))== 6 ){
                    $weekend = 1;
                }
                else $weekend =0;


			if ($weekend == 1) {
			if ( $bookingStart == 0 && $bookingEnd == 0 && $booked == 0) {
				$arr['tx_flatmgr_pi1[newStart]'] = $nd.'.'.$nm.'.'.$this->_GP['year'];
				$out .= ($this->conf['markWeekends'])?'<td class="vacantWeekend"><div>'.
					$this->pi_LinkToPage($day,$bookingPageId,'_top',$arr).'</div></td>':'<td class="vacantDay"><div>'.
					$this->pi_LinkToPage($day,$bookingPageId,'_top',$arr).'</div></td>';
			}
			if ( $booked > 0 && $bookingStart == 0 && $bookingEnd == 0) {
				if ($this->conf['markWeekends'])
				$out .= ($color %2 == 0)?'<td class="bookedDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="bookedDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';
				else
				$out .= ($color %2 == 0)?'<td class="bookedDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="bookedDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';

			}

			if ( $bookingStart == 1 && $bookingEnd == 0) {
				if ($this->conf['markWeekends'])
			    $out .= ($color %2 == 0)?'<td class="startWeekend1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="startWeekend2"'. $title.$onClick .'><div>'.$day.'</div></td>';
				else
			    $out .= ($color %2 == 0)?'<td class="startDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="startDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';
			}
			if ( $bookingStart == 0 && $bookingEnd == 1) {
				if ($this->conf['markWeekends'])
			    $out .= ($color %2 == 0)?'<td class="endWeekend1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="endWeekend2"'. $title.$onClick .'><div>'.$day.'</div></td>';
				else
				$out .= ($color %2 == 0)?'<td class="endDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="endDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';

			}
			if ( $bookingStart == 1 && $bookingEnd == 1) {
			    $out .= ($color %2 == 0)?'<td class="bookerChangedDay12"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="bookerChangedDay21"'. $title.$onClick .'><div>'.$day.'</div></td>';
			}

			
			} // weekend == 1
			
			if ($weekend == 0) {
			if ( $bookingStart == 0 && $bookingEnd == 0 && $booked == 0) {
				$arr['tx_flatmgr_pi1[newStart]'] = $nd.'.'.$nm.'.'.$this->_GP['year'];
				$out .= '<td class="vacantDay"><div>'.
					$this->pi_LinkToPage($day,$bookingPageId,'_top',$arr).'</div></td>';
			}
			if ( $booked > 0 && $bookingStart == 0 && $bookingEnd == 0) {
			    $out .= ($color %2 == 0)?'<td class="bookedDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="bookedDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';
			}

			if ( $bookingStart == 1 && $bookingEnd == 0) {
			    $out .= ($color %2 == 0)?'<td class="startDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="startDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';
			}
			if ( $bookingStart == 0 && $bookingEnd == 1) {
			    $out .= ($color %2 == 0)?'<td class="endDay1"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="endDay2"'. $title.$onClick .'><div>'.$day.'</div></td>';
			}
			if ( $bookingStart == 1 && $bookingEnd == 1) {
			    $out .= ($color %2 == 0)?'<td class="bookerChangedDay12"'. $title.$onClick .'><div>'.$day.'</div></td>':
					'<td class="bookerChangedDay21"'. $title.$onClick .'><div>'.$day.'</div></td>';
			}

			} // weekend == 0


			$bookingStart = 0;
			$bookingEnd = 0;
			} // end day-loop
			$out .= '</tr>';

			} // room loop

			$f++;
		}


		$out .= '</table></div>';
		return $out;
	}

    	function checkAvailability() {
        $lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        $startDates = array();
        $endDates = array();
		$years = array();

        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start'])) return $this->showError('invalid start date');
        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end'])) return $this->showError('invalid end date');
        if (strlen($this->_GP['start']) != 10 ||strlen($this->_GP['end']) != 10) {
			return $this->showError('invalid date');
		}
		// check startdate < enddate
		list($d, $m, $y) =	explode(".", $this->_GP['start']);
		$d1 = mktime(0,0,0,$m,$d,$y);
        list($d, $m, $y) =	explode(".", $this->_GP['end']);
		$d2 = mktime(0,0,0,$m,$d,$y);
		if ( $d2 <= $d1) return $this->showError('invalid date');
		// to disable checks in the past
		// if ( $d1 < time()) return $this->showError('startdate in the past');
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
				$this->prefixId.'[flat]' => $this->_GP['flat'] ,
			);
			$booking = '<span class="availableFlatLink">'. $this->pi_getLL('bookingCalendar'). '</span>';
			$out  = '<tr class="availableFlat"><td>';
			$out .= $this->_GP['flat'].'</td><td>'.$this->_GP['start'].' - '.$this->_GP['end'] .'</td><td class="bookingCalendarLink">';
			$out .= $this->pi_linkToPage($booking, $calendarPageId,'', $urlParameter). '</td></tr>';
		}
		return $out;
	}


	function isDataOk(&$model) {

		if (isset($this->_GP['bookedBy']))
			if (! preg_match($this->conf['allowedChars'],$this->_GP['bookedBy']))
				$errors .= $this->controller->showError('invalid name');
        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start']))
			$errors .= $this->controller->showError('invalid start date');
        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end']))
			$errors .= $this->controller->showError('invalid end date');
   		// check startdate < enddate
		list($d, $m, $y) =	explode(".", $this->_GP['start']);
		$d1 = mktime(0,0,0,$m,$d,$y);
		list($d, $m, $y) =	explode(".", $this->_GP['end']);
		$d2 = mktime(0,0,0,$m,$d,$y);
		if ( $d2 <= $d1) $errors .=  $this->controller->showError('invalid date');

		if ( $model->testOverbooking($d1, $d2, $this->_GP['bookingId'], $this->_GP['flatUid'], $this->_GP['roomUid']))
		    $errors .= $this->controller->showError('try to overbook');

       	if (isset($this->_GP['customerNumber']))
			if (! preg_match($this->conf['allowedChars'],$this->_GP['customerNumber']))
				$errors .= $this->controller->showError('invalid customerNumber');
       	if (isset($this->_GP['grownups']))
			if (! preg_match('/^[0-9]+$/',$this->_GP['grownups']))
				$errors .= $this->controller->showError('invalid grownups');
       	if (isset($this->_GP['childs']))
			if (! preg_match('/^[0-9]$/',$this->_GP['childs']))
				$errors .= $this->controller->showError('invalid childs');
       	if (isset($this->_GP['memo']))
			if (! preg_match($this->conf['allowedChars'],$this->_GP['memo']))
				$errors .= $this->controller->showError('invalid memo');
		return $errors;
	}

} // end of class


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_admin_view.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_admin_view.php']);
}
?>