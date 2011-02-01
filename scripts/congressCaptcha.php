<?php
	// include congress stuff
	
	require_once($_SERVER['DOCUMENT_ROOT'].'/typo3conf/ext/x4econgress/pi1/class.tx_x4econgress_pi1.php');
	
	class congress_captchaval extends tx_x4econgress_pi1 {
		
		function main(){
			$this->cObj = $GLOBALS["TSFE"]->cObj;
			
			$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_x4econgress_pi1.'] = array(
				'noWrapInBaseClass' => 1
			);
			
			$GLOBALS['TSFE']->tmpl->setup['config.']['spamProtectEmailAddresses'] = 3;
			$GLOBALS['TSFE']->tmpl->setup['config.']['spamProtectEmailAddresses_atSubst'] = '&nbsp;at&nbsp';
			
			$res = parent::main('',$GLOBALS['TSFE']->tmpl->setup['plugin.']['tx_x4econgress_pi1.']);
							
			echo $res;
			
			exit;
			
		}
		
	}
	
	
	function initTYPO3() {
		tslib_eidtools::connectDB();
			
		if (!defined('PATH_tslib')) {
			if (@is_dir(PATH_site.TYPO3_mainDir.'sysext/cms/tslib/')) {
				define('PATH_tslib', PATH_site.TYPO3_mainDir.'sysext/cms/tslib/');
			} elseif (@is_dir(PATH_site.'tslib/')) {
				define('PATH_tslib', PATH_site.'tslib/');
			}
		}
		require_once(PATH_tslib.'class.tslib_content.php');
		require_once(PATH_tslib.'class.tslib_fe.php');
		
		/*
		 * following two includes are for image handling
		 */
		require_once(PATH_t3lib.'class.t3lib_stdgraphic.php');
		require_once(PATH_tslib.'class.tslib_gifbuilder.php');
		
		
		
		require_once(PATH_t3lib.'class.t3lib_page.php');
		require_once(PATH_t3lib.'class.t3lib_userauth.php');
		require_once(PATH_tslib.'class.tslib_feuserauth.php');
		require_once(PATH_t3lib.'class.t3lib_tstemplate.php');
		require_once(PATH_t3lib.'class.t3lib_cs.php');
		
		// ***********************************
		// Create $TSFE object (TSFE = TypoScript Front End)
		// Connecting to database
		// ***********************************
		
		$temp_TSFEclassName = t3lib_div::makeInstanceClassName('tslib_fe');
		$GLOBALS["TSFE"] = new $temp_TSFEclassName(
			$GLOBALS["TYPO3_CONF_VARS"],
			t3lib_div::_GP('id'),
			t3lib_div::_GP('type'),
			t3lib_div::_GP('no_cache'),
			t3lib_div::_GP('cHash'),
			t3lib_div::_GP('jumpurl'),
			t3lib_div::_GP('MP'),
			t3lib_div::_GP('RDCT')
		);
		$GLOBALS['TSFE']->absRefPrefix = "/";
		$GLOBALS["TSFE"]->initFEuser();
		$GLOBALS["TSFE"]->determineId();
		$GLOBALS["TSFE"]->getCompressedTCarray();
		$GLOBALS["TSFE"]->initTemplate();
		$GLOBALS['TSFE']->forceTemplateParsing = 1;
		$GLOBALS['TSFE']->getConfigArray();
		$GLOBALS["TSFE"]->convPOSTCharset();
		$GLOBALS["TSFE"]->settingLanguage();
		$GLOBALS["TSFE"]->settingLocale();
		$GLOBALS["TSFE"]->cObj = t3lib_div::makeInstance('tslib_cObj');
		$GLOBALS['TSFE']->spamProtectEmailAddresses = $GLOBALS['TSFE']->tmpl->setup['config.']['spamProtectEmailAddresses'];

	}
	
	initTYPO3();
	$output = t3lib_div::makeInstance('congress_captchaval');
	$output->main();
	
?>