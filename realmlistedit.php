<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "includes/framework/ewcfg9.php" ?>
<?php include_once "includes/framework/ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "realmlistinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$realmlist_edit = NULL; // Initialize page object first

class crealmlist_edit extends crealmlist {

	// Page ID
	var $PageID = 'edit';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'realmlist';

	// Page object name
	var $PageObjName = 'realmlist_edit';

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

		// Table object (realmlist)
		if (!isset($GLOBALS["realmlist"])) {
			$GLOBALS["realmlist"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["realmlist"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'edit', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'realmlist', TRUE);

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

		// Process form if post back
		if (@$_POST["a_edit"] <> "") {
			$this->CurrentAction = $_POST["a_edit"]; // Get action code
			$this->LoadFormValues(); // Get form values
		} else {
			$this->CurrentAction = "I"; // Default action is display
		}

		// Check if valid key
		if ($this->id->CurrentValue == "")
			$this->Page_Terminate("realmlistlist.php"); // Invalid key, return to list

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
					$this->Page_Terminate("realmlistlist.php"); // No matching record, return to list
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
		if (!$this->id->FldIsDetailKey)
			$this->id->setFormValue($objForm->GetValue("x_id"));
		if (!$this->name->FldIsDetailKey) {
			$this->name->setFormValue($objForm->GetValue("x_name"));
		}
		if (!$this->address->FldIsDetailKey) {
			$this->address->setFormValue($objForm->GetValue("x_address"));
		}
		if (!$this->localAddress->FldIsDetailKey) {
			$this->localAddress->setFormValue($objForm->GetValue("x_localAddress"));
		}
		if (!$this->localSubnetMask->FldIsDetailKey) {
			$this->localSubnetMask->setFormValue($objForm->GetValue("x_localSubnetMask"));
		}
		if (!$this->port->FldIsDetailKey) {
			$this->port->setFormValue($objForm->GetValue("x_port"));
		}
		if (!$this->icon->FldIsDetailKey) {
			$this->icon->setFormValue($objForm->GetValue("x_icon"));
		}
		if (!$this->timezone->FldIsDetailKey) {
			$this->timezone->setFormValue($objForm->GetValue("x_timezone"));
		}
		if (!$this->allowedSecurityLevel->FldIsDetailKey) {
			$this->allowedSecurityLevel->setFormValue($objForm->GetValue("x_allowedSecurityLevel"));
		}
		if (!$this->gamebuild->FldIsDetailKey) {
			$this->gamebuild->setFormValue($objForm->GetValue("x_gamebuild"));
		}
	}

	// Restore form values
	function RestoreFormValues() {
		global $objForm;
		$this->LoadRow();
		$this->id->CurrentValue = $this->id->FormValue;
		$this->name->CurrentValue = $this->name->FormValue;
		$this->address->CurrentValue = $this->address->FormValue;
		$this->localAddress->CurrentValue = $this->localAddress->FormValue;
		$this->localSubnetMask->CurrentValue = $this->localSubnetMask->FormValue;
		$this->port->CurrentValue = $this->port->FormValue;
		$this->icon->CurrentValue = $this->icon->FormValue;
		$this->timezone->CurrentValue = $this->timezone->FormValue;
		$this->allowedSecurityLevel->CurrentValue = $this->allowedSecurityLevel->FormValue;
		$this->gamebuild->CurrentValue = $this->gamebuild->FormValue;
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

			// gamebuild
			$this->gamebuild->LinkCustomAttributes = "";
			$this->gamebuild->HrefValue = "";
			$this->gamebuild->TooltipValue = "";
		} elseif ($this->RowType == EW_ROWTYPE_EDIT) { // Edit row

			// id
			$this->id->EditCustomAttributes = "";
			$this->id->EditValue = $this->id->CurrentValue;
			$this->id->ViewCustomAttributes = "";

			// name
			$this->name->EditCustomAttributes = "";
			$this->name->EditValue = ew_HtmlEncode($this->name->CurrentValue);

			// address
			$this->address->EditCustomAttributes = "";
			$this->address->EditValue = ew_HtmlEncode($this->address->CurrentValue);

			// localAddress
			$this->localAddress->EditCustomAttributes = "";
			$this->localAddress->EditValue = ew_HtmlEncode($this->localAddress->CurrentValue);

			// localSubnetMask
			$this->localSubnetMask->EditCustomAttributes = "";
			$this->localSubnetMask->EditValue = ew_HtmlEncode($this->localSubnetMask->CurrentValue);

			// port
			$this->port->EditCustomAttributes = "";
			$this->port->EditValue = ew_HtmlEncode($this->port->CurrentValue);

			// icon
			$this->icon->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->icon->FldTagValue(1), $this->icon->FldTagCaption(1) <> "" ? $this->icon->FldTagCaption(1) : $this->icon->FldTagValue(1));
			$arwrk[] = array($this->icon->FldTagValue(2), $this->icon->FldTagCaption(2) <> "" ? $this->icon->FldTagCaption(2) : $this->icon->FldTagValue(2));
			$arwrk[] = array($this->icon->FldTagValue(3), $this->icon->FldTagCaption(3) <> "" ? $this->icon->FldTagCaption(3) : $this->icon->FldTagValue(3));
			$arwrk[] = array($this->icon->FldTagValue(4), $this->icon->FldTagCaption(4) <> "" ? $this->icon->FldTagCaption(4) : $this->icon->FldTagValue(4));
			$arwrk[] = array($this->icon->FldTagValue(5), $this->icon->FldTagCaption(5) <> "" ? $this->icon->FldTagCaption(5) : $this->icon->FldTagValue(5));
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect")));
			$this->icon->EditValue = $arwrk;

			// timezone
			$this->timezone->EditCustomAttributes = "";
			$arwrk = array();
			$arwrk[] = array($this->timezone->FldTagValue(1), $this->timezone->FldTagCaption(1) <> "" ? $this->timezone->FldTagCaption(1) : $this->timezone->FldTagValue(1));
			$arwrk[] = array($this->timezone->FldTagValue(2), $this->timezone->FldTagCaption(2) <> "" ? $this->timezone->FldTagCaption(2) : $this->timezone->FldTagValue(2));
			$arwrk[] = array($this->timezone->FldTagValue(3), $this->timezone->FldTagCaption(3) <> "" ? $this->timezone->FldTagCaption(3) : $this->timezone->FldTagValue(3));
			$arwrk[] = array($this->timezone->FldTagValue(4), $this->timezone->FldTagCaption(4) <> "" ? $this->timezone->FldTagCaption(4) : $this->timezone->FldTagValue(4));
			$arwrk[] = array($this->timezone->FldTagValue(5), $this->timezone->FldTagCaption(5) <> "" ? $this->timezone->FldTagCaption(5) : $this->timezone->FldTagValue(5));
			$arwrk[] = array($this->timezone->FldTagValue(6), $this->timezone->FldTagCaption(6) <> "" ? $this->timezone->FldTagCaption(6) : $this->timezone->FldTagValue(6));
			$arwrk[] = array($this->timezone->FldTagValue(7), $this->timezone->FldTagCaption(7) <> "" ? $this->timezone->FldTagCaption(7) : $this->timezone->FldTagValue(7));
			$arwrk[] = array($this->timezone->FldTagValue(8), $this->timezone->FldTagCaption(8) <> "" ? $this->timezone->FldTagCaption(8) : $this->timezone->FldTagValue(8));
			$arwrk[] = array($this->timezone->FldTagValue(9), $this->timezone->FldTagCaption(9) <> "" ? $this->timezone->FldTagCaption(9) : $this->timezone->FldTagValue(9));
			$arwrk[] = array($this->timezone->FldTagValue(10), $this->timezone->FldTagCaption(10) <> "" ? $this->timezone->FldTagCaption(10) : $this->timezone->FldTagValue(10));
			$arwrk[] = array($this->timezone->FldTagValue(11), $this->timezone->FldTagCaption(11) <> "" ? $this->timezone->FldTagCaption(11) : $this->timezone->FldTagValue(11));
			$arwrk[] = array($this->timezone->FldTagValue(12), $this->timezone->FldTagCaption(12) <> "" ? $this->timezone->FldTagCaption(12) : $this->timezone->FldTagValue(12));
			$arwrk[] = array($this->timezone->FldTagValue(13), $this->timezone->FldTagCaption(13) <> "" ? $this->timezone->FldTagCaption(13) : $this->timezone->FldTagValue(13));
			$arwrk[] = array($this->timezone->FldTagValue(14), $this->timezone->FldTagCaption(14) <> "" ? $this->timezone->FldTagCaption(14) : $this->timezone->FldTagValue(14));
			$arwrk[] = array($this->timezone->FldTagValue(15), $this->timezone->FldTagCaption(15) <> "" ? $this->timezone->FldTagCaption(15) : $this->timezone->FldTagValue(15));
			$arwrk[] = array($this->timezone->FldTagValue(16), $this->timezone->FldTagCaption(16) <> "" ? $this->timezone->FldTagCaption(16) : $this->timezone->FldTagValue(16));
			$arwrk[] = array($this->timezone->FldTagValue(17), $this->timezone->FldTagCaption(17) <> "" ? $this->timezone->FldTagCaption(17) : $this->timezone->FldTagValue(17));
			$arwrk[] = array($this->timezone->FldTagValue(18), $this->timezone->FldTagCaption(18) <> "" ? $this->timezone->FldTagCaption(18) : $this->timezone->FldTagValue(18));
			$arwrk[] = array($this->timezone->FldTagValue(19), $this->timezone->FldTagCaption(19) <> "" ? $this->timezone->FldTagCaption(19) : $this->timezone->FldTagValue(19));
			$arwrk[] = array($this->timezone->FldTagValue(20), $this->timezone->FldTagCaption(20) <> "" ? $this->timezone->FldTagCaption(20) : $this->timezone->FldTagValue(20));
			$arwrk[] = array($this->timezone->FldTagValue(21), $this->timezone->FldTagCaption(21) <> "" ? $this->timezone->FldTagCaption(21) : $this->timezone->FldTagValue(21));
			$arwrk[] = array($this->timezone->FldTagValue(22), $this->timezone->FldTagCaption(22) <> "" ? $this->timezone->FldTagCaption(22) : $this->timezone->FldTagValue(22));
			$arwrk[] = array($this->timezone->FldTagValue(23), $this->timezone->FldTagCaption(23) <> "" ? $this->timezone->FldTagCaption(23) : $this->timezone->FldTagValue(23));
			$arwrk[] = array($this->timezone->FldTagValue(24), $this->timezone->FldTagCaption(24) <> "" ? $this->timezone->FldTagCaption(24) : $this->timezone->FldTagValue(24));
			$arwrk[] = array($this->timezone->FldTagValue(25), $this->timezone->FldTagCaption(25) <> "" ? $this->timezone->FldTagCaption(25) : $this->timezone->FldTagValue(25));
			$arwrk[] = array($this->timezone->FldTagValue(26), $this->timezone->FldTagCaption(26) <> "" ? $this->timezone->FldTagCaption(26) : $this->timezone->FldTagValue(26));
			$arwrk[] = array($this->timezone->FldTagValue(27), $this->timezone->FldTagCaption(27) <> "" ? $this->timezone->FldTagCaption(27) : $this->timezone->FldTagValue(27));
			$arwrk[] = array($this->timezone->FldTagValue(28), $this->timezone->FldTagCaption(28) <> "" ? $this->timezone->FldTagCaption(28) : $this->timezone->FldTagValue(28));
			$arwrk[] = array($this->timezone->FldTagValue(29), $this->timezone->FldTagCaption(29) <> "" ? $this->timezone->FldTagCaption(29) : $this->timezone->FldTagValue(29));
			array_unshift($arwrk, array("", $Language->Phrase("PleaseSelect")));
			$this->timezone->EditValue = $arwrk;

			// allowedSecurityLevel
			$this->allowedSecurityLevel->EditCustomAttributes = "";
			$this->allowedSecurityLevel->EditValue = ew_HtmlEncode($this->allowedSecurityLevel->CurrentValue);

			// gamebuild
			$this->gamebuild->EditCustomAttributes = "";
			$this->gamebuild->EditValue = ew_HtmlEncode($this->gamebuild->CurrentValue);

			// Edit refer script
			// id

			$this->id->HrefValue = "";

			// name
			$this->name->HrefValue = "";

			// address
			$this->address->HrefValue = "";

			// localAddress
			$this->localAddress->HrefValue = "";

			// localSubnetMask
			$this->localSubnetMask->HrefValue = "";

			// port
			$this->port->HrefValue = "";

			// icon
			$this->icon->HrefValue = "";

			// timezone
			$this->timezone->HrefValue = "";

			// allowedSecurityLevel
			$this->allowedSecurityLevel->HrefValue = "";

			// gamebuild
			$this->gamebuild->HrefValue = "";
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
		if (!is_null($this->name->FormValue) && $this->name->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->name->FldCaption());
		}
		if (!is_null($this->address->FormValue) && $this->address->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->address->FldCaption());
		}
		if (!is_null($this->localAddress->FormValue) && $this->localAddress->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->localAddress->FldCaption());
		}
		if (!is_null($this->localSubnetMask->FormValue) && $this->localSubnetMask->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->localSubnetMask->FldCaption());
		}
		if (!is_null($this->port->FormValue) && $this->port->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->port->FldCaption());
		}
		if (!ew_CheckInteger($this->port->FormValue)) {
			ew_AddMessage($gsFormError, $this->port->FldErrMsg());
		}
		if (!is_null($this->icon->FormValue) && $this->icon->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->icon->FldCaption());
		}
		if (!is_null($this->timezone->FormValue) && $this->timezone->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->timezone->FldCaption());
		}
		if (!is_null($this->allowedSecurityLevel->FormValue) && $this->allowedSecurityLevel->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->allowedSecurityLevel->FldCaption());
		}
		if (!ew_CheckInteger($this->allowedSecurityLevel->FormValue)) {
			ew_AddMessage($gsFormError, $this->allowedSecurityLevel->FldErrMsg());
		}
		if (!is_null($this->gamebuild->FormValue) && $this->gamebuild->FormValue == "") {
			ew_AddMessage($gsFormError, $Language->Phrase("EnterRequiredField") . " - " . $this->gamebuild->FldCaption());
		}
		if (!ew_CheckInteger($this->gamebuild->FormValue)) {
			ew_AddMessage($gsFormError, $this->gamebuild->FldErrMsg());
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
			if ($this->name->CurrentValue <> "") { // Check field with unique index
			$sFilterChk = "(`name` = '" . ew_AdjustSql($this->name->CurrentValue) . "')";
			$sFilterChk .= " AND NOT (" . $sFilter . ")";
			$this->CurrentFilter = $sFilterChk;
			$sSqlChk = $this->SQL();
			$conn->raiseErrorFn = 'ew_ErrorFn';
			$rsChk = $conn->Execute($sSqlChk);
			$conn->raiseErrorFn = '';
			if ($rsChk === FALSE) {
				return FALSE;
			} elseif (!$rsChk->EOF) {
				$sIdxErrMsg = str_replace("%f", $this->name->FldCaption(), $Language->Phrase("DupIndex"));
				$sIdxErrMsg = str_replace("%v", $this->name->CurrentValue, $sIdxErrMsg);
				$this->setFailureMessage($sIdxErrMsg);
				$rsChk->Close();
				return FALSE;
			}
			$rsChk->Close();
		}
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

			// name
			$this->name->SetDbValueDef($rsnew, $this->name->CurrentValue, "", $this->name->ReadOnly);

			// address
			$this->address->SetDbValueDef($rsnew, $this->address->CurrentValue, "", $this->address->ReadOnly);

			// localAddress
			$this->localAddress->SetDbValueDef($rsnew, $this->localAddress->CurrentValue, "", $this->localAddress->ReadOnly);

			// localSubnetMask
			$this->localSubnetMask->SetDbValueDef($rsnew, $this->localSubnetMask->CurrentValue, "", $this->localSubnetMask->ReadOnly);

			// port
			$this->port->SetDbValueDef($rsnew, $this->port->CurrentValue, 0, $this->port->ReadOnly);

			// icon
			$this->icon->SetDbValueDef($rsnew, $this->icon->CurrentValue, 0, $this->icon->ReadOnly);

			// timezone
			$this->timezone->SetDbValueDef($rsnew, $this->timezone->CurrentValue, 0, $this->timezone->ReadOnly);

			// allowedSecurityLevel
			$this->allowedSecurityLevel->SetDbValueDef($rsnew, $this->allowedSecurityLevel->CurrentValue, 0, $this->allowedSecurityLevel->ReadOnly);

			// gamebuild
			$this->gamebuild->SetDbValueDef($rsnew, $this->gamebuild->CurrentValue, 0, $this->gamebuild->ReadOnly);

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
if (!isset($realmlist_edit)) $realmlist_edit = new crealmlist_edit();

// Page init
$realmlist_edit->Page_Init();

// Page main
$realmlist_edit->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var realmlist_edit = new ew_Page("realmlist_edit");
realmlist_edit.PageID = "edit"; // Page ID
var EW_PAGE_ID = realmlist_edit.PageID; // For backward compatibility

// Form object
var frealmlistedit = new ew_Form("frealmlistedit");

// Validate form
frealmlistedit.Validate = function(fobj) {
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
		elm = fobj.elements["x" + infix + "_name"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->name->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_address"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->address->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_localAddress"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->localAddress->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_localSubnetMask"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->localSubnetMask->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_port"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->port->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_port"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($realmlist->port->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_icon"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->icon->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_timezone"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->timezone->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_allowedSecurityLevel"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->allowedSecurityLevel->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_allowedSecurityLevel"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($realmlist->allowedSecurityLevel->FldErrMsg()) ?>");
		elm = fobj.elements["x" + infix + "_gamebuild"];
		if (elm && !ew_HasValue(elm))
			return ew_OnError(this, elm, ewLanguage.Phrase("EnterRequiredField") + " - <?php echo ew_JsEncode2($realmlist->gamebuild->FldCaption()) ?>");
		elm = fobj.elements["x" + infix + "_gamebuild"];
		if (elm && !ew_CheckInteger(elm.value))
			return ew_OnError(this, elm, "<?php echo ew_JsEncode2($realmlist->gamebuild->FldErrMsg()) ?>");

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
frealmlistedit.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frealmlistedit.ValidateRequired = true;
<?php } else { ?>
frealmlistedit.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Edit") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $realmlist->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $realmlist->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $realmlist_edit->ShowPageHeader(); ?>
<?php
$realmlist_edit->ShowMessage();
?>
<form name="frealmlistedit" id="frealmlistedit" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post" onsubmit="return ewForms[this.id].Submit();">
<br>
<input type="hidden" name="t" value="realmlist">
<input type="hidden" name="a_edit" id="a_edit" value="U">
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_realmlistedit" class="ewTable">
<?php if ($realmlist->id->Visible) { // id ?>
	<tr id="r_id"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->id->FldCaption() ?></td></tr></table></span></td>
		<td<?php echo $realmlist->id->CellAttributes() ?>><span id="el_realmlist_id">
<span<?php echo $realmlist->id->ViewAttributes() ?>>
<?php echo $realmlist->id->EditValue ?></span>
<input type="hidden" name="x_id" id="x_id" value="<?php echo ew_HtmlEncode($realmlist->id->CurrentValue) ?>">
</span><?php echo $realmlist->id->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->name->Visible) { // name ?>
	<tr id="r_name"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_name"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->name->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->name->CellAttributes() ?>><span id="el_realmlist_name">
<input type="text" name="x_name" id="x_name" size="30" maxlength="32" value="<?php echo $realmlist->name->EditValue ?>"<?php echo $realmlist->name->EditAttributes() ?>>
</span><?php echo $realmlist->name->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->address->Visible) { // address ?>
	<tr id="r_address"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_address"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->address->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->address->CellAttributes() ?>><span id="el_realmlist_address">
<input type="text" name="x_address" id="x_address" size="30" maxlength="255" value="<?php echo $realmlist->address->EditValue ?>"<?php echo $realmlist->address->EditAttributes() ?>>
</span><?php echo $realmlist->address->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->localAddress->Visible) { // localAddress ?>
	<tr id="r_localAddress"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_localAddress"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->localAddress->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->localAddress->CellAttributes() ?>><span id="el_realmlist_localAddress">
<input type="text" name="x_localAddress" id="x_localAddress" size="30" maxlength="255" value="<?php echo $realmlist->localAddress->EditValue ?>"<?php echo $realmlist->localAddress->EditAttributes() ?>>
</span><?php echo $realmlist->localAddress->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->localSubnetMask->Visible) { // localSubnetMask ?>
	<tr id="r_localSubnetMask"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_localSubnetMask"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->localSubnetMask->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->localSubnetMask->CellAttributes() ?>><span id="el_realmlist_localSubnetMask">
<input type="text" name="x_localSubnetMask" id="x_localSubnetMask" size="30" maxlength="255" value="<?php echo $realmlist->localSubnetMask->EditValue ?>"<?php echo $realmlist->localSubnetMask->EditAttributes() ?>>
</span><?php echo $realmlist->localSubnetMask->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->port->Visible) { // port ?>
	<tr id="r_port"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_port"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->port->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->port->CellAttributes() ?>><span id="el_realmlist_port">
<input type="text" name="x_port" id="x_port" size="30" value="<?php echo $realmlist->port->EditValue ?>"<?php echo $realmlist->port->EditAttributes() ?>>
</span><?php echo $realmlist->port->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->icon->Visible) { // icon ?>
	<tr id="r_icon"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_icon"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->icon->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->icon->CellAttributes() ?>><span id="el_realmlist_icon">
<select id="x_icon" name="x_icon"<?php echo $realmlist->icon->EditAttributes() ?>>
<?php
if (is_array($realmlist->icon->EditValue)) {
	$arwrk = $realmlist->icon->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($realmlist->icon->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
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
</span><?php echo $realmlist->icon->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->timezone->Visible) { // timezone ?>
	<tr id="r_timezone"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_timezone"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->timezone->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->timezone->CellAttributes() ?>><span id="el_realmlist_timezone">
<select id="x_timezone" name="x_timezone"<?php echo $realmlist->timezone->EditAttributes() ?>>
<?php
if (is_array($realmlist->timezone->EditValue)) {
	$arwrk = $realmlist->timezone->EditValue;
	$rowswrk = count($arwrk);
	$emptywrk = TRUE;
	for ($rowcntwrk = 0; $rowcntwrk < $rowswrk; $rowcntwrk++) {
		$selwrk = (strval($realmlist->timezone->CurrentValue) == strval($arwrk[$rowcntwrk][0])) ? " selected=\"selected\"" : "";
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
</span><?php echo $realmlist->timezone->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->allowedSecurityLevel->Visible) { // allowedSecurityLevel ?>
	<tr id="r_allowedSecurityLevel"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_allowedSecurityLevel"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->allowedSecurityLevel->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->allowedSecurityLevel->CellAttributes() ?>><span id="el_realmlist_allowedSecurityLevel">
<input type="text" name="x_allowedSecurityLevel" id="x_allowedSecurityLevel" size="30" value="<?php echo $realmlist->allowedSecurityLevel->EditValue ?>"<?php echo $realmlist->allowedSecurityLevel->EditAttributes() ?>>
</span><?php echo $realmlist->allowedSecurityLevel->CustomMsg ?></td>
	</tr>
<?php } ?>
<?php if ($realmlist->gamebuild->Visible) { // gamebuild ?>
	<tr id="r_gamebuild"<?php echo $realmlist->RowAttributes() ?>>
		<td class="ewTableHeader"><span id="elh_realmlist_gamebuild"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->gamebuild->FldCaption() ?><?php echo $Language->Phrase("FieldRequiredIndicator") ?></td></tr></table></span></td>
		<td<?php echo $realmlist->gamebuild->CellAttributes() ?>><span id="el_realmlist_gamebuild">
<input type="text" name="x_gamebuild" id="x_gamebuild" size="30" value="<?php echo $realmlist->gamebuild->EditValue ?>"<?php echo $realmlist->gamebuild->EditAttributes() ?>>
</span><?php echo $realmlist->gamebuild->CustomMsg ?></td>
	</tr>
<?php } ?>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="btnAction" id="btnAction" value="<?php echo ew_BtnCaption($Language->Phrase("EditBtn")) ?>">
</form>
<script type="text/javascript">
frealmlistedit.Init();
</script>
<?php
$realmlist_edit->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$realmlist_edit->Page_Terminate();
?>
