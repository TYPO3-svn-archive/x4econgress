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

require_once(t3lib_extMgm::extPath('jm_recaptcha')."class.tx_jmrecaptcha.php");
require_once('typo3conf/ext/x4epibase/class.x4epibase.php');


/**
 * Plugin 'Congress database' for the 'x4econgress' extension.
 *
 * @author	Markus Stauffiger (4eyes GmbH) <markus@4eyes.ch>
 * @package	TYPO3
 * @subpackage	tx_x4econgress
 */
class tx_x4econgress_pi1 extends x4epibase {
	var $prefixId      = 'tx_x4econgress_pi1';		// Same as class name
	var $scriptRelPath = 'pi1/class.tx_x4econgress_pi1.php';	// Path to this script relative to the extension dir.
	var $extKey        = 'x4econgress';	// The extension key.
	var $pi_checkCHash = true;
	var $tableName = 'tx_x4econgress_congresses';
	var $participantsTable = 'tx_x4econgress_participants';
	var $categoryTable = 'tx_x4econgress_categories';
	var $categoryField = 'categories';
	var $registrationUid; // uid of the registration, used for the speaker's poster-infos
	var $uploaddir = 'uploads/tx_x4econgress/';
	var $personDetailPlugin = 'tx_x4epersdb_pi7';
	
	/**
	 * Uid to use, if there is no actual record to use
	 * 
	 * @var integer
	 */
	var $dummyCongressUid = 0;

	function main($content,$conf) {
		$GLOBALS['TSFE']->additionalHeaderData[$this->extKey].='
					<link rel="stylesheet" type="text/css" href="typo3conf/ext/x4econgress/templates/styles.css" />';

		return parent::main($content,$conf);
	}

	function init($content, $conf){
		parent::init($content, $conf);

		$this->dummyCongressUid = intval($this->conf['dummyCongressUid']);
		// manage common Categories for View ListByCategory

		if($this->getTSFFvar('categoryField')){
			$this->categoryField = $this->getTSFFvar('categoryField');

			if($this->categoryField == 'persons'){
				$this->categoryTable = 'tx_x4epersdb_person';
			} else if($this->categoryField != 'categories' && $this->getTSFFvar('categoryTable')) {
				$this->categoryTable = $this->getTSFFvar('categoryTable');
			}
		}
	}


	/**
	 * Get the content of a field with type "input"
	 *
	 * @param	String	$fN		Name of the field
	 * @param 	Array	$t		TCA config of this field
	 * @return	String			Handled content;
	 */
	function getInputContent($fN,&$t) {
		if (strpos($t['eval'],'datetime')!== false) {
			if ($this->internal['currentRow'][$fN] != 0) {
				$out = strftime($this->conf['datetimeFormat'],$this->internal['currentRow'][$fN]);
				if($this->charset == 'utf-8'){
					$out = utf8_encode($out);
				}
			} else {
				return '';
			}
		} elseif(strpos($t['eval'],'date')!== false) {
			if ($this->internal['currentRow'][$fN] != 0) {
				$out = strftime($this->conf['dateFormat'],$this->internal['currentRow'][$fN]);
				if($this->charset == 'utf-8'){
					$out = utf8_encode($out);
				}

			} else {
				return '';
			}

		} elseif (isset($t['wizards']['link'])) {
			if ($this->internal['currentRow'][$fN.'Original'] == '') {
				$this->internal['currentRow'][$fN.'Original'] = $this->internal['currentRow'][$fN];
				$this->internal['currentRow'][$fN] = '';
			}
			$out = $this->cObj->getTypoLink(htmlentities($this->internal['currentRow'][$fN]),$this->internal['currentRow'][$fN.'Original']);
			array_push($this->skipHtmlEntitiesFields,$fN);
		} else {
			$out = $this->internal['currentRow'][$fN];
		}
		return $out;
	}

	/**
	 * Returns boxed content (or nothing, if field is empty)
	 *
	 * @param	string		Fieldname
	 * @return	string		Content, ready for HTML output.
	 */
	function getBoxedFieldContent($fN){

		global $TCA;

		$tmpl = $this->cObj->getSubpart($this->template,'###'.$fN.'Box###');
		if (($tmpl != '') && ($this->internal['currentRow'][$fN]!='') && $this->checkDisplayField($fN)) {
			if( ($fN=='max_participants') && ($this->internal['currentRow'][$fN] == 0)){
				return '';
			}else if( ($fN=='registration_deadline') && ($this->internal['currentRow'][$fN]<time())){
				return '';
			}

			if($TCA[$this->tableName]['columns'][$fN]['config']['type'] == 'radio'){
				if(intval($this->getFieldContent($fN)) > 0){
					$mArr[$fN] = $this->pi_getLL($fN.'.opt'.$this->getFieldContent($fN));
				} else {
					return '';
				}
			} else {
				$mArr[$fN] = $this->getFieldContent($fN);
			}

			if($fN == 'tstamp'){
				$mArr['updated'] = date($this->conf['dateFormat'],$mArr['tstamp']);
			}


			$mArr[$fN.'Label'] = $this->pi_getLL($fN);
			return $this->cObj->substituteMarkerArray($tmpl,$mArr,'###|###');
		} else {
			return '';
		}
	}

	/**
	 * Sets the showUid to the dummy uid, if registration is set to single and
	 * no other congress is selected
	 *
	 * @return void
	 */
	function handleDummyUid() {
		if ($this->piVars['showUid'] == $this->dummyCongressUid) {
			$this->conf['includeHiddenRecords'] = 1;
		}
	}

	/**
	 * Sets the showUid to the dummy congress in order to save the registration
	 * to this congress
	 *
	 * @return void
	 */
	function setDummyUid() {
		if ($this->conf['registration.']['type'] == 'single') {
			$this->piVars['showUid'] = $this->dummyCongressUid;
		}
	}


	function singleView() {
		global $TCA;

		$this->handleDummyUid();

		if (isset($this->piVars['showUid'])) {
			if($this->conf['includeHiddenRecords']){
				$rec = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->tableName,'uid IN ('.$this->piVars['showUid'].') AND deleted = 0');
				$this->internal['currentRow'] = $rec[0];
			} else {
				$this->internal['currentRow'] = $this->pi_getRecord($this->tableName,intval($this->piVars['showUid']));
			}
		}

