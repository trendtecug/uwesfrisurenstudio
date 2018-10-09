<?php
if (!defined ('TYPO3_MODE'))     die ('Access denied.');

$TCA["tx_flatmgr_flat"] = Array (
    "ctrl" => $TCA["tx_flatmgr_flat"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden,name,feuseruid,pic"
    ),
    "feInterface" => $TCA["tx_flatmgr_flat"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),
        "name" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.name",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "required,trim",
            )
        ),


        "capacity" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.capacity",
            "config" => Array (
                "type" => "input",
                "size" => "2",
                "default" => "4",
                "eval" => "int,trim",
            )
        ),

        "capacitytext" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.capacitytext",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "default" => "",
                "eval" => "trim",
            )
        ),

        "marketing" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.marketing",
            "config" => Array (
                'type' => 'select',
                'items' => Array (
                    Array('LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.marketingRental', 0),
                    Array('LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.marketingPurchase', 1),
				),
                'default' => '0',
                'size' => '1',
                'noIconsBelowSelect' => '1',
            )
        ),

        "livingspace" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.livingspace",
            "config" => Array (
                "type" => "input",
                "size" => "5",
                "default" => "",
                "eval" => "int,trim",
            )
        ),

        "landarea" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.landarea",
            "config" => Array (
                "type" => "input",
                "size" => "5",
                "default" => "",
                "eval" => "int,trim",
            )
        ),

        "minprice" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.minprice",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "default" => "",
                "eval" => "trim",
            )
        ),

        "attribute" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.attribute",
            "config" => Array (
                'type'  => 'select',
                'foreign_table' => 'tx_flatmgr_attribute',
                'foreign_table_loadIcons' => '1',
                'foreign_table_where' => ' order by tx_flatmgr_attribute.name asc',
                'renderMode' => 'checkbox',
                //'itemsProcFunc' => 'tx_flatmgr_tcefunc->showAttributeIcons',
                'size' => 10,
                'maxitems' => 30,
                "show_thumbs" => 1,
            )
        ),



        "video" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.video",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            )
        ),


/*****************************/
        "street" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.street",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            )
        ),
        "city" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.city",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            )
        ),
        "zip" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.zip",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            )
        ),
        "country" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.country",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            )
        ),
