<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011-2013 Joachim Ruhs (postmaster@joachim-ruhs.de)
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
*  A copy is found in the textfile GPL.txt and important notices to the license
*  from the author is found in LICENSE.txt distributed with these scripts.
*
*
*  This script is distributed in the hope that it will be useful,
*  but WITHOUT ANY WARRANTY; without even the implied warranty of
*  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
*  GNU General Public License for more details.
*
*  This copyright notice MUST APPEAR in all copies of the script!
***************************************************************/
/**
* class.tx_booking_eID.php
*
*
* @author Joachim Ruhs <postmaster@joachim-ruhs.de>
*/


if (!class_exists('tslib_pibase')) require_once(PATH_tslib . 'class.tslib_pibase.php');
 
require_once(t3lib_extMgm::extPath('lang', 'lang.php'));
if (is_dir(PATH_site . 'tslib'))
	require_once(t3lib_extMgm::extPath('cms', 'tslib/class.tslib_content.php'));

$_EXTKEY = 'flatmgr';
//require_once(t3lib_extMgm::extPath('booking', 'ext_tables.php'));
unset($_EXTKEY);
//require_once(t3lib_extMgm::extPath('booking', 'tca.php'));

/**
 * FE users login statistic script.
 *
 * @author Joachim Ruhs <postmaster@joachim-ruhs.de>
 * @package TYPO3
 * @subpackage tx_booking
 */
class tx_flatmgr_eID {
	protected $ref;
	protected $pid;
	protected $conf;

	/**
	 * Initializes the class
	 *
	 * @return	void
	 */
	public function __construct() {
//		$GLOBALS['LANG']->includeLLFile('EXT:ratings/locallang_ajax.xml');

		tslib_eidtools::connectDB();
	}

	/**
	 * Main processing function of eID script
	 *
	 * @return	void
	 */
	public function main() {
	    // main is called at the end of
		// this script with $SOBE->main();

		// needed for correct encoding...
		header("Content-type:text/html; charset=utf-8");


		// Init language
		if ($GLOBALS['LANG'] instanceof language) {
			$this->language = &$GLOBALS['LANG'];
		}
		else {
            if (version_compare(TYPO3_branch, '6.0', '<')) {
                $this->language = t3lib_div::makeInstance('language');
            } else {
                $this->language = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('language');
            }
			$this->language->init($GLOBALS['TSFE']->lang);
		}

/*
		// reading data vars for $this->conf
		$arr = t3lib_div::_GP('data');
		$arr = json_decode(urldecode($arr));
		foreach(get_object_vars($arr) as $key => $value) {
			if (is_string($key) && is_string($value)) {
				//echo $key . '-' . $value;
				$this->conf[$key] = $value;
			}
			// reading data vars for $this->_GP in alternative mode
			// not used yet
			if($key == '_GP' && is_object($value)) {
				foreach(get_object_vars($value) as $key => $value) {
					if (is_string($key) && (is_string($value) || is_int($value))) {
						//echo $key . '-' . $value;
						$this->conf['_GP'][$key] = $value;
					}
				}
//			echo 'object';
			}if(is_array($value)) echo 'array';
		}
*/

   		// Get/Post Variables
		$this->_GP = t3lib_div::_GP('tx_flatmgr_pi1');
		// for alternative way
		//$this->_GP = $this->conf['_GP'];

//print_r($this->_GP);
//print_r($this->_conf);

    	// language
	    $this->lang = $this->_GP['lang'];
    	// locallang.xml parse
    	$this->LOCAL_LANG=t3lib_div::readLLfile('EXT:flatmgr/pi1/locallang.xml', $this->lang);

		switch ($this->_GP['action']) {
		    case 'checkAvailability':
				// used by extension flatmgrcalc
				$this->checkAvailability();
				$this->calculatePrice();
		    	break;
		    case 'searchAddress':
				$this->getAddress($_GET['term']);
			break;
		    default: echo 'Bad mode!';
		}
	}

