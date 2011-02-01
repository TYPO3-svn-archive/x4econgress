<?php
/***************************************************************
*  Copyright notice
*
*  (c) 2009 Michel Georgy (michel@4eyes.ch)
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
 * Module 'Congress' for the 'x4econgress' extension.
 *
 * @author    Michel Georgy <michel@4eyes.ch>
 */ 



// DEFAULT initialization of a module [BEGIN]
//unset($MCONF);
//require ("conf.php");
//require ($BACK_PATH."init.php");
//require ($BACK_PATH."template.php");
$LANG->includeLLFile("EXT:x4econgress/mod1/locallang.php");
#include ("locallang.php");
require_once (PATH_t3lib."class.t3lib_scbase.php");
$BE_USER->modAccess($MCONF,1);    // This checks permissions and exits if the users has no permission for entry.
    // DEFAULT initialization of a module [END]


class tx_x4econgress_module1 extends t3lib_SCbase {
    var $pageinfo;
	var $tableCongresses = 'tx_x4econgress_congresses';
	var $tableParticipants = 'tx_x4econgress_participants';
	
	var $csvDiv = ';';

    /**
     *
     */
    function init()    {
        global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;

        parent::init();

        /*
        if (t3lib_div::_GP("clear_all_cache"))    {
            $this->include_once[]=PATH_t3lib."class.t3lib_tcemain.php";
        }
        */
    }

    /**
     * Adds items to the ->MOD_MENU array. Used for the function menu selector.
     */
    function menuConfig()    {
        global $LANG;
        $this->MOD_MENU = Array (
            "function" => Array (
                "1" => $LANG->getLL("overview"),
                "2" => $LANG->getLL("new"),
                "3" => $LANG->getLL("function3"),
            )
        );
        parent::menuConfig();
    }

        // If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
    /**
     * Main function of the module. Write the content to $this->content
     */
    function main()    {
        global $BE_USER,$LANG,$BACK_PATH,$TCA_DESCR,$TCA,$CLIENT,$TYPO3_CONF_VARS;
        $config = $BE_USER->getTSConfig('x4econgress');
		$this->id = $config['properties']['pidList'];
		$this->showFields = $config['properties']['showFields'];
		
		if(empty($this->showFields)){
			$this->showFields = 'name,firstname,type,address,zip,city,country,email,remarks';
		}
		
        // Access check!
        // The page will show only if there is a valid page and if this page may be viewed by the user
        $this->pageinfo = t3lib_BEfunc::readPageAccess($this->id,$this->perms_clause);
        $access = is_array($this->pageinfo) ? 1 : 0;
		
        if (($this->id && $access) || ($BE_USER->user["admin"] && !$this->id))    {
		
                // Draw the header.
            $this->doc = t3lib_div::makeInstance("bigDoc");
            $this->doc->backPath = $BACK_PATH;
            //$this->doc->form='<form action="" method="POST">';
            $backUrl = 'http://'.$_SERVER['HTTP_HOST'].'/';
			$backUrl .= 'typo3/mod.php?M=txx4econgressM1_txx4econgressM1';
			if (isset($_POST['x4econgress_congresses'])) {
				$backUrl .= '&x4econgress_congresses='.$_POST['x4econgress_congresses'];
			}
                // JavaScript
            $this->doc->JScode = '
                <script language="javascript" type="text/javascript">
                    script_ended = 0;
                    function jumpToUrl(URL)    {
                        document.location = URL;
                    }
					function jump(url,modName,mainModName)	{
					// Clear information about which entry in nav. tree that might have been highlighted.
					top.fsMod.navFrameHighlightedID = new Array();
					
					if (top.content && top.content.nav_frame && top.content.nav_frame.refresh_nav)	{
						top.content.nav_frame.refresh_nav();
					}

					top.nextLoadModuleUrl = url;
					top.goToModule(modName);
					}
					var T3_THIS_LOCATION = top.getModuleUrl(top.TS.PATH_typo3+"../typo3conf/ext/x4econgress/mod1/index.php?");
					T3_THIS_LOCATION = "'.urlencode($backUrl).'";
                </script>
            ';
            $this->doc->postCode='
                <script language="javascript" type="text/javascript">
                    script_ended = 1;
                    if (top.fsMod) top.fsMod.recentIds["web"] = '.intval($this->id).';
                </script>
            ';

            $headerSection = $this->doc->getHeader("pages",$this->pageinfo,$this->pageinfo["_thePath"])."<br>".$LANG->sL("LLL:EXT:lang/locallang_core.php:labels.path").": ".t3lib_div::fixed_lgd_pre($this->pageinfo["_thePath"],50);

            $this->content.=$this->doc->startPage($LANG->getLL("title"));
            $this->content.=$this->doc->header($LANG->getLL("title"));
            $this->content.=$this->doc->divider(5);

            // Render content:
            $this->moduleContent();


            // ShortCut
            if ($BE_USER->mayMakeShortcut())    {
                $this->content.=$this->doc->spacer(20).$this->doc->section("",$this->doc->makeShortcutIcon("id",implode(",",array_keys($this->MOD_MENU)),$this->MCONF["name"]));
            }

            $this->content.=$this->doc->spacer(10);
        } else {
            // If no access or if ID == zero

            $this->doc = t3lib_div::makeInstance("mediumDoc");
            $this->doc->backPath = $BACK_PATH;

            $this->content.=$this->doc->startPage($LANG->getLL("title"));
            $this->content.=$this->doc->header($LANG->getLL("title"));
            $this->content.=$this->doc->spacer(5);
            $this->content.=$this->doc->spacer(10);
        }
    }

