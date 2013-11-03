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

$rbac_account_permissions_delete = NULL; // Initialize page object first

class crbac_account_permissions_delete extends crbac_account_permissions {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'rbac_account_permissions';

	// Page object name
	var $PageObjName = 'rbac_account_permissions_delete';

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
			define("EW_PAGE_ID", 'delete', TRUE);

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
			$this->Page_Terminate("rbac_account_permissionslist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in rbac_account_permissions class, rbac_account_permissionsinfo.php

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
				$sThisKey .= $row['accountId'];
				if ($sThisKey <> "") $sThisKey .= $GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"];
				$sThisKey .= $row['permissionId'];
				if ($sThisKey <> "") $sThisKey .= $GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"];
				$sThisKey .= $row['realmId'];
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
if (!isset($rbac_account_permissions_delete)) $rbac_account_permissions_delete = new crbac_account_permissions_delete();

// Page init
$rbac_account_permissions_delete->Page_Init();

// Page main
$rbac_account_permissions_delete->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var rbac_account_permissions_delete = new ew_Page("rbac_account_permissions_delete");
rbac_account_permissions_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = rbac_account_permissions_delete.PageID; // For backward compatibility

// Form object
var frbac_account_permissionsdelete = new ew_Form("frbac_account_permissionsdelete");

// Form_CustomValidate event
frbac_account_permissionsdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frbac_account_permissionsdelete.ValidateRequired = true;
<?php } else { ?>
frbac_account_permissionsdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
frbac_account_permissionsdelete.Lists["x_accountId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_username","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
frbac_account_permissionsdelete.Lists["x_permissionId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_name","",""],"ParentFields":[],"FilterFields":[],"Options":[]};
frbac_account_permissionsdelete.Lists["x_realmId"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_name","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($rbac_account_permissions_delete->Recordset = $rbac_account_permissions_delete->LoadRecordset())
	$rbac_account_permissions_deleteTotalRecs = $rbac_account_permissions_delete->Recordset->RecordCount(); // Get record count
if ($rbac_account_permissions_deleteTotalRecs <= 0) { // No record found, exit
	if ($rbac_account_permissions_delete->Recordset)
		$rbac_account_permissions_delete->Recordset->Close();
	$rbac_account_permissions_delete->Page_Terminate("rbac_account_permissionslist.php"); // Return to list
}
?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Delete") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $rbac_account_permissions->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $rbac_account_permissions->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $rbac_account_permissions_delete->ShowPageHeader(); ?>
<?php
$rbac_account_permissions_delete->ShowMessage();
?>
<form name="frbac_account_permissionsdelete" id="frbac_account_permissionsdelete" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post">
<br>
<input type="hidden" name="t" value="rbac_account_permissions">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($rbac_account_permissions_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_rbac_account_permissionsdelete" class="ewTable ewTableSeparate">
<?php echo $rbac_account_permissions->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
		<td><span id="elh_rbac_account_permissions_accountId" class="rbac_account_permissions_accountId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->accountId->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_rbac_account_permissions_permissionId" class="rbac_account_permissions_permissionId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->permissionId->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_rbac_account_permissions_granted" class="rbac_account_permissions_granted"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->granted->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_rbac_account_permissions_realmId" class="rbac_account_permissions_realmId"><table class="ewTableHeaderBtn"><tr><td><?php echo $rbac_account_permissions->realmId->FldCaption() ?></td></tr></table></span></td>
	</tr>
	</thead>
	<tbody>
<?php
$rbac_account_permissions_delete->RecCnt = 0;
$i = 0;
while (!$rbac_account_permissions_delete->Recordset->EOF) {
	$rbac_account_permissions_delete->RecCnt++;
	$rbac_account_permissions_delete->RowCnt++;

	// Set row properties
	$rbac_account_permissions->ResetAttrs();
	$rbac_account_permissions->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$rbac_account_permissions_delete->LoadRowValues($rbac_account_permissions_delete->Recordset);

	// Render row
	$rbac_account_permissions_delete->RenderRow();
?>
	<tr<?php echo $rbac_account_permissions->RowAttributes() ?>>
		<td<?php echo $rbac_account_permissions->accountId->CellAttributes() ?>><span id="el<?php echo $rbac_account_permissions_delete->RowCnt ?>_rbac_account_permissions_accountId" class="rbac_account_permissions_accountId">
<span<?php echo $rbac_account_permissions->accountId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->accountId->ListViewValue() ?></span>
</span></td>
		<td<?php echo $rbac_account_permissions->permissionId->CellAttributes() ?>><span id="el<?php echo $rbac_account_permissions_delete->RowCnt ?>_rbac_account_permissions_permissionId" class="rbac_account_permissions_permissionId">
<span<?php echo $rbac_account_permissions->permissionId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->permissionId->ListViewValue() ?></span>
</span></td>
		<td<?php echo $rbac_account_permissions->granted->CellAttributes() ?>><span id="el<?php echo $rbac_account_permissions_delete->RowCnt ?>_rbac_account_permissions_granted" class="rbac_account_permissions_granted">
<span<?php echo $rbac_account_permissions->granted->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->granted->ListViewValue() ?></span>
</span></td>
		<td<?php echo $rbac_account_permissions->realmId->CellAttributes() ?>><span id="el<?php echo $rbac_account_permissions_delete->RowCnt ?>_rbac_account_permissions_realmId" class="rbac_account_permissions_realmId">
<span<?php echo $rbac_account_permissions->realmId->ViewAttributes() ?>>
<?php echo $rbac_account_permissions->realmId->ListViewValue() ?></span>
</span></td>
	</tr>
<?php
	$rbac_account_permissions_delete->Recordset->MoveNext();
}
$rbac_account_permissions_delete->Recordset->Close();
?>
</tbody>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="Action" value="<?php echo ew_BtnCaption($Language->Phrase("DeleteBtn")) ?>">
</form>
<script type="text/javascript">
frbac_account_permissionsdelete.Init();
</script>
<?php
$rbac_account_permissions_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$rbac_account_permissions_delete->Page_Terminate();
?>
