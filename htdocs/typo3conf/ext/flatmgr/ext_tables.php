<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

// class for displaying the category tree in BE forms.
if (TYPO3_MODE=="BE")	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_flatmgr_tcefunc_selecttreeview.php');
if (TYPO3_MODE=="BE") 	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_flatmgr_treeview.php');

if (TYPO3_MODE=="BE")	require_once(t3lib_extMgm::extPath($_EXTKEY).'class.tx_flatmgr_tcefunc.php');


t3lib_extMgm::allowTableOnStandardPages("tx_flatmgr_flat");
t3lib_extMgm::addToInsertRecords("tx_flatmgr_flat");

// style-pointer
/*
$TBE_STYLES['colorschemes'] = Array (
       '0' => '#F7F7F3,#E3E3DF,#EDEDE9',
	   '1' => '#94A19A,#7C8D84,#7C8D84',
	   '2' => '#E4D69E,#E7DBA8,#E9DEAF',
	   '3' => '#C2BFC0,#C7C5C5,#C7C5C5',
	   '4' => '#B2B5C3,#C4C6D1,#D5D7DE',
	   '5' => '#C3B2B5,#D1C4C6,#DED5D7'
);
$TBE_STYLES['styleschemes'] = Array (
       '0' => array('all'=>'background-color: #F7F7F3;border:#7C8D84 solid 1px;', 'check'=>''),
	   '1' => array('all'=>'background-color: #94A19A;border:#7C8D84 solid 1px;', 'check'=>''),
	   '2' => array('all'=>'background-color: #E4D69E;border:#7C8D84 solid 1px;', 'check'=>''),
	   '3' => array('all'=>'background-color: #C2BFC0;border:#7C8D84 solid 1px;', 'check'=>''),
	   '4' => array('all'=>'background-color: #B2B5C3;border:#7C8D84 solid 1px;', 'check'=>''),
	   '5' => array('all'=>'background-color: #C3B2B5;border:#7C8D84 solid 1px;', 'check'=>''),
);
$TBE_STYLES['borderschemes'] = Array (
	'0' => array('border:solid 1px black;',5),
	'1' => array('border:solid 1px black;',5),
	'2' => array('border:solid 1px black;',5),
	'3' => array('border:solid 1px black;',5),
	'4' => array('border:solid 1px black;',5),
	'5' => array('border:solid 1px black;',5)
);
*/
//$TBE_STYLES['colorschemes'][0]='red,yellow,blue,olive,green';


$TCA["tx_flatmgr_flat"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flat',		
		'label' => 'name',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",
		"delete" => "deleted",	
//		'default_sortby' => 'category, sorting',
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_flatmgr_flat.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, name, capacity, pic",
	)
);



t3lib_extMgm::allowTableOnStandardPages('tx_flatmgr_description');
t3lib_extMgm::addToInsertRecords('tx_flatmgr_description');

