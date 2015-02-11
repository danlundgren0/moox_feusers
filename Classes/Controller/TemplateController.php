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
class TemplateController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {
	
	/**
	 * templateRepository
	 *
	 * @var \TYPO3\MooxMailer\Domain\Repository\TemplateRepository
	 */
	protected $templateRepository;		
	
	/**
	 * extConf
	 *
	 * @var boolean
	 */
	protected $extConf;
	
	/**
	 * initialize the controller
	 *
	 * @return void
	 */
	protected function initializeAction() {
		parent::initializeAction();					
		$this->extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);		
		$this->templateRepository = $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Repository\\TemplateRepository');			
	}
	
	/**
	 * action index
	 *
	 * @return void
	 */
	public function indexAction() {			
		
		$this->view->assign('templates', $this->templateRepository->findAll(false));
		$this->view->assign('action', 'show');
	}
	
	/**
	 * action add
	 *	
	 * @param \array $add
	 * @return void
	 */
	public function addAction($add = array()) {			
		
		if(isset($add['save']) || isset($add['saveAndClose']) ||  isset($add['saveAndNew'])){
						
			$object = $this->objectManager->get('TYPO3\\MooxFeusers\\Domain\\Model\\Template');
			$object->setTitle($add['title']);
			$object->setSubject($add['subject']);
			$object->setCategory($add['category']);
			$object->setTemplate($add['template']);
			
			$this->templateRepository->add($object);								
			$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
			
			$this->flashMessageContainer->add(
				'', 
				'Vorlage wurde erfolgreich gespeichert.', 
				\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
		}
		if(isset($add['save'])){
			$this->redirect("edit",NULL,NULL,array('uid' => $object->getUid()));
		} elseif(isset($add['saveAndClose'])){
			$this->redirect("index");
		} elseif(isset($add['saveAndNew'])){			
			$this->redirect("add");
		} else {			
			$this->view->assign('object', $add);
			$this->view->assign('categories',$this->getTemplateCategories());			
			$this->view->assign('action', 'add');
		}
	}
	
	/**
	 * action edit
	 *
	 * @param \int $uid
	 * @param \array $edit
	 * @return void
	 */
	public function editAction($uid = 0, $edit = array()) {			
		
		if($uid>0){
		
			$object = $this->templateRepository->findByUid($uid);
			
			if(!count($edit)){
				$edit['title'] 		= $object->getTitle();
				$edit['subject'] 		= $object->getSubject();
				$edit['category'] 	= $object->getCategory();
				$edit['template'] 	= $object->getTemplate();				
				$edit['uid'] 		= $object->getUid();				
			}
						
			if(isset($edit['save']) || isset($edit['saveAndClose']) ||  isset($edit['saveAndNew'])){				
				
				$object->setTitle($edit['title']);
				$object->setSubject($edit['subject']);
				$object->setCategory($edit['category']);
				$object->setTemplate($edit['template']);
				
				$this->templateRepository->update($object);								
				$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
				
				$this->flashMessageContainer->add(
					'', 
					'Änderungen wurden erfolgreich gespeichert.', 
					\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
			}
			if(isset($edit['saveAndClose'])){
				$this->redirect("index");
			} elseif(isset($edit['saveAndNew'])){
				$this->redirect("add");
			} else {
				$this->view->assign('object', $edit);
				$this->view->assign('categories',$this->getTemplateCategories());
				$this->view->assign('action', 'edit');
				$this->view->assign('uid', $uid);
			}
		} else {
			$this->redirect("index");
		}
	}
	
	/**
	 * action delete
	 *	
	 * @param \int $uid
	 * @return void
	 */
	public function deleteAction($uid = 0) {			
		
		if($uid>0){
		
			$object = $this->templateRepository->findByUid($uid);
			
			$this->templateRepository->remove($object);
			
			$this->objectManager->get('TYPO3\CMS\Extbase\Persistence\PersistenceManagerInterface')->persistAll();
			
			$this->flashMessageContainer->add(
					'', 
					'Vorlage wurde gelöscht.', 
					\TYPO3\CMS\Core\Messaging\FlashMessage::OK);
						
		} 
		
		$this->redirect("index");
	}
	
	/**
	 * action preview iframe
	 *
	 * @param \int $uid
	 * @return void
	 */
	public function previewIframeAction($uid = 0) {							
		
		if($uid>0){
		
			$template = $this->templateRepository->findByUid($uid);
			
			$data['gender'] 		= 1;
			$data['title'] 			= "Dr.";
			$data['username'] 		= "hans.mustermann";
			$data['name'] 			= "Hans Mustermann";
			$data['firstName'] 		= "Hans";
			$data['middleName'] 	= "Jürgen";
			$data['lastName'] 		= "Mustermann";
			$data['email'] 			= "email@example.net";
			$data['url'] 			= "http://example.net/recovery.html";
			
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
			
			$previewView = $this->objectManager->create('TYPO3\\CMS\\Fluid\\View\StandaloneView');
			$previewView->setFormat('html');     
			$previewView->setTemplateSource($template->getTemplate());
			if($partialRootPath!=""){
				$previewView->setPartialRootPath($partialRootPath);
			}
			$previewView->assignMultiple($data);
			$preview	 = $previewView->render();
			
			$this->view->assign('preview', $preview);
		} else {
			$this->view->assign('preview', "Vorschau kann nicht angezeigt werden.");
		}
	}
	
	/**
	 * Returns template categories
	 *
	 * @return array
	 */
	public function getTemplateCategories() {
		
		$categories = array();	
		
		$categories['passwordrecovery'] = "Passwort-Wiederherstellungs-Mail";
		
		return $categories;
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