    /**
     * Prints out the module HTML
     */
    function printContent()    {

        $this->content.=$this->doc->endPage();
		if(isset($_REQUEST[export])){
			header("Content-type: application/force-download");
			header("Content-Disposition: filename=participants_".$_REQUEST['cuid'].".csv");

			header("Content-Description: Downloaded File");

			echo $this->csvExport($_REQUEST['cuid']);
		} else {
			echo $this->content;
		}
    }
	
	function csvExport($cuid){
		global $LANG;
		$csvDiv = $this->csvDiv;
		$csv = '';
		$where = 'uid="'.$cuid.'" AND deleted=0';
		$congresses = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('name',$this->tableCongresses,$where,'');
		$congressname = $congresses[0]['name'];
		
		$where = 'congress_id="'.$cuid.'" AND deleted=0';
		$participants = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows($this->showFields,$this->tableParticipants,$where,'name, firstname');
		$csv .= $LANG->getLL("title"). ": " . $csvDiv . $congressname . "\n";
		
		$fields = explode(',',$this->showFields);
		foreach($fields as $field){
			switch($field){
			case 'type':
				$csv .= $LANG->getLL("typetitle").$csvDiv;
				break;
			default:
				$csv .= $LANG->getLL($field).$csvDiv;
			}
		}
		
		$csv .= "\n";

		foreach($participants as $p){
			foreach($fields as $field){
				switch($field){
				case 'type':
					$type = $p['type'];
					$csv .= $LANG->getLL("type[".$type."]") . $csvDiv;
					break;
				default:
					$csv .= utf8_decode($p[$field]).$csvDiv; 
				}
			}
		
			$csv .= "\n";
		}
		return $csv;
	}

    /**
     * Generates the module content
     */
    function moduleContent()    {
    	global $LANG;
        switch((string)$this->MOD_SETTINGS["function"])    {
        	default:
            case 1:
 				$content .= '<p><a href="#" onclick="jumpToUrl(\'\/typo3\/alt_doc.php?returnUrl=\'+T3_THIS_LOCATION+\'&edit[tx_x4econgress_congresses]['.$this->id.']=new\',\'txx4econgressM1\',\'txx4econgressM1\');"><img src="http://histsem.unibas.ch/fileadmin/histsem/_templates/images/icon_new.gif"> '.$LANG->getLL("new").'</a></p>';

                $this->content.=$this->doc->section("",$content,0,1);
				isset($_GET[remSeries]) ? $series = 1 : $series = '';
				$content = $this->showCongresses();
				$this->content.=$this->doc->section($LANG->getLL("show"),$content,0,1);
						
				
				
            break;
        }
    }
	
	
	/**
   * Erstellt einen Link auf die aktuelle Location mit zusätzlichen Parametern
   * @param string $params Zusätzliche Parameter. Müssen mit & beginnen
   * @param int $pid UID der aktuellen Seite
   * @param string $label Label des Links
   */
  function createLink($params, $pid, $label) {
 
    return '<a href="#" onclick="'.htmlspecialchars("window.location.href='index.php?id=".$pid . $params)."'; return false;\">".
       $label .'</a>';
  }
  
