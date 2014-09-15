<?php
namespace TYPO3\MooxFeusers\Domain\Model;

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
class FrontendUser extends \TYPO3\CMS\Extbase\Domain\Model\FrontendUser  {
		
	/**
	 * is company admin
	 *
	 * @var boolean
	 */
    protected $isCompanyAdmin = FALSE;
	
	/**
	 * is moox feuser
	 *
	 * @var boolean
	 */
    protected $isMooxFeuser = FALSE;
 
    /**
     *
     * @return boolean $isCompanyAdmin
     */
    public function getIsCompanyAdmin() {
       return $this->isCompanyAdmin;
    }
     
    /**
     *
     * @param boolean $isCompanyAdmin
     */
    public function setIsCompanyAdmin($isCompanyAdmin) {
        $this->isCompanyAdmin = $isCompanyAdmin;
    }
	
	/**
     *
     * @return boolean $isMooxFeuser
     */
    public function getIsMooxFeuser() {
       return $this->isMooxFeuser;
    }
     
    /**
     *
     * @param boolean $isMooxFeuser
     */
    public function setIsMooxFeuser($isMooxFeuser) {
        $this->isMooxFeuser = $isMooxFeuser;
    }
	
	/**
	 * Setter for password. As we have encrypted passwords here, we need to encrypt them before storing!
	 *
	 * @param $password
	 */
	public function setPassword($password) {
		if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
			$saltedpasswordsInstance = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance();
			$encryptedPassword = $saltedpasswordsInstance->getHashedPassword($password);
			$this->password = $encryptedPassword;
		} else {
			parent::setPassword($password);
		}
	}
}
?>