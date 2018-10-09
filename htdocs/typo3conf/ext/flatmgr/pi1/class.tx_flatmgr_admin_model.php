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
 
class tx_flatmgr_admin_model /*extends tslib_pibase*/ {

	/* DB Table Names... */
	var $flat_table	    = "tx_flatmgr_flat";
	var $book_table	    = "tx_flatmgr_book";

	var $cat_table		= "tx_jmgallery_categories";
	var $image_table	= "tx_jmgallery_images";
	var $cat_2_album	= "tx_jmgallery_categories_albums_mm";
	var $album_2_image	= "tx_jmgallery_albums_images_mm";
	var $property_table	= "tx_jmgallery_album_properties";
	var $comment_table	= "tx_jmgallery_comments";

	/* Global Configuration */
	var $conf;

	/* Set Globals Configuration ... */
	function setConfiguration($conf) {
		$this->conf = $conf;
	}
	
	/* Default Setters & Getters */
	function getUid() {
		if ($this->uid === NULL) $this->refresh();
		return $this->uid;
	}
	function setUid($id) {
		$this->uid = $id;
	}
	
	function getPid() {
		if ($this->uid === NULL) $this->refresh();
		return $this->pid;
	}

   	function setGPVars($gp) {
		$this->_GP = $gp;
	}

