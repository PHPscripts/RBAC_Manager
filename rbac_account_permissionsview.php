<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "includes/framework/ewcfg9.php" ?>
<?php include_once "includes/framework/ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "rbac_account_permissionsinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$rbac_account_permissions_view = NULL; // Initialize page object first

class crbac_account_permissions_view extends crbac_account_permissions {

	// Page ID
	var $PageID = 'view';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'rbac_account_permissions';

	// Page object name
	var $PageObjName = 'rbac_account_permissions_view';

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

		// Table object (rbac_account_permissions)
		if (!isset($GLOBALS["rbac_account_permissions"])) {
			$GLOBALS["rbac_account_permissions"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["rbac_account_permissions"];
		}
		$KeyUrl = "";
		if (@$_GET["accountId"] <> "") {
			$this->RecKey["accountId"] = $_GET["accountId"];
			$KeyUrl .= "&accountId=" . urlencode($this->RecKey["accountId"]);
		}
		if (@$_GET["permissionId"] <> "") {
			$this->RecKey["permissionId"] = $_GET["permissionId"];
			$KeyUrl .= "&permissionId=" . urlencode($this->RecKey["permissionId"]);
		}
		if (@$_GET["realmId"] <> "") {
			$this->RecKey["realmId"] = $_GET["realmId"];
			$KeyUrl .= "&realmId=" . urlencode($this->RecKey["realmId"]);
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
			define("EW_TABLE_NAME", 'rbac_account_permissions', TRUE);

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
		if (@$_GET["accountId"] <> "") {
			if ($gsExportFile <> "") $gsExportFile .= "_";
			$gsExportFile .= ew_StripSlashes($_GET["accountId"]);
		}
		if (@$_GET["permissionId"] <> "") {
			if ($gsExportFile <> "") $gsExportFile .= "_";
			$gsExportFile .= ew_StripSlashes($_GET["permissionId"]);
		}
		if (@$_GET["realmId"] <> "") {
			if ($gsExportFile <> "") $gsExportFile .= "_";
			$gsExportFile .= ew_StripSlashes($_GET["realmId"]);
		}

		// Setup export options
		$this->SetupExportOptions();
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
			if (@$_GET["accountId"] <> "") {
				$this->accountId->setQueryStringValue($_GET["accountId"]);
				$this->RecKey["accountId"] = $this->accountId->QueryStringValue;
			} else {
				$sReturnUrl = "rbac_account_permissionslist.php"; // Return to list
			}
			if (@$_GET["permissionId"] <> "") {
				$this->permissionId->setQueryStringValue($_GET["permissionId"]);
				$this->RecKey["permissionId"] = $this->permissionId->QueryStringValue;
			} else {
				$sReturnUrl = "rbac_account_permissionslist.php"; // Return to list
			}
			if (@$_GET["realmId"] <> "") {
				$this->realmId->setQueryStringValue($_GET["realmId"]);
				$this->RecKey["realmId"] = $this->realmId->QueryStringValue;
			} else {
				$sReturnUrl = "rbac_account_permissionslist.php"; // Return to list
			}

			// Get action
			$this->CurrentAction = "I"; // Display form
			switch ($this->CurrentAction) {
				case "I": // Get a record to display
					if (!$this->LoadRow()) { // Load record based on key
						if ($this->getSuccessMessage() == "" && $this->getFailureMessage() == "")
							$this->setFailureMessage($Language->Phrase("NoRecord")); // Set no record message
						$sReturnUrl = "rbac_account_permissionslist.php"; // No matching record, return to list
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
			$sReturnUrl = "rbac_account_permissionslist.php"; // Not page request, return to list
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
		$this->accountId->setDbValue($rs->fields('accountId'));
		$this->permissionId->setDbValue($rs->fields('permissionId'));
		if (array_key_exists('EV__permissionId', $rs->fields)) {
			$this->permissionId->VirtualValue = $rs->fields('EV__permissionId'); // Set up virtual field value
		} else {
			$this->permissionId->VirtualValue = ""; // Clear value
		}
		$this->granted->setDbValue($rs->fields('granted'));
		$this->realmId->setDbValue($rs->fields('realmId'));
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
		// accountId
		// permissionId
		// granted
		// realmId

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// accountId
			if (strval($this->accountId->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->accountId->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `id` AS `DispFld`, `username` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `account`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->accountId->ViewValue = $rswrk->fields('DispFld');
					$this->accountId->ViewValue .= ew_ValueSeparator(1,$this->accountId) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->accountId->ViewValue = $this->accountId->CurrentValue;
				}
			} else {
				$this->accountId->ViewValue = NULL;
			}
			$this->accountId->ViewCustomAttributes = "";

			// permissionId
			if ($this->permissionId->VirtualValue <> "") {
				$this->permissionId->ViewValue = $this->permissionId->VirtualValue;
			} else {
			if (strval($this->permissionId->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->permissionId->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `id` AS `DispFld`, `name` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `rbac_permissions`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->permissionId->ViewValue = $rswrk->fields('DispFld');
					$this->permissionId->ViewValue .= ew_ValueSeparator(1,$this->permissionId) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->permissionId->ViewValue = $this->permissionId->CurrentValue;
				}
			} else {
				$this->permissionId->ViewValue = NULL;
			}
			}
			$this->permissionId->ViewCustomAttributes = "";

			// granted
			if (strval($this->granted->CurrentValue) <> "") {
				switch ($this->granted->CurrentValue) {
					case $this->granted->FldTagValue(1):
						$this->granted->ViewValue = $this->granted->FldTagCaption(1) <> "" ? $this->granted->FldTagCaption(1) : $this->granted->CurrentValue;
						break;
					case $this->granted->FldTagValue(2):
						$this->granted->ViewValue = $this->granted->FldTagCaption(2) <> "" ? $this->granted->FldTagCaption(2) : $this->granted->CurrentValue;
						break;
					default:
						$this->granted->ViewValue = $this->granted->CurrentValue;
				}
			} else {
				$this->granted->ViewValue = NULL;
			}
			$this->granted->ViewCustomAttributes = "";

			// realmId
			if (strval($this->realmId->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->realmId->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `id` AS `DispFld`, `name` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `realmlist`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->realmId->ViewValue = $rswrk->fields('DispFld');
					$this->realmId->ViewValue .= ew_ValueSeparator(1,$this->realmId) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->realmId->ViewValue = $this->realmId->CurrentValue;
				}
			} else {
				$this->realmId->ViewValue = NULL;
			}
			$this->realmId->ViewCustomAttributes = "";

			// accountId
			$this->accountId->LinkCustomAttributes = "";
			$this->accountId->HrefValue = "";
			$this->accountId->TooltipValue = "";

			// permissionId
			$this->permissionId->LinkCustomAttributes = "";
			$this->permissionId->HrefValue = "";
			$this->permissionId->TooltipValue = "";

			// granted
			$this->granted->LinkCustomAttributes = "";
			$this->granted->HrefValue = "";
			$this->granted->TooltipValue = "";

			// realmId
			$this->realmId->LinkCustomAttributes = "";
			$this->realmId->HrefValue = "";
			$this->realmId->TooltipValue = "";
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
		$item->Body = "<a id=\"emf_rbac_account_permissions\" href=\"javascript:void(0);\" onclick=\"ew_EmailDialogShow({lnk:'emf_rbac_account_permissions',hdr:ewLanguage.Phrase('ExportToEmail'),key:" . ew_ArrayToJsonAttr($this->RecKey) . ",sel:false});\">" . $Language->Phrase("ExportToEmail") . "</a>";
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
if (!isset($rbac_account_permissions_view)) $rbac_account_permissions_view = new crbac_account_permissions_view();

// Page init
$rbac_account_permissions_view->Page_Init();

// Page main
$rbac_account_permissions_view->Page_Main();
?>
<?php include_once "header.php" ?>
<?php if ($rbac_account_permissions->Export == "") { ?>
<script type="text/javascript">

// Page object
var rbac_account_permissions_view = new ew_Page("rbac_account_permissions_view");
rbac_account_permissions_view.PageID = "view"; // Page ID
var EW_PAGE_ID = rbac_account_permissions_view.PageID; // For backward compatibility

// Form object
var frbac_account_permissionsview = new ew_Form("frbac_account_permissionsview");

// Form_CustomValidate event
frbac_account_permissionsview.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frbac_account_permissionsview.ValidateRequired = true;
<?php } else { ?>
frbac_account_permissionsview.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
frbac_account_permissionsview.Lists["x_accountId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_username","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
frbac_account_permissionsview.Lists["x_permissionId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_name","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
frbac_account_permissionsview.Lists["x_realmId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_name","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("View") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $rbac_account_permissions->TableCaption() ?>&nbsp;&nbsp;</span><?php $rbac_account_permissions_view->ExportOptions->Render("body"); ?>
</p>
<?php if ($rbac_account_permissions->Export == "") { ?>
<p class="phpmaker">
<a href="<?php echo $rbac_account_permissions_view->ListUrl ?>" id="a_BackToList" class="ewLink"><?php echo $Language->Phrase("BackToList") ?></a>&nbsp;
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($rbac_account_permissions_view->AddUrl <> "") { ?>
<a href="<?php echo $rbac_account_permissions_view->AddUrl ?>" id="a_AddLink" class="ewLink"><?php echo $Language->Phrase("ViewPageAddLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($rbac_account_permissions_view->EditUrl <> "") { ?>
<a href="<?php echo $rbac_account_permissions_view->EditUrl ?>" id="a_EditLink" class="ewLink"><?php echo $Language->Phrase("ViewPageEditLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($rbac_account_permissions_view->CopyUrl <> "") { ?>
<a href="<?php echo $rbac_account_permissions_view->CopyUrl ?>" id="a_CopyLink" class="ewLink"><?php echo $Language->Phrase("ViewPageCopyLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($rbac_account_permissions_view->DeleteUrl <> "") { ?>
<a href="<?php echo $rbac_account_permissions_view->DeleteUrl ?>" id="a_DeleteLink" class="ewLink"><?php echo $Language->Phrase("ViewPageDeleteLink") ?></a>&nbsp;
<?php } ?>
<?php } ?>
</p>
<?php } ?>
<?php $rbac_account_permissions_view->ShowPageHeader(); ?>
<?php
$rbac_account_permissions_view->ShowMessage();
?>
<form name="frbac_account_permissionsview" id="frbac_account_permissionsview" class="ewForm" action="" method="post">
<input type="hidden" name="t" value="rbac_account_permissions">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_rbac_account_permissionsview" class="ewTable">
<?php if ($rbac_account_permissions->accountId->Visible) { // accountId ?>
	<tr id="r_accountId"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_accountId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->accountId->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->accountId->CellAttributes() ?>><span id="el_rbac_account_permissions_accountId">
<span<?php echo $rbac_account_permissions->accountId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->accountId->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($rbac_account_permissions->permissionId->Visible) { // permissionId ?>
	<tr id="r_permissionId"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_permissionId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->permissionId->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->permissionId->CellAttributes() ?>><span id="el_rbac_account_permissions_permissionId">
<span<?php echo $rbac_account_permissions->permissionId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->permissionId->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($rbac_account_permissions->granted->Visible) { // granted ?>
	<tr id="r_granted"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_granted"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->granted->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->granted->CellAttributes() ?>><span id="el_rbac_account_permissions_granted">
<span<?php echo $rbac_account_permissions->granted->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->granted->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
<?php if ($rbac_account_permissions->realmId->Visible) { // realmId ?>
	<tr id="r_realmId"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_realmId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->realmId->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->realmId->CellAttributes() ?>><span id="el_rbac_account_permissions_realmId">
<span<?php echo $rbac_account_permissions->realmId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->realmId->ViewValue ?></span>
</span></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
</form>
<br>
<script type="text/javascript">
frbac_account_permissionsview.Init();
</script>
<?php
$rbac_account_permissions_view->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<?php if ($rbac_account_permissions->Export == "") { ?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php } ?>
<?php include_once "footer.php" ?>
<?php
$rbac_account_permissions_view->Page_Terminate();
?>
