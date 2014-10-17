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
			$mainModuleKey 		= "FluidTYPO3.moox_core";
			$mainModuleIcon 	= 'EXT:moox_core/ext_icon32.png';
			$mainModuleLabels 	= 'LLL:EXT:moox_core/Resources/Private/Language/MainModule.xlf';			
		} else {
			$mainModuleKey 		= "TYPO3.".$_EXTKEY;
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
		'TYPO3.' . $_EXTKEY,
		$mainModuleName,	 	// Make module a submodule of 'moox'
		'feusermanagement',	// Submodule key
		'',	// Position
		array(
			'Administration' => 'index,add,edit,delete,addGroup,editGroup,deleteGroup,toggleState,toggleMailingAllowed,import',
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
	'tstamp' => array(
		'tstamp',
		'config' => array(
			'type' => 'passthrough'
		)
	),
	'crdate' => array(
		'crdate',
		'config' => array(
			'type' => 'passthrough'
		)
	),
	'falImages' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_frontenduser.images',
		'config' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::getFileFieldTCAConfig(
			'falImages',
			array(
				'appearance' => array(
							'headerThumbnail' => array(
								'width' => '100',
								'height' => '100',
							),
						'createNewRelationLinkTitle' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_frontenduser.add_images'
						),
				// custom configuration for displaying fields in the overlay/reference table
				// to use the imageoverlayPalette instead of the basicoverlayPalette
				'foreign_types' => array(
					'0' => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_TEXT => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_IMAGE => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_AUDIO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_VIDEO => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					),
					\TYPO3\CMS\Core\Resource\File::FILETYPE_APPLICATION => array(
						'showitem' => '
							--palette--;LLL:EXT:lang/locallang_tca.xlf:sys_file_reference.imageoverlayPalette;imageoverlayPalette,
							--palette--;;filePalette'
					)
				),
			),
			$GLOBALS['TYPO3_CONF_VARS']['GFX']['imagefile_ext']
		)
	),
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

if($extConf['imageUploadFolder']==""){
	$extConf['imageUploadFolder'] = "uploads/tx_mooxfeusers";
}

$GLOBALS['TCA']['fe_users']['columns']['image']['config']['uploadfolder'] = $extConf['imageUploadFolder'];
	


// use company admin
if (!empty($extConf['useCompanyAdmin'])) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'is_company_admin');
}

/***************
	 * Icon in page tree
	 */
	$TCA['pages']['columns']['module']['config']['items'][] = array('MOOX-Frontend-Benutzer', 'mxfeuser', 'EXT:moox_feusers/ext_icon.gif');
	\TYPO3\CMS\Backend\Sprite\SpriteManager::addTcaTypeIcon('pages', 'contains-mxfeuser', '../typo3conf/ext/moox_feusers/ext_icon.gif');

?>