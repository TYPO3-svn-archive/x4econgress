<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2008 Markus Stauffiger (4eyes GmbH) <markus@4eyes.ch>
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

require_once('typo3conf/ext/x4epibase/class.x4epibase.php');


/**
 * Plugin 'Congress database' for the 'x4econgress' extension.
 *
 * @author	Markus Stauffiger (4eyes GmbH) <markus@4eyes.ch>
 * @package	TYPO3
 * @subpackage	tx_x4econgress
 */
 
 
class tx_x4econgress_pi2 extends x4epibase {
	var $prefixId      = 'tx_x4econgress_pi2';		// Same as class name
	var $scriptRelPath = 'pi2/class.tx_x4econgress_pi2.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'x4econgress';	// The extension key.
	var $pi_checkCHash = true;
	var $tableName = 'tx_x4econgress_congresses';
	var $participantsTable = 'tx_x4econgress_participants';
	var $feuserMMtable = 'tx_x4econgress_congresses_feusers_mm';
	var $droppedLastNodeLevel;
	var $dropNodeLevel;
	var $parents = array();
	
	
	function main($content,$conf){
	
		$this->init($content,$conf);
		$content = $this->getView();
		if($_GET['eID']){
			return $content;
		} else {
			return $this->pi_wrapInBaseClass($content);
		}
	}
	
	function getView(){
		
		if($_GET['eID'] && $_GET['eID'] == 'congressSort'){
			$GLOBALS['TSFE']->set_no_cache();
			if($_GET['action']){
				switch($_GET['action']){
					case 'sort':
						return $this->sortRecordsFromExtTree($_GET['parentUid'],$_GET['childUid']);
						break;
				}
			}
			if($_GET['type']){
				return $this->createRecordJSonArray($_GET['type']);
			} else {
				return $this->createRecordJSonArray();
			}
		} else {
			return $this->singleView();
		}
		
	}
	
	function singleView(){
	
		// include JavaScript File to render the trees
		if(!empty($this->conf['extTreeScript'])){
			if(!empty($this->conf['useAlternExtJSPath'])){
				$extPath = $this->conf['useAlternExtJSPath'];
			} else {
				$extPath = 'typo3conf/ext/x4efeedit/res/extjs/';
			}
			
			
			//add JavaScript files and pidList into a JS-var
			$GLOBALS['TSFE']->additionalHeaderData['90'] = '<script type="text/javascript">actSysFolder = '.$this->getTSFFvar('pidList').';</script>';
			$GLOBALS['TSFE']->additionalHeaderData['100'] = '<script type="text/javascript" src="'.$extPath.'adapter/ext/ext-base.js"></script>';
			$GLOBALS['TSFE']->additionalHeaderData['200'] = '<script type="text/javascript" src="'.$extPath.'ext-all-debug.js"></script>';
			$GLOBALS['TSFE']->additionalHeaderData['300'] = '<script type="text/javascript" src="'.$this->conf['extTreeScript'].'"></script>';
			$GLOBALS['TSFE']->additionalHeaderData['400'] = '<link rel="stylesheet" type="text/css" href="'.$extPath.'resources/css/ext-all.css" media="all" />';
			
		}
		
		return parent::singleView();

	}

	
	/**
	 *	Creates an array of nodes to show in the tree dependent on the fe-user
	 *
	 *	@param	string	$type	What kind of list should be returned? (all/proposals/assigned)
	 *
	 *	@return JSon-Object JSon-encoded object containing all nodes
	 */
	function createRecordJSonArray($type = 'all'){
		if($GLOBALS['TSFE']->fe_user->user){
			$this->fe_user = $GLOBALS['TSFE']->fe_user->user['uid']; // FE-User id
		
			// get records with actual fe-user permission
			$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->tableName,$this->tableName.'.pid IN ('.$_GET['pid'].') AND '.$this->tableName.'.uid IN ('.$GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$this->feuserMMtable,$this->feuserMMtable.'.uid_foreign IN ('.$this->fe_user.')').')');
			if(count($res) <= 0){
				return json_encode(false);
			}

			$nodes = $this->makeNodeArray($type,0,0,$res[0]['uid']);
		
