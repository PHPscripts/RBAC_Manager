<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg9.php" ?>
<?php include_once "ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "accountinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$account_add = NULL; // Initialize page object first

class caccount_add extends caccount {

	// Page ID
	var $PageID = 'add';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'account';

	// Page object name
	var $PageObjName = 'account_add';

	// Page name
	function PageName() {
		return ew_CurrentPage();
	}

	// Page URL
	function PageUrl() {
		$PageUrl = ew_CurrentPage() . "?";
		if ($this->UseTokenInUrl) $PageUrl .= "t=" . $this->TableVar . "&"; // Add page token
		return $PageUrl;
	}

	// Message
	function getMessage() {
		return @$_SESSION[EW_SESSION_MESSAGE];
	}

	function setMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $v);
	}

	function getFailureMessage() {
		return @$_SESSION[EW_SESSION_FAILURE_MESSAGE];
	}

	function setFailureMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $v);
	}

	function getSuccessMessage() {
		return @$_SESSION[EW_SESSION_SUCCESS_MESSAGE];
	}

	function setSuccessMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_SUCCESS_MESSAGE], $v);
	}

	function getWarningMessage() {
		return @$_SESSION[EW_SESSION_WARNING_MESSAGE];
	}

	function setWarningMessage($v) {
		ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $v);
	}

	// Show message
	function ShowMessage() {
		$hidden = FALSE;
		$html = "";

		// Message
		$sMessage = $this->getMessage();
		$this->Message_Showing($sMessage, "");
		if ($sMessage <> "") { // Message in Session, display
			$html .= "<p class=\"ewMessage\">" . $sMessage . "</p>";
			$_SESSION[EW_SESSION_MESSAGE] = ""; // Clear message in Session
		}

		// Warning message
		$sWarningMessage = $this->getWarningMessage();
		$this->Message_Showing($sWarningMessage, "warning");
		if ($sWarningMessage <> "") { // Message in Session, display
			$html .= "<table class=\"ewMessageTable\"><tr><td class=\"ewWarningIcon\"></td><td class=\"ewWarningMessage\">" . $sWarningMessage . "</td></tr></table>";
			$_SESSION[EW_SESSION_WARNING_MESSAGE] = ""; // Clear message in Session
		}

		// Success message
		$sSuccessMessage = $this->getSuccessMessage();
		$this->Message_Showing($sSuccessMessage, "success");
		if ($sSuccessMessage <> "") { // Message in Session, display
			$html .= "<table class=\"ewMessageTable\"><tr><td class=\"ewSuccessIcon\"></td><td class=\"ewSuccessMessage\">" . $sSuccessMessage . "</td></tr></table>";
			$_SESSION[EW_SESSION_SUCCESS_MESSAGE] = ""; // Clear message in Session
		}

		// Failure message
		$sErrorMessage = $this->getFailureMessage();
		$this->Message_Showing($sErrorMessage, "failure");
		if ($sErrorMessage <> "") { // Message in Session, display
			$html .= "<table class=\"ewMessageTable\"><tr><td class=\"ewErrorIcon\"></td><td class=\"ewErrorMessage\">" . $sErrorMessage . "</td></tr></table>";
			$_SESSION[EW_SESSION_FAILURE_MESSAGE] = ""; // Clear message in Session
		}
		echo "<div class=\"ewMessageDialog\"" . (($hidden) ? " style=\"display: none;\"" : "") . ">" . $html . "</div>";
	}
	var $PageHeader;
	var $PageFooter;

	// Show Page Header
	function ShowPageHeader() {
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		if ($sHeader <> "") { // Header exists, display
			echo "<p class=\"phpmaker\">" . $sHeader . "</p>";
		}
	}

	// Show Page Footer
	function ShowPageFooter() {
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		if ($sFooter <> "") { // Fotoer exists, display
			echo "<p class=\"phpmaker\">" . $sFooter . "</p>";
		}
	}

	// Validate page request
	function IsPageRequest() {
		global $objForm;
		if ($this->UseTokenInUrl) {
			if ($objForm)
				return ($this->TableVar == $objForm->GetValue("t"));
			if (@$_GET["t"] <> "")
				return ($this->TableVar == $_GET["t"]);
		} else {
			return TRUE;
		}
	}

	//
	// Page class constructor
	//
	function __construct() {
		global $conn, $Language, $UserAgent;

		// User agent
		$UserAgent = ew_UserAgent();
		$GLOBALS["Page"] = &$this;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();

		// Parent constuctor
		parent::__construct();

		// Table object (account)
		if (!isset($GLOBALS["account"])) {
			$GLOBALS["account"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["account"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'add', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'account', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();
	}

	// 
	//  Page_Init
	//
	function Page_Init() {
		global $gsExport, $gsExportFile, $UserProfile, $Language, $Security, $objForm;

		// Security
		$Security = new cAdvancedSecurity();
		if (!$Security->IsLoggedIn()) $Security->AutoLogin();
		if (!$Security->IsLoggedIn()) {
			$Security->SaveLastUrl();
			$this->Page_Terminate("login.php");
		}

		// Create form object
		$objForm = new cFormObj();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"];

		// Global Page Loading event (in userfn*.php)
		Page_Loading();

		// Page Load event
		$this->Page_Load();
	}

	//
	// Page_Terminate
	//
	function Page_Terminate($url = "") {
		global $conn;

		// Page Unload event
		$this->Page_Unload();

		// Global Page Unloaded event (in userfn*.php)
		Page_Unloaded();
		$this->Page_Redirecting($url);

		 // Close connection
		$conn->Close();

		// Go to URL if specified
		if ($url <> "") {
			if (!EW_DEBUG_ENABLED && ob_get_length())
				ob_end_clean();
			header("Location: " . $url);
		}
		exit();
	}
	var $DbMasterFilter = "";
	var $DbDetailFilter = "";
	var $Priv = 0;
	var $OldRecordset;
	var $CopyRecord;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Process form if post back
		if (@$_POST["a_add"] <> "") {
			$this->CurrentAction = $_POST["a_add"]; // Get form action
			$this->CopyRecord = $this->LoadOldRecord(); // Load old recordset
			$this->LoadFormValues(); // Load form values
		} else { // Not post back

			// Load key values from QueryString
			$this->CopyRecord = TRUE;
			if (@$_GET["id"] != "") {
				$this->id->setQueryStringValue($_GET["id"]);
				$this->setKey("id", $this->id->CurrentValue); // Set up key
			} else {
				$this->setKey("id", ""); // Clear key
				$this->CopyRecord = FALSE;
			}
			if ($this->CopyRecord) {
				$this->CurrentAction = "C"; // Copy record
			} else {
				$this->CurrentAction = "I"; // Display blank record
				$this->LoadDefaultValues(); // Load default values
			}
		}

		// Validate form if post back
		if (@$_POST["a_add"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = "I"; // Form error, reset action
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues(); // Restore form values
				$this->setFailureMessage($gsFormError);
			}
		}

		// Perform action based on action code
		switch ($this->CurrentAction) {
			case "I": // Blank record, no action required
				break;
			case "C": // Copy an existing record
				if (!$this->LoadRow()) { // Load record based on key
					if ($this->getFailureMessage() == "") $this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
					$this->Page_Terminate("accountlist.php"); // No matching record, return to list
				}
				break;
			case "A": // Add new record
				$this->SendEmail = TRUE; // Send email on add success
				if ($this->AddRow($this->OldRecordset)) { // Add successful
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("AddSuccess")); // Set up success message
					$sReturnUrl = $this->getReturnUrl();
					if (ew_GetPageName($sReturnUrl) == "accountview.php")
						$sReturnUrl = $this->GetViewUrl(); // View paging, return to view page with keyurl directly
					$this->Page_Terminate($sReturnUrl); // Clean up and return
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Add failed, restore form values
				}
		}

		// Render row based on row type
		$this->RowType = EW_ROWTYPE_ADD;  // Render add type

		// Render row
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Get upload files
	function GetUploadFiles() {
		global $objForm;

		// Get upload data
		$index = $objForm->Index; // Save form index
		$objForm->Index = -1;
		$confirmPage = (strval($objForm->GetValue("a_confirm")) <> "");
		$objForm->Index = $index; // Restore form index
	}

	// Load default values
	function LoadDefaultValues() {
		$this->username->CurrentValue = NULL;
		$this->username->OldValue = $this->username->CurrentValue;
		$this->_email->CurrentValue = NULL;
		$this->_email->OldValue = $this->_email->CurrentValue;
		$this->reg_mail->CurrentValue = NULL;
		$this->reg_mail->OldValue = $this->reg_mail->CurrentValue;
		$this->locked->CurrentValue = 0;
		$this->lock_country->CurrentValue = "00";
		$this->expansion->CurrentValue = 2;
		$this->mutetime->CurrentValue = 0;
		$this->mutereason->CurrentValue = NULL;
		$this->mutereason->OldValue = $this->mutereason->CurrentValue;
		$this->muteby->CurrentValue = NULL;
		$this->muteby->OldValue = $this->muteby->CurrentValue;
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->username->FldIsDetailKey) {
			$this->username->setFormValue($objForm->GetValue("x_username"));
		}
		if (!$this->_email->FldIsDetailKey) {
			$this->_email->setFormValue($objForm->GetValue("x__email"));
		}
		if (!$this->reg_mail->FldIsDetailKey) {
			$this->reg_mail->setFormValue($objForm->GetValue("x_reg_mail"));
		}
		if (!$this->locked->FldIsDetailKey) {
			$this->locked->setFormValue($objForm->GetValue("x_locked"));
		}
		if (!$this->lock_country->FldIsDetailKey) {
			$this->lock_country->setFormValue($objForm->GetValue("x_lock_country"));
		}
		if (!$this->expansion->FldIsDetailKey) {
			$this->expansion->setFormValue($objForm->GetValue("x_expansion"));
		}
		if (!$this->mutetime->FldIsDetailKey) {
			$this->mutetime->setFormValue($objForm->GetValue("x_mutetime"));
		}
		if (!$this->mutereason->FldIsDetailKey) {
			$this->mutereason->setFormValue($objForm->GetValue("x_mutereason"));
		}
		if (!$this->muteby->FldIsDetailKey) {
			$this->muteby->setFormValue($objForm->GetValue("x_muteby"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadOldRecord();
		$this->username->CurrentValue = $this->username->FormValue;
		$this->_email->CurrentValue = $this->_email->FormValue;
		$this->reg_mail->CurrentValue = $this->reg_mail->FormValue;
		$this->locked->CurrentValue = $this->locked->FormValue;
		$this->lock_country->CurrentValue = $this->lock_country->FormValue;
		$this->expansion->CurrentValue = $this->expansion->FormValue;
		$this->mutetime->CurrentValue = $this->mutetime->FormValue;
		$this->mutereason->CurrentValue = $this->mutereason->FormValue;
		$this->muteby->CurrentValue = $this->muteby->FormValue;
	}

	// Load row based on key values
	function LoadRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();

		// Call Row Selecting event
		$this->Row_Selecting($sFilter);

		// Load SQL based on filter
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$res = FALSE;
		$rs = ew_LoadRecordset($sSql);
		if ($rs && !$rs->EOF) {
			$res = TRUE;
			$this->LoadRowValues($rs); // Load row values
			$rs->Close();
		}
		return $res;
	}

	// Load row values from recordset
	function LoadRowValues(&$rs) {
		global $conn;
		if (!$rs || $rs->EOF) return;

		// Call Row Selected event
		$row = &$rs->fields;
		$this->Row_Selected($row);
		$this->id->setDbValue($rs->fields('id'));
		$this->username->setDbValue($rs->fields('username'));
		$this->sha_pass_hash->setDbValue($rs->fields('sha_pass_hash'));
		$this->sessionkey->setDbValue($rs->fields('sessionkey'));
		$this->v->setDbValue($rs->fields('v'));
		$this->s->setDbValue($rs->fields('s'));
		$this->token_key->setDbValue($rs->fields('token_key'));
		$this->_email->setDbValue($rs->fields('email'));
		$this->reg_mail->setDbValue($rs->fields('reg_mail'));
		$this->joindate->setDbValue($rs->fields('joindate'));
		$this->last_ip->setDbValue($rs->fields('last_ip'));
		$this->failed_logins->setDbValue($rs->fields('failed_logins'));
		$this->locked->setDbValue($rs->fields('locked'));
		$this->lock_country->setDbValue($rs->fields('lock_country'));
		$this->last_login->setDbValue($rs->fields('last_login'));
		$this->online->setDbValue($rs->fields('online'));
		$this->expansion->setDbValue($rs->fields('expansion'));
		$this->mutetime->setDbValue($rs->fields('mutetime'));
		$this->mutereason->setDbValue($rs->fields('mutereason'));
		$this->muteby->setDbValue($rs->fields('muteby'));
		$this->locale->setDbValue($rs->fields('locale'));
		$this->os->setDbValue($rs->fields('os'));
		$this->recruiter->setDbValue($rs->fields('recruiter'));
	}

	// Load old record
	function LoadOldRecord() {

		// Load key values from Session
		$bValidKey = TRUE;
		if (strval($this->getKey("id")) <> "")
			$this->id->CurrentValue = $this->getKey("id"); // id
		else
			$bValidKey = FALSE;

		// Load old recordset
		if ($bValidKey) {
			$this->CurrentFilter = $this->KeyFilter();
			$sSql = $this->SQL();
			$this->OldRecordset = ew_LoadRecordset($sSql);
			$this->LoadRowValues($this->OldRecordset); // Load row values
		} else {
			$this->OldRecordset = NULL;
		}
		return $bValidKey;
	}

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		// Call Row_Rendering event

		$this->Row_Rendering();

		// Common render codes for all row types
		// id
		// username
		// sha_pass_hash
		// sessionkey
		// v
		// s
		// token_key
		// email
		// reg_mail
		// joindate
		// last_ip
		// failed_logins
		// locked
		// lock_country
		// last_login
		// online
		// expansion
		// mutetime
		// mutereason
		// muteby
		// locale
		// os
		// recruiter

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// username
			$this->username->ViewValue = $this->username->CurrentValue;
			$this->username->ViewCustomAttributes = "";

			// email
			$this->_email->ViewValue = $this->_email->CurrentValue;
			$this->_email->ViewCustomAttributes = "";

			// reg_mail
			$this->reg_mail->ViewValue = $this->reg_mail->CurrentValue;
			$this->reg_mail->ViewCustomAttributes = "";

			// joindate
			$this->joindate->ViewValue = $this->joindate->CurrentValue;
			$this->joindate->ViewValue = ew_FormatDateTime($this->joindate->ViewValue, 9);
			$this->joindate->ViewCustomAttributes = "";

			// last_ip
			$this->last_ip->ViewValue = $this->last_ip->CurrentValue;
			$this->last_ip->ViewCustomAttributes = "";

			// locked
			$this->locked->ViewValue = $this->locked->CurrentValue;
			$this->locked->ViewCustomAttributes = "";

			// lock_country
			$this->lock_country->ViewValue = $this->lock_country->CurrentValue;
			$this->lock_country->ViewCustomAttributes = "";

			// online
			if (strval($this->online->CurrentValue) <> "") {
				switch ($this->online->CurrentValue) {
					case $this->online->FldTagValue(1):
						$this->online->ViewValue = $this->online->FldTagCaption(1) <> "" ? $this->online->FldTagCaption(1) : $this->online->CurrentValue;
						break;
					case $this->online->FldTagValue(2):
						$this->online->ViewValue = $this->online->FldTagCaption(2) <> "" ? $this->online->FldTagCaption(2) : $this->online->CurrentValue;
						break;
					default:
						$this->online->ViewValue = $this->online->CurrentValue;
				}
			} else {
				$this->online->ViewValue = NULL;
			}
			$this->online->ViewCustomAttributes = "";

			// expansion
			if (strval($this->expansion->CurrentValue) <> "") {
				switch ($this->expansion->CurrentValue) {
					case $this->expansion->FldTagValue(1):
						$this->expansion->ViewValue = $this->expansion->FldTagCaption(1) <> "" ? $this->expansion->FldTagCaption(1) : $this->expansion->CurrentValue;
						break;
					case $this->expansion->FldTagValue(2):
						$this->expansion->ViewValue = $this->expansion->FldTagCaption(2) <> "" ? $this->expansion->FldTagCaption(2) : $this->expansion->CurrentValue;
						break;
					case $this->expansion->FldTagValue(3):
						$this->expansion->ViewValue = $this->expansion->FldTagCaption(3) <> "" ? $this->expansion->FldTagCaption(3) : $this->expansion->CurrentValue;
						break;
					case $this->expansion->FldTagValue(4):
						$this->expansion->ViewValue = $this->expansion->FldTagCaption(4) <> "" ? $this->expansion->FldTagCaption(4) : $this->expansion->CurrentValue;
						break;
					case $this->expansion->FldTagValue(5):
						$this->expansion->ViewValue = $this->expansion->FldTagCaption(5) <> "" ? $this->expansion->FldTagCaption(5) : $this->expansion->CurrentValue;
						break;
					default:
						$this->expansion->ViewValue = $this->expansion->CurrentValue;
				}
			} else {
				$this->expansion->ViewValue = NULL;
			}
			$this->expansion->ViewCustomAttributes = "";

			// mutetime
			$this->mutetime->ViewValue = $this->mutetime->CurrentValue;
			$this->mutetime->ViewCustomAttributes = "";

			// mutereason
			$this->mutereason->ViewValue = $this->mutereason->CurrentValue;
			$this->mutereason->ViewCustomAttributes = "";

			// muteby
			$this->muteby->ViewValue = $this->muteby->CurrentValue;
			$this->muteby->ViewCustomAttributes = "";

			// username
			$this->username->LinkCustomAttributes = "";
			$this->username->HrefValue = "";
			$this->username->TooltipValue = "";

			// email
			$this->_email->LinkCustomAttributes = "";
			$this->_email->HrefValue = "";
			$this->_email->TooltipValue = "";

			// reg_mail
			$this->reg_mail->LinkCustomAttributes = "";
			$this->reg_mail->HrefValue = "";
			$this->reg_mail->TooltipValue = "";

			// locked
			$this->locked->LinkCustomAttributes = "";
			$this->locked->HrefValue = "";
			$this->locked->TooltipValue = "";

			// lock_country
			$this->lock_country->LinkCustomAttributes = "";
			$this->lock_country->HrefValue = "";
			$this->lock_country->TooltipValue = "";

			// expansion
			$this->expansion->LinkCustomAttributes = "";
			$this->expansion->HrefValue = "";
			$this->expansion->TooltipValue = "";

			// mutetime
			$this->mutetime->LinkCustomAttributes = "";
			$this->mutetime->HrefValue = "";
			$this->mutetime->TooltipValue = "";

			// mutereason
			$this->mutereason->LinkCustomAttributes = "";
			$this->mutereason->HrefValue = "";
			$this->mutereason->TooltipValue = "";

			// muteby
			$this->muteby->LinkCustomAttributes = "";
			$this->muteby->HrefValue = "";
			$this->muteby->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_ADD) { // Add row

			// username
			$this->username->EditCustomAttributes = "";
			$this->username->EditValue = ew_HtmlEncode($this->username->CurrentValue);

			// email
			$this->_email->EditCustomAttributes = "";
			$this->_email->EditValue = ew_HtmlEncode($this->_email->CurrentValue);

			// reg_mail
			$this->reg_mail->EditCustomAttributes = "";
			$this->reg_mail->EditValue = ew_HtmlEncode($this->reg_mail->CurrentValue);

			// locked
			$this->locked->EditCustomAttributes = "";
			$this->locked->EditValue = ew_HtmlEncode($this->locked->CurrentValue);

			// lock_country
			$this->lock_country->EditCustomAttributes = "";
			$this->lock_country->EditValue = ew_HtmlEncode($this->lock_country->CurrentValue);

			// expansion
			$this->expansion->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->expansion->FldTagValue(1), $this->expansion->FldTagCaption(1) <> "" ? $this->expansion->FldTagCaption(1) : $this->expansion->FldTagValue(1));
			$arwrk[] = array($this->expansion->FldTagValue(2), $this->expansion->FldTagCaption(2) <> "" ? $this->expansion->FldTagCaption(2) : $this->expansion->FldTagValue(2));
			$arwrk[] = array($this->expansion->FldTagValue(3), $this->expansion->FldTagCaption(3) <> "" ? $this->expansion->FldTagCaption(3) : $this->expansion->FldTagValue(3));
			$arwrk[] = array($this->expansion->FldTagValue(4), $this->expansion->FldTagCaption(4) <> "" ? $this->expansion->FldTagCaption(4) : $this->expansion->FldTagValue(4));
			$arwrk[] = array($this->expansion->FldTagValue(5), $this->expansion->FldTagCaption(5) <> "" ? $this->expansion->FldTagCaption(5) : $this->expansion->FldTagValue(5));
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect")));
			$this->expansion->EditValue = $arwrk;

			// mutetime
			$this->mutetime->EditCustomAttributes = "";
			$this->mutetime->EditValue = ew_HtmlEncode($this->mutetime->CurrentValue);

			// mutereason
			$this->mutereason->EditCustomAttributes = "";
			$this->mutereason->EditValue = ew_HtmlEncode($this->mutereason->CurrentValue);

			// muteby
			$this->muteby->EditCustomAttributes = "";
			$this->muteby->EditValue = ew_HtmlEncode($this->muteby->CurrentValue);

			// Edit refer script
			// username

			$this->username->HrefValue = "";

			// email
			$this->_email->HrefValue = "";

			// reg_mail
			$this->reg_mail->HrefValue = "";

			// locked
			$this->locked->HrefValue = "";

			// lock_country
			$this->lock_country->HrefValue = "";

			// expansion
			$this->expansion->HrefValue = "";

			// mutetime
			$this->mutetime->HrefValue = "";

			// mutereason
			$this->mutereason->HrefValue = "";

			// muteby
			$this->muteby->HrefValue = "";
		}
		if ($this->RowType == EW_ROWTYPE_ADD ||
			$this->RowType == EW_ROWTYPE_EDIT ||
			$this->RowType == EW_ROWTYPE_SEARCH) { // Add / Edit / Search row
			$this->SetupFieldTitles();
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Validate form
	function ValidateForm() {
		global $Language, $gsFormError;

		// Initialize form error message
		$gsFormError = "";

		// Check if validation required
		if (!EW_SERVER_VALIDATE)
			return ($gsFormError == "");
		if (!is_null($this->username->FormValue) && $this->username->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->username->FldCaption());
		}
		if (!is_null($this->_email->FormValue) && $this->_email->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->_email->FldCaption());
		}
		if (!is_null($this->reg_mail->FormValue) && $this->reg_mail->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->reg_mail->FldCaption());
		}
		if (!ew_CheckInteger($this->locked->FormValue)) {
			ew_AddMessage($gsFormError, $this->locked->FldErrMsg());
		}
		if (!is_null($this->expansion->FormValue) && $this->expansion->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->expansion->FldCaption());
		}
		if (!ew_CheckInteger($this->mutetime->FormValue)) {
			ew_AddMessage($gsFormError, $this->mutetime->FldErrMsg());
		}
		if (!is_null($this->mutereason->FormValue) && $this->mutereason->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->mutereason->FldCaption());
		}
		if (!is_null($this->muteby->FormValue) && $this->muteby->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->muteby->FldCaption());
		}

		// Return validate result
		$ValidateForm = ($gsFormError == "");

		// Call Form_CustomValidate event
		$sFormCustomError = "";
		$ValidateForm = $ValidateForm && $this->Form_CustomValidate($sFormCustomError);
		if ($sFormCustomError <> "") {
			ew_AddMessage($gsFormError, $sFormCustomError);
		}
		return $ValidateForm;
	}

	// Add record
	function AddRow($rsold = NULL) {
		global $conn, $Language, $Security;
		if ($this->username->CurrentValue <> "") { // Check field with unique index
			$sFilter = "(username = '" . ew_AdjustSql($this->username->CurrentValue) . "')";
			$rsChk = $this->LoadRs($sFilter);
			if ($rsChk && !$rsChk->EOF) {
				$sIdxErrMsg = str_replace("%f", $this->username->FldCaption(), $Language->Phrase("DupIndex"));
				$sIdxErrMsg = str_replace("%v", $this->username->CurrentValue, $sIdxErrMsg);
				$this->setFailureMessage($sIdxErrMsg);
				$rsChk->Close();
				return FALSE;
			}
		}
		$rsnew = array();

		// username
		$this->username->SetDbValueDef($rsnew, $this->username->CurrentValue, "", FALSE);

		// email
		$this->_email->SetDbValueDef($rsnew, $this->_email->CurrentValue, "", FALSE);

		// reg_mail
		$this->reg_mail->SetDbValueDef($rsnew, $this->reg_mail->CurrentValue, "", FALSE);

		// locked
		$this->locked->SetDbValueDef($rsnew, $this->locked->CurrentValue, 0, strval($this->locked->CurrentValue) == "");

		// lock_country
		$this->lock_country->SetDbValueDef($rsnew, $this->lock_country->CurrentValue, "", strval($this->lock_country->CurrentValue) == "");

		// expansion
		$this->expansion->SetDbValueDef($rsnew, $this->expansion->CurrentValue, 0, strval($this->expansion->CurrentValue) == "");

		// mutetime
		$this->mutetime->SetDbValueDef($rsnew, $this->mutetime->CurrentValue, 0, strval($this->mutetime->CurrentValue) == "");

		// mutereason
		$this->mutereason->SetDbValueDef($rsnew, $this->mutereason->CurrentValue, "", FALSE);

		// muteby
		$this->muteby->SetDbValueDef($rsnew, $this->muteby->CurrentValue, "", FALSE);

		// Call Row Inserting event
		$rs = ($rsold == NULL) ? NULL : $rsold->fields;
		$bInsertRow = $this->Row_Inserting($rs, $rsnew);
		if ($bInsertRow) {
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$AddRow = $this->Insert($rsnew);
			$conn->raiseErrorFn = '';
			if ($AddRow) {
			}
		} else {
			if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

				// Use the message, do nothing
			} elseif ($this->CancelMessage <> "") {
				$this->setFailureMessage($this->CancelMessage);
				$this->CancelMessage = "";
			} else {
				$this->setFailureMessage($Language->Phrase("InsertCancelled"));
			}
			$AddRow = FALSE;
		}

		// Get insert id if necessary
		if ($AddRow) {
			$this->id->setDbValue($conn->Insert_ID());
			$rsnew['id'] = $this->id->DbValue;
		}
		if ($AddRow) {

			// Call Row Inserted event
			$rs = ($rsold == NULL) ? NULL : $rsold->fields;
			$this->Row_Inserted($rs, $rsnew);
		}
		return $AddRow;
	}

	// Page Load event
	function Page_Load() {

		//echo "Page Load";
	}

	// Page Unload event
	function Page_Unload() {

		//echo "Page Unload";
	}

	// Page Redirecting event
	function Page_Redirecting(&$url) {

		// Example:
		//$url = "your URL";

	}

	// Message Showing event
	// $type = ''|'success'|'failure'|'warning'
	function Message_Showing(&$msg, $type) {
		if ($type == 'success') {

			//$msg = "your success message";
		} elseif ($type == 'failure') {

			//$msg = "your failure message";
		} elseif ($type == 'warning') {

			//$msg = "your warning message";
		} else {

			//$msg = "your message";
		}
	}

	// Page Data Rendering event
	function Page_DataRendering(&$header) {

		// Example:
		//$header = "your header";

	}

	// Page Data Rendered event
	function Page_DataRendered(&$footer) {

		// Example:
		//$footer = "your footer";

	}

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($account_add)) $account_add = new caccount_add();