$TCA["tx_flatmgr_description"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_description',
		'label'     => 'name',
//		'label_alt' => 'icon',
//		'label_alt_force' => '1',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',

		'type' => 'type',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',

//		'treeParentField' => 'parentuid',
		'enablecolumns' => array (
			'disabled' => 'hidden',
//			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_flatmgr_description.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "name",
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_flatmgr_special');
t3lib_extMgm::addToInsertRecords('tx_flatmgr_special');

$TCA["tx_flatmgr_special"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_special',
		'label'     => 'name',
//		'label_alt' => 'icon',
//		'label_alt_force' => '1',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',

		'type' => 'type',
		'languageField'            => 'sys_language_uid',
		'transOrigPointerField'    => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',

//		'treeParentField' => 'parentuid',
		'enablecolumns' => array (
			'disabled' => 'hidden',
//			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_flatmgr_description.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "name",
	)
);


t3lib_extMgm::allowTableOnStandardPages('tx_flatmgr_season');
t3lib_extMgm::addToInsertRecords('tx_flatmgr_season');

$TCA["tx_flatmgr_season"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_season',
		'label'     => 'year',
//		'label_alt' => 'icon',
//		'label_alt_force' => '1',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'type' => 'type',
		'enablecolumns' => array (
			'disabled' => 'hidden',
//			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_flatmgr_season.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "name",
	)
);

t3lib_extMgm::allowTableOnStandardPages('tx_flatmgr_attribute');
t3lib_extMgm::addToInsertRecords('tx_flatmgr_attribute');

$TCA["tx_flatmgr_attribute"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_attribute',
		'label'     => 'name',
//		'label_alt' => 'icon',
//		'label_alt_force' => '1',
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'sortby' => 'sorting',
		'delete' => 'deleted',
		'type' => 'type',
		'thumbnail' => 'icon',
		'selicon_field' => 'icon',
		'selicon_field_path' => 'uploads/tx_flatmgr',
		'enablecolumns' => array (
			'disabled' => 'hidden',
//			'fe_group' => 'fe_group',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_flatmgr_season.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "name",
	)
);


t3lib_extMgm::allowTableOnStandardPages("tx_flatmgr_room");
t3lib_extMgm::addToInsertRecords("tx_flatmgr_room");
$TCA["tx_flatmgr_room"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_room',
		'label' => 'flatuid',
		'label_alt' => 'name',
		'label_alt_force' => '1',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		"sortby" => "sorting",
		"delete" => "deleted",

		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_flatmgr_flat.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, name, capacity",
	)
);




t3lib_extMgm::allowTableOnStandardPages("tx_flatmgr_book");


t3lib_extMgm::addToInsertRecords("tx_flatmgr_book");

$TCA["tx_flatmgr_book"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_book',		
		'label' => 'startdate',	
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'category' => 'category',
		'bookedby' => 'bookedby',
		'customernumber' => 'customernumber',
		'grownups' => 'grownups',
		'childs' => 'childs',
		'memo' => 'memo',
		"sortby" => "sorting",	
		"delete" => "deleted",	
		"enablecolumns" => Array (		
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_flatmgr_book.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, flatid, startdate, enddate, bookedby, category, customernumber, grownups, childs, memo",
	)
);

t3lib_extMgm::allowTableOnStandardPages("tx_flatmgr_flatcategory");
t3lib_extMgm::addToInsertRecords("tx_flatmgr_flatcategory");

$TCA["tx_flatmgr_flatcategory"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_flatcategory',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'name' => 'name',
		"sortby" => "sorting",
		"delete" => "deleted",


		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_flatmgr_category.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, name",
	)
);




t3lib_extMgm::allowTableOnStandardPages("tx_flatmgr_category");
t3lib_extMgm::addToInsertRecords("tx_flatmgr_category");

$TCA["tx_flatmgr_category"] = Array (
	"ctrl" => Array (
		'title' => 'LLL:EXT:flatmgr/locallang_db.xml:tx_flatmgr_category',
		'label' => 'name',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'name' => 'name',
		"sortby" => "sorting",
		"delete" => "deleted",


		"enablecolumns" => Array (
			"disabled" => "hidden",
		),
		"dynamicConfigFile" => t3lib_extMgm::extPath($_EXTKEY)."tca.php",
		"iconfile" => t3lib_extMgm::extRelPath($_EXTKEY)."icon_tx_flatmgr_category.gif",
	),
	"feInterface" => Array (
		"fe_admin_fieldList" => "hidden, name",
	)
);






if (version_compare(TYPO3_branch, '6.1', '<')) {
	t3lib_div::loadTCA('tt_content');
}
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key';

t3lib_extMgm::addPlugin(Array('LLL:EXT:flatmgr/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


// ab hier flexform
$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';

# Wir definieren die Datei, die unser Flexform Schema enthält

t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/flexform_ds.xml');

t3lib_extMgm::addPlugin(Array('LLL:EXT:flatmgr/locallang_db.php:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');


//t3lib_extMgm::addStaticFile($_EXTKEY,'pi1/static/','Default CSS-Styles');
?>