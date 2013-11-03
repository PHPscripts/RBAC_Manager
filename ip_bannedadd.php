<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg9.php" ?>
<?php include_once "ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "ip_bannedinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$ip_banned_add = NULL; // Initialize page object first

class cip_banned_add extends cip_banned {

	// Page ID
	var $PageID = 'add';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'ip_banned';

	// Page object name
	var $PageObjName = 'ip_banned_add';

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

		// Table object (ip_banned)
		if (!isset($GLOBALS["ip_banned"])) {
			$GLOBALS["ip_banned"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["ip_banned"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'add', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'ip_banned', TRUE);

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
			if (@$_GET["ip"] != "") {
				$this->ip->setQueryStringValue($_GET["ip"]);
				$this->setKey("ip", $this->ip->CurrentValue); // Set up key
			} else {
				$this->setKey("ip", ""); // Clear key
				$this->CopyRecord = FALSE;
			}
			if (@$_GET["bandate"] != "") {
				$this->bandate->setQueryStringValue($_GET["bandate"]);
				$this->setKey("bandate", $this->bandate->CurrentValue); // Set up key
			} else {
				$this->setKey("bandate", ""); // Clear key
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
					$this->Page_Terminate("ip_bannedlist.php"); // No matching record, return to list
				}
				break;
			case "A": // Add new record
				$this->SendEmail = TRUE; // Send email on add success
				if ($this->AddRow($this->OldRecordset)) { // Add successful
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("AddSuccess")); // Set up success message
					$sReturnUrl = $this->getReturnUrl();
					if (ew_GetPageName($sReturnUrl) == "ip_bannedview.php")
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
		$this->ip->CurrentValue = "127.0.0.1";
		$this->bandate->CurrentValue = NULL;
		$this->bandate->OldValue = $this->bandate->CurrentValue;
		$this->unbandate->CurrentValue = NULL;
		$this->unbandate->OldValue = $this->unbandate->CurrentValue;
		$this->bannedby->CurrentValue = "[Console]";
		$this->banreason->CurrentValue = "no reason";
	}

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->ip->FldIsDetailKey) {
			$this->ip->setFormValue($objForm->GetValue("x_ip"));
		}
		if (!$this->bandate->FldIsDetailKey) {
			$this->bandate->setFormValue($objForm->GetValue("x_bandate"));
		}
		if (!$this->unbandate->FldIsDetailKey) {
			$this->unbandate->setFormValue($objForm->GetValue("x_unbandate"));
		}
		if (!$this->bannedby->FldIsDetailKey) {
			$this->bannedby->setFormValue($objForm->GetValue("x_bannedby"));
		}
		if (!$this->banreason->FldIsDetailKey) {
			$this->banreason->setFormValue($objForm->GetValue("x_banreason"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadOldRecord();
		$this->ip->CurrentValue = $this->ip->FormValue;
		$this->bandate->CurrentValue = $this->bandate->FormValue;
		$this->unbandate->CurrentValue = $this->unbandate->FormValue;
		$this->bannedby->CurrentValue = $this->bannedby->FormValue;
		$this->banreason->CurrentValue = $this->banreason->FormValue;
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
		$this->ip->setDbValue($rs->fields('ip'));
		$this->bandate->setDbValue($rs->fields('bandate'));
		$this->unbandate->setDbValue($rs->fields('unbandate'));
		$this->bannedby->setDbValue($rs->fields('bannedby'));
		$this->banreason->setDbValue($rs->fields('banreason'));
	}

	// Load old record
	function LoadOldRecord() {

		// Load key values from Session
		$bValidKey = TRUE;
		if (strval($this->getKey("ip")) <> "")
			$this->ip->CurrentValue = $this->getKey("ip"); // ip
		else
			$bValidKey = FALSE;
		if (strval($this->getKey("bandate")) <> "")
			$this->bandate->CurrentValue = $this->getKey("bandate"); // bandate
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
		// ip
		// bandate
		// unbandate
		// bannedby
		// banreason

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// ip
			$this->ip->ViewValue = $this->ip->CurrentValue;
			$this->ip->ViewCustomAttributes = "";

			// bandate
			$this->bandate->ViewValue = $this->bandate->CurrentValue;
			$this->bandate->ViewValue = ew_FormatDateTime($this->bandate->ViewValue, 15);
			$this->bandate->ViewCustomAttributes = "";

			// unbandate
			$this->unbandate->ViewValue = $this->unbandate->CurrentValue;
			$this->unbandate->ViewValue = ew_FormatDateTime($this->unbandate->ViewValue, 9);
			$this->unbandate->ViewCustomAttributes = "";

			// bannedby
			$this->bannedby->ViewValue = $this->bannedby->CurrentValue;
			$this->bannedby->ViewCustomAttributes = "";

			// banreason
			$this->banreason->ViewValue = $this->banreason->CurrentValue;
			$this->banreason->ViewCustomAttributes = "";

			// ip
			$this->ip->LinkCustomAttributes = "";
			$this->ip->HrefValue = "";
			$this->ip->TooltipValue = "";

			// bandate
			$this->bandate->LinkCustomAttributes = "";
			$this->bandate->HrefValue = "";
			$this->bandate->TooltipValue = "";

			// unbandate
			$this->unbandate->LinkCustomAttributes = "";
			$this->unbandate->HrefValue = "";
			$this->unbandate->TooltipValue = "";

			// bannedby
			$this->bannedby->LinkCustomAttributes = "";
			$this->bannedby->HrefValue = "";
			$this->bannedby->TooltipValue = "";

			// banreason
			$this->banreason->LinkCustomAttributes = "";
			$this->banreason->HrefValue = "";
			$this->banreason->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_ADD) { // Add row

			// ip
			$this->ip->EditCustomAttributes = "";
			$this->ip->EditValue = ew_HtmlEncode($this->ip->CurrentValue);

			// bandate
			$this->bandate->EditCustomAttributes = "";
			$this->bandate->EditValue = ew_HtmlEncode($this->bandate->CurrentValue);

			// unbandate
			$this->unbandate->EditCustomAttributes = "";
			$this->unbandate->EditValue = ew_HtmlEncode($this->unbandate->CurrentValue);

			// bannedby
			$this->bannedby->EditCustomAttributes = "";
			$this->bannedby->EditValue = ew_HtmlEncode($this->bannedby->CurrentValue);

			// banreason
			$this->banreason->EditCustomAttributes = "";
			$this->banreason->EditValue = ew_HtmlEncode($this->banreason->CurrentValue);

			// Edit refer script
			// ip

			$this->ip->HrefValue = "";

			// bandate
			$this->bandate->HrefValue = "";

			// unbandate
			$this->unbandate->HrefValue = "";

			// bannedby
			$this->bannedby->HrefValue = "";

			// banreason
			$this->banreason->HrefValue = "";
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
		if (!is_null($this->ip->FormValue) && $this->ip->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->ip->FldCaption());
		}
		if (!is_null($this->bandate->FormValue) && $this->bandate->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->bandate->FldCaption());
		}
		if (!ew_CheckInteger($this->bandate->FormValue)) {
			ew_AddMessage($gsFormError, $this->bandate->FldErrMsg());
		}
		if (!is_null($this->unbandate->FormValue) && $this->unbandate->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->unbandate->FldCaption());
		}
		if (!ew_CheckInteger($this->unbandate->FormValue)) {
			ew_AddMessage($gsFormError, $this->unbandate->FldErrMsg());
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
		$rsnew = array();

		// ip
		$this->ip->SetDbValueDef($rsnew, $this->ip->CurrentValue, "", strval($this->ip->CurrentValue) == "");

		// bandate
		$this->bandate->SetDbValueDef($rsnew, $this->bandate->CurrentValue, 0, FALSE);

		// unbandate
		$this->unbandate->SetDbValueDef($rsnew, $this->unbandate->CurrentValue, 0, FALSE);

		// bannedby
		$this->bannedby->SetDbValueDef($rsnew, $this->bannedby->CurrentValue, "", strval($this->bannedby->CurrentValue) == "");

		// banreason
		$this->banreason->SetDbValueDef($rsnew, $this->banreason->CurrentValue, "", strval($this->banreason->CurrentValue) == "");

		// Call Row Inserting event
		$rs = ($rsold == NULL) ? NULL : $rsold->fields;
		$bInsertRow = $this->Row_Inserting($rs, $rsnew);

		// Check if key value entered
		if ($bInsertRow && $this->ValidateKey && $this->ip->CurrentValue == "" && $this->ip->getSessionValue() == "") {
			$this->setFailureMessage($Language->Phrase("InvalidKeyValue"));
			$bInsertRow = FALSE;
		}

		// Check if key value entered
		if ($bInsertRow && $this->ValidateKey && $this->bandate->CurrentValue == "" && $this->bandate->getSessionValue() == "") {
			$this->setFailureMessage($Language->Phrase("InvalidKeyValue"));
			$bInsertRow = FALSE;
		}

		// Check for duplicate key
		if ($bInsertRow && $this->ValidateKey) {
			$sFilter = $this->KeyFilter();
			$rsChk = $this->LoadRs($sFilter);
			if ($rsChk && !$rsChk->EOF) {
				$sKeyErrMsg = str_replace("%f", $sFilter, $Language->Phrase("DupKey"));
				$this->setFailureMessage($sKeyErrMsg);
				$rsChk->Close();
				$bInsertRow = FALSE;
			}
		}
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
if (!isset($ip_banned_add)) $ip_banned_add = new cip_banned_add();

// Page init
$ip_banned_add->Page_Init();

// Page main
$ip_banned_add->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var ip_banned_add = new ew_Page("ip_banned_add");
ip_banned_add.PageID = "add"; // Page ID
var EW_PAGE_ID = ip_banned_add.PageID; // For backward compatibility

// Form object
var fip_bannedadd = new ew_Form("fip_bannedadd");

// Validate form
fip_bannedadd.Validate = function(fobj) {
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
		elm = fobj.elements["x" + infix + "_ip"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($ip_banned->ip->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_bandate"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($ip_banned->bandate->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_bandate"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($ip_banned->bandate->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_unbandate"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($ip_banned->unbandate->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_unbandate"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($ip_banned->unbandate->FldErrMsg()) ?>");

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
fip_bannedadd.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
fip_bannedadd.ValidateRequired = true;
<?php } else { ?>
fip_bannedadd.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Add") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $ip_banned->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $ip_banned->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $ip_banned_add->ShowPageHeader(); ?>
<?php
$ip_banned_add->ShowMessage();
?>
<form name="fip_bannedadd" id="fip_bannedadd" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post" onsubmit="return ewForms[this.id].Submit();">
<br>
<input type="hidden" name="t" value="ip_banned">
<input type="hidden" name="a_add" id="a_add" value="A">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_ip_bannedadd" class="ewTable">
<?php if ($ip_banned->ip->Visible) { // ip ?>
	<tr id="r_ip"<?php echo $ip_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_ip_banned_ip"><table class="ewTableHeaderBtn"><tr><td><?php echo $ip_banned->ip->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $ip_banned->ip->CellAttributes() ?>><span id="el_ip_banned_ip">
<input type="text" name="x_ip" id="x_ip" size="30" maxlength="15" value="<?php echo $ip_banned->ip->EditValue ?>"<?php echo $ip_banned->ip->EditAttributes() ?>>
</span><?php echo $ip_banned->ip->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($ip_banned->bandate->Visible) { // bandate ?>
	<tr id="r_bandate"<?php echo $ip_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_ip_banned_bandate"><table class="ewTableHeaderBtn"><tr><td><?php echo $ip_banned->bandate->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $ip_banned->bandate->CellAttributes() ?>><span id="el_ip_banned_bandate">
<input type="text" name="x_bandate" id="x_bandate" size="30" value="<?php echo $ip_banned->bandate->EditValue ?>"<?php echo $ip_banned->bandate->EditAttributes() ?>>
</span><?php echo $ip_banned->bandate->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($ip_banned->unbandate->Visible) { // unbandate ?>
	<tr id="r_unbandate"<?php echo $ip_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_ip_banned_unbandate"><table class="ewTableHeaderBtn"><tr><td><?php echo $ip_banned->unbandate->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $ip_banned->unbandate->CellAttributes() ?>><span id="el_ip_banned_unbandate">
<input type="text" name="x_unbandate" id="x_unbandate" size="30" value="<?php echo $ip_banned->unbandate->EditValue ?>"<?php echo $ip_banned->unbandate->EditAttributes() ?>>
</span><?php echo $ip_banned->unbandate->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($ip_banned->bannedby->Visible) { // bannedby ?>
	<tr id="r_bannedby"<?php echo $ip_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_ip_banned_bannedby"><table class="ewTableHeaderBtn"><tr><td><?php echo $ip_banned->bannedby->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $ip_banned->bannedby->CellAttributes() ?>><span id="el_ip_banned_bannedby">
<input type="text" name="x_bannedby" id="x_bannedby" size="30" maxlength="50" value="<?php echo $ip_banned->bannedby->EditValue ?>"<?php echo $ip_banned->bannedby->EditAttributes() ?>>
</span><?php echo $ip_banned->bannedby->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($ip_banned->banreason->Visible) { // banreason ?>
	<tr id="r_banreason"<?php echo $ip_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_ip_banned_banreason"><table class="ewTableHeaderBtn"><tr><td><?php echo $ip_banned->banreason->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $ip_banned->banreason->CellAttributes() ?>><span id="el_ip_banned_banreason">
<input type="text" name="x_banreason" id="x_banreason" size="30" maxlength="255" value="<?php echo $ip_banned->banreason->EditValue ?>"<?php echo $ip_banned->banreason->EditAttributes() ?>>
</span><?php echo $ip_banned->banreason->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="btnAction" id="btnAction" value="<?php echo ew_BtnCaption($Language->Phrase("AddBtn")) ?>">
</form>
<script type="text/javascript">
fip_bannedadd.Init();
</script>
<?php
$ip_banned_add->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$ip_banned_add->Page_Terminate();
?>