   	function getFlat($flatUid) {
		/* prepare query */
		$fields = "*";
		$where_clause = " uid = " . intval($flatUid) . " AND pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $this->flat_table,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		$flats = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$flat = $row["name"];
		}
		return $flat;
	}

   	function getFlats() {
		/* prepare query */
		$fields = "*";
		$where_clause = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = $this->flat_table . ".name ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $this->flat_table,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		$flats = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$flats[] .= $row["name"];
		}
		return $flats;
	}

   	function getYearsOfBooking($flat) {
    	// get the years of booking
		if ($flat != '') {
			$flatId = $this->getFlatId($flat);
        	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            	'a.startdate, a.enddate',
	            'tx_flatmgr_book a',
    	        'a.pid in ('.$this->conf['pid_list'].') and a.flatid = ' . intval($flatId) .' and a.deleted != 1 and a.hidden != 1',
        	    '',
            	'a.startdate, a.enddate',
	            ''
    	    );
        }
        else {
        	$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            	'a.startdate, a.enddate',
	            'tx_flatmgr_book a',
    	        'a.pid in ('.$this->conf['pid_list'].') and a.deleted != 1 and a.hidden != 1',
        	    '',
            	'a.startdate, a.enddate',
	            ''
    	    );
        }
		$years = array();
		$tempYears = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
			$tempYears[date('Y',$row['startdate'])] = 1;
			$tempYears[date('Y',$row['enddate'])] = 1;
		}
		while (list($id, $val) = each($tempYears))
		    $years[] .= $id;
		return $years;
	}

    function getFlatOfBookingId($bookingId){
		/* prepare query */
		$tables = "tx_flatmgr_book a, tx_flatmgr_flat b";
		$fields = "a.uid, a.flatid, b.uid, b.name";
		$where_clause = " a.uid='" . intval($bookingId) . "' and a.flatid = b.uid and a.pid IN (".$this->conf['pid_list'].") AND a.deleted='0' AND a.hidden='0'";
		$orderBy = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$flatName = $row["name"];
		}
		return $flatName;
    }

   	function getFlatsAndIds() {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
		/* prepare query */
		$table = 'tx_flatmgr_flat a, tx_flatmgr_flatcategory b';
		$fields = "distinct a.name, a.uid, a.capacity, a.url";
		$where = " a.pid IN (".$this->conf['pid_list'].") AND a.deleted='0' AND a.hidden='0'";
   		if ($this->conf['enableFeUser'] && $this->conf['displayMode'] == 'administration' && $feUserUid != 0) {
   			$where .= ' AND a.feuseruid=' . $feUserUid;
   			$where .= ' AND a.category=b.uid';

   		}
   		if ($this->conf['enableFeUser'] && $this->conf['displayMode'] == 'monthlyOverview'  && !$this->conf['enableContigentSearch']) {
   			$where .= ' AND a.category=b.uid';
		if (!$GLOBALS['TSFE']->fe_user->user['uid'])
   			$where .= ' AND b.name=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($this->_GP['category'], 'tx_flatmgr_flatcategory') ;

		}
		
     	if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid'] && !$this->conf['enableContigentSearch']) {
     	    $where .= ' AND a.feuseruid =' . $GLOBALS['TSFE']->fe_user->user['uid'];
     	}

     	if ($this->conf['enableContigentSearch'] && $this->conf['displayMode'] != 'administration') {
     	    $where .= ' AND a.uid =' . intval($this->_GP['flatUid']);
     	}


		$orderBy = "a.name ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
		$flats = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$flats["name"][] 	.= $row['name'];
       		$flats["uid"][] 	.= $row["uid"];
			$flats['capacity'][] .= $row['capacity'];
       		$flats['url'][] 	.= $row['url'];

			// get the rooms
			$flats['rooms'][$row['uid']] = $this->getRooms($row['uid']);

		}
		return $flats;
	}

	function getFlatOwner($flatUid) {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$table = 'tx_flatmgr_flat';
		$fields = 'feuseruid';
		$where = 'uid=' . intval($flatUid);
		$orderBy = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
														 $table,
														 $where,
														 "",
														 $orderBy);
		/* process query result */
		$flats = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$feUserUid = $row['feuseruid'];
		}
		return $feUserUid;
	}


   	function getRoom($roomUid) {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$table = 'tx_flatmgr_room';
		$fields = '*';
		$where = " uid='".intval($roomUid)."' and pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = 'name ASC';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
		$data = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
	        $room = $row['name'];
	    }
		return $room;
	}

   	function getRooms($flatUid) {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$table = 'tx_flatmgr_room';
		$fields = '*';
		$where = " flatuid='" . intval($flatUid) . "' and pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = 'name ASC';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
		$data = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
	        $data['roomUid'][] .= $row['uid'];
	        $data['name'][] .= $row['name'];
	        $data['capacity'][] .= $row['capacity'];
	    }
		return $data;
	}

   	function getFeUserOfFlat($flatUid) {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		/* prepare query */
		$table = 'tx_flatmgr_flat';
		$fields = 'feuseruid';
		$where = " uid='" . intval($flatUid) . "' and pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = $this->flat_table . ".name ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$feUserUid = $row['feuseruid'];
		}
	return $feUserUid;
	}

   	function getFlatCategories() {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		/* prepare query */
		$table = 'tx_flatmgr_flatcategory';
		$fields = 'uid, name';
		$where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = 'name ASC';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$data['uid'][] .= $row['uid'];
       		$data['name'][] .= $row['name'];
		}
		return $data;
	}



   	function getCapacity($flat) {
		/* prepare query */
		$table = 'tx_flatmgr_flat';
		$fields = 'capacity';
		$where_clause = " name='".$flat."' and pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = $this->flat_table . ".name ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$capacity = $row['capacity'];
		}
		return $capacity;
	}

	function getCategories() {
		/* prepare query */
		$table = 'tx_flatmgr_category';
		$fields = "*";
		$where_clause = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
		$orderBy = "name ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		$cat = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$cat['uid'][] .= $row["uid"];
       		$cat['category'][] .= $row["name"];
		}
		return $cat;
	}

    function getAddresses() {
		/* prepare query */
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		$table = 'tt_address a, tt_address_group_mm b';
		$fields = "distinct a.uid, a.first_name, a.last_name";
		$where_clause = " a.pid IN (".$this->conf['pid_list'].")" .
			" AND b.uid_local =a.uid" .
			" AND b.uid_foreign in (". $this->conf['ttaddressGroup'].")".
			" AND deleted='0' AND hidden='0'";
		$orderBy = "last_name ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $table,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		$data = array();
	    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$data['uid'][] .= $row["uid"];
       		$data['firstName'][] .= $row["first_name"];
       		$data['lastName'][] .= $row["last_name"];
		}
		return $data;
	}


    /*
	 * @return 	array of bookings of a year
	 * for a given flat if booking starts or ends
	 * in the given year $this->_GP['year']
 	 *
	 * @param	$flat	string
	 *
	 */
   	function getBooking($flatUid) {
		$GLOBALS['TYPO3_DB']->debugOutput = 1;
       	$startOfYear = mktime(0,0,0,1,1,$this->_GP['year']);
       	$endOfYear =  mktime(0,0,0,12,31,$this->_GP['year']);
		/* prepare query */
		$tables = "tx_flatmgr_book a, tx_flatmgr_flat b";
		$fields = "a.*";
		$where = " b.uid ='" . intval($flatUid) ."' and a.flatid = b.uid AND b.pid IN (".
					$this->conf['pid_list'].
					") AND b.deleted='0' AND b.hidden='0'" .
					" AND  ( ( a.startdate >= " . $startOfYear ." AND a.startdate <= ". $endOfYear .") OR " .
					" ( a.enddate >= " . $startOfYear ." AND a.enddate <= ". $endOfYear ."))" .
					" AND a.deleted='0' AND a.hidden='0'";
		if ($this->_GP['roomUid'] > 0)
			$where .= ' AND roomuid=' . intval($this->_GP['roomUid']);
		$orderBy = "startdate ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
		$booked = array();
		$booked['starts']   = array();
		$booked['ends']     = array();
	    $booked['uid']      = array();
    	$booked['bookedby'] = array();
	    $booked['customerNumber'] = array();
    	$booked['grownups']     = array();
	    $booked['childs']      	= array();
    	$booked['memo']  	    = array();
	    $booked['category']     = array();
	    $booked['agent']     = array();
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$booked['starts'][]  	    .= $row['startdate'];
	       	$booked['ends'][]     	    .= $row['enddate'];
  			$booked['uid'][]     	    .= $row['uid'];
    		$booked['bookedby'][] 	    .= $row['bookedby'];
		    $booked['customerNumber'][] .= $row['customernumber'];
    		$booked['grownups'][]       .= $row['grownups'];
	    	$booked['childs'][]         .= $row['childs'];;
	    	$booked['memo'][]      	    .= $row['memo'];
    		$booked['category'][]      	.= $row['category'];
    		$booked['agent'][]      	.= $row['agent'];
    		$booked['onRequest'][]     	.= $row['onrequest'];
		}
		return $booked;
	}

    /*
	 * @return 	array of bookings of a year
	 * for a given flat if booking starts or ends
	 * in the given year $this->_GP['year']
 	 *
	 * @param	$flat	string
	 *
	 */
   	function getBookingOfId($bookingUid) {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		/* prepare query */
		$tables = "tx_flatmgr_book a";
		$fields = "a.*";
		$where_clause = " a.uid ='" . intval($bookingUid) ."' AND a.pid IN (".
					$this->conf['pid_list'].
					") AND a.deleted='0' AND a.hidden='0'" ;
		$orderBy = "a.startdate ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		$booked = array();
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$booked['starts']  		    .= $row['startdate'];
	       	$booked['ends']     	    .= $row['enddate'];
  			$booked['uid']  	   	    .= $row['uid'];
    		$booked['bookedby'] 	    .= $row['bookedby'];
		    $booked['customerNumber']   .= $row['customernumber'];
    		$booked['grownups']         .= $row['grownups'];
	    	$booked['childs']           .= $row['childs'];;
	    	$booked['memo']      	    .= $row['memo'];
    		$booked['category']      	.= $row['category'];
    		$booked['agent']	      	.= $row['agent'];
		}
		return $booked;
	}



    /*
	 * @return 	array of bookings of a year and month
	 * for all flats if booking starts or ends
	 * in the time period
 	 *
	 * @param	$year	string
	 *          $month  string
	 *
	 */
   	function getMonthBooking($year, $month) {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
       	$startOfBooking = mktime(0,0,0,$month,1,$year);
       	$endOfBooking =  mktime(0,0,0,$month,31,$year);
		/* prepare query */
		$tables = "tx_flatmgr_book a, tx_flatmgr_flat b";
		$fields = "a.*";
		$where = "a.flatid = b.uid AND b.pid IN (".
					$this->conf['pid_list'].
					") AND b.deleted='0' AND b.hidden='0'" .
					" AND  ( ( a.startdate >= " . $startOfBooking ." AND a.startdate <= ". $endOfBooking .") OR " .
					" ( a.enddate >= " . $startOfBooking ." AND a.enddate <= ". $endOfBooking .") OR " .
					" ( a.startdate <= ". $startOfBooking ." AND a.enddate >= " . $startOfBooking ." AND a.enddate >= ". $endOfBooking ."))" .
					" AND a.deleted='0' AND a.hidden='0'";
     	if ($this->conf['enableFeUser'] && $GLOBALS['TSFE']->fe_user->user['uid']) {
     	    $where .= ' AND b.feuseruid =' . $GLOBALS['TSFE']->fe_user->user['uid'];
     	}
     	if ($this->conf['enableContigentSearch']) {
     	    $where .= ' AND b.uid =' . intval($this->_GP['flatUid']);
     	}


		$orderBy = "startdate ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where,
													 "",
													 $orderBy);
		/* process query result */
		$booked = array();
		$booked['starts']   = array();
		$booked['ends']     = array();
	    $booked['uid']      = array();
    	$booked['flatId']      = array();
    	$booked['bookedBy'] = array();
	    $booked['customerNumber'] = array();
    	$booked['grownups']     = array();
	    $booked['childs']      	= array();
    	$booked['memo']  	    = array();
	    $booked['category']     = array();
   		$booked['agent']	    = array();
   		$booked['roomUid']	    = array();

    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
       		$booked['starts'][]  	    .= $row['startdate'];
	       	$booked['ends'][]     	    .= $row['enddate'];
  			$booked['uid'][]     	    .= $row['uid'];
  			$booked['flatId'][]     	.= $row['flatid'];
    		$booked['bookedBy'][] 	    .= $row['bookedby'];
		    $booked['customerNumber'][] .= $row['customernumber'];
    		$booked['grownups'][]       .= $row['grownups'];
	    	$booked['childs'][]         .= $row['childs'];;
	    	$booked['memo'][]      	    .= $row['memo'];
    		$booked['category'][]      	.= $row['category'];
    		$booked['agent'][]	      	.= $row['agent'];
    		$booked['roomUid'][]	   	.= $row['roomuid'];
		}
		return $booked;
	}


	/*
	* Returns the additional fields in an array
	*
	* @params   $bookingUid     uid of the booking record
	*
	*/
   	function getAdditionalFieldsOfBooking($bookingUid) {
	/* prepare query */
	$tables = "tx_flatmgr_book a";
	$fields = "a.uid, a.customernumber, a.grownups, a.childs, a.memo, a.agent";
	$where_clause = " a.uid ='". intval($bookingUid) ."'" .
					" AND a.deleted='0' AND a.hidden='0'";
	$orderBy = '';
	$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
	/* process query result */
	$booked = array();
    $booked['customerNumber']= '';
    $booked['grownups']      = 0;
    $booked['childs']      	 = 0;
    $booked['memo']      	 = '';
    $booked['agent']      	 = '';
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
	    $booked['customerNumber']   = $row['customernumber'];
    	$booked['grownups']         = $row['grownups'];
	    $booked['childs']           = $row['childs'];;
    	$booked['memo']       	    = $row['memo'];
    	$booked['agent']       	    = $row['agent'];
	}
	return $booked;
	}

   	function getCategory($bookingUid) {
		/* prepare query */
		$tables = "tx_flatmgr_book a";
		$fields = "a.uid, a.category";
		$where_clause = " a.uid ='". intval($bookingUid) ."'" .
					" AND a.deleted='0' AND a.hidden='0'";
		$orderBy = '';
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
	    	$category = $row['category'];
		}
		return $category;
	}

   	function deleteBooking($uid) {
		/* prepare update query */
		$table = "tx_flatmgr_book";
		$fields = array();
		$fields["hidden"] 	= 1;
		$where = " uid='" . intval($uid) . "'";
		/* execute delete query */
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
	return;
	}

    function getFlatId($flat) {
	/* prepare query */
		$tables = "tx_flatmgr_flat";
		$fields = "uid";
		$where_clause = " name ='". $flat ."' AND pid IN (".
					$this->conf['pid_list'].
					") AND deleted='0' AND hidden='0'";
		$orderBy = "";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
			$id = $row['uid'];
		}
		return $id;
	}

	function getRebooking() {
		/* prepare start- and end-dates */
    	list($day, $month, $year) =	explode(".", $this->_GP['start']);
		$d1 = mktime(0,0,0,$month,$day,$year);
		list($day, $month, $year) =	explode(".", $this->_GP['end']);
		$d2 = mktime(0,0,0,$month,$day,$year);

		$flats = $this->getFlatsAndIds();
//debug($flats);
		while ( list($id, $flat) = each($flats['name'])) {
			if ( $this->_GP['grownups'] > $flats['capacity'][$id]) {
				$flats['possible'][$id] = 0;
				continue;
			}
			$flatId = $flats['uid'][$id];

			// flat with rooms
			if ($flats['rooms'][$flatId]['roomUid'] > 0) {
//debug($flats['rooms'][$flatId]['roomUid']);
			for ($j = 0; $j < count($flats['rooms'][$flatId]['roomUid']); $j++) {
			if ( $this->_GP['grownups'] > $flats['rooms'][$flatId]['roomUid'][$j]) {
//				$flats['possible'][$id] = 0;
				continue;
			}


			$roomUid = $flats['rooms'][$flatId]['roomUid'][$j];
			$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
			if ($bookingId == '') $bookingId = 0;
			/* prepare start- and end-dates */
			$tables = "tx_flatmgr_book a";
			$fields = "a.startdate, a.enddate, a.uid";
			$where = " a.flatid = '" . intval($flatId) ."' AND a.pid IN (".
				$this->conf['pid_list'].
				") AND a.deleted='0' AND a.hidden='0'";
			$where .= ' AND roomuid = ' . intval($roomUid);
				
			$orderBy = "a.startdate ASC";
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where,
													 "",
													 $orderBy);
			/* process query result */
			$booked = array();
			$booked['starts']   = array();
			$booked['ends']     = array();
    		$booked['uid']      = array();
			$flag = 0;
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
				if ( $d1 >= $row['startdate'] && $d1 <= $row['enddate'] - 3600 * 24)
				    $flag = 1;
				if ( $d1 < $row['startdate'] && $d2 > $row['startdate'] && $d2 <= $row['enddate'] - 3600 * 24)
				    $flag = 1;
	            if ( $d1 < $row['startdate'] && $d2 > $row['startdate'])
				    $flag = 1;
			}
			if ($flag == 1) $flats['rooms'][$flatId]['possible'][$j] = 0; else $flats['rooms'][$flatId]['possible'][$j] = 1;


			} // room loop
			} // if ($flats['rooms'][$flatId]['roomUid'] > 0)

			//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
			if ($bookingId == '') $bookingId = 0;
			/* prepare start- and end-dates */
			$tables = "tx_flatmgr_book a";
			$fields = "a.startdate, a.enddate, a.uid";
			$where_clause = " a.flatid = '" . intval($flatId) ."' AND a.pid IN (".
				$this->conf['pid_list'].
				") AND a.deleted='0' AND a.hidden='0'";
			$orderBy = "a.startdate ASC";
			$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
			/* process query result */
			$booked = array();
			$booked['starts']   = array();
			$booked['ends']     = array();
    		$booked['uid']      = array();
			$flag = 0;
			while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
				if ( $d1 >= $row['startdate'] && $d1 <= $row['enddate'] - 3600 * 24)
				    $flag = 1;
				if ( $d1 < $row['startdate'] && $d2 > $row['startdate'] && $d2 <= $row['enddate'] - 3600 * 24)
				    $flag = 1;
	            if ( $d1 < $row['startdate'] && $d2 > $row['startdate'])
				    $flag = 1;
			}
			if ($flag == 1) $flats['possible'][$id] = 0; else $flats['possible'][$id] = 1;
		}
