<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg9.php" ?>
<?php include_once "ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "realmlistinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$realmlist_view = NULL; // Initialize page object first

class crealmlist_view extends crealmlist {

	// Page ID
	var $PageID = 'view';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'realmlist';

	// Page object name
	var $PageObjName = 'realmlist_view';

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

		// Table object (realmlist)
		if (!isset($GLOBALS["realmlist"])) {
			$GLOBALS["realmlist"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["realmlist"];
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
			define("EW_TABLE_NAME", 'realmlist', TRUE);

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
				$sReturnUrl = "realmlistlist.php"; // Return to list
			}

			// Get action
			$this->CurrentAction = "I"; // Display form
			switch ($this->CurrentAction) {
				case "I": // Get a record to display
					if (!$this->LoadRow()) { // Load record based on key
						if ($this->getSuccessMessage() == "" && $this->getFailureMessage() == "")
							$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
						$sReturnUrl = "realmlistlist.php"; // No matching record, return to list
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
			$sReturnUrl = "realmlistlist.php"; // Not page request, return to list
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
		$this->name->setDbValue($rs->fields('name'));
		$this->address->setDbValue($rs->fields('address'));
		$this->localAddress->setDbValue($rs->fields('localAddress'));
		$this->localSubnetMask->setDbValue($rs->fields('localSubnetMask'));
		$this->port->setDbValue($rs->fields('port'));
		$this->icon->setDbValue($rs->fields('icon'));
		$this->flag->setDbValue($rs->fields('flag'));
		$this->timezone->setDbValue($rs->fields('timezone'));
		$this->allowedSecurityLevel->setDbValue($rs->fields('allowedSecurityLevel'));
		$this->population->setDbValue($rs->fields('population'));
		$this->gamebuild->setDbValue($rs->fields('gamebuild'));
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

		// Convert decimal values if posted back
		if ($this->population->FormValue == $this->population->CurrentValue && is_numeric(ew_StrToFloat($this->population->CurrentValue)))
			$this->population->CurrentValue = ew_StrToFloat($this->population->CurrentValue);

		// Call Row_Rendering event
		$this->Row_Rendering();

		// Common render codes for all row types
		// id
		// name
		// address
		// localAddress
		// localSubnetMask
		// port
		// icon
		// flag
		// timezone
		// allowedSecurityLevel
		// population
		// gamebuild

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			$this->id->ViewValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// name
			$this->name->ViewValue = $this->name->CurrentValue;
			$this->name->ViewCustomAttributes = "";

			// address
			$this->address->ViewValue = $this->address->CurrentValue;
			$this->address->ViewCustomAttributes = "";

			// localAddress
			$this->localAddress->ViewValue = $this->localAddress->CurrentValue;
			$this->localAddress->ViewCustomAttributes = "";

			// localSubnetMask
			$this->localSubnetMask->ViewValue = $this->localSubnetMask->CurrentValue;
			$this->localSubnetMask->ViewCustomAttributes = "";

			// port
			$this->port->ViewValue = $this->port->CurrentValue;
			$this->port->ViewCustomAttributes = "";

			// icon
			if (strval($this->icon->CurrentValue) <> "") {
				switch ($this->icon->CurrentValue) {
					case $this->icon->FldTagValue(1):
						$this->icon->ViewValue = $this->icon->FldTagCaption(1) <> "" ? $this->icon->FldTagCaption(1) : $this->icon->CurrentValue;
						break;
					case $this->icon->FldTagValue(2):
						$this->icon->ViewValue = $this->icon->FldTagCaption(2) <> "" ? $this->icon->FldTagCaption(2) : $this->icon->CurrentValue;
						break;
					case $this->icon->FldTagValue(3):
						$this->icon->ViewValue = $this->icon->FldTagCaption(3) <> "" ? $this->icon->FldTagCaption(3) : $this->icon->CurrentValue;
						break;
					case $this->icon->FldTagValue(4):
						$this->icon->ViewValue = $this->icon->FldTagCaption(4) <> "" ? $this->icon->FldTagCaption(4) : $this->icon->CurrentValue;
						break;
					case $this->icon->FldTagValue(5):
						$this->icon->ViewValue = $this->icon->FldTagCaption(5) <> "" ? $this->icon->FldTagCaption(5) : $this->icon->CurrentValue;
						break;
					default:
						$this->icon->ViewValue = $this->icon->CurrentValue;
				}
			} else {
				$this->icon->ViewValue = NULL;
			}
			$this->icon->ViewCustomAttributes = "";

			// timezone
			if (strval($this->timezone->CurrentValue) <> "") {
				switch ($this->timezone->CurrentValue) {
					case $this->timezone->FldTagValue(1):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(1) <> "" ? $this->timezone->FldTagCaption(1) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(2):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(2) <> "" ? $this->timezone->FldTagCaption(2) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(3):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(3) <> "" ? $this->timezone->FldTagCaption(3) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(4):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(4) <> "" ? $this->timezone->FldTagCaption(4) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(5):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(5) <> "" ? $this->timezone->FldTagCaption(5) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(6):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(6) <> "" ? $this->timezone->FldTagCaption(6) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(7):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(7) <> "" ? $this->timezone->FldTagCaption(7) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(8):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(8) <> "" ? $this->timezone->FldTagCaption(8) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(9):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(9) <> "" ? $this->timezone->FldTagCaption(9) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(10):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(10) <> "" ? $this->timezone->FldTagCaption(10) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(11):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(11) <> "" ? $this->timezone->FldTagCaption(11) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(12):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(12) <> "" ? $this->timezone->FldTagCaption(12) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(13):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(13) <> "" ? $this->timezone->FldTagCaption(13) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(14):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(14) <> "" ? $this->timezone->FldTagCaption(14) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(15):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(15) <> "" ? $this->timezone->FldTagCaption(15) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(16):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(16) <> "" ? $this->timezone->FldTagCaption(16) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(17):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(17) <> "" ? $this->timezone->FldTagCaption(17) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(18):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(18) <> "" ? $this->timezone->FldTagCaption(18) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(19):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(19) <> "" ? $this->timezone->FldTagCaption(19) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(20):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(20) <> "" ? $this->timezone->FldTagCaption(20) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(21):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(21) <> "" ? $this->timezone->FldTagCaption(21) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(22):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(22) <> "" ? $this->timezone->FldTagCaption(22) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(23):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(23) <> "" ? $this->timezone->FldTagCaption(23) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(24):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(24) <> "" ? $this->timezone->FldTagCaption(24) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(25):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(25) <> "" ? $this->timezone->FldTagCaption(25) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(26):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(26) <> "" ? $this->timezone->FldTagCaption(26) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(27):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(27) <> "" ? $this->timezone->FldTagCaption(27) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(28):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(28) <> "" ? $this->timezone->FldTagCaption(28) : $this->timezone->CurrentValue;
						break;
					case $this->timezone->FldTagValue(29):
						$this->timezone->ViewValue = $this->timezone->FldTagCaption(29) <> "" ? $this->timezone->FldTagCaption(29) : $this->timezone->CurrentValue;
						break;
					default:
						$this->timezone->ViewValue = $this->timezone->CurrentValue;
				}
			} else {
				$this->timezone->ViewValue = NULL;
			}
			$this->timezone->ViewCustomAttributes = "";

			// allowedSecurityLevel
			$this->allowedSecurityLevel->ViewValue = $this->allowedSecurityLevel->CurrentValue;
			$this->allowedSecurityLevel->ViewCustomAttributes = "";

			// population
			$this->population->ViewValue = $this->population->CurrentValue;
			$this->population->ViewCustomAttributes = "";

			// gamebuild
			$this->gamebuild->ViewValue = $this->gamebuild->CurrentValue;
			$this->gamebuild->ViewCustomAttributes = "";

			// id
			$this->id->LinkCustomAttributes = "";
			$this->id->HrefValue = "";
			$this->id->TooltipValue = "";

			// name
			$this->name->LinkCustomAttributes = "";
			$this->name->HrefValue = "";
			$this->name->TooltipValue = "";

			// address
			$this->address->LinkCustomAttributes = "";
			$this->address->HrefValue = "";
			$this->address->TooltipValue = "";

			// localAddress
			$this->localAddress->LinkCustomAttributes = "";
			$this->localAddress->HrefValue = "";
			$this->localAddress->TooltipValue = "";

			// localSubnetMask
			$this->localSubnetMask->LinkCustomAttributes = "";
			$this->localSubnetMask->HrefValue = "";
			$this->localSubnetMask->TooltipValue = "";

			// port
			$this->port->LinkCustomAttributes = "";
			$this->port->HrefValue = "";
			$this->port->TooltipValue = "";

			// icon
			$this->icon->LinkCustomAttributes = "";
			$this->icon->HrefValue = "";
			$this->icon->TooltipValue = "";

			// timezone
			$this->timezone->LinkCustomAttributes = "";
			$this->timezone->HrefValue = "";
			$this->timezone->TooltipValue = "";

			// allowedSecurityLevel
			$this->allowedSecurityLevel->LinkCustomAttributes = "";
			$this->allowedSecurityLevel->HrefValue = "";
			$this->allowedSecurityLevel->TooltipValue = "";

			// population
			$this->population->LinkCustomAttributes = "";
			$this->population->HrefValue = "";
			$this->population->TooltipValue = "";

			// gamebuild
			$this->gamebuild->LinkCustomAttributes = "";
			$this->gamebuild->HrefValue = "";
			$this->gamebuild->TooltipValue = "";
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
		$item->Body = "<a id=\"emf_realmlist\" href=\"javascript:void(0);\" onclick=\"ew_EmailDialogShow({lnk:'emf_realmlist',hdr:ewLanguage.Phrase('ExportToEmail'),key:" . ew_ArrayToJsonAttr($this->RecKey) . ",sel:false});\">" . $Language->Phrase("ExportToEmail") . "</a>";
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
if (!isset($realmlist_view)) $realmlist_view = new crealmlist_view();

// Page init
$realmlist_view->Page_Init();

// Page main
$realmlist_view->Page_Main();
?>
<?php include_once "header.php" ?>
<?php if ($realmlist->Export == "") { ?>
<script type="text/javascript">

// Page object
var realmlist_view = new ew_Page("realmlist_view");
realmlist_view.PageID = "view"; // Page ID
var EW_PAGE_ID = realmlist_view.PageID; // For backward compatibility

// Form object
var frealmlistview = new ew_Form("frealmlistview");

// Form_CustomValidate event
frealmlistview.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frealmlistview.ValidateRequired = true;
<?php } else { ?>
frealmlistview.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("View") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $realmlist->TableCaption() ?>&nbsp;&nbsp;</span><?php $realmlist_view->ExportOptions->Render("body"); ?>
</p>
<?php if ($realmlist->Export == "") { ?>
<p class="phpmaker">
<a href="<?php echo $realmlist_view->ListUrl ?>" id="a_BackToList" class="ewLink"><?php echo $Language->Phrase("BackToList") ?></a>&nbsp;
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($realmlist_view->AddUrl <> "") { ?>
<a href="<?php echo $realmlist_view->AddUrl ?>" id="a_AddLink" class="ewLink"><?php echo $Language->Phrase("ViewPageAddLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($realmlist_view->EditUrl <> "") { ?>
<a href="<?php echo $realmlist_view->EditUrl ?>" id="a_EditLink" class="ewLink"><?php echo $Language->Phrase("ViewPageEditLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($realmlist_view->CopyUrl <> "") { ?>
<a href="<?php echo $realmlist_view->CopyUrl ?>" id="a_CopyLink" class="ewLink"><?php echo $Language->Phrase("ViewPageCopyLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($realmlist_view->DeleteUrl <> "") { ?>
<a href="<?php echo $realmlist_view->DeleteUrl ?>" id="a_DeleteLink" class="ewLink"><?php echo $Language->Phrase("ViewPageDeleteLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
</p>
<?php } ?>
<?php $realmlist_view->ShowPageHeader(); ?>
<?php
$realmlist_view->ShowMessage();
?>
<form name="frealmlistview" id="frealmlistview" class="ewForm" action="" method="post">
<input type="hidden" name="t" value="realmlist">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_realmlistview" class="ewTable">
<?php if ($realmlist->id->Visible) { // id ?>
	<tr id="r_id"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->id->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->id->CellAttributes() ?>><span id="el_realmlist_id">
<span<?php echo $realmlist->id->ViewAttributes() ?>>
<?php echo $realmlist->id->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->name->Visible) { // name ?>
	<tr id="r_name"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_name"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->name->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->name->CellAttributes() ?>><span id="el_realmlist_name">
<span<?php echo $realmlist->name->ViewAttributes() ?>>
<?php echo $realmlist->name->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->address->Visible) { // address ?>
	<tr id="r_address"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_address"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->address->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->address->CellAttributes() ?>><span id="el_realmlist_address">
<span<?php echo $realmlist->address->ViewAttributes() ?>>
<?php echo $realmlist->address->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->localAddress->Visible) { // localAddress ?>
	<tr id="r_localAddress"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_localAddress"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->localAddress->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->localAddress->CellAttributes() ?>><span id="el_realmlist_localAddress">
<span<?php echo $realmlist->localAddress->ViewAttributes() ?>>
<?php echo $realmlist->localAddress->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->localSubnetMask->Visible) { // localSubnetMask ?>
	<tr id="r_localSubnetMask"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_localSubnetMask"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->localSubnetMask->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->localSubnetMask->CellAttributes() ?>><span id="el_realmlist_localSubnetMask">
<span<?php echo $realmlist->localSubnetMask->ViewAttributes() ?>>
<?php echo $realmlist->localSubnetMask->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->port->Visible) { // port ?>
	<tr id="r_port"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_port"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->port->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->port->CellAttributes() ?>><span id="el_realmlist_port">
<span<?php echo $realmlist->port->ViewAttributes() ?>>
<?php echo $realmlist->port->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->icon->Visible) { // icon ?>
	<tr id="r_icon"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_icon"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->icon->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->icon->CellAttributes() ?>><span id="el_realmlist_icon">
<span<?php echo $realmlist->icon->ViewAttributes() ?>>
<?php echo $realmlist->icon->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->timezone->Visible) { // timezone ?>
	<tr id="r_timezone"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_timezone"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->timezone->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->timezone->CellAttributes() ?>><span id="el_realmlist_timezone">
<span<?php echo $realmlist->timezone->ViewAttributes() ?>>
<?php echo $realmlist->timezone->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->allowedSecurityLevel->Visible) { // allowedSecurityLevel ?>
	<tr id="r_allowedSecurityLevel"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_allowedSecurityLevel"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->allowedSecurityLevel->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->allowedSecurityLevel->CellAttributes() ?>><span id="el_realmlist_allowedSecurityLevel">
<span<?php echo $realmlist->allowedSecurityLevel->ViewAttributes() ?>>
<?php echo $realmlist->allowedSecurityLevel->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->population->Visible) { // population ?>
	<tr id="r_population"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_population"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->population->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->population->CellAttributes() ?>><span id="el_realmlist_population">
<span<?php echo $realmlist->population->ViewAttributes() ?>>
<?php echo $realmlist->population->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($realmlist->gamebuild->Visible) { // gamebuild ?>
	<tr id="r_gamebuild"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_gamebuild"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->gamebuild->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->gamebuild->CellAttributes() ?>><span id="el_realmlist_gamebuild">
<span<?php echo $realmlist->gamebuild->ViewAttributes() ?>>
<?php echo $realmlist->gamebuild->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
</form>
<br>
<script type="text/javascript">
frealmlistview.Init();
</script>
<?php
$realmlist_view->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<?php if ($realmlist->Export == "") { ?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php } ?>
<?php include_once "footer.php" ?>
<?php
$realmlist_view->Page_Terminate();
?>