// Page init
$account_add->Page_Init();

// Page main
$account_add->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var account_add = new ew_Page("account_add");
account_add.PageID = "add"; // Page ID
var EW_PAGE_ID = account_add.PageID; // For backward compatibility

// Form object
var faccountadd = new ew_Form("faccountadd");

// Validate form
faccountadd.Validate = function(fobj) {
	if (!this.ValidateRequired)
		return true; // Ignore validation
	fobj = fobj || this.Form;
	this.PostAutoSuggest();	
	if (fobj.a_confirm && fobj.a_confirm.value == "F")
		return true;
	var elm, aelm;
	var rowcnt = 1;
	var startcnt = (rowcnt == 0) ? 0 : 1; // rowcnt == 0 => Inline-Add
	for (var i = startcnt; i <= rowcnt; i++) {
		var infix = "";
		elm = fobj.elements["x" + infix + "_username"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account->username->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "__email"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account->_email->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_reg_mail"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account->reg_mail->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_locked"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($account->locked->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_expansion"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account->expansion->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_mutetime"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($account->mutetime->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_mutereason"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account->mutereason->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_muteby"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account->muteby->FldCaption()) ?>");

		// Set up row object
		ew_ElementsToRow(fobj, infix);

		// Fire Form_CustomValidate event
		if (!this.Form_CustomValidate(fobj))
			return false;
	}

	// Process detail page
	if (fobj.detailpage && fobj.detailpage.value && ewForms[fobj.detailpage.value])
		return ewForms[fobj.detailpage.value].Validate(fobj);
	return true;
}

// Form_CustomValidate event
faccountadd.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
faccountadd.ValidateRequired = true;
<?php } else { ?>
faccountadd.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Add") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $account->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $account->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $account_add->ShowPageHeader(); ?>
<?php
$account_add->ShowMessage();
?>
<form name="faccountadd" id="faccountadd" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post" onsubmit="return ewForms[this.id].Submit();">
<br>
<input type="hidden" name="t" value="account">
<input type="hidden" name="a_add" id="a_add" value="A">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_accountadd" class="ewTable">
<?php if ($account->username->Visible) { // username ?>
	<tr id="r_username"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_username"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->username->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account->username->CellAttributes() ?>><span id="el_account_username">
<input type="text" name="x_username" id="x_username" size="30" maxlength="32" value="<?php echo $account->username->EditValue ?>"<?php echo $account->username->EditAttributes() ?>>
</span><?php echo $account->username->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->_email->Visible) { // email ?>
	<tr id="r__email"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account__email"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->_email->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account->_email->CellAttributes() ?>><span id="el_account__email">
<input type="text" name="x__email" id="x__email" size="30" maxlength="255" value="<?php echo $account->_email->EditValue ?>"<?php echo $account->_email->EditAttributes() ?>>
</span><?php echo $account->_email->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->reg_mail->Visible) { // reg_mail ?>
	<tr id="r_reg_mail"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_reg_mail"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->reg_mail->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account->reg_mail->CellAttributes() ?>><span id="el_account_reg_mail">
<input type="text" name="x_reg_mail" id="x_reg_mail" size="30" maxlength="255" value="<?php echo $account->reg_mail->EditValue ?>"<?php echo $account->reg_mail->EditAttributes() ?>>
</span><?php echo $account->reg_mail->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->locked->Visible) { // locked ?>
	<tr id="r_locked"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_locked"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->locked->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->locked->CellAttributes() ?>><span id="el_account_locked">
<input type="text" name="x_locked" id="x_locked" size="30" value="<?php echo $account->locked->EditValue ?>"<?php echo $account->locked->EditAttributes() ?>>
</span><?php echo $account->locked->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->lock_country->Visible) { // lock_country ?>
	<tr id="r_lock_country"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_lock_country"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->lock_country->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->lock_country->CellAttributes() ?>><span id="el_account_lock_country">
<input type="text" name="x_lock_country" id="x_lock_country" size="30" maxlength="2" value="<?php echo $account->lock_country->EditValue ?>"<?php echo $account->lock_country->EditAttributes() ?>>
</span><?php echo $account->lock_country->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->expansion->Visible) { // expansion ?>
	<tr id="r_expansion"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_expansion"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->expansion->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account->expansion->CellAttributes() ?>><span id="el_account_expansion">
<select id="x_expansion" name="x_expansion"<?php echo $account->expansion->EditAttributes() ?>>
<?php
if (is_array($account->expansion->EditValue)) {
	$arwrk = $account->expansion->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($account->expansion->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
		if ($selwrk <> "") $emptywrk = FALSE;
?>
<option value="<?php echo ew_HtmlEncode($arwrk[$rowcntwrk][0]) ?>"<?php echo $selwrk ?>>
<?php echo $arwrk[$rowcntwrk][1] ?>
</option>
<?php
	}
}
?>
</select>
</span><?php echo $account->expansion->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->mutetime->Visible) { // mutetime ?>
	<tr id="r_mutetime"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_mutetime"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->mutetime->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->mutetime->CellAttributes() ?>><span id="el_account_mutetime">
<input type="text" name="x_mutetime" id="x_mutetime" size="30" value="<?php echo $account->mutetime->EditValue ?>"<?php echo $account->mutetime->EditAttributes() ?>>
</span><?php echo $account->mutetime->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->mutereason->Visible) { // mutereason ?>
	<tr id="r_mutereason"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_mutereason"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->mutereason->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account->mutereason->CellAttributes() ?>><span id="el_account_mutereason">
<input type="text" name="x_mutereason" id="x_mutereason" size="30" maxlength="255" value="<?php echo $account->mutereason->EditValue ?>"<?php echo $account->mutereason->EditAttributes() ?>>
</span><?php echo $account->mutereason->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account->muteby->Visible) { // muteby ?>
	<tr id="r_muteby"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_muteby"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->muteby->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account->muteby->CellAttributes() ?>><span id="el_account_muteby">
<input type="text" name="x_muteby" id="x_muteby" size="30" maxlength="50" value="<?php echo $account->muteby->EditValue ?>"<?php echo $account->muteby->EditAttributes() ?>>
</span><?php echo $account->muteby->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="btnAction" id="btnAction" value="<?php echo ew_BtnCaption($Language->Phrase("AddBtn")) ?>">
</form>
<script type="text/javascript">
faccountadd.Init();
</script>
<?php
$account_add->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$account_add->Page_Terminate();
?>
