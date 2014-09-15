<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2011 Georg Ringer <typo3@ringerge.org>
*  All rights reserved
*
*  This script is part of the TYPO3 project. The TYPO3 project is
*  free software; you can redistribute it and/or modify
*  it under the terms of the GNU General Public License as published by
*  the Free Software Foundation; either version 2 of the License, or
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
 * Hook to display verbose information about pi1 plugin in Web>Page module
 *
 * @package TYPO3
 * @subpackage tx_mooxfeusers
 */
class TxMooxFeusersPageLayoutViewDrawItem implements \TYPO3\CMS\Backend\View\PageLayoutViewDrawItemHookInterface {
	
	/**
	 * Path to the locallang file
	 * @var string
	 */
	const LLPATH = 'LLL:EXT:moox_feusers/Resources/Private/Language/locallang_be.xml:';
	
	
	/**
	 * Preprocesses the preview rendering of a content element.
	 *
	 * @param	\TYPO3\CMS\Backend\View\PageLayoutView	$parentObject:  Calling parent object
	 * @param	\boolean         						$drawItem:      Whether to draw the item using the default functionalities
	 * @param	\string	        						$headerContent: Header content
	 * @param	\string	        						$itemContent:   Item content
	 * @param	\array									$row:           Record row of tt_content
	 * @return	\void
	 */
	public function preProcess(\TYPO3\CMS\Backend\View\PageLayoutView &$parentObject, &$drawItem, &$headerContent, &$itemContent, array &$row) {
		switch($row['list_type']) {
			case 'mooxfeusers_pi1':
				$drawItem = FALSE;
				$headerContent = $headerContent.'<strong>'.$GLOBALS['LANG']->sL(self::LLPATH . 'pi1_title').'</strong><br />';
				
				$flexformData = t3lib_div::xml2array($row['pi_flexform']);

				// if flexform data is found
				$actions = $this->getFieldFromFlexform($flexformData, 'switchableControllerActions');
				if (!empty($actions)) {
					$actionList = t3lib_div::trimExplode(';', $actions);

					// translate the first action into its translation
					$actionTranslationKey = strtolower(str_replace('->', '_', $actionList[0]));
					$actionTranslation = $GLOBALS['LANG']->sL(self::LLPATH . 'pi1_controller_selection.' . $actionTranslationKey);

					$mode = $actionTranslation;

				} else {
					$mode = $GLOBALS['LANG']->sL(self::LLPATH . 'pi1_controller_selection.not_configured');
				}

				if (is_array($flexformData)) {

					switch ($actionTranslationKey) {
						case 'frontenduser_login':						
							break;
						case 'frontenduser_register':						
							break;
						case 'profile_edit':						
							break;					
						default:
					}
				}
				
				$itemContent = $GLOBALS['LANG']->sL(self::LLPATH . 'pi1_preview_mode').": ".$mode;
			break;
		}
	}
	
	/**
	 * Get field value from flexform configuration,
	 * including checks if flexform configuration is available
	 *
	 * @param array $flexform flexform configuration
	 * @param string $key name of the key
	 * @param string $sheet name of the sheet
	 * @return NULL if nothing found, value if found
	 */
	protected function getFieldFromFlexform($flexform, $key, $sheet = 'sDEF') {
		if (is_array($flexform) && isset($flexform['data'])) {
			$flexform = $flexform['data'];
			if (is_array($flexform) && is_array($flexform[$sheet]) && is_array($flexform[$sheet]['lDEF'])
					&& is_array($flexform[$sheet]['lDEF'][$key]) && isset($flexform[$sheet]['lDEF'][$key]['vDEF'])
			) {
				return $flexform[$sheet]['lDEF'][$key]['vDEF'];
			}
		}

		return NULL;
	}
}
?>