		if (count($this->internal['currentRow'])>0) {

			if($this->conf['xmlExport'] == 1){
				$this->internal['currentRow']['name']?$filename = $this->internal['currentRow']['name']:$filename='xml_export';
				$filename = str_ireplace(' ','_',$filename);
				header('Content-Type: text/xml');
				header('charset=utf-8');
				header('Content-disposition: attachment; filename='.$filename.'.xml');
			}

			$this->getLanguageOverlay();

			if ($this->template == '') {
				$this->template = $this->cObj->fileResource($this->conf['detailView.']['template']);
			}

			if ($this->template == '') {
				return 'No detail view template found';
			}

			$tmpl = $this->cObj->getSubpart($this->template,'###singleView###');
			$this->completeTemplate = $this->template;

			if (isset($TCA[$this->tableName]['ctrl']['type']) && ($this->conf['ignoreTypeTemplate'] != 1)) {
				$this->template = $this->cObj->getSubpart($this->template,'###type'.$this->internal['currentRow'][$TCA[$this->tableName]['ctrl']['type']].'Box###');
			}

			foreach($this->internal['currentRow'] as $k=>$v){
				$sub['###'.$k.'Box###'] = $this->getBoxedFieldContent($k);
			}


			// added 13.04.2010 to display picture of person selected on the top of selectbox
			t3lib_div::loadTCA('tx_x4epersdb_person');
			$relDir = $TCA['tx_x4epersdb_person']['columns']['image']['config']['uploadfolder'];
			$persUid = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid_foreign','tx_x4econgress_congresses_persons_mm','uid_local = '.$this->internal['currentRow']['uid']);
			if(count($persUid) > 0){
				$persrec = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','tx_x4epersdb_person','uid = '.$persUid[0]['uid_foreign']);
				if($persrec[0]['image']){
					$mArr['###personpic###'] = '<img alt="Profile picture" src="'.$relDir.$persrec[0]['image'].'" />';
				}
			} else {
				$mArr['###personpic###'] = '';
			}
			t3lib_div::loadTCA($this->internal['currentTable']);
			//  --> end


			if($this->piVars['action'] == 'complete_speaker_registration'){
				unset($this->piVars['action']);
				unset($this->piVars['type']);
				unset($this->piVars['poster_title']);
				unset($this->piVars['poster_abstract']);
				unset($this->piVars['uploads']);
			}
			$mArr['###backLink###'] = $this->getBackLink($this->piVars);
			$mArr['###backLabel###'] = $this->pi_getLL('back');

			if ($this->conf['addTitleToPageTitle']) {
				$GLOBALS['TSFE']->page['title'] .= ' - '.$this->internal['currentRow'][$TCA[$this->tableName]['ctrl']['label']];
			}

			if (isset($TCA[$this->tableName]['ctrl']['type'])) {
				$mArr['###content###'] = $this->cObj->substituteMarkerArrayCached($this->template,array(),$sub);
			}

			$mArr['###updated###'] = date($this->conf['dateFormat'],$this->internal['currentRow']['tstamp']);

			$out = $this->cObj->substituteMarkerArrayCached($tmpl,$mArr,$sub);


			$out = str_ireplace('###updated###',date($this->conf['dateFormat'],$this->internal['currentRow']['tstamp']),$out);

			return $this->getRegistrationLink($out);
		}else{
			return $this->pi_getLL('noRecords');
		}
	}

	function getRegistrationLink($out,$uid=''){

		$showLink = true;

		if(intval($GLOBALS['TSFE']->fe_user->user['uid'])>0){
			$feuser = $GLOBALS['TSFE']->fe_user->user['uid'];
		}

		if($uid!=''){
			$regTrue = $this->checkRegistrationDeadline($uid);
		} else {
			$regTrue = $this->checkRegistrationDeadline();
		}

		//ugly quick fix for dreiländertagung
		if($this->piVars['showUid'] == 297 && in_array('539',explode(',',$GLOBALS['TSFE']->fe_user->user['usergroup']))){
			$showLink = false;
		}

		if(intval($feuser)>0){
			$uRegs = $this->getFeUserRegs($feuser);
			$piArr['regFeUser'] = $feuser;
			if($uRegs === false || intval($uRegs)<0 || intval($uRegs)>=3){
				$showLink = false;
			}
		}

		if (!empty($regTrue) && $showLink == true) {
			$id = $this->getTSFFvar('registrationPageUid');
			if ($id == '') {
				$id = $GLOBALS['TSFE']->id;
			}

			$piArr['action'] = 'registration';
			if(!empty($uid)){
				$piArr['showUid'] = $uid;
			}

			$mArr['###registrationLink###'] = $this->pi_linkTP_keepPIvars_url($piArr,1,0,$id);
			$mArr['###registrationLinkLabel###'] = $this->pi_getLL('registrationLink');
			$mArr['###showBox###'] = 'block';
			$mArr['###noMoreAbstracts###'] = '';

		} else {
			$mArr['###registrationLink###'] = '';
			$mArr['###registrationLinkLabel###'] = '';
			$mArr['###showBox###'] = 'none';
			$mArr['###noMoreAbstracts###'] = $this->pi_getLL('noMoreAbstracts');
		}

		return $this->cObj->substituteMarkerArray($out,$mArr);
	}

	/**
	 * Functions which establishes what kind of view can be presented
	 *
	 * @return	String
	 */
	function getView() {
		if(!empty($_GET['eID'])){
			switch($_GET['eID']){
				case 'congressCaptcha':
					$recaptcha = new tx_jmrecaptcha();
					$valid = $recaptcha->validateReCaptcha();
					return intval($valid['verified']);
				break;
			}
		} else {

			if ($this->getTSFFvar('modeSelection') == 'registrationLink') {
				// use a fake uid to trigger the correct behavior
				$this->piVars['showUid'] = $this->dummyCongressUid;
				return $this->showRegistrationLink();
			}
			
			if ($this->piVars['showUid']) {
				if ($this->checkRegistrationDeadline()) {
					switch($this->piVars['action']) {
						case 'registration':
							return $this->registrationView();
						break;
						case 'complete_registration':
							return $this->completeRegistration();
						break;
						case 'complete_speaker_registration':
							return $this->completeSpeakerRegistration();
						break;
						default:
							return $this->singleView();
						break;
					}
				} else {
					return $this->singleView();
				}
			} else {
				switch($this->piVars['action']){
					case 'postPaymentUpload':
						if($this->getTSFFvar('modeSelection') == 'profile')
						return $this->postPaymentUpload();
					break;
					default:
						return $this->getCorrectListView();
					break;
				}
			}
		}
	}

	function checkRegistrationDeadline($uid='') {
		if($uid != ''){
			$this->piVars['showUid'] = $uid;
		}

		if(intval($this->conf['unlinkRegFromDate']) > 0){
			return true;
		}

		$this->internal['currentRow'] = $this->pi_getRecord($this->tableName,$this->piVars['showUid']);

		return $this->internal['currentRow']['registration_deadline']>time();
	}

	/**
	 * Display registration view
	 */
	function registrationView() {
		$this->setDummyUid();
		if($this->piVars['regFeUser']){
			$this->piVars['type'] = 1;
			// $this->piVars['action'] = 'complete_registration';
			return $this->showSpeakerRegistration();
		} else {
			$this->template = $this->cObj->fileResource($this->conf['registration.']['template']);
			$out = $this->singleView();
			$mArr = array();
			$this->addLanguageLabels($mArr);
			$mArr['###formAction###'] = $this->pi_linkTP_keepPIvars_url(array('action'=>'complete_registration'));
			$mArr['###prefixId###'] = $this->prefixId;

			if($this->conf['registration.']['useReCaptcha'] == 1){
				$recaptcha = new tx_jmrecaptcha();
				$mArr['###RECAPTCHA###'] = $recaptcha->getReCaptcha();
			} else {
				$mArr['###RECAPTCHA###'] = '';
			}

			$out .= $this->addJSValidation('tx_x4econgress_form');
			return $this->cObj->substituteMarkerArray($out,$mArr);
		}
	}

	function completeRegistration() {
		$this->saveRegistration();
		if ($this->piVars['type']==1) {
			return $this->showSpeakerRegistration();
		} else {
			$this->sendRegistrationEmail();
			return $this->showPaymentInfos();
		}
	}

	function sendRegistrationEmail() {
		$congress = $this->internal['currentRow'];
		if(empty($congress)){
			$c = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->tableName,'uid IN ('.$this->piVars['congress_id'].')');
			$congress = $c[0];
		}
		if ($congress['notification_email'] != '' || $this->conf['forceRegistrationMail'] == 1) {
			$registration = $this->pi_getRecord($this->participantsTable,$this->registrationUid);
			$this->template = $this->cObj->fileResource($this->conf['registration_email.']['template']);
			if ($registration['type'] == 1) {
				$this->template = $this->cObj->getSubpart($this->template,'###speaker###');
			} else {
				$this->template = $this->cObj->getSubpart($this->template,'###participant###');
			}
			$this->template = trim($this->template);

			foreach($registration as $key => $value) {
				$m[$key] = $value;
			}
			if ($m['type'] == 1) {
				$m['type'] = 'Referent';
			} else {
				$m['type'] = 'Teilnehmer';
			}

			//$m['congressName'] = $congress['name'];

			foreach($congress as $key => $value){
				$m['congress'.ucfirst($key)] = $value; // to have congress data available in markerArray
			}

			$m['feUserName'] = $this->feUserName;
			$m['feUserPw'] = $this->feUserPw;

			$recipients = array();
			$message = array();
			$subject = array();

			if(!empty($congress['notification_email'])){
				$recipients[] = $congress['notification_email'];
				$message[] = $this->cObj->substituteMarkerArray($this->template,$m,'###|###');
				$subject[] = 'Kongress Anmeldung';
			}

			if(!empty($m['email'])){
				$recipients[] = $m['email'];
				if($this->conf['userRegMailTemplate']){
					$userTemplate = $this->cObj->fileResource($this->conf['userRegMailTemplate']);
					$tSub = $this->cObj->getSubpart($userTemplate,'###subject###');
					$tMess[] = $this->cObj->getSubpart($userTemplate,'###message###');
					$subject[] = strip_tags(trim($this->cObj->substituteMarkerArray($tSub,$m,'###|###')));
					$message[] = strip_tags(str_replace("<br />", "\n", $this->cObj->substituteMarkerArray($tMess[0],$m,'###|###')));
				} else {
					$message[] = $this->cObj->substituteMarkerArray($this->template,$m,'###|###');
					$subject[] = 'Kongress Anmeldung';
				}
			}

			require_once(PATH_t3lib.'class.t3lib_htmlmail.php');
			$mailer = t3lib_div::makeInstance('t3lib_htmlmail');

			$i = 0;
			foreach($recipients as $recipient){
				$mailer->start();
				$mailer->addPlain($message[$i]);
				$mailer->from_email = 'noreply@unibas.ch';
				$mailer->from_name = 'Kongress-Anmeldung';
				$mailer->subject = $subject[$i];
				$mailer->replyto_email = $mailer->from_email;
				$mailer->replyto_name = $mailer->from_name;
				$mailer->organisation = 'Uni Basel';

				// added by manuel - 5.5.2010 - overwrite with TS constants - begin
				if(!empty($this->conf['mailer.'])){
					$mailer->from_email = $this->conf['mailer.']['from_mail'];
					$mailer->from_name = $this->conf['mailer.']['from_name'];
					if(empty($subject[$i]) || $subject[$i] == $subject[0]){
						$mailer->subject = $this->conf['mailer.']['subject'];
					}
					$mailer->organisation = $this->conf['mailer.']['organisation'];
					if(!empty($this->conf['mailer.']['mailcc']) && $i < 1){
						$mailer->recipient_copy = $this->conf['mailer.']['mailcc'];
					}
				}
				// end

				if ($registration['type']==1) {
					$files = t3lib_div::trimExplode(',',$registration['poster_images'],1);
					foreach($files as $file) {
						$mailer->addAttachment($this->uploaddir.$file);
					}
				}
				$mailer->send($recipient); // send mail to admin
				++$i;
			}
		}
	}

	function saveRegistration() {
		global $TCA;
		t3lib_div::loadTCA($this->participantsTable);

		$fields = t3lib_div::trimExplode(',',$TCA[$this->participantsTable]['feInterface']['fe_admin_fieldList'],1);
		$this->piVars['congress_id'] = intval($this->piVars['showUid']);

		if($GLOBALS['TSFE']->fe_user->user){
			$user = $GLOBALS['TSFE']->fe_user->user;
			foreach($fields as $field){
				switch($field){
					case 'name':
						$user['name'] = $user['last_name'];
						break;
					case 'firstname':
						$user['firstname'] = $user['first_name'];
						break;
					case 'phone':
						$user['phone'] = $user['telephone'];
						break;
					case 'worklocation':
						$user['worklocation'] = $user['company'];
						break;
					case 'feuser_id':
						$user['feuser_id'] = $user['uid'];
						break;
				}

				if(isset($user[$field])){
					$ins[$field] = $GLOBALS['TYPO3_DB']->quoteStr($user[$field],$this->participantsTable);
				}

				$ins['random_key'] = $this->piVars['rand'];
			}
		} else {
			foreach($fields as $field) {
				if (isset($this->piVars[$field])) {
					$ins[$field] = $GLOBALS['TYPO3_DB']->quoteStr($this->piVars[$field],$this->participantsTable);
				}
			}
		}

		$ins['pid'] = $this->getTSFFvar('pidList');
		$GLOBALS['TYPO3_DB']->exec_INSERTquery($this->participantsTable,$ins);
		$this->registrationUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
		$this->userPaymentName = $ins['firstname'].' '.$ins['name'];

		// create FE-User if wanted
		if(!empty($this->registrationUid) && $this->conf['registration.']['createFeUser'] == 1){
			// need for evaluation whether registrationEmail goes out
			$no_insert = false;
			if(!empty($ins['email'])){
				$userArr['username'] = $ins['email'];
				$userArr['email'] = $ins['email'];
			} else if(!empty($ins['firstname']) && !empty($ins['name'])) {
				$userArr['username'] = $ins['firstname'].'.'.$ins['name'];
			} else {
				$no_insert = true;
				// todo !!
			}

			if(!empty($ins['firstname']) && !empty($ins['name'])){
				$userArr['name'] = $ins['firstname'].' '.$ins['name'];
				$userArr['first_name'] = $ins['firstname'];
				$userArr['last_name'] = $ins['name'];
			}

			if(!empty($ins['address'])){
				$userArr['address'] = $ins['address'];
			}

			if(!empty($ins['zip'])){
				$userArr['zip'] = $ins['zip'];
			}

			if(!empty($ins['city'])){
				$userArr['city'] = $ins['city'];
			}

			if(!empty($ins['country'])){
				$userArr['country'] = $ins['country'];
			}

			if(!empty($ins['phone'])){
				$userArr['telephone'] = $ins['phone'];
			}

			if(!empty($ins['worklocation'])){
				$userArr['company'] = $ins['worklocation'];
			}

			$userArr['username'] = $this->checkFeUserName($userArr['username'],$this->conf['registration.']['feUserPid']);

			if(!$userArr['email'] && $ins['email']){
				$userArr['email'] = $ins['email'];
			}

			if($no_insert == false){

				$this->feUserName = $userArr['username'];
				$this->feUserPw = $this->createRandomPassword();

				$userArr['pid'] = $this->conf['registration.']['feUserPid'];
				$userArr['tstamp'] = time();
				$userArr['password'] = $this->feUserPw;
				$userArr['crdate'] = time();
				$userArr['usergroup'] = $this->conf['registration.']['userGroups'];

				$GLOBALS['TYPO3_DB']->exec_INSERTquery('fe_users',$userArr);
				$feUid = $GLOBALS['TYPO3_DB']->sql_insert_id();
				if($feUid && intval($feUid) > 0){
					$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->participantsTable,'uid IN ('.$this->registrationUid.')',array('feuser_id' => $feUid));
				}

			}
		}
	}

	function checkFeUserName($uname,$pid,$i=0){
		if($i == 0){
			$us = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('username','fe_users','pid IN ('.$pid.') AND username LIKE '.$GLOBALS['TYPO3_DB']->escapeStrForLike($GLOBALS['TYPO3_DB']->fullQuoteStr($uname, 'fe_users'),'fe_users').$this->cObj->enableFields('fe_users'));
			if(count($us)<1){
				return $uname;
			}
		}

		$temp = $uname.$i;
		$users = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('username','fe_users','pid IN ('.$pid.') AND username LIKE '.$GLOBALS['TYPO3_DB']->escapeStrForLike($GLOBALS['TYPO3_DB']->fullQuoteStr($temp, 'fe_users'),'fe_users').$this->cObj->enableFields('fe_users'));

		if(count($users) > 0){
			return $this->checkFeUserName($uname,$pid,++$i);
		} else {
			return $temp;
		}
	}

	/**
	 * creates a random password
	 *
	 * @return String
	 */
	function createRandomPassword() {
		$chars = "abcdefghijkmnopqrstuvwxyz023456789";
		srand((double)microtime()*1000000);

		$i = 0;
		$pass = '' ;

		while ($i <= 7) {
			$num = rand() % 33;
			$tmp = substr($chars, $num, 1);
			$pass = $pass . $tmp;
			$i++;
		}

		return $pass;
	}

	/**
	 * Displays the registration link
	 *
	 * @return String
	 */
	function showRegistrationLink() {
		return $this->getRegistrationLink('<a href="###registrationLink###">###registrationLinkLabel###</a>',$uid='');
	}

	/**
	 * Display the payment information for participants
	 *
	 * @return String
	 */
	function showPaymentInfos() {
		$this->template = $this->cObj->fileResource($this->conf['payment.']['template']);
		$mArr = array(
			'###regno###' => $this->registrationUid,
			'###username###' => $this->userPaymentName
		);
		$this->addLanguageLabels($mArr);
		$out = $this->singleView();
		return $this->cObj->substituteMarkerArray($out,$mArr);
	}

	/**
	 * Displays the form for the speakers to give their information about their speech
	 *
	 * @return String
	 */
	function showSpeakerRegistration() {

		$this->addfValidate();

		if($this->piVars['regFeUser']){
			// set regUid = 0 because if FE-User there is no registration
			$this->registrationUid = 0;
		}

		$this->template = $this->cObj->fileResource($this->conf['speakerRegistration.']['template']);
		$out = $this->singleView();
		$mArr = array();
		$this->addLanguageLabels($mArr);
		$mArr['###formAction###'] = $this->pi_linkTP_keepPIvars_url(array('action'=>'complete_speaker_registration'));
		$mArr['###prefixId###'] = $this->prefixId;
		$mArr['###uid###'] = $this->registrationUid;
			// use of random key to prevent people to edit one another's registration
		$mArr['###rand###'] = rand();
		$upd['random_key'] = $mArr['###rand###'];
		if(!$this->piVars['regFeUser']){
			// only if no FE-User is available
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->participantsTable,'uid = '.$this->registrationUid,$upd);
		}
		return $this->cObj->substituteMarkerArray($out,$mArr);
	}

	/**
	 * Completes the speakers registration by saving the poster information
	 *
	 */
	function completeSpeakerRegistration() {
		$this->updateSpeakerRegistration();
		$this->template = $this->cObj->fileResource($this->conf['completeSpeakerRegistration.']['template']);
		$out = $this->singleView();
		$mArr = array();
		$this->addLanguageLabels($mArr);
		$out = $this->cObj->substituteMarkerArray($out,$mArr);
		$this->sendRegistrationEmail();
		return $out;
	}

	/**
	 * Updates the registration
	 *
	 */
	function updateSpeakerRegistration() {
		global $TCA;

		if($this->piVars['regFeUser']){
			$this->saveRegistration();
			$this->piVars['uid'] = $this->registrationUid;
			$upd['congress_id'] = $this->piVars['showUid'];
		}

		/* added by manuel - required fields check */
		$reqFields = explode(',',$this->conf['speakerRegistration.']['requiredFields']);

		t3lib_div::loadTCA($this->participantsTable);
		$fields = t3lib_div::trimExplode(',',$TCA[$this->participantsTable]['feInterface']['fe_admin_fieldList'],1);
		$upd = array();
		foreach($fields as $field) {
			if (isset($this->piVars[$field]) && ($field != 'poster_images')) {
				$upd[$field] = $GLOBALS['TYPO3_DB']->quoteStr($this->piVars[$field],$this->participantsTable);
			}
		}
		$upd['poster_images'] = $this->handlePosterImages();
		$upd['uploads'] = $this->handleUploads();
		$upd['random_key'] = '';

		$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->participantsTable,'uid = '.intval($this->piVars['uid']).' AND random_key = '.intval($this->piVars['rand']),$upd);
		$this->registrationUid = $this->piVars['uid'];
	}

	/**
	 * Uploads the poster files
	 *
	 */
	function handlePosterImages() {

		$maxFiles = 5;
		$files = array();
		for($i = 0; $i < $maxFiles; $i++) {
			if ($_FILES[$this->prefixId]['name']['poster_images'][$i] != '') {
				if (t3lib_div::verifyFilenameAgainstDenyPattern($_FILES[$this->prefixId]['name']['poster_images'][$i])) {
					$filenameParts = t3lib_div::trimExplode('.',$_FILES[$this->prefixId]['name']['poster_images'][$i],1);
					$ending = array_pop($filenameParts);
					$name = array_shift($filenameParts);
					$j = 1;
					while (is_file($this->uploaddir . $_FILES[$this->prefixId]['name']['poster_images'][$i])) {
						$_FILES[$this->prefixId]['name']['poster_images'][$i] = $name.$j.'.'.$ending;
						$j++;
					}
					if (t3lib_div::upload_copy_move($_FILES[$this->prefixId]['tmp_name']['poster_images'][$i],$this->uploaddir . $_FILES[$this->prefixId]['name']['poster_images'][$i])) {
						$files[]= $_FILES[$this->prefixId]['name']['poster_images'][$i];
					}
				}
			}
		}
		return implode(',',$files);
	}


	/**
	 * Uploads files
	 *
	 */
	function handleUploads($max=3) {

		$maxFiles = $max;
		$files = array();
		for($i = 0; $i < $maxFiles; $i++) {
			if ($_FILES[$this->prefixId]['name']['uploads'][$i] != '') {
				if (t3lib_div::verifyFilenameAgainstDenyPattern($_FILES[$this->prefixId]['name']['uploads'][$i])) {
					$filenameParts = t3lib_div::trimExplode('.',$_FILES[$this->prefixId]['name']['uploads'][$i],1);
					$ending = array_pop($filenameParts);
					$name = array_shift($filenameParts);
					$j = 1;
					while (is_file($this->uploaddir . $_FILES[$this->prefixId]['name']['uploads'][$i])) {
						$_FILES[$this->prefixId]['name']['uploads'][$i] = $name.$j.'.'.$ending;
						$j++;
					}
					if (t3lib_div::upload_copy_move($_FILES[$this->prefixId]['tmp_name']['uploads'][$i],$this->uploaddir . $_FILES[$this->prefixId]['name']['uploads'][$i])) {
						$files[]= $_FILES[$this->prefixId]['name']['uploads'][$i];
					}
				}
			}
		}
		return implode(',',$files);
	}

	function getBackLink(){
		/*foreach($this->piVars as $k=>$v) {
			if ($k != 'showUid') {
				$p[$this->prefixId.'['.$k.']'] = $v;
			}
		}*/
		$id = $this->getTSFFvar('listPageUid');

		// get correct Backlink in categoryMenu mode
		if(!empty($this->conf['categoryMenu.']['altPageUid']) && !empty($this->piVars['category'])){
			$id = $this->conf['categoryMenu.']['altPageUid'];
		}

		// get corrent Backlink to searchresults
		if($this->piVars['action'] == 'search'){
			$id = $this->conf['searchView.']['listPid'];
		}

		if ($id == '') {
			$id = $GLOBALS['TSFE']->id;
		}

		return $this->pi_linkTP_keepPIvars_url(array('showUid' => ''),0,0,$id);
		//return $this->pi_getPageLink($id,'',$p);
	}

	/**
	  * Adds search parameters given by the form to the query
	  *
	  * @return 	String		SQL Where statement
	  **/
	function addSearchParameters() {
		// filter for time period of records
		$period = $this->getTSFFvar('recordPeriod');
		$addWhere = '';
		if(isset($period)){
			$nowTime = time();
			switch($period){
				case 1:
					$addWhere = ' AND ('.$this->tableName.'.date_to >= '.$nowTime.' OR '.$this->tableName.'.date_to = 0)';
				break;
				case 2:
					$addWhere = ' AND '.$this->tableName.'.date_to <= '.$nowTime.' AND '.$this->tableName.'.date_to != 0';
				break;
				default:
				break;
			}
		}
		return $addWhere.parent::addSearchParameters();
	}

	/**
	 * Function to create a default table-like list view
	 *
	 * @param	String 	$addWhere	Additional where condition to select the records
	 * @return	String				HTML-View of list
	 */
	function listView($addWhere=''){

		/* snippet for searchResults
		 * added: April 7th, 2010
		 * by: Manuel
		 */
		if($this->piVars['action'] == 'search'){
			$from = $this->piVars['fromdate'];
			$to = $this->piVars['todate'];
			$sword = $this->piVars['sword'];

			if(!empty($from)){
				$from = $this->getLangDate($from);
				$from = mktime(0,0,0,$from[1],$from[0],$from[2]);
				$addWhere .= ' AND ('.$this->tableName.'.date_from >= '.$from.' OR '.$this->tableName.'.date_from = 0)';
			}

			if(!empty($to)){
				$to = $this->getLangDate($to);
				$to = mktime(23,59,59,$to[1],$to[0],$to[2]);
				$addWhere .= ' AND ('.$this->tableName.'.date_to <= '.$to.' OR '.$this->tableName.'.date_to = 0)';
			}
		}

		// end of snippet

		if($_GET['tx_x4epersdb_pi1']){
			$persUid = $_GET['tx_x4epersdb_pi1']['showUid'];
			$userCong = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid_local','tx_x4econgress_congresses_persons_mm','tx_x4econgress_congresses_persons_mm.uid_foreign IN ('.$persUid.')');
			$persCong = array();
			while($uCong = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($userCong)){
				array_push($persCong,$uCong['uid_local']);
			}
			$persCong = implode(',',$persCong);
			$addWhere .= ' AND '.$this->tableName.'.uid IN ('.$persCong.')';
		}

		$out = parent::listView($addWhere.' AND sys_language_uid = 0');
		return $out;
	}

	/**
	 * Returns a list row. Get data from $this->internal['currentRow'];
	 */
	function pi_list_row($c) {
		$cells = '';
		$this->getLanguageOverlay();
		foreach($this->manualFieldOrder_list as $fieldName)	{
			// add either link of field-value-only
			$mArr['###class###'] = '';
			if ($this->conf['columnClasses.'][$fieldName] != '') {
				$mArr['###class###'] = 'class="'.$this->conf['columnClasses.'][$fieldName].'"';
			}

			if(empty($this->conf['listView.']['detailLinkFields'])){
				$this->conf['listView.']['detailLinkFields'] = '';
			}

			if (in_array($fieldName,$this->conf['listView.']['detailLinkFields'])) {

				$mArr['###content###'] = $this->pi_list_linkSingle($this->internal['currentRow'][$fieldName],$this->internal['currentRow']['uid'],true,array(),false,$this->conf['listView.']['detailPageUid']);

				$fe_user = $GLOBALS['TSFE']->fe_user->user['uid'];

				if(!empty($fe_user) && intval($fe_user) > 0 && intval($this->getFeUserRegs($fe_user)) <= 3 && $this->conf['showRegLinkInListView'] == 1){
					$abstractLinkTmpl = ' - ( <a href="###registrationLink###" target="_self">Jetzt Abstract einreichen!</a>  )';
					if(!empty($abstractLinkTmpl)){
						$mArr['###content###'] .= $this->getRegistrationLink($abstractLinkTmpl,$this->internal['currentRow']['uid']);
					}
				}
			} else {
				$mArr['###content###'] = $this->getFieldContent($fieldName);
			}
			$cells .= $this->cObj->substituteMarkerArray($this->cellT[$c%2],$mArr);
		}
		$mArr['###uid###'] = $this->internal['currentRow']['uid'];

		$sub['###cell###'] = $cells;
		return $this->cObj->substituteMarkerArrayCached($this->rowT[$c%2],$mArr,$sub);
	}

	/**
	 * triggers member rendering;
	 */
	function getFieldContent($fN){
		global $TCA;
		$t = $TCA[$this->internal['currentTable']]['columns'][$fN]['config'];
		if($t['type']=='select' && $t['foreign_table'] != '' && $t['foreign_table']=='tx_x4epersdb_person') {
			if($fN=='persons'){
				$this->handleStdWrap($fN);
				$out = $this->renderPersonRelation($t['foreign_table'],$fN);
			}
		}else{
			switch($fN) {
				case 'dl_files':
					$out = parent::getFieldContent($fN);

					if(!empty($this->internal['currentRow']['dl_files'])){
						$file = $_SERVER['DOCUMENT_ROOT'].'/'.$t['uploadfolder'].'/'.$this->internal['currentRow']['dl_files'];
						$fsize = $this->formatFilesize(filesize($file));

						$ext = array_reverse(explode('.',$this->internal['currentRow']['dl_files']));
						$ext = strtoupper($ext[0]);

						$img = 'http://'.$_SERVER['SERVER_NAME'].'/typo3/sysext/cms/tslib/media/fileicons/'.strtolower($ext).'.gif';
						$img = '<img src="'.$img.'" title="'.$this->internal['currentRow']['dl_files'].'" />';

						$out .= ' ['.$fsize.' / '.$ext.'] '.$img;
					}

					$out = parent::handlePostStdWrap($out,$fN);

					break;
				case 'files':
					$this->conf['filelink'] = $this->conf['filelink.'];
				default:
					$out = parent::getFieldContent($fN);
				break;
			}
		}

		// remove HTML-Tags from content.
		if(intval($this->getTSFFvar('stripTagContent')) == 1){
			// remove empty paragraphs
			//$out = strip_tags($out);
			$out = str_replace('<p>&nbsp;</p>', '', $out);
			$out = str_replace('&nbsp;', ' ', $out);
			$out = str_replace('<br />',"\n",$out);
			$out = htmlspecialchars_decode($out);
			//$out = wordwrap($out);
			return htmlspecialchars(strip_tags($out));
		}

		return $out;
	}

	/**
	 * Renders Membertable of involved persons with links to persDB detail page;
	 */
	function renderPersonRelation($table,$fN){
		global $TCA;
		$content='';
		$fullTmpl=$this->cObj->fileResource($this->conf['detailView.']['template']);
		$listTmpl=$this->cObj->getSubpart($fullTmpl,'###personTable###');
		$listItem=$this->cObj->getSubpart($fullTmpl,'###listPerson###');
		$mmTable=$TCA[$this->tableName]['columns'][$fN]['config']['MM'];
		$relatedItems=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid_foreign',$mmTable,'uid_local = '.$this->internal['currentRow']['uid']);
		//$detailFieldList=t3lib_div::trimExplode(',',$this->conf['detailView.'][$table.'.']['enableDetailFields']);
		$i = 0;
		foreach($relatedItems as $item){
			// load from originating table (=$table) and render with listview.template
			$mArr=array();
			$row=$GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$table,'uid = '.$item['uid_foreign']);
			if(is_array($row)){
				foreach($row[0] as $fieldName=>$fieldValue){
					//if(in_array($fieldName,$detailFieldList)){
						if($fieldName=='email'){
							$mArr['###'.$fieldName.'###']=$this->cObj->getTypoLink($fieldValue,$fieldValue);
						}else{
							$mArr['###'.$fieldName.'###']=($fieldValue);
							if ($fieldName == 'office_phone') {
								$mArr['###'.$fieldName.'###'] = str_replace(' ','&nbsp;',$mArr['###'.$fieldName.'###']);
							}
						}
					//}
				}

				$tmpPrefix=$this->prefixId;
				$this->prefixId=$this->personDetailPlugin;
				$xVars['showUid']=$row[0]['uid'];
				$xVars['originPageID']=$GLOBALS['TSFE']->id;
				$oldPrefix=$this->prefixId;
				$this->prefixId='tx_x4epersdb_pi1';
				$mArr['###personLink###']=$this->pi_linkTP_keepPIvars_url($xVars,0,0,$this->conf['persDB.']['detailPageUid']);
				$this->prefixId=$oldPrefix;
				$mArr['###class###'] = '';
				if (($i%2)==0) {
					$mArr['###class###'] = 'class="odd"';
				}
				$i++;
				$this->prefixId=$tmpPrefix;

				$content.=$this->cObj->substituteMarkerArray($listItem,$mArr);
			}
		}
		/* foreach(t3lib_div::trimExplode(',',$this->conf['detailView.'][$table.'.']['enableDetailFields'],1) as $headerField){
			$headArr['###'.$headerField.'Label###']=$this->pi_getLL($headerField.'Label');
		} */

		$content=$this->cObj->substituteMarker($listTmpl,'###list###',$content);

		//$content=$this->cObj->substituteMarkerArray($content,$headArr);
		return($content);
	}


	/**
	 * Checks which list view is supposed to show up and calls the appropriate
	 * function
	 *
	 * @return String
	 */
	function getCorrectListView() {
		switch($this->getTSFFvar('modeSelection')) {
			case 'category':
				return $this->listByCategory();
			break;
			case 'categoryMenu':
				return $this->getCategoryMenu();
			break;
			case 'listOfDetail':
				return $this->listOfDetailView();
			break;
			case 'alphabeticalList':
				return $this->listByAlphabet();
			break;
			case 'search':
				return $this->searchView();
				break;
			case 'feedit':
				return $this->courseInput();
				break;
			case 'hierarchy':
				if(!empty($this->piVars['hierarchyParentUid'])){
					return $this->listHierarchy($this->piVars['hierarchyParentUid']);
				}
				return $this->listHierarchy();
				break;
			case 'profile':
				return $this->profilePage();
				break;
			case 'paypallistener':
				return $this->payPalResponse();
				break;
			default:
				return $this->listView();
			break;
		}
	}

	/**
	 * displays the searchForm
	 *
	 * @return String
	 */
	function searchView(){
		if ($this->template == '') {
			$this->template = $this->cObj->fileResource($this->conf['searchView.']['template']);
		}

		if ($this->template == '') {
			return 'No template for list view found. File: '.$this->conf['searchView.']['template'];
		}

		if ($this->sBox == '') {
			$this->sBox = $this->cObj->getSubpart($this->template,'###searchBox###');
		}

		$mArr = array();

		if(!empty($this->conf['searchView.']['listPid'])){
			$mArr['###formAction###'] = $this->pi_linkTP_keepPIvars_url(array('action'=>'search'),0,0,$this->conf['searchView.']['listPid']);
		} else {
			$mArr['###formAction###'] = $this->pi_linkTP_keepPIvars_url(array('action'=>'search'));
		}

		$mArr['###fromdate###'] = $this->pi_getLL('search_fromdate');
		$mArr['###todate###'] = $this->pi_getLL('search_todate');
		$mArr['###keyword###'] = $this->pi_getLL('search_keyword');
		$mArr['###submit###'] = $this->pi_getLL('form_submit');
		$mArr['###pickerLang###'] = $GLOBALS['TSFE']->lang;

		return $this->cObj->substituteMarkerArrayCached($this->sBox,$mArr);

	}

	/**
	 * returns Array of date-input exploded by '.' and arranged as day|month|year dependent on actual page language
	 *
	 * @return String
	 */
	function getLangDate($date){

		$date = explode('.',$date);

		switch($GLOBALS['TSFE']->lang){
		case 'en':
			$d = $date[1];
			$m = $date[0];
			$y = $date[2];
			break;
		default:
			$d = $date[0];
			$m = $date[1];
			$y = $date[2];
		}

		return array($d,$m,$y);
	}

	/**
	 * inputView to enter course record from front-end
	 *
	 * @return
	 */
	function courseInput(){

		global $TCA;

		$Tmpl=$this->cObj->fileResource($this->conf['feedit.']['templateFile']);
		if(empty($Tmpl)){
			return 'No template found!';
		}

		$feeditTmpl=$this->cObj->getSubpart($Tmpl,'###editForm###');
		if(empty($feeditTmpl)){
			return 'Subpart \'editForm\' not found in template \''.$this->conf['feedit.']['templateFile'].'\' !';
		}


		$cols = $TCA[$this->tableName]['columns'];

		foreach($cols as $col => $colConf){

			if($colConf['config']['eval'] == 'datetime'){
				$colName = 'date_from';
				$lang = $GLOBALS['TSFE']->lang;
				$mArr['###datepickerScript###'] = "var ".$col."Datepicker = new DatePicker({relative : '$colName',language : '$lang'});";
			}

		}

		if(!empty($mArr['###datepickerScript###'])){
			$script = $mArr['###datepickerScript###'];

			$script = '<script type="text/javascript">/*<[CDATA[*/ '.$script;
			$script .= ' /*]]>*/</script>';

			$mArr['###datepickerScript###'] = $script;
		} else {
			$mArr['###datepickerScript###'] = '';
		}


		return $this->cObj->substituteMarkerArray($feeditTmpl,$mArr);



	}

	/**
	 * Renders a category
	 *
	 * @param	Array	$category	Category record
	 * @return 	String				Rendered category
	 *
	 */
	function renderCategory(&$category) {

		global $TCA;
		t3lib_div::loadTCA($this->categoryTable);
		$displayCatFields = $this->conf['listView.']['dispCatFields'];


		if (isset($TCA[$this->tableName]['columns'][$this->categoryField]['config']['MM'])) {
			$where = $GLOBALS['TYPO3_DB']->SELECTquery('uid_local',$TCA[$this->tableName]['columns'][$this->categoryField]['config']['MM'],'uid_foreign = '.$category['uid']);
			$where = 'uid IN ('.$where.')';
		} else {
			$where = $this->categoryField.'='.intval($category['uid']);
		}

		$s['###list###'] = $this->listView(' AND '.$where);
		$bakTable = $this->tableName;
		$this->tableName = $this->categoryTable;
		$this->internal['currentRow'] = $category;
		$this->getLanguageOverlay();
		$m['###categoryLabel###'] = $this->internal['currentRow'][$TCA[$this->categoryTable]['ctrl']['label']];
		$m['###categoryUid###'] = $this->internal['currentRow']['uid'];

		if($displayCatFields != ''){
			$cField = explode(',',$displayCatFields);
			if(count($cField) > 0){
				foreach($cField as $f){
					$m['###category'.$f.'###'] = $this->internal['currentRow'][$f];
				}
			} else {
				$m['###category'.$f.'###'] = $this->internal['currentRow'][$f];
			}
		}


		$this->tableName = $bakTable;
		return $this->cObj->substituteMarkerArray($s['###list###'],$m);
	}


