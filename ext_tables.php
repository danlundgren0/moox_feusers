<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
	$_EXTKEY,
	'Pi1',
	'MOOX-FE-Users'
);

if (TYPO3_MODE === 'BE') {

    /***************
     * Register Main Module
     */
	$mainModuleName = "moox";
	if (!isset($TBE_MODULES[$mainModuleName])) {
        $temp_TBE_MODULES = array();
        foreach ($TBE_MODULES as $key => $val) {
            if ($key == 'web') {
                $temp_TBE_MODULES[$key] = $val;
                $temp_TBE_MODULES[$mainModuleName] = '';
            } else {
                $temp_TBE_MODULES[$key] = $val;
            }
        }
        $TBE_MODULES = $temp_TBE_MODULES;
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('moox_core')) {
			$mainModuleKey 		= "DCNGmbH.moox_core";
			$mainModuleIcon 	= 'EXT:moox_core/ext_icon32.png';
			$mainModuleLabels 	= 'LLL:EXT:moox_core/Resources/Private/Language/MainModule.xlf';			
		} else {
			$mainModuleKey 		= "DCNGmbH.".$_EXTKEY;
			$mainModuleIcon 	= 'EXT:'.$_EXTKEY.'/Resources/Public/Moox/MainModuleExtIcon.png';
			$mainModuleLabels 	= 'LLL:EXT:'.$_EXTKEY.'/Resources/Public/Moox/MainModule.xlf';
		}
		\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
			$mainModuleKey,
			$mainModuleName,
			'',
			'',
			array(),
			array(
				'access' => 'user,group',
				'icon'   => $mainModuleIcon,
				'labels' => $mainModuleLabels,
			)
		);
    } 

	/**
	 * Registers a Backend Module
	 */
	\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
		'DCNGmbH.' . $_EXTKEY,
		$mainModuleName,	 	// Make module a submodule of 'moox'
		'feusermanagement',	// Submodule key
		'',	// Position
		array(
			'Administration' => 'index,add,edit,delete,addGroup,editGroup,deleteGroup,toggleState,toggleDisallowMailing,changeGroup,import,moveToFolder',
			'Template' => 'index,add,edit,delete,previewIframe',
			'Import' => 'index',
		),
		array(
			'access' => 'user,group',
			'icon'   => 'EXT:' . $_EXTKEY . '/ext_icon.gif',
			'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_init.xlf',
		)
	);
}

$pluginSignature = str_replace('_','',$_EXTKEY) . '_pi1';
$TCA['tt_content']['types']['list']['subtypes_addlist'][$pluginSignature] = 'pi_flexform';
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue($pluginSignature, 'FILE:EXT:' . $_EXTKEY . '/Configuration/FlexForms/flexform_pi1.xml');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile($_EXTKEY, 'Configuration/TypoScript', 'MOOX feusers');

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addLLrefForTCAdescr('tx_mooxfeusers_domain_model_template', 'EXT:moox_mailer/Resources/Private/Language/locallang_csh_tx_mooxfeusers_domain_model_template.xlf');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::allowTableOnStandardPages('tx_mooxfeusers_domain_model_template');

// include pageTS
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig('<INCLUDE_TYPOSCRIPT: source="FILE:EXT:moox_feusers/pageTSconfig.txt">');

/***************
 * Wizard
 */
$extensionName = \TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($_EXTKEY);
$pluginSignature = strtolower($extensionName);
if (TYPO3_MODE == 'BE') {
	$TBE_MODULES_EXT['xMOD_db_new_content_el']['addElClasses'][$pluginSignature . '_wizicon'] =
		\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath($_EXTKEY) . 'Resources/Private/Php/class.' . $pluginSignature . '_wizicon.php';
}

/***************
* Icon in page tree
*/
$TCA['pages']['columns']['module']['config']['items'][] = array('MOOX-Frontend-Benutzer', 'mxfeuser', 'EXT:moox_feusers/ext_icon.gif');
\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-mxfeuser', '../typo3conf/ext/moox_feusers/ext_icon.gif');

?>