	function getAddress($term) {
		$GLOBALS['TYPO3_DB']->debugOutput = 1;
		$table = 'tt_address';
		$fields = '*';
		if (preg_match('/^[0-9,]*$/', $this->_GP['pid']) == 1) {
		    // all is ok
		} else $this->_GP['pid'] = '';

		$term = strtoupper($GLOBALS['TYPO3_DB']->fullQuoteStr($term . '%', 'tt_address'));

		$where = ' upper(last_name) like "' . strtoupper($term) . '%" AND pid in (' . $this->_GP['pid'] . ') AND deleted=0';
		$orderBy = 'last_name ASC limit 10';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
											 $table,
											 $where,
											 '',
											 $orderBy);

		if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {         
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$_data = array();
				$_data['label']		=	$row['last_name'] . ' ' . $row['first_name'] . ', ' . $row['company'];
				$_data['value']		=	$row['last_name'] . ' ' . $row['first_name'] . ', ' . $row['company'];
				$_data['uid']		=	$row['uid'];
				$_rData[] = $_data;
			}
		}
		if (!is_array($_rData)) $_rData = array("0" => "noData");
	    echo json_encode($_rData);
	}
	
	function checkAvailability() {
		$GLOBALS['TYPO3_DB']->debugOutput = 1;
        // check startdate < enddate
        list($d, $m, $y) =    explode(".", $this->_GP['startDate']);
        $d1 = mktime(0,0,0,$m,$d,$y);
        list($d, $m, $y) =    explode(".", $this->_GP['endDate']);
        // we have to decrease end with one day
        $endSave = $this->_GP['endDate'];
        $d2 = mktime(12,0,0,$m,$d,$y) - 86400; // 12 hour because otherwise on day booking starting today -> error
        $this->_GP['end'] = date('d.m.Y', $d2);

        if ( $this->_GP['startDate'] == '' || $this->_GP['endDate'] == '') {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('invalidDates') . '</div>';
		    return;
		}
        if ( $d2 <= $d1) {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('invalidDates') . '</div>';
		    return;
		}
		$table = 'tx_flatmgr_book';
		$fields = '*';
		$where = 'flatid=' . intval($this->_GP['flatUid']);
		$where .= " AND ((startdate >= $d1 && startdate < $d2) ||";
		$where .= " (enddate > $d1 && enddate < $d2) ||";
		$where .= " (startdate < $d1 && enddate > $d2) ||";
		$where .= " (startdate < $d1 && enddate < $d2 && enddate > $d1)) ";
		$where .= " AND roomuid=0 AND deleted=0";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
											 $table,
											 $where,
											 '',
											 '');
		$data = array();
		$i = 0;
		if ($GLOBALS['TYPO3_DB']->sql_num_rows($result)) {         
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result)){
				$data['startDate'][$i]		=	date('d.m.Y',$row['startdate']);
				$data['endDate'][$i]		=	date('d.m.Y',$row['enddate']);
				$i++;
			}
		}
