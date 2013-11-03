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

$rbac_account_permissions_edit = NULL; // Initialize page object first

class crbac_account_permissions_edit extends crbac_account_permissions {

	// Page ID
	var $PageID = 'edit';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'rbac_account_permissions';

	// Page object name
	var $PageObjName = 'rbac_account_permissions_edit';

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

		// Table object (rbac_account_permissions)
		if (!isset($GLOBALS["rbac_account_permissions"])) {
			$GLOBALS["rbac_account_permissions"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["rbac_account_permissions"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'edit', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'rbac_account_permissions', TRUE);

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
		if (@$_GET["accountId"] <> "")
			$this->accountId->setQueryStringValue($_GET["accountId"]);
		if (@$_GET["permissionId"] <> "")
			$this->permissionId->setQueryStringValue($_GET["permissionId"]);
		if (@$_GET["realmId"] <> "")
			$this->realmId->setQueryStringValue($_GET["realmId"]);

		// Process form if post back
		if (@$_POST["a_edit"] <> "") {
			$this->CurrentAction = $_POST["a_edit"]; // Get action code
			$this->LoadFormValues(); // Get form values
		} else {
			$this->CurrentAction = "I"; // Default action is display
		}

		// Check if valid key
		if ($this->accountId->CurrentValue == "")
			$this->Page_Terminate("rbac_account_permissionslist.php"); // Invalid key, return to list
		if ($this->permissionId->CurrentValue == "")
			$this->Page_Terminate("rbac_account_permissionslist.php"); // Invalid key, return to list
		if ($this->realmId->CurrentValue == "")
			$this->Page_Terminate("rbac_account_permissionslist.php"); // Invalid key, return to list

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
					$this->Page_Terminate("rbac_account_permissionslist.php"); // No matching record, return to list
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
		if (!$this->accountId->FldIsDetailKey) {
			$this->accountId->setFormValue($objForm->GetValue("x_accountId"));
		}
		if (!$this->permissionId->FldIsDetailKey) {
			$this->permissionId->setFormValue($objForm->GetValue("x_permissionId"));
		}
		if (!$this->granted->FldIsDetailKey) {
			$this->granted->setFormValue($objForm->GetValue("x_granted"));
		}
		if (!$this->realmId->FldIsDetailKey) {
			$this->realmId->setFormValue($objForm->GetValue("x_realmId"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadRow();
		$this->accountId->CurrentValue = $this->accountId->FormValue;
		$this->permissionId->CurrentValue = $this->permissionId->FormValue;
		$this->granted->CurrentValue = $this->granted->FormValue;
		$this->realmId->CurrentValue = $this->realmId->FormValue;
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
		} elseif ($this->RowType == EW_ROWTYPE_EDIT) { // Edit row

			// accountId
			$this->accountId->EditCustomAttributes = "";
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
					$this->accountId->EditValue = $rswrk->fields('DispFld');
					$this->accountId->EditValue .= ew_ValueSeparator(1,$this->accountId) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->accountId->EditValue = $this->accountId->CurrentValue;
				}
			} else {
				$this->accountId->EditValue = NULL;
			}
			$this->accountId->ViewCustomAttributes = "";

			// permissionId
			$this->permissionId->EditCustomAttributes = "";
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
					$this->permissionId->EditValue = $rswrk->fields('DispFld');
					$this->permissionId->EditValue .= ew_ValueSeparator(1,$this->permissionId) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->permissionId->EditValue = $this->permissionId->CurrentValue;
				}
			} else {
				$this->permissionId->EditValue = NULL;
			}
			}
			$this->permissionId->ViewCustomAttributes = "";

			// granted
			$this->granted->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->granted->FldTagValue(1), $this->granted->FldTagCaption(1) <> "" ? $this->granted->FldTagCaption(1) : $this->granted->FldTagValue(1));
			$arwrk[] = array($this->granted->FldTagValue(2), $this->granted->FldTagCaption(2) <> "" ? $this->granted->FldTagCaption(2) : $this->granted->FldTagValue(2));
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect")));
			$this->granted->EditValue = $arwrk;

			// realmId
			$this->realmId->EditCustomAttributes = "";
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
					$this->realmId->EditValue = $rswrk->fields('DispFld');
					$this->realmId->EditValue .= ew_ValueSeparator(1,$this->realmId) . $rswrk->fields('Disp2Fld');
					$rswrk->Close();
				} else {
					$this->realmId->EditValue = $this->realmId->CurrentValue;
				}
			} else {
				$this->realmId->EditValue = NULL;
			}
			$this->realmId->ViewCustomAttributes = "";

			// Edit refer script
			// accountId

			$this->accountId->HrefValue = "";

			// permissionId
			$this->permissionId->HrefValue = "";

			// granted
			$this->granted->HrefValue = "";

			// realmId
			$this->realmId->HrefValue = "";
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
		if (!is_null($this->accountId->FormValue) && $this->accountId->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->accountId->FldCaption());
		}
		if (!is_null($this->permissionId->FormValue) && $this->permissionId->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->permissionId->FldCaption());
		}
		if (!is_null($this->granted->FormValue) && $this->granted->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->granted->FldCaption());
		}
		if (!is_null($this->realmId->FormValue) && $this->realmId->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->realmId->FldCaption());
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

			// accountId
			// permissionId
			// granted

			$this->granted->SetDbValueDef($rsnew, $this->granted->CurrentValue, 0, $this->granted->ReadOnly);

			// realmId
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
if (!isset($rbac_account_permissions_edit)) $rbac_account_permissions_edit = new crbac_account_permissions_edit();

