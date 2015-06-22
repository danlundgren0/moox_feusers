<?php
namespace DCNGmbH\MooxFeusers\Domain\Model;

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
	 * uid
	 *
	 * @var integer
	 */
    protected $uid;
	
	/**
	 * pid
	 *
	 * @var integer
	 */
    protected $pid;
	
	/**
	 * tstamp
	 *
	 * @var integer
	 */
    protected $tstamp;
	
	/**
	 * starttime
	 *
	 * @var integer
	 */
    protected $starttime;
	
	/**
	 * endtime
	 *
	 * @var integer
	 */
    protected $endtime;
	
	/**
	 * crdate
	 *
	 * @var integer
	 */
    protected $crdate;
	
	/**
	 * lastlogin
	 *
	 * @var integer
	 */
    protected $lastlogin;
	
	/**
	 * gender
	 *
	 * @var integer
	 */
    protected $gender;
	
	/**
	 * username
	 *
	 * @var string
	 */
    protected $username;	
	
	/**
	 * disallow mailing
	 *
	 * @var boolean
	 */
    protected $disallowMailing;
	
	/**
	 * fal images to use in the gallery
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\TYPO3\CMS\Extbase\Domain\Model\FileReference>
	 * @lazy
	 */
	protected $falImages;
	
	/**
	 * Sichtbarkeit
	 *
	 * @var \integer	
	 */
	protected $disable;
	
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
	 * password recovery hash
	 *
	 * @var string
	 */
    protected $passwordRecoveryHash;
	
	/**
	 * password recovery timestamp
	 *
	 * @var integer
	 */
    protected $passwordRecoveryTstamp;
	
	/**
	 * sorted usergroup
	 *
	 * @var array
	 */
    protected $sortedUsergroup;
	
	/**
	 * quality
	 *
	 * @var integer
	 */
	protected $quality;
	
	/**
	 * bounces
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DCNGmbH\MooxMailer\Domain\Model\Bounce>
	 */
	protected $bounces = NULL;
	
	/**
	 * errors
	 *
	 * @var \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DCNGmbH\MooxMailer\Domain\Model\Error>
	 */
	protected $errors = NULL;
	  
	/**
	 * initialize object
	 */
	public function initializeObject() {
		//Do not remove the next line: It would break the functionality
		$this->initStorageObjects();
	}
  
	/**
	 * Initializes all ObjectStorage properties
	 * Do not modify this method!
	 * It will be rewritten on each save in the extension builder
	 * You may modify the constructor of this class instead
	 *
	 * @return void
	 */
	protected function initStorageObjects() {
		$this->bounces = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->errors = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
		$this->falImages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
	}
	
	/**
     * get uid
	 *
     * @return integer $uid uid
     */
    public function getUid() {
       return $this->uid;
    }
     
    /**
     * set uid
	 *
     * @param integer $uid uid
	 * @return void
     */
    public function setUid($uid) {
        $this->uid = $uid;
    }
	
	/**
     * get pid
	 *
     * @return integer $pid pid
     */
    public function getPid() {
       return $this->pid;
    }
     
    /**
     * set pid
	 *
     * @param integer $pid pid
	 * @return void
     */
    public function setPid($pid) {
        $this->pid = $pid;
    }
	
	/**
     * get tstamp
	 *
     * @return integer $tstamp tstamp
     */
    public function getTstamp() {
       return $this->tstamp;
    }
     
    /**
     * set tstamp
	 *
     * @param integer $tstamp tstamp
	 * @return void
     */
    public function setTstamp($tstamp) {
        $this->tstamp = $tstamp;
    }
	
	/**
     * get starttime
	 *
     * @return integer $starttime starttime
     */
    public function getStarttime() {
       return $this->starttime;
    }
     
    /**
     * set starttime
	 *
     * @param integer $starttime starttime
	 * @return void
     */
    public function setStarttime($starttime) {
        $this->starttime = $starttime;
    }
	
	/**
     * get endtime
	 *
     * @return integer $endtime endtime
     */
    public function getEndtime() {
       return $this->endtime;
    }
     
    /**
     * set endtime
	 *
     * @param integer $endtime endtime
	 * @return void
     */
    public function setEndtime($endtime) {
        $this->endtime = $endtime;
    }
	
	/**
     * get crdate
	 *
     * @return integer $crdate crdate
     */
    public function getCrdate() {
       return $this->crdate;
    }
     
    /**
     * set crdate
	 *
     * @param integer $crdate crdate
	 * @return void
     */
    public function setCrdate($crdate) {
        $this->crdate = $crdate;
    }
	
	/**
	 * Get year of crdate
	 *
	 * @return integer
	 */
	public function getYearOfCrdate() {
		return $this->getCrdate()->format('Y');
	}

	/**
	 * Get month of crdate
	 *
	 * @return integer
	 */
	public function getMonthOfCrdate() {
		return $this->getCrdate()->format('m');
	}

	/**
	 * Get day of crdate
	 *
	 * @return integer
	 */
	public function getDayOfCrdate() {
		return (int)$this->crdate->format('d');
	}
	
	/**
     * get lastlogin
	 *
     * @return integer $lastlogin lastlogin
     */
    public function getLastlogin() {
       return $this->lastlogin;
    }
     
    /**
     * set lastlogin
	 *
     * @param integer $lastlogin lastlogin
	 * @return void
     */
    public function setLastlogin($lastlogin) {
        $this->lastlogin = $lastlogin;
    }
	
	/**
     * get gender
	 *
     * @return integer $gender gender
     */
    public function getGender() {
       return $this->gender;
    }
     
    /**
     * set gender
	 *
     * @param integer $gender gender
	 * @return void
     */
    public function setGender($gender) {
        $this->gender = $gender;
    }
	
	/**
     * get username
	 *
     * @return string $username username
     */
    public function getUsername() {
       return $this->username;
    }
     
    /**
     * set username
	 *
     * @param string $username username
	 * @return void
     */
    public function setUsername($username) {
        $this->username = $username;
    }
	
	/**
     * get disallow mailing
	 *
     * @return boolean $disallowMailing disallow mailing
     */
    public function getDisallowMailing() {
       return $this->disallowMailing;
    }
     
    /**
     * set disallow mailing
	 *
     * @param boolean $disallowMailing disallow mailing
	 * @return void
     */
    public function setDisallowMailing($disallowMailing) {
        $this->disallowMailing = $disallowMailing;
    }
	
	/**
	 * sets the fal images
	 *
	 * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage $falImages
	 *
	 * @return void
	 */
	public function setFalImages($falImages) {
		$this->falImages = $falImages;
	}
	 
	/**
	 * get the fal images
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage
	 */
	public function getFalImages() {
		return $this->falImages;
	}

	/**
     * adds a fal image
     *
     * @param \DCNGmbH\MooxFeusers\Domain\Model\FileReference $falImage
     *
     * @return void
     */
    public function addFalImage(\DCNGmbH\MooxFeusers\Domain\Model\FileReference $falImage) {
        $this->falImages->attach($falImage);
    }	
	
	/**
     * remove a fal image
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FileReference $falImage
     *
     * @return void
     */
    public function removeFalImage(\TYPO3\CMS\Extbase\Domain\Model\FileReference $falImage) {
        $this->falImages->detach($falImage);
    }
	
	/**
	 * Returns the disable
	 *
	 * @return \integer $disable
	 */
	public function getDisable() {
		return $this->disable;
	}

	/**
	 * Sets the disable
	 *
	 * @param \integer $disable
	 * @return void
	 */
	public function setDisable($disable) {
		$this->disable = $disable;
	}
	
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
	 * @param string $password
	 * @param boolean $plain
	 */
	public function setPassword($password,$plain=FALSE) {
		if($plain){
			$this->password = $password;	
		} else {
			if (\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded('saltedpasswords')) {
				if (\TYPO3\CMS\Saltedpasswords\Utility\SaltedPasswordsUtility::isUsageEnabled('BE')) {
					$objSalt = \TYPO3\CMS\Saltedpasswords\Salt\SaltFactory::getSaltingInstance(NULL);
					if (is_object($objSalt)) {
						$password = $objSalt->getHashedPassword($password);
					}
				}
				$this->password = $password;
			} else {
				parent::setPassword($password);
			}	
		}
	}
	
	/**
     * get password recovery hash
	 *
     * @return integer $passwordRecoveryHash password recovery hash
     */
    public function getPasswordRecoveryHash() {
       return $this->passwordRecoveryHash;
    }
     
    /**
     * set password recovery hash
	 *
     * @param integer $passwordRecoveryHash password recovery hash
	 * @return void
     */
    public function setPasswordRecoveryHash($passwordRecoveryHash) {
        $this->passwordRecoveryHash = $passwordRecoveryHash;
    }
	
	/**
     * get password recovery timestamp
	 *
     * @return integer $passwordRecoveryTstamp password recovery timestamp
     */
    public function getPasswordRecoveryTstamp() {
       return $this->passwordRecoveryTstamp;
    }
     
    /**
     * set password recovery timestamp
	 *
     * @param integer $passwordRecoveryTstamp password recovery timestamp
	 * @return void
     */
    public function setPasswordRecoveryTstamp($passwordRecoveryTstamp) {
        $this->passwordRecoveryTstamp = $passwordRecoveryTstamp;
    }
		
	/**
     * get sorted usergroup
	 *
     * @return array
     */
    public function getSortedUsergroup() {
       $user = $GLOBALS['TYPO3_DB']->exec_SELECTgetSingleRow('usergroup', 'fe_users', 'uid='.$this->uid);
	   if(!is_null($user) && $user['usergroup']!="" && $user['usergroup']!=0){
			$objectManager = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Extbase\Object\ObjectManager');
			$frontendUserGroupRepository = $objectManager->get('DCNGmbH\\MooxFeusers\\Domain\\Repository\\FrontendUserGroupRepository');
			$return = array();
			foreach(explode(",",$user['usergroup']) AS $uid){
				$group = $frontendUserGroupRepository->findByUid($uid,FALSE);
				if(is_object($group)){
					$return[] = $group;
				}
			}
			return $return;
	   } else {
			return array();
	   }	  
    }
	
	/**
	 * get quality
	 *
	 * @return integer $quality quality
	 */
	public function getQuality() {
	   return $this->quality;
	}
	
	/**
	 * Returns the bounces
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DCNGmbH\MooxMailer\Domain\Model\Bounce> $bounces
	 */
	public function getBounces() {
		return $this->bounces;
	}
		
	/**
	 * Returns the errors
	 *
	 * @return \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\DCNGmbH\MooxMailer\Domain\Model\Error> $errors
	 */
	public function getErrors() {
		return $this->errors;
	}
}
?>