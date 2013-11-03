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

$account_view = NULL; // Initialize page object first

class caccount_view extends caccount {

	// Page ID
	var $PageID = 'view';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'account';

	// Page object name
	var $PageObjName = 'account_view';

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

	// Page URLs
	var $AddUrl;
	var $EditUrl;
	var $CopyUrl;
	var $DeleteUrl;
	var $ViewUrl;
	var $ListUrl;

	// Export URLs
	var $ExportPrintUrl;
	var $ExportHtmlUrl;
	var $ExportExcelUrl;
	var $ExportWordUrl;
	var $ExportXmlUrl;
	var $ExportCsvUrl;
	var $ExportPdfUrl;

	// Update URLs
	var $InlineAddUrl;
	var $InlineCopyUrl;
	var $InlineEditUrl;
	var $GridAddUrl;
	var $GridEditUrl;
	var $MultiDeleteUrl;
	var $MultiUpdateUrl;

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
		$KeyUrl = "";
		if (@$_GET["id"] <> "") {
			$this->RecKey["id"] = $_GET["id"];
			$KeyUrl .= "&id=" . urlencode($this->RecKey["id"]);
		}
		$this->ExportPrintUrl = $this->PageUrl() . "export=print" . $KeyUrl;
		$this->ExportHtmlUrl = $this->PageUrl() . "export=html" . $KeyUrl;
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel" . $KeyUrl;
		$this->ExportWordUrl = $this->PageUrl() . "export=word" . $KeyUrl;
		$this->ExportXmlUrl = $this->PageUrl() . "export=xml" . $KeyUrl;
		$this->ExportCsvUrl = $this->PageUrl() . "export=csv" . $KeyUrl;
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf" . $KeyUrl;

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'view', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'account', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();

		// Export options
		$this->ExportOptions = new cListOptions();
		$this->ExportOptions->Tag = "span";
		$this->ExportOptions->TagClassName = "ewExportOption";
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

		// Get export parameters
		if (@$_GET["export"] <> "") {
			$this->Export = $_GET["export"];
		} elseif (ew_IsHttpPost()) {
			if (@$_POST["exporttype"] <> "")
				$this->Export = $_POST["exporttype"];
		} else {
			$this->setExportReturnUrl(ew_CurrentUrl());
		}
		$gsExport = $this->Export; // Get export parameter, used in header
		$gsExportFile = $this->TableVar; // Get export file, used in header
		if (@$_GET["id"] <> "") {
			if ($gsExportFile <> "") $gsExportFile .= "_";
			$gsExportFile .= ew_StripSlashes($_GET["id"]);
		}

		// Setup export options
		$this->SetupExportOptions();
		$this->CurrentAction = (@$_GET["a"] <> "") ? $_GET["a"] : @$_POST["a_list"];
		$this->id->Visible = !$this->IsAdd() && !$this->IsCopy() && !$this->IsGridAdd();

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
	var $ExportOptions; // Export options
	var $DisplayRecs = 1;
	var $StartRec;
	var $StopRec;
	var $TotalRecs = 0;
	var $RecRange = 10;
	var $RecCnt;
	var $RecKey = array();
	var $Recordset;

	//
	// Page main
	//
	function Page_Main() {
		global $Language;

		// Load current record
		$bLoadCurrentRecord = FALSE;
		$sReturnUrl = "";
		$bMatchRecord = FALSE;
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET["id"] <> "") {
				$this->id->setQueryStringValue($_GET["id"]);
				$this->RecKey["id"] = $this->id->QueryStringValue;
			} else {
				$sReturnUrl = "accountlist.php"; // Return to list
			}

			// Get action
			$this->CurrentAction = "I"; // Display form
			switch ($this->CurrentAction) {
				case "I": // Get a record to display
					if (!$this->LoadRow()) { // Load record based on key
						if ($this->getSuccessMessage() == "" && $this->getFailureMessage() == "")
							$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
						$sReturnUrl = "accountlist.php"; // No matching record, return to list
					}
			}

