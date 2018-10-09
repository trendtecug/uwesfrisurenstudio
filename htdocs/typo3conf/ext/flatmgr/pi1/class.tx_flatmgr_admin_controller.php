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

class tx_flatmgr_admin_controller  {

	/* Extension Stuff */
	var $prefixId 		= 'tx_flatmgr_pi1';
	var $scriptRelPath 	= 'pi1/class.tx_flatmgr_admin_controller.php';
	var $extKey 		= 'flatmgr';
	

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
	        $view = t3lib_div::makeInstance('tx_flatmgr_admin_view', $this->conf);
	    } else {
	        $view = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_view', $this->conf);
	    }


		$view->setLocalLang($this->LOCAL_LANG);
//debug($this->_GP, 'controller');
//		if ($this->conf['enableFeUser'] && !$GLOBALS['TSFE']->fe_user->user['uid'])
//		    $errors = $this->showError('fe user required');
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
        }
		$model->setConfiguration($this->conf);

  		switch ($this->_GP["action"]) {
			case 'showBooking':
//						if (! preg_match($this->conf['allowedChars'],$this->_GP['flat']))
//								$errors .= $this->showError('flat input error');
						$out = '';
						$categories = $model->getCategories();
						if ($errors == '') {
							$this->_GP['flat'] = $model->getFlat($this->_GP['flatUid']);
							$out = $this->getYearList($model, $view);
							if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
							if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;
                            if (! preg_match('/^20[0-3][0-9]$/',$this->_GP['year']))
								$errors .= $this->showError('year input error');
							if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFeUserOfFlat($this->_GP['flatUid']))
								$errors .= $this->showError('invalid fe user');
							if ( $errors == '') {
								$this->_GP['flat'] = $model->getFlat($this->_GP['flatUid']);
      							$model->setGPvars($this->_GP);
								$data = $model->getBooking($this->_GP['flatUid']);
								$data['categories']['uid'] = $categories['uid'];
								$data['categories']['category'] = $categories['category'];
								$data['capacity'] = $model->getCapacity($this->_GP['flat']);
								$data['room'] = $model->getRoom($this->_GP['roomUid']);

								if ( $this->conf['useTtaddresses'] ){
									$data['ttaddress'] = $model->getAddresses();
								}
								$view->setGPvars($this->_GP);
								$view->setCode("BOOKING");
								$view->setInput($data);
								$view->setController($this);
							}
							$out .= $errors . $view->display();
						}
						return $errors . $out;
			            break;

 			case 'deleteBooking':
						//if (! preg_match($this->conf['allowedChars'],$this->_GP['flat']))
						//		$errors .= $this->showError('flat input error');
						$flat =  $this->_GP['flat'];
						if (! preg_match('/^[0-9]*$/', $this->_GP["uid"]))
								$errors .= $this->showError('flat uid input error');
						if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFeUserOfFlat($this->_GP['flatUid']))
							$errors .= $this->showError('invalid fe user');

						if ( $errors == '' ){
                        	$model->deleteBooking((int)$this->_GP['uid']);
                        }
						$out = $this->getYearList($model, $view);
						$this->_GP['action']= 'showBooking';
   						$years = $model->getYearsOfBooking($this->_GP['flat']);
						$this->_GP['years'] = $years;
						if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
						if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;
                        if (! preg_match('/^2[0-9][0-9][0-9]$/',$this->_GP['year']))
							$errors .= $this->showError('year input error');
						$model->setGPvars($this->_GP);

						$flat = $model->getFlat($this->_GP['flatUid']);
						$data = $model->getBooking($this->_GP['flatUid']);
						$categories = $model->getCategories();
						$data['categories']['uid'] = $categories['uid'];
						$data['categories']['category'] = $categories['category'];
						$data['capacity'] = $model->getCapacity($this->_GP['flat']);
						$data['room'] = $model->getRoom($this->_GP['roomUid']);
						if ( $this->conf['useTtaddresses'] ){
							$data['ttaddress'] = $model->getAddresses();
						}

						$view->setGPvars($this->_GP);
						$view->setCode("BOOKING");
						$view->setInput($data);
						return $errors . $out . $view->display();
			            break;

			case 'insertBooking':
						$model->setGPvars($this->_GP);
						if (isset($this->_GP['bookedby']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['bookedby']))
								$errors .= $this->showError('invalid name');
				        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start']))
								$errors .= $this->showError('invalid start date');
				        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end']))
								$errors .= $this->showError('invalid end date');
						if ($this->conf['useTtaddresses'] && !$this->_GP['customerNumber'])
								$errors .= $this->showError('selectCustomer');

                   		// check startdate < enddate
						list($d, $m, $y) =	explode(".", $this->_GP['start']);
						$d1 = mktime(0,0,0,$m,$d,$y);
        				list($d, $m, $y) =	explode(".", $this->_GP['end']);
						$d2 = mktime(0,0,0,$m,$d,$y);
						if ( $d2 <= $d1) $errors .= $this->showError('invalid date');
						// check if overbooking would occur
						$this->_GP['flat'] = $model->getFlat($this->_GP['flatUid']);
						// it would be better to call all functions with flatUid
						if ($model->testOverbooking($d1, $d2,0,$this->_GP['flatUid'], $this->_GP['roomUid']))
						    $errors .= $this->showError('try to overbook');

                    	if (isset($this->_GP['customerNumber']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['customerNumber']))
							$errors .= $this->showError('invalid customerNumber');
                    	if (isset($this->_GP['grownups']))
							if (! preg_match('/^[0-9]+$/',$this->_GP['grownups']))
							$errors .= $this->showError('invalid grownups');
                    	if (isset($this->_GP['childs']))
							if (! preg_match('/^[0-9]$/',$this->_GP['childs']))
							$errors .= $this->showError('invalid childs');
                    	if (isset($this->_GP['memo']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['memo']))
							$errors .= $this->showError('invalid memo');
	                 	if (isset($this->_GP['agent']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['agent']))
							$errors .= $this->showError('invalid agent');

						if ( $this->conf['useTtaddresses'] && $this->_GP['bookedBy'] == '' ){
						
						    $errors .= $this->showError('pleaseSelectCustomer');
						}
						if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFeUserOfFlat($this->_GP['flatUid']))
							$errors .= $this->showError('invalid fe user');

						if ($errors == '') $model->insertBooking($this->_GP['flatUid']);
						// stay in same year
						list ($day, $month, $year) = explode('.', $this->_GP['end']);
						$this->_GP['year'] = $year;

						$out = $this->getYearList($model, $view);
						if ( !isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
						if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;
                        if (! preg_match('/^2[0-9][0-9][0-9]$/',$this->_GP['year']))
							$errors .= $this->showError('year input error');
						$model->setGPvars($this->_GP);
						$data = $model->getBooking($this->_GP['flatUid']);
						if ( $this->conf['useTtaddresses'] ){
								$data['ttaddress'] = $model->getAddresses();
							}
						$categories = $model->getCategories();


						$data['categories']['uid'] = $categories['uid'];
						$data['categories']['category'] = $categories['category'];
						$data['capacity'] = $model->getCapacity($this->_GP['flat']);
						$data['room'] = $model->getRoom($this->_GP['roomUid']);
						$view->setGPvars($this->_GP);
						$view->setCode("BOOKING");
						$view->setInput($data);
						return $errors . $out . $view->display();
			            break;

			case 'updateBooking':
						if ( $this->isBeUser() != 1)
							$warnings .= $this->showWarning('backendUserRequired');
						$model->setGPvars($this->_GP);
						if (isset($this->_GP['bookedBy']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['bookedBy']))
								$errors .= $this->showError('invalid name');
				        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start']))
								$errors .= $this->showError('invalid start date');
				        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end']))
								$errors .= $this->showError('invalid end date');
                   		// check startdate < enddate
						list($d, $m, $y) =	explode(".", $this->_GP['start']);
						$startYear = $y;
						$d1 = mktime(0,0,0,(int)$m,(int)$d,(int)$y);
        				list($d, $m, $y) =	explode(".", $this->_GP['end']);
						$d2 = mktime(0,0,0,(int)$m,(int)$d,(int)$y);
						if ( $d2 <= $d1) return $this->showError('invalid date');
						if ( $model->testOverbooking($d1, $d2, $this->_GP['bookingId'], $this->_GP['flatUid'], $this->_GP['roomUid']))
						    $errors .= $this->showError('try to overbook');
                    	if (isset($this->_GP['customerNumber']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['customerNumber']))
							$errors .= $this->showError('invalid customerNumber');
                    	if (isset($this->_GP['grownups']))
							if (! preg_match('/^[0-9]+$/',$this->_GP['grownups']))
							$errors .= $this->showError('invalid grownups');
                    	if (isset($this->_GP['childs']))
							if (! preg_match('/^[0-9]$/',$this->_GP['childs']))
							$errors .= $this->showError('invalid childs');
                    	if (isset($this->_GP['memo']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['memo']))
							$errors .= $this->showError('invalid memo');
                    	if (isset($this->_GP['agent']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['agent']))
							$errors .= $this->showError('invalid agent');

						if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFeUserOfFlat($this->_GP['flatUid']))
							$errors .= $this->showError('invalid fe user');

						if ($errors == '') $model->updateBooking($this->_GP['bookingId'], $d1,$d2,$this->_GP["bookedBy"],
							$this->_GP["customerNumber"],$this->_GP["grownups"],$this->_GP["childs"],$this->_GP["memo"],
							$this->_GP["category"], $this->_GP["agent"], $this->_GP['onRequest'] );
						$this->_GP['flat'] = $model->getFlatOfBookingId($this->_GP['bookingId']);

						if ( !isset($this->_GP['year']) ) $this->_GP['year'] = $startYear; //date('Y');
						$out = $this->getYearList($model, $view);

						if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;
                        if (! preg_match('/^20[0-3][0-9]$/',$this->_GP['year']))
							$errors .= $this->showError('year input error');
						$model->setGPvars($this->_GP);
						$data = $model->getBooking($this->_GP['flatUid']);
						$view->setGPvars($this->_GP);
						$view->setCode("BOOKING");

						if ( $this->conf['useTtaddresses'] ){
								$data['ttaddress'] = $model->getAddresses();
							}
						$categories = $model->getCategories();
						$data['categories']['uid'] = $categories['uid'];
						$data['categories']['category'] = $categories['category'];
						$data['capacity'] = $model->getCapacity($this->_GP['flat']);

						$data['room'] = $model->getRoom($this->_GP['roomUid']);


						$view->setInput($data);
						return $errors . $warnings . $out . $view->display();
			            break;

			case 'rebookBooking':
						//debug($this->_GP, 'rebook controller');

						if ( $this->isBeUser() != 1)
							$warnings .= $this->showWarning('backendUserRequired');
						$model->setGPvars($this->_GP);
						if (isset($this->_GP['bookedBy']))
							if (! preg_match($this->conf['allowedChars'],$this->_GP['bookedBy']))
								$errors .= $this->showError('invalid name');
				        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['start']))
								$errors .= $this->showError('invalid start date');
				        if (! preg_match($this->conf['allowedDateChars'],$this->_GP['end']))
								$errors .= $this->showError('invalid end date');

						// get available flats for desired time and with capacity >= grownups
						$model->setGPvars($this->_GP);
						$data = $model->getRebooking();

						// check for rebooking possibility
						$posFlag = 0;
						while (list($id, $pos) = each($data['possible'])) {
						    if ($pos) $posFlag = 1;
						}
						if (! $posFlag) {
				 			return 	$this->showError('no rebooking possible');
				 		}

						$data['booked'] = $model->getBookingOfId($this->_GP['uid']);
						$view->setGPvars($this->_GP);
						$view->setInput($data);

						$view->setGPvars($this->_GP);
						$view->setCode("SHOWREBOOKING");

						return $errors . $warnings . $view->display();
			            break;

			case 'rebook':
						//debug($this->_GP, 'rebook controller1');
                        if (! preg_match('/^[0-9]*$/', $this->_GP["bookingUid"]))
								$errors .= $this->showError('booking uid error');
                        if (! preg_match('/^[0-9,]*$/', $this->_GP["flatUid"]))
								$errors .= $this->showError('flat uid error');

						if ( $this->_GP['flatUid'] == '')
								$errors .= $this->showError('no rebooking flat error');
						if ( $this->isBeUser() != 1 && $this->conf['showWarnings'])
							$warnings .= $this->showWarning('backendUserRequired');

						if ($this->conf['enableFeUser'] &&
							$GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFlatOwner($this->_GP['flatUid'])) {
							$errors = $this->showError('flat uid error!');
						}


						$arr = explode(',', $this->_GP['flatUid']);
						$this->_GP['flatUid'] = $arr[0];
						$this->_GP['roomUid'] = $arr[1];
						$model->setGPvars($this->_GP);

						if ( $errors == '') $model->rebook($this->_GP['bookingUid'], $this->_GP['flatUid'], $this->_GP['roomUid']);

						// Warum wird hier eine neue Instanz erzeugt?
						// Ist die alte "verbrannt" ? 
						// Bzw. wäre es nicht sinnvoller die alte zurück zu 
						// setzen (setGPvars ?) und weiter zu verwenden?
						// @todo: Testen!
			            if (version_compare(TYPO3_branch, '6.0', '<')) {
			                $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
			            } else {
			                $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
			            }
						$model->setConfiguration($this->conf);
						$data = $model->getFlatsAndIds();
						$this->setCode("DEFAULT");
						$view->setInput($data);

						return $errors . $warnings . $out . $view->display();
			            break;

   			case 'insertFlat':
						$model->setGPvars($this->_GP);
						if (! preg_match($this->conf['allowedChars'],$this->_GP['name']))
								$errors .= $this->showError('flat input error');
                        if (! preg_match('/^[0-9]*$/', $this->_GP["capacity"]))
								$errors .= $this->showError('capacity input error');

						if ( strlen($this->_GP['name']) &&  $errors == '') $model->insertFlat($this->_GP['name'],$this->_GP['capacity'] );
						$data = $model->getFlatsAndIds();
					    $data['categories'] = $model->getFlatCategories();
						$view->setGPvars($this->_GP);
						$view->setCode("DEFAULT");
						$view->setInput($data);
						if ($errors) return $errors;
						return $errors . $view->display();
			            break;
						
   			case 'deleteFlat':
         				if (! preg_match($this->conf['allowedChars'],$this->_GP['flat']))
						$errors .= $this->showError('flat input error');
						$model->setGPvars($this->_GP);
						if ($this->conf['enableFeUser'] &&
							$GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFlatOwner($this->_GP['flatUid'])) {
							$errors = $this->showError('flat uid error!');
						}
						if ($errors == '') $model->deleteFlat($this->_GP["flat"]);
						$data = $model->getFlatsAndIds();
						$view->setGPvars($this->_GP);
						$view->setCode("DEFAULT");
						$view->setInput($data);
						return $errors . $view->display();
			            break;

   			case 'updateFlat':
						if ( $this->isBeUser() != 1)
							$warnings .= $this->showWarning('backendUserRequired');

						$model->setGPvars($this->_GP);
						if (! preg_match($this->conf['allowedChars'],$this->_GP['flatName']))
								$errors .= $this->showError('flat input error');
						if (! preg_match('/^[0-9]*$/', $this->_GP["flatUid"]))
								$errors .= $this->showError('flat uid input error');
                        if (! preg_match('/^[0-9]*$/', $this->_GP["capacity"]))
								$errors .= $this->showError('capacity input error');
    					if ($this->conf['enableFeUser'] &&
							$GLOBALS['TSFE']->fe_user->user['uid'] != $model->getFlatOwner($this->_GP['flatUid'])) {
							$errors = $this->showError('flat uid error!');
						}


						if ($errors == '') $model->updateFlat($this->_GP["flatUid"], $this->_GP["flatName"], $this->_GP['capacity']);
						$data = $model->getFlatsAndIds();
						$view->setGPvars($this->_GP);
						$view->setCode("DEFAULT");
						$view->setInput($data);
						return $errors . $warnings . $view->display();
			            break;

			case 'monthlyOverview':
			            // parameters are month, year
						if (! preg_match($this->conf['allowedChars'],$this->_GP['month']))
								$errors .= $this->showError('month input error');
						$out = '';
						$categories = $model->getCategories();

						if ($errors == '') {
							if (!isset($this->_GP['year']) && $this->conf['enableContigentSearch']) {
								$this->_GP['year'] = substr($this->_GP['end'], -4);
                            	$this->_GP['month'] = substr($this->_GP['end'], 3, 2);
							}

							$out = $this->getBookingYearList($model, $view);
							if (!$this->conf['enableContigentSearch'])
								$out .= $this->getFlatCategoriesSelector($model, $view);

							if (!isset($this->_GP['year']) ) $this->_GP['year'] = date('Y');
							if (!isset($this->_GP['month']) ) $this->_GP['month'] = date('m');
							if ($this->_GP['year'] > 2037) $this->_GP['year'] = 2037;

							$model->setGPvars($this->_GP);

							$data = $model->getMonthBooking($this->_GP['year'], $this->_GP["month"]);
							$data['flat'] = $model->getFlatsAndIds();
                        	$data['categories'] = $categories;

							$view->setGPvars($this->_GP);
							$view->setCode("MONTHLYBOOKING");
							$view->setInput($data);
							$view->setController($this);
							$out .= $errors . $view->display();
						}

						return $errors . $out;
			            break;

			case 'editRooms':
						$data = $model->getRooms($this->_GP['flatUid']);
						$data['flatUid'] = $this->_GP['flatUid'];
						$view->setGPvars($this->_GP);
						$view->setCode("EDITROOMS");
						$view->setInput($data);
						if ($errors) return $errors;
						return $view->display();
			            break;

			case 'insertRoom':
						$model->setGPvars($this->_GP);
						$model->insertRoom();

						$data = $model->getRooms($this->_GP['flatUid']);
						$data['flatUid'] = $this->_GP['flatUid'];
						$view->setCode("EDITROOMS");
						$view->setInput($data);
						if ($errors) return $errors;
						return $view->display();
			            break;

			case 'deleteRoom':
						$model->setGPvars($this->_GP);
						$model->deleteRoom();

						$data = $model->getRooms($this->_GP['flatUid']);
						$data['flatUid'] = $this->_GP['flatUid'];
						$view->setGPvars($this->_GP);
						$view->setCode("EDITROOMS");
						$view->setInput($data);
						if ($errors) return $errors;
						return $view->display();
			            break;

			case 'updateRoom':
						$model->setGPvars($this->_GP);
						$model->updateRoom();

						$data = $model->getRooms($this->_GP['flatUid']);
						$data['flatUid'] = $this->_GP['flatUid'];
						$view->setCode("EDITROOMS");
						$view->setInput($data);
						$view->setGPvars($this->_GP);
						if ($errors) return $errors;
						return $view->display();
			            break;

			case 'downloadCsv':
			            $this->downloadCsv();
			            break;
			default:
						$data = $model->getFlatsAndIds();
						if ($this->conf['enableFeUser'] && !$GLOBALS['TSFE']->fe_user->user['uid'])
		    				$errors = $this->showError('fe user required');

					    $data['categories'] = $model->getFlatCategories();

						$this->setCode("DEFAULT");
						$view->setInput($data);
						if ($errors) return $errors;
						return $view->display();
		}
		
	}

	function getYearList(&$model, &$view) {
		//$years = $model->getYearsOfBooking($this->_GP['flat']);
		$years = array(	0 => date('Y') - 2,
	                1 => date('Y') - 1,
	                2 => date('Y'),
                    3 => date('Y') + 1,
                    4 => date('Y') + 2);
		$this->_GP['years'] = $years;
        $view->setGPvars($this->_GP);
		$view->setCode("SHOWYEARS");
		$view->setInput($years);
		return  $view->display();
 	}

	function getBookingYearList(&$model, &$view) {
		//$years = $model->getYearsOfBooking($this->_GP['flat']);
		$years = array(	0 => date('Y') - 2,
	                1 => date('Y') - 1,
	                2 => date('Y'),
                    3 => date('Y') + 1,
                    4 => date('Y') + 2);
		$this->_GP['years'] = $years;
        $view->setGPvars($this->_GP);
		$view->setCode("SHOWBOOKINGYEARS");
		$view->setInput($years);
		return  $view->display();
 	}

	function getFlatCategoriesSelector(&$model, &$view) {
      	if (!$this->conf['enableFeUser']) return;
	    $data = $model->getFlatCategories();
		if ($GLOBALS['TSFE']->fe_user->user['uid']) return; // show no categroy selector when fe user
		if ($this->_GP['category'] == '')
		    $this->_GP['category'] = $data['name'][0];
		$out = '<select class="categorySelect" onchange="reload(this.value);">';
		for($i = 0; $i < count($data['name']); $i++) {
			if($this->_GP['category'] == $data['name'][$i])
			$out .= '<option value="' . $data['name'][$i] . '" selected="selected">' . $data['name'][$i] .'</option>';
			else $out .= '<option value="' . $data['name'][$i] . '">' . $data['name'][$i] .'</option>';
		}
		$out .= '</select>';
		return $out;
	}


	function downloadCsv() {
	    // fetch the data of eventUid $this->_GP['eventUid']
	    // which have been confirmed by the customer
	    // confirmed == 1
        if (version_compare(TYPO3_branch, '6.0', '<')) {
            $model = t3lib_div::makeInstance('tx_flatmgr_admin_model');
        } else {
            $model = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_admin_model');
        }
		$model->setConfiguration($this->conf);
		$model->setGPvars($this->_GP);
		$data = array();
		$data = $model->getCSVData($this->_GP['flatUid']);

		$flat = $model->getFlat($this->_GP['flatUid']);
//debug($data);
//return;
		$csv = '"Buchungsbeginn","Buchungsende","Memo","Kundennr.","Anrede","Vorname","Name","Organisation","Adresse","PLZ","Stadt","Land"';


		$csv .= chr(13).chr(10);
			$k= 1;
			for ($k = 0; $k < count($data['startDate']); $k++) {
			    $csv .= '"'.$data['startDate'][$k].'",';
			    $csv .= '"'.$data['endDate'][$k].'",';
			    $csv .= '"'.utf8_decode($data['memo'][$k]).'",';
			    $csv .= '"'.utf8_decode($data['customerNumber'][$k]).'",';
				if ($data['gender'][$k] == 'f')
				$csv .= '"Frau",';
				else $csv .= '"Herr",';

			    $csv .= '"'.utf8_decode($data['firstname'][$k]).'",';
			    $csv .= '"'.utf8_decode($data['lastname'][$k]).'",';
			    $csv .= '"'.utf8_decode($data['company'][$k]).'",';
			    $csv .= '"'.utf8_decode($data['address'][$k]).'",';
			    $csv .= '"'.$data['zipcode'][$k].'",';
			    $csv .= '"'.utf8_decode($data['city'][$k]).'",';
			    $csv .= '"'.utf8_decode($data['country'][$k]).'"';
				$csv .= chr(13).chr(10);

			}


		header("Content-type: application/download");

        header('Pragma: public');
        header('Last-Modified: '.gmdate('D, d M Y H:i:s') . ' GMT');
        header('Cache-Control: no-store, no-cache, must-revalidate');
        header('Cache-Control: pre-check=0, post-check=0, max-age=0');
        header('Content-Transfer-Encoding: none');

		header("Content-type: application/octet-stream");
		header("Content-Disposition: attachment; filename=\"". $flat . '-' . $this->_GP['year'] . "-data.csv\"");

		print $csv;

		if (!$this->conf['removeExported']) exit;
	}




	function isBeUser() {
		global $HTTP_COOKIE_VARS, $key,$value;
	    $is_be_user = 0;
	    if ($HTTP_COOKIE_VARS["be_typo_user"]) {
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery("*", "be_sessions", "ses_id='".$HTTP_COOKIE_VARS["be_typo_user"]."'");
			if ($GLOBALS['TYPO3_DB']->sql_num_rows($res)==1)	{
				$be_user = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
				$is_be_user=1;
			}
		}
		return $is_be_user;
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
		'selectCustomer' => 'Please select a customer',
	    'invalid date' => 'You entered an invalid date, proper dates are in the format dd.mm.yyyy',
	    'invalid name' => 'You entered invalid character(s) in booked from, allowed are [A-z0-9 .,:;-]',
	    'invalid start date' => 'You entered an invalid start date, proper dates are in the format dd.mm.yyyy',
	    'invalid end date' => 'You entered an invalid end date, proper dates are in the format dd.mm.yyyy',
	    'flat input error' => 'You entered an invalid flat name, allowed characters are [A-z0-9 .,:;-]',
	    'year input error' => 'You entered an invalid year',
	    'capacity input error' => 'You entered an invalid flat capacity, allowed characters are [0-9]',
	    'backendUserRequired' => 'This operation needs a backend login',
	    'invalid customerNumber' => 'There are invalid character(s) in the customernumber, allowed are [A-z0-9 .,:;-]',
	    'invalid grownups' => 'There are invalid character(s) in the grownups, allowed are 0-9',
	    'invalid childs' => 'There are invalid character(s) in the childs, allowed are 0-9',
	    'invalid memo' => 'There are invalid character(s) in the memo, allowed are [A-z0-9 .,:;-]',
	    'invalid agent' => 'There are invalid character(s) in the agent, allowed are [A-z0-9 .,:;-]',
        'flat uid input error' => 'The flat uid is invalid',
        'flat uid error' => 'The flat uid is invalid',
        'booking uid error' => 'The booking uid is invalid',
        'try to overbook' => 'The dates you entered would result in an overboooking, therfore they are rejected!',
        'pleaseSelectCustomer' => 'You have to select a customer!',
	    'month input error' => 'You entered an invalid month, allowed characters are [A-z0-9 .,:;-]',
	    'no rebooking flat error' => 'You entered no flat for rebooking!',
	    'no rebooking possible' => 'For this dates and flat there is no rebooking possible!',
	    'invalid fe user' => 'Invalid FE user!',
	    'flat uid error' => 'Flat uid error!',
	    'fe user required' => 'FE User required!',

	);

	/*
	 * Returns the message wrapped in <div class="warnMessage">...</div>
	 *
	 * @params  $msg    String
	 */
	function showWarning($msg, $text='') {
	    if ($this->conf['showWarnings']) {
			return '<div class="warnMessage">' . 'flatmgr warning: '.$this->warnMessages[$msg] .
				'<br />You can disable the warnings in flexform setup.</div>';
		}
        if (isset($this->conf['displayAdditionalFieldsWithXAJAX']) &&
			!$this->conf['displayAdditionalFieldsWithXAJAX']) return;
        // with XAJAX alway return warnings
		//return '<div class="warnMessage">' . 'flatmgr warning: '.$this->warnMessages[$msg].'</div>';
		return;
	}
   	var $warnMessages = array (
	    'backendUserRequired' => 'This operation needs a backend login to insert the changes to the history tables.<br />Details are explained in the manual->reference installation.',
	    'backendUserIsNoAdmin' => 'The associated backend user %s is no admin! <br />To fix this issue, enable the admin-checkbox in the user administration.',
	);

   	function showMessage($msg, $text='') {
   		return '<div class="normalMessage">' . 'flatmgr message: '.$this->normalMessages[$msg] .'</div>';
	}


   	var $normalMessages = array (
	    'newBookingInserted' => 'New record has been inserted to the database.',
	    'dataRecordSaved' => 'The record has been saved to the database.',
	    'bookingRecordDeleted' => 'The record has been deleted from the database.',

	);


	/*
	 * Returns the date of yesterday
	 *
	 * @params  $date    date in form dd.mm.yyyy
	 */
	function getDateBefore($date){
		$lengthOfMonth = array (1 => 31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31);
        // leap year calculating....
	   	list($d, $m, $y) =	explode(".", $date);
        if ( date("L", mktime(0,0,0,1,1,$y)) == 1) {
            $lengthOfMonth[2] = 29;
        }
		$d = (int)$d;
		$m = (int)$m;
		if ( $d == 1 ) {
			if ( $m - 1 == 0 ) {
		    	$m = 12; $y = $y--; $d = 31;
		 	}
		 	else {
				$d = $lengthOfMonth[$m - 1];
				$m = $m--;
		    }
		}
		else $d--;
            $d = ($d < 10) ? '0'. $d : $d ;
            $m = ($m < 10) ? '0'. $m : $m ;
	    return $d . '.' . $m . '.' . $y;
	}

}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_admin_controller.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_admin_controller.php']);
}
 ?>