/********************************/

        "pic" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.pic",
            "config" => Array (
                "type" => "input",
                "size" => "15",
                "max" => "255",
                "checkbox" => "",
                "eval" => "trim",
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    )
                )
            )
        ),
        "url" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.url",
            "config" => Array (
                "type" => "input",
                "size" => "15",
                "max" => "255",
                "checkbox" => "",
                "eval" => "trim",
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    )
                )
            )
        ),

        "feuseruid" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.feuser",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array ('',0),
                ),
                "foreign_table" => "fe_users",
                "foreign_table_where" => " order by fe_users.username",
            )
        ),

         "category" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.category",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array ('',0),
                ),
                "foreign_table" => "tx_flatmgr_flatcategory",
                "foreign_table_where" => " and tx_flatmgr_flatcategory.pid=###CURRENT_PID### order by name",
            )
        ),

        'description' => array (
            'exclude' => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_description.description",
            'config'  => array (
                'type'  => 'inline',
                'foreign_table' => 'tx_flatmgr_description',
//                "foreign_table_where" => " and tx_flatmgr_description.pid=###CURRENT_PID### order by name",
                "foreign_field" => "parentuid",
                "foreign_table_field" => "parenttable",
            )
        ),
        "pricepeakseason" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.pricepeakseason",
            "config" => Array (
                "type" => "text",
                "columns" => "20",
                "rows" => "3",
                "eval" => "trim",
            )
        ),
        "pricelowseason" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.pricelowseason",
            "config" => Array (
                "type" => "text",
                "columns" => "20",
                "rows" => "3",
                "eval" => "trim",
            )
        ),
        "pricesavingseason" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.pricesavingseason",
            "config" => Array (
                "type" => "text",
                "columns" => "20",
                "rows" => "3",
                "eval" => "trim",
            )
        ),

        "showalternativeprices" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.showalternativeprices",
            "config" => Array (
                "type" => "check",
                "default" => "1",
            )
        ),
        "alternativepricepeakseason" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.alternativepricepeakseason",
            "config" => Array (
                "type" => "text",
                "columns" => "20",
                "rows" => "3",
                "eval" => "trim",
            )
        ),
        "alternativepricelowseason" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.alternativepricelowseason",
            "config" => Array (
                "type" => "text",
                "columns" => "20",
                "rows" => "3",
                "eval" => "trim",
            )
        ),
        "alternativepricesavingseason" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.alternativepricesavingseason",
            "config" => Array (
                "type" => "text",
                "columns" => "20",
                "rows" => "3",
                "eval" => "trim",
            )
        ),


        'special' => array (
            'exclude' => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_special.description",
            'config'  => array (
                'type'  => 'inline',
                'foreign_table' => 'tx_flatmgr_special',
//                "foreign_table_where" => " and tx_flatmgr_description.pid=###CURRENT_PID### order by name",
                "foreign_field" => "parentuid",
                "foreign_table_field" => "parenttable",
            )
        ),

        "priceendcleaning" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.priceendcleaning",
            "config" => Array (
                "type" => "input",
                "size" => "6",
                "eval" => "trim",
            )
        ),
        "caution" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.caution",
            "config" => Array (
                "type" => "input",
                "size" => "6",
                "eval" => "trim",
            )
        ),
        "dayofarrival" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.dayofarrival",
            "config" => Array (
                "type" => "select",
                'items' => array(
                    array('LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.arrivaldayfr', 0),
                    array('LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.arrivaldaysa', 1),
                    array('LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.arrivaldaysu', 2)
                )
            )
        ),

        "gallery" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.gallery",
            "config" => Array (
                "type" => "group",
                "internal_type" => "folder",


//                "size" => "6",
//                "eval" => "trim",
            )
        ),


        "tx_locator_lat" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr.tx_locator_lat",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            )
        ),
        "tx_locator_lon" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr.tx_locator_lon",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            )
        ),
        "tx_locator_geocode" => Array (
            "exclude" => 1,
            "label" => "tx_locator[geocode]",
            "config" => Array (
                "type" => "check",
                "default" => "1",
            )
        ),

        "showcalendar" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.showcalendar",
            "config" => Array (
                "type" => "check",
                "default" => "1",
            )
        ),

        "showfutureyearsincalendar" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.showfutureyearsincalendar",
            "config" => Array (
                "type" => "check",
                "default" => "1",
            )
        ),

        "showmap" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.showmap",
            "config" => Array (
                "type" => "check",
                "default" => "1",
            )
        ),

        /*
        "price" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat.price",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "required,trim",
            )
        ),
        */
    ),

    // 1-1-1 = style pointer which defines color, style and border
    "types" => Array (
        "0" => Array("showitem" => "hidden;;1;;1-1-1, name, category, feuseruid, capacity, capacitytext, marketing, livingspace, landarea, minprice, attribute, video, street, zip, city, country, pic, url, description, pricepeakseason, pricelowseason, pricesavingseason, showalternativeprices, alternativepricepeakseason, alternativepricelowseason, alternativepricesavingseason, special, caution, priceendcleaning, dayofarrival, gallery, tx_locator_lat, tx_locator_lon, tx_locator_geocode, showmap, showcalendar, showfutureyearsincalendar")
    ),
    "palettes" => Array (
        "1" => Array("showitem" => "")
    )
);


if (t3lib_extMgm::isLoaded('flatmgrcalc')) {
/*  Reading pid in TCA */

parse_str($_SERVER['QUERY_STRING'], $_arr);
parse_str($_arr['returnUrl'], $_res);
$pid = $_res['id'];
//debug($pid);

	$TCA["tx_flatmgr_flat"]['columns']["flexdata"] = Array (
				"exclude" => 1,
	//			"label" => "LLL:EXT:dynatest/locallang_db.xml:tx_dynatest.flexdata",
				'label' => 'LLL:EXT:flatmgrcalc/locallang_db.xml:tx_flatmgrcalc_seasons.title',
				"config" => Array (
					'type' => 'flex',
	                 'ds' => array (
	                     'default' => '<T3DataStructure>' .
	                             '    <meta>' .
	//                             '        <langdisable>1</langdisable>' .
	                             '    </meta>' .
								 '      <ROOT>' .
								 '        <type>array</type>' .
	                             '        <el>' .
	                             '            <dummy type="array">xx</dummy>' .
	                             '        </el>' .
	                             '    </ROOT>' .
	                             '</T3DataStructure>'
	                 ),
				)
			);

	$TCA["tx_flatmgr_flat"]["types"] = Array (
        "0" => Array("showitem" => "hidden;;1;;1-1-1, name, flexdata, category, feuseruid, capacity, capacitytext, marketing, livingspace, landarea, minprice, attribute, video, street, zip, city, country, pic, url, description, pricepeakseason, pricelowseason, pricesavingseason, showalternativeprices, alternativepricepeakseason, alternativepricelowseason, alternativepricesavingseason, special, caution, priceendcleaning, dayofarrival, gallery, tx_locator_lat, tx_locator_lon, tx_locator_geocode, showmap, showcalendar, showfutureyearsincalendar")
    );

}