/**

	 * Erstellt das Kategorien-Menü

 	 */

	function getCategoryMenu($addWhere='') {
		$templateCode = $this->cObj->fileResource($this->conf['categoryMenu.']['template']);
		if ($templateCode == '') {
			$templateCode = $this->template;
		}



		$template['total'] = $this->cObj->getSubpart($templateCode, '###catMenu###');
		$template['menu'] = $this->cObj->getSubpart($template['total'], '###menu###');

		if (isset($this->conf['categoryMenu.']['catTable'])) {
			$catTable = $this->conf['categoryMenu.']['catTable'];
		} else {
			$catTable = $this->categoryTable;
		}
		if (isset($this->conf['categoryMenu.']['catField'])) {
			$catField = $this->conf['categoryMenu.']['catField'];
		} else {
			$catField = $this->categoryField;
		}

		if (isset($this->conf['categoryMenu.']['catLabelField'])) {
			$catLabelField = $this->conf['categoryMenu.']['catLabelField'];
		} else {
			$catLabelField = $this->categoryLabelField;
		}

		// Wenn nur 'gebrauchte' Kategorien angezeigt werden sollen, hole diese Kategorien
		if($this->conf['categoryMenu.']['onlyShowNecessaryCats'] == '1')
		{
			$catArr = array();
			$projects = $GLOBALS['TYPO3_DB']->exec_SELECTquery('uid,'.$catField, $this->tableName, '1 '.$this->cObj->enableFields($this->tableName));
			while($p = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($projects)) {
				array_push($catArr, $p[$catField]);
			}

			$catArr = array_unique($catArr);

			$WHERE = 'uid IN ('.implode(',',$catArr).')';
		} else {
			$WHERE = '1';
		}

		if(!empty($this->conf['categoryMenu.']['onlyShowSelectedCats'])){
			$catUids = implode(',',array_unique(explode(',',$this->getTSFFvar('which_cat'))));
			if(!empty($catUids)){
				$WHERE .= ' AND uid IN ('.$catUids.')';
			}
		}

		global $TCA;
		t3lib_div::loadTCA($catTable);
		if (isset($TCA[$catTable]['ctrl']['languageField'])) {
			$WHERE .= ' AND sys_language_uid = 0';
		}

		$catsPID = $this->getTSFFvar('categoryPidList');
		if($catsPID == '{$plugin.tx_x4econgress_pi1.pidList}') $catsPID = $this->getTSFFvar('pidList');

		$WHERE .= ' AND '.$catTable.'.pid IN ('.$catsPID.')'.$this->cObj->enableFields($catTable);

		$WHERE .= $addWhere;

		$this->conf['categoryMenu.']['orderCatBy'] ? $ORDERBY = $this->conf['categoryMenu.']['orderCatBy'] : $this->categorySortField;

		$altPageUid = intval($this->conf['categoryMenu.']['altPageUid']);
		$cats = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', $catTable, $WHERE, '', $ORDERBY);
		$bakTable = $this->tableName;
		$this->tableName = $catTable;
		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($cats)) {
			$this->getLanguageOverlay();
			$params = array("category" => $this->internal['currentRow']['uid']);
			$markerArray['###item###'] = $this->pi_linkTP_keepPIvars($this->internal['currentRow'][$catLabelField], $params,1,0,$altPageUid);
			$markerArray['###itemLabel###'] = $this->internal['currentRow'][$catLabelField];
			$markerArray['###itemUid###'] = $this->internal['currentRow']['uid'];
			if($this->piVars['category'] == $this->internal['currentRow']['uid']) {
				$markerArray['###class###'] = 'act';
				$markerArray['###selected###'] = 'selected="selected"';
			} else {
				$markerArray['###class###'] = 'no';
				$markerArray['###selected###'] = '';
			}
			$items .= $this->cObj->substituteMarkerArrayCached($template['menu'], $markerArray);
		}
		$this->tableName = $bakTable;
		$subpartArray['###menu###'] = $items;
		$markerArray['###categorySearchLabel###'] = $this->pi_getLL('categorySearchLabel');
		$content = $this->cObj->substituteMarkerArrayCached($template['total'], $markerArray, $subpartArray);

		return $content;
	}


	/**
	 * Renders a hierarchyView
	 *
	 *
	 */

	function listHierarchy($parentID=0,$lvl = 0) {
		global $TCA;

		$PID = $this->getTSFFvar('pidList');
		$addWhere = ' AND '.$this->tableName.'.rparent = '.$parentID;

		if($this->piVars['hierarchyUid'] && $lvl == 0){
			$addWhere .= ' AND uid = '.$this->piVars['hierarchyUid'];
		}

		$regLvl = intval($this->getTSFFvar('registrationLevel')); // level to display registration link

		// Get FE-User
		$fe_user = $GLOBALS['TSFE']->fe_user->user['uid'];

		// get FE-Users application
		$payed = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*',$this->participantsTable,$this->participantsTable.'.feuser_id IN ('.intval($fe_user).')'.$this->cObj->enableFields($this->participantsTable));
		$userReg = $payed[0];

		$res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->tableName,$this->tableName.'.pid IN ('.$PID.')'.$this->cObj->enableFields($this->tableName).$addWhere,'',$this->getTSFFvar('orderBy'));

		if ($this->template == '') {
			$this->template = $this->cObj->fileResource($this->conf['listView.']['hierarchyTemplateFile']);
		}

		if ($this->template == '') {
			return 'No template found for hierarchy view...';
		}

		$this->completeTemplate = $this->template;

		$tmpl = $this->cObj->getSubpart($this->template,'###listView###');
		$sub = $this->cObj->getSubpart($this->template,'###lvl_'.$lvl.'###');
		$nameLink = $this->cObj->getSubpart($this->template,'###nameWithRegLink###');

		while($this->internal['currentRow'] = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res)) {

				$h = $this->internal['currentRow'];

				$h['fe_user'] = $this->getFieldContent('fe_user');

				if($showRegLink == true){
					// if record of defined level, show reg-link
					$h['name'] = $this->cObj->substituteMarkerArray($this->getRegistrationLink($nameLink,$h['uid']),$h,'###|###');
				}

				$paperWhere = ' AND '.$this->participantsTable.'.congress_id IN ('.$h['uid'].')';
				$h['papers'] = $this->renderPapers($paperWhere,$PID,$userReg);

				$parent = $this->cObj->substituteMarkerArray($sub,$h,'###|###');

				$child = $this->listHierarchy($h['uid'],$lvl+1);

				$out .= $this->cObj->substituteSubpart($parent,'###lvl_'.($lvl+1).'###',$child);
		}

		$GLOBALS['TYPO3_DB']->sql_free_result($res);

		if(!empty($out)){
				$out = '<ul>'.$out.'</ul>';
		}

		return $out;
	}

	function getFeUserRegs($feuser){

		if(intval($feuser) > 0 && !empty($feuser)){
			$regs = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('uid',$this->participantsTable,$this->participantsTable.'.feuser_id IN ('.$feuser.') AND '.$this->participantsTable.'.pid IN ('.$this->getTSFFvar('pidList').')'.$this->cObj->enableFields($this->participantsTable));

			if(empty($regs)){
				return 0;
			} else {
				return count($regs);
			}
		} else {
			return false;
		}

	}


	/**
	 * Gets all Papers of a congress registration and creates a listView for all congress records connected
	 *
	 *
	 */
	function renderPapers($addWhere='',$PID,$userReg){
		if($GLOBALS['TSFE']->fe_user->user){
			$tmpConf = $this->conf;
			$tmpTemplate = $this->template;
			$tmpTable = $this->tableName;
			$tmpOrder = $this->manualFieldOrder_list;
			$tmpEntitiesFields = $this->skipHtmlEntitiesFields;

			$this->template = $this->cObj->getSubpart($this->template,'###papersList###');
			$this->tableName = $this->participantsTable;
			$this->conf = $this->conf['paperList.'];
			$this->conf['pidList'] = $PID;
			$this->manualFieldOrder_list = t3lib_div::trimExplode(',',$this->conf['field_orderList'],1);

			// do not show download-links if not payed
			if($userReg['payed'] != 1){
				$files = array_search('dl_files',$this->manualFieldOrder_list);
				if($files !== false){
					unset($this->manualFieldOrder_list[$files]);
				}
			}

			$this->internal['currentTable'] = $this->tableName;
			$this->skipHtmlEntitiesFields = explode(',',$this->conf['skipHtmlEntitiesFields']);

			$paperList = parent::listView($addWhere);

			$this->tableName = $tmpTable;
			$this->template = $tmpTemplate;
			$this->conf = $tmpConf;
			$this->manualFieldOrder_list = $tmpOrder;
			$this->internal['currentTable'] = $this->tableName;

			 return $paperList;
		} else {
			return '';
		}
	}

	/**
	 * Displays a profile page with fe-user-info, participations and paypal-link
	 *
	 */
	function profilePage(){
		$feuser = $GLOBALS['TSFE']->fe_user->user;

		if(intval($feuser['uid']) > 0){
			// Get all participations from connected fe-user as audience
			$audience = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->participantsTable,$this->participantsTable.'.feuser_id = '.$feuser['uid'].' AND '.$this->participantsTable.'.pid IN ('.$this->getTSFFvar('pidList').') AND '.$this->participantsTable.'.type = 0'.$this->cObj->enableFields($this->participantsTable));

			// Get all participations from connected fe-user as speaker
			$speaker = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*',$this->participantsTable,$this->participantsTable.'.feuser_id = '.$feuser['uid'].' AND '.$this->participantsTable.'.pid IN ('.$this->getTSFFvar('pidList').') AND '.$this->participantsTable.'.type = 1'.$this->cObj->enableFields($this->participantsTable));

			// Get templates
			if(!empty($this->conf['profileView.']['template'])){
				$this->template = $this->cObj->fileResource($this->conf['profileView.']['template']);

				if ($this->template == '') {
					return 'No template for profile view found. File: '.$this->conf['profileView.']['templateFile'];
				}
			}

			// set vars for pi_list_makelist()
			$tmpTable = $this->internal['currentTable'];
			$this->internal['currentTable'] = $this->participantsTable;

			// handle audiences
			$this->listT = $this->cObj->getSubpart($this->template,'###audienceList###');
			if(!empty($this->conf['profileView.']['showAudienceFields'])){
				$this->manualFieldOrder_list = explode(',',$this->conf['profileView.']['showAudienceFields']);
			} else {
				$this->manualFieldOrder_list = array('congress_id');
			}
			$mArr['audience'] = $this->pi_list_makelist($audience);
			if($GLOBALS['TYPO3_DB']->sql_num_rows($audience) == 0){
				$audReg = 0;
				$mArr['audience'] = $this->pi_getLL('noResultFound');
			} else {
				$audReg = $GLOBALS['TYPO3_DB']->sql_num_rows($audience);
			}

			// handle speaker registrations
			$this->listT = $this->cObj->getSubpart($this->template,'###speakerList###');
			if(!empty($this->conf['profileView.']['showSpeakerFields'])){
				$this->manualFieldOrder_list = explode(',',$this->conf['profileView.']['showSpeakerFields']);
			} else {
				$this->manualFieldOrder_list = array('congress_id');
			}
			$mArr['speaker'] = $this->pi_list_makelist($speaker);
			if($GLOBALS['TYPO3_DB']->sql_num_rows($speaker) == 0){
				$spReg = 0;
				$mArr['speaker'] = $this->pi_getLL('noResultFound');
			} else {
				$spReg = $GLOBALS['TYPO3_DB']->sql_num_rows($speaker);
			}

			// reset vars from pi_list_makelist()
			$this->internal['currentTable'] = $tmpTable;

			// get FE-User info
			$feUserTmpl = $this->cObj->getSubpart($this->template,'###feUserInfo###');
			$mArr['feUser'] = $this->cObj->substituteMarkerArray($feUserTmpl,$feuser,'###|###');

			// get Payment-Infos
			$payed = false;
			$GLOBALS['TYPO3_DB']->sql_data_seek($speaker,0);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($speaker)){
				if($row['payed'] == 1){
					$payed = true;
					$payArr[] = $row;
				}
			}

			$GLOBALS['TYPO3_DB']->sql_data_seek($audience,0);
			while($row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($audience)){
				if($row['payed'] == 1){
					$payed = true;
					$payArr[] = $row;
				}
			}

			if($payed == false){
				$payTmpl = $this->cObj->getSubpart($this->template,'###paymentInfo###');
				foreach(explode(',',$this->conf['profileView.']['additionalJS']) as $jsFile){
					$pArr['additionalJS'] .= '<script type="text/javascript" src="'.$jsFile.'"></script>';
				}
				$pArr['feUserId'] = $feuser['uid'];

				if($spReg > 0 || $audReg > 0){
					$mArr['payment'] = $this->cObj->substituteMarkerArray($payTmpl,$pArr,'###|###');
				} else {
					$mArr['payment'] = '';
				}
				$mArr['upload'] = '';
			} else {
				$mArr['payment'] = '<p class="paypal-payed">Sie haben bereits bezahlt, Danke!<br />You have already paid, thank you!</p>';
				$mArr['upload'] = $this->getFileUploadTemplate($payArr);
			}

			// prepare final template
			$profileTmpl = $this->cObj->getSubpart($this->template,'###profile###');

			return $this->cObj->substituteMarkerArray($profileTmpl,$mArr,'###|###');

		} else {
			return 'No data for the logged in FE-User available.';
		}
	}

	function getFileUploadTemplate($registrations){

		// Get templates
			if(!empty($this->conf['profileView.']['template'])){
				$this->template = $this->cObj->fileResource($this->conf['profileView.']['template']);

				if ($this->template == '') {
					return 'No template for profile view found. File: '.$this->conf['profileView.']['templateFile'];
				}
			}

		$tmpl = $this->cObj->getSubpart($this->template,'###fileUpload###');
		$title = '<div class="fileupload"><h2>Vortrag Upload</h2>';
		foreach($registrations as $reg){
			if($reg['type'] == 1){
				if(!empty($reg['uid']) && empty($reg['dl_files'])){
					$fuTemp['regUid'] = $reg['uid'];
					$fuTemp['formAction'] = $this->pi_linkTP_keepPIvars_url(array('action'=>'postPaymentUpload','regUid'=>$reg['uid']));
					$cName = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('name',$this->tableName,'uid IN ('.intval($reg['congress_id']).')');
					$fuTemp['congressName'] = $cName[0]['name'];
					$t[] = $this->cObj->substituteMarkerArray($tmpl,$fuTemp,'###|###');
				} else {
					$t[] = '<p>Sie haben bereits eine Datei hochgeladen.</p>';
				}
			}
		}

		if(count($t) < 1){
			return '';
		}

		return $title.implode('',$t).'</div>';
	}

	function postPaymentUpload(){
	// Get templates
			if(!empty($this->conf['profileView.']['template'])){
				$this->template = $this->cObj->fileResource($this->conf['profileView.']['template']);

				if ($this->template == '') {
					return 'No template for profile view found. File: '.$this->conf['profileView.']['templateFile'];
				}
			}

		$regUid = $this->piVars['regUid'];

		require_once(PATH_t3lib.'class.t3lib_extfilefunc.php');

		// conf array
		$cmds['data'] = $regUid;
		$cmds['target'] = 'uploads/tx_x4econgress/';

		// allow/deny pattern
		$f_ext['webspace']['allow']='';
		$f_ext['webspace']['deny']= '*';
		$f_ext['ftpspace']['allow']='jpg,jpeg,png,gif,pdf,doc,docx';
		$f_ext['ftpspace']['deny']='*';

		// make instance
		$fileInst = t3lib_div::makeInstance('t3lib_extFileFunctions');
		$fileInst->init(array("path" => $cmds['target']),$f_ext);
		$fileInst->actionPerms['uploadFile'] = 1;
		$newFile = $fileInst->func_upload($cmds);

		$mArr = array();

		if(!empty($newFile)){
			$newFile = array_reverse(explode('/',$newFile));
			$newFile = $newFile[0];
			$GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->participantsTable,'uid = '.$regUid,array('dl_files' => $newFile));
			$mArr['SUCCESS'] = 'Ihre Datei wurde erfolgreich hochgeladen.';
			$mArr['FAILURE'] = '';
			$mArr['BackLink'] = $this->pi_linkTP_keepPIvars_url(array('action' => null,'regUid' => null));
			$mArr['BackLinkLabel'] = $this->pi_getLL('back');
			return $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->template,'###finishFileUpload###'),$mArr,'###|###');
		} else {
			$mArr['SUCCESS'] = '';
			$mArr['FAILURE'] = 'Das Hochladen der Datei hat leider nicht funktioniert. Probieren Sie es noch einmal.';
			$mArr['BackLink'] = $this->pi_linkTP_keepPIvars_url(array('action' => null,'regUid' => null));
			$mArr['BackLinkLabel'] = $this->pi_getLL('back');
			return $this->cObj->substituteMarkerArray($this->cObj->getSubpart($this->template,'###finishFileUpload###'),$mArr,'###|###');
		}
	}

	/**
	 * Process PayPal-IPN with log and user-reg
	 *
	 */
	function payPalResponse(){
		$logStr = "";
		if(!empty($this->conf['ppIpnLogFile'])){
			$logFd = fopen(PATH_site.$this->conf['ppIpnLogFile'], "a");
		} else {
			return;
		}


		fwrite($logFd, "****************************************************************************************************\n");

		if(array_key_exists("txn_id", $_POST)) {
			$logStr = "Received IPN,  TX ID : ".htmlspecialchars($_POST["txn_id"]);
			fwrite($logFd, strftime("%d %b %Y %H:%M:%S ")."[x4econgress-IPN-Listener] $logStr\n");
		} else {
			$logStr = "IPN Listner received an HTTP request with out a Transaction ID.";
			fwrite($logFd, strftime("%d %b %Y %H:%M:%S ")."[x4econgress-IPN-Listener] $logStr\n");
			fclose($logFd);
		}

		$tmpAr = array_merge($_POST, array("cmd" => "_notify-validate"));
		$postFieldsAr = array();
		foreach ($tmpAr as $name => $value) {
			$postFieldsAr[] = "$name=$value";
		}
		$logStr = "Sending IPN values:\n".implode("\n", $postFieldsAr);
		fwrite($logFd, strftime("%d %b %Y %H:%M:%S ")."[x4econgress-IPN-Listener] $logStr\n");

		$ppResponseAr = $this->PPHttpPost("https://www.paypal.com/cgi-bin/webscr", implode("&", $postFieldsAr), false);
		if(!$ppResponseAr["status"]) {
			fwrite($logFd, "--------------------\n");
			$logStr = "IPN Listner received an Error:\n";
			if(0 !== $ppResponseAr["error_no"]) {
				$logStr .= "Error ".$ppResponseAr["error_no"].": ";
			}
			$logStr .= $ppResponseAr["error_msg"];
			fwrite($logFd, strftime("%d %b %Y %H:%M:%S ")."[x4econgress-IPN-Listener] $logStr\n");
			fclose($logFd);
		}

		fwrite($logFd, "--------------------\n");
		$logStr = "IPN Post Response:\n".$ppResponseAr["httpResponse"];
		fwrite($logFd, strftime("%d %b %Y %H:%M:%S ")."[x4econgress-IPN-Listener] $logStr\n");

		$success = $this->registerUserPayment($tmpAr);
		fwrite($logFd, "******** Congress Registration *********\n");
		fwrite($logFd, strftime("%d %b %Y %H:%M:%S ")."[x4econgress-IPN-Listener] $success\n");

		fclose($logFd);
	}

	function registerUserPayment($fields){
		if(intval($fields['test_ipn']) == 1){
			return "This was a Sandbox IPN!";
		} else {
			// extract custom data
			//$fields['payment_status'] = 'completed';

			if(strtolower($fields['payment_status']) == 'completed'){

				$userData = explode('|',$fields['custom']);
				foreach($userData as $data){
					$data = explode(':',$data);
					$tmpData[$data[0]] = $data[1];
				}

				// save custom data in prepared db-fields
					$this->conf['storeCustomPPVars'] = explode(',',$this->conf['storeCustomPPVars']);
					if(sizeof($this->conf['storeCustomPPVars']) > 0){
						foreach($this->conf['storeCustomPPVars'] as $custom){
							$save[$custom] = $tmpData[$custom	];
						}
					}

				$save['custom'] = $fields['custom'];
				$save['payed'] = 1;

				if($tmpData['feuser']){
					$save = $GLOBALS['TYPO3_DB']->exec_UPDATEquery($this->participantsTable,'feuser_id = '.$tmpData['feuser'],$save);
				}

				if($GLOBALS['TYPO3_DB']->sql_affected_rows() > 0){
					foreach($save as $k => $v){
						$tmpVar .= $k.'='.$v.'|';
					}
					return "Registration for FE-User ".$tmpData['feuser']." Updated!\n$tmpVar";
				} else {
					return "Registration Update for FE-User ".$tmpData['feuser']." failed!\n".$GLOBALS['TYPO3_DB']->UPDATEquery($this->participantsTable,'feuser_id = '.$tmpData['feuser'],$save);
				}
			} else {
				return "Payment not completed. Registration not updated!";

			}

		}
	}

	/**
	 * Send HTTP POST Request
	 *
	 * @param	string	The request URL
	 * @param	string	The POST Message fields in &name=value pair format
	 * @param	bool		determines whether to return a parsed array (true) or a raw array (false)
	 * @return	array		Contains a bool status, error_msg, error_no,
	 *				and the HTTP Response body(parsed=httpParsedResponseAr  or non-parsed=httpResponse) if successful
	 *
	 * @access	public
	 * @static
	 */
	function PPHttpPost($url_, $postFields_, $parsed_)
	{
		//setting the curl parameters.
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL,$url_);
		curl_setopt($ch, CURLOPT_VERBOSE, 1);

		//turning off the server and peer verification(TrustManager Concept).
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

		curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
		curl_setopt($ch, CURLOPT_POST, 1);

		//setting the nvpreq as POST FIELD to curl
		curl_setopt($ch,CURLOPT_POSTFIELDS,$postFields_);

		//getting response from server
		$httpResponse = curl_exec($ch);

		if(!$httpResponse) {
			return array("status" => false, "error_msg" => curl_error($ch), "error_no" => curl_errno($ch));
		}

		if(!$parsed_) {
			return array("status" => true, "httpResponse" => $httpResponse);
		}

		$httpResponseAr = explode("\n", $httpResponse);

		$httpParsedResponseAr = array();
		foreach ($httpResponseAr as $i => $value) {
			$tmpAr = explode("=", $value);
			if(sizeof($tmpAr) > 1) {
				$httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
			}
		}

		if(0 == sizeof($httpParsedResponseAr)) {
			$error = "Invalid HTTP Response for POST request($postFields_) to $url_.";
			return array("status" => false, "error_msg" => $error, "error_no" => 0);
		}
		return array("status" => true, "httpParsedResponseAr" => $httpParsedResponseAr);

	} // PPHttpPost



}



if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4econgress/pi1/class.tx_x4econgress_pi1.php'])	{
	include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/x4econgress/pi1/class.tx_x4econgress_pi1.php']);
}

?>