  function showCongresses () {
  	global $LANG;
	$csvDiv = $this->csvDiv;
	$fields = explode(',',$this->showFields);
	
  	//$WHERE = 'deleted=0 AND hidden=0 AND date_to > '. time();
	$WHERE = 'pid = '.$this->id.' AND deleted=0 AND hidden=0 AND rparent=0';
  	$congresses = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid,name,description,max_participants,date_from,date_to',$this->tableCongresses,$WHERE,'','name, date_from');
	
	$out =  '</form><form action="" method="post" id="congressSelect" target="_self">';
	$out .= '<label for="x4econgress_congresses">'.$LANG->getLL("choose").' </label> ';
	$out .= '<select name="x4econgress_congresses" onchange="document.getElementById(\'congressSelect\').submit();">';
	$out .= '<option value="-1"></option>';
	
	foreach($congresses as $congress){
		$selected = '';
		if($_REQUEST['x4econgress_congresses'] == $congress['uid']) $selected = "selected";
		$out .= '<option value="'.$congress['uid'].'" '.$selected.'>'.$congress['name'].'</option>';
	}
	$out .= '</select></form>';
	
	//Only if Congress is selected: 
	if(isset($_REQUEST[x4econgress_congresses])){
		$cuid = $_REQUEST[x4econgress_congresses];
		
		foreach($congresses as $c){
			if($c['uid'] == $cuid){
				$out .= '<p>&nbsp;</p><p>'.$LANG->getLL("title").': '.$c['name'].'<br />';
				$out .= '';
				$out .= '</p>';
			}
		}
		
		
			
		
		
		$out .= '<p>&nbsp;</p><form id="exportCsv" method="post" action="" target="_blank">
					<input type="hidden" name="cuid" value="'.$cuid.'" />
  					<input type="submit" name="export" id="export" value="'.$LANG->getLL("exportb").'" /></td></form>';
		//$where = 'congress_id="'.$cuid.'" AND deleted=0';
		//$out .= '<b> : '.$person['firstname']." ".$person['lastname'].'</b>';
	
		$this->content.=$this->doc->spacer(5);
            
		$where = 'congress_id="'.$cuid.'" AND deleted=0';
		$participants = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_x4econgress_participants',$where,'name, firstname');
		$out .= '<p>&nbsp;</p><table style="width: 100%">';
		$out .= '<tr style="font-size: 10px; text-align: left">';
		
		foreach($fields as $field){
			switch($field){
			case 'type':
				$out .= '<th>'.$LANG->getLL("typetitle").'</th>';
				break;
			default:
				$out .= '<th>'.$LANG->getLL($field).'</th>';
			}
		}
		
		$out .= '</tr>';
		//$out .= '<th>'.$LANG->getLL("name").'</th><th>'.$LANG->getLL("firstname").'</th><th>'.$LANG->getLL("typetitle").'</th><th>'.$LANG->getLL("address").'</th><th>'.$LANG->getLL("zip").'</th><th>'.$LANG->getLL("city").'</th><th>'.$LANG->getLL("country").'</th><th>'.$LANG->getLL("email").'</th><th>'.$LANG->getLL("phone").'</th><th>'.$LANG->getLL("worklocation").'</th><th>'.$LANG->getLL("remarks").'</th></tr>';
		$c = 0;
		
		foreach($participants as $p){
			$back = '';
			if ($c++ % 2) $back = 'background-color: #cccccc';
			$out .= '<tr style="'.$back.'">';
			
			foreach($fields as $field){
				switch($field){
				case 'type':
					$type = $p['type'];
					$out .= '<td >'. $LANG->getLL("type[".$type."]") . '</td>';
					break;
				default:
					$out .= '<td >'. $p[$field] . '</td>';
				}
			}
			
			$out .= '</tr>';
		}
		$out .= '</table>';
	}
  	return $out;
  } 
  
  function getTimeS($timestamp){
		//This condition normally should not be used
		if ($timestamp > 60*60*24){
			$timestamp = $timestamp % (60*60*24) + 2*(60*60);
		}
		$hours = intval($timestamp / (60*60));
		$minutes = intval(($timestamp % (60*60)) / 60);
		return sprintf('%02d', $hours).":".sprintf('%02d', $minutes); 
	}
	
	function getUserName($userid){
		if(!is_array($userid)){
			$where = 'uid="'.$userid.'" AND deleted=0 ';
			$user = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->tableNameUsers,$where); $user=$user[0];
			//t3lib_div::debug($user);
			return $user['title']." ".$user['firstname']." ".$user['lastname'];
		}
		
	}
	
	function getWeekDay($tstamp = '', $full = ''){
		global $LANG;
		($full == '') ? $length = "short" : $length = "long";
		($tstamp == '') ? $weekDay = date("w") : $weekDay = date("w",$tstamp);
		if($weekDay >= 0 || $weekDay <= 6){
			//t3lib_div::debug($LANG);
			//t3lib_div::debug($LANG->getLL("day.".$length.".".$weekDay));
	 		return $LANG->getLL("day.".$length.".".$weekDay);
		}else {
			return "wrong timestamp";
		}
	}
}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4econgress/mod1/index.php'])    {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4econgress/mod1/index.php']);
}




// Make instance:
$SOBE = t3lib_div::makeInstance('tx_x4econgress_module1');
$SOBE->init();

// Include files?
foreach($SOBE->include_once as $INC_FILE)    include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>