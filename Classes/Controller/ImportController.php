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
class ImportController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
		
	/**
	 * addressRepository
	 *
	 * @var \TYPO3\MooxAddress\Domain\Repository\AddressRepository
	 */
	protected $addressRepository;				
	
	/**
	 * @var \TYPO3\CMS\Frontend\Page\PageRepository
	 */
	protected $pageRepository;
	
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
		
		//fallback to current pid if no storagePid is defined
		if (version_compare(TYPO3_version, '6.0.0', '>=')) {
			$configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		} else {
			$configuration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
		}
		if (empty($configuration['persistence']['storagePid'])) {
			$currentPid['persistence']['storagePid'] = (int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id');
			$this->configurationManager->setConfiguration(array_merge($configuration, $currentPid));
		}
		$this->setMailerActive(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('moox_mailer'));
		$this->setPage((int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id'));
		
		$this->extConf 					= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_address']);	
		$this->addressRepository 		= $this->objectManager->get('TYPO3\\MooxAddress\\Domain\\Repository\\AddressRepository');		
		$this->pageRepository 			= $this->objectManager->get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');	
	}
	
	/**
	 * action index
	 *
	 * @param \array $import
	 * @return void
	 */
	public function indexAction($import = array()) {			
		
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
						
						$object = $this->objectManager->get('TYPO3\\MooxAddress\\Domain\\Model\\Address');
						
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
							$duplicate = $this->addressRepository->findDuplicate($object);
							if(!is_object($duplicate)){
								if($import['mode']>0){
									$foundAddress = $this->addressRepository->findByEmail($object->getEmail());									
									if(!is_object($foundAddress)){
										$this->addressRepository->add($object);										
										$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
										$added++;
									} elseif(is_object($foundAddress) && in_array($import['mode'],array(1,2))){
										if($import['mode']==2 && $foundAddress->getUnregistered()==0){
											$foundAddress->setMailingAllowed($object->getMailingAllowed());
											$foundAddress->setRegistered($object->getRegistered());
										}
										if($import['mode']==2 || $foundAddress->getGender()==0){
											$foundAddress->setGender($object->getGender());
										}
										if($import['mode']==2 || $foundAddress->getTitle()==""){
											$foundAddress->setTitle($object->getTitle());
										}
										if($import['mode']==2 || $foundAddress->getForename()==""){
											$foundAddress->setForename($object->getForename());
										}
										if($import['mode']==2 || $foundAddress->getSurname()==""){
											$foundAddress->setSurname($object->getSurname());
										}
										if($import['mode']==2 || $foundAddress->getCompany()==""){
											$foundAddress->setCompany($object->getCompany());
										}
										if($import['mode']==2 || $foundAddress->getDepartment()==""){
											$foundAddress->setDepartment($object->getDepartment());
										}
										if($import['mode']==2 || $foundAddress->getStreet()==""){
											$foundAddress->setStreet($object->getStreet());
										}
										if($import['mode']==2 || $foundAddress->getZip()==""){
											$foundAddress->setZip($object->getZip());
										}
										if($import['mode']==2 || $foundAddress->getCity()==""){
											$foundAddress->setCity($object->getCity());
										}
										if($import['mode']==2 || $foundAddress->getRegion()==""){
											$foundAddress->setRegion($object->getRegion());
										}
										if($import['mode']==2 || $foundAddress->getCountry()==""){
											$foundAddress->setCountry($object->getCountry());
										}
										if($import['mode']==2 || $foundAddress->getPhone()==""){
											$foundAddress->setPhone($object->getPhone());
										}
										if($import['mode']==2 || $foundAddress->getMobile()==""){
											$foundAddress->setMobile($object->getMobile());
										}
										if($import['mode']==2 || $foundAddress->getFax()==""){
											$foundAddress->setFax($object->getFax());
										}
										if($import['mode']==2 || $foundAddress->getWww()==""){
											$foundAddress->setWww($object->getWww());
										}
										$this->addressRepository->update($foundAddress);
										$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
										$updated++;
									} else {
										$skipped++;
									}
								} else {
									$this->addressRepository->add($object);
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
			if($pageInfo['module']=='mxaddress'){
				$folder = $pageInfo['uid'];				
			}
			
		}
		
		$folders = $this->getFolders();
				
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
	 * Get array of folders with addresses module	
	 *	
	 * @return	array	folders with addresses module	
	 */
	public function getFolders() {
		
		global $BE_USER;
		
		$folders = array();
		
		$query = array(
			'SELECT' => '*',
			'FROM' => 'pages',
			'WHERE' => $BE_USER->getPagePermsClause(1)." AND deleted=0 AND doktype=254 AND module='mxaddress'"
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
				if($pageInfo['module']=='mxaddress'){
					$folders[$folderCnt]['folder'] = $pageInfo['uid'];				
				}
				
			}
			
			$folders[$folderCnt]['rootline'] = $rootline;
			$folderCnt++;
		}
				
		usort($folders, array("\TYPO3\MooxAddress\Controller\ImportController", "sortByFolderAndTitle"));
		
		$folders = array_reverse($folders);
		
		return $folders;		
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
	 * @return array
	 */
	public function getMailerActive() {
		return $this->mailerActive;
	}

	/**
	 * Set mailer active
	 *
	 * @param array $mailerActive mailer active
	 * @return void
	 */
	public function setMailerActive($mailerActive) {
		$this->mailerActive = $mailerActive;
	}
}
?>