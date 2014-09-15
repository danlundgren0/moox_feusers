<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'MOOX-FE-Users'
);

$pluginSignature = str_replace('_','',$_EXTKEY) . '_pi1';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_pi1.xml');


if (false && TYPO3_MODE === 'BE') {

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'TYPO3.' . $_EXTKEY,
		'tools',	 // Make module a submodule of 'tools'
		'management',	// Submodule key
		'',						// Position
		array(
			'Administration' => 'listUsers,showUser,editUser,deleteUser',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_init.xlf',
		)
	);

}

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'MOOX feusers');

// include pageTS
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:moox_feusers/pageTSconfig.txt">');

/***************
 * Wizard
 */
$extensionName = t3lib_div::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName);
if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'][$pluginSignature . '_wizicon'] =
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/Php/class.' . $pluginSignature . '_wizicon.php';
}

$ll = 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xml:';

/***************
 * Add extra fields to the fe_users record
 */
$newFeUserColumns = array(
	'is_company_admin' => Array (		
		'exclude' => 1,		
		'label' => $ll . 'tx_mooxfeusers_domain_model_frontenduser.is_company_admin',		
		'config' => Array (
			'type' => 'check',
			'default' => 0,
		)
	),
	'is_moox_feuser' => array(
		'label' => $ll . 'tx_mooxfeusers_domain_model_frontenduser.is_moox_feuser',
		'config' => array(
			'type' => 'passthrough'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $newFeUserColumns);

// Get the extensions's configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf'][$_EXTKEY]);

// use company admin
if (!empty($extConf['useCompanyAdmin'])) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'is_company_admin');
}

/***************
 * Icon in page tree
 */
$TCA['pages']['columns']['module']['config']['items'][] = array('MOOX-FE-Users', 'feuser', 'EXT:moox_feusers/ext_icon.gif');
t3lib_SpriteManager::addTcaTypeIcon('pages', 'contains-feusers', '../typo3conf/ext/moox_feuser/ext_icon.gif');
?>