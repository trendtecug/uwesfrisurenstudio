<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE == 'BE') {

    if (class_exists('t3lib_extMgm')) {
        t3lib_extMgm::addModule('tools', 'txcooluriM1', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod1/');
        t3lib_extMgm::addModule('web', 'txcooluriM2', '', t3lib_extMgm::extPath($_EXTKEY) . 'mod2/');
    } else {
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('tools', 'txcooluriM1', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod1/');
        \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addModule('web', 'txcooluriM2', '', \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'mod2/');
    }
}

$TCA['pages']['columns']['tx_realurl_pathsegment'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_pathsegment',
    'exclude' => 1,
    'config' => Array(
        'type' => 'input',
        'size' => '30',
        'max' => '30',
        'eval' => 'trim,nospace,lower,uniqueInPid'
    )
);

$TCA['pages']['columns']['tx_cooluri_exclude'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_exclude',
    'exclude' => 1,
    'config' => Array(
        'type' => 'check',
        'default' => '0'
    )
);

$TCA['pages']['columns']['tx_cooluri_excludealways'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_excludealways',
    'exclude' => 1,
    'config' => Array(
        'type' => 'check',
        'default' => '0'
    )
);

$TCA['pages_language_overlay']['columns']['tx_realurl_pathsegment'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_pathsegment',
    'exclude' => 1,
    'config' => Array(
        'type' => 'input',
        'size' => '30',
        'max' => '30',
        'eval' => 'trim,nospace,lower,uniqueInPid'
    )
);

if (class_exists('t3lib_div')) {
    t3lib_extMgm::addToAllTCAtypes('pages', 'tx_realurl_pathsegment,tx_cooluri_exclude,tx_cooluri_excludealways', (t3lib_div::compat_version('4.2') ? '1' : '2'), 'after:nav_title');
    t3lib_extMgm::addToAllTCAtypes('pages', 'tx_realurl_pathsegment,tx_cooluri_exclude,tx_cooluri_excludealways', (t3lib_div::compat_version('4.2') ? '' : '1,5,') . '4,254', 'after:nav_title');
    t3lib_extMgm::addToAllTCAtypes('pages_language_overlay', 'tx_realurl_pathsegment', (t3lib_div::compat_version('4.2') ? '1' : '2'), 'after:nav_title');
    t3lib_extMgm::addToAllTCAtypes('pages_language_overlay', 'tx_realurl_pathsegment', (t3lib_div::compat_version('4.2') ? '' : '1,5,') . '4,254', 'after:nav_title');
} else {
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_realurl_pathsegment,tx_cooluri_exclude,tx_cooluri_excludealways', '1,2,5,4,254', 'after:nav_title');
    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages_language_overlay', 'tx_realurl_pathsegment', '1,2,5,4,254', 'after:nav_title');
}

?>