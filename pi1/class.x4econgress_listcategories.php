<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2003-2004 Kasper Sk�rh�j (kasper@typo3.com)
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
 * Class/Function which manipulates the item-array for the FEusers listing
 *
 * @author	Kasper Sk�rh�j <kasper@typo3.com>
 */


/**
 * SELECT box processing
 *
 * @author	Kasper Sk�rh�j (kasper@typo3.com)
 * @package TYPO3
 * @subpackage tx_newloginbox
 */
class tx_x4econgress_listcategories {

	/**
	 * Adding field list to selector box array
	 *
	 * @param	array		Parameters, changing "items". Passed by reference.
	 * @param	object		Parent object
	 * @return	void
	 */
	function listCats(&$params,&$pObj)	{
		global $TCA;
		
		/* $table = $params['config']['params']['table'];
		if ($table == '') {
			$table = tx_jkpoll_flexform::getSelectedTable($params,$pObj);
		} */
		
		$actPage = $params['row']['pid'];
		
		$pTS = t3lib_BEfunc::getPagesTSConfig($actPage);
		$pTSres = $pTS['plugin.']['tx_x4econgress_pi1.'];
		
		$sF = $pTSres['pollPidList'];
		
		if(!empty($params['row']['pi_flexform'])){
			$tmpFFvars = t3lib_div::xml2array($params['row']['pi_flexform']);
		
			$tmpPidList = explode('|',$tmpFFvars['data']['sDEF']['lDEF']['pidList']['vDEF']);
			$tmpPidList = array_reverse(explode('_',$tmpPidList[0]));
			$pidList = $tmpPidList[0];
		
			$tmpCPid = explode('|',$tmpFFvars['data']['sDEF']['lDEF']['categoryPidList']['vDEF']);
			$tmpcPid = array_reverse(explode('_',$tmpCPid[0]));
			$catPid = $tmpcPid[0];
		}
		
		
		
		
		if(!empty($catPid)){
			$sF = $catPid;
		} else if(!empty($pidList)) {
			$sF = $pidList;
		} else {
			$sF = 0;
		}
		
		$polls = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_x4econgress_categories','1 AND pid = '.intval($sF).t3lib_BEfunc::BEenableFields('tx_x4econgress_categories').' AND tx_x4econgress_categories.deleted = 0');
		$params['items'] = array();
		$params['items'][] = array('',0);
		
		if(count($polls) > 0){
			
			foreach($polls as $poll){
				if($poll['title'] != ''){
					$params['items'][] = array($poll['title'],$poll['uid']);
				}
			}
		}
	}
	
	/**
	 *
	 *
	 */
	function getSelectedTable(&$params,&$pObj) {
		$xml = t3lib_div::xml2array($params['row']['pi_flexform']);
		return $xml['data']['sDEF']['lDEF']['tableName']['vDEF'];
	}

	
}


?>