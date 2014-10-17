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
	protected $hidden;
	
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
	
	public function initializeObject() {
		/**
		 * Do not modify this method!
		 * It will be rewritten on each save in the extension builder
		 * You may modify the constructor of this class instead
		 */
		$this->falImages = new \TYPO3\CMS\Extbase\Persistence\ObjectStorage();
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
     * @param \TYPO3\MooxFeusers\Domain\Model\FileReference $falImage
     *
     * @return void
     */
    public function addFalImage(\TYPO3\MooxFeusers\Domain\Model\FileReference $falImage) {
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
	 * Returns the hidden
	 *
	 * @return \integer $hidden
	 */
	public function getHidden() {
		return $this->hidden;
	}

	/**
	 * Sets the hidden
	 *
	 * @param \integer $hidden
	 * @return void
	 */
	public function setHidden($hidden) {
		$this->hidden = $hidden;
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