if ($_REQUEST['edit']['tx_flatmgr_flat'] &&
    t3lib_extMgm::isLoaded('flatmgrcalc') &&
/*  !isset($_REQUEST['data']) && */
  	t3lib_extMgm::isLoaded('dynaflex')) {
	// important for dynaflex first load class with DCA
	require_once(t3lib_extMgm::extPath('flatmgrcalc') .'pi1/class.tx_dynaflexconfig.php');
    if (version_compare(TYPO3_branch, '6.0', '<')) {
        $dyn = t3lib_div::makeInstance('tx_dynaflexconfig');
    } else {
        $dyn = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_dynaflexconfig');
    }

	$where = $dyn->DCA[0]['modifications'][0]['source_config']['where'];
	$where = str_replace('###CURRENT_PID###', $pid, $where);

	$dyn->DCA[0]['modifications'][0]['source_config']['where'] = $where;
	require_once(t3lib_extMgm::extPath('dynaflex') .'class.dynaflex.php');
    if (version_compare(TYPO3_branch, '6.0', '<')) {
        $dynaflex = t3lib_div::makeInstance('dynaflex');
    } else {
        $dynaflex = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('dynaflex');
    }

	$dynaflex->init($TCA, $dyn->DCA);
	$TCA = $dynaflex->getDynamicTCA();
}





/********************************/
$TCA["tx_flatmgr_description"] = array (
    "ctrl" => $TCA["tx_flatmgr_description"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "sys_language_uid,l10n_parent,l10n_diffsource,name,description"
    ),
    "feInterface" => $TCA["tx_flatmgr_description"]["feInterface"],
    "columns" => array (

        'sys_language_uid' => array (
            'exclude' => 1,
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array (
                'type'                => 'select',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                )
            )
        ),
        'l10n_parent' => array (
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config'      => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                ),
                'foreign_table'       => 'tx_flatmgr_description',
                'foreign_table_where' => 'AND tx_flatmgr_description.pid=###CURRENT_PID### AND tx_flatmgr_description.sys_language_uid IN (-1,0)',
            )
        ),
        'l10n_diffsource' => array (
            'config' => array (
                'type' => 'passthrough',
            )
        ),


        "parentuid" => Array (
             "config" => Array (
                 "type" => "passthrough",
             )
         ),
         "parenttable" => Array (
             "config" => Array (
                 "type" => "passthrough",
             )
         ),



        "name" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_description.name",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "required,trim",
            )
        ),

        "description" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_description.description",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
            'defaultExtras' => 'richtext[*]'
        ),


    ),
    "types" => array (
        "0" => array("showitem" => "sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, name, description")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);

/**********************************/
$TCA["tx_flatmgr_special"] = array (
    "ctrl" => $TCA["tx_flatmgr_special"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "sys_language_uid,l10n_parent,l10n_diffsource,name,description"
    ),
    "feInterface" => $TCA["tx_flatmgr_special"]["feInterface"],
    "columns" => array (

        'sys_language_uid' => array (
            'exclude' => 1,
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array (
                'type'                => 'select',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                )
            )
        ),
        'l10n_parent' => array (
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config'      => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                ),
                'foreign_table'       => 'tx_flatmgr_special',
                'foreign_table_where' => 'AND tx_flatmgr_special.pid=###CURRENT_PID### AND tx_flatmgr_special.sys_language_uid IN (-1,0)',
            )
        ),
        'l10n_diffsource' => array (
            'config' => array (
                'type' => 'passthrough',
            )
        ),


        "parentuid" => Array (
             "config" => Array (
                 "type" => "passthrough",
             )
         ),
         "parenttable" => Array (
             "config" => Array (
                 "type" => "passthrough",
             )
         ),



        "name" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_special.name",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "required,trim",
            )
        ),

        "description" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_special.description",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),


    ),
    "types" => array (
        "0" => array("showitem" => "sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, name, description")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);


