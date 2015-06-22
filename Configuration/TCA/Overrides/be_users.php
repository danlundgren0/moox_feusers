<?php
if (!defined('TYPO3_MODE')) {
	die ('Access denied.');
}

$ll = 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xml:';

/***************
 * Add extra fields to the be_users record
 */
$newBeUsersColumns = array(	
	'moox_feusers_custom_csv_export_fields' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:be_users.moox_feusers_custom_csv_export_fields',
		'config' => array(
			'type' => 'input',
			'size' => 40,
			'eval' => 'trim'
		),
	),
	'moox_feusers_custom_csv_import_fields' => array(
		'exclude' => 0,
		'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:be_users.moox_feusers_custom_csv_import_fields',
		'config' => array(
			'type' => 'input',
			'size' => 40,
			'eval' => 'trim'
		),
	),
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addTCAcolumns('be_users',$newBeUsersColumns,1);
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('be_users', 'moox_feusers_custom_csv_export_fields,moox_feusers_custom_csv_import_fields', '', 'after:file_mountpoints');
