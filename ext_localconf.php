<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Get the extensions's configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// use company admin
$actionList = "profile";
if (!empty($extConf['useCompanyAdmin'])) {
	$actionList .= ",list,add,edit,delete";
}
$actionList .= ",passwordRecovery,newPassword,error";

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
	'DCNGmbH.' . $_EXTKEY,
	'Pi1',
	array(
		'FrontendUser' => $actionList,
	),
	// non-cacheable actions
	array(
		'FrontendUser' => $actionList,	
	)
);

// Hook backend preview of pi1
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['cms/layout/class.tx_cms_layout.php']['tt_content_drawItem'][$_EXTKEY] = \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY).'Classes/Hooks/PageLayoutViewDrawItem.php:TxMooxFeusersPageLayoutViewDrawItem';
	
?>