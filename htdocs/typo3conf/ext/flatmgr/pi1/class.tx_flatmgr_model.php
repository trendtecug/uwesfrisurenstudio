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
 

class tx_flatmgr_model /*extends tslib_pibase*/ {

    /* DB Table Names... */
    var $flat_table        = "tx_flatmgr_flat";
    var $book_table        = "tx_flatmgr_book";

    var $cat_table        = "tx_jmgallery_categories";
    var $image_table    = "tx_jmgallery_images";
    var $cat_2_album    = "tx_jmgallery_categories_albums_mm";
    var $album_2_image    = "tx_jmgallery_albums_images_mm";
    var $property_table    = "tx_jmgallery_album_properties";
    var $comment_table    = "tx_jmgallery_comments";

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

    function getFlatFields() {
        $res = $GLOBALS['TYPO3_DB']->admin_get_fields('tx_flatmgr_flat');
        return $res;
    }

       function getStartDates() {
        // fetching the ordered startDates
        $startDates = array();
        $where = 'b.pid in ('.$this->conf['pid_list'].') and a.name='.
            $GLOBALS['TYPO3_DB']->fullQuoteStr($this->_GP['flat'], 'tx_flatmgr_flat').' and a.uid = b.flatid' .
            ' and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 ';
        if($this->conf['hideOldBooking'])
            $where .= ' AND b.enddate > ' . time();

        if($this->conf['exactAvailabilityCheck'] && !$this->_GP['roomUid'])
            $where .= ' AND a.capacity = ' . intval($this->_GP['flatCapacity']);

        if ($this->_GP['roomUid'] > 0)
            $where .= ' AND b.roomuid = ' . intval($this->_GP['roomUid']);
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'b.startdate',
            'tx_flatmgr_flat a, tx_flatmgr_book b',
            $where, /*'b.pid in ('.$this->conf['pid_list'].') and a.name='.$GLOBALS['TYPO3_DB']->fullQuoteStr($this->_GP['flat'], 'tx_flatmgr_flat').' and a.uid = b.flatid and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 ',*/
            '',
            'b.startdate',
            ''
            );
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
              $startDates[] .= date('d.m.Y',$row['startdate']);
        }
        return $startDates;
    }
    /*
     * returns array of endDates
     *
     * @params  null
     *
     * the enddates are one day earlier than the
     * enddates in the database because the calendar view
     * loops through the year and the bookingEnd-flag is set
     * at the end of the loop, so that the endday is one day after
     * the actual day
     */
       function getEndDates() {
         // fetching the ordered endDdates
  $GLOBALS['TYPO3_DB']->debugOutput = 1;
           $startOfYear = mktime(0,0,0,1,1,$this->_GP['year']);
           $endOfYear =  mktime(0,0,0,12,31,$this->_GP['year']);
        $endDates= array();
//        $where = 'b.pid in ('.$this->conf['pid_list'].') and a.name="'.$this->_GP['flat'].'" '.
        $where = 'b.pid in ('.$this->conf['pid_list'].') and a.uid=' . intval($this->_GP['flatUid']).
            ' AND a.uid = b.flatid and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 '.
            " AND  ( ( b.startdate >= " . $startOfYear ." AND b.startdate <= ". $endOfYear .") OR " .
            " ( b.enddate >= " . $startOfYear ." AND b.enddate <= ". $endOfYear ."))";
        if($this->conf['hideOldBooking'])
            $where .= ' AND b.enddate > ' . time();


        if ($this->_GP['roomUid'] > 0)
            $where .= ' AND roomuid=' . intval($this->_GP['roomUid']);
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'b.enddate',
            'tx_flatmgr_flat a, tx_flatmgr_book b',
            /*'b.pid in ('.$this->conf['pid_list'].') and a.name="'.$this->_GP['flat'].'" '.
            'and a.uid = b.flatid and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 '.
                    " AND  ( ( b.startdate >= " . $startOfYear ." AND b.startdate <= ". $endOfYear .") OR " .
                    " ( b.enddate >= " . $startOfYear ." AND b.enddate <= ". $endOfYear ."))",*/
            $where,

            '',
            'b.enddate',
            ''
            );

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
               // because of the summer- and wintertime
               // subtract only 23h
               $endDates[] .= date('d.m.Y',$row['enddate'] - 3600 * 23);
        }
        return $endDates;
    }

    /*
     * returns array of endDates
     *
     * @params  null
     *
     * the enddates are one day earlier than the
     * enddates in the database because the calendar view
     * loops through the year and the bookingEnd-flag is set
     * at the end of the loop, so that the endday is one day after
     * the actual day
     */
       function getAllEndDates() {
         // fetching the ordered endDdates
  $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $where = 'b.pid in ('.$this->conf['pid_list'].') and a.uid='. $this->_GP['flatUid'].
            ' AND a.uid = b.flatid and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 ';
        if ($this->_GP['roomUid'] > 0)
            $where .= ' AND b.roomuid = ' . intval($this->_GP['roomUid']);

        if($this->conf['hideOldBooking'])
            $where .= ' AND b.enddate > ' . time();
        if($this->conf['exactAvailabilityCheck'] && !$this->_GP['roomUid'])
            $where .= ' AND a.capacity = ' . intval($this->_GP['flatCapacity']);
        $endDates= array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'b.enddate',
            'tx_flatmgr_flat a, tx_flatmgr_book b',
            $where,
            '',
            'b.enddate',
            ''
            );

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
               $endDates[] .= date('d.m.Y',$row['enddate']-3600 * 24);
        }
        return $endDates;
    }


       function getBookedBy() {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        // fetching the ordered start- and enddates
        $bookedBy = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'b.bookedby, b.startdate',
            'tx_flatmgr_flat a, tx_flatmgr_book b',
            'b.pid in ('.$this->conf['pid_list'].') and a.name="'.$this->_GP['flat'].'" and a.uid = b.flatid and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 ',
            '',
            'b.startdate',
            ''
            );
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
              $bookedBy[] .= $row['bookedby'];
        }
        return $bookedBy;
    }

       function getAdditionalFields() {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        // fetching the ordered start- and enddates
        $bookedBy = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'b.customernumber, b.grownups, b.childs, b.memo, b.startdate',
            'tx_flatmgr_flat a, tx_flatmgr_book b',
            'b.pid in ('.$this->conf['pid_list'].') and a.name="'.$this->_GP['flat'].'" and a.uid = b.flatid and a.hidden = 0 and a.deleted = 0 and b.hidden = 0 and b.deleted = 0 ',
            '',
            'b.startdate',
            ''
            );
        $result = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
              $result['customerNumber'][]    .= $row['customernumber'];
              $result['grownups'][]        .= $row['grownups'];
              $result['childs'][]            .= $row['childs'];
              $result['memo'][]            .= $row['memo'];
        }
        return $result;
    }



       function getYears() {
        // get the years of booking
        $years = array();
        $tempYears = array();
        $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery(
            'a.startdate, a.enddate',
            'tx_flatmgr_book a',
            'a.pid in ('.$this->conf['pid_list'].') and a.deleted != 1 and a.hidden != 1',
            '',
            'a.startdate, a.enddate',
            ''
        );

        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
            $tempYears[date('Y',$row['startdate'])] = 1;
            $tempYears[date('Y',$row['enddate'])] = 1;
        }
        while (list($id, $val) = each($tempYears))
            $years[] .= $id;
        return $years;
    }


       function getFlats() {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
         /* prepare query */
        $table = 'tx_flatmgr_flat a';
        $flats = array();
        $fields = "*";
        $where = " a.pid IN (".$this->conf['pid_list'].") AND a.deleted='0' AND a.hidden='0'";
        if ($this->_GP['flatCategoryUid'] > 0)
            $where .= ' AND a.category=' . intval($this->_GP['flatCategoryUid']);

        if($this->conf['exactAvailabilityCheck'] && $this->_GP['flatCapacity'] > 0) {
            $where .= ' AND (a.capacity = ' . intval($this->_GP['flatCapacity']) .' || (select count(*) from tx_flatmgr_room b where b.flatuid=a.uid AND b.capacity=' . $this->_GP['flatCapacity'] . ') > 0)';
        } else {
            if ($this->_GP['flatCapacity'] > 0)
                $where .= ' AND a.capacity>=' . intval($this->_GP['flatCapacity']);
        }


        if ($this->conf['enableFeUser'])
            $where .= ' AND a.feuseruid=' . $feUserUid;
        $orderBy = 'a.category, a.name ASC';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $flats['uid'][]     .= $row['uid'];
               $flats['name'][]     .= $row['name'];
               $flats['url'][]     .= $row['url'];

               $flats['email'][]     .= $this->getFeUserEmail($row['uid']);

               $flats['flatCategory'][]     .= $row['category'];
               $flats['capacity'][]         .= $row['capacity'];
        }
        return $flats;
    }
    
    function getFlatList($categoryUid) {
	    $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
         /* prepare query */
        $flats = array();
        $table = 'tx_flatmgr_flat a';
        $fields = "*";
        $where = " category=" . $categoryUid ." AND a.pid IN (".$this->conf['pid_list'].") AND a.deleted='0' AND a.hidden='0'";
        if ($this->_GP['flatUid'] > 0)
            $where .= ' AND uid=' . intval($this->_GP['flatUid']);
        if ($this->_GP['flatCategoryUid'] > 0)
            $where .= ' AND category=' . intval($this->_GP['flatCategoryUid']);
        if ($this->_GP['flatCapacity'] > 0)
            $where .= ' AND capacity>=' . intval($this->_GP['flatCapacity']);

        if ($this->conf['enableFeUser'])
            $where .= ' AND feuseruid=' . $feUserUid;

        if ($this->conf['showOnlySpecials']) {
            $where .= ' AND special > 0';
        }
        //$orderBy = 'category,' . $this->flat_table . ".name ASC";
        $orderBy = 'a.sorting';
		if (t3lib_extMgm::isLoaded('flatmgrcalc')) {
			if ($this->_GP['orderBy'] == '') $orderBy = 'name';
			if ($this->_GP['orderBy'] == 'name') $orderBy = 'name';
			if ($this->_GP['orderBy'] == 'totalWeight') $orderBy = 'totalweight';
			if ($this->_GP['orderBy'] == 'minprice') $orderBy = 'minprice';
			if ($this->_GP['orderBy'] == 'capacity') $orderBy = 'capacity';
			if ($this->_GP['sort'] == 'asc') $sort = 'asc';
			if ($this->_GP['sort'] == 'desc') $sort = 'desc';
		}
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy .' ' . $sort);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $flats['uid'][]     .= $row['uid'];
               $flats['name'][]     .= $row['name'];
               $flats['url'][]     .= $row['url'];

               $flats['email'][]     .= $this->getFeUserEmail($row['uid']);

               $flats['flatCategory'][]     .= $row['category'];
               $flats['capacity'][]         .= $row['capacity'];

               $flats['street'][]             .= $row['street'];
               $flats['zip'][]             .= $row['zip'];
               $flats['city'][]             .= $row['city'];
               $flats['pic'][]             .= $row['pic'];
               $flats['capacitytext'][]     .= $row['capacitytext'];
               $flats['marketing'][]         .= $row['marketing'];
               $flats['livingspace'][]     .= $row['livingspace'];
               $flats['landarea'][]         .= $row['landarea'];
               $flats['minprice'][]         .= $row['minprice'];
               $flats['attribute'][]         .= $row['attribute'];
               $flats['video'][]             .= $row['video'];
               $flats['pricepeakseason'][] .= $row['pricepeakseason'];
               $flats['pricelowseason'][]     .= $row['pricelowseason'];
               $flats['pricesavingseason'][]     .= $row['pricesavingseason'];
               $flats['caution'][]         .= $row['caution'];
               $flats['dayofarrival'][]     .= $row['dayofarrival'];
               $flats['priceendcleaning'][]     .= $row['priceendcleaning'];
               $flats['gallery'][]         .= $row['gallery'];
            $flats['attributes'][] = $this->getAttributes($row['attribute']);
            $flats['special'][]    .= $this->getSpecial($row['uid']);
               $flats['totalweight'][]         .= $row['totalweight'];
               $flats['bookingmindays'][]         .= $row['bookingmindays'];
               $flats['bookingmindayswhitsun'][]         .= $row['bookingmindayswhitsun'];
               $flats['bookingmindayssummer'][]         .= $row['bookingmindayssummer'];
        }
        return $flats;
    }

		/* 20150401 infoworxx Start - new Feature - Randomlist */
		function getRandomFlatList($categoryUid) {
		    $GLOBALS['TYPO3_DB']->debugOutput = 1;
		    $feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
		     /* prepare query */
		    $flats = array();
		    $table = 'tx_flatmgr_flat a';
		    $fields = "*";
		    $where = " category=" . $categoryUid ." AND a.pid IN (".$this->conf['pid_list'].") AND a.deleted='0' AND a.hidden='0'";
		    if ($this->_GP['flatUid'] > 0)
		        $where .= ' AND uid=' . intval($this->_GP['flatUid']);
		    if ($this->_GP['flatCategoryUid'] > 0)
		        $where .= ' AND category=' . intval($this->_GP['flatCategoryUid']);
		    if ($this->_GP['flatCapacity'] > 0)
		        $where .= ' AND capacity>=' . intval($this->_GP['flatCapacity']);
		
		    if ($this->conf['enableFeUser'])
		        $where .= ' AND feuseruid=' . $feUserUid;
		
		    if ($this->conf['showOnlySpecials']) {
		        $where .= ' AND special > 0';
		    }
		    //$orderBy = 'category,' . $this->flat_table . ".name ASC";
		    $orderBy = 'RAND()';
		    $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
		                                                 $table,
		                                                 $where,
		                                                 '',
		                                                 $orderBy .' ' . $sort);
		    /* process query result */
		    $flats = array();
		    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
				$flats['uid'][]     .= $row['uid'];
				$flats['name'][]     .= $row['name'];
				$flats['url'][]     .= $row['url'];
				$flats['email'][]     .= $this->getFeUserEmail($row['uid']);
				$flats['flatCategory'][]     .= $row['category'];
				$flats['capacity'][]         .= $row['capacity'];
				$flats['street'][]             .= $row['street'];
				$flats['zip'][]             .= $row['zip'];
				$flats['city'][]             .= $row['city'];
				$flats['pic'][]             .= $row['pic'];
				$flats['capacitytext'][]     .= $row['capacitytext'];
				$flats['marketing'][]         .= $row['marketing'];
				$flats['livingspace'][]     .= $row['livingspace'];
				$flats['landarea'][]         .= $row['landarea'];
				$flats['minprice'][]         .= $row['minprice'];
				$flats['attribute'][]         .= $row['attribute'];
				$flats['video'][]             .= $row['video'];
				$flats['pricepeakseason'][] .= $row['pricepeakseason'];
				$flats['pricelowseason'][]     .= $row['pricelowseason'];
				$flats['pricesavingseason'][]     .= $row['pricesavingseason'];
				$flats['caution'][]         .= $row['caution'];
				$flats['dayofarrival'][]     .= $row['dayofarrival'];
				$flats['priceendcleaning'][]     .= $row['priceendcleaning'];
				$flats['gallery'][]         .= $row['gallery'];
				$flats['attributes'][] = $this->getAttributes($row['attribute']);
				$flats['special'][]    .= $this->getSpecial($row['uid']);
				$flats['totalweight'][]         .= $row['totalweight'];
		    }
		    return $flats;
		}
		/* 20150401 infoworxx End */

       function getSingleFlat($flatUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
         /* prepare query */
        $flats = array();
        $fields = "*";
        $where = "  pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .=  ' AND uid=' . intval($flatUid);
        if ($this->_GP['flatUid'] > 0)
            $where .= ' AND uid=' . intval($this->_GP['flatUid']);
        $orderBy = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $this->flat_table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $flats['uid'][]     .= $row['uid'];
               $flats['name'][]     .= $row['name'];
               $flats['url'][]     .= $row['url'];

               $flats['email'][]     .= $this->getFeUserEmail($row['uid']);

               $flats['flatCategory'][]     .= $row['category'];
               $flats['capacity'][]         .= $row['capacity'];

               $flats['street'][]           .= $row['street'];
               $flats['zip'][]              .= $row['zip'];
               $flats['city'][]             .= $row['city'];
               $flats['pic'][]              .= $row['pic'];
               $flats['capacitytext'][]     .= $row['capacitytext'];
               $flats['marketing'][]        .= $row['marketing'];
               $flats['livingspace'][]      .= $row['livingspace'];
               $flats['landarea'][]         .= $row['landarea'];
               $flats['minprice'][]         .= $row['minprice'];
               $flats['attribute'][]        .= $row['attribute'];
               $flats['video'][]            .= $row['video'];
               if ($row['showalternativeprices']) {
                   $flats['pricepeakseason'][]    .= $row['alternativepricepeakseason'];
                   $flats['pricelowseason'][]     .= $row['alternativepricelowseason'];
                   $flats['pricesavingseason'][]  .= $row['alternativepricesavingseason'];
               } else {
                   $flats['pricepeakseason'][]    .= $row['pricepeakseason'];
                   $flats['pricelowseason'][]     .= $row['pricelowseason'];
                   $flats['pricesavingseason'][]  .= $row['pricesavingseason'];
               }

               $flats['caution'][]          .= $row['caution'];
               $flats['dayofarrival'][]     .= $row['dayofarrival'];
               $flats['priceendcleaning'][] .= $row['priceendcleaning'];
               $flats['gallery'][]          .= $row['gallery'];
               $flats['attributes'][] = $this->getAttributes($row['attribute']);
               $flats['description'][]     .= $this->getDescription($row['uid']);
               $flats['seasonDates']     = $this->getSeasons($row['showalternativeprices']);
               $flats['showMap'][]         .= $row['showmap'];
               $flats['showCalendar'][]    .= $row['showcalendar'];
               $flats['showFutureYearsInCalendar'][]         .= $row['showfutureyearsincalendar'];
               $flats['special'][]    = $this->getSpecial($row['uid']);
               $flats['flexdata'][]         .= $row['flexdata'];
        }
        return $flats;
    }

    function getSeasons($alternative = 0) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
         /* prepare query */
         $year = date('Y', time());
         $year = $this->_GP['year'];
         //if ($year < 2011) $year = 2011;
        $table = 'tx_flatmgr_season';
        $fields = "*";
        $where = "  pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .= "  AND year = " . $year;
        $orderBy = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
			if ($alternative) {
	            $data['peakseason'] = $row['alternativepeakseason'];
	            $data['lowseason'] = $row['alternativelowseason'];
	            $data['savingseason'] = $row['alternativesavingseason'];
            } else {
		        $data['peakseason'] = $row['peakseason'];
	            $data['lowseason'] = $row['lowseason'];
	            $data['savingseason'] = $row['savingseason'];
            }
        }
        return $data;
    }

    function getDescription($flatUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
         /* prepare query */
        $table = 'tx_flatmgr_description';
        $fields = "*";
        $where = "  pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .=  ' AND parentuid=' . intval($flatUid);
        $where .=  ' AND sys_language_uid=' . $GLOBALS['TSFE']->config['config']['sys_language_uid'];
        $orderBy = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
            $description = nl2br($row['description']);
        }
        return $description;
    }

    function getAttributes($attributes) {
        if ($attributes == '') return '';
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $table = 'tx_flatmgr_attribute';
        $fields = '*';
        $where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .= ' AND UID in (' . $attributes .')';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                             $table,
                                             $where,
                                             '',
                                             $orderBy);
        $attribute = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
            $attribute['name'][] .= $row['name'];
            $attribute['icon'][] .= $row['icon'];
        }
        return $attribute;
    }

    function getSpecial($uid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $table = 'tx_flatmgr_special';
        $fields = '*';
        $where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .= ' AND parentuid = ' . $uid;
        $where .=  ' AND sys_language_uid=' . $GLOBALS['TSFE']->config['config']['sys_language_uid'];
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                             $table,
                                             $where,
                                             '',
                                             $orderBy);
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
            $data = $row['description'];
        }
        return $data;
    }

    function getFeUserEmail($flatUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $table = 'tx_flatmgr_flat a, fe_users b';
        $fields = "b.email";
        $where = " a.pid IN (".$this->conf['pid_list'].") AND a.deleted='0' AND a.hidden='0'";
        $where .= ' AND a.uid=' . intval($flatUid) . ' AND a.feuseruid=b.uid';
        $orderBy = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $email     = $row['email'];
        }
        return $email;

    }

    function getFlat($flatUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
         /* prepare query */
        $flats = array();
        $fields = "name";
        $where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .= ' AND uid=' . intval($flatUid);
        $orderBy = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $this->flat_table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $flats = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $name     = $row["name"];
        }
        return $name;
    }

       function getFlatUidOfRoom($roomUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $feUserUid = $GLOBALS['TSFE']->fe_user->user['uid'];
         /* prepare query */
        $table = 'tx_flatmgr_room';
        $fields = "*";
        $where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";

        $where .= ' AND uid=' . intval($roomUid);
        $orderBy = '';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $uid     = $row['flatuid'];
        }
        return $uid;
    }


       function getRoom($roomUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
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

       function getFirstRoomUid($flatUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $table = 'tx_flatmgr_room';
        $fields = '*';
        $where = " flatuid='".intval($flatUid)."' and pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $orderBy = 'name ASC';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     "",
                                                     $orderBy);
        /* process query result */
        $data = array();
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        $uid = $row['uid'];
        return $uid;
    }

       function getRooms($flatUid, $flatCapacity = 0) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
        $table = 'tx_flatmgr_room';
        $fields = '*';
        $where = " flatuid='".intval($flatUid)."' and pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";

        if($this->conf['exactAvailabilityCheck']) {
            $where .= ' AND capacity = ' . intval($this->_GP['flatCapacity']);
        } else {
            if($flatCapacity > 0)
            $where .= ' AND capacity >= ' . intval($flatCapacity);
        }
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

     function getCategories() {
        /* prepare query */
        $table = 'tx_flatmgr_category';
        $fields = "*";
        $where_clause = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $orderBy = "uid ASC";
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where_clause,
                                                     "",
                                                     $orderBy);
        /* process query result */
        $categories = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $categories['category'][] .= $row["name"];
               $categories['uid'][] .= $row["uid"];
        }
        return $categories;
    }

    function getFlatCategories() {
        /* prepare query */
        $table = 'tx_flatmgr_flatcategory';
        $fields = "*";
        $where_clause = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $orderBy = "name ASC";
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where_clause,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $categories = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $categories['category'][] .= $row["name"];
               $categories['uid'][] .= $row["uid"];
        }
        return $categories;
    }

    function getFlatCategory($uid) {
        /* prepare query */
        $table = 'tx_flatmgr_flatcategory';
        $fields = "*";
        $where_clause = " pid IN (".$this->conf['pid_list'].") AND uid=" . $uid . " AND deleted='0' AND hidden='0'";
        $orderBy = "name ASC";
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where_clause,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $categories = array();
        $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result);
        $category= $row["name"];

        return $category;
    }

    function getFlatCapacities() {
        /* prepare query */
        $table = 'tx_flatmgr_flat';
        $fields = 'distinct capacity';
        $where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $where .= ' AND uid not in (select flatuid from tx_flatmgr_room where deleted="0" AND hidden="0")';
        $orderBy = 'capacity ASC';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        $categories = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $capacities['capacity'][] .= $row['capacity'];
        }

        // capacities of the rooms
        $table = 'tx_flatmgr_room';
        $fields = 'distinct capacity';
        $where = " pid IN (".$this->conf['pid_list'].") AND deleted='0' AND hidden='0'";
        $orderBy = 'capacity ASC';
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $table,
                                                     $where,
                                                     '',
                                                     $orderBy);
        /* process query result */
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
           $in = 0;
            for ($i = 0; $i < count($capacities['capacity']); $i++) {
               if ($capacities['capacity'][$i] == $row['capacity']) $in = 1;
            }
            if (!$in) $capacities['capacity'][] .= $row['capacity'];
        }
        array_multisort($capacities['capacity']);
        return $capacities;
    }

       function getBooking($flatUid) {
        $GLOBALS['TYPO3_DB']->debugOutput = 1;
           $startOfYear = mktime(0,0,0,1,1,$this->_GP['year']);
           $endOfYear =  mktime(0,0,0,12,31,$this->_GP['year']);

        /* prepare query */
        $tables = " tx_flatmgr_flat a, tx_flatmgr_book b";
        $fields = "b.startdate, b.enddate, b.uid, b.bookedby, b.customernumber, b.grownups, b.childs, b.memo, b.category, b.agent, b.onrequest";
        $where_clause = " a.uid ='". intval($flatUid) ."' and b.flatid = a.uid AND b.pid IN (".
                        $this->conf['pid_list'].
                        ") AND b.deleted='0' AND b.hidden='0'" .
                        " AND a.deleted='0' AND a.hidden='0'".
                        " AND  ( ( b.startdate >= " . $startOfYear ." AND b.startdate <= ". $endOfYear .") OR " .
                        " ( b.enddate >= " . $startOfYear ." AND b.enddate <= ". $endOfYear ."))";

        if($this->conf['hideOldBooking'])
            $where_clause .= ' AND b.enddate > ' . time();

        if ($this->_GP['roomUid'] > 0)
        $where_clause .= ' AND roomuid=' . intval($this->_GP['roomUid']);

        $orderBy = "b.startdate ASC";
        $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                         $tables,
                                                         $where_clause,
                                                         '',
                                                         $orderBy);
        /* process query result */
        $booked = array();
        $booked['startDates'] = array();
        $booked['endDates']   = array();
        $booked['uid']    = array();
        $booked['bookedBy']    = array();
        $booked['customerNumber']    = array();
        $booked['grownups']    = array();
        $booked['childs']    = array();
        $booked['memo']    = array();
        $booked['category']    = array();
        $booked['agent']    = array();
        $booked['onRequest']    = array();
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
               $booked['startDates'][]     .= date('d.m.Y',$row['startdate']);
               $booked['endDates'][]       .= date('d.m.Y',$row['enddate'] - 3600 * 24);
			   $booked['bookedBy'][]        .= $row['bookedby'];

               $booked['customerNumber'][] .= $row['customernumber'];
               $booked['grownups'][]       .= $row['grownups'];
               $booked['childs'][]            .= $row['childs'];
               $booked['memo'][]            .= $row['memo'];
               $booked['categoryId'][]      .= $row['category'];
               $booked['agent'][]            .= $row['agent'];
               $booked['onRequest'][]      .= $row['onrequest'];
        }
        return $booked;
    }


	function getTtaddressCompany($uid) {
	    if ($uid == 0) return;
		$tables = 'tt_address';
		$where = 'uid = ' . $uid;
		$fields = 'company';
	    $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                             $tables,
                                             $where,
                                             '',
                                             $orderBy);
        while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
		$company = $row['company'];
		}
		return $company;
	}


       function getUtilisationBooking() {
    /* prepare query */
     $GLOBALS['TYPO3_DB']->debugOutput = 1;


    $tables = "tx_flatmgr_flat a, tx_flatmgr_book b";
    $fields = "a.name, a.capacity, b.startdate, b.enddate, b.uid, b.bookedby, b.customernumber, b.grownups, b.childs, b.memo, b.category";
    $where_clause = " b.flatid = a.uid AND b.pid IN (".
                    $this->conf['pid_list'].
                    ") AND b.deleted='0' AND b.hidden='0'" .
                    " AND a.deleted='0' AND a.hidden='0'";

    $orderBy = "b.startdate ASC";
    $result = $GLOBALS['TYPO3_DB']->exec_SELECTquery($fields,
                                                     $tables,
                                                     $where_clause,
                                                     "",
                                                     $orderBy);
    /* process query result */
    $booked = array();
    $booked['flat'] = array();
    $booked['capacity'] = array();
    $booked['startDates'] = array();
    $booked['endDates']   = array();
    $booked['uid']    = array();
    $booked['bookedBy']    = array();
    $booked['customerNumber']    = array();
    $booked['grownups']    = array();
    $booked['childs']    = array();
    $booked['memo']    = array();
    $booked['category']    = array();
    while ($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($result) ) {
           $booked['flat'][]            .= $row['name'];
           $booked['capacity'][]            .= $row['capacity'];
           $booked['startDates'][]         .= $row['startdate'];
           $booked['endDates'][]           .= $row['enddate'];
           $booked['uid'][]            .= $row['uid'];
           $booked['bookedBy'][]            .= $row['bookedby'];
           $booked['customerNumber'][]            .= $row['customernumber'];
           $booked['grownups'][]            .= $row['grownups'];
           $booked['childs'][]            .= $row['childs'];
           $booked['memo'][]            .= $row['memo'];
           $booked['categoryId'][]            .= $row['category'];
    }
    return $booked;
    }



       function deleteBooking($uid) {
        /* prepare delete query */
        $table = "tx_flatmgr_book";
        $where = " uid='" . intval($uid) . "'";
        /* execute delete query */
        $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
    return;
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

       function insertBooking($flat) {
        /* prepare start- and end-dates */
        list($day, $month, $year) =    explode(".", $this->_GP['start']);
        $sDate = mktime(0,0,0,$month,$day,$year);
        list($day, $month, $year) =    explode(".", $this->_GP['end']);
        $eDate = mktime(0,0,0,$month,$day,$year);

        $flatId = (int)$this->getFlatId($flat);

           /* prepare insert query */
        $fields = array();
        $fields["pid"]            = 230;//(int)$this->conf['pid_list'];
        $fields["startdate"]    = $sDate;
        $fields["enddate"]        = $eDate;
        $fields["flatid"]        = $flatId;
        $fields["tstamp"]        = time();
        $fields["crdate"]        = time();
        $fields["cruser_id"]    = $GLOBALS['TSFE']->beUserLogin;

        /* execute query */
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_flatmgr_book",
                 $fields);

        /* update object information */
        $this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
    }

      function insertFlat($flat) {
        /* prepare start- and end-dates */
        list($day, $month, $year) =    explode(".", $this->_GP['start']);
        $sDate = mktime(0,0,0,$month,$day,$year);
        list($day, $month, $year) =    explode(".", $this->_GP['end']);
        $eDate = mktime(0,0,0,$month,$day,$year);

           /* prepare insert query */
        $fields = array();
        $fields["pid"]            = (int)$this->conf['pid_list'];
        $fields["price"]        = '';
        $fields["name"]            = (string)$flat;
        $fields["tstamp"]        = time();
        $fields["crdate"]        = time();
        $fields["cruser_id"]    = $GLOBALS['TSFE']->beUserLogin;

        /* execute query */
        $result = $GLOBALS['TYPO3_DB']->exec_INSERTquery("tx_flatmgr_flat",
                 $fields);

        /* update object information */
        $this->uid = $GLOBALS['TYPO3_DB']->sql_insert_id();
    }
    
    function deleteFlat($flat) {

        $flatId = $this->getFlatId($flat);
        
          /* prepare delete query */
        $table = "tx_flatmgr_book";
        $where = " flatid=" . $flatId;
        /* execute delete query */
        $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
        
        /* prepare delete query */
        $table = "tx_flatmgr_flat";
        $where = " uid=" . $flatId;
        /* execute delete query */
        $GLOBALS['TYPO3_DB']->exec_DELETEquery($table, $where);
    return;
    }
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_model.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/flatmgr/pi1/class.tx_flatmgr_model.php']);
}
?>