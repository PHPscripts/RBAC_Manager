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

$realmlist_delete = NULL; // Initialize page object first

class crealmlist_delete extends crealmlist {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'realmlist';

	// Page object name
	var $PageObjName = 'realmlist_delete';

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
			define("EW_PAGE_ID", 'delete', TRUE);

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
	var $TotalRecs = 0;
	var $RecCnt;
	var $RecKeys = array();
	var $Recordset;
	var $StartRowCnt = 1;
	var $RowCnt = 0;

	//
	// Page main
	//
	function Page_Main() {
		global $Language;

		// Load key parameters
		$this->RecKeys = $this->GetRecordKeys(); // Load record keys
		$sFilter = $this->GetKeyFilter();
		if ($sFilter == "")
			$this->Page_Terminate("realmlistlist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in realmlist class, realmlistinfo.php

		$this->CurrentFilter = $sFilter;

		// Get action
		if (@$_POST["a_delete"] <> "") {
			$this->CurrentAction = $_POST["a_delete"];
		} else {
			$this->CurrentAction = "I"; // Display record
		}
		switch ($this->CurrentAction) {
			case "D": // Delete
				$this->SendEmail = TRUE; // Send email on delete success
				if ($this->DeleteRows()) { // delete rows
					if ($this->getSuccessMessage() == "")
						$this->setSuccessMessage($Language->Phrase("DeleteSuccess")); // Set up success message
					$this->Page_Terminate($this->getReturnUrl()); // Return to caller
				}
		}
	}

// No functions
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

	//
	// Delete records based on current filter
	//
	function DeleteRows() {
		global $conn, $Language, $Security;
		$DeleteRows = TRUE;
		$sSql = $this->SQL();
		$conn->raiseErrorFn = 'ew_ErrorFn';
		$rs = $conn->Execute($sSql);
		$conn->raiseErrorFn = '';
		if ($rs === FALSE) {
			return FALSE;
		} elseif ($rs->EOF) {
			$this->setFailureMessage($Language->Phrase("NoRecord")); // No record found
			$rs->Close();
			return FALSE;
		} else {
			$this->LoadRowValues($rs); // Load row values
		}
		$conn->BeginTrans();

		// Clone old rows
		$rsold = ($rs) ? $rs->GetRows() : array();
		if ($rs)
			$rs->Close();

		// Call row deleting event
		if ($DeleteRows) {
			foreach ($rsold as $row) {
				$DeleteRows = $this->Row_Deleting($row);
				if (!$DeleteRows) break;
			}
		}
		if ($DeleteRows) {
			$sKey = "";
			foreach ($rsold as $row) {
				$sThisKey = "";
				if ($sThisKey <> "") $sThisKey .= $GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"];
				$sThisKey .= $row['id'];
				$conn->raiseErrorFn = 'ew_ErrorFn';
				$DeleteRows = $this->Delete($row); // Delete
				$conn->raiseErrorFn = '';
				if ($DeleteRows === FALSE)
					break;
				if ($sKey <> "") $sKey .= ", ";
				$sKey .= $sThisKey;
			}
		} else {

			// Set up error message
			if ($this->getSuccessMessage() <> "" || $this->getFailureMessage() <> "") {

				// Use the message, do nothing
			} elseif ($this->CancelMessage <> "") {
				$this->setFailureMessage($this->CancelMessage);
				$this->CancelMessage = "";
			} else {
				$this->setFailureMessage($Language->Phrase("DeleteCancelled"));
			}
		}
		if ($DeleteRows) {
			$conn->CommitTrans(); // Commit the changes
		} else {
			$conn->RollbackTrans(); // Rollback changes
		}

		// Call Row Deleted event
		if ($DeleteRows) {
			foreach ($rsold as $row) {
				$this->Row_Deleted($row);
			}
		}
		return $DeleteRows;
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
if (!isset($realmlist_delete)) $realmlist_delete = new crealmlist_delete();

// Page init
$realmlist_delete->Page_Init();

// Page main
$realmlist_delete->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var realmlist_delete = new ew_Page("realmlist_delete");
realmlist_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = realmlist_delete.PageID; // For backward compatibility

// Form object
var frealmlistdelete = new ew_Form("frealmlistdelete");

// Form_CustomValidate event
frealmlistdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frealmlistdelete.ValidateRequired = true;
<?php } else { ?>
frealmlistdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($realmlist_delete->Recordset = $realmlist_delete->LoadRecordset())
	$realmlist_deleteTotalRecs = $realmlist_delete->Recordset->RecordCount(); // Get record count
if ($realmlist_deleteTotalRecs <= 0) { // No record found, exit
	if ($realmlist_delete->Recordset)
		$realmlist_delete->Recordset->Close();
	$realmlist_delete->Page_Terminate("realmlistlist.php"); // Return to list
}
?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Delete") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $realmlist->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $realmlist->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $realmlist_delete->ShowPageHeader(); ?>
<?php
$realmlist_delete->ShowMessage();
?>
<form name="frealmlistdelete" id="frealmlistdelete" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post">
<br>
<input type="hidden" name="t" value="realmlist">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($realmlist_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_realmlistdelete" class="ewTable ewTableSeparate">
<?php echo $realmlist->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
		<td><span id="elh_realmlist_id" class="realmlist_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->id->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_name" class="realmlist_name"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->name->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_address" class="realmlist_address"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->address->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_localAddress" class="realmlist_localAddress"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->localAddress->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_port" class="realmlist_port"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->port->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_icon" class="realmlist_icon"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->icon->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_timezone" class="realmlist_timezone"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->timezone->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_allowedSecurityLevel" class="realmlist_allowedSecurityLevel"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->allowedSecurityLevel->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_population" class="realmlist_population"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->population->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_realmlist_gamebuild" class="realmlist_gamebuild"><table class="ewTableHeaderBtn"><tr><td><?php echo $realmlist->gamebuild->FldCaption() ?></td></tr></table></span></td>
	</tr>
	</thead>
	<tbody>
<?php
$realmlist_delete->RecCnt = 0;
$i = 0;
while (!$realmlist_delete->Recordset->EOF) {
	$realmlist_delete->RecCnt++;
	$realmlist_delete->RowCnt++;

	// Set row properties
	$realmlist->ResetAttrs();
	$realmlist->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$realmlist_delete->LoadRowValues($realmlist_delete->Recordset);

	// Render row
	$realmlist_delete->RenderRow();
?>
	<tr<?php echo $realmlist->RowAttributes() ?>>
		<td<?php echo $realmlist->id->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_id" class="realmlist_id">
<span<?php echo $realmlist->id->ViewAttributes() ?>>
<?php echo $realmlist->id->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->name->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_name" class="realmlist_name">
<span<?php echo $realmlist->name->ViewAttributes() ?>>
<?php echo $realmlist->name->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->address->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_address" class="realmlist_address">
<span<?php echo $realmlist->address->ViewAttributes() ?>>
<?php echo $realmlist->address->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->localAddress->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_localAddress" class="realmlist_localAddress">
<span<?php echo $realmlist->localAddress->ViewAttributes() ?>>
<?php echo $realmlist->localAddress->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->port->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_port" class="realmlist_port">
<span<?php echo $realmlist->port->ViewAttributes() ?>>
<?php echo $realmlist->port->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->icon->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_icon" class="realmlist_icon">
<span<?php echo $realmlist->icon->ViewAttributes() ?>>
<?php echo $realmlist->icon->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->timezone->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_timezone" class="realmlist_timezone">
<span<?php echo $realmlist->timezone->ViewAttributes() ?>>
<?php echo $realmlist->timezone->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->allowedSecurityLevel->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_allowedSecurityLevel" class="realmlist_allowedSecurityLevel">
<span<?php echo $realmlist->allowedSecurityLevel->ViewAttributes() ?>>
<?php echo $realmlist->allowedSecurityLevel->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->population->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_population" class="realmlist_population">
<span<?php echo $realmlist->population->ViewAttributes() ?>>
<?php echo $realmlist->population->ListViewValue() ?></span>
</span></td>
		<td<?php echo $realmlist->gamebuild->CellAttributes() ?>><span id="el<?php echo $realmlist_delete->RowCnt ?>_realmlist_gamebuild" class="realmlist_gamebuild">
<span<?php echo $realmlist->gamebuild->ViewAttributes() ?>>
<?php echo $realmlist->gamebuild->ListViewValue() ?></span>
</span></td>
	</tr>
<?php
	$realmlist_delete->Recordset->MoveNext();
}
$realmlist_delete->Recordset->Close();
?>
</tbody>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="Action" value="<?php echo ew_BtnCaption($Language->Phrase("DeleteBtn")) ?>">
</form>
<script type="text/javascript">
frealmlistdelete.Init();
</script>
<?php
$realmlist_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$realmlist_delete->Page_Terminate();
?>
