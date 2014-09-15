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
	
	
	public $langLL = "LLL:EXT:moox_feusers/Resources/Private/Language/locallang.xlf:tx_mooxfeusers_frontenduser.";
	
	/**
     *
     * @return void
     */
    public function initializeAction() {		 
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		if(FALSE === $accessControllService->hasLoggedInFrontendUser()) {
			
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
            $frontendUser = $this->frontendUserRepository->findOneByUid($accessControllService->getFrontendUserUid());		  
        } 
		
		if($frontendUser && $accessControllService->isAccessAllowed($frontendUser)) {
            
			if($extConf['useCompanyAdmin'] && $frontendUser->getIsCompanyAdmin() && $frontendUser->getCompany()!=""){
			
				$this->view->assign('frontendUser', $frontendUser);            			
				$this->view->assign('companyUsers', $this->frontendUserRepository->findByCompany($frontendUser->getUid(),$frontendUser->getCompany()));
				
			} else {
				$this->redirect("profile");
			}
			
        } else {		
			$this->redirect("error");
        }				
	}
		
	/**
	 * action create
	 *
	 * @return void
	 */
	public function createAction() {				
		
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
		
		$adminUser = $this->frontendUserRepository->findOneByUid($accessControllService->getFrontendUserUid());
		
		$createUser = $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Model\\FrontendUser');	
		
		$groupSelect = array();
		
		if($this->settings['fe_group']!=""){
			$groups = explode(",",$this->settings['fe_group']);
			foreach($groups AS $group){
				$loadedGroup = $this->frontendUserGroupRepository->findOneByUid($group);
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
				} elseif($this->frontendUserRepository->findByUsername(trim($requestArguments['FrontendUser']['username']))->count()>0){
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.username.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.username.existing', $this->extensionName )
												);					
					$allErrors['username']	= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(trim($requestArguments['FrontendUser']['name'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.name.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.name.missing', $this->extensionName )
												);					
					$allErrors['name']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(trim($requestArguments['FrontendUser']['email'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.missing', $this->extensionName )
												);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($requestArguments['FrontendUser']['email']))){
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
					$createUser->setPassword(trim($requestArguments['FrontendUser']['password']));				
				}
								
				/*
				foreach($adminUser->getUsergroup() AS $group){
					$loadedGroup = $this->frontendUserGroupRepository->findOneByUid($group->getUid());					
					$createUser->addUsergroup($loadedGroup);					
				}
				*/
				
				if(trim($requestArguments['FrontendUser']['usergroup'])!=""){
					$groups = explode(",",trim($requestArguments['FrontendUser']['usergroup']));
					foreach($groups AS $group){
						if(isset($groupSelect[$group])){
							$loadedGroup = $this->frontendUserGroupRepository->findOneByUid($group);																
							$createUser->addUsergroup($loadedGroup);	
						}
					}
				}
												
				$createUser->setIsMooxFeuser(1);				
				$createUser->setCompany($adminUser->getCompany());
				$createUser->setUsername(trim($requestArguments['FrontendUser']['username']));
				$createUser->setTitle(trim($requestArguments['FrontendUser']['title']));
				$createUser->setName(trim($requestArguments['FrontendUser']['name']));
				$createUser->setFirstName(trim($requestArguments['FrontendUser']['first_name']));
				$createUser->setMiddleName(trim($requestArguments['FrontendUser']['middle_name']));
				$createUser->setLastName(trim($requestArguments['FrontendUser']['last_name']));
				$createUser->setAddress(trim($requestArguments['FrontendUser']['address']));
				$createUser->setZip(trim($requestArguments['FrontendUser']['zip']));
				$createUser->setCity(trim($requestArguments['FrontendUser']['city']));
				$createUser->setCountry(trim($requestArguments['FrontendUser']['country']));
				$createUser->setTelephone(trim($requestArguments['FrontendUser']['telephone']));
				$createUser->setFax(trim($requestArguments['FrontendUser']['fax']));
				$createUser->setEmail(trim($requestArguments['FrontendUser']['email']));
				$createUser->setWww(trim($requestArguments['FrontendUser']['www']));								
				
				if(!$hasErrors){					
					$this->frontendUserRepository->add($createUser);								
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
					$this->flashMessageContainer->add(
						'&nbsp;', 
						LocalizationUtility::translate( $this->langLL.'messages.useradded', $this->extensionName ), 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);				
					$this->redirect("list");					
				} else {
					$this->flashMessageContainer->add(
						LocalizationUtility::translate( $this->langLL.'messages.createerror', $this->extensionName ), 
						LocalizationUtility::translate( $this->langLL.'messages.createerror.header', $this->extensionName ), 
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
					
					$this->view->assign('frontendUser', $createUser);
					$this->view->assign('adminUser', $adminUser);
					$this->view->assign('self', 1);					
				}
	
			} else {			
				$this->view->assign('frontendUser', $createUser);
				$this->view->assign('adminUser', $adminUser);
				$this->view->assign('self', 1);				
			}
			
			$usergroups = $createUser->getUsergroup();
			foreach($usergroups AS $usergroup){
				$usergroup = $usergroup->getUid();
				break;
			}
			
			$this->view->assign('usergroup', ($requestArguments['FrontendUser']['usergroup'])?$requestArguments['FrontendUser']['usergroup']:$usergroup);			
			$this->view->assign('fegroups', $groupSelect);
			
			if($extConf['useCompanyAdmin']){
				$this->view->assign('useCompanyAdmin', 1);
			}
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
				$loadedGroup = $this->frontendUserGroupRepository->findOneByUid($group);
				$groupSelect[$group] = $loadedGroup->getTitle();				
			}
		}
		
		$accessControllService = $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\AccessControlService');
		
		$requestArguments = $this->request->getArguments();
		
		$adminUser = $this->frontendUserRepository->findOneByUid($accessControllService->getFrontendUserUid());
		
		if(!is_object($editUser)){						
			$editUser = $this->frontendUserRepository->findOneByUid($requestArguments['editUser']);			
		}
				
		if($accessControllService->isEditAllowed($adminUser,$editUser)){
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if($requestArguments['do'] == 'save'){
				
				if(trim($requestArguments['FrontendUser']['name'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.name.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.name.missing', $this->extensionName )
												);			
					$allErrors['name']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(trim($requestArguments['FrontendUser']['email'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.missing', $this->extensionName )
												);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($requestArguments['FrontendUser']['email']))){
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
						$loadedGroup = $this->frontendUserGroupRepository->findOneByUid($group->getUid());					
						$editUser->removeUsergroup($loadedGroup);					
					}
				}
				
				if(trim($requestArguments['FrontendUser']['usergroup'])!=""){
					$groups = explode(",",trim($requestArguments['FrontendUser']['usergroup']));
					foreach($groups AS $group){
						if(isset($groupSelect[$group])){
							$loadedGroup = $this->frontendUserGroupRepository->findOneByUid($group);																
							$editUser->addUsergroup($loadedGroup);	
						}
					}
				}
				
				if(!$extConf['useCompanyAdmin']){
					$editUser->setCompany(trim($requestArguments['FrontendUser']['company']));
				}
				$editUser->setTitle(trim($requestArguments['FrontendUser']['title']));
				$editUser->setName(trim($requestArguments['FrontendUser']['name']));
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
					$this->view->assign('enableCompanyField', 1);
				}
	
			} else {			
				$this->view->assign('frontendUser', $editUser);				
				$this->view->assign('self', 0);
				$this->view->assign('enableCompanyField', 1);
			}
			
			$usergroups = $editUser->getUsergroup();
			foreach($usergroups AS $usergroup){
				$usergroup = $usergroup->getUid();
				break;
			}
			
			$this->view->assign('usergroup', ($requestArguments['FrontendUser']['usergroup'])?$requestArguments['FrontendUser']['usergroup']:$usergroup);
			$this->view->assign('fegroups', $groupSelect);
			
			if($extConf['useCompanyAdmin']){
				$this->view->assign('useCompanyAdmin', 1);
			}
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
		
		$adminUser = $this->frontendUserRepository->findOneByUid($accessControllService->getFrontendUserUid());
		
		if(!is_object($deleteUser)){						
			$deleteUser = $this->frontendUserRepository->findOneByUid($requestArguments['deleteUser']);			
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
		$frontendUser = $this->frontendUserRepository->findOneByUid($accessControllService->getFrontendUserUid());
            
		if($frontendUser && $accessControllService->isAccessAllowed($frontendUser)) {
            
			$requestArguments = $this->request->getArguments();
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if($requestArguments['do'] == 'save'){
				
				if(trim($requestArguments['FrontendUser']['name'])==""){										
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.name.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.name.missing', $this->extensionName )
												);
					$allErrors['name']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				
				if(trim($requestArguments['FrontendUser']['email'])==""){					
					$errorMessages[] 		= 	array( 
														"title" => LocalizationUtility::translate( $this->langLL.'messages.email.header', $this->extensionName ),
														"message" => LocalizationUtility::translate( $this->langLL.'messages.email.missing', $this->extensionName )
												);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				} elseif(!\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($requestArguments['FrontendUser']['email']))){				
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
				$frontendUser->setTitle(trim($requestArguments['FrontendUser']['title']));
				$frontendUser->setName(trim($requestArguments['FrontendUser']['name']));
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
			$this->view->assign('enableCompanyField', 1);
			
			if($extConf['useCompanyAdmin']){
				$this->view->assign('useCompanyAdmin', 1);
			}						
			
        } else {		
			$this->redirect("error");
        }
						
	}
	
	/**
	 * action error
	 *
	 * @return void
	 */
	public function errorAction() {				
					
	}
}
?>