			// Export data only
			if (in_array($this->Export, array("html","word","excel","xml","csv","email","pdf"))) {
				if ($this->Export == "email" && $this->ExportReturnUrl() == ew_CurrentPage()) // Default return page
					$this->setExportReturnUrl($this->GetViewUrl()); // Add key
				$this->ExportData();
				if ($this->Export == "email")
					$this->Page_Terminate($this->ExportReturnUrl());
				else
					$this->Page_Terminate(); // Terminate response
				exit();
			}
		} else {
			$sReturnUrl = "accountlist.php"; // Not page request, return to list
		}
		if ($sReturnUrl <> "")
			$this->Page_Terminate($sReturnUrl);

		// Render row
		$this->RowType = EW_ROWTYPE_VIEW;
		$this->ResetAttrs();
		$this->RenderRow();
	}

	// Set up starting record parameters
	function SetUpStartRec() {
		if ($this->DisplayRecs == 0)
			return;
		if ($this->IsPageRequest()) { // Validate request
			if (@$_GET[EW_TABLE_START_REC] <> "") { // Check for "start" parameter
				$this->StartRec = $_GET[EW_TABLE_START_REC];
				$this->setStartRecordNumber($this->StartRec);
			} elseif (@$_GET[EW_TABLE_PAGE_NO] <> "") {
				$PageNo = $_GET[EW_TABLE_PAGE_NO];
				if (is_numeric($PageNo)) {
					$this->StartRec = ($PageNo-1)*$this->DisplayRecs+1;
					if ($this->StartRec <= 0) {
						$this->StartRec = 1;
					} elseif ($this->StartRec >= intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1) {
						$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1;
					}
					$this->setStartRecordNumber($this->StartRec);
				}
			}
		}
		$this->StartRec = $this->getStartRecordNumber();

		// Check if correct start record counter
		if (!is_numeric($this->StartRec) || $this->StartRec == "") { // Avoid invalid start record counter
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} elseif (intval($this->StartRec) > intval($this->TotalRecs)) { // Avoid starting record > total records
			$this->StartRec = intval(($this->TotalRecs-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to last page first record
			$this->setStartRecordNumber($this->StartRec);
		} elseif (($this->StartRec-1) % $this->DisplayRecs <> 0) {
			$this->StartRec = intval(($this->StartRec-1)/$this->DisplayRecs)*$this->DisplayRecs+1; // Point to page boundary
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Load recordset
	function LoadRecordset($offset = -1, $rowcnt = -1) {
		global $conn;

		// Call Recordset Selecting event
		$this->Recordset_Selecting($this->CurrentFilter);

		// Load List page SQL
		$sSql = $this->SelectSQL();
		if ($offset > -1 && $rowcnt > -1)
			$sSql .= " LIMIT $rowcnt OFFSET $offset";

		// Load recordset
		$rs = ew_LoadRecordset($sSql);

		// Call Recordset Selected event
		$this->Recordset_Selected($rs);
		return $rs;
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

	// Render row values based on field settings
	function RenderRow() {
		global $conn, $Security, $Language;
		global $gsLanguage;

		// Initialize URLs
		$this->AddUrl = $this->GetAddUrl();
		$this->EditUrl = $this->GetEditUrl();
		$this->CopyUrl = $this->GetCopyUrl();
		$this->DeleteUrl = $this->GetDeleteUrl();
		$this->ListUrl = $this->GetListUrl();

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

			// failed_logins
			$this->failed_logins->ViewValue = $this->failed_logins->CurrentValue;
			$this->failed_logins->ViewCustomAttributes = "";

			// locked
			$this->locked->ViewValue = $this->locked->CurrentValue;
			$this->locked->ViewCustomAttributes = "";

			// lock_country
			$this->lock_country->ViewValue = $this->lock_country->CurrentValue;
			$this->lock_country->ViewCustomAttributes = "";

			// last_login
			$this->last_login->ViewValue = $this->last_login->CurrentValue;
			$this->last_login->ViewValue = ew_FormatDateTime($this->last_login->ViewValue, 9);
			$this->last_login->ViewCustomAttributes = "";

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

			// locale
			if (strval($this->locale->CurrentValue) <> "") {
				switch ($this->locale->CurrentValue) {
					case $this->locale->FldTagValue(1):
						$this->locale->ViewValue = $this->locale->FldTagCaption(1) <> "" ? $this->locale->FldTagCaption(1) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(2):
						$this->locale->ViewValue = $this->locale->FldTagCaption(2) <> "" ? $this->locale->FldTagCaption(2) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(3):
						$this->locale->ViewValue = $this->locale->FldTagCaption(3) <> "" ? $this->locale->FldTagCaption(3) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(4):
						$this->locale->ViewValue = $this->locale->FldTagCaption(4) <> "" ? $this->locale->FldTagCaption(4) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(5):
						$this->locale->ViewValue = $this->locale->FldTagCaption(5) <> "" ? $this->locale->FldTagCaption(5) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(6):
						$this->locale->ViewValue = $this->locale->FldTagCaption(6) <> "" ? $this->locale->FldTagCaption(6) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(7):
						$this->locale->ViewValue = $this->locale->FldTagCaption(7) <> "" ? $this->locale->FldTagCaption(7) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(8):
						$this->locale->ViewValue = $this->locale->FldTagCaption(8) <> "" ? $this->locale->FldTagCaption(8) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(9):
						$this->locale->ViewValue = $this->locale->FldTagCaption(9) <> "" ? $this->locale->FldTagCaption(9) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(10):
						$this->locale->ViewValue = $this->locale->FldTagCaption(10) <> "" ? $this->locale->FldTagCaption(10) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(11):
						$this->locale->ViewValue = $this->locale->FldTagCaption(11) <> "" ? $this->locale->FldTagCaption(11) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(12):
						$this->locale->ViewValue = $this->locale->FldTagCaption(12) <> "" ? $this->locale->FldTagCaption(12) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(13):
						$this->locale->ViewValue = $this->locale->FldTagCaption(13) <> "" ? $this->locale->FldTagCaption(13) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(14):
						$this->locale->ViewValue = $this->locale->FldTagCaption(14) <> "" ? $this->locale->FldTagCaption(14) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(15):
						$this->locale->ViewValue = $this->locale->FldTagCaption(15) <> "" ? $this->locale->FldTagCaption(15) : $this->locale->CurrentValue;
						break;
					case $this->locale->FldTagValue(16):
						$this->locale->ViewValue = $this->locale->FldTagCaption(16) <> "" ? $this->locale->FldTagCaption(16) : $this->locale->CurrentValue;
						break;
					default:
						$this->locale->ViewValue = $this->locale->CurrentValue;
				}
			} else {
				$this->locale->ViewValue = NULL;
			}
			$this->locale->ViewCustomAttributes = "";

			// os
			$this->os->ViewValue = $this->os->CurrentValue;
			$this->os->ViewCustomAttributes = "";

			// recruiter
			$this->recruiter->ViewValue = $this->recruiter->CurrentValue;
			$this->recruiter->ViewCustomAttributes = "";

			// id
			$this->id->LinkCustomAttributes = "";
			$this->id->HrefValue = "";
			$this->id->TooltipValue = "";

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

			// joindate
			$this->joindate->LinkCustomAttributes = "";
			$this->joindate->HrefValue = "";
			$this->joindate->TooltipValue = "";

			// last_ip
			$this->last_ip->LinkCustomAttributes = "";
			$this->last_ip->HrefValue = "";
			$this->last_ip->TooltipValue = "";

			// failed_logins
			$this->failed_logins->LinkCustomAttributes = "";
			$this->failed_logins->HrefValue = "";
			$this->failed_logins->TooltipValue = "";

			// locked
			$this->locked->LinkCustomAttributes = "";
			$this->locked->HrefValue = "";
			$this->locked->TooltipValue = "";

			// lock_country
			$this->lock_country->LinkCustomAttributes = "";
			$this->lock_country->HrefValue = "";
			$this->lock_country->TooltipValue = "";

			// last_login
			$this->last_login->LinkCustomAttributes = "";
			$this->last_login->HrefValue = "";
			$this->last_login->TooltipValue = "";

			// online
			$this->online->LinkCustomAttributes = "";
			$this->online->HrefValue = "";
			$this->online->TooltipValue = "";

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

			// locale
			$this->locale->LinkCustomAttributes = "";
			$this->locale->HrefValue = "";
			$this->locale->TooltipValue = "";

			// os
			$this->os->LinkCustomAttributes = "";
			$this->os->HrefValue = "";
			$this->os->TooltipValue = "";

			// recruiter
			$this->recruiter->LinkCustomAttributes = "";
			$this->recruiter->HrefValue = "";
			$this->recruiter->TooltipValue = "";
		}

		// Call Row Rendered event
		if ($this->RowType <> EW_ROWTYPE_AGGREGATEINIT)
			$this->Row_Rendered();
	}

	// Set up export options
	function SetupExportOptions() {
		global $Language;

		// Printer friendly
		$item = &$this->ExportOptions->Add("print");
		$item->Body = "<a href=\"" . $this->ExportPrintUrl . "\">" . $Language->Phrase("PrinterFriendly") . "</a>";
		$item->Visible = TRUE;

		// Export to Excel
		$item = &$this->ExportOptions->Add("excel");
		$item->Body = "<a href=\"" . $this->ExportExcelUrl . "\">" . $Language->Phrase("ExportToExcel") . "</a>";
		$item->Visible = FALSE;

		// Export to Word
		$item = &$this->ExportOptions->Add("word");
		$item->Body = "<a href=\"" . $this->ExportWordUrl . "\">" . $Language->Phrase("ExportToWord") . "</a>";
		$item->Visible = FALSE;

		// Export to Html
		$item = &$this->ExportOptions->Add("html");
		$item->Body = "<a href=\"" . $this->ExportHtmlUrl . "\">" . $Language->Phrase("ExportToHtml") . "</a>";
		$item->Visible = TRUE;

		// Export to Xml
		$item = &$this->ExportOptions->Add("xml");
		$item->Body = "<a href=\"" . $this->ExportXmlUrl . "\">" . $Language->Phrase("ExportToXml") . "</a>";
		$item->Visible = FALSE;

		// Export to Csv
		$item = &$this->ExportOptions->Add("csv");
		$item->Body = "<a href=\"" . $this->ExportCsvUrl . "\">" . $Language->Phrase("ExportToCsv") . "</a>";
		$item->Visible = FALSE;

		// Export to Pdf
		$item = &$this->ExportOptions->Add("pdf");
		$item->Body = "<a href=\"" . $this->ExportPdfUrl . "\">" . $Language->Phrase("ExportToPDF") . "</a>";
		$item->Visible = TRUE;

		// Export to Email
		$item = &$this->ExportOptions->Add("email");
		$item->Body = "<a id=\"emf_account\" href=\"javascript:void(0);\" onclick=\"ew_EmailDialogShow({lnk:'emf_account',hdr:ewLanguage.Phrase('ExportToEmail'),key:" . ew_ArrayToJsonAttr($this->RecKey) . ",sel:false});\">" . $Language->Phrase("ExportToEmail") . "</a>";
		$item->Visible = FALSE;

		// Hide options for export/action
		if ($this->Export <> "")
			$this->ExportOptions->HideAllOptions();
	}

	// Export data in HTML/CSV/Word/Excel/XML/Email/PDF format
	function ExportData() {
		$utf8 = (strtolower(EW_CHARSET) == "utf-8");
		$bSelectLimit = FALSE;

		// Load recordset
		if ($bSelectLimit) {
			$this->TotalRecs = $this->SelectRecordCount();
		} else {
			if ($rs = $this->LoadRecordset())
				$this->TotalRecs = $rs->RecordCount();
		}
		$this->StartRec = 1;
		$this->SetUpStartRec(); // Set up start record position

		// Set the last record to display
		if ($this->DisplayRecs <= 0) {
			$this->StopRec = $this->TotalRecs;
		} else {
			$this->StopRec = $this->StartRec + $this->DisplayRecs - 1;
		}
		if (!$rs) {
			header("Content-Type:"); // Remove header
			header("Content-Disposition:");
			$this->ShowMessage();
			return;
		}
		$ExportDoc = ew_ExportDocument($this, "v");
		$ParentTable = "";
		if ($bSelectLimit) {
			$StartRec = 1;
			$StopRec = $this->DisplayRecs <= 0 ? $this->TotalRecs : $this->DisplayRecs;
		} else {
			$StartRec = $this->StartRec;
			$StopRec = $this->StopRec;
		}
		$sHeader = $this->PageHeader;
		$this->Page_DataRendering($sHeader);
		$ExportDoc->Text .= $sHeader;
		$this->ExportDocument($ExportDoc, $rs, $StartRec, $StopRec, "view");
		$sFooter = $this->PageFooter;
		$this->Page_DataRendered($sFooter);
		$ExportDoc->Text .= $sFooter;

		// Close recordset
		$rs->Close();

		// Export header and footer
		$ExportDoc->ExportHeaderAndFooter();

		// Clean output buffer
		if (!EW_DEBUG_ENABLED && ob_get_length())
			ob_end_clean();

		// Write debug message if enabled
		if (EW_DEBUG_ENABLED)
			echo ew_DebugMsg();

		// Output data
		$ExportDoc->Export();
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
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($account_view)) $account_view = new caccount_view();

// Page init
$account_view->Page_Init();

// Page main
$account_view->Page_Main();
?>
<?php include_once "header.php" ?>
<?php if ($account->Export == "") { ?>
<script type="text/javascript">

// Page object
var account_view = new ew_Page("account_view");
account_view.PageID = "view"; // Page ID
var EW_PAGE_ID = account_view.PageID; // For backward compatibility

// Form object
var faccountview = new ew_Form("faccountview");

// Form_CustomValidate event
faccountview.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
faccountview.ValidateRequired = true;
<?php } else { ?>
faccountview.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("View") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $account->TableCaption() ?>&nbsp;&nbsp;</span><?php $account_view->ExportOptions->Render("body"); ?>
</p>
<?php if ($account->Export == "") { ?>
<p class="phpmaker">
<a href="<?php echo $account_view->ListUrl ?>" id="a_BackToList" class="ewLink"><?php echo $Language->Phrase("BackToList") ?></a>&nbsp;
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($account_view->AddUrl <> "") { ?>
<a href="<?php echo $account_view->AddUrl ?>" id="a_AddLink" class="ewLink"><?php echo $Language->Phrase("ViewPageAddLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($account_view->EditUrl <> "") { ?>
<a href="<?php echo $account_view->EditUrl ?>" id="a_EditLink" class="ewLink"><?php echo $Language->Phrase("ViewPageEditLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($account_view->CopyUrl <> "") { ?>
<a href="<?php echo $account_view->CopyUrl ?>" id="a_CopyLink" class="ewLink"><?php echo $Language->Phrase("ViewPageCopyLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($account_view->DeleteUrl <> "") { ?>
<a href="<?php echo $account_view->DeleteUrl ?>" id="a_DeleteLink" class="ewLink"><?php echo $Language->Phrase("ViewPageDeleteLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
</p>
<?php } ?>
<?php $account_view->ShowPageHeader(); ?>
<?php
$account_view->ShowMessage();
?>
<form name="faccountview" id="faccountview" class="ewForm" action="" method="post">
<input type="hidden" name="t" value="account">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_accountview" class="ewTable">
<?php if ($account->id->Visible) { // id ?>
	<tr id="r_id"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->id->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->id->CellAttributes() ?>><span id="el_account_id">
<span<?php echo $account->id->ViewAttributes() ?>>
<?php echo $account->id->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->username->Visible) { // username ?>
	<tr id="r_username"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_username"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->username->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->username->CellAttributes() ?>><span id="el_account_username">
<span<?php echo $account->username->ViewAttributes() ?>>
<?php echo $account->username->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->_email->Visible) { // email ?>
	<tr id="r__email"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account__email"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->_email->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->_email->CellAttributes() ?>><span id="el_account__email">
<span<?php echo $account->_email->ViewAttributes() ?>>
<?php echo $account->_email->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->reg_mail->Visible) { // reg_mail ?>
	<tr id="r_reg_mail"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_reg_mail"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->reg_mail->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->reg_mail->CellAttributes() ?>><span id="el_account_reg_mail">
<span<?php echo $account->reg_mail->ViewAttributes() ?>>
<?php echo $account->reg_mail->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->joindate->Visible) { // joindate ?>
	<tr id="r_joindate"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_joindate"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->joindate->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->joindate->CellAttributes() ?>><span id="el_account_joindate">
<span<?php echo $account->joindate->ViewAttributes() ?>>
<?php echo $account->joindate->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->last_ip->Visible) { // last_ip ?>
	<tr id="r_last_ip"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_last_ip"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->last_ip->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->last_ip->CellAttributes() ?>><span id="el_account_last_ip">
<span<?php echo $account->last_ip->ViewAttributes() ?>>
<?php echo $account->last_ip->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->failed_logins->Visible) { // failed_logins ?>
	<tr id="r_failed_logins"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_failed_logins"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->failed_logins->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->failed_logins->CellAttributes() ?>><span id="el_account_failed_logins">
<span<?php echo $account->failed_logins->ViewAttributes() ?>>
<?php echo $account->failed_logins->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->locked->Visible) { // locked ?>
	<tr id="r_locked"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_locked"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->locked->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->locked->CellAttributes() ?>><span id="el_account_locked">
<span<?php echo $account->locked->ViewAttributes() ?>>
<?php echo $account->locked->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->lock_country->Visible) { // lock_country ?>
	<tr id="r_lock_country"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_lock_country"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->lock_country->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->lock_country->CellAttributes() ?>><span id="el_account_lock_country">
<span<?php echo $account->lock_country->ViewAttributes() ?>>
<?php echo $account->lock_country->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->last_login->Visible) { // last_login ?>
	<tr id="r_last_login"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_last_login"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->last_login->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->last_login->CellAttributes() ?>><span id="el_account_last_login">
<span<?php echo $account->last_login->ViewAttributes() ?>>
<?php echo $account->last_login->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->online->Visible) { // online ?>
	<tr id="r_online"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_online"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->online->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->online->CellAttributes() ?>><span id="el_account_online">
<span<?php echo $account->online->ViewAttributes() ?>>
<?php echo $account->online->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->expansion->Visible) { // expansion ?>
	<tr id="r_expansion"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_expansion"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->expansion->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->expansion->CellAttributes() ?>><span id="el_account_expansion">
<span<?php echo $account->expansion->ViewAttributes() ?>>
<?php echo $account->expansion->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->mutetime->Visible) { // mutetime ?>
	<tr id="r_mutetime"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_mutetime"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->mutetime->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->mutetime->CellAttributes() ?>><span id="el_account_mutetime">
<span<?php echo $account->mutetime->ViewAttributes() ?>>
<?php echo $account->mutetime->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->mutereason->Visible) { // mutereason ?>
	<tr id="r_mutereason"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_mutereason"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->mutereason->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->mutereason->CellAttributes() ?>><span id="el_account_mutereason">
<span<?php echo $account->mutereason->ViewAttributes() ?>>
<?php echo $account->mutereason->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->muteby->Visible) { // muteby ?>
	<tr id="r_muteby"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_muteby"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->muteby->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->muteby->CellAttributes() ?>><span id="el_account_muteby">
<span<?php echo $account->muteby->ViewAttributes() ?>>
<?php echo $account->muteby->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->locale->Visible) { // locale ?>
	<tr id="r_locale"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_locale"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->locale->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->locale->CellAttributes() ?>><span id="el_account_locale">
<span<?php echo $account->locale->ViewAttributes() ?>>
<?php echo $account->locale->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->os->Visible) { // os ?>
	<tr id="r_os"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_os"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->os->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->os->CellAttributes() ?>><span id="el_account_os">
<span<?php echo $account->os->ViewAttributes() ?>>
<?php echo $account->os->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($account->recruiter->Visible) { // recruiter ?>
	<tr id="r_recruiter"<?php echo $account->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_recruiter"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->recruiter->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account->recruiter->CellAttributes() ?>><span id="el_account_recruiter">
<span<?php echo $account->recruiter->ViewAttributes() ?>>
<?php echo $account->recruiter->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
</form>
<br>
<script type="text/javascript">
faccountview.Init();
</script>
<?php
$account_view->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<?php if ($account->Export == "") { ?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php } ?>
<?php include_once "footer.php" ?>
<?php
$account_view->Page_Terminate();
?>
