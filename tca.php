<?php
if (!defined ('TYPO3_MODE')) 	die ('Access denied.');

$TCA["tx_x4econgress_congresses"] = array (
	"ctrl" => $TCA["tx_x4econgress_congresses"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "sys_language_uid,l18n_parent,l18n_diffsource,hidden,name,description,categories,persons,date_from,date_to,files,max_participants,format,form,clang,audience,teacher,administration,contact,schedule,location,host,website,intranet"
	),
	"feInterface" => $TCA["tx_x4econgress_congresses"]["feInterface"],
	"columns" => array (
		'sys_language_uid' => array (		
			'exclude' => 0,
			'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
			'config' => array (
				'type'                => 'select',
				'foreign_table'       => 'sys_language',
				'foreign_table_where' => 'ORDER BY sys_language.title',
				'items' => array(
					array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
					array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
				)
			)
		),
		'l18n_parent' => array (		
			'displayCond' => 'FIELD:sys_language_uid:>:0',
			'exclude'     => 0,
			'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
			'config'      => array (
				'type'  => 'select',
				'items' => array (
					array('', 0),
				),
				'foreign_table'       => 'tx_x4econgress_congresses',
				'foreign_table_where' => 'AND tx_x4econgress_congresses.pid=###CURRENT_PID### AND tx_x4econgress_congresses.sys_language_uid IN (-1,0)',
			)
		),
		'l18n_diffsource' => array (		
			'config' => array (
				'type' => 'passthrough'
			)
		),
		'hidden' => array (		
			'exclude' => 0,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"rparent" => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.rparent',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'tx_x4econgress_congresses',    
                'foreign_table_where' => 'AND tx_x4econgress_congresses.pid=###CURRENT_PID### ORDER BY tx_x4econgress_congresses.name',    
                'size' => 5,    
                'minitems' => 0,
                'maxitems' => 1,
            )
        ),
 
		"description" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.description",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"payment_info" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.payment_info",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"speaker_info" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.speaker_info",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"date_from" => Array (		
			"exclude" => 1,
			'l10n_mode' => 'exclude',		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.date_from",		
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"date_to" => Array (		
			"exclude" => 1,	
			'l10n_mode' => 'exclude',	
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.date_to",		
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "datetime",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"registration_deadline" => Array (		
			"exclude" => 0,
			'l10n_mode' => 'exclude',		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.registration_deadline",		
			"config" => Array (
				"type"     => "input",
				"size"     => "12",
				"max"      => "20",
				"eval"     => "date",
				"checkbox" => "0",
				"default"  => "0"
			)
		),
		"course_reg_deadline" => Array (		
			"exclude" => 1,
			'l10n_mode' => 'exclude',		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.course_reg_deadline",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",
				"rows" => "5",
				"wizards" => Array(
					"_PADDING" => 2,
					"RTE" => array(
						"notNewRecords" => 1,
						"RTEonly" => 1,
						"type" => "script",
						"title" => "Full screen Rich Text Editing|Formatteret redigering i hele vinduet",
						"icon" => "wizard_rte2.gif",
						"script" => "wizard_rte.php",
					),
				),
			)
		),
		"files" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.files",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 102400,	
				"uploadfolder" => "uploads/tx_x4econgress",
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		"max_participants" => Array (		
			"exclude" => 1,
			'l10n_mode' => 'exclude',		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.max_participants",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"credits" => Array (		
			"exclude" => 1,
			'l10n_mode' => 'exclude',		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.credits",		
			"config" => Array (
				"type"     => "input",
				"size"     => "4",
				"max"      => "4",
				"eval"     => "int",
				"checkbox" => "0",
				"range"    => Array (
					"upper" => "1000",
					"lower" => "10"
				),
				"default" => 0
			)
		),
		"notification_email" => Array (		
			"exclude" => 0,
			'l10n_mode' => 'exclude',		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.notification_email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
			)
		),
		 'categories' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.categories',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'tx_x4econgress_categories',    
                'foreign_table_where' => 'AND tx_x4econgress_categories.pid=###CURRENT_PID### ORDER BY tx_x4econgress_categories.uid',    
                'size' => 5,    
                'minitems' => 0,
                'maxitems' => 10,    
                "MM" => "tx_x4econgress_congresses_categories_mm",
            )
        ),
         'fe_user' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.fe_user',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'fe_users',    
                'foreign_table_where' => 'AND fe_users.pid=###PAGE_TSCONFIG_ID### ORDER BY fe_users.uid',    
                'size' => 8,    
                'minitems' => 0,
                'maxitems' => 20,    
                "MM" => "tx_x4econgress_congresses_feusers_mm",
            )
        ),
        'persons' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.persons',        
            'config' => array (
                'type' => 'select',    
                'foreign_table' => 'tx_x4epersdb_person',    
                'foreign_table_where' => 'AND tx_x4epersdb_person.pid=###PAGE_TSCONFIG_ID### ORDER BY tx_x4epersdb_person.uid',    
                'size' => 5,    
                'minitems' => 0,
                'maxitems' => 10,    
                "MM" => "tx_x4econgress_congresses_persons_mm",
            )
        ),
        'format' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.format',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
		'courseformats' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.courseformats',
			'config' => array(
				'type' => 'radio',
				'items' => array (
                    array('LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.courseformats.I.0', '1'),
                    array('LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.courseformats.I.1', '2'),
                    array('LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.courseformats.I.2', '0'),
                ),

			)
		),
		'lectureformats' => array(
			'exclude' => 1,
			'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.lectureformats',
			'config' => array(
				'type' => 'radio',
				'items' => array (
                    array('LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.lectureformats.I.0', '1'),
                    array('LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.lectureformats.I.1', '2'),
                    array('LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.lectureformats.I.2', '0'),
                ),

			)
		),
        'form' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.form',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'clang' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.clang',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'audience' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.audience',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'teacher' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.teacher',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'administration' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.administration',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'contact' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.contact',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'schedule' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.schedule',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'location' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.location',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'host' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.host',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'website' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.website',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'intranet' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.intranet',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
         'diverse' => array (        
            'exclude' => 1,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.diverse',        
            'config' => array (
                'type' => 'text',
                'cols' => '30',
                'rows' => '5',
                'wizards' => array(
                    '_PADDING' => 2,
                    'RTE' => array(
                        'notNewRecords' => 1,
                        'RTEonly'       => 1,
                        'type'          => 'script',
                        'title'         => 'Full screen Rich Text Editing|Formatteret redigering i hele vinduet',
                        'icon'          => 'wizard_rte2.gif',
                        'script'        => 'wizard_rte.php',
                    ),
                ),
            )
        ),
        'publish' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_congresses.publish',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
	),
	"types" => array (
		"0" => array("showitem" => "sys_language_uid;;;;1-1-1, l18n_parent, l18n_diffsource, hidden;;1, publish, rectype, name, rparent, fe_user, courseformats, lectureformats, credits, format;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], description;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], date_from, date_to, form;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], clang;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], audience;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], teacher;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], administration;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], contact;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], , speaker_info;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], payment_info;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], schedule;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], location;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], registration_deadline, course_reg_deadline;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], host;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], website;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], diverse;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts], files, notification_email,  max_participants, categories, persons, intranet;;;richtext[paste|bold|italic|underline|formatblock|class|left|center|right|orderedlist|unorderedlist|outdent|indent|link|image]:rte_transform[mode=ts]")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);



