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
class Template extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity {
	
	/**
	 * Titel
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $title;
	
	/**
	 * Mail-Betreff
	 *
	 * @var \string
	 * @validate NotEmpty
	 */
	protected $subject;
	
	/**
	 * Kategorie
	 *
	 * @var \string
	 */
	protected $category;
	
	/**
	 * Vorlage
	 *
	 * @var \string
	 */
	protected $template;	
	
	/**
	 * Returns the title
	 *
	 * @return \string $title
	 */
	public function getTitle() {
		return $this->title;
	}

	/**
	 * Sets the title
	 *
	 * @param \string $title
	 * @return void
	 */
	public function setTitle($title) {
		$this->title = $title;
	}
	
	/**
	 * Returns the subject
	 *
	 * @return \string $subject
	 */
	public function getSubject() {
		return $this->subject;
	}

	/**
	 * Sets the subject
	 *
	 * @param \string $subject
	 * @return void
	 */
	public function setSubject($subject) {
		$this->subject = $subject;
	}
	
	/**
	 * Returns the category
	 *
	 * @return \string $category
	 */
	public function getCategory() {
		return $this->category;
	}

	/**
	 * Sets the category
	 *
	 * @param \string $category
	 * @return void
	 */
	public function setCategory($category) {
		$this->category = $category;
	}
	
	/**
	 * Returns the template
	 *
	 * @return \string $template
	 */
	public function getTemplate() {
		return $this->template;
	}

	/**
	 * Sets the template
	 *
	 * @param \string $template
	 * @return void
	 */
	public function setTemplate($template) {
		$this->template = $template;
	}
}
?>