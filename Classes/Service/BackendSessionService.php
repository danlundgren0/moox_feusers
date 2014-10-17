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
class BackendSessionService extends \TYPO3\CMS\Extbase\Persistence\Repository {	
	
	/**
     * @var string 
     */
	protected $storageKey = 'tx_mooxfeusers';

	/**
      * @param string $storageKey 
	  */
	public function setStorageKey($storageKey) { 
		$this->storageKey = $storageKey; 
	}

	/**
	  * Wert in der Session speichern
	  * @param string $key
	  * @param mixed $value
	  * @return boolean
	  */
	public function store($key, $value) {
		$data = $GLOBALS['BE_USER']->getSessionData($this->storageKey);
		$data[ $key ] = $value;
		return $GLOBALS['BE_USER']->setAndSaveSessionData($this->storageKey, $data);
	}

	/**
	  * Wert löschen
	  * @param string $key
	  * @return boolean
	  */
	public function delete($key) {
		$data = $GLOBALS['BE_USER']->getSessionData($this->storageKey);
		unset($data[ $key ]);
		return $GLOBALS['BE_USER']->setAndSaveSessionData($this->storageKey, $data);
	}


	/**
	  * Wert auslesen
	  * @param string $key
	  * @return mixed
	  */
	public function get($key) {
		$data = $GLOBALS['BE_USER']->getSessionData($this->storageKey);
		return isset($data[ $key ]) ? $data[ $key ] : NULL;
	}
}
?>