<?php
return array(
	'BE' => array(
		'debug' => TRUE,
		'explicitADmode' => 'explicitAllow',
		'installToolPassword' => '$P$CfIbAPfSkEoaHAimov2xrBBwsTdUAJ.',
		'loginSecurityLevel' => 'rsa',
		'versionNumberInFilename' => '0',
	),
	'DB' => array(
		'database' => 'ttug_uwesfrisurenstudio',
		'extTablesDefinitionScript' => 'extTables.php',
		'host' => '127.0.0.1',
		'password' => 'sdt69W2*',
		'port' => 3306,
		'socket' => '',
		'username' => 'ttug',
	),
	'EXT' => array(
		'extConf' => array(
			'cooluri' => 'a:3:{s:6:"LANGID";s:1:"L";s:7:"XMLPATH";s:10:"typo3conf/";s:11:"MULTIDOMAIN";s:1:"1";}',
			'flatmgr' => 'a:0:{}',
			'form' => 'a:0:{}',
			'js_contact_form' => 'a:2:{s:15:"storeCompatName";s:1:"1";s:16:"nameCompatFormat";s:9:"%1$s %3$s";}',
			'recycler' => 'a:0:{}',
			'rlmp_dateselectlib' => 'a:0:{}',
			'rsaauth' => 'a:1:{s:18:"temporaryDirectory";s:0:"";}',
			'saltedpasswords' => 'a:2:{s:3:"BE.";a:4:{s:21:"saltedPWHashingMethod";s:41:"TYPO3\\CMS\\Saltedpasswords\\Salt\\PhpassSalt";s:11:"forceSalted";i:0;s:15:"onlyAuthService";i:0;s:12:"updatePasswd";i:1;}s:3:"FE.";a:5:{s:7:"enabled";i:1;s:21:"saltedPWHashingMethod";s:41:"TYPO3\\CMS\\Saltedpasswords\\Salt\\PhpassSalt";s:11:"forceSalted";i:0;s:15:"onlyAuthService";i:0;s:12:"updatePasswd";i:1;}}',
			'xajax' => 'a:0:{}',
		),
	),
	'EXTCONF' => array(
		'lang' => array(
			'availableLanguages' => array(
				'fr',
				'de',
				'it',
				'ru',
			),
		),
	),
	'FE' => array(
		'activateContentAdapter' => FALSE,
		'debug' => FALSE,
		'loginSecurityLevel' => 'rsa',
	),
	'GFX' => array(
		'colorspace' => 'sRGB',
		'gdlib_png' => '1',
		'im' => 1,
		'im_mask_temp_ext_gif' => 1,
		'im_path' => '/usr/bin/',
		'im_path_lzw' => '/usr/bin/',
		'im_v5effects' => 1,
		'im_version_5' => 'im6',
		'image_processing' => 1,
		'jpg_quality' => '80',
	),
	'MAIL' => array(
		'defaultMailFromAddress' => 'uwesfrisurenstudio@web.de',
		'defaultMailFromName' => 'Uwe Brosius',
		'transport' => 'smtp',
		'transport_smtp_username' => 'accounting@trendtec.info',
		'transport_smtp_password' => 'Otafukona493',
		'transport_smtp_server' => 'smtp.trendtec.info',
	),
	'SYS' => array(
		'caching' => array(
			'cacheConfigurations' => array(
				'extbase_object' => array(
					'backend' => 'TYPO3\\CMS\\Core\\Cache\\Backend\\Typo3DatabaseBackend',
					'frontend' => 'TYPO3\\CMS\\Core\\Cache\\Frontend\\VariableFrontend',
					'groups' => array(
						'system',
					),
					'options' => array(
						'defaultLifetime' => 0,
					),
				),
			),
		),
		'clearCacheSystem' => FALSE,
		'compat_version' => '6.2',
		'devIPmask' => '',
		'displayErrors' => 0,
		'enableDeprecationLog' => FALSE,
		'encryptionKey' => '742cb166014a551a555a1b7dff0bcea9e4f69b3cd5d22d57dea3faf5ef0212bfa6adb48fafca55b3d2695b3d0de6f37a',
		'isInitialInstallationInProgress' => FALSE,
		'sitename' => 'Friseur Edingen-Neckarhausen Friseure Mannheim Heidelberg Home',
		'sqlDebug' => 0,
		'systemLogLevel' => 2,
		't3lib_cs_convMethod' => 'mbstring',
		't3lib_cs_utils' => 'mbstring',
		'trustedHostsPattern' => 'www.uwesfrisurenstudio.de.trendtec.de',
	),
);
?>
