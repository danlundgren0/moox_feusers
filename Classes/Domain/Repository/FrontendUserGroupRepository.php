<?php
namespace DCNGmbH\MooxFeusers\Domain\Repository;

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
class FrontendUserGroupRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserGroupRepository {
	
	protected $defaultOrderings = array ('title' => \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING);
	
	/**
	 * Find user groups by pid(list)
	 *
	 * @param array $pids pids	
	 * @return Tx_Extbase_Persistence_QueryInterface
	 */
	public function findByPids($pids = array()) {
		
		$pids 	= (is_array($pids))?$pids:array($pids);		
		
		$query = $this->createQuery();
		
		if(count($pids)){
			$query->getQuerySettings()->setStoragePageIds($pids);
		} else {
			$query->getQuerySettings()->setRespectStoragePage(FALSE);
		}
		
		return $query->execute();
	}
	
	/**
	 * Find user groups by uid(list)
	 *
	 * @param array $uids uids	
	 * @return Tx_Extbase_Persistence_QueryInterface
	 */
	public function findByIdList($uids = array()) {
		
		$uids 	= (is_array($uids))?$uids:array($uids);		
		
		$query = $this->createQuery();
				
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		
		$query->matching(
			$query->in('uid', $uids)        
        );
		
		return $query->execute();
	}
	
	/**
	 * Override default findByUid function to enable also the option to turn of
	 * the enableField setting
	 *
	 * @param integer $uid id of record
	 * @param boolean $respectEnableFields if set to false, hidden records are shown
	 * @return \DCNGmbH\MooxFeusers\Domain\Model\FrontendUserGroup
	 */
	public function findByUid($uid, $respectEnableFields = TRUE) {
		$query = $this->createQuery();
		$query->getQuerySettings()->setRespectStoragePage(FALSE);
		$query->getQuerySettings()->setRespectSysLanguage(FALSE);
		$query->getQuerySettings()->setIgnoreEnableFields(!$respectEnableFields);

		return $query->matching(
			$query->logicalAnd(
				$query->equals('uid', $uid),
				$query->equals('deleted', 0)
			))->execute()->getFirst();
	}
}
?>