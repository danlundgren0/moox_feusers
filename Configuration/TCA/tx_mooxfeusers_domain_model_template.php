<?php
return array(
	'ctrl' => array(
		'title'	=> 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_template',
		'label' => 'title',
		'tstamp' => 'tstamp',
		'crdate' => 'crdate',
		'cruser_id' => 'cruser_id',
		'dividers2tabs' => TRUE,
		'sortby' => 'sorting',
		'versioningWS' => 2,
		'versioning_followPages' => TRUE,
		'origUid' => 't3_origuid',
		'languageField' => 'sys_language_uid',
		'transOrigPointerField' => 'l10n_parent',
		'transOrigDiffSourceField' => 'l10n_diffsource',
		'delete' => 'deleted',
		'enablecolumns' => array(
			'disabled' => 'hidden',
			'starttime' => 'starttime',
			'endtime' => 'endtime',
		),		
		'searchFields' => 'title,subject,template',		
		'iconfile' => \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('moox_feusers') . 'Resources/Public/Icons/tx_mooxfeusers_domain_model_template.gif',
		'hideTable' => TRUE
	),
	'interface' => array(
		'showRecordFieldList' => 'sys_language_uid, l10n_parent, l10n_diffsource, hidden, title, category, subject, template',
	),
	'types' => array(
		'1' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, title, category, subject, template,--div--;LLL:EXT:cms/locallang_ttc.xlf:tabs.access,hidden, starttime, endtime'),
	),
	'palettes' => array(
		'1' => array('showitem' => ''),
	),
	'columns' => array(
		'sys_language_uid' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.language',
			'config' => array(
				'type' => 'select',
				'foreign_table' => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xlf:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xlf:LGL.default_value', 0)
				),
			),
		),
		'l10n_parent' => array(
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.l18n_parent',
			'config' => array(
				'type' => 'select',
				'items' => array(
					array('', 0),
				),
				'foreign_table' => 'tx_mooxfeusers_domain_model_template',
				'foreign_table_where' => 'AND tx_mooxfeusers_domain_model_template.pid=###CURRENT_PID### AND tx_mooxfeusers_domain_model_template.sys_language_uid IN (-1,0)',
			),
		),
		'l10n_diffsource' => array(
			'config' => array(
				'type' => 'passthrough',
			),
		),
		't3ver_label' => array(
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.versionLabel',
			'config' => array(
				'type' => 'input',
				'size' => 30,
				'max' => 255,
			)
		),
		'hidden' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.hidden',
			'config' => array(
				'type' => 'check',
			),
		),
		'starttime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.starttime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'endtime' => array(
			'exclude' => 1,
			'l10n_mode' => 'mergeIfNotBlank',
			'label' => 'LLL:EXT:lang/locallang_general.xlf:LGL.endtime',
			'config' => array(
				'type' => 'input',
				'size' => 13,
				'max' => 20,
				'eval' => 'datetime',
				'checkbox' => 0,
				'default' => 0,
				'range' => array(
					'lower' => mktime(0, 0, 0, date('m'), date('d'), date('Y'))
				),
			),
		),
		'title' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_template.title',
			'config' => array(
				'type' => 'input',
				'size' => 40,
				'eval' => 'required,trim'
			),
		),
		'subject' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_template.subject',
			'config' => array(
				'type' => 'input',
				'size' => 40,
				'eval' => 'required,trim'
			),
		),
		'category' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_template.category',
			'config' => array(
				'type' => 'select',
				'size' => 1,
				'minitems' => 1,
				'maxitems' => 1,
				'itemsProcFunc' => 'EXT:moox_feusers/Classes/Hooks/ItemsProcFunc.php:&Tx_MooxFeusers_Hooks_ItemsProcFunc->processTemplateCategorySelector'
			),
		),
		'template' => array(
			'exclude' => 0,
			'label' => 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_db.xlf:tx_mooxfeusers_domain_model_template.template',
			'config' => array(
				'type' => 'text',
				'cols' => 40,
				'rows' => 20,
				'eval' => 'trim'
			),
		),		
	),
);
?>