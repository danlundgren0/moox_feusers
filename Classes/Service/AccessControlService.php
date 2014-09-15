<?php
namespace TYPO3\MooxFeusers\Service;

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
class AccessControlService implements \TYPO3\CMS\Core\SingletonInterface {
	
	/**
	 * frontendUserRepository
	 *
	 * @var \TYPO3\MooxFeusers\Domain\Repository\FrontendUserRepository
	 * @inject
	 */
	protected $frontendUserRepository;
	
	/**
     * Do we have a logged in feuser
     * @return boolean
     */
    public function hasLoggedInFrontendUser() {		
        return ($GLOBALS['TSFE']->loginUser == 1) ? TRUE : FALSE;
    }
 
    /**
     * Get the uid of the current feuser
     * @return mixed
     */
    public function getFrontendUserUid() {
        if ($this->hasLoggedInFrontendUser() && !empty($GLOBALS['TSFE']->fe_user->user['uid'])) {
            return intval($GLOBALS['TSFE']->fe_user->user['uid']);
        }
        return NULL;
    }
     
    /**
     * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $frontendUser
     * @return boolean
     */
    public function isAccessAllowed(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $frontendUser) {
		return $this->getFrontendUserUid() === $frontendUser->getUid() ? TRUE : FALSE;
    }
	
	/**     
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $adminUser
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $deleteUser
     * @return boolean
     */
    public function isDeleteAllowed(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $adminUser = NULL, \TYPO3\MooxFeusers\Domain\Model\FrontendUser $deleteUser = NULL) {
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);	
		
		if($adminUser && $deleteUser){						
			if($extConf['useCompanyAdmin'] && $this->isAccessAllowed($adminUser) && $adminUser->getIsCompanyAdmin() && $adminUser->getCompany()!="" && $adminUser->getCompany()==$deleteUser->getCompany()){
				return true;
			}
		}
		
		return NULL;
    }
	
	/**     
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $adminUser
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $editUser
     * @return boolean
     */
    public function isEditAllowed(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $adminUser = NULL, \TYPO3\MooxFeusers\Domain\Model\FrontendUser $editUser = NULL) {
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);	
		
		if($adminUser && $editUser){						
			if($extConf['useCompanyAdmin'] && $this->isAccessAllowed($adminUser) && $adminUser->getIsCompanyAdmin() && $adminUser->getCompany()!="" && $adminUser->getCompany()==$editUser->getCompany()){
				return true;
			}
		}
		
		return NULL;
    }
	
	/**     
	 * @param \TYPO3\MooxFeusers\Domain\Model\FrontendUser $adminUser	
     * @return boolean
     */
    public function isCreateAllowed(\TYPO3\MooxFeusers\Domain\Model\FrontendUser $adminUser = NULL) {
		
		// Get the extensions's configuration
		$extConf = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['moox_feusers']);	
		
		if($adminUser){						
			if($extConf['useCompanyAdmin'] && $this->isAccessAllowed($adminUser) && $adminUser->getIsCompanyAdmin() && $adminUser->getCompany()!=""){
				return true;
			}
		}
		
		return NULL;
    }

}
?>