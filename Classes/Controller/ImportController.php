<?php
namespace DCNGmbH\MooxFeusers\Controller;

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
     * frontendUserRepository
     *
     * @var \DCNGmbH\MooxFeusers\Domain\Repository\FrontendUserRepository
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

        $this->setPage((int)\TYPO3\CMS\Core\Utility\GeneralUtility::_GET('id'));

        //fallback to current pid if no storagePid is defined
        if (version_compare(TYPO3_version, '6.0.0', '>=')) {
            $configuration = $this->configurationManager->getConfiguration(\TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        } else {
            $configuration = $this->configurationManager->getConfiguration(Tx_Extbase_Configuration_ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        }

        $currentPid['persistence']['storagePid'] = $this->page;
        $this->configurationManager->setConfiguration(array_merge($configuration, $currentPid));

        $this->setMailerActive(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('moox_mailer'));

        $this->extConf 						= unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);
        $this->frontendUserRepository 		= $this->objectManager->get('DCNGmbH\\MooxFeusers\\Domain\\Repository\\FrontendUserRepository');
        $this->frontendUserGroupRepository 	= $this->objectManager->get('DCNGmbH\\MooxFeusers\\Domain\\Repository\\FrontendUserGroupRepository');
        $this->pageRepository 				= $this->objectManager->get('TYPO3\\CMS\\Frontend\\Page\\PageRepository');
    }

    /**
     * action index
     *
     * @param \array $import
     * @return void
     */
    public function indexAction($import = array()) {

        global $BE_USER;

        $allowedFields = array(	"uid",
								"usergroup",
								"disable",
								"disallow_mailing",
								"username",
								"password",
								"gender",
								"title",
								"first_name",
								"middle_name",
								"last_name",
								"company",
								"country",
								"zip",
								"city",
								"address",
								"telephone",
								"fax",
								"email",
								"www",
								"unused");

        $defaultFields = array(	"uid",
								"usergroup",
								"disable",
								"disallow_mailing",
								"username",
								"password",
								"gender",
								"title",
								"first_name",
								"middle_name",
								"last_name",
								"company",
								"country",
								"zip",
								"city",
								"address",
								"telephone",
								"fax",
								"email",
								"www");

        $additionalImportFields = $this->getAdditionalImportFields();

        if(count($additionalImportFields)){
            $allowedFields = array_merge($allowedFields,$additionalImportFields);
        }

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
                if(in_array($import['mode'],array(1,2)) && !in_array("uid",$csvFields) && !in_array("username",$csvFields) && !in_array("email",$csvFields)){
                    $errorMessages[] 		= 	array(
                        "title" => "Format",
                        "message" => 'Ihr Format muss für den gewählten Modus mindestens eines der folgenden Felder beinhalten: uid, username, email'
                    );
                    $allErrors['format']	= true;
                    $dataError 				= true;
                    $hasErrors 				= true;
                } elseif(!in_array($import['mode'],array(1,2)) && !in_array("username",$csvFields) ){
                    $errorMessages[] 		= 	array(
                        "title" => "Format",
                        "message" => 'Ihr Format muss für den gewählten Modus mindestens das Feld "username" beinhalten'
                    );
                    $allErrors['format']	= true;
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
                    } elseif(!$import['skipFirst'] && $row==1 && (in_array("uid",$data) || in_array("username",$data) || in_array("email",$data))){
                        $skip = true;
                    }

                    $usergroups = array();

                    if(!$skip){

                        $object = $this->objectManager->get('DCNGmbH\\MooxFeusers\\Domain\\Model\\FrontendUser');

                        $row++;

                        $num = count($data);
                        
						for ($c=0; $c < $num; $c++) {
                            if($csvFields[$c] == "gender"){
                                if(in_array(strtolower(trim($data[$c])),array("herr","männlich","m","mr","male","1"))){
                                    $gender = 1;
                                } elseif(in_array(strtolower(trim($data[$c])),array("frau","weiblich","w","mrs","female","f","2"))){
                                    $gender = 2;
                                } else {
                                    $gender = 0;
                                }
                                $object->setGender($gender);
                            } elseif($csvFields[$c] == "usergroup"){
                                if(trim($data[$c])!=""){
                                    $usergroupsTmp = explode(",",trim($data[$c]));
                                    if(count($usergroupsTmp)){
                                        foreach($usergroupsTmp AS $usergroupUid){
                                            $usergroup = $this->frontendUserGroupRepository->findByUid($usergroupUid);
                                            if(is_object($usergroup)){
                                                $usergroups[] = $usergroup->getUid();
                                            }
                                        }
                                    }
                                }
                            } elseif($csvFields[$c] == "password"){
                                if(trim($data[$c])==""){
                                    $password = md5(time()."gbJYG2mVCvLTPf");
                                } else {
                                    $password = $data[$c];
                                }
                                $object->setPassword($password,TRUE);
                            } elseif(in_array($csvFields[$c],$allowedFields) && $csvFields[$c] != "unused"){
                                $setCall = "set".\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($csvFields[$c]);
                                if(method_exists($object,$setCall)){
                                    $object->$setCall($data[$c]);
                                }
                            }
                        }
						
						if($this->mailerActive && $import['disallowMailing']){
                            $object->setDisallowMailing(1);
                        }	

						if(is_array($import['feGroups']) && count($import['feGroups'])){
                            $usergroups = array();
							foreach($import['feGroups'] AS $usergroupUid){
                                $usergroup = $this->frontendUserGroupRepository->findByUid($usergroupUid);
                                if(is_object($usergroup)){
                                    $usergroups[] = $usergroup->getUid();
                                }
                            }
                        }
												
						$isValid = true;
						
						if($object->getGender()==""){
                            $object->setGender(0);
                        }												
						
						//$duplicate = $this->frontendUserRepository->findDuplicate($object);
						//if(true || !is_object($duplicate)){
						if($import['mode']>0){
							
							if(in_array("uid",$csvFields)){
								$existingUser = $this->frontendUserRepository->findExistingByUid($object);								
							}
							if(!is_object($existingUser) && in_array("username",$csvFields)){
								$existingUser = $this->frontendUserRepository->findExistingByUsername($object);
							}

							if(!is_object($existingUser)){
								
								if($object->getUsername()==""){
									$isValid = false;
								}
								if($object->getEmail()!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($object->getEmail())){
									$isValid = false;
								}
								
								if($isValid){
								
									$name = "";
									if(trim($object->getFirstName())!=""){
										$name = trim($object->getFirstName());
									}
									if(trim($object->getMiddleName())!="" && $name==""){
										$name = trim($object->getMiddleName());
									} elseif(trim($object->getMiddleName())!="" && $name!=""){
										$name .= " ".trim($object->getMiddleName());
									}
									if(trim($object->getLastName())!="" && $name==""){
										$name = trim($object->getLastName());
									} elseif(trim($object->getLastName())!="" && $name!=""){
										$name .= " ".trim($object->getLastName());
									}
									$object->setName($name);

									if($import['disableNew']){
										$object->setDisable(1);
									}									
									
									foreach($usergroups AS $usergroupUid){
										$usergroup = $this->frontendUserGroupRepository->findByUid($usergroupUid);
										if(is_object($usergroup)){
											$object->addUsergroup($usergroup);
										}
									}

									$object->setUid(NULL);
									
									$this->frontendUserRepository->add($object);

									$added++;
								}

							} elseif(is_object($existingUser) && in_array($import['mode'],array(1,2))){
																
								if(in_array("username",$csvFields) && $object->getUsername()==""){
									$isValid = false;
								}
								if(in_array("email",$csvFields) && $object->getEmail()!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($object->getEmail())){
									$isValid = false;
								}
								
								if($isValid){
									foreach($allowedFields AS $allowedField){
										if(!in_array($allowedField,array("uid","usergroup","password","unused"))){
											$getCall = "get".\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($allowedField);
											if(method_exists($object,$getCall)){
												$setCall = "set".\TYPO3\CMS\Core\Utility\GeneralUtility::underscoredToUpperCamelCase($allowedField);
												if(method_exists($existingUser,$getCall) && method_exists($existingUser,$setCall)){
													if($import['mode']==1 || ($import['mode']==2 && $existingUser->$getCall()=="")){
														$existingUser->$setCall($object->$getCall());
													}
												}
											}
										} elseif($allowedField=="password"){
											if($import['mode']==1 || ($import['mode']==2 && $existingUser->getPassword()=="")){
												if($import['encodePassword']){
													$existingUser->setPassword($object->getPassword());
												} else {
													$existingUser->setPassword($object->getPassword(),TRUE);
												}
											}
										} elseif($allowedField=="usergroup" && count($usergroups)){
											if($import['mode']==1 || ($import['mode']==2 && count($existingUser->getUsergroup())==0)){
												// workaround because of problems with setting new groups by extbase
												$res = $GLOBALS['TYPO3_DB']->exec_UPDATEquery("fe_users","uid=".$existingUser->getUid(),array("usergroup" => implode(",",$usergroups)));
											}
										}
									}

									$name = "";
									if(trim($existingUser->getFirstName())!=""){
										$name = trim($existingUser->getFirstName());
									}
									if(trim($existingUser->getMiddleName())!="" && $name==""){
										$name = trim($existingUser->getMiddleName());
									} elseif(trim($existingUser->getMiddleName())!="" && $name!=""){
										$name .= " ".trim($existingUser->getMiddleName());
									}
									if(trim($existingUser->getLastName())!="" && $name==""){
										$name = trim($existingUser->getLastName());
									} elseif(trim($existingUser->getLastName())!="" && $name!=""){
										$name .= " ".trim($existingUser->getLastName());
									}
									$existingUser->setName($name);

									$this->frontendUserRepository->update($existingUser);

									$updated++;
								}
							} else {
								$skipped++;
							}
						} else {
							
							$existingUser = $this->frontendUserRepository->findExistingByUsername($object);
							
							if(!is_object($existingUser)){
								
								if($object->getUsername()==""){
									$isValid = false;
								}
								if($object->getEmail()!="" && !\TYPO3\CMS\Core\Utility\GeneralUtility::validEmail($object->getEmail())){
									$isValid = false;
								}
								
								if($isValid){
								
									$name = "";
									if(trim($object->getFirstName())!=""){
										$name = trim($object->getFirstName());
									}
									if(trim($object->getMiddleName())!="" && $name==""){
										$name = trim($object->getMiddleName());
									} elseif(trim($object->getMiddleName())!="" && $name!=""){
										$name .= " ".trim($object->getMiddleName());
									}
									if(trim($object->getLastName())!="" && $name==""){
										$name = trim($object->getLastName());
									} elseif(trim($object->getLastName())!="" && $name!=""){
										$name .= " ".trim($object->getLastName());
									}
									$object->setName($name);

									if($import['disableNew']){
										$object->setDisable(1);
									}

									foreach($usergroups AS $usergroupUid){
										$usergroup = $this->frontendUserGroupRepository->findByUid($usergroupUid);
										if(is_object($usergroup)){
											$object->addUsergroup($usergroup);
										}
									}
									
									$object->setUid(NULL);

									$this->frontendUserRepository->add($object);
									
									$added++;
								}

							} else {
								$existing++;
							}
						}
						/*
						} else {
							$existing++;
						}
						*/
                     
						if(!$isValid){
                            $errors++;
                        } else {
							$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();						
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
                    $successMessage .= ' '.$updated.' Adresse(n) wurden '.(($import['mode']==1)?"überschrieben":"ergänzt").'.';
                }
                if($skipped>0){
                    $infoMessage .= ' '.$skipped.' Adresse(n) wurde übersprungen.';
                }
                if($existing>0){
                    $infoMessage .= ' '.$existing.' Adresse(n) waren bereits vorhanden.';
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

                $this->view->assign('object', $import);

                if($added>0 || $updated>0){
                    $this->redirect("index","Administration");
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

                $import['separator'] 		= ($this->settings['importSeparator'])?$this->settings['importSeparator']:";";

                if($BE_USER->user['moox_feusers_custom_csv_import_fields']!=""){
                    $importFormat = implode($import['separator'],explode(",",$BE_USER->user['moox_feusers_custom_csv_import_fields']));
                } elseif($this->extConf['defaultCsvImportFields']!=""){
                    $importFormat = implode($import['separator'],explode(",",$this->extConf['defaultCsvImportFields']));
                } else {
                    $importFormat = ($this->settings['importFormat'])?$this->settings['importFormat']:implode($import['separator'],$defaultFields);
                }

                $import['format'] 			= $importFormat;
                $import['mode'] 			= (in_array($this->settings['importMode'],array(0,1,2,3)))?$this->settings['importMode']:"1";
                $import['disableNew'] 		= ($this->settings['importDisableNew'])?$this->settings['importDisableNew']:0;
                $import['disallowMailing'] 	= ($this->settings['importDisallowMailing'])?$this->settings['importDisallowMailing']:0;
                $import['encodePassword'] 	= ($this->settings['importEncodePassword'])?$this->settings['importEncodePassword']:0;
                $import['skipFirst'] 		= ($this->settings['importSkipFirst'])?$this->settings['importSkipFirst']:0;
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
            if($pageInfo['module']=='mxfeuser'){
                $folder = $pageInfo['uid'];
            }

        }

        $folders = $this->getFolders();

        $feGroups = array();
        $feGroupsTmp = $this->frontendUserGroupRepository->findByPids($this->page);
        foreach($feGroupsTmp AS $feGroup){
            $feGroups[$feGroup->getUid()] = $feGroup->getTitle()." [UID: ".$feGroup->getUid()."]";
        }


        $this->view->assign('action', 'index');
        $this->view->assign('page', $this->page);
        $this->view->assign('folder', $folder);
        $this->view->assign('rootpage', $rootpage);
        $this->view->assign('rootline', $rootline);
        $this->view->assign('folders', (count($folders)>0)?$folders:false);
        $this->view->assign('feGroups', (count($feGroups)>0)?$feGroups:false);
        $this->view->assign('object', $import);
        $this->view->assign('mailerActive', $this->mailerActive);

    }

    /**
     * Get array of folders with feusers module
     *
     * @return	array	folders with feusers module
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

        usort($folders, array("\DCNGmbH\MooxFeusers\Controller\ImportController", "sortByFolderAndTitle"));

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
     * Get additional import fields
     *
     * @return	array	additional import fields
     */
    public function getAdditionalImportFields() {

        $additionalImportFields = array();

        if(isset($GLOBALS['TCA']['fe_users']['additionalImportFields']) && is_array($GLOBALS['TCA']['fe_users']['additionalImportFields']) && count($GLOBALS['TCA']['fe_users']['additionalImportFields'])){
            foreach($GLOBALS['TCA']['fe_users']['additionalImportFields'] AS $importFields){
                if($importFields!=""){
                    $additionalImportFields = array_merge($additionalImportFields,explode(",",$importFields));
                }
            }
        }

        return $additionalImportFields;
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