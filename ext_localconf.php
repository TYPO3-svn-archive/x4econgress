<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

  ## Extending TypoScript from static template uid=43 to set up userdefined tag:
t3lib_extMgm::addTypoScript($_EXTKEY,'editorcfg','
	tt_content.CSS_editor.ch.tx_x4econgress_pi1 = < plugin.tx_x4econgress_pi1.CSS_editor
',43);

t3lib_extMgm::addUserTSConfig('
    options.saveDocNew.tx_x4econgress_categories=1
');

t3lib_extMgm::addPItoST43($_EXTKEY,'pi1/class.tx_x4econgress_pi1.php','_pi1','list_type',1);
t3lib_extMgm::addPItoST43($_EXTKEY,'pi2/class.tx_x4econgress_pi2.php','_pi2','list_type',1);

// eID stuff
$TYPO3_CONF_VARS['FE']['eID_include']['congressSort'] = 'EXT:x4econgress/scripts/recordSorting.php';
$TYPO3_CONF_VARS['FE']['eID_include']['congressCaptcha'] = 'EXT:x4econgress/scripts/congressCaptcha.php';


// userFunc Condition
function user_congressGroup($gUid){
	if($_GET['tx_x4econgress_pi1']['showUid']){
		$records = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*','tx_x4econgress_congresses_categories_mm','uid_local = '.$_GET['tx_x4econgress_pi1']['showUid']);
		while($rec = mysql_fetch_assoc($records)){
			$groups[] = $rec['uid_foreign'];
		}
		
		if(in_array($gUid, $groups)){
			return true;
		} else {
			return false;
		}
	} else {
		return false;
	}
}

?>