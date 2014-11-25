<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2014 Dominic Martin <dm@dcn.de>, DCN GmbH
 *  
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 *
 *
 * @package moox_feusers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class Tx_MooxFeusers_Hooks_ItemsProcFunc {

	/**
	 * Itemsproc function to extend the selection of storage pids in flexform
	 *
	 * @param array &$config configuration array
	 * @return void
	 */
	public function processStoragePidSelector(array &$config,&$pObj) {
		for($i=0;$i<count($config['items']);$i++){
			if($config['items'][$i][1]!="" && $config['items'][$i][1]!="TS"){
				$config['items'][$i][0] = $config['items'][$i][0]." [PID: ".$config['items'][$i][1]."]";
			} elseif($config['items'][$i][1]=="TS"){
				$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
				$configurationManager = $objectManager->get('\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface');				
				$configuration = $configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,"MooxFeusers");								
				if($configuration['persistence']['storagePid']!=""){
					$pageRepository = $objectManager->get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
					$pageInfo = $pageRepository->getPage($configuration['persistence']['storagePid']);
					$config['items'][$i][0] = $config['items'][$i][0]." ".$pageInfo['title']." [PID: ".$configuration['persistence']['storagePid']."]";
				}
			}
		}
	}
	
	/**
	 * Itemsproc function to generate the selection of template category
	 *
	 * @param array &$config configuration array
	 * @return void
	 */
	public function processTemplateCategorySelector(array &$config,&$pObj) {
		
		$categories = \TYPO3\MooxFeusers\Controller\TemplateController::getTemplateCategories();
		
		$config['items'] = array();
		
		foreach($categories AS $key => $category){
			$config['items'][] = array(0 => $category, 1 => $key);
		}
		
	}
	
	/**
	 * Itemsproc function to generate the selection of email templates
	 *
	 * @param array &$config configuration array
	 * @return void
	 */
	public function processEmailTemplateSelector(array &$config,&$pObj) {
		
		$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
		$templateRepository = $objectManager->get('TYPO3\\MooxFeusers\\Domain\\Repository\\TemplateRepository');
		$templates = $templateRepository->findAll(FALSE);
		$config['items'] = array();
		
		foreach($templates AS $template){
			$config['items'][] = array(0 => $template->getTitle(), 1 => $template->getUid());
		}
		
	}
	
	/**
	 * Itemsproc function to generate the selection of switchable controller actions
	 *
	 * @param array &$config configuration array
	 * @return void
	 */
	public function processPi1SwitchableControllerActionsSelector(array &$config,&$pObj) {
		
		$config['items'] = array();
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);
		
		$actions 					= array();
		$actionsCompanyAdmin 		= array();
		$actionsProfile 			= array("profile");		
		
		if (!empty($extConf['useCompanyAdmin'])) {
			$actionsCompanyAdmin   	= array("list","add","edit","delete");			
		}
		
		$actionsRecovery  			= array("passwordRecovery","newPassword");		
		$actionsError  				= array("error");		
		
		$actionsProfileTmp 			= array_merge($actionsProfile,$actionsCompanyAdmin,$actionsError);
		$actionsRecoveryTmp			= array_merge($actionsRecovery,$actionsError);
		
		$actionsProfile = array();
		foreach($actionsProfileTmp AS $actionProfile){
			$actionsProfile[] = "FrontendUser->".$actionProfile;
		}
		$labelProfile = $GLOBALS['LANG']->sL('LLL:EXT:moox_feusers/Resources/Private/Language/locallang_be.xml:pi1_controller_selection.frontenduser_profile');
		
		$actionsRecovery = array();
		foreach($actionsRecoveryTmp AS $actionRecovery){
			$actionsRecovery[] = "FrontendUser->".$actionRecovery;
		}
		$labelRecovery = $GLOBALS['LANG']->sL('LLL:EXT:moox_feusers/Resources/Private/Language/locallang_be.xml:pi1_controller_selection.frontenduser_passwordrecovery');
		
		$config['items'][] = array(0 => $labelProfile, 1 => implode(";",$actionsProfile));
		$config['items'][] = array(0 => $labelRecovery, 1 => implode(";",$actionsRecovery));
		$config['config']['default'] = implode(";",$actionsProfile);
		
	}
}