			return json_encode($nodes);
		} else {
			return json_encode(false);
		}
	}
	
	
	/**
	 *	returns a ExtJS.tree.node
	 *
	 *	@param	int	$id	record Uid
	 *	@param	string	$text	Node text
	 *	@param	array	$children	Array of child nodes
	 *	@param	bool	$drop	If true, elements can be dropped on this node
	 *	@param	bool	$drag	If true, this element can be dragged
	 *	@param	bool	$leaf	If true, node is a leaf (last element)
	 *	@param	bool	$exp	If true, node is expanded on creation
	 *
	 *	@return array Array with elements to create a tree node
	 */
	 
	function addNodeToArray($id,$text,$children=array(),$drop=false,$drag=false,$leaf=false,$exp=false){
		return array(
			'id' => $id,
			'text' => $text,
			'children' => $children,
			'allowDrop' => $drop,
			'allowDrag' => $drag,
			'expanded' => $exp,
		);
	}
	
	
	/**
	 *	Creates a congress node and checks if there are any children from both kinds congress and subscription records
	 *
	 *	@param	string	$type	What kind of list should be returned? (all/proposals/assigned)
	 *	@param	int	$parentID	Uid of parental congress record
	 *	@param	int	$lvl	Level of nodes
	 *	@param	int	$uid	Uid of a record
	 *
	 *	@return	array	Array of node properties
	 */
	
	function makeNodeArray($type = 'all', $parentID=0, $lvl = 0, $uid=0) {
		$nodeArr = array();
		$pFl = 0;

		$PID = $_GET['pid'];
		
		if($uid == 0){
			$addWhere = ' AND '.$this->tableName.'.rparent = '.$parentID;
		} else {
			$addWhere = ' AND '.$this->tableName.'.uid = '.$uid;
		}
		
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,name',$this->tableName,$this->tableName.'.pid IN ('.$PID.')'.$this->cObj->enableFields($this->tableName).$addWhere,'',$this->conf['orderByList']);
			
		while($node = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {
		
			// fetch children congress-records
			$children = $this->makeNodeArray($type,$node['uid'],$lvl+1);
				
			// fetch children proposals
			$proposals = $this->proposalNodeArray($type,$node['uid']);
					
			switch($type){
				case 'assigned':
					// if node has children of congress-type don't show proposals
					if(count($children) > 0){
						$proposals = array();
					}
				break;
				case 'proposals':
					// set flag for proposalMode
					$pFl = 1;
				break;
			}
			
			
			// if congress AND proposals as children
			if(count($children) > 0 && count($proposals) > 0){
				$children = array_merge($children,$proposals);
			}
			
			if(count($children) > 0){
				// node has children of any kind but for sure congress
				$nodeArr[] = $this->addNodeToArray($node['uid'],$node['name'],$children);
			} else {
				if( ($pFl == 1 && $this->droppedLastNodeLevel != true) || ($pFl == 1 && $this->dropNodeLevel == $lvl)){
					$dropped = true;
				} else {
					// node has no children -> empty node -> append proposals if any
					if(count($proposals) > 0){
						// session has proposals
						$nodeArr[] = $this->addNodeToArray($node['uid'],$node['name'],$proposals,true,true);
					} else {
						// session has no proposals
						$nodeArr[] = $this->addNodeToArray($node['uid'],$node['name'],array(),true);
					}
					
				}
			}
		
		}
		
		if($dropped == true){
			$this->droppedLastNodeLevel = true;
			$this->dropNodeLevel = $lvl;
		}
		
		
		if(count($nodeArr) > 0){
			return $nodeArr;
		} else {
			return array();
		}
	}
	
	
	/**
	 *	Creates a subscription node
	 *
	 *	@param	string	$type	What kind of list should be returned? (all/proposals/assigned)
	 *	@param	int	$sessId	Uid of parental congress record
	 *
	 *	@return	array	Array of node properties
	 */
	function proposalNodeArray($type = 'all', $sessId = 0){
		$nodeArr = array();

		$PID = $_GET['pid'];
		$showFieldsInNodeDesc = 'name,firstname,poster_title';
		
		$addWhere = ' AND '.$this->participantsTable.'.type = 1 AND '.$this->participantsTable.'.congress_id = '.intval($sessId);
		
		if($type == 'assigned'){
			$addWhere .= ' AND '.$this->participantsTable.'.congress_id != 0';
		}
				
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,'.$showFieldsInNodeDesc,$this->participantsTable,$this->participantsTable.'.pid IN ('.$PID.')'.$this->cObj->enableFields($this->participantsTable).$addWhere);
		
		if(count($res) < 1 && $sessId == 0){
			return array(array(
				'id' => 0,
				'text' => 'No proposals found !',
				'leaf' => true,
			));
		}
		
		foreach($res as $proposal){
			$nodeArr[] = array(
				'id' => $proposal['uid'],
				'text' => $proposal['firstname'].' '.$proposal['name'].' - '.$proposal['poster_title'],
				'leaf' => true,
			);
		}
		
		if(empty($nodeArr)){
			return array();
		} else {
			return $nodeArr;
		}
	}
	
	
	function sortRecordsFromExtTree($pID,$uID){
		
		if($GLOBALS['TSFE']->fe_user->user){
			$fUser = $GLOBALS['TSFE']->fe_user->user['uid'];
			
			$this->getAllParents($pID);
			
			if(empty($this->parents)){
				return json_encode(false);
			}
			
			array_push($this->parents, $pID);
			
			foreach($this->parents as $pUid){
				// get all feusers of the parent record from mm-table
				$feusers = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid_foreign',$this->feuserMMtable,$this->feuserMMtable.'.uid_local IN ('.$pUid.')');
				foreach($feusers as $recFUser){
					if($recFUser['uid_foreign'] == $fUser){
						// check if fe_user is in charge of ANY parent node
						$allowSafe = 1;
					}
				}
				
				$GLOBALS['TYPO3_DB']->sql_free_result($feusers);
			}
			
			unset($this->parents);
						
			if($allowSafe == 1){
				$upd = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->participantsTable,$this->participantsTable.'.uid IN ('.$uID.')',array('congress_id' => $pID));
				return json_encode(true);
			}
		} else {
			return json_encode(false);
		}
		
	}
	
	
	function getAllParents($pid){
		$res = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,rparent',$this->tableName,$this->tableName.'.uid IN ('.$pid.')'.$this->cObj->enableFields($this->tableName));
		
		if(count($res) > 0){			
			if($res[0]['rparent'] != 0){
				$this->parents[] = $res[0]['rparent'];
				$this->getAllParents($res[0]['rparent']);
			}
		}
	}

	
	
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4econgress/pi1/class.tx_x4econgress_pi2.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4econgress/pi1/class.tx_x4econgress_pi2.php']);
}

?>