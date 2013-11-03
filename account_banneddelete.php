<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg9.php" ?>
<?php include_once "ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "account_bannedinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$account_banned_delete = NULL; // Initialize page object first

class caccount_banned_delete extends caccount_banned {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'account_banned';

	// Page object name
	var $PageObjName = 'account_banned_delete';

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
			define("EW_PAGE_ID", 'delete', TRUE);

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
			$this->Page_Terminate("account_bannedlist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in account_banned class, account_bannedinfo.php

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
				if ($sThisKey <> "") $sThisKey .= $GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"];
				$sThisKey .= $row['bandate'];
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
if (!isset($account_banned_delete)) $account_banned_delete = new caccount_banned_delete();

// Page init
$account_banned_delete->Page_Init();

// Page main
$account_banned_delete->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var account_banned_delete = new ew_Page("account_banned_delete");
account_banned_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = account_banned_delete.PageID; // For backward compatibility

// Form object
var faccount_banneddelete = new ew_Form("faccount_banneddelete");

// Form_CustomValidate event
faccount_banneddelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
faccount_banneddelete.ValidateRequired = true;
<?php } else { ?>
faccount_banneddelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
faccount_banneddelete.Lists["x_id"] = {"LinkField":"x_id","Ajax":null,"AutoFill":false,"DisplayFields":["x_id","x_username","",""],"ParentFields":[],"FilterFields":[],"Options":[]};

// Form object for search
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($account_banned_delete->Recordset = $account_banned_delete->LoadRecordset())
	$account_banned_deleteTotalRecs = $account_banned_delete->Recordset->RecordCount(); // Get record count
if ($account_banned_deleteTotalRecs <= 0) { // No record found, exit
	if ($account_banned_delete->Recordset)
		$account_banned_delete->Recordset->Close();
	$account_banned_delete->Page_Terminate("account_bannedlist.php"); // Return to list
}
?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Delete") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $account_banned->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $account_banned->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $account_banned_delete->ShowPageHeader(); ?>
<?php
$account_banned_delete->ShowMessage();
?>
<form name="faccount_banneddelete" id="faccount_banneddelete" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post">
<br>
<input type="hidden" name="t" value="account_banned">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($account_banned_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_account_banneddelete" class="ewTable ewTableSeparate">
<?php echo $account_banned->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
		<td><span id="elh_account_banned_id" class="account_banned_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->id->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_banned_bandate" class="account_banned_bandate"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->bandate->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_banned_unbandate" class="account_banned_unbandate"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->unbandate->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_banned_bannedby" class="account_banned_bannedby"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->bannedby->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_banned_banreason" class="account_banned_banreason"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->banreason->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_banned_active" class="account_banned_active"><table class="ewTableHeaderBtn"><tr><td><?php echo $account_banned->active->FldCaption() ?></td></tr></table></span></td>
	</tr>
	</thead>
	<tbody>
<?php
$account_banned_delete->RecCnt = 0;
$i = 0;
while (!$account_banned_delete->Recordset->EOF) {
	$account_banned_delete->RecCnt++;
	$account_banned_delete->RowCnt++;

	// Set row properties
	$account_banned->ResetAttrs();
	$account_banned->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$account_banned_delete->LoadRowValues($account_banned_delete->Recordset);

	// Render row
	$account_banned_delete->RenderRow();
?>
	<tr<?php echo $account_banned->RowAttributes() ?>>
		<td<?php echo $account_banned->id->CellAttributes() ?>><span id="el<?php echo $account_banned_delete->RowCnt ?>_account_banned_id" class="account_banned_id">
<span<?php echo $account_banned->id->ViewAttributes() ?>>
<?php echo $account_banned->id->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account_banned->bandate->CellAttributes() ?>><span id="el<?php echo $account_banned_delete->RowCnt ?>_account_banned_bandate" class="account_banned_bandate">
<span<?php echo $account_banned->bandate->ViewAttributes() ?>>
<?php echo $account_banned->bandate->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account_banned->unbandate->CellAttributes() ?>><span id="el<?php echo $account_banned_delete->RowCnt ?>_account_banned_unbandate" class="account_banned_unbandate">
<span<?php echo $account_banned->unbandate->ViewAttributes() ?>>
<?php echo $account_banned->unbandate->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account_banned->bannedby->CellAttributes() ?>><span id="el<?php echo $account_banned_delete->RowCnt ?>_account_banned_bannedby" class="account_banned_bannedby">
<span<?php echo $account_banned->bannedby->ViewAttributes() ?>>
<?php echo $account_banned->bannedby->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account_banned->banreason->CellAttributes() ?>><span id="el<?php echo $account_banned_delete->RowCnt ?>_account_banned_banreason" class="account_banned_banreason">
<span<?php echo $account_banned->banreason->ViewAttributes() ?>>
<?php echo $account_banned->banreason->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account_banned->active->CellAttributes() ?>><span id="el<?php echo $account_banned_delete->RowCnt ?>_account_banned_active" class="account_banned_active">
<span<?php echo $account_banned->active->ViewAttributes() ?>>
<?php echo $account_banned->active->ListViewValue() ?></span>
</span></td>
	</tr>
<?php
	$account_banned_delete->Recordset->MoveNext();
}
$account_banned_delete->Recordset->Close();
?>
</tbody>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="Action" value="<?php echo ew_BtnCaption($Language->Phrase("DeleteBtn")) ?>">
</form>
<script type="text/javascript">
faccount_banneddelete.Init();
</script>
<?php
$account_banned_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$account_banned_delete->Page_Terminate();
?>
