<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "includes/framework/ewcfg9.php" ?>
<?php include_once "includes/framework/ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "account_bannedinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$account_banned_edit = NULL; // Initialize page object first

class caccount_banned_edit extends caccount_banned {

	// Page ID
	var $PageID = 'edit';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'account_banned';

	// Page object name
	var $PageObjName = 'account_banned_edit';

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

		// Table object (account_banned)
		if (!isset($GLOBALS["account_banned"])) {
			$GLOBALS["account_banned"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["account_banned"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'edit', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'account_banned', TRUE);

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
	var $DbMasterFilter;
	var $DbDetailFilter;

	// 
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError;

		// Load key from QueryString
		if (@$_GET["id"] <> "")
			$this->id->setQueryStringValue($_GET["id"]);
		if (@$_GET["bandate"] <> "")
			$this->bandate->setQueryStringValue($_GET["bandate"]);

		// Process form if post back
		if (@$_POST["a_edit"] <> "") {
			$this->CurrentAction = $_POST["a_edit"]; // Get action code
			$this->LoadFormValues(); // Get form values
		} else {
			$this->CurrentAction = "I"; // Default action is display
		}

		// Check if valid key
		if ($this->id->CurrentValue == "")
			$this->Page_Terminate("account_bannedlist.php"); // Invalid key, return to list
		if ($this->bandate->CurrentValue == "")
			$this->Page_Terminate("account_bannedlist.php"); // Invalid key, return to list

		// Validate form if post back
		if (@$_POST["a_edit"] <> "") {
			if (!$this->ValidateForm()) {
				$this->CurrentAction = ""; // Form error, reset action
				$this->setFailureMessage($gsFormError);
				$this->EventCancelled = TRUE; // Event cancelled
				$this->RestoreFormValues();
			}
		}
		switch ($this->CurrentAction) {
			case "I": // Get a record to display
				if (!$this->LoadRow()) { // Load record based on key
					if ($this->getFailureMessage() == "") $this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
					$this->Page_Terminate("account_bannedlist.php"); // No matching record, return to list
				}
				break;
			Case "U": // Update
				$this->SendEmail = TRUE; // Send email on update success
				if ($this->EditRow()) { // Update record based on key
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("UpdateSuccess")); // Update success
					$sReturnUrl = $this->getReturnUrl();
					$this->Page_Terminate($sReturnUrl); // Return to caller
				} else {
					$this->EventCancelled = TRUE; // Event cancelled
					$this->RestoreFormValues(); // Restore form values if update failed
				}
		}

		// Render the record
		$this->RowType = EW_ROWTYPE_EDIT; // Render as Edit
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

	// Load form values
	function LoadFormValues() {

		// Load from form
		global $objForm;
		if (!$this->id->FldIsDetailKey) {
			$this->id->setFormValue($objForm->GetValue("x_id"));
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
		if (!$this->active->FldIsDetailKey) {
			$this->active->setFormValue($objForm->GetValue("x_active"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadRow();
		$this->id->CurrentValue = $this->id->FormValue;
		$this->bandate->CurrentValue = $this->bandate->FormValue;
		$this->unbandate->CurrentValue = $this->unbandate->FormValue;
		$this->bannedby->CurrentValue = $this->bannedby->FormValue;
		$this->banreason->CurrentValue = $this->banreason->FormValue;
		$this->active->CurrentValue = $this->active->FormValue;
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
		$this->bandate->setDbValue($rs->fields('bandate'));
		$this->unbandate->setDbValue($rs->fields('unbandate'));
		$this->bannedby->setDbValue($rs->fields('bannedby'));
		$this->banreason->setDbValue($rs->fields('banreason'));
		$this->active->setDbValue($rs->fields('active'));
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
		// bandate
		// unbandate
		// bannedby
		// banreason
		// active

		if ($this->RowType == EW_ROWTYPE_VIEW) { // View row

			// id
			if (strval($this->id->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->id->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `id` AS `DispFld`, `username` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `account`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->id->ViewValue = $rswrk->fields('DispFld');
					$this->id->ViewValue .= ew_ValueSeparator(1,$this->id) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->id->ViewValue = $this->id->CurrentValue;
				}
			} else {
				$this->id->ViewValue = NULL;
			}
			$this->id->ViewCustomAttributes = "";

			// bandate
			$this->bandate->ViewValue = $this->bandate->CurrentValue;
			$this->bandate->ViewValue = ew_FormatDateTime($this->bandate->ViewValue, 9);
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

			// active
			if (strval($this->active->CurrentValue) <> "") {
				switch ($this->active->CurrentValue) {
					case $this->active->FldTagValue(1):
						$this->active->ViewValue = $this->active->FldTagCaption(1) <> "" ? $this->active->FldTagCaption(1) : $this->active->CurrentValue;
						break;
					case $this->active->FldTagValue(2):
						$this->active->ViewValue = $this->active->FldTagCaption(2) <> "" ? $this->active->FldTagCaption(2) : $this->active->CurrentValue;
						break;
					default:
						$this->active->ViewValue = $this->active->CurrentValue;
				}
			} else {
				$this->active->ViewValue = NULL;
			}
			$this->active->ViewCustomAttributes = "";

			// id
			$this->id->LinkCustomAttributes = "";
			$this->id->HrefValue = "";
			$this->id->TooltipValue = "";

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

			// active
			$this->active->LinkCustomAttributes = "";
			$this->active->HrefValue = "";
			$this->active->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_EDIT) { // Edit row

			// id
			$this->id->EditCustomAttributes = "";
			if (strval($this->id->CurrentValue) <> "") {
				$sFilterWrk = "`id`" . ew_SearchString("=", $this->id->CurrentValue, EW_DATATYPE_NUMBER);
			$sSqlWrk = "SELECT `id`, `id` AS `DispFld`, `username` AS `Disp2Fld`, '' AS `Disp3Fld`, '' AS `Disp4Fld` FROM `account`";
			$sWhereWrk = "";
			if ($sFilterWrk <> "") {
				ew_AddFilter($sWhereWrk, $sFilterWrk);
			}
			if ($sWhereWrk <> "") $sSqlWrk .= " WHERE " . $sWhereWrk;
				$rswrk = $conn->Execute($sSqlWrk);
				if ($rswrk && !$rswrk->EOF) { // Lookup values found
					$this->id->EditValue = $rswrk->fields('DispFld');
					$this->id->EditValue .= ew_ValueSeparator(1,$this->id) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->id->EditValue = $this->id->CurrentValue;
				}
			} else {
				$this->id->EditValue = NULL;
			}
			$this->id->ViewCustomAttributes = "";

			// bandate
			$this->bandate->EditCustomAttributes = "";
			$this->bandate->EditValue = $this->bandate->CurrentValue;
			$this->bandate->EditValue = ew_FormatDateTime($this->bandate->EditValue, 9);
			$this->bandate->ViewCustomAttributes = "";

			// unbandate
			$this->unbandate->EditCustomAttributes = "";
			$this->unbandate->EditValue = ew_HtmlEncode($this->unbandate->CurrentValue);

			// bannedby
			$this->bannedby->EditCustomAttributes = "";
			$this->bannedby->EditValue = ew_HtmlEncode($this->bannedby->CurrentValue);

			// banreason
			$this->banreason->EditCustomAttributes = "";
			$this->banreason->EditValue = ew_HtmlEncode($this->banreason->CurrentValue);

			// active
			$this->active->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->active->FldTagValue(1), $this->active->FldTagCaption(1) <> "" ? $this->active->FldTagCaption(1) : $this->active->FldTagValue(1));
			$arwrk[] = array($this->active->FldTagValue(2), $this->active->FldTagCaption(2) <> "" ? $this->active->FldTagCaption(2) : $this->active->FldTagValue(2));
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect")));
			$this->active->EditValue = $arwrk;

			// Edit refer script
			// id

			$this->id->HrefValue = "";

			// bandate
			$this->bandate->HrefValue = "";

			// unbandate
			$this->unbandate->HrefValue = "";

			// bannedby
			$this->bannedby->HrefValue = "";

			// banreason
			$this->banreason->HrefValue = "";

			// active
			$this->active->HrefValue = "";
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
		if (!is_null($this->id->FormValue) && $this->id->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->id->FldCaption());
		}
		if (!ew_CheckInteger($this->bandate->FormValue)) {
			ew_AddMessage($gsFormError, $this->bandate->FldErrMsg());
		}
		if (!ew_CheckInteger($this->unbandate->FormValue)) {
			ew_AddMessage($gsFormError, $this->unbandate->FldErrMsg());
		}
		if (!is_null($this->bannedby->FormValue) && $this->bannedby->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->bannedby->FldCaption());
		}
		if (!is_null($this->banreason->FormValue) && $this->banreason->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->banreason->FldCaption());
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

	// Update record based on key values
	function EditRow() {
		global $conn, $Security, $Language;
		$sFilter = $this->KeyFilter();
		$this->CurrentFilter = $sFilter;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE)
			return FALSE;
		if ($rs->EOF) {
			$EditRow = FALSE; // Update Failed
		} else {

			// Save old values
			$rsold = &$rs->fields;
			$rsnew = array();

			// id
			// bandate
			// unbandate

			$this->unbandate->SetDbValueDef($rsnew, $this->unbandate->CurrentValue, 0, $this->unbandate->ReadOnly);

			// bannedby
			$this->bannedby->SetDbValueDef($rsnew, $this->bannedby->CurrentValue, "", $this->bannedby->ReadOnly);

			// banreason
			$this->banreason->SetDbValueDef($rsnew, $this->banreason->CurrentValue, "", $this->banreason->ReadOnly);

			// active
			$this->active->SetDbValueDef($rsnew, $this->active->CurrentValue, 0, $this->active->ReadOnly);

			// Call Row Updating event
			$bUpdateRow = $this->Row_Updating($rsold, $rsnew);
			if ($bUpdateRow) {
				$conn->raiseErrorFn = 'ew_ErrorFn';
				if (count($rsnew) > 0)
					$EditRow = $this->Update($rsnew);
				else
					$EditRow = TRUE; // No field to update
				$conn->raiseErrorFn = '';
			} else {
				if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

					// Use the message, do nothing
				} elseif ($this->CancelMessage <> "") {
					$this->setFailureMessage($this->CancelMessage);
					$this->CancelMessage = "";
				} else {
					$this->setFailureMessage($Language->Phrase("UpdateCancelled"));
				}
				$EditRow = FALSE;
			}
		}

		// Call Row_Updated event
		if ($EditRow)
			$this->Row_Updated($rsold, $rsnew);
		$rs->Close();
		return $EditRow;
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
if (!isset($account_banned_edit)) $account_banned_edit = new caccount_banned_edit();

// Page init
$account_banned_edit->Page_Init();

// Page main
$account_banned_edit->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var account_banned_edit = new ew_Page("account_banned_edit");
account_banned_edit.PageID = "edit"; // Page ID
var EW_PAGE_ID = account_banned_edit.PageID; // For backward compatibility

// Form object
var faccount_bannededit = new ew_Form("faccount_bannededit");

// Validate form
faccount_bannededit.Validate = function(fobj) {
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
		elm = fobj.elements["x" + infix + "_id"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account_banned->id->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_bandate"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($account_banned->bandate->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_unbandate"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($account_banned->unbandate->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_bannedby"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account_banned->bannedby->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_banreason"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($account_banned->banreason->FldCaption()) ?>");

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
faccount_bannededit.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
faccount_bannededit.ValidateRequired = true;
<?php } else { ?>
faccount_bannededit.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
faccount_bannededit.Lists["x_id"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_username","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Edit") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $account_banned->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $account_banned->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $account_banned_edit->ShowPageHeader(); ?>
<?php
$account_banned_edit->ShowMessage();
?>
<form name="faccount_bannededit" id="faccount_bannededit" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post" onsubmit="return ewForms[this.id].Submit();">
<br>
<input type="hidden" name="t" value="account_banned">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_account_bannededit" class="ewTable">
<?php if ($account_banned->id->Visible) { // id ?>
	<tr id="r_id"<?php echo $account_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_banned_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->id->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account_banned->id->CellAttributes() ?>><span id="el_account_banned_id">
<span<?php echo $account_banned->id->ViewAttributes() ?>>
<?php echo $account_banned->id->EditValue ?></span>
<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($account_banned->id->CurrentValue) ?>">
</span><?php echo $account_banned->id->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account_banned->bandate->Visible) { // bandate ?>
	<tr id="r_bandate"<?php echo $account_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_banned_bandate"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->bandate->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account_banned->bandate->CellAttributes() ?>><span id="el_account_banned_bandate">
<span<?php echo $account_banned->bandate->ViewAttributes() ?>>
<?php echo $account_banned->bandate->EditValue ?></span>
<input type="hidden" name="x_bandate" id="x_bandate" value="<?php echo ew_HtmlEncode($account_banned->bandate->CurrentValue) ?>">
</span><?php echo $account_banned->bandate->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account_banned->unbandate->Visible) { // unbandate ?>
	<tr id="r_unbandate"<?php echo $account_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_banned_unbandate"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->unbandate->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account_banned->unbandate->CellAttributes() ?>><span id="el_account_banned_unbandate">
<input type="text" name="x_unbandate" id="x_unbandate" size="30" value="<?php echo $account_banned->unbandate->EditValue ?>"<?php echo $account_banned->unbandate->EditAttributes() ?>>
</span><?php echo $account_banned->unbandate->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account_banned->bannedby->Visible) { // bannedby ?>
	<tr id="r_bannedby"<?php echo $account_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_banned_bannedby"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->bannedby->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account_banned->bannedby->CellAttributes() ?>><span id="el_account_banned_bannedby">
<input type="text" name="x_bannedby" id="x_bannedby" size="30" maxlength="50" value="<?php echo $account_banned->bannedby->EditValue ?>"<?php echo $account_banned->bannedby->EditAttributes() ?>>
</span><?php echo $account_banned->bannedby->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account_banned->banreason->Visible) { // banreason ?>
	<tr id="r_banreason"<?php echo $account_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_banned_banreason"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->banreason->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $account_banned->banreason->CellAttributes() ?>><span id="el_account_banned_banreason">
<input type="text" name="x_banreason" id="x_banreason" size="30" maxlength="255" value="<?php echo $account_banned->banreason->EditValue ?>"<?php echo $account_banned->banreason->EditAttributes() ?>>
</span><?php echo $account_banned->banreason->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($account_banned->active->Visible) { // active ?>
	<tr id="r_active"<?php echo $account_banned->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_account_banned_active"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->active->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $account_banned->active->CellAttributes() ?>><span id="el_account_banned_active">
<select id="x_active" name="x_active"<?php echo $account_banned->active->EditAttributes() ?>>
<?php
if (is_array($account_banned->active->EditValue)) {
	$arwrk = $account_banned->active->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($account_banned->active->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
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
</span><?php echo $account_banned->active->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="btnAction" id="btnAction" value="<?php echo ew_BtnCaption($Language->Phrase("EditBtn")) ?>">
</form>
<script type="text/javascript">
faccount_bannededit.Init();
</script>
<?php
$account_banned_edit->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$account_banned_edit->Page_Terminate();
?>