// Page init
$rbac_account_permissions_edit->Page_Init();

// Page main
$rbac_account_permissions_edit->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var rbac_account_permissions_edit = new ew_Page("rbac_account_permissions_edit");
rbac_account_permissions_edit.PageID = "edit"; // Page ID
var EW_PAGE_ID = rbac_account_permissions_edit.PageID; // For backward compatibility

// Form object
var frbac_account_permissionsedit = new ew_Form("frbac_account_permissionsedit");

// Validate form
frbac_account_permissionsedit.Validate = function(fobj) {
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
		elm = fobj.elements["x" + infix + "_accountId"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($rbac_account_permissions->accountId->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_permissionId"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($rbac_account_permissions->permissionId->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_granted"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($rbac_account_permissions->granted->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_realmId"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($rbac_account_permissions->realmId->FldCaption()) ?>");

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
frbac_account_permissionsedit.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frbac_account_permissionsedit.ValidateRequired = true;
<?php } else { ?>
frbac_account_permissionsedit.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
frbac_account_permissionsedit.Lists["x_accountId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_username","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
frbac_account_permissionsedit.Lists["x_permissionId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_name","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
frbac_account_permissionsedit.Lists["x_realmId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_name","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Edit") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $rbac_account_permissions->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $rbac_account_permissions->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $rbac_account_permissions_edit->ShowPageHeader(); ?>
<?php
$rbac_account_permissions_edit->ShowMessage();
?>
<form name="frbac_account_permissionsedit" id="frbac_account_permissionsedit" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post" onsubmit="return ewForms[this.id].Submit();">
<br>
<input type="hidden" name="t" value="rbac_account_permissions">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_rbac_account_permissionsedit" class="ewTable">
<?php if ($rbac_account_permissions->accountId->Visible) { // accountId ?>
	<tr id="r_accountId"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_accountId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->accountId->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->accountId->CellAttributes() ?>><span id="el_rbac_account_permissions_accountId">
<span<?php echo $rbac_account_permissions->accountId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->accountId->EditValue ?></span>
<input type="hidden" name="x_accountId" id="x_accountId" value="<?php echo ew_HtmlEncode($rbac_account_permissions->accountId->CurrentValue) ?>">
</span><?php echo $rbac_account_permissions->accountId->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($rbac_account_permissions->permissionId->Visible) { // permissionId ?>
	<tr id="r_permissionId"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_permissionId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->permissionId->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->permissionId->CellAttributes() ?>><span id="el_rbac_account_permissions_permissionId">
<span<?php echo $rbac_account_permissions->permissionId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->permissionId->EditValue ?></span>
<input type="hidden" name="x_permissionId" id="x_permissionId" value="<?php echo ew_HtmlEncode($rbac_account_permissions->permissionId->CurrentValue) ?>">
</span><?php echo $rbac_account_permissions->permissionId->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($rbac_account_permissions->granted->Visible) { // granted ?>
	<tr id="r_granted"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_granted"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->granted->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->granted->CellAttributes() ?>><span id="el_rbac_account_permissions_granted">
<select id="x_granted" name="x_granted"<?php echo $rbac_account_permissions->granted->EditAttributes() ?>>
<?php
if (is_array($rbac_account_permissions->granted->EditValue)) {
	$arwrk = $rbac_account_permissions->granted->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($rbac_account_permissions->granted->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
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
</span><?php echo $rbac_account_permissions->granted->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($rbac_account_permissions->realmId->Visible) { // realmId ?>
	<tr id="r_realmId"<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_rbac_account_permissions_realmId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->realmId->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $rbac_account_permissions->realmId->CellAttributes() ?>><span id="el_rbac_account_permissions_realmId">
<span<?php echo $rbac_account_permissions->realmId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->realmId->EditValue ?></span>
<input type="hidden" name="x_realmId" id="x_realmId" value="<?php echo ew_HtmlEncode($rbac_account_permissions->realmId->CurrentValue) ?>">
</span><?php echo $rbac_account_permissions->realmId->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="btnAction" id="btnAction" value="<?php echo ew_BtnCaption($Language->Phrase("EditBtn")) ?>">
</form>
<script type="text/javascript">
frbac_account_permissionsedit.Init();
</script>
<?php
$rbac_account_permissions_edit->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$rbac_account_permissions_edit->Page_Terminate();
?>
