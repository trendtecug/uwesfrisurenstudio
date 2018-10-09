<?php

/***************************************************************
 * Extension Manager/Repository config file for ext "flatmgr".
 *
 * Auto generated 28-09-2015 10:33
 *
 * Manual updates:
 * Only the data in the array - everything else is removed by next
 * writing. "version" and "dependencies" must not be touched!
 ***************************************************************/

$EM_CONF[$_EXTKEY] = array (
	'title' => 'Flat Manager',
	'description' => 'Vacation flat manager, shows the booking of vacation flats in a calendar, admin interface included, depends on rlmp_dateselectlib, xajax',
	'category' => 'plugin',
	'version' => '2.8.2',
	'state' => 'stable',
	'uploadfolder' => false,
	'createDirs' => '',
	'clearcacheonload' => true,
	'author' => 'Joachim Ruhs',
	'author_email' => 'postmaster@joachim-ruhs.de',
	'author_company' => 'Web Services Ruhs',
	'constraints' => 
	array (
		'depends' => 
		array (
			'php' => '5.3.2-5.99.99',
			'typo3' => '4.5.0-6.2.99',
			'rlmp_dateselectlib' => '0.1.7',
			'xajax' => '0.2.4',
		),
		'conflicts' => 
		array (
		),
		'suggests' => 
		array (
		),
	),
);

