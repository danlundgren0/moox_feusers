<?php
namespace TYPO3\MooxFeusers\Controller;

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
 
 use \TYPO3\CMS\Extbase\Utility\LocalizationUtility;
 
/**
 *
 *
 * @package moox_feusers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class FrontendUserController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

	/**
	 * frontendUserRepository
	 *
	 * @var \TYPO3\MooxFeusers\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository
	 * @inject
	 */
	protected $frontendUserGroupRepository;
	
	/**
	 * templateRepository
	 *
	 * @var \TYPO3\MooxFeusers\Domain\Repository\TemplateRepository
	 * @inject
	 */
	protected $templateRepository;
	
	/**
	 * extConf
	 *
	 * @var boolean
	 */
	protected $extConf;
	
	/**
	 * storagePids
	 *
	 * @var array 	
	 */
	protected $storagePids;
	
	public $langLL = "LLL:EXT:moox_feusers/Resources/Private/Language/locallang.xlf:tx_mooxfeusers_frontenduser.";
	
	/**
     *
     * @return void
     */
    public function initializeAction() {					
		parent::initializeAction();
		
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);
		
		$this->initializeStorageSettings();		
    }

	/**
	 * initialize storage settings
	 *	 
	 * @return void
	 */
	protected function initializeStorageSettings() {
				
		$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);		
		
		if($this->settings['storagePids']!=""){
			$this->setStoragePids(explode(",",$this->settings['storagePids']));
		} else {
			$this->setStoragePids(array());
		}
		
		if(!empty($this->settings['storagePid']) && $this->settings['storagePid']!="TS"){
			if (empty($configuration['persistence']['storagePid'])) {
				$storagePids['persistence']['storagePid'] = $this->settings['storagePid'];
				$this->configurationManager->setConfiguration(array_merge($configuration, $storagePids));
			} else {
				$configuration['persistence']['storagePid'] = $this->settings['storagePid'];
				$this->configurationManager->setConfiguration($configuration);
			}
		}		
	}
	
	
	/**
	 * action list
	 *
	 * @return void
	 */
	public function listAction() {
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);		
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		if(TRUE === $accessControllService->hasLoggedInFrontendUser()) {
            $frontendUser = $this->frontendUserRepository->findByUid($accessControllService->getFrontendUserUid());		  
        } 
		
		if($frontendUser && $accessControllService->isAccessAllowed($frontendUser)) {
            
			if($extConf['useCompanyAdmin'] && $frontendUser->getIsCompanyAdmin() && $frontendUser->getCompany()!=""){
			
				$this->view->assign('frontendUser', $frontendUser);            			
				$this->view->assign('companyUsers', $this->frontendUserRepository->findByCompany($this->storagePids,$frontendUser->getUid(),$frontendUser->getCompany()));
				
			} else {
				$this->redirect("profile");
			}
			
        } else {		
			$this->redirect("error");
        }				
	}
		
	/**
	 * action add
	 *
	 * @return void
	 */
	public function addAction() {				
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);
		
		if($this->settings['usernameMinlength']<3){
			$this->settings['usernameMinlength'] = 3;
		}
		
		if($this->settings['passwordMinlength']<3){
			$this->settings['passwordMinlength'] = 3;
		}
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		$requestArguments = $this->request->getArguments();
		
		$adminUser = $this->frontendUserRepository->findByUid($accessControllService->getFrontendUserUid());
		
		$addUser = $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Model\\FrontendUser');	
		
		$groupSelect = array();
		
		if($this->settings['fe_group']!=""){
			$groups = explode(",",$this->settings['fe_group']);
			foreach($groups AS $group){
				$loadedGroup = $this->frontendUserGroupRepository->findByUid($group);
				$groupSelect[$group] = $loadedGroup->getTitle();				
			}
		}
		
		if($accessControllService->isCreateAllowed($adminUser)){
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if($requestArguments['do'] == 'save'){
				
				if(trim($requestArguments['FrontendUser']['username'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.username.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.username.missing', $this->extensionName )
												);					
					$allErrors['username']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(strlen(trim($requestArguments['FrontendUser']['username']))<$this->settings['usernameMinlength']){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.username.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.username.wronglength', $this->extensionName, array($this->settings['usernameMinlength']) )
												);					
					$allErrors['username']	= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif($this->frontendUserRepository->findByUsername($this->storagePids,trim($requestArguments['FrontendUser']['username']))->count()>0){
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.username.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.username.existing', $this->extensionName )
												);					
					$allErrors['username']	= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(false && trim($requestArguments['FrontendUser']['name'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.name.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.name.missing', $this->extensionName )
												);					
					$allErrors['name']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(false && trim($requestArguments['FrontendUser']['email'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.missing', $this->extensionName )
												);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(trim($requestArguments['FrontendUser']['email'])!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($requestArguments['FrontendUser']['email']))){
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.invalid', $this->extensionName )
												);
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				
				if(trim($requestArguments['FrontendUser']['password'])==""){
						
					$this->view->assign('password',trim($requestArguments['FrontendUser']['password']));
					$this->view->assign('passwordRepeat',trim($requestArguments['FrontendUser']['passwordRepeat']));
						
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.missing', $this->extensionName )
												);														
					$allErrors['password']	= true;
					$passwordError 			= true;
					$hasErrors 				= true;
					
				} elseif(strlen(trim($requestArguments['FrontendUser']['password']))<$this->settings['passwordMinlength']){
						
					$this->view->assign('password',trim($requestArguments['FrontendUser']['password']));
					$this->view->assign('passwordRepeat',trim($requestArguments['FrontendUser']['passwordRepeat']));
						
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.wronglength', $this->extensionName, array($this->settings['passwordMinlength']) )
												);	
					$allErrors['password']	= true;
					$passwordError 			= true;
					$hasErrors 				= true;
				}
				if(trim($requestArguments['FrontendUser']['password'])!=trim($requestArguments['FrontendUser']['passwordRepeat'])){
						
					$this->view->assign('password',trim($requestArguments['FrontendUser']['password']));
					$this->view->assign('passwordRepeat',trim($requestArguments['FrontendUser']['passwordRepeat']));
						
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.notmatching', $this->extensionName )
												);												
					$allErrors['repeat']	= true;
					$passwordError 			= true;
					$hasErrors 				= true;
				}
				
				if(!$passwordError){
					$addUser->setPassword(trim($requestArguments['FrontendUser']['password']));				
				}
								
				/*
				foreach($adminUser->getUsergroup() AS $group){
					$loadedGroup = $this->frontendUserGroupRepository->findByUid($group->getUid());					
					$addUser->addUsergroup($loadedGroup);					
				}
				*/
				
				if(trim($requestArguments['FrontendUser']['usergroup'])!=""){
					$groups = explode(",",trim($requestArguments['FrontendUser']['usergroup']));
					foreach($groups AS $group){
						if(isset($groupSelect[$group])){
							$loadedGroup = $this->frontendUserGroupRepository->findByUid($group);																
							$addUser->addUsergroup($loadedGroup);	
						}
					}
				}
												
				$addUser->setIsMooxFeuser(1);				
				$addUser->setCompany($adminUser->getCompany());
				$addUser->setUsername(trim($requestArguments['FrontendUser']['username']));
				$addUser->setGender(trim($requestArguments['FrontendUser']['gender']));
				$addUser->setTitle(trim($requestArguments['FrontendUser']['title']));
				$name = "";
				if(trim($requestArguments['FrontendUser']['first_name'])!=""){
					$name = trim($requestArguments['FrontendUser']['first_name']);
				}
				if(trim($requestArguments['FrontendUser']['middle_name'])!="" && $name==""){
					$name = trim($requestArguments['FrontendUser']['middle_name']);
				} elseif(trim($requestArguments['FrontendUser']['middle_name'])!="" && $name!=""){
					$name .= " ".trim($requestArguments['FrontendUser']['middle_name']);
				}
				if(trim($requestArguments['FrontendUser']['last_name'])!="" && $name==""){
					$name = trim($requestArguments['FrontendUser']['last_name']);
				} elseif(trim($requestArguments['FrontendUser']['last_name'])!="" && $name!=""){
					$name .= " ".trim($requestArguments['FrontendUser']['last_name']);
				}
				$addUser->setName($name);
				$addUser->setFirstName(trim($requestArguments['FrontendUser']['first_name']));
				$addUser->setMiddleName(trim($requestArguments['FrontendUser']['middle_name']));
				$addUser->setLastName(trim($requestArguments['FrontendUser']['last_name']));
				$addUser->setAddress(trim($requestArguments['FrontendUser']['address']));
				$addUser->setZip(trim($requestArguments['FrontendUser']['zip']));
				$addUser->setCity(trim($requestArguments['FrontendUser']['city']));
				$addUser->setCountry(trim($requestArguments['FrontendUser']['country']));
				$addUser->setTelephone(trim($requestArguments['FrontendUser']['telephone']));
				$addUser->setFax(trim($requestArguments['FrontendUser']['fax']));
				$addUser->setEmail(trim($requestArguments['FrontendUser']['email']));
				$addUser->setWww(trim($requestArguments['FrontendUser']['www']));								
				
				if(!$hasErrors){					
					$this->frontendUserRepository->add($addUser);								
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
					$this->flashMessageContainer->add(
						'&nbsp;', 
						LocalizationUtility::translate( $this->langLL.'messages.useradded', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);				
					$this->redirect("list");					
				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate( $this->langLL.'messages.adderror', $this->extensionName ), 
						LocalizationUtility::translate( $this->langLL.'messages.adderror.header', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					foreach($errorMessages AS $errorMessage){
						$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					}
					
					$this->view->assign('allErrors', $allErrors);					
					
					if($dataError){
						$this->view->assign('dataError',1);
					}
					
					if($passwordError){
						$this->view->assign('passwordError',1);
					}
					
					$this->view->assign('frontendUser', $addUser);
					$this->view->assign('adminUser', $adminUser);
					$this->view->assign('self', 1);					
				}
	
			} else {			
				$this->view->assign('frontendUser', $addUser);
				$this->view->assign('adminUser', $adminUser);
				$this->view->assign('self', 1);				
			}
			
			$usergroups = $addUser->getUsergroup();
			foreach($usergroups AS $usergroup){
				$usergroup = $usergroup->getUid();
				break;
			}
			
			$this->view->assign('usergroup', ($requestArguments['FrontendUser']['usergroup'])?$requestArguments['FrontendUser']['usergroup']:$usergroup);			
			$this->view->assign('fegroups', $groupSelect);
			
			if($extConf['useCompanyAdmin']){
				$this->view->assign('useCompanyAdmin', 1);
			}
			
			$this->view->assign('action', 'add');
		} else {								
			$this->redirect("list");
		}				
	}
	
	/**
	 * action edit
	 *	
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $editUser
	 * @return void
	 */
	public function editAction(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $editUser = NULL) {			
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);
		
		if($this->settings['passwordMinlength']<3){
			$this->settings['passwordMinlength'] = 3;
		}
		
		$groupSelect = array();
		
		if($this->settings['fe_group']!=""){
			$groups = explode(",",$this->settings['fe_group']);
			foreach($groups AS $group){
				$loadedGroup = $this->frontendUserGroupRepository->findByUid($group);
				$groupSelect[$group] = $loadedGroup->getTitle();				
			}
		}
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		$requestArguments = $this->request->getArguments();
		
		$adminUser = $this->frontendUserRepository->findByUid($accessControllService->getFrontendUserUid());
		
		if(!is_object($editUser)){						
			$editUser = $this->frontendUserRepository->findByUid($requestArguments['editUser']);			
		}
				
		if($accessControllService->isEditAllowed($adminUser,$editUser)){
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if($requestArguments['do'] == 'save'){
				
				if(false && trim($requestArguments['FrontendUser']['name'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.name.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.name.missing', $this->extensionName )
												);			
					$allErrors['name']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(false && trim($requestArguments['FrontendUser']['email'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.missing', $this->extensionName )
												);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(trim($requestArguments['FrontendUser']['email'])!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($requestArguments['FrontendUser']['email']))){
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.invalid', $this->extensionName )
												);
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(trim($requestArguments['FrontendUser']['password'])!=""){
					if(strlen(trim($requestArguments['FrontendUser']['password']))<$this->settings['passwordMinlength']){
						
						$this->view->assign('password',trim($requestArguments['FrontendUser']['password']));
						$this->view->assign('passwordRepeat',trim($requestArguments['FrontendUser']['passwordRepeat']));
						
						$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.wronglength', $this->extensionName, array($this->settings['passwordMinlength']) )
													);
						$allErrors['password']	= true;
						$passwordError 			= true;
						$hasErrors 				= true;
					} 
					
					if(!$passwordError){
						$editUser->setPassword(trim($requestArguments['FrontendUser']['password']));
					}
				}
								
				if(count($groups)>0){
					foreach($groups AS $group){
						$loadedGroup = $this->frontendUserGroupRepository->findByUid($group);					
						$editUser->removeUsergroup($loadedGroup);					
					}
				}
				
				if(trim($requestArguments['FrontendUser']['usergroup'])!=""){
					$groups = explode(",",trim($requestArguments['FrontendUser']['usergroup']));
					foreach($groups AS $group){
						if(isset($groupSelect[$group])){
							$loadedGroup = $this->frontendUserGroupRepository->findByUid($group);																
							$editUser->addUsergroup($loadedGroup);	
						}
					}
				}
				
				if(!$extConf['useCompanyAdmin']){
					$editUser->setCompany(trim($requestArguments['FrontendUser']['company']));
				}
				$editUser->setGender(trim($requestArguments['FrontendUser']['gender']));
				$editUser->setTitle(trim($requestArguments['FrontendUser']['title']));				
				$name = "";
				if(trim($requestArguments['FrontendUser']['first_name'])!=""){
					$name = trim($requestArguments['FrontendUser']['first_name']);
				}
				if(trim($requestArguments['FrontendUser']['middle_name'])!="" && $name==""){
					$name = trim($requestArguments['FrontendUser']['middle_name']);
				} elseif(trim($requestArguments['FrontendUser']['middle_name'])!="" && $name!=""){
					$name .= " ".trim($requestArguments['FrontendUser']['middle_name']);
				}
				if(trim($requestArguments['FrontendUser']['last_name'])!="" && $name==""){
					$name = trim($requestArguments['FrontendUser']['last_name']);
				} elseif(trim($requestArguments['FrontendUser']['last_name'])!="" && $name!=""){
					$name .= " ".trim($requestArguments['FrontendUser']['last_name']);
				}
				$editUser->setName($name);				
				$editUser->setFirstName(trim($requestArguments['FrontendUser']['first_name']));
				$editUser->setMiddleName(trim($requestArguments['FrontendUser']['middle_name']));
				$editUser->setLastName(trim($requestArguments['FrontendUser']['last_name']));
				$editUser->setAddress(trim($requestArguments['FrontendUser']['address']));
				$editUser->setZip(trim($requestArguments['FrontendUser']['zip']));
				$editUser->setCity(trim($requestArguments['FrontendUser']['city']));
				$editUser->setCountry(trim($requestArguments['FrontendUser']['country']));
				$editUser->setTelephone(trim($requestArguments['FrontendUser']['telephone']));
				$editUser->setFax(trim($requestArguments['FrontendUser']['fax']));
				$editUser->setEmail(trim($requestArguments['FrontendUser']['email']));
				$editUser->setWww(trim($requestArguments['FrontendUser']['www']));								
				
				if(!$hasErrors){					
					$this->frontendUserRepository->update($editUser);								
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
					$this->flashMessageContainer->add(
						LocalizationUtility::translate( $this->langLL.'messages.usersaved', $this->extensionName ), 
						LocalizationUtility::translate( $this->langLL.'messages.usersaved.header', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
					$this->redirect("list");					
				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate( $this->langLL.'messages.editerror', $this->extensionName ), 
						LocalizationUtility::translate( $this->langLL.'messages.editerror.header', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					foreach($errorMessages AS $errorMessage){
						$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					}
					
					$this->view->assign('allErrors', $allErrors);					
					
					if($dataError){
						$this->view->assign('dataError',1);
					}
					
					if($passwordError){
						$this->view->assign('passwordError',1);
					}
					
					$this->view->assign('frontendUser', $editUser);					
					$this->view->assign('self', 0);
				}
	
			} else {			
				$this->view->assign('frontendUser', $editUser);				
				$this->view->assign('self', 0);
			}
			
			$usergroups = $editUser->getUsergroup();
			foreach($usergroups AS $usergroup){
				if(isset($groupSelect[$usergroup->getUid()])){
					$usergroup = $usergroup->getUid();
					break;
				}
			}
			
			$this->view->assign('usergroup', ($requestArguments['FrontendUser']['usergroup'])?$requestArguments['FrontendUser']['usergroup']:$usergroup);
			$this->view->assign('fegroups', $groupSelect);
			
			if($extConf['useCompanyAdmin']){
				$this->view->assign('useCompanyAdmin', 1);
			}
			
			$this->view->assign('action', 'edit');
			
		} else {						
			$this->redirect("list");
		}
	}
	
	/**
	 * action delete
	 *
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $deleteUser
	 * @return void
	 */
	public function deleteAction(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $deleteUser = NULL) {				
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		$requestArguments = $this->request->getArguments();
		
		$adminUser = $this->frontendUserRepository->findByUid($accessControllService->getFrontendUserUid());
		
		if(!is_object($deleteUser)){						
			$deleteUser = $this->frontendUserRepository->findByUid($requestArguments['deleteUser']);			
		}
				
		if($accessControllService->isDeleteAllowed($adminUser,$deleteUser)){
			
			$this->frontendUserRepository->remove($deleteUser);
			$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
			$this->flashMessageContainer->add(
				'&nbsp;', 
				LocalizationUtility::translate( $this->langLL.'messages.userdeleted', $this->extensionName ), 
				\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			
		} 
		
		$this->redirect("list");
	}	
	
	/**
	 * renew recovery hash
	 *
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $user
	 * @param string $hash	 
	 * @return void
	 */
	public function renewRecoveryHash(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $user = NULL, $hash) {				
		
		$user->setPasswordRecoveryHash($hash);
		$user->setPasswordRecoveryTstamp(time());
		$this->frontendUserRepository->update($user);
		$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
	}	
	
	/**
	 * action profile
	 *
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $frontendUser
	 * @return void
	 */
	public function profileAction(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $frontendUser = NULL) {					
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);		
		
		if($this->settings['passwordMinlength']<3){
			$this->settings['passwordMinlength'] = 3;
		}			
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		// Get all the request arguments                    
		$frontendUser = $this->frontendUserRepository->findByUid($accessControllService->getFrontendUserUid());
       
		if($frontendUser && $accessControllService->isAccessAllowed($frontendUser)) {
            
			$requestArguments = $this->request->getArguments();
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if($requestArguments['do'] == 'save'){
				
				if(false && trim($requestArguments['FrontendUser']['name'])==""){										
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.name.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.name.missing', $this->extensionName )
												);
					$allErrors['name']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(false && trim($requestArguments['FrontendUser']['email'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.missing', $this->extensionName )
												);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(trim($requestArguments['FrontendUser']['email'])!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($requestArguments['FrontendUser']['email']))){				
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.invalid', $this->extensionName )
												);
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(trim($requestArguments['FrontendUser']['password'])!=""){
					if(strlen(trim($requestArguments['FrontendUser']['password']))<$this->settings['passwordMinlength']){
						
						$this->view->assign('password',trim($requestArguments['FrontendUser']['password']));
						$this->view->assign('passwordRepeat',trim($requestArguments['FrontendUser']['passwordRepeat']));
												
						$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.wronglength', $this->extensionName, array($this->settings['passwordMinlength']) )
												);
						$allErrors['password']	= true;
						$passwordError 			= true;
						$hasErrors 				= true;
					} 
					if(trim($requestArguments['FrontendUser']['password'])!=trim($requestArguments['FrontendUser']['passwordRepeat'])){
						
						$this->view->assign('password',trim($requestArguments['FrontendUser']['password']));
						$this->view->assign('passwordRepeat',trim($requestArguments['FrontendUser']['passwordRepeat']));
						
						$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.notmatching', $this->extensionName )
												);																	
						$allErrors['repeat']	= true;
						$passwordError 			= true;
						$hasErrors 				= true;
					}
					if(!$passwordError){
						$frontendUser->setPassword(trim($requestArguments['FrontendUser']['password']));
					}
				}

				$frontendUser->setCompany(trim($requestArguments['FrontendUser']['company']));
				$frontendUser->setGender(trim($requestArguments['FrontendUser']['gender']));
				$frontendUser->setTitle(trim($requestArguments['FrontendUser']['title']));
				$name = "";
				if(trim($requestArguments['FrontendUser']['first_name'])!=""){
					$name = trim($requestArguments['FrontendUser']['first_name']);
				}
				if(trim($requestArguments['FrontendUser']['middle_name'])!="" && $name==""){
					$name = trim($requestArguments['FrontendUser']['middle_name']);
				} else {
					$name .= " ".trim($requestArguments['FrontendUser']['middle_name']);
				}
				if(trim($requestArguments['FrontendUser']['last_name'])!="" && $name==""){
					$name = trim($requestArguments['FrontendUser']['last_name']);
				} else {
					$name .= " ".trim($requestArguments['FrontendUser']['last_name']);
				}
				$frontendUser->setName($name);					
				$frontendUser->setFirstName(trim($requestArguments['FrontendUser']['first_name']));
				$frontendUser->setMiddleName(trim($requestArguments['FrontendUser']['middle_name']));
				$frontendUser->setLastName(trim($requestArguments['FrontendUser']['last_name']));
				$frontendUser->setAddress(trim($requestArguments['FrontendUser']['address']));
				$frontendUser->setZip(trim($requestArguments['FrontendUser']['zip']));
				$frontendUser->setCity(trim($requestArguments['FrontendUser']['city']));
				$frontendUser->setCountry(trim($requestArguments['FrontendUser']['country']));
				$frontendUser->setTelephone(trim($requestArguments['FrontendUser']['telephone']));
				$frontendUser->setFax(trim($requestArguments['FrontendUser']['fax']));
				$frontendUser->setEmail(trim($requestArguments['FrontendUser']['email']));
				$frontendUser->setWww(trim($requestArguments['FrontendUser']['www']));
				
				if(!$hasErrors){					
					$this->frontendUserRepository->update($frontendUser);								
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();					
					$this->flashMessageContainer->add(
						LocalizationUtility::translate( $this->langLL.'messages.profilesaved', $this->extensionName ), 
						LocalizationUtility::translate( $this->langLL.'messages.profilesaved.header', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate( $this->langLL.'messages.profileerror', $this->extensionName ), 
						LocalizationUtility::translate( $this->langLL.'messages.profileerror.header', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);					
					foreach($errorMessages AS $errorMessage){
						$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					}
										
					$this->view->assign('allErrors', $allErrors);					
					
					if($dataError){
						$this->view->assign('dataError',1);
					}
					
					if($passwordError){
						$this->view->assign('passwordError',1);
					}
				}								
			}
						
			$this->view->assign('frontendUser', $frontendUser);
			$this->view->assign('self', 1);
			
			if($extConf['useCompanyAdmin']){
				$this->view->assign('useCompanyAdmin', 1);
			}						
			
        } else {		
			$this->redirect("error");
        }
						
	}
	
	/**
	 * action password recovery
	 *
	 * @param array $recovery	
	 * @return void
	 */
	public function passwordRecoveryAction($recovery = NULL) {				
		
		$hasErrors 		= false;			
		$errors 		= array();
		$errorMessages 	= array();
		
		$mailTemplate = $this->loadTemplate($this->settings['recoveryEmailTemplate']);
		
		if($mailTemplate['uid']>0){
		
			if($recovery){				
				if(trim($recovery['username'])=="" && trim($recovery['email'])==""){										
					$errorMessages[] 		= 	array( 
														"message" => "Bitte geben Sie Ihren Benutzernamen oder Ihre hinterlegte Email-Adresse ein"
												);
					$allErrors['username']	= true;
					$allErrors['email']		= true;				
					$hasErrors 				= true;
				} elseif(trim($recovery['email'])!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($recovery['email']))){				
					$errorMessages[] 		= 	array( 
														"message" => "Bitte geben Sie eine korrekte Email-Adresse ein"
												);
					$allErrors['email']		= true;
					$hasErrors 				= true;
				}
				
				if(!$hasErrors){	
					
					$notUnique = false;
					
					if(trim($recovery['username'])!="" && trim($recovery['email'])!=""){
						$foundUsers = $this->frontendUserRepository->findByUsernameAndEmail($this->storagePids,trim($recovery['username']),trim($recovery['email']));
						if($foundUsers->count()==1){
							$foundUser = $foundUsers->getFirst();
						} elseif($foundUsers->count()>1){
							$notUnique = true;
						}
					}
					
					if(trim($recovery['username'])!=""){
						$foundUsers = $this->frontendUserRepository->findByUsername($this->storagePids,trim($recovery['username']));
						if($foundUsers->count()==1){
							$foundUser = $foundUsers->getFirst();
						} elseif($foundUsers->count()>1){
							$notUnique = true;
						}
					}
					
					if(trim($recovery['email'])!="" && !$foundUser){
						$foundUsers = $this->frontendUserRepository->findByEmail($this->storagePids,trim($recovery['email']));
						if($foundUsers->count()==1){
							$foundUser = $foundUsers->getFirst();
						} elseif($foundUsers->count()>1){
							$notUnique = true;
						}
					}
					
					if($foundUser){
						
						if($foundUser->getEmail()!=""){
						
							$this->flashMessageContainer->add(
								"Sie erhalten in Kürze ein Email mit weiteren Informationen zum Neu-Setzen Ihres Passworts", 
								"Ihr Account konnte erfolgreich zugeordnet werden:", 
								\TYPO3\CMS\Core\Messaging\FlashMessage::OK
							);	
							
							$email = array();
							$email['sendername'] 		= $this->settings['recoverySendername'];
							$email['senderaddress'] 	= $this->settings['recoverySenderaddress'];
							$email['receivername'] 		= $foundUser->getName();
							$email['receiveraddress'] 	= $foundUser->getEmail();
							$email['uid'] 				= $foundUser->getUid();
							$email['email'] 			= $foundUser->getEmail();
							$email['username'] 			= $foundUser->getUsername();
							$email['gender'] 			= $foundUser->getGender();
							$email['title'] 			= $foundUser->getTitle();
							$email['name'] 				= $foundUser->getName();
							$email['firstName'] 		= $foundUser->getFirstName();
							$email['middleName'] 		= $foundUser->getMiddleName();
							$email['lastName'] 			= $foundUser->getLastName();
							$email['subject'] 			= $this->prepareSubject($mailTemplate['subject'],$email);
							$email['body'] 				= $this->getEmailBody($mailTemplate,$email);
							$email['pid'] 				= $GLOBALS["TSFE"]->id;							
							$email['hash']				= md5($email['uid'].time());							
							$email['url'] 				= $this->uriBuilder->setTargetPageUid($email['pid'])->setNoCache(TRUE)->setCreateAbsoluteUri(TRUE)->uriFor('newPassword', array("uid" => $email['uid'], "hash" => $email['hash']), 'FrontendUser', 'MooxFeusers', 'Pi1');
							$email['body'] 				= $this->getEmailBody($mailTemplate,$email);
							
							$this->renewRecoveryHash($foundUser,$email['hash']);
							
							$this->sendMail($email);
							
						} else {							
							$this->flashMessageContainer->add(
								"Bitte wenden Sie Sich an den Administrator", 
								"Für Ihre Account wurde keine Email-Adresse hinterlegt:", 
								\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
							);
						}

						$recovery = NULL;
					} elseif($notUnique) {
						$this->flashMessageContainer->add(
							"Bitte wenden Sie Sich an den Administrator", 
							"Ihr Account konnte nicht eindeutig zugeordnet werden:", 
							\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
						);
					} else {
						$this->flashMessageContainer->add(
							"Bitte überprüfen Sie Ihre Eingaben oder wenden Sie Sich an den Administrator", 
							"Ihr Account konnte nicht zugeordnet werden:", 
							\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
						);
					}
					
				} else {							
					foreach($errorMessages AS $errorMessage){
						$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					}
											
					$this->view->assign('allErrors', $allErrors);					
				}	
			}
			
			$this->view->assign('object', $recovery);
			$this->view->assign('hasMailTemplate', true);
			
		} else {
		
			$this->flashMessageContainer->add(
				"Bitte wenden Sie Sich an den Administrator", 
				"Es ist ein Fehler aufgetreten [no mail template selected]:", 
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);			
		}
	}
	
	/**
	 * action password recovery
	 *
	 * @param array $new	
	 * @param integer $uid
	 * @param string $hash
	 * @return void
	 */
	public function newPasswordAction($new = NULL, $uid = 0, $hash = '') {						
		
		$newPasswordAllowed = true;
		
		if(is_null($new)){
			$new 			= array();
			$new['uid'] 	= $uid;
			$new['hash'] 	= $hash;
		}
		
		$users = $this->frontendUserRepository->findByUidAndHash($this->storagePids,$new['uid'],$new['hash']);
		
		if(is_object($users) && $users->count()>1){			
			$newPasswordAllowed = false;
		} elseif(!is_object($users)){
			$newPasswordAllowed = false;
		} else {
			$user = $users->getFirst();
			if(is_object($user)){								
				if(isset($new['save'])){
					if(strlen(trim($new['password']))<$this->settings['passwordMinlength']){							
						$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.wronglength', $this->extensionName, array($this->settings['passwordMinlength']) )
												);
						$allErrors['password']	= true;
						$passwordError 			= true;
						$hasErrors 				= true;
					} 
					if(trim($new['password'])!=trim($new['passwordRepeat'])){
						
						$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.password.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.password.notmatching', $this->extensionName )
												);																	
						$allErrors['repeat']	= true;
						$passwordError 			= true;
						$hasErrors 				= true;
					}

					if($hasErrors){
						foreach($errorMessages AS $errorMessage){
							$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
						}										
						$this->view->assign('allErrors', $allErrors);
						$this->view->assign('passwordError', $passwordError);
					} else {
						$user->setPassword(trim($new['password']));
						$user->setPasswordRecoveryHash("");
						$user->setPasswordRecoveryTstamp(0);
						$this->frontendUserRepository->update($user);
						$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
						
						$this->flashMessageContainer->add(
							"Sie können Sich ab jetzt mit Ihrem neuen Passwort anmelden", 
							"Ihr Passwort wurde erfolgreich geändert:", 
							\TYPO3\CMS\Core\Messaging\FlashMessage::OK
						);
						unset($new['password']);
						unset($new['passwordRepeat']);
						$this->redirect("passwordRecovery");
					}
				}
			} else {
				$newPasswordAllowed = false;
			}
		}
		
		if(!$newPasswordAllowed){
			$this->flashMessageContainer->add(
				"Bitte fordern Sie unten eine neue URL an oder wenden Sie sich an Ihren Administrator", 
				"Diese Passwort-Zrücksetzen-Url ist fehlerhaft oder nicht mehr gültig:", 
				\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR
			);
			$this->redirect("passwordRecovery");
		}
		
		$this->view->assign('object', $new);
		$this->view->assign('newPasswordAllowed', $newPasswordAllowed);		
	}
	
	/**
	 * load template
	 *
	 * @param \int $uid
	 * @return array/object $template
	 */
	public function loadTemplate($uid = 0) {
		
		$template = array();
		
		if($uid>0){
		
			$object = $this->templateRepository->findByUid($uid);
			
			if($object->getTemplate()!=""){
				$template['uid'] 		= $object->getUid();
				$template['title'] 		= $object->getTitle();
				$template['subject'] 	= $object->getSubject();
				$template['category'] 	= $object->getCategory();
				$template['template'] 	= $object->getTemplate();				
			}
		}
		
		return $template;
	}
	
	/**
	 * get email template and render email body
	 *
	 * @param array $template template
	 * @param array $variables variables
	 * @return string $emailBody email body
	 */
	private function getEmailBody($template, $variables) {
		
		if (!empty($this->extConf['mailRenderingPartialRoot'])){
			$partialRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName($this->extConf['mailRenderingPartialRoot']);
			if(!is_dir($partialRootPath)){
				unset($partialRootPath);	
			} 
		} 
			
		if($partialRootPath==""){
			$conf = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
			$partialRootPath = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName(str_replace("Backend/","",$conf['view']['partialRootPath'])."Mail");
		}
		
		$emailView = $this->objectManager->create('TYPO3\\CMS\\Fluid\\View\StandaloneView');
        $emailView->setFormat('html');
        $emailView->setTemplateSource($template['template']);
		if($partialRootPath!=""){
			$emailView->setPartialRootPath($partialRootPath);
		}
        $emailView->assignMultiple($variables);
        $emailBody = $emailView->render();
		
        return $emailBody;
    }
	
	/**
	 * prepare mail subject
	 *
	 * @param string $subject subject
	 * @param array $variables $variables
	 * @return string $subject
	 */
	public function prepareSubject($subject,$variables = NULL) {
        
		$subject = str_replace("#KW#",date("W"),$subject);
		$subject = str_replace("#YEAR#",date("Y"),$subject);
		$subject = str_replace("#MONTH#",date("m"),$subject);
		$subject = str_replace("#DAY#",date("d"),$subject);
		$subject = str_replace("#TITLE#",$variables['title'],$subject);
		$subject = str_replace("#USERNAME#",$variables['username'],$subject);
		$subject = str_replace("#NAME#",$variables['name'],$subject);
		$subject = str_replace("#FIRSTNAME#",$variables['firstName'],$subject);
		$subject = str_replace("#MIDDLENAME#",$variables['middleName'],$subject);
		$subject = str_replace("#LASTNAME#",$variables['lastName'],$subject);
		$subject = str_replace("#EMAIL#",$variables['email'],$subject);
				
		return $subject;
	}
	
	/**
	 * send email
	 *
	 * @param array $mail mail
	 * @param array $variables variables
	 * @return string $emailBody email body
	 */
	private function sendMail($email) {
		
		if($this->extConf['useSMTP']){
			$TYPO3_CONF_VARS['MAIL']['transport'] 				= "smtp";
			if($this->extConf['smtpEncrypt']!="" && $this->extConf['smtpEncrypt']!="none"){
				$TYPO3_CONF_VARS['MAIL']['transport_smtp_server'] 	= $this->extConf['smtpEncrypt'];
			}
			$TYPO3_CONF_VARS['MAIL']['transport_smtp_encrypt']  = $this->extConf['smtpServer'];
			$TYPO3_CONF_VARS['MAIL']['transport_smtp_username'] = $this->extConf['smtpUsername'];
			$TYPO3_CONF_VARS['MAIL']['transport_smtp_password'] = $this->extConf['smtpPassword'];
		}
		
		if($email['sendername']==""){
			$email['sendername'] = $email['senderaddress'];
		}
		
		if($email['receivername']==""){
			$email['receivername'] = $email['receiveraddress'];
		}
		
		$mail = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Core\Mail\MailMessage');				
		$mail->setFrom(array($email['senderaddress'] => $email['sendername']));
		$mail->setTo(array($email['receiveraddress'] => $email['receivername']));						
		$mail->setSubject($email['subject']);
		$mail->setBody(strip_tags($email['body']));
		$mail->addPart($email['body'], 'text/html');
		$mail->send();
	}
	
	/**
	 * action error
	 *
	 * @return void
	 */
	public function errorAction() {				
		$this->redirect("profile");			
	}
	
	/**
	 * Returns storage pids
	 *
	 * @return array
	 */
	public function getStoragePids() {
		return $this->storagePids;
	}

	/**
	 * Set storage pids
	 *
	 * @param array $storagePids storage pids
	 * @return void
	 */
	public function setStoragePids($storagePids) {
		$this->storagePids = $storagePids;
	}
	
	/**
	 * Returns ext conf
	 *
	 * @return array
	 */
	public function getExtConf() {
		return $this->extConf;
	}

	/**
	 * Set ext conf
	 *
	 * @param array $extConf ext conf
	 * @return void
	 */
	public function setExtConf($extConf) {
		$this->extConf = $extConf;
	}
}
?>