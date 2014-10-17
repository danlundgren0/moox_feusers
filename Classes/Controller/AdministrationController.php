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

/**
 *
 *
 * @package moox_feusers
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 *
 */
class AdministrationController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
		
	/**
	 * frontendUserRepository
	 *
	 * @var \TYPO3\MooxFeusers\Domain\Repository\FrontendUserRepository	
	 */
	protected $frontendUserRepository;

	/**
	 * frontendUserGroupRepository
	 *
	 * @var \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository	
	 */
	protected $frontendUserGroupRepository;				
	
	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $pageRepository;
	
	/**
	 * @var \TYPO3\CMS\Core\Resource\StorageRepository
	 */
	protected $storageRepository;
	
	/**
	 * fileRepository
	 *
	 * @var \TYPO3\MooxFeusers\Domain\Repository\FileRepository
	 */
	protected $fileRepository;
	
	/**
	 * fileReferenceRepository
	 *
	 * @var \TYPO3\MooxFeusers\Domain\Repository\FileReferenceRepository
	 */
	protected $fileReferenceRepository;
	
	/**
	 * backend session
	 *
	 * @var \TYPO3\MooxFeusers\Service\BackendSessionService
	 */
	protected $backendSession;
	
	/**
	 * page
	 *
	 * @var integer
	 */
	protected $page;
	
	/**
	 * extConf
	 *
	 * @var boolean
	 */
	protected $extConf;
	
	/**
	 * mailerActive
	 *
	 * @var boolean
	 */
	protected $mailerActive;
	
	/**
	 * allowedFields
	 *
	 * @var array
	 */
	protected $allowedFields;
	
	/**
	 * sort helper function
	 *
	 * @param \array $a
	 * @param \array $b
	 * @return void
	 */
	public function sortByFolderAndTitle($a, $b) {
		return strcmp($a["folder"].$a["title"], $b["folder"].$b["title"]);
	}
	
	/**
	 * initialize the controller
	 *	 
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();
		
		$this->setPage((int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id'));
		
		$this->initializeStorageSettings();
		
		$this->setMailerActive(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('moox_mailer'));
		
		$this->setAllowedFields(
									array(	"username",
											"name",
											"first_name",
											"middle_name",
											"last_name",
											"address",
											"telephone",
											"fax",
											"email",
											"title",
											"zip",
											"city",
											"country",
											"www",
											"company",
											"gender",
											"tstamp",
											"starttime",
											"endtime",
											"crdate",
											"lastlogin"
									)
								);
		
		$this->extConf 						= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);
		$this->frontendUserRepository 		= $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Repository\\FrontendUserRepository');
		$this->frontendUserGroupRepository 	= $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Repository\\FrontendUserGroupRepository');
		$this->storageRepository 			= $this->objectManager->get('TYPO3\\CMS\\Core\\Resource\\StorageRepository');		
		$this->fileRepository 				= $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Repository\\FileRepository');
		$this->fileReferenceRepository 		= $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Repository\\FileReferenceRepository');
		$this->pageRepository 				= $this->objectManager->get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
		$this->backendSession 				= $this->objectManager->get('TYPO3\\MooxFeusers\\Service\\BackendSessionService');
	}
	
	/**
	 * initialize storage settings
	 *	 
	 * @return void
	 */
	protected function initializeStorageSettings() {
				
		//fallback to current pid if no storagePid is defined
		if (version_compare(TYPO3_version, '6.0.0', '>=')) {
			$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		} else {
			$configuration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		}
		if (empty($configuration['persistence']['storagePid'])) {
			$currentPid['persistence']['storagePid'] = $this->page;
			$this->configurationManager->setConfiguration(array_merge($configuration, $currentPid));
		}		
	}
	
	/**
	 * action index
	 *
	 * @param \array $filter
	 * @return void
	 */
	public function indexAction($filter = array()) {			
		
		$folders 	= $this->getFolders();
		
		$this->redirectToFolder($folders);
		
		$filter = $this->processFilter($filter);
		
		if($this->settings['listViewFieldSeparator']!=""){
			$listViewFieldSeparator = $this->settings['listViewFieldSeparator'];
		} else {
			$listViewFieldSeparator = "&nbsp;|&nbsp;";
		}
		
		if($this->request->hasArgument('@widget_0')){
			$pagination = $this->request->getArgument('@widget_0');			
		}
		
		$rootline = $this->pageRepository->getRootLine($this->page);
		
		foreach($rootline AS $rootlinepage){
			if($rootlinepage['is_siteroot']){
				$rootpage = $rootlinepage;
				break;
			}
		}
		
		if(!$rootpage){
			$rootpage = $rootline[0];
		}
		
		$rootfound = false;
		for($i=0;$i<count($rootline);$i++){
			if($rootfound){
				unset($rootline[$i]);
			} else {
				if($rootline[$i]['is_siteroot']){
					$rootfound = true;
				}
			}
		}
		
		$rootline = array_reverse($rootline);
		
		if(isset($rootline[count($rootline)-2])){			
			$pageInfo = $this->pageRepository->getPage((int)$rootline[count($rootline)-2]['uid']);		
			if($pageInfo['module']=='mxfeuser'){
				$folder = $pageInfo['uid'];				
			}
			
		}		
		
		$feUsers 	= $this->frontendUserRepository->findAll($this->page,$filter);
		
		$this->view->assign('feUsers', $feUsers);
		$this->view->assign('fields', $this->getListViewFields());
		$this->view->assign('fieldsSeparator', $listViewFieldSeparator);		
		$this->view->assign('filter', $filter);		
		$this->view->assign('action', 'show');
		$this->view->assign('page', $this->page);
		$this->view->assign('folder', $folder);
		$this->view->assign('rootpage', $rootpage);
		$this->view->assign('rootline', $rootline);
		$this->view->assign('folders', (count($folders)>0)?$folders:false);
		$this->view->assign('pagination', $pagination);
		$this->view->assign('replacements', $this->getQueryReplacements($filter['query']));
		$this->view->assign('mailerActive', $this->mailerActive);
		$this->view->assign('conf', $this->extConf);
	}
	
	/**
	 * action import
	 *
	 * @param \array $import
	 * @return void
	 */
	public function importAction($import = array()) {			
		
		$allowedFields = 	array( 
									"gender",
									"title",
									"forename",
									"surname",
									"company",
									"department",
									"street",
									"zip",
									"city",
									"region",
									"country",
									"phone",
									"mobile",
									"fax",
									"email",
									"www",
									"unused"								
							);
		
		if(isset($import['process'])){
			
			$csvFields = explode($import['separator'],$import['format']);
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if(trim($import['file']['name'])==""){					
				$errorMessages[] 		= 	array( 
													"title" => "Datei",
													"message" => "Bitte wählen Sie eine Import-Datei aus"
										);					
				$allErrors['file']		= true;
				$dataError 				= true;
				$hasErrors 				= true;
			} else {
				$fileInfo = $this->getFileInfo($import['file']['name']);
				if(!in_array($fileInfo['extension'],array("csv"))){					
					$errorMessages[] 		= 	array( 
														"title" => "Datei",
														"message" => 'Es sind nur Dateien vom Typ "csv" erlaubt'									
												);					
					$allErrors['file']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
			}
			if(trim($import['format'])==""){					
				$errorMessages[] 		= 	array( 
													"title" => "Format",
													"message" => "Bitte geben Sie ein Import-Format vor"
										);					
				$allErrors['format']		= true;
				$dataError 				= true;
				$hasErrors 				= true;
			} elseif(trim($import['separator'])!=""){				
				if(!in_array("forename",$csvFields)){					
					$errorMessages[] 		= 	array( 
														"title" => "Format",
														"message" => 'Ihr Format muss mindestens das Feld "forename" beinhalten'
											);					
					$allErrors['format']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
			}
			if(trim($import['separator'])==""){					
				$errorMessages[] 		= 	array( 
													"title" => "Trennzeichen",
													"message" => "Bitte geben Sie ein Trennzeichen vor"
										);					
				$allErrors['separator']		= true;
				$dataError 				= true;
				$hasErrors 				= true;
			}
				
			if(!$hasErrors){
								
				$row 			= 1;
				$handle 		= fopen($import['file']['tmp_name'], "r");
				
				$errors 		= 0;
				$added 			= 0;
				$skipped 		= 0;
				$exisiting 		= 0;
				
				$csvFieldsTmp 	= $csvFields;
				$csvFields 		= array();
				
				$cnt 			= 0;
				foreach($csvFieldsTmp AS $csvField){
					if(in_array(strtolower($csvField),$allowedFields)){
						$csvFields[$cnt] = strtolower($csvField);
					} else {
						$csvFields[$cnt] = "unused";
					}
					$cnt++;
				}
								
				while (($data = fgetcsv($handle, 5000, $import['separator'])) !== FALSE) {
					
					$skip = false;
					
					if($import['skipFirst'] && $row==1){
						$skip = true;
					} elseif(!$import['skipFirst'] && $row==1 && in_array("forename",$data)){
						$skip = true;
					}
					
					if(!$skip){
						
						$object = $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Model\\FrontendUser');
						
						$row++;
						
						$num = count($data);
					    
						for ($c=0; $c < $num; $c++) {
							if($csvFields[$c] == "gender"){
								if(in_array(strtolower(trim($data[$c])),array("herr","männlich","m","mr","male","1"))){
									$data[$c] = 1;
								} elseif(in_array(strtolower(trim($data[$c])),array("frau","weiblich","w","mrs","female","f","2"))){
									$data[$c] = 2;
								} else {
									$data[$c] = 0;
								}
								$object->setGender($data[$c]);			
							} elseif($csvFields[$c] == "title"){
								$object->setTitle(trim($data[$c]));			
							} elseif($csvFields[$c] == "forename"){								
								$object->setForename(trim($data[$c]));			
							} elseif($csvFields[$c] == "surname"){
							    $object->setSurname(trim($data[$c]));
							} elseif($csvFields[$c] == "company"){
								$object->setCompany(trim($data[$c]));
							} elseif($csvFields[$c] == "department"){
								$object->setDepartment(trim($data[$c]));
							} elseif($csvFields[$c] == "street"){
								$object->setStreet(trim($data[$c]));
							} elseif($csvFields[$c] == "zip"){
								$object->setZip(trim($data[$c]));
							} elseif($csvFields[$c] == "city"){
								$object->setCity(trim($data[$c]));
							} elseif($csvFields[$c] == "region"){
								$object->setRegion(trim($data[$c]));
							} elseif($csvFields[$c] == "country"){
								$object->setCountry(trim($data[$c]));
							} elseif($csvFields[$c] == "phone"){
								$object->setPhone(trim($data[$c]));
							} elseif($csvFields[$c] == "mobile"){
								$object->setMobile(trim($data[$c]));
							} elseif($csvFields[$c] == "fax"){
								$object->setFax(trim($data[$c]));
							} elseif($csvFields[$c] == "email"){
								$object->setEmail(trim($data[$c]));
							} elseif($csvFields[$c] == "www"){
								$object->setWww(trim($data[$c]));
							}
						}
						
						$isValid = true;
						
						if($object->getGender()==""){
							$object->setGender(0);
						}						
						if($object->getForename()==""){
							$isValid = false;
						}
						if($object->getEmail()!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($object->getEmail())){
							$isValid = false;
						}
						if($import['mailingAllowed'] && $object->getEmail()!=""){
							$object->setMailingAllowed(1);
							$object->setRegistered(time());
						}
						if($isValid){
							$duplicate = $this->frontendUserRepository->findDuplicate($object);
							if(!is_object($duplicate)){
								if($import['mode']>0){
									$foundFeUser = $this->frontendUserRepository->findByEmail($object->getEmail());									
									if(!is_object($foundFeUser)){
										$this->frontendUserRepository->add($object);										
										$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
										$added++;
									} elseif(is_object($foundFeUser) && in_array($import['mode'],array(1,2))){
										if($import['mode']==2 && $foundFeUser->getUnregistered()==0){
											$foundFeUser->setMailingAllowed($object->getMailingAllowed());
											$foundFeUser->setRegistered($object->getRegistered());
										}
										if($import['mode']==2 || $foundFeUser->getGender()==0){
											$foundFeUser->setGender($object->getGender());
										}
										if($import['mode']==2 || $foundFeUser->getTitle()==""){
											$foundFeUser->setTitle($object->getTitle());
										}
										if($import['mode']==2 || $foundFeUser->getForename()==""){
											$foundFeUser->setForename($object->getForename());
										}
										if($import['mode']==2 || $foundFeUser->getSurname()==""){
											$foundFeUser->setSurname($object->getSurname());
										}
										if($import['mode']==2 || $foundFeUser->getCompany()==""){
											$foundFeUser->setCompany($object->getCompany());
										}
										if($import['mode']==2 || $foundFeUser->getDepartment()==""){
											$foundFeUser->setDepartment($object->getDepartment());
										}
										if($import['mode']==2 || $foundFeUser->getStreet()==""){
											$foundFeUser->setStreet($object->getStreet());
										}
										if($import['mode']==2 || $foundFeUser->getZip()==""){
											$foundFeUser->setZip($object->getZip());
										}
										if($import['mode']==2 || $foundFeUser->getCity()==""){
											$foundFeUser->setCity($object->getCity());
										}
										if($import['mode']==2 || $foundFeUser->getRegion()==""){
											$foundFeUser->setRegion($object->getRegion());
										}
										if($import['mode']==2 || $foundFeUser->getCountry()==""){
											$foundFeUser->setCountry($object->getCountry());
										}
										if($import['mode']==2 || $foundFeUser->getPhone()==""){
											$foundFeUser->setPhone($object->getPhone());
										}
										if($import['mode']==2 || $foundFeUser->getMobile()==""){
											$foundFeUser->setMobile($object->getMobile());
										}
										if($import['mode']==2 || $foundFeUser->getFax()==""){
											$foundFeUser->setFax($object->getFax());
										}
										if($import['mode']==2 || $foundFeUser->getWww()==""){
											$foundFeUser->setWww($object->getWww());
										}
										$this->frontendUserRepository->update($foundFeUser);
										$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
										$updated++;
									} else {
										$skipped++;
									}
								} else {
									$this->frontendUserRepository->add($object);
									$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
									$added++;
								}																									
							} else {
								$exisiting++;
							}
						} else {
							$errors++;
						}

						$object = NULL;
					
					} else {
						$row++;
					}
				}
				$successMessage = "";
				$infoMessage 	= "";
				$errorMessage 	= "";
				if($added>0){
					$successMessage .= $added.' Adresse(n) wurde importiert.';
				}
				if($updated>0){
					$successMessage .= ' '.$updated.' Adresse(n) wurden '.(($import['mode']==2)?"überschrieben":"upgedated").'.';
				}
				if($skipped>0){
					$infoMessage .= ' '.$skipped.' Adresse(n) wurde übersprungen.';
				}
				if($exisiting>0){
					$infoMessage .= ' '.$exisiting.' Adresse(n) waren bereits vorhanden.';
				}
				if($errors>0){
					$errorMessage .= ' '.$errors.' Adresse(n) konnten wegen fehlerhafter Daten nicht importiert werden.';
				}
				
				if($successMessage!=""){
					$this->flashMessageContainer->add(
						'', 
						$successMessage, 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
				}
				if($infoMessage!=""){
					$this->flashMessageContainer->add(
						'', 
						$infoMessage, 
						\TYPO3\CMS\Core\Messaging\FlashMessage::INFO);
				}
				if($errorMessage!=""){
					$this->flashMessageContainer->add(
						'', 
						$errorMessage, 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
				
				if($added>0 || $updated>0){
					$this->redirect("index");
				}
				
			} else {					
					
				foreach($errorMessages AS $errorMessage){
					$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
					
				$this->view->assign('allErrors', $allErrors);					
					
				if($dataError){
					$this->view->assign('dataError',1);
				}											
			}
			
		} else {
			
			if(!count($import)){
				$import['format'] 			= ($this->settings['importFormat'])?$this->settings['importFormat']:"forename;surname;email";				
				$import['separator'] 		= ($this->settings['importSeparator'])?$this->settings['importSeparator']:";";
				$import['mode'] 			= (in_array($this->settings['importMode'],array(0,1,2,3)))?$this->settings['importMode']:"2";
				$import['skipFirst'] 		= ($this->settings['importSkipFirst'])?$this->settings['importSkipFirst']:0;
				$import['mailingAllowed'] 	= ($this->settings['importMailingAllowed'])?$this->settings['importMailingAllowed']:0;
			}
		}
		
		$rootline = $this->pageRepository->getRootLine($this->page);
		
		foreach($rootline AS $rootlinepage){
			if($rootlinepage['is_siteroot']){
				$rootpage = $rootlinepage;
				break;
			}
		}
		
		if(!$rootpage){
			$rootpage = $rootline[0];
		}
		
		$rootline = array_reverse($rootline);
		
		if(isset($rootline[count($rootline)-2])){			
			$pageInfo = $this->pageRepository->getPage((int)$rootline[count($rootline)-2]['uid']);		
			if($pageInfo['module']=='mxfeuser'){
				$folder = $pageInfo['uid'];				
			}
			
		}
		
		$folders 	= $this->getFolders();
				
		$this->view->assign('action', 'import');
		$this->view->assign('page', $this->page);
		$this->view->assign('folder', $folder);
		$this->view->assign('rootpage', $rootpage);
		$this->view->assign('rootline', $rootline);
		$this->view->assign('folders', (count($folders)>0)?$folders:false);
		$this->view->assign('object', $import);
		$this->view->assign('mailerActive', $this->mailerActive);
		
	}
	
	/**
	 * action add
	 *	
	 * @param \array $add	
	 * @return void
	 */
	public function addAction($add = array()) {			
		
		if(!is_null($this->backendSession->get("id")) && $this->backendSession->get("id")!=""){
			$this->setPage($this->backendSession->get("id"));
		}
		
		$new_images_tmp = array();
		$fal_images_tmp = array();
		
		if(count($add)){
			foreach($add AS $key => $value){
				if(strpos($key,"newImage_")!==FALSE){					
					$newImageIndex 	= str_replace("newImage_","",$key);
					if(!isset($add["removeNewImage_".$newImageIndex ])){
						$newImageObject 							= unserialize((string)$value);	
						$newImageInfo 								= $this->getFileInfo($newImageObject['name']);			
						$newImagesCnt 								= count($new_images_tmp);
						$new_images_tmp[$newImagesCnt]['name'] 		= $newImageInfo['name'].".".$newImageInfo['extension'];
						$new_images_tmp[$newImagesCnt]['src'] 		= "/typo3temp/moox_feusers/".md5($newImageInfo['name']).".".$newImageInfo['extension'];
						$new_images_tmp[$newImagesCnt]['object'] 	= $value;
					}
				}
			}
			/*
			foreach($add AS $key => $value){
				if(strpos($key,"image_")!==FALSE){					
					$imageIndex 						= str_replace("image_","",$key);
					if(!isset($add["removeImage_".$imageIndex ])){
						$imageObject 						= unserialize((string)$value);	
						$imageInfo 							= $this->getFileInfo($imageObject['name']);			
						$imagesCnt 							= count($images_tmp);
						$images_tmp[$imagesCnt]['name'] 	= $imageInfo['name'].".".$imageInfo['extension'];
						$images_tmp[$imagesCnt]['src'] 		= "/typo3temp/moox_feusers/".md5($imageInfo['name']).".".$imageInfo['extension'];
						$images_tmp[$imagesCnt]['object'] 	= $value;
					}
				}
			}
			*/
			if($add['usergroups']!=""){
				$usergroupUids = explode(",",$add['usergroups']);
				$add['usergroups'] 		= array();
				$add['usergroupsUids'] 	= array();
				foreach($usergroupUids AS $usergroupUid){
					$usergroup = $this->frontendUserGroupRepository->findByUid($usergroupUid);
					$add['usergroups'][] 		= array("uid" => $usergroup->getUid(), "title" => $usergroup->getTitle());
					$add['usergroupsUids'][] 	= $usergroup->getUid();
				}
			} else {
				$add['usergroups'] 		= array();
				$add['usergroupsUids'] 	= array();
			}
		} else {
			$add['usergroups'] 		= array();
			$add['usergroupsUids'] 	= array();
			$add['disallowMailing'] = ($this->settings['addDisallowMailing'])?1:0;		
		}
		
		if($add['newImage']['name']!=""){
			
			$newImageInfo = $this->getFileInfo($add['newImage']['name']);
				
			if(!in_array($newImageInfo['extension'],array("png","jpg","gif"))){														
				$allErrors['newImage']		= true;
				if($add['addImage']){
					$this->flashMessageContainer->add(
						'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt', 
						'Bild', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
			} else {
				$newImagesCnt = count($new_images_tmp);
				$new_images_tmp[$newImagesCnt]['name'] 		= $newImageInfo['name'].".".$newImageInfo['extension'];
				$add['newImage']['tmp_name'] 				= $this->copyToTypo3Temp($add['newImage']['tmp_name'],$newImageInfo['name'].".".$newImageInfo['extension']);
				$new_images_tmp[$newImagesCnt]['src'] 		= "/typo3temp/moox_feusers/".md5($newImageInfo['name']).".".$newImageInfo['extension'];
				$new_images_tmp[$newImagesCnt]['object'] 	= serialize($add['newImage']);				
			}
		}		
		/*
		if($add['falImage']['name']!=""){
			
			$falImageInfo = $this->getFileInfo($add['falImage']['name']);
				
			if(!in_array($falImageInfo['extension'],array("png","jpg","gif"))){														
				$allErrors['falImage']		= true;
				if($add['addImage']){
					$this->flashMessageContainer->add(
						'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt', 
						'Bild', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
			} else {
				$falImagesCnt = count($fal_images_tmp);
				$fal_images_tmp[$falImagesCnt]['name'] 		= $falImageInfo['name'].".".$falImageInfo['extension'];
				$add['falImage']['tmp_name'] 				= $this->copyToTypo3Temp($add['falImage']['tmp_name'],$falImageInfo['name'].".".$falImageInfo['extension']);
				$fal_images_tmp[$falImagesCnt]['src'] 		= "/typo3temp/moox_feusers/".md5($falImageInfo['name']).".".$falImageInfo['extension'];
				$fal_images_tmp[$falImagesCnt]['object'] 	= serialize($add['falImage']);				
			}
		}
		*/
				
		if(count($new_images_tmp)>0){
			$add['images'] = $new_images_tmp;
		}
				
		if(isset($add['save']) || isset($add['saveAndClose']) ||  isset($add['saveAndNew'])){
			
			if($this->settings['addUsernameMinlength']<3){
				$this->settings['addUsernameMinlength'] = 3;
			}
			
			$hasErrors 		= false;			
			$errors 		= array();
			$errorMessages 	= array();
			
			if(trim($add['username'])==""){					
				$errorMessages[] 		= 	array( 
													"title" => "Benutzername",
													"message" => "Bitte geben Sie einen Benutzernamen ein."
											);					
				$allErrors['username']	= true;
				$dataError 				= true;
				$hasErrors 				= true;
			} elseif(strlen($add['username'])<$this->settings['addUsernameMinlength']){					
				$errorMessages[] 		= 	array( 
													"title" => "Benutzername",
													"message" => "Bitte geben Sie einen Benutzernamen mit mindestens ".$this->settings['addUsernameMinlength']." Zeichen ein"
											);					
				$allErrors['username']	= true;
				$dataError 				= true;
				$hasErrors 				= true;
			} elseif($this->frontendUserRepository->findByUsername(trim($add['username']))->count()>0){
				$errorMessages[] 		= 	array( 
														"title" => "Benutzername",
														"message" => "Dieser Benutzername existiert bereits"
												);					
				$allErrors['username']	= true;
				$dataError 				= true;
				$hasErrors 				= true;
			}
			
			if($add['password']!="" && strlen($add['password'])<$this->settings['passwordMinlength']){
				$errorMessages[] 		= 	array( 
													"title" => "Passwort",
													"message" => "Geben Sie ein Passwort mit mindestens ".$this->settings['passwordMinlength']." Zeichen ein"
										);					
				$allErrors['password']	= true;
				$dataError 				= true;
				$hasErrors 				= true;
			}
			
			if(trim($add['email'])!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($add['email']))){					
				$errorMessages[] 		= 	array( 
													"title" => "Email",
													"message" => "Bitte geben Sie eine korrekte Email-Adresse ein."
										);					
				$allErrors['email']		= true;
				$dataError 				= true;
				$hasErrors 				= true;
			}
			
			if($add['isCompanyAdmin'] && $add['company']==""){
					$errorMessages[] 				= 	array( 
																"title" => "Firmen-Administrator",
																"message" => "Sie müssen eine Firma angeben um den Benutzer als Administrator zu registrieren"
													);					
					$allErrors['isCompanyAdmin']	= true;
					$allErrors['company']			= true;
					$dataError 						= true;
					$hasErrors 						= true;
				}
			
			if($allErrors['newImage']){
				$errorMessages[] 		= 	array( 
												"title" => "Bild",
												"message" => 'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt'									
											);									
				$dataError 				= true;
				$hasErrors 				= true;
			}
			
			if($allErrors['falImage']){
				$errorMessages[] 		= 	array( 
												"title" => "Bild",
												"message" => 'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt'									
											);									
				$dataError 				= true;
				$hasErrors 				= true;
			}
							
			if(!$hasErrors){
			
				$object = $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Model\\FrontendUser');
				
				foreach($add['usergroupsUids'] AS $usergroupUid){
					$usergroup = $this->frontendUserGroupRepository->findOneByUid($usergroupUid);																
					$object->addUsergroup($usergroup);							
				}
				//$object->setUsergroup($add['usergroup']);
				//$object->setTxExtbaseType("Tx_Extbase_Domain_Model_FrontendUser");				
				$object->setUsername($add['username']);
				if($add['password']==""){
					$add['password'] = md5($add['username']);
				}
				$object->setPassword($add['password']);
				$object->setStarttime($add['starttime']);
				$object->setEndtime($add['endtime']);					
				$object->setFirstName($add['firstName']);
				$object->setMiddleName($add['middleName']);
				$object->setLastName($add['lastName']);
				$name = "";
				if($add['firstName']!=""){
					$name = $add['firstName'];
				}
				if($add['middleName']!="" && $name==""){
					$name = $add['middleName'];
				} else {
					$name .= " ".$add['middleName'];
				}
				if($add['lastName']!="" && $name==""){
					$name = $add['lastName'];
				} else {
					$name .= " ".$add['lastName'];
				}
				$object->setName($name);
				$object->setAddress($add['address']);
				$object->setTelephone($add['telephone']);
				$object->setFax($add['fax']);
				$object->setEmail($add['email']);
				$object->setTitle($add['title']);
				$object->setZip($add['zip']);
				$object->setCity($add['city']);
				$object->setCountry($add['country']);
				$object->setWww($add['www']);
				$object->setCompany($add['company']);
				//$object->setImage($add['image']);
				//$object->setFalImage($add['falImage']);
				$object->setDisallowMailing($add['disallowMailing']);
				$object->setIsCompanyAdmin($add['isCompanyAdmin']);
				
				if(count($new_images_tmp)){					
					$images = array();
					foreach($new_images_tmp AS $image){
						$image = unserialize($image['object']);
						$newImageInfo = $this->getFileInfo($image['name']);
						$newImagePath = $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$image['name'];						
						if(file_exists($newImagePath)){				
							$copyCount = 1;					
							while($copyCount<=1000){
								$newFileName = $newImageInfo['name']."_".str_pad($copyCount,2,"0",STR_PAD_LEFT).".".$newImageInfo['extension'];
								$newImagePath = $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$newFileName;
								if(!file_exists($newImagePath)){
									$image['name'] = $newFileName;
									break;
								}						
								$copyCount++;
							}
							
						}
						if(file_exists($image['tmp_name'])){
							$images[] = $image['name'];
							copy($image['tmp_name'],$newImagePath);	
							if(file_exists($image['tmp_name'])){
								unlink($image['tmp_name']);
							}
						}
						$object->setImage(implode(",",$images));						
					}
				}
				
				if(false && count($images_tmp)){					
					/** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
					$storage = $this->storageRepository->findByUid('1');
					
					foreach($images_tmp AS $image){
						$image = unserialize($image['object']);
						// this will already handle the moving of the file to the storage:
						$newFileObject = $storage->addFile(
							$image['tmp_name'], $storage->getRootLevelFolder(), $image['name']
						);
						$newFileObject = $storage->getFile($newFileObject->getIdentifier());
						$newFile = $this->fileRepository->findByUid($newFileObject->getProperty('uid'));
						
						/** @var \TYPO3\MooxFeusers\Domain\Model\FileReference $newFileReference */
						$newFileReference = $this->objectManager->get('TYPO3\MooxFeusers\Domain\Model\FileReference');
						$newFileReference->setFile($newFile);
						$newFileReference->setCruserId($GLOBALS['BE_USER']->user['uid']);						
						
						$object->addImage($newFileReference);
					}
				}
				
				$this->frontendUserRepository->add($object);								
				$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
				
				$this->flashMessageContainer->add(
					'', 
					'Adresse wurde erfolgreich gespeichert.', 
					\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			
				if(isset($add['save'])){
					$this->redirect("edit",NULL,NULL,array('uid' => $object->getUid()));
				} elseif(isset($add['saveAndClose'])){
					$this->redirect("index");
				} elseif(isset($add['saveAndNew'])){			
					$this->redirect("add");
				} else {			
					$this->view->assign('object', $add);			
					$this->view->assign('action', 'add');
					$this->view->assign('page', $this->page);
					$this->view->assign('mailerActive', $this->mailerActive);
					$this->view->assign('usergroupSelectionTree', $this->generateUsergroupSelectionTree("usergroups",NULL,$add['usergroupsUids']));
					$this->view->assign('conf', $this->extConf);
				}
				
			} else {					
					
				foreach($errorMessages AS $errorMessage){
					$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
				}
					
				$this->view->assign('allErrors', $allErrors);					
					
				if($dataError){
					$this->view->assign('dataError',1);
				}
				
				$this->view->assign('object', $add);			
				$this->view->assign('action', 'add');
				$this->view->assign('page', $this->page);
				$this->view->assign('mailerActive', $this->mailerActive);
				$this->view->assign('usergroupSelectionTree', $this->generateUsergroupSelectionTree("usergroups",NULL,$add['usergroupsUids']));
				$this->view->assign('conf', $this->extConf);				
			}
			
		} else {
		
			$this->view->assign('object', $add);			
			$this->view->assign('action', 'add');
			$this->view->assign('page', $this->page);
			$this->view->assign('mailerActive', $this->mailerActive);
			$this->view->assign('usergroupSelectionTree', $this->generateUsergroupSelectionTree("usergroups",NULL,$add['usergroupsUids']));
			$this->view->assign('conf', $this->extConf);
			if($add['addImage']){
				$this->view->assign('allErrors', $allErrors);
			}
		}
	}
	
	/**
	 * action edit
	 *
	 * @param \int $uid
	 * @param \array $edit
	 * @param \array $pagination
	 * @return void
	 */
	public function editAction($uid = 0, $edit = array(), $pagination = array()) {			
		
		if(!is_null($this->backendSession->get("id")) && $this->backendSession->get("id")!=""){
			$this->setPage($this->backendSession->get("id"));
		}
		
		if($uid>0){						
			
			$object = $this->frontendUserRepository->findByUid($uid,FALSE);
			
			if(count($edit)){
				foreach($edit AS $key => $value){
					if(strpos($key,"removeImage_")!==FALSE){					
						$imageToRemove 		= str_replace("removeImage_","",$key);
						$imageToRemove 		= str_replace("~",".",$imageToRemove);						
						$imageToRemovePath 	= $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$imageToRemove;
						if(file_exists($imageToRemovePath)){
							unlink($imageToRemovePath);
						}
						$images = array();						
						$imagesBefore = explode(",",$object->getImage());
						foreach($imagesBefore AS $image){
							if($image!=$imageToRemove){
								$images[] = $image;
							}
						}
						$object->setImage(implode(",",$images));
						$edit['image'] = implode(",",$images);
						$this->frontendUserRepository->update($object);
						$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
						
						$this->flashMessageContainer->add(
									'', 
									'Bild "'.$imageToRemove.'" wurde erfolgreich entfernt.', 
									\TYPO3\CMS\Core\Messaging\FlashMessage::OK);														
					}
					/*
					if(strpos($key,"removeFalImage_")!==FALSE){					
						$referenceUid 	= str_replace("removeFalImage_","",$key);						
						foreach($object->getFalImages() AS $reference){
							$falImageName = $reference->getOriginalResource()->getName();
							if($reference->getUid()==$referenceUid){
								$object = $this->frontendUserRepository->findByUid($uid,FALSE);
								$object->removeFalImage($reference);
								$this->frontendUserRepository->update($object);
								$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
								$this->flashMessageContainer->add(
									'', 
									'Bild "'.$falImageName.'" wurde erfolgreich entfernt.', 
									\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
							}
						}						
					}
					*/
				}
			}												
			
			if(isset($edit['addImage']) && $edit['newImage']['name']!=""){
				$imageInfo = $this->getFileInfo($edit['newImage']['name']);
				if(!in_array($imageInfo['extension'],array("png","jpg","gif"))){														
					$this->flashMessageContainer->add(
						'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt', 
						'Bild', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);								
				} else {
					$newImagePath = $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$edit['newImage']['name'];
					if(file_exists($newImagePath)){				
						$copyCount = 1;					
						while($copyCount<=1000){
							$newFileName = $imageInfo['name']."_".str_pad($copyCount,2,"0",STR_PAD_LEFT).".".$imageInfo['extension'];
							$newImagePath = $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$newFileName;
							if(!file_exists($newImagePath)){
								$edit['newImage']['name'] = $newFileName;
								break;
							}						
							$copyCount++;
						}
						
					}
					move_uploaded_file($edit['newImage']['tmp_name'],$newImagePath);
					$images = explode(",",$object->getImage());
					$images[] = $edit['newImage']['name'];
					$object->setImage(implode(",",$images));
					$edit['image'] = implode(",",$images);
					$this->frontendUserRepository->update($object);
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
					$this->flashMessageContainer->add(
						'', 
						'Bild "'.$edit['newImage']['name'].'" wurde erfolgreich hinzugefügt.', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
				}
			}
			
			if(false && $edit['addFalImage'] && $edit['falImage']['name']!=""){
			
				$falImageInfo = $this->getFileInfo($edit['falImage']['name']);
							
				if(!in_array($falImageInfo['extension'],array("png","jpg","gif"))){														
					$this->flashMessageContainer->add(
						'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt', 
						'Bild', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);								
				} else {
					/** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
					$storage = $this->storageRepository->findByUid('1');
					$newFileObject = $storage->addFile(
						$edit['falImage']['tmp_name'], $storage->getRootLevelFolder(), $edit['falImage']['name']
					);
					$newFileObject = $storage->getFile($newFileObject->getIdentifier());
					$newFile = $this->fileRepository->findByUid($newFileObject->getProperty('uid'));
						
					/** @var \TYPO3\MooxFeusers\Domain\Model\FileReference $newFileReference */
					$newFileReference = $this->objectManager->get('TYPO3\MooxFeusers\Domain\Model\FileReference');
					$newFileReference->setFile($newFile);
					$newFileReference->setCruserId($GLOBALS['BE_USER']->user['uid']);																
					
					$object->addFalImage($newFileReference);
					
					$this->frontendUserRepository->update($object);
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
					$this->flashMessageContainer->add(
						'', 
						'Bild "'.$edit['falImage']['name'].'" wurde erfolgreich hinzugefügt.', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
				}
			}
					
			$object = $this->frontendUserRepository->findByUid($uid,FALSE);
			
			if(!count($edit)){
				$edit['uid'] 				= $object->getUid();
				$edit['username'] 			= $object->getUsername();
				//$edit['usergroup']		= $object->getUsergroup();
				$edit['usergroups'] 		= array();
				$edit['usergroupsUids'] 	= array();
				if(count($object->getUsergroup())){
					foreach($object->getUsergroup() AS $usergroup){
						$edit['usergroups'][] 		= array("uid" => $usergroup->getUid(), "title" => $usergroup->getTitle());
						$edit['usergroupsUids'][] 	= $usergroup->getUid();
					}
				}
				$edit['starttime'] 			= $object->getStarttime();
				$edit['endtime'] 			= $object->getEndtime();
				$edit['firstName'] 			= $object->getFirstName();
				$edit['middleName']	 		= $object->getMiddleName();
				$edit['lastName'] 			= $object->getLastName();
				$edit['address'] 			= $object->getAddress();
				$edit['telephone'] 			= $object->getTelephone();
				$edit['fax'] 				= $object->getFax();
				$edit['email'] 				= $object->getEmail();
				$edit['title'] 				= $object->getTitle();
				$edit['zip'] 				= $object->getZip();
				$edit['city'] 				= $object->getCity();
				$edit['country'] 			= $object->getCountry();
				$edit['www'] 				= $object->getWww();
				$edit['company'] 			= $object->getCompany();
				$edit['image'] 				= $object->getImage();
				//$edit['falImage'] 		= $object->getFalImages();
				$edit['gender'] 			= $object->getGender();
				$edit['disallowMailing'] 	= $object->getDisallowMailing();
				$edit['isCompanyAdmin'] 	= $object->getIsCompanyAdmin();											
			} else {
				$edit['image'] 				= $object->getImage();
				if($edit['usergroups']!=""){
					$usergroupUids = explode(",",$edit['usergroups']);
					$edit['usergroups'] 		= array();
					$edit['usergroupsUids'] 	= array();
					foreach($usergroupUids AS $usergroupUid){
						$usergroup = $this->frontendUserGroupRepository->findByUid($usergroupUid);
						$edit['usergroups'][] 		= array("uid" => $usergroup->getUid(), "title" => $usergroup->getTitle());
						$edit['usergroupsUids'][] 	= $usergroup->getUid();
					}
				} else {
					$edit['usergroups'] 		= array();
					$edit['usergroupsUids'] 	= array();
				}
			}
						
			if(isset($edit['save']) || isset($edit['saveAndClose']) ||  isset($edit['saveAndNew'])){
				
				$hasErrors 		= false;			
				$errors 		= array();
				$errorMessages 	= array();
				
				if($edit['password']!="" && strlen($edit['password'])<$this->settings['passwordMinlength']){
					$errorMessages[] 		= 	array( 
														"title" => "Passwort",
														"message" => "Geben Sie ein Passwort mit mindestens ".$this->settings['passwordMinlength']." Zeichen ein"
											);					
					$allErrors['password']	= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				if(trim($edit['email'])!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail(trim($edit['email']))){					
					$errorMessages[] 		= 	array( 
														"title" => "Email",
														"message" => "Bitte geben Sie eine korrekte Email-Adresse ein."
											);					
					$allErrors['email']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}				
				if($edit['isCompanyAdmin'] && $edit['company']==""){
					$errorMessages[] 		= 	array( 
														"title" => "Firmen-Administrator",
														"message" => "Sie müssen eine Firma angeben um den Benutzer als Administrator zu registrieren"
											);					
					$allErrors['isCompanyAdmin']		= true;
					$dataError 				= true;
					$hasErrors 				= true;
				}
				if($edit['newImage']['name']!=""){
					$imageInfo = $this->getFileInfo($edit['newImage']['name']);
							
					if(!in_array($imageInfo['extension'],array("png","jpg","gif"))){														
						$errorMessages[] 				= 	array( 
																	"title" => "Bild",
																	"message" => 'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt'
														);					
						$allErrors['newImage']	= true;
						$dataError 						= true;
						$hasErrors 						= true;								
					}
				}
				if(false && $edit['falImage']['name']!=""){
					$falImageInfo = $this->getFileInfo($edit['falImage']['name']);
							
					if(!in_array($falImageInfo['extension'],array("png","jpg","gif"))){														
						$errorMessages[] 				= 	array( 
																	"title" => "Bild",
																	"message" => 'Es sind nur Bilder vom Typ; "png","jpg" und "gif" erlaubt'
														);					
						$allErrors['falImage']			= true;
						$dataError 						= true;
						$hasErrors 						= true;								
					}
				}
				
				if(!$hasErrors){					
										
					foreach($object->getUsergroup() AS $usergroup){
						$object->removeUsergroup($usergroup);					
					}					
					foreach($edit['usergroupsUids'] AS $usergroupUid){
						$usergroup = $this->frontendUserGroupRepository->findOneByUid($usergroupUid);																
						$object->addUsergroup($usergroup);							
					}
					//$object->setUsergroup($edit['usergroup']);
					if($edit['password']!=""){
						$object->setPassword($edit['password']);
					}
					$object->setStarttime($edit['starttime']);
					$object->setEndtime($edit['endtime']);					
					$object->setFirstName($edit['firstName']);
					$object->setMiddleName($edit['middleName']);
					$object->setLastName($edit['lastName']);
					$name = "";
					if($edit['firstName']!=""){
						$name = $edit['firstName'];
					}
					if($edit['middleName']!="" && $name==""){
						$name = $edit['middleName'];
					} else {
						$name .= " ".$edit['middleName'];
					}
					if($edit['lastName']!="" && $name==""){
						$name = $edit['lastName'];
					} else {
						$name .= " ".$edit['lastName'];
					}
					$object->setName($name);
					$object->setAddress($edit['address']);
					$object->setTelephone($edit['telephone']);
					$object->setFax($edit['fax']);
					$object->setEmail($edit['email']);
					$object->setTitle($edit['title']);
					$object->setZip($edit['zip']);
					$object->setCity($edit['city']);
					$object->setCountry($edit['country']);
					$object->setWww($edit['www']);
					$object->setCompany($edit['company']);
					//$object->setImage($edit['image']);
					//$object->setFalImage($edit['falImage']);
					$object->setDisallowMailing($edit['disallowMailing']);
					$object->setIsCompanyAdmin($edit['isCompanyAdmin']);
					
					if(false && $edit['falImage']['name']!=""){
				
						/** @var \TYPO3\CMS\Core\Resource\ResourceStorage $storage */
						$storage = $this->storageRepository->findByUid('1');
						$newFileObject = $storage->addFile(
							$edit['falImage']['tmp_name'], $storage->getRootLevelFolder(), $edit['falImage']['name']
						);
						$newFileObject = $storage->getFile($newFileObject->getIdentifier());
						$newFile = $this->fileRepository->findByUid($newFileObject->getProperty('uid'));
							
						/** @var \TYPO3\MooxFeusers\Domain\Model\FileReference $newFileReference */
						$newFileReference = $this->objectManager->get('TYPO3\MooxFeusers\Domain\Model\FileReference');
						$newFileReference->setFile($newFile);
						$newFileReference->setCruserId($GLOBALS['BE_USER']->user['uid']);						
							
						$object->addFalImage($newFileReference);
					}
					
					if($edit['newImage']['name']!=""){
						$newImagePath = $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$edit['newImage']['name'];
						if(file_exists($newImagePath)){				
							$copyCount = 1;
							$imageInfo = $this->getFileInfo($edit['newImage']['name']);
							while($copyCount<=1000){
								$newFileName = $imageInfo['name']."_".str_pad($copyCount,2,"0",STR_PAD_LEFT).".".$imageInfo['extension'];
								$newImagePath = $_SERVER['DOCUMENT_ROOT']."/".$this->extConf['imageUploadFolder']."/".$newFileName;
								if(!file_exists($newImagePath)){
									$edit['newImage']['name'] = $newFileName;
									break;
								}						
								$copyCount++;
							}
							
						}
						move_uploaded_file($edit['newImage']['tmp_name'],$newImagePath);
						$images = explode(",",$object->getImage());
						$images[] = $edit['newImage']['name'];
						$object->setImage(implode(",",$images));
						$edit['image'] = implode(",",$images);
						$this->frontendUserRepository->update($object);
						$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
						$this->flashMessageContainer->add(
							'', 
							'Bild "'.$edit['newImage']['name'].'" wurde erfolgreich hinzugefügt.', 
							\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
					}
															
					$this->frontendUserRepository->update($object);								
					$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
					
					$this->flashMessageContainer->add(
						'', 
						'Änderungen wurden erfolgreich gespeichert.', 
						\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
				
					if(isset($edit['saveAndClose'])){
						if($pagination['currentPage']>1){
							$this->redirect("index","Administration","MooxFeusers",array('@widget_0' => $pagination));
						} else {
							$this->redirect("index");
						}
					} elseif(isset($edit['saveAndNew'])){
						$this->redirect("add");
					} else {
						$this->view->assign('object', $edit);				
						$this->view->assign('action', 'edit');
						$this->view->assign('uid', $uid);
						$this->view->assign('pagination', $pagination);
						$this->view->assign('page', $this->page);
						$this->view->assign('mailerActive', $this->mailerActive);
						$this->view->assign('usergroupSelectionTree', $this->generateUsergroupSelectionTree("usergroups",NULL,$edit['usergroupsUids']));
						$this->view->assign('conf', $this->extConf);
					}
					
				} else {					
					
					foreach($errorMessages AS $errorMessage){
						$this->flashMessageContainer->add($errorMessage['message'], ($errorMessage['title']!="")?$errorMessage['title'].": ":"", \TYPO3\CMS\Core\Messaging\FlashMessage::ERROR);
					}
					
					$this->view->assign('allErrors', $allErrors);					
					
					if($dataError){
						$this->view->assign('dataError',1);
					}
					
					$this->view->assign('object', $edit);				
					$this->view->assign('action', 'edit');
					$this->view->assign('uid', $uid);
					$this->view->assign('pagination', $pagination);
					$this->view->assign('page', $this->page);
					$this->view->assign('mailerActive', $this->mailerActive);
					$this->view->assign('usergroupSelectionTree', $this->generateUsergroupSelectionTree("usergroups",NULL,$edit['usergroupsUids']));
					$this->view->assign('conf', $this->extConf);
				}
				
			} else {
				
				$this->view->assign('object', $edit);				
				$this->view->assign('action', 'edit');
				$this->view->assign('uid', $uid);
				$this->view->assign('pagination', $pagination);
				$this->view->assign('page', $this->page);
				$this->view->assign('mailerActive', $this->mailerActive);
				$this->view->assign('usergroupSelectionTree', $this->generateUsergroupSelectionTree("usergroups",NULL,$edit['usergroupsUids']));
				$this->view->assign('conf', $this->extConf);
				if($edit['addImage']){
					$this->view->assign('allErrors', $allErrors);
				}
			}
			
		} else {
			if($pagination['currentPage']>1){
				$this->redirect("index","Administration","MooxFeusers",array('@widget_0' => $pagination));
			} else {
				$this->redirect("index");
			}
		}
	}
	
	/**
	 * action delete
	 *	
	 * @param \int $uid
	 * @param \array $pagination
	 * @return void
	 */
	public function deleteAction($uid = 0, $pagination = array()) {			
		
		if($uid>0){
		
			$object = $this->frontendUserRepository->findByUid($uid,FALSE);
			
			$this->frontendUserRepository->remove($object);
			
			$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
			
			$this->flashMessageContainer->add(
					'', 
					'Adresse wurde gelöscht.', 
					\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
						
		} 
		
		if($pagination['currentPage']>1){
			$this->redirect("index","Administration","MooxFeusers",array('@widget_0' => $pagination));
		} else {
			$this->redirect("index");
		}
	}
	
	/**
	 * action toggle state
	 *
	 * @param \int $uid	
	 * @param \array $pagination
	 * @return void
	 */
	public function toggleStateAction($uid = 0, $pagination = array()) {			
		
		if($uid>0){						
			
			$object = $this->frontendUserRepository->findByUid($uid,FALSE);
			
			if($object->getHidden()==1){
				$object->setHidden(0);
				$action = "aktviert";
			} else {
				$object->setHidden(1);
				$action = "deaktviert";
			}			
			
			$this->frontendUserRepository->update($object);								
			$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
			
			$this->flashMessageContainer->add(
					'', 
					'Adresse wurde erfolgreich '.$action, 
					\TYPO3\CMS\Core\Messaging\FlashMessage::OK);						
		} 		
		if($pagination['currentPage']>1){			
			$this->redirect("index","Administration","MooxFeusers",array('@widget_0' => $pagination));			
		} else {
			$this->redirect("index");
		}
	}
	
	/**
	 * action toggle mailing allowed
	 *
	 * @param \int $uid	
	 * @param \array $pagination
	 * @return void
	 */
	public function toggleMailingAllowedAction($uid = 0, $pagination = array()) {			
		
		if($uid>0){						
			
			$object = $this->frontendUserRepository->findByUid($uid,FALSE);
			
			if($object->getMailingAllowed()==1){
				$object->setMailingAllowed(0);
				$action = "aktviert";
			} else {
				$object->setMailingAllowed(1);
				$action = "deaktviert";
			}			
			
			$this->frontendUserRepository->update($object);								
			$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
			
			$this->flashMessageContainer->add(
					'', 
					'Newsletter-Empfang für Benutzer wurde erfolgreich '.$action, 
					\TYPO3\CMS\Core\Messaging\FlashMessage::OK);						
		} 		
		if($pagination['currentPage']>1){			
			$this->redirect("index","Administration","MooxFeusers",array('@widget_0' => $pagination));			
		} else {
			$this->redirect("index");
		}
	}
	
	/**
	 * get fe user
	 *
	 * @param \int $uid
	 * @return array/object $feUser
	 */
	public function getFeUser($uid) {
		
		$feUser = array();
		
		if($uid){
					
			$feUser = $this->frontendUserRepository->findByUid($uid,FALSE);
		}
		
		return $feUser;
	}

	/**
	 * Get array of folders with frontend users module	
	 *	
	 * @return	array	folders with frontend users module	
	 */
	public function getFolders() {
		
		global $BE_USER;
		
		$folders = array();
		
		$query = array(
			'SELECT' => '*',
			'FROM' => 'pages',
			'WHERE' => $BE_USER->getPagePermsClause(1)." AND deleted=0 AND doktype=254 AND module='mxfeuser'"
		);
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);
		
		$folderCnt = 0;
		while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($pages)) {
			$folders[$folderCnt] = $row;
			$rootline = $this->pageRepository->getRootLine($row['uid']);
			
			foreach($rootline AS $rootlinepage){
				if($rootlinepage['is_siteroot']){
					$folders[$folderCnt]['rootpage'] = $rootlinepage;
					break;
				}
			}
			
			if(!$folders[$folderCnt]['rootpage']){
				$folders[$folderCnt]['rootpage'] = $rootline[0];
			}
					
			$rootline = array_reverse($rootline);
			
			if(isset($rootline[count($rootline)-2])){			
				$pageInfo = $this->pageRepository->getPage((int)$rootline[count($rootline)-2]['uid']);		
				if($pageInfo['module']=='mxfeuser'){
					$folders[$folderCnt]['folder'] = $pageInfo['uid'];				
				}
				
			}
			
			$folders[$folderCnt]['rootline'] = $rootline;
			$folderCnt++;
		}
				
		usort($folders, array("\TYPO3\MooxFeusers\Controller\AdministrationController", "sortByFolderAndTitle"));
		
		$folders = array_reverse($folders);
		
		return $folders;		
	}
	
	/**
	 * Get query replacements	
	 *
	 * @param string $query
	 * @return	array	list view fields 	
	 */
	public function getQueryReplacements($query = "") {
		
		$replacements = array();
		
		if($query!=""){
			$replacements[] = array("search" => $query, "replace" => '<span class="query">'.$query.'</span>');
			$replacements[] = array("search" => strtolower($query), "replace" => '<span class="query">'.strtolower($query).'</span>');
			$replacements[] = array("search" => strtoupper($query), "replace" => '<span class="query">'.strtoupper($query).'</span>');
			$replacements[] = array("search" => ucfirst($query), "replace" => '<span class="query">'.ucfirst($query).'</span>');
			$replacements[] = array("search" => '<span class="query"><span class="query">', "replace" => '<span class="query">');
			$replacements[] = array("search" => '</span></span>', "replace" => '</span>');
		}
		
		return $replacements;
	}
	
	/**
	 * Get list view fields	
	 *	
	 * @return	array	list view fields 	
	 */
	public function getListViewFields() {
		
		$dateTimeFields = array ("tstamp","starttime","endtime","crdate","lastlogin");
		
		$listViewFields = array();
		if($this->settings['listViewFields']!=""){
			$listViewFieldsTmp = explode(",",$this->settings['listViewFields']);
			foreach($listViewFieldsTmp AS $listViewField){
				$listViewField = explode(":",$listViewField);
				if(in_array(trim($listViewField[0]),$this->getAllowedFields())){
					$listViewFields[] = array(	
												"name" 		=> trim($listViewField[0]),
												"lenght" 	=> (trim($listViewField[1])>0)?trim($listViewField[1]):10000,
												"type"		=> (in_array(trim($listViewField[0]),$dateTimeFields))?"datetime":"string"
										);
				} else {
					$additionalFieldsAccepted = true;
					$listViewAdditionalFields = array();
					$listViewAdditionalFieldsTmp = explode("|",$listViewField[0]);					
					if(count($listViewAdditionalFieldsTmp)>1){
						foreach($listViewAdditionalFieldsTmp AS $listViewAdditionalField){
							if(in_array(trim($listViewAdditionalField),$this->getAllowedFields())){
								$listViewAdditionalFields[] = 	array(
																		"name" 	=> trim($listViewAdditionalField),
																		"type"	=> (in_array(trim($listViewAdditionalField),$dateTimeFields))?"datetime":"string",
																);									
							} else {
								$additionalFieldsAccepted = false;
								break;
							}							
						}
					} else {
						$additionalFieldsAccepted = false;
					}
					if($additionalFieldsAccepted){
						$mainViewField = $listViewAdditionalFields[0];
						unset($listViewAdditionalFields[0]);
						$listViewFields[] = array(	
													"name" 			=> $mainViewField,
													"lenght" 		=> (trim($listViewField[1])>0)?trim($listViewField[1]):10000,
													"type"			=> (in_array($mainViewField,$dateTimeFields))?"datetime":"string",
													"additional" 	=> $listViewAdditionalFields
											);
					}
				}
			}
		}		
		if(!count($listViewFields) && $this->extConf['listViewFields']!=""){
			$listViewFieldsTmp = explode(",",$this->extConf['listViewFields']);			
			foreach($listViewFieldsTmp AS $listViewField){
				$listViewField = explode(":",$listViewField);				
				if(in_array(trim($listViewField[0]),$this->getAllowedFields())){
					$listViewFields[] = array(	
												"name" 		=> trim($listViewField[0]),
												"lenght" 	=> (trim($listViewField[1])>0)?trim($listViewField[1]):10000,
												"type"		=> (in_array(trim($listViewField[0]),$dateTimeFields))?"datetime":"string"
										);
				} else {
					$additionalFieldsAccepted = true;
					$listViewAdditionalFields = array();
					$listViewAdditionalFieldsTmp = explode("|",$listViewField[0]);
					if(count($listViewAdditionalFieldsTmp)>1){
						foreach($listViewAdditionalFieldsTmp AS $listViewAdditionalField){							
							if(in_array(trim($listViewAdditionalField),$this->getAllowedFields())){
								$listViewAdditionalFields[] = 	array(
																		"name" 	=> trim($listViewAdditionalField),
																		"type"	=> (in_array(trim($listViewAdditionalField),$dateTimeFields))?"datetime":"string",
																);								
							} else {
								$additionalFieldsAccepted = false;
								break;
							}							
						}
					} else {
						$additionalFieldsAccepted = false;
					}
					if($additionalFieldsAccepted){
						
						$mainViewField = $listViewAdditionalFields[0];
						unset($listViewAdditionalFields[0]);
						$listViewFields[] = array(	
													"name" 			=> $mainViewField,
													"lenght" 		=> (trim($listViewField[1])>0)?trim($listViewField[1]):10000,
													"type"			=> (in_array($mainViewField,$dateTimeFields))?"datetime":"string",
													"additional" 	=> $listViewAdditionalFields
											);
					}
				}
			}
		}
		if(!count($listViewFields)){
			$listViewFields[0] = array("name" => "name", "length" => 10000, "type" => "string");
			$listViewFields[1] = array("name" => "email", "length" => 10000, "type" => "string");
		}
		return $listViewFields;
	}
	
	/**
	 * Get first folder with fe users module	
	 *	
	 * @return	array	folder 	
	 */
	public function getFirstFolder() {
		
		global $BE_USER;
		
		$query = array(
			'SELECT' => '*',
			'FROM' => 'pages',
			'WHERE' => $BE_USER->getPagePermsClause(1)." AND deleted=0 AND doktype=254 AND module='mxfeuser'",
			'LIMIT' => 1
		);
		$pages = $GLOBALS['TYPO3_DB']->exec_SELECT_queryArray($query);		
		return $pages->fetch_assoc();		
	}
	
	/**
	 * Get file info	
	 *
	 * @param \int $uid
	 * @return	array	file 	
	 */
	public function getFileInfo($filename){
		$seperatorIndex 	= strrpos ($filename, ".");
		$file['name'] 		= substr ($filename, 0, $seperatorIndex);
		$file['extension'] 	= strtolower(substr ($filename, ($seperatorIndex+1)));
		return $file;
	}
	
	/**
	 * copy to temp	
	 *
	 * @param \string $source
	 * @param \string $filename
	 * @return	array	file 	
	 */
	public function copyToTypo3Temp($source,$filename){
		$tempdir = $GLOBALS["_SERVER"]["DOCUMENT_ROOT"]."/typo3temp/moox_feusers";
		$filenameParts = explode(".",$filename);		
		$tempfile = $tempdir."/".md5($filenameParts[0]).".".$filenameParts[1];
		if(!is_dir($tempdir)){
			mkdir($tempdir);
		}
		if(file_exists($tempfile)){
			unlink($tempfile);
		}
		if(!file_exists($tempfile)){
			move_uploaded_file($source,$tempfile);
		} 
		return $tempfile;
	}
	
	/**
	 * set page or redirect to default/last folder
	 *
	 * @param \array $folders
	 * @return void
	 */
	public function redirectToFolder($folders = array()) {
		
		$id 		= \TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id');
		$id			= ($id!="")?(int)$id:NULL;
		
		if(is_null($this->backendSession->get("id")) && (int)$this->settings['feusersStartPid']>0){
			$this->setPage((int)$this->settings['feusersStartPid']);
			$this->backendSession->store("id",$this->page);
			$this->initializeStorageSettings();
		} elseif(is_null($this->backendSession->get("id")) && (int)$this->settings['feusersStartPid']<1) {			
			$this->setPage(0);
			$this->backendSession->store("id",$this->page);
			$this->initializeStorageSettings();
		} elseif(!is_null($id)){
			$this->setPage($id);
			$this->backendSession->store("id",$this->page);
			$this->initializeStorageSettings();
		} else {
			$this->setPage($this->backendSession->get("id"));
			$this->backendSession->store("id",$this->page);
			$this->initializeStorageSettings();
		}
				
		if($this->page>0){
			$isFeUsersFolder = false;
			foreach($folders AS $feUsersFolder){
				if($feUsersFolder['uid']==$this->page){
					$isFeUsersFolder = true;
					break;
				}
			}
			if(!$isFeUsersFolder){
				$this->backendSession->delete("id");
				\TYPO3\CMS\Core\Utility\HttpUtility::redirect($_SERVER['REQUEST_URI']);				
			}
		}
	}
	
	/**
	 * process filter
	 *
	 * @param \array $filter
	 * @return \array $filter
	 */
	public function processFilter($filter = array()) {
		
		if($this->backendSession->get("filter")){
			$filter_tmp = unserialize($this->backendSession->get("filter"));
			if(isset($filter['mailing'])){
				$filter_tmp['mailing'] = $filter['mailing'];
			}
			if(isset($filter['state'])){
				$filter_tmp['state'] = $filter['state'];
			}
			if(isset($filter['perPage'])){
				$filter_tmp['perPage'] = $filter['perPage'];
			}
			if(isset($filter['sortField'])){
				$filter_tmp['sortField'] = $filter['sortField'];
			}
			if(isset($filter['sortDirection'])){
				$filter_tmp['sortDirection'] = $filter['sortDirection'];
			}
			if(isset($filter['query'])){
				$filter_tmp['query'] = $filter['query'];
			}
			$filter = $filter_tmp;			
		} else {			
			$filter['mailing'] 	= 0;			
			$filter['state'] 	= 0;			
			if($this->settings['itemsPerPage']>0){
				$filter['perPage'] 	= $this->settings['itemsPerPage'];
			} else {
				$filter['perPage'] 	= 25;
			}			
			if(in_array($this->settings['defaultSortField'],$this->getAllowedFields())){
				$filter['sortField'] = $this->settings['defaultSortField'];
			} else {
				$filter['sortField'] = "name";
			}							
			if(in_array($this->settings['defaultSortDirection'],array("ASC","DESC"))){
				$filter['sortDirection'] = $this->settings['defaultSortDirection'];
			} else {
				$filter['sortDirection'] = "ASC";
			}						
		}
		$this->backendSession->store("filter",serialize($filter));
		return $filter;
	}
	
	/**
	 * generate usergroups selection tree
	 *
	 * @param string $id identifier of selection field
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $usergroups usergroups
	 * @param array $selected selected categories
	 * @param string $class class
	 * @param integer $depth depth
	 * @return string $tree
	 */
	function generateUsergroupSelectionTree($id,$usergroups = array(),$selected = array(),$class="",$title="",$depth = 0){
		
		if(!is_array($usergroups) || count($usergroups)<1){			
			$usergroups = $this->frontendUserGroupRepository->findByPids($this->page);
		}
		
		//$subgroups = $this->collectUsergroupSubgroups($usergroups);#
		
		$tree  = "<ul>";
		foreach($usergroups AS $usergroup){
			if(true || !in_array($usergroup->getUid(),$subgroups)){
				if(in_array($usergroup->getUid(),$selected)){
					$activeClass = " feuser-usergroup-selector-active"; 
				} else {
					$activeClass = "";
				}
				if($class!=""){
					$elementClass = $class." feuser-usergroup-selector-".$usergroup->getUid();
				} else {
					$elementClass = "feuser-usergroup-selector feuser-usergroup-selector-".$usergroup->getUid();
				}
				if($title!=""){
					$wrapperTitle = $title." &rsaquo; ".$usergroup->getTitle();
				} else {
					$wrapperTitle = $usergroup->getTitle();
				}
				$wrapperClass = " ".str_replace("-selector","-selector-wrapper",$elementClass);				
				$tree .= '<li id="feuser-usergroup-selector-'.$usergroup->getUid().'" class="'.$elementClass.'">';
				$tree .= '<div id="feuser-usergroup-selector-wrapper-'.$usergroup->getUid().'" title="'.$usergroup->getTitle().' [UID: '.$usergroup->getUid().']" onclick="mooxFeusersToggleOption(\'#'.$id.'\','.$usergroup->getUid().',\''.$wrapperTitle.'\')" class="feuser-usergroup-selector-wrapper'.$wrapperClass.$activeClass.'">';			
				if($selected[0]==$usergroup->getUid()){
					$tree .= '<span id="feuser-usergroup-selector-icon-'.$usergroup->getUid().'" class="t3-icon t3-icon-apps t3-icon-apps-toolbar t3-icon-toolbar-menu-shortcut feuser-usergroup-selector-icon">&nbsp;</span>';
				} else {
					$tree .= '<span id="feuser-usergroup-selector-icon-'.$usergroup->getUid().'" class="t3-icon t3-icon-mimetypes t3-icon-mimetypes-x t3-icon-x-sys_category feuser-usergroup-selector-icon">&nbsp;</span>';
				}
				$tree .= '<span id="feuser-usergroup-selector-title-'.$usergroup->getUid().'" title="'.$wrapperTitle.'" class="feuser-usergroup-selector-title">'.$usergroup->getTitle().'</span>';
				$tree .= '</div>';			
				$tree .= '<div id="feuser-usergroup-selector-context-'.$usergroup->getUid().'" class="feuser-usergroup-selector-context">';
				$tree .= '<div onclick="mooxFeusersSetMainUsergroup(\'#'.$id.'\','.$usergroup->getUid().',\''.$wrapperTitle.'\')" class="moox-feusers-context-element icon-select-mainusergroup"><span class="t3-icon t3-icon-apps t3-icon-apps-toolbar t3-icon-toolbar-menu-shortcut feuser-usergroup-selector-icon">&nbsp;</span>"'.$usergroup->getTitle().'" als Hauptgruppe wählen</div>';				
				$tree .= '</div>';								
				$tree .= "</li>";				
			}
		}
		$tree .= "</ul>";
		return $tree;
	}
	
	/**
	 * collect usergroup subgroups
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\QueryInterface $usergroups usergroups	
	 * @param integer $depth depth
	 * @return array $subgroups
	 */
	function collectUsergroupSubgroups($usergroups,$depth = 0){
		$subgroups = array();
		foreach($usergroups AS $usergroup){
			if($depth>0){
				$subgroups[] = $usergroup->getUid();
			}
			$subgroups = array_merge($subgroups,$this->generateUsergroupSelectionTree($usergroup->getSubgroup(),$depth+1));
		}
		return $subgroups;
	}

	/**
	 * Returns page
	 *
	 * @return integer
	 */
	public function getPage() {
		return $this->page;
	}

	/**
	 * Set page
	 *
	 * @param integer $page page
	 * @return void
	 */
	public function setPage($page) {
		$this->page = $page;
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

	/**
	 * Returns mailer active
	 *
	 * @return boolean
	 */
	public function getMailerActive() {
		return $this->mailerActive;
	}

	/**
	 * Set mailer active
	 *
	 * @param boolean $mailerActive mailer active
	 * @return void
	 */
	public function setMailerActive($mailerActive) {
		$this->mailerActive = $mailerActive;
	}
	
	/**
	 * Returns allowed fields
	 *
	 * @return array
	 */
	public function getAllowedFields() {
		return $this->allowedFields;
	}

	/**
	 * Set allowed fields
	 *
	 * @param array $allowedFields allowed fields
	 * @return void
	 */
	public function setAllowedFields($allowedFields) {
		$this->allowedFields = $allowedFields;
	}
}
?>