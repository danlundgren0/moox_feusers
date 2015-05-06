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
class FileReference extends \TYPO3\CMS\Extbase\Domain\Model\FileReference {
	
	/**
     * @var string
     */
    protected $fieldname = 'falImages';
	
	/**
     * @var string
     */
    protected $tableLocal = 'sys_file';
	
	/**
	 * @var integer
	 */
	protected $cruserId;
		
    /**
     * @var \DCNGmbH\MooxFeusers\Domain\Model\File
     */
    protected $file;
	
	/**
	 * @var string
	 */
	protected $title;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $alternative;

	/**
	 * @var string
	 */
	protected $link;
	
	/**
	 * Get cruser id
	 *
	 * @return string
	 */
	public function getCruserId() {
		return $this->cruserId;
	}

	/**
	 * Set cruser id
	 *
	 * @param string $cruserId cruser id
	 * @return void
	 */
	public function setCruserId($cruserId) {
		$this->cruserId = $cruserId;
	}
	
    /**
     * Set file
     *
     * @param \DCNGmbH\MooxFeusers\Domain\Model\File $file
     */
    public function setFile($file) {
        $this->file = $file;
    }

    /**
     * Get file
     *
     * @return \DCNGmbH\MooxFeusers\Domain\Model\File $file
     */
    public function getFile() {
        return $this->file;
    }
	
	/**
	 * Set alternative
	 *
	 * @param string $alternative
	 * @return void
	 */
	public function setAlternative($alternative) {
		$this->alternative = $alternative;
	}

	/**
	 * Get alternative
	 *
	 * @return string
	 */
	public function getAlternative() {
		return $this->alternative !== NULL ? $this->alternative : $this->getOriginalResource()->getAlternative();
	}

	/**
	 * Set description
	 *
	 * @param string $description
	 * @return void
	 */
	public function setDescription($description) {
		$this->description = $description;
	}

	/**
	 * Get description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->description !== NULL ? $this->description : $this->getOriginalResource()->getDescription();
	}

	/**
	 * Set link
	 *
	 * @param string $link
	 * @return void
	 */
	public function setLink($link) {
		$this->link = $link;
	}

	/**
	 * Get link
	 *
	 * @return mixed
	 * @return void
	 */
	public function getLink() {
		return $this->link !== NULL ? $this->link : $this->getOriginalResource()->getLink();
	}

	/**
	 * Set title
	 *
	 * @param string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}

	/**
	 * Get title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->title !== NULL ? $this->title : $this->getOriginalResource()->getTitle();
	}
}
?>