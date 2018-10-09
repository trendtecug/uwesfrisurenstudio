<?php
/*
 * Register necessary class names with autoloader
 */

$flatmgrExtPath = t3lib_extMgm::extPath('flatmgr');

$arr = array(

	'tx_flatmgr_controller' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_controller.php',
	'tx_flatmgr_admin_controller' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_admin_controller.php',
	
	'tx_flatmgr_view' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_view.php',
	'tx_flatmgr_model' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_model.php', 
	'tx_flatmgr_snoopy' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_snoopy.php',
	
	'tx_flatmgr_admin_model' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_admin_model.php',
	'tx_flatmgr_admin_view' => $flatmgrExtPath . 'pi1/class.tx_flatmgr_admin_view.php',
	
);

return $arr;
?>