//print_r($data['startDate'][0]);
		if (count($data['startDate']) > 0) {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('bookingNotAvailable');
			echo '<span class="bookingData">' . $data['startDate'][0] . ' - ' .  $data['endDate'][0] . '</span></div>';
		    return;
		}
		return;
	}

	function calculatePrice() {
		//$feUserObj = tslib_eidtools::initFeUser();
		//print_r($feUserObj);

		list($d, $m, $y) = explode('.', $this->_GP['startDate']);
		$startYear = $y;
		$startTime = mktime(0, 0, 0, $m, $d, $y);
		list($d, $m, $y) = explode('.', $this->_GP['endDate']);
		$endYear = $y;
		$endTime = mktime(0, 0, 0, $m, $d, $y);

		if (date('w', $startTime) ==  0 || date('w', $startTime) ==  6 ||
            date('w', $endTime) ==  0 || date('w', $endTime) ==  6) {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('storeClosed') . '</div>';
        }

		$data = json_decode(urldecode($this->_GP['jsonData']));

		list($start, $end) = explode('-', $this->_GP['summerHolidays']);
		list ($d, $m, $y) = explode('.', $end);
		if ($y < 2000)$y += 2000;
		$summerEnd = mktime(0,0,0, $m, $d, $y);
		list ($d, $m) = explode('.', $start);
		$summerStart = mktime(0,0,0, $m, $d, $y);

		list($start, $end) = explode('-', $this->_GP['whitsunHolidays']);
		list ($d, $m, $y) = explode('.', $end);
		if ($y < 2000)$y += 2000;
		$whitsunEnd = mktime(0,0,0, $m, $d, $y);
		list ($d, $m) = explode('.', $start);
		$whitsunStart = mktime(0,0,0, $m, $d, $y);

		list($start, $end) = explode('-', $this->_GP['whitsunHolidays']);
//		debug($start);

//		$data = json_decode(urldecode($this->_GP['jsonData'], 1));
//print_r( $data);
/*
		print_r($data->_GP);

		foreach(get_object_vars($data) as $key => $value) {
			if (is_string($key) && is_string($value)) {
				echo 'conf: ' . $key . '-' . $value . '<br />';
				$this->conf[$key] = $value;
			}
			// reading data vars for $this->_GP in alternative mode
			// not used yet
			if($key == '_GP' && is_object($value)) {
				foreach(get_object_vars($value) as $key => $value) {
					if (is_string($key) && (is_string($value) || is_int($value))) {
						echo 'conf1: ' . $key . '-' . $value . '<br />';
						$this->conf['_GP'][$key] = $value;
					}
				}
//			echo 'object';
			}if(is_array($value)) echo 'array';
		}
*/


//		print_r($data->price);
//		print_r($data->seasonData);


		$whitsunDays = 0;
		$summerDays = 0;

		$startTime += 86400;
		// now the loop over the booking days
		for ($t = $startTime; $t <= $endTime; $t += 86400) {
			if ($t >= $whitsunStart && $t <= $whitsunEnd) $whitsunDays++;
			if ($t >= $summerStart && $t <= $summerEnd) $summerDays++;

			$days++;
			for ($i = 0; $i < count($data->title); $i++) {
//	print_r($data->price);
//	echo($data->price[$i]);
			    $data->seasonData[$i] = str_replace('\r', '', $data->seasonData[$i]);
			    $_seasonsDates = explode('\n', $data->seasonData[$i]);
			    for ($j = 0; $j < count($_seasonsDates); $j++) {
			        $_dates = explode(chr(10), $_seasonsDates[$j]);
	                for ($k = 0; $k < count($_dates); $k++) {
		                $_start = explode('-', $_dates[$k]);
//		echo '<br />Start:' . $_start[0];
//		echo '<br />END:' . $_start[1];
						// get the year  from the season dd.mm.-dd.mm.yyyy
						list($d, $m, $y) = explode('.', $_start[1]);

						list($d, $m) = explode('.', $_start[0]);
						$seasonStartTime = mktime(0, 0, 0, $m, $d, $y /*date('Y', time())*/ );
						list($d, $m, $y) = explode('.', $_start[1]);
						$seasonEndTime = mktime(0, 0, 0, $m, $d, $y /*date('Y', time())*/ );
//		echo '<br />seasonStart:' . $seasonStartTime;
//		echo '<br />seasonEnd:' . $seasonEndTime;

						if ($t >= $seasonStartTime && $t <= $seasonEndTime) {
//		echo '<br />seasonStart:' . date('d.m.Y',$seasonStartTime);
//		echo '<br />seasonEnd:' . date('d.m.Y', $seasonEndTime);

//							echo '<br />' . date('d.m.Y', $t) . ' Preis: ' . $data->title[$i] . $data->price[$i];

							// may be json_decode changed in php versions
							if (is_array($data->price))
								$price += str_replace(',', '.', $data->price[$i]);  // used for wohnwagen-guenstig-mieten
							else
								$price += str_replace(',', '.', $data->price->$i);

						}

					}

	//				print_r($dates);

			    }
			}
		}

//debug($data);
		// delivery costs
		if ($this->_GP['delivery'] == 'A')
		    $price += $data->deliveryPriceA;
		if ($this->_GP['delivery'] == 'B')
		    $price += $data->deliveryPriceB;
		if ($this->_GP['delivery'] == 'C')
		    $price += $data->deliveryPriceC;

		$rebate = 0;
		if ($this->_GP['registeredCustomer'] == 'true') $rebate = $data->rebateRegisteredCustomer;
		if ($this->_GP['earlyBirdRebate1'] == 'true' && $this->_GP['earlyBirdRebate2'] == 'false') $rebate += $data->rebateEarlyBird1;
		if ($this->_GP['earlyBirdRebate2'] == 'true' && $this->_GP['earlyBirdRebate1'] == 'false') $rebate += $data->rebateEarlyBird2;
		// long time booking

/*
		if ($days >= $data->longTimeBookingDays1 && $days < $data->longTimeBookingDays2) $rebate += $data->longTimeBookingRebate1;
		if ($days >= $data->longTimeBookingDays2 ) $rebate += $data->longTimeBookingRebate2;
*/
		if ($this->_GP['longTimeBookingRebate1'] == 'true') $rebate += $data->longTimeBookingRebate1;
		if ($this->_GP['longTimeBookingRebate2'] == 'true') $rebate += $data->longTimeBookingRebate2;

		if($this->_GP['tentOption'] != 'false' && $this->_GP['tentOption']) $price += $data->priceTentOption;


		if($this->_GP['lastMinuteRebate'] == 'true') $rebate += $data->rebateLastMinute;
//		if($this->_GP['noCleaningOutside'] == 'true') $price -= $data->noCleaningOutside;

		$officalPrice = $price;

		if($this->_GP['noCleaningOutside'] == 'true' && $this->_GP['delivery'] != 'C') $price -= $data->noCleaningOutside;

		if($this->_GP['followUpBooking'] == 'true') $price -= $data->followUpBooking;

//		$officalPrice = $price;
		$price = $price * (1 - $rebate/100);
		
		if ($this->_GP['longTimeBookingRebate1'] == 'true' && $this->_GP['longTimeBookingRebate2'] == 'true') {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('onlyOneLongTimeBookingRebate') . '</div>';
		}

        if ($this->_GP['earlyBirdRebate1'] == 'true' && $this->_GP['earlyBirdRebate2'] == 'true') {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('onlyOneEarlyBirdRebate') . '</div>';
        }
		$y = date('Y', time());
		
		if ($startYear > $y + 1 || $endYear > $y + 1 ) {
		    echo '<div class="flatmgrcalcWarning">' . $this->getLL('noSeasonDataAvailable') . '</div>';
		    exit;
		}

		if ($whitsunDays < $this->_GP['bookingMinDaysWhitsun'] && $whitsunDays > $days / 2)
		    echo '<div class="minDaysWhitsunWarning">' . $this->getLL('bookedNotEnoughWhitsunDays') . '</div>';
		else if ($summerDays < $this->_GP['bookingMinDaysSummer'] && $summerDays > $days / 2)
		    echo '<div class="minDaysSummerWarning">' . $this->getLL('bookedNotEnoughSummerDays') . '</div>';
		else if ($days < $this->_GP['bookingMinDays'])
		    echo '<div class="minDaysWarning">' . $this->getLL('bookedNotEnoughDays') . '</div>';

        echo '<div class="priceDetails"><span class="days">' . $days .' Tage</span><span class="rebate">' .  ' Rabatt:' . $rebate .'%</span><br /></div>';
		echo '<div class="flatmgrcalcTotalPrice">' . $this->getLL('flatmgrcalcPrice') . '<span class="price" id="flatmgrPrice">' . number_format($price, 2, ',', '') . ' &euro;</span></div>';
		echo '<div class="flatmgrcalcOfficalPrice">' . $this->getLL('flatmgrcalcOfficalPrice') . '<span class="price" id="flatmgrOfficalPrice">' . number_format($officalPrice, 2, ',', '') . ' &euro;</span></div>';
	}
	




	function getLL($s) {
		global $TYPO3_CONF_VARS;
		if ($TYPO3_CONF_VARS['SYS']['compat_version'] < '4.6')
		return $this->LOCAL_LANG[$this->lang][$s];
		else {
		    $_label = $this->LOCAL_LANG[$this->lang][$s];
		    return $_label[0]['target'];
		}
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

if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/class.tx_flatmgr_eID.php']) {
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/class.tx_flatmgr_eID.php']);
}

// Make instance:
if (version_compare(TYPO3_branch, '6.0', '<')) {
    $SOBE = t3lib_div::makeInstance('tx_flatmgr_eID');
} else {
    $SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_flatmgr_eID');
}
$SOBE->main();

?>