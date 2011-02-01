<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');
$TCA["tx_x4econgress_congresses"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses',		
		'label'     => 'name',	
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'languageField'            => 'sys_language_uid',	
		'transOrigPointerField'    => 'l18n_parent',	
		'transOrigDiffSourceField' => 'l18n_diffsource',	
		'default_sortby' => "ORDER BY name",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_x4econgress_congresses.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "sys_language_uid, l18n_parent, l18n_diffsource, hidden, name, description, date_from, date_to, files, max_participants",
	)
);

$TCA["tx_x4econgress_participants"] = array (
	"ctrl" => array (
		'title'     => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants',		
		'label'     => 'name',	
		'label_alt' => 'congress_id',
		'label_alt_force' => 1, 
		'tstamp'    => 'tstamp',
		'crdate'    => 'crdate',
		'cruser_id' => 'cruser_id',
		'type' => 'type',	
		'default_sortby' => "ORDER BY crdate",	
		'delete' => 'deleted',	
		'enablecolumns' => array (		
			'disabled' => 'hidden',
		),
		'dividers2tabs'     => true,
		'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
		'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_x4econgress_participants.gif',
	),
	"feInterface" => array (
		"fe_admin_fieldList" => "type, feuser_id, congress_id, name, firstname, address, zip, city, country, poster_title, poster_abstract, poster_detail, poster_images, email, phone, worklocation, phone, worklocation, remarks, uploads",
	)
);

$TCA['tx_x4econgress_categories'] = array (
    'ctrl' => array (
        'title'     => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_categories',        
        'label'     => 'title',    
        'tstamp'    => 'tstamp',
        'crdate'    => 'crdate',
        'cruser_id' => 'cruser_id',
        'languageField'            => 'sys_language_uid',    
        'transOrigPointerField'    => 'l10n_parent',    
        'transOrigDiffSourceField' => 'l10n_diffsource',    
        'sortby' => 'sorting',    
        'delete' => 'deleted',    
        'enablecolumns' => array (        
            'disabled' => 'hidden',
        ),
        'dynamicConfigFile' => t3lib_extMgm::extPath($_EXTKEY).'tca.php',
        'iconfile'          => t3lib_extMgm::extRelPath($_EXTKEY).'icon_tx_x4econgress_categories.gif',
    ),
);



t3lib_div::loadTCA('tt_content');
$TCA['tt_content']['types']['list']['subtypes_excludelist'][$_EXTKEY.'_pi1']='layout,select_key,pages';


require_once(t3lib_extMgm::extPath($_EXTKEY).'pi1/class.x4econgress_listcategories.php');


t3lib_extMgm::addPlugin(array('LLL:EXT:x4econgress/locallang_db.xml:tt_content.list_type_pi1', $_EXTKEY.'_pi1'),'list_type');
t3lib_extMgm::addPlugin(array('LLL:EXT:x4econgress/locallang_db.xml:tt_content.list_type_pi2', $_EXTKEY.'_pi1'),'list_type');



t3lib_extMgm::addStaticFile($_EXTKEY,"pi1/static/","Congress database");

t3lib_extMgm::addStaticFile($_EXTKEY,'static/4eyes_Congress_database/', '4eyes Congress database');


$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi1']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi1', 'FILE:EXT:'.$_EXTKEY.'/pi1/flexform_ds.xml');

$TCA['tt_content']['types']['list']['subtypes_addlist'][$_EXTKEY.'_pi2']='pi_flexform';
t3lib_extMgm::addPiFlexFormValue($_EXTKEY.'_pi2', 'FILE:EXT:'.$_EXTKEY.'/pi2/flexform_ds.xml');


if (TYPO3_MODE=="BE")    {
	$TBE_MODULES_EXT[ 'xMOD_db_new_content_el' ][ 'addElClasses' ][ 'tx_x4econgress_pi1_wizicon' ] = t3lib_extMgm::extPath( $_EXTKEY ).'pi1/class.tx_x4econgress_pi1_wizicon.php';
	$TBE_MODULES_EXT[ 'xMOD_db_new_content_el' ][ 'addElClasses' ][ 'tx_x4econgress_pi2_wizicon' ] = t3lib_extMgm::extPath( $_EXTKEY ).'pi2/class.tx_x4econgress_pi2_wizicon.php';
    t3lib_extMgm::addModulePath('txx4econgressM1',t3lib_extMgm::extPath($_EXTKEY).'mod1/'); 
    t3lib_extMgm::addModule("txx4econgressM1","","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
    t3lib_extMgm::addModule("txx4econgressM1","txx4econgressM1","",t3lib_extMgm::extPath($_EXTKEY)."mod1/");
    
}
?>