$TCA["tx_flatmgr_season"] = array (
    "ctrl" => $TCA["tx_flatmgr_season"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "year,peakseason,lowseason,savingseason"
    ),
    "feInterface" => $TCA["tx_flatmgr_description"]["feInterface"],
    "columns" => array (
        "year" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.year",
            "config" => Array (
                "type" => "input",
                "size" => "4",
                "eval" => "year,required,trim",
            )
        ),

        "peakseason" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.peakseason",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),
        "lowseason" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.lowseason",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),
        "savingseason" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.savingseason",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),

        "alternativepeakseason" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.alternativePeakseason",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),
        "alternativelowseason" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.alternativeLowseason",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),
        "alternativesavingseason" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season.alternativeSavingseason",
            "config" => Array (
                "type" => "text",
                "columns" => "50",
                "rows" => "10",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),


    ),
    "types" => array (
        "0" => array("showitem" => "year;;;;1-1-1, peakseason, lowseason, savingseason, alternativepeakseason, alternativelowseason, alternativesavingseason")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);


$TCA["tx_flatmgr_attribute"] = array (
    "ctrl" => $TCA["tx_flatmgr_attribute"]["ctrl"],
    "interface" => array (
        "showRecordFieldList" => "name, icon"
    ),
    "feInterface" => $TCA["tx_flatmgr_attribute"]["feInterface"],
    "columns" => array (

        "name" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_attribute.name",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "trim",
            ),
//            'defaultExtras' => 'richtext[*]'
        ),
        "icon" => Array (
            "exclude" => 0,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_attribute.icon",
            "config" => Array (
                "type" => "group",
                "internal_type" => "file",
                "allowed" => "jpg,jpeg,gif,png",
                "uploadfolder" => "uploads/tx_flatmgr",
                "max_size" => 10000,

/*
                "type" => "input",
                "size" => "15",
                "max" => "255",
                "checkbox" => "",
                "eval" => "trim",
                "wizards" => Array(
                    "_PADDING" => 2,
                    "link" => Array(
                        "type" => "popup",
                        "title" => "Link",
                        "icon" => "link_popup.gif",
                        "script" => "browse_links.php?mode=wizard",
                        "JSopenParams" => "height=300,width=500,status=0,menubar=0,scrollbars=1"
                    )
                ),
                "showThumbs" => "1",
*/
            )

//            'defaultExtras' => 'richtext[*]'
        ),

    ),
    "types" => array (
        "0" => array("showitem" => "name;;;;1-1-1, icon")
    ),
    "palettes" => array (
        "1" => array("showitem" => "")
    )
);











$TCA["tx_flatmgr_room"] = Array (
    "ctrl" => $TCA["tx_flatmgr_room"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden,name,flatuid"
    ),
    "feInterface" => $TCA["tx_flatmgr_flat"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),

         "flatuid" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_room.flatuid",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array ('',0),
                ),
                "foreign_table" => "tx_flatmgr_flat",
                "foreign_table_where" => " and tx_flatmgr_flat.pid=###CURRENT_PID### order by name",
            )
        ),

        "name" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_room.name",
            "config" => Array (
                "type" => "input",
                "size" => "30",
                "eval" => "required,trim",
            )
        ),
        "capacity" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_room.capacity",
            "config" => Array (
                "type" => "input",
                "size" => "2",
                "default" => "4",
                "eval" => "int,required,trim",
            )
        ),
    ),

    // 1-1-1 = style pointer which defines color, style and border
    "types" => Array (
        "0" => Array("showitem" => "hidden;;1;;1-1-1, flatuid, name,capacity")
    ),
    "palettes" => Array (
        "1" => Array("showitem" => "")
    )
);