$TCA["tx_x4econgress_participants"] = array (
	"ctrl" => $TCA["tx_x4econgress_participants"]["ctrl"],
	"interface" => array (
		"showRecordFieldList" => "hidden,type,feuser_id,congress_id,name,firstname,address,zip,city,country,poster_title,poster_abstract,poster_detail,poster_images,gender,birthyear"
	),
	"feInterface" => $TCA["tx_x4econgress_participants"]["feInterface"],
	"columns" => array (
		'hidden' => array (		
			'exclude' => 0,
			'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"type" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.type",		
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.type.I.0", "0"),
					Array("LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.type.I.1", "1"),
				),
			)
		),
		"gender" => Array (
			"exclude" => 0,
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.gender",
			"config" => Array (
				"type" => "radio",
				"items" => Array (
					Array("LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.gender.male", "m"),
					Array("LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.gender.female", "f"),
				),
			)
		),
		"birthyear" => Array (
			"exclude" => 0,
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.birthyear",
			"config" => Array (
				"type" => "input",
				"size" => "30",
				"eval" => "required"
			)
		),
		"congress_id" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.congress_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "tx_x4econgress_congresses",	
				"foreign_table_where" => "AND tx_x4econgress_congresses.pid=###CURRENT_PID### ORDER BY tx_x4econgress_congresses.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"feuser_id" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.feuser_id",		
			"config" => Array (
				"type" => "select",	
				"foreign_table" => "fe_users",	
				"foreign_table_where" => "AND fe_users.pid=###PAGE_TSCONFIG_ID### ORDER BY fe_users.uid",	
				"size" => 1,	
				"minitems" => 0,
				"maxitems" => 1,
			)
		),
		"name" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.name",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",	
				"eval" => "required",
			)
		),
		"firstname" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.firstname",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"address" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.address",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"zip" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.zip",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"city" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.city",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"email" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.email",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"phone" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.phone",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"worklocation" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.worklocation",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"remarks" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.remarks",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5"
			)
		),
		"country" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.country",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"poster_title" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.poster_title",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"poster_abstract" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.poster_abstract",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"poster_detail" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.poster_detail",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		),
		"poster_images" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.poster_images",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => $GLOBALS["TYPO3_CONF_VARS"]["GFX"]["imagefile_ext"],	
				"max_size" => 1000,	
				"uploadfolder" => "uploads/tx_x4econgress",
				"show_thumbs" => 1,	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 5,
			)
		),
		"uploads" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.uploads",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "pdf",	
				"max_size" => 5000,	
				"uploadfolder" => "uploads/tx_x4econgress",
				"show_thumbs" => 0,	
				"size" => 5,	
				"minitems" => 0,
				"maxitems" => 1,
				"show_thumbs" => 1,
			)
		),
		"dl_files" => Array (		
			"exclude" => 0,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.dl_files",		
			"config" => Array (
				"type" => "group",
				"internal_type" => "file",
				"allowed" => "",	
				"disallowed" => "php,php3",	
				"max_size" => 102400,	
				"uploadfolder" => "uploads/tx_x4econgress",
				"size" => 3,	
				"minitems" => 0,
				"maxitems" => 100,
			)
		),
		'payed' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.payed',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		'veggie' => array (		
			'exclude' => 1,
			'label'   => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.veggie',
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"custom" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.custom",		
			"config" => Array (
				"type" => "input",	
				"size" => "30",
			)
		),
		"evening" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.evening",		
			'config'  => array (
				'type'    => 'check',
				'default' => '0'
			)
		),
		"discussant" => Array (		
			"exclude" => 1,		
			"label" => "LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_participants.discussant",		
			"config" => Array (
				"type" => "text",
				"cols" => "30",	
				"rows" => "5",
			)
		)
	),
	"types" => array (
		"0" => array("showitem" => "hidden;;1;;1-1-1, type, congress_id, feuser_id, gender, name, firstname, birthyear, address, zip, city, country, email, phone, worklocation, remarks,
						--div--;Payment,payed,veggie,evening,custom"),
		"1" => array("showitem" => "hidden;;1;;1-1-1, type, congress_id, feuser_id, gender, name, firstname, birthyear, address, zip, city, country, email, phone, worklocation, remarks,
						--div--;Poster,poster_title, poster_abstract, poster_detail, poster_images, uploads, dl_files,discussant,
						--div--;Payment,payed,veggie,evening,custom,gender")
	),
	"palettes" => array (
		"1" => array("showitem" => "")
	)
);

$TCA['tx_x4econgress_categories'] = array (
    'ctrl' => $TCA['tx_x4econgress_categories']['ctrl'],
    'interface' => array (
        'showRecordFieldList' => 'sys_language_uid,l10n_parent,l10n_diffsource,hidden,title'
    ),
    'feInterface' => $TCA['tx_x4econgress_categories']['feInterface'],
    'columns' => array (
        'sys_language_uid' => array (        
            'exclude' => 1,
            'label'  => 'LLL:EXT:lang/locallang_general.xml:LGL.language',
            'config' => array (
                'type'                => 'select',
                'foreign_table'       => 'sys_language',
                'foreign_table_where' => 'ORDER BY sys_language.title',
                'items' => array(
                    array('LLL:EXT:lang/locallang_general.xml:LGL.allLanguages', -1),
                    array('LLL:EXT:lang/locallang_general.xml:LGL.default_value', 0)
                )
            )
        ),
        'l10n_parent' => array (        
            'displayCond' => 'FIELD:sys_language_uid:>:0',
            'exclude'     => 1,
            'label'       => 'LLL:EXT:lang/locallang_general.xml:LGL.l18n_parent',
            'config'      => array (
                'type'  => 'select',
                'items' => array (
                    array('', 0),
                ),
                'foreign_table'       => 'tx_x4econgress_categories',
                'foreign_table_where' => 'AND tx_x4econgress_categories.pid=###CURRENT_PID### AND tx_x4econgress_categories.sys_language_uid IN (-1,0)',
            )
        ),
        'l10n_diffsource' => array (        
            'config' => array (
                'type' => 'passthrough'
            )
        ),
        'hidden' => array (        
            'exclude' => 1,
            'label'   => 'LLL:EXT:lang/locallang_general.xml:LGL.hidden',
            'config'  => array (
                'type'    => 'check',
                'default' => '0'
            )
        ),
        'title' => array (        
            'exclude' => 0,        
            'label' => 'LLL:EXT:x4econgress/locallang_db.xml:tx_x4econgress_categories.title',        
            'config' => array (
                'type' => 'input',    
                'size' => '30',    
                'eval' => 'required',
            )
        ),
    ),
    'types' => array (
        '0' => array('showitem' => 'sys_language_uid;;;;1-1-1, l10n_parent, l10n_diffsource, hidden;;1, title;;;;2-2-2')
    ),
    'palettes' => array (
        '1' => array('showitem' => '')
    )
);

?>