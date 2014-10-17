<?php
namespace TYPO3\MooxFeusers\Domain\Repository;

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
class FrontendUserRepository extends \TYPO3\CMS\Extbase\Domain\Repository\FrontendUserRepository {
	
	/**
	 * Finds all frontend users (overwrite)
	 *	
	 * @param array $storagePids
	 * @param array $filter
	 * @return Tx_Extbase_Persistence_QueryResultInterface The frontend users
	 */
	public function findAll($storagePids = array(), $filter = array()) {
		
		$query = $this->createQuery();
		
		if(is_numeric($storagePids) && $storagePids>0){
			$storagePids = array($storagePids);
		}
		
		if(!is_array($storagePids) || count($storagePids)<1){
			$storagePids = array(0);			
		}
		
		//$query->getQuerySettings()->setRespectStoragePage(true);
		$query->getQuerySettings()->setIgnoreEnableFields(TRUE);
		$query->getQuerySettings()->setStoragePageIds($storagePids);
		
		$constraints = array();
				
		$constraints[] = $query->equals('deleted', 0);
		
		if($filter['mailing']==1){
			$constraints[] = $query->equals('disallow_mailing', 0);
		} elseif($filter['mailing']==2){
			$constraints[] = $query->equals('disallow_mailing', 1);
		}
		if($filter['admin']==1){
			$constraints[] = $query->equals('is_company_admin', 1);
		} elseif($filter['admin']==2){
			$constraints[] = $query->equals('is_company_admin', 0);
		}
		if($filter['state']==1){
			$constraints[] = $query->equals('hidden', 0);
		} elseif($filter['state']==2){
			$constraints[] = $query->equals('hidden', 1);
		}
		$filter['query'] = trim($filter['query']);
		if($filter['query']!=""){
			$constraints[] = $query->logicalOr(	
								$query->like('username', "%".$filter['query']."%"),
								$query->like('name', "%".$filter['query']."%"),
								$query->like('first_name', "%".$filter['query']."%"),
								$query->like('middle_name', "%".$filter['query']."%"),
								$query->like('last_name', "%".$filter['query']."%"),
								$query->like('address', "%".$filter['query']."%"),								
								$query->like('telephone', "%".$filter['query']."%"),
								$query->like('fax', "%".$filter['query']."%"),
								$query->like('email', "%".$filter['query']."%"),
								$query->like('title', "%".$filter['query']."%"),
								$query->like('zip', "%".$filter['query']."%"),
								$query->like('city', "%".$filter['query']."%"),
								$query->like('country', "%".$filter['query']."%"),
								$query->like('www', "%".$filter['query']."%"),
								$query->like('company', "%".$filter['query']."%")
							);
		}
		
		if($filter['sortDirection']=="DESC"){
			$filter['sortDirection'] = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_DESCENDING;
		} else {
			$filter['sortDirection'] = \TYPO3\CMS\Extbase\Persistence\QueryInterface::ORDER_ASCENDING;
		}
		
		if($filter['sortField']=="address"){
			$query->setOrderings (Array('zip' => $filter['sortDirection'], 'city' => $filter['sortDirection'], 'address' => $filter['sortDirection']));
		} else {
			$sortFields = explode("|",$filter['sortField']);
			if(count($sortFields)>1){
				$sortOrderings = array();
				foreach($sortFields AS $sortField){
					if($sortField=="address"){
						$sortOrderings = array_merge($sortOrderings,Array('zip' => $filter['sortDirection'], 'city' => $filter['sortDirection'], 'address' => $filter['sortDirection']));
					} else {
						$sortOrderings = array_merge($sortOrderings,Array($sortField => $filter['sortDirection']));
					}
				}
				$query->setOrderings ($sortOrderings);
			} else {
				$query->setOrderings (Array($filter['sortField'] => $filter['sortDirection']));
			}
		}
				
		return $query->matching(
			$query->logicalAnd($constraints))->execute();
	}
	
	/**
	 * Finds all company users
	 *	
	 * @param string $company The company to get users from
	 * @return Tx_Extbase_Persistence_QueryResultInterface The users
	 */
	public function findByCompany($uid = 0, $company = '') {
		
		if($uid>0 && $company!=""){
		
			$query = $this->createQuery();
					
			$query->matching(
				$query->logicalAnd(
					$query->equals('company', $company),
					$query->logicalNot(
						$query->equals('uid', $uid)
					)					
				)					
			);						
			
			return $query->execute();
			
		} else {
			return NULL;
		}
	}
	/**
	 * Finds user by username
	 *	
	 * @param string $company The company to get users from
	 * @return Tx_Extbase_Persistence_QueryResultInterface The users
	 */
	public function findByUsername($username = '') {
		
		if($username!=""){
		
			$query = $this->createQuery();
					
			$query->matching(
				$query->equals('username', $username)			
			);						
			
			return $query->execute();
			
		} else {
			return NULL;
		}
	}
}
?>