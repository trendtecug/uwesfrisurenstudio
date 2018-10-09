<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_flatmgr_flat=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_flatmgr_book=1
');
t3lib_extMgm::addUserTSConfig('
	options.saveDocNew.tx_flatmgr_category=1
');

## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_flatmgr_pi1 = < plugin.tx_flatmgr_pi1.CSS_editor
',43);

// eID
$GLOBALS['TYPO3_CONF_VARS']['FE']['eID_include']['tx_flatmgr_eID'] = 'EXT:flatmgr/pi1/class.tx_flatmgr_eID.php';


## list_type 0 not cached to work correct with xajax
t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_flatmgr_pi1.php','_pi1','list_type',0);


t3lib_extMgm::addTypoScript($_EXTKEY,'setup','
	tt_content.shortcut.20.0.conf.tx_flatmgr_flat = < plugin.'.t3lib_extMgm::getCN($_EXTKEY).'_pi1
	tt_content.shortcut.20.0.conf.tx_flatmgr_flat.CMD = singleView
',43);
?>