$TCA["tx_flatmgr_book"] = Array (
    "ctrl" => $TCA["tx_flatmgr_book"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden,flatid,startdate,enddate,bookedby, customernumber, grownups, childs, memo, agent"
    ),
    "feInterface" => $TCA["tx_flatmgr_book"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),

                "flatid" => Array (
            'suppress_icons' => '1',
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.flatid",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array ('',0),
                ),
                "foreign_table" => "tx_flatmgr_flat",
                "foreign_table_where" => "and tx_flatmgr_flat.pid=###CURRENT_PID### order by tx_flatmgr_flat.name",
                "size" => "1",
                "minitems" => 1,
                "maxitems" => 1,
                "eval" => "trim",
                'wizards' => Array(
                     '_PADDING' => 1,
                     '_VERTICAL' => 1,
                     'edit' => Array(
                         'type' => 'popup',
                         'title' => 'Edit booking categories',
                         'script' => 'wizard_edit.php',
                         'icon' => 'edit2.gif',
                        'popup_onlyOpenIfSelected' => 1,
                         'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
                    ),
                     'add' => Array(
                         'type' => 'script',
                         'title' => 'Create new flat',
                         'icon' => 'add.gif',
                         'params' => Array(
                             'table'=>'tx_flatmgr_flat',
                             'pid' => '###CURRENT_PID###',
                             'setValue' => 'prepend'
                         ),
                         'script' => 'wizard_add.php',
                     ),
                    'list' => Array(
                         'type' => 'script',
                         'title' => 'List categories',
                         'icon' => 'list.gif',
                         'params' => Array(
                             'table'=>'flatmgr_flat',
                             'pid' => '###CURRENT_PID###',
                         ),
                         'script' => 'wizard_list.php',
                    ),
                ),
            )
        ),


           "roomuid" => Array (
            'suppress_icons' => '1',
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.roomuid",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array ('',0),
                ),
                "foreign_table" => "tx_flatmgr_room",
                "foreign_table_where" => "and tx_flatmgr_room.pid=###CURRENT_PID### order by tx_flatmgr_room.name",
                "size" => "1",
                "minitems" => 1,
                "maxitems" => 1,
                "eval" => "trim",
                'wizards' => Array(
                     '_PADDING' => 1,
                     '_VERTICAL' => 1,
                     'edit' => Array(
                         'type' => 'popup',
                         'title' => 'Edit booking categories',
                         'script' => 'wizard_edit.php',
                         'icon' => 'edit2.gif',
                        'popup_onlyOpenIfSelected' => 1,
                         'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
                    ),
                     'add' => Array(
                         'type' => 'script',
                         'title' => 'Create new flat',
                         'icon' => 'add.gif',
                         'params' => Array(
                             'table'=>'tx_flatmgr_flat',
                             'pid' => '###CURRENT_PID###',
                             'setValue' => 'prepend'
                         ),
                         'script' => 'wizard_add.php',
                     ),
                    'list' => Array(
                         'type' => 'script',
                         'title' => 'List categories',
                         'icon' => 'list.gif',
                         'params' => Array(
                             'table'=>'flatmgr_flat',
                             'pid' => '###CURRENT_PID###',
                         ),
                         'script' => 'wizard_list.php',
                    ),
                ),
            )
        ),