//debug($flats);
			return $flats;
	}

    function getCSVData($flatUid) {
	/* prepare query */
		$GLOBALS['TYPO3_DB']->debugOutput = 1;
		$tables = "tx_flatmgr_book a, tt_address b";
		$fields = '*';
		$where = " a.flatid ='". intval($flatUid) ."' AND a.pid IN (".
					$this->conf['pid_list'].
					") AND a.deleted='0' AND a.hidden='0'";
		$where .= ' AND b.uid=a.customernumber';
		$start = mktime(0,0,0,1,1, $this->_GP['year']);
		$end = mktime(23, 59, 59, 12, 31, $this->_GP['year']);
		$where .= ' AND ( (startdate >=' . $start . ' AND enddate <=' . $end . ') OR ' .
			' (startdate <= '. $start . ' AND enddate >= ' . $start .' AND enddate <= ' . $end .') OR' .
			' (startdate >= '. $start . ' AND startdate <= ' . $end . ') OR' .
			' (enddate >= '. $start . ' AND enddate <= ' . $end . '))';

		$orderBy = "startdate";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where,
													 "",
													 $orderBy);
    	while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
			$data['startDate'][] 	.= date("d.m.Y", $row['startdate']);
			$data['endDate'][] 		.= date("d.m.Y", $row['enddate']);
			$data['memo'][] 		.= $row['memo'];
			$data['customerNumber'][] .= $row['customernumber'];
			$data['gender'][] 		.= $row['gender'];
			$data['firstname'][] 	.= $row['first_name'];
			$data['lastname'][] 	.= $row['last_name'];
			$data['company'][] 		.= $row['company'];
			$data['address'][] 		.= $row['address'];
			$data['zipcode'][] 		.= $row['zip'];
			$data['city'][] 		.= $row['city'];
			$data['country'][] 		.= $row['country'];


		}
		return $data;
	}




   	function insertBooking($flatUid) {
		/* prepare start- and end-dates */
    	list($day, $month, $year) =	explode(".", $this->_GP['start']);
		$sDate = mktime(0,0,0,$month,$day,$year);
		list($day, $month, $year) =	explode(".", $this->_GP['end']);
		$eDate = mktime(0,0,0,$month,$day,$year);

//		$flatId = (int)$this->getFlatId($flat);
		$bookedBy = $this->_GP['bookedBy'];
		/* prepare insert query */
		$fields = array();
		$fields["pid"]			= $this->conf['pid_list'];
		$fields["startdate"]	= $sDate;
		$fields["enddate"]		= $eDate;
		$fields["flatid"]		= intval($flatUid);
		$fields["roomuid"]		= intval($this->_GP['roomUid']);
		$fields["bookedby"]		= $bookedBy;
		$fields["customernumber"]= $this->_GP['customerNumber'];
        $fields["grownups"]		= $this->_GP['grownups'];
        $fields["childs"]		= $this->_GP['childs'];
        $fields["memo"]			= $this->_GP['memo'];
        $fields["category"]		= $this->_GP['category'];
        $fields["agent"]		= $this->_GP['agent'];
        $fields["onrequest"]	= $this->_GP['onRequest'];
		$fields["tstamp"]		= time();
		$fields["crdate"]		= time();
		$fields["cruser_id"]	= $GLOBALS['TSFE']->beUserLogin;

		/* execute query */
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_flatmgr_book",
        		 $fields);

		/* update object information */
		$this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
	}

	/*
	* Returns the result of the overbooking test
	*
	* @params   $d1, $d2	time     start- and end-date
	*           $bookingId  integer bookingId
	*
	*/
   	function testOverbooking($d1, $d2, $bookingId = 0, $flatUid, $roomUid) {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		if ($bookingId == '') $bookingId = 0;
		/* prepare start- and end-dates */
//		$flatId = $this->getFlatId($flat);
		$tables = "tx_flatmgr_book a";
		$fields = "a.startdate, a.enddate, a.uid";
		$where_clause = " a.flatid = '" . intval($flatUid) ."' AND roomuid='" . intval($roomUid) . "' AND a.pid IN (".
				$this->conf['pid_list'].
				") AND uid !='".$bookingId."' AND a.deleted='0' AND a.hidden='0'";
		$orderBy = "a.startdate ASC";
		$result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
													 $tables,
													 $where_clause,
													 "",
													 $orderBy);
		/* process query result */
		$booked = array();
		$booked['starts']   = array();
		$booked['ends']     = array();
    	$booked['uid']      = array();
		while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
			if ( $d1 >= $row['startdate'] && $d1 <= $row['enddate'] - 3600 * 24)
			    return 1;
			if ( $d1 < $row['startdate'] && $d2 > $row['startdate'] && $d2 <= $row['enddate'] - 3600 * 24)
			    return 1;
            if ( $d1 < $row['startdate'] && $d2 > $row['startdate'])
                return 1;
		}
		return 0;
	}


  	function insertFlat($flat, $capacity) {
		/* prepare start- and end-dates */

		$feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];

   		/* prepare insert query */
		$fields = array();
		$fields["pid"]			= (int)$this->conf['pid_list'];
		$fields["name"]			= (string)$flat;
		$fields["capacity"]		= (int)$capacity;
        $fields["tstamp"]		= time();
		$fields["crdate"]		= time();
		$fields["cruser_id"]	= $GLOBALS['TSFE']->beUserLogin;

        $fields["feuseruid"]	= $feUserUid;
        $fields["category"]	= $this->_GP['flatCategory'];

		/* execute query */
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_flatmgr_flat",
        		 $fields);

		/* update object information */
		$this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
	}
	

  	function insertRoom() {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;

		$feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];

   		/* prepare insert query */
		$fields = array();
		$fields["pid"]			= (int)$this->conf['pid_list'];
		$fields["flatuid"]		= intval($this->_GP['flatUid']);
		$fields["name"]			= $this->_GP['name'];
		$fields["capacity"]		= intval($this->_GP['capacity']);
        $fields["tstamp"]		= time();
		$fields["crdate"]		= time();
		$fields["cruser_id"]	= $GLOBALS['TSFE']->beUserLogin;

		/* execute query */
		$result = $GLOBALS['TYPO3_DB']->exec_INSERTquery('tx_flatmgr_room',
        		 $fields);
		//debug($GLOBALS['TYPO3_DB']->sql_error());

		/* update object information */
		$this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
	}


    /*
	 * @return 	null
	 *
 	 * Updates the tx_flatmgr_flat table
	 * with new flatname and manage also the history
	 * if a backend login is active
	 *
	 */
	function updateFlat($flatId, $flatName, $capacity) {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		
        $this->checkStoredRecords = true;
	  	/* prepare update query */
		$table = "tx_flatmgr_flat";
		$fields = array();
		$fields['name']	  	= $flatName;
		$fields['capacity']	= $capacity;
		$fields['tstamp'] 	= time();
		$where 				= 'uid=' . intval($flatId);
		/* execute update query */

		if ($GLOBALS['TSFE']->beUserLogin != '') {
			// Init TCEmain object and store:
            if (version_compare(TYPO3_branch, '6.0', '<')) {
                $tce = t3lib_div::makeInstance('t3lib_TCEmain');
            } else {
                $tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
            }
        	$tce->stripslashes_values = 0;
			$GLOBALS['TSFE']->includeTCA();

			$tce->start('','');
			$tce->compareFieldArrayWithCurrentAndUnset($table, $flatId, $fields);
			$tce->updateDB($table,$flatId,$fields);
			unset($tce);
		}
		else $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
		
		return;
	}

	function updateRoom() {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;

        $this->checkStoredRecords = true;
	  	/* prepare update query */
		$table = 'tx_flatmgr_room';
		$fields = array();
		$fields['name']	  	= $this->_GP['roomName'];
		$fields['capacity']	= intval($this->_GP['capacity']);
		$fields['tstamp'] 	= time();
		$where 				= 'uid=' . intval($this->_GP['roomUid']);
		$where 				.= ' AND flatuid=' . intval($this->_GP['flatUid']);
		/* execute update query */

		if ($GLOBALS['TSFE']->beUserLogin != '') {
			// Init TCEmain object and store:
            if (version_compare(TYPO3_branch, '6.0', '<')) {
                $tce = t3lib_div::makeInstance('t3lib_TCEmain');
            } else {
                $tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
            }
        	$tce->stripslashes_values = 0;
			$GLOBALS['TSFE']->includeTCA();

			$tce->start('','');
			$tce->compareFieldArrayWithCurrentAndUnset($table, intval($this->_GP['roomUid']), $fields);
			$tce->updateDB($table,intval($this->_GP['roomUid']),$fields);
			unset($tce);
		}
		else

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);

		return;
	}



	function deleteFlat($flat) {

		$flatId = $this->getFlatId($flat);
		
  		/* prepare update query */
		$table = "tx_flatmgr_book";
		$fields = array();
		$fields["hidden"] 	= 1;
		$where = " flatid=" . intval($flatId);
		/* execute delete query */
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
		
		/* prepare update query */
		$table = "tx_flatmgr_flat";
		$fields = array();
		$fields["hidden"] 	= 1;
		$where = " uid=" . $flatId;
		/* execute delete query */
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
		return;
	}

	function deleteRoom() {
		$GLOBALS['TYPO3_DB']->debugOutput = TRUE;
		/* prepare update query */
		$table = "tx_flatmgr_room";
		$fields = array();
		$fields["hidden"] 	= 1;
		$where = " uid=" . intval($this->_GP['roomUid']);
		/* execute delete query */
		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
		return;
	}


    /*
	 * @return 	null
	 *
 	 * Updates the tx_flatmgr_book table
	 * with new data and manage also the history
	 * if a backend login is active
	 *
	 */
	function updateBooking($uid, $startDate, $endDate, $bookedBy, $customerNumber, $grownups,
		$childs, $memo, $category, $agent, $onRequest) {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;

        $this->checkStoredRecords = true;
	  	/* prepare update query */
		$table = "tx_flatmgr_book";
		$fields = array();
/*		$fields["startdate"]  = $startDate;
		$fields["enddate"]	  = $endDate;
		$fields["bookedby"]	  = $bookedBy;
		$fields["customernumber"]	  = $customerNumber;
		$fields["grownups"]	  = $grownups;
		$fields["childs"]	  = $childs;
		$fields["memo"]	 	  = $memo;
		$fields["category"]   = $category;
		$fields["tstamp"] = time();
		$where = "uid=" . $uid;
*/
		// if no detail are shown update only dates and bookedBy
		if ( $customerNumber == '' && $grownups == '' && $childs == '' && $memo == '' && $category == '') {
		$fields["startdate"]  = $startDate;
		$fields["enddate"]	  = $endDate;
		$fields["bookedby"]	  = $bookedBy;
		$fields["onrequest"]  = $onRequest;
		$fields["customernumber"]	  = $customerNumber;
		$fields["tstamp"] = time();
		$where = "uid=" . intval($uid);
		}
		else {
		$fields["startdate"]  = $startDate;
		$fields["enddate"]	  = $endDate;
		$fields["bookedby"]	  = $bookedBy;
		$fields["customernumber"]	  = $customerNumber;
		$fields["grownups"]	  = $grownups;
		$fields["childs"]	  = $childs;
		$fields["memo"]	 	  = $memo;
		$fields["category"]   = $category;
		$fields["agent"]   	  = $agent;
		$fields["onrequest"]  = $onRequest;
		$fields["tstamp"] = time();
		$where = "uid=" . intval($uid);
		}
		/* execute update query */
		if ($GLOBALS['TSFE']->beUserLogin != '') {
			// Init TCEmain object and store:
            if (version_compare(TYPO3_branch, '6.0', '<')) {
                $tce = t3lib_div::makeInstance('t3lib_TCEmain');
            } else {
                $tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
            }
        	$tce->stripslashes_values = 0;
			$GLOBALS['TSFE']->includeTCA();
			$tce->start('','');
			$tce->compareFieldArrayWithCurrentAndUnset($table, $uid, $fields);
			$tce->updateDB($table,$uid,$fields);
			unset($tce);
		}
		else $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);
		return;
	}

    /*
	 * @return 	null
	 *
 	 * Updates the tx_flatmgr_book table
	 * with new flatid and manage also the history
	 * if a backend login is active
	 *
	 */
	function rebook($bookingUid, $flatUid, $roomUid = 0) {
		//$GLOBALS['TYPO3_DB']->debugOutput = TRUE;

        $this->checkStoredRecords = true;
	  	/* prepare update query */
		$table = "tx_flatmgr_book";
		$fields = array();
		$fields['flatid']	= intval($flatUid);
		$fields['roomuid'] = intval($roomUid);
		$fields['tstamp'] 	= time();
		$where 				= 'uid=' . intval($bookingUid);
		/* execute update query */

		if ($GLOBALS['TSFE']->beUserLogin != '') {
			// Init TCEmain object and store:
            if (version_compare(TYPO3_branch, '6.0', '<')) {
                $tce = t3lib_div::makeInstance('t3lib_TCEmain');
            } else {
                $tce = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('t3lib_TCEmain');
            }
        	$tce->stripslashes_values = 0;
			$GLOBALS['TSFE']->includeTCA();

			$tce->start('','');
			$tce->compareFieldArrayWithCurrentAndUnset($table, $bookingUid, $fields);
			$tce->updateDB($table,$bookingUid,$fields);
			unset($tce);
		}
		else $GLOBALS['TYPO3_DB']->exec_UPDATEquery($table, $where, $fields);

		return;
	}


}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_admin_model.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_admin_model.php']);
}
 ?>