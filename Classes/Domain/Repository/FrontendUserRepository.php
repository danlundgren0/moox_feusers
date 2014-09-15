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