/*
        "flatid" => Array (
            'suppress_icons' => '1',
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.flatid",
            "config" => Array (
                "type" => "group",
                "internal_type" => "db",
                "allowed" => "tx_flatmgr_flat",
                "size" => 4,
                "minitems" => 0,
                "maxitems" => 1,
            )
        ),
*/

        "startdate" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.startdate",
            "config" => Array (
                "type" => "input",
                "size" => "8",
                "max" => "20",
                "eval" => "date",
                "checkbox" => "0",
                "default" => "0"
            )
        ),
        "enddate" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.enddate",
            "config" => Array (
                "type" => "input",
                "size" => "8",
                "max" => "20",
                "eval" => "date",
                "checkbox" => "0",
                "default" => "0"
            )
        ),
        "onrequest" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.onrequest",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),


        "category" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_category.name",
            "config" => Array (
                "type" => "select",
                "items" => Array (
                    Array ('',0),
                ),
                "foreign_table" => "tx_flatmgr_category",
                "foreign_table_where" => "order by tx_flatmgr_category.name",

                "size" => "1",
                "minitems" => 0,
                "maxitems" => 1,
                "eval" => "trim",


                'wizards' => Array(
                     '_PADDING' => 1,
                     '_VERTICAL' => 1,
                     'edit' => Array(
                         'type' => 'popup',
                         'title' => 'Edit booking categories',
                         'script' => 'wizard_edit.php',
                         'icon' => 'edit2.gif',
                        'popup_onlyOpenIfSelected' => 1,
                         'JSopenParams' => 'height=350,width=580,status=0,menubar=0,scrollbars=1',
                    ),
                     'add' => Array(
                         'type' => 'script',
                         'title' => 'Create new booking category',
                         'icon' => 'add.gif',
                         'params' => Array(
                             'table'=>'tx_flatmgr_category',
                             'pid' => '###CURRENT_PID###',
                             'setValue' => 'prepend'
                         ),
                         'script' => 'wizard_add.php',
                     ),
                    'list' => Array(
                         'type' => 'script',
                         'title' => 'List categories',
                         'icon' => 'list.gif',
                         'params' => Array(
                             'table'=>'flatmgr_category',
                             'pid' => '###CURRENT_PID###',
                         ),
                         'script' => 'wizard_list.php',
                    )


            )
        ),
        ),





        "bookedby" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.bookedby",
            "config" => Array (
                "type" => "input",
                "size" => "20",
                "eval" => "trim",
            )
        ),


        "customernumber" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.customernumber",
            "config" => Array (
                "type" => "input",
                "size" => "20",
                "eval" => "trim",
            )
        ),

        "grownups" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.grownups",
            "config" => Array (
                'suppress_icons' => '1',
                'type' => 'select',
                'items' => Array (
                    Array('-', 0),
                    Array('1', 1),
                    Array('2', 2),
                    Array('3', 3),
                    Array('4', 4),
                    Array('5', 5),
                    Array('6', 6),
			    ),
                'default' => '2',
                'size' => '1',


            )
        ),


        "childs" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.childs",
            "config" => Array (
                'suppress_icons' => '1',
                'type' => 'select',
                'items' => Array (
                    Array('-', 0),
                    Array('1', 1),
                    Array('2', 2),
                    Array('3', 3),
                    Array('4', 4),
                    Array('5', 5),
                    Array('6', 6),
                    Array('7', 7),
                    Array('8', 8),
                    Array('9', 9),
   		        ),
                'default' => '0',
                'size' => '1',
            )
        ),



        'memo' => Array (
            'exclude' => 1,
            "label" => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.memo',
            'config' => Array (
                'type' => 'text',
                'cols' => '40',
                'rows' => '6',
                'wrap' => 'off'
            )
        ),

           "agent" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.agent",
            "config" => Array (
                "type" => "input",
                "size" => "20",
                "eval" => "trim",
            )
        ),



    ),
    "types" => Array (
//        "0" => Array("showitem" => "hidden;;1;;1-1-1, flatid, startdate, enddate, bookedby, customernumber, grownups, childs, memo"),
        "0" => Array("showitem" => "hidden;;1;;1-1-1, flatid, roomuid, startdate, enddate, onrequest, category, bookedby, customernumber, grownups, childs, memo, agent"),
//        "0" => Array("showitem" => "hidden;;1;;2-2-2, flatid, startdate, enddate, bookedby, customernumber, grownups, childs, memo"),
//        "1" => Array("showitem" => "hidden;;2;;2-2-2, flatid, startdate, enddate, bookedby"),
    ),

    "palettes" => Array (
        "1" => Array("showitem" => "")
    )
);


$TCA["tx_flatmgr_flatcategory"] = Array (
    "ctrl" => $TCA["tx_flatmgr_flatcategory"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden, name"
    ),
    "feInterface" => $TCA["tx_flatmgr_book"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),
        "name" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flatcategory.categoryName",
            "config" => Array (
                "type" => "input",
                "size" => "20",
                "eval" => "trim",
            )
        ),
    ),
    "types" => Array (
        "0" => Array("showitem" => "hidden;;1;;0-0-0, name"),
//        "1" => Array("showitem" => "hidden;;2;;1-1-1, flatid, startdate, enddate, bookedby"),
    ),

    "palettes" => Array (
        "1" => Array("showitem" => "")
    )

);



$TCA["tx_flatmgr_category"] = Array (
    "ctrl" => $TCA["tx_flatmgr_category"]["ctrl"],
    "interface" => Array (
        "showRecordFieldList" => "hidden, name"
    ),
    "feInterface" => $TCA["tx_flatmgr_book"]["feInterface"],
    "columns" => Array (
        "hidden" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:lang/locallang_general.xml:LGL.hidden",
            "config" => Array (
                "type" => "check",
                "default" => "0"
            )
        ),
        "name" => Array (
            "exclude" => 1,
            "label" => "LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book.categoryName",
            "config" => Array (
                "type" => "input",
                "size" => "20",
                "eval" => "trim",
            )
        ),
    ),
    "types" => Array (
        "0" => Array("showitem" => "hidden;;1;;0-0-0, name"),
//        "1" => Array("showitem" => "hidden;;2;;1-1-1, flatid, startdate, enddate, bookedby"),
    ),

    "palettes" => Array (
        "1" => Array("showitem" => "")
    )

);



?>