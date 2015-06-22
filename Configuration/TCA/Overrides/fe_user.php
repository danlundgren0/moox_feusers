<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

// Get the extensions's configuration
$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);

$ll = 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xml:';

/***************
 * Add extra fields to the fe_users record
 */
$newFeUserColumns = array(	
	'pid' => array(
		'label' => 'pid',
		'config' => array(
			'type' => 'passthrough'
		)
	),
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
	'gender' => Array (		
		'exclude' => 1,		
		'label'	=> 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxmailer_domain_model_frontenduser.gender',		
		'config' => Array (
			'type' => 'select',
			'items' => Array (
					Array('LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxmailer_domain_model_frontenduser.gender.none', 0),
					Array('LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxmailer_domain_model_frontenduser.gender.male', 1),
					Array('LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxmailer_domain_model_frontenduser.gender.female', 2),					
			),			
			'size' => 1,	
			'maxitems' => 1,
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
	'password_recovery_hash' => array(
		'label' => 'password_recovery_hash',
		'config' => array(
			'type' => 'passthrough'
		)
	),
	'password_recovery_tstamp' => array(
		'label' => 'password_recovery_tstamp',
		'config' => array(
			'type' => 'passthrough'
		)
	),
	'sorted_usergroup' => array(
		'label' => 'sorted_usergroup',
		'config' => array(
			'type' => 'passthrough'
		)
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('fe_users', $newFeUserColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'gender', '','before:name');

if($extConf['imageUploadFolder']==""){
	$extConf['imageUploadFolder'] = "uploads/tx_mooxfeusers";
}

$GLOBALS['TCA']['fe_users']['columns']['image']['config']['uploadfolder'] = $extConf['imageUploadFolder'];

// use company admin
if (!empty($extConf['useCompanyAdmin'])) {
	\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('fe_users', 'is_company_admin');
}