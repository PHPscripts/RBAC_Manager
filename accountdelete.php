<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "includes/framework/ewcfg9.php" ?>
<?php include_once "includes/framework/ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php include_once "accountinfo.php" ?>
<?php include_once "userfn9.php" ?>
<?php

//
// Page class
//

$account_delete = NULL; // Initialize page object first

class caccount_delete extends caccount {

	// Page ID
	var $PageID = 'delete';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'account';

	// Page object name
	var $PageObjName = 'account_delete';

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

		// Table object (account)
		if (!isset($GLOBALS["account"])) {
			$GLOBALS["account"] = &$this;
			$GLOBALS["Table"] = &$GLOBALS["account"];
		}

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'delete', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'account', TRUE);

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
			$this->Page_Terminate("accountlist.php"); // Prevent SQL injection, return to list

		// Set up filter (SQL WHHERE clause) and get return SQL
		// SQL constructor in account class, accountinfo.php

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

			// online
			$this->online->LinkCustomAttributes = "";
			$this->online->HrefValue = "";
			$this->online->TooltipValue = "";

			// expansion
			$this->expansion->LinkCustomAttributes = "";
			$this->expansion->HrefValue = "";
			$this->expansion->TooltipValue = "";
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
if (!isset($account_delete)) $account_delete = new caccount_delete();

// Page init
$account_delete->Page_Init();

// Page main
$account_delete->Page_Main();
?>
<?php include_once "header.php" ?>
<script type="text/javascript">

// Page object
var account_delete = new ew_Page("account_delete");
account_delete.PageID = "delete"; // Page ID
var EW_PAGE_ID = account_delete.PageID; // For backward compatibility

// Form object
var faccountdelete = new ew_Form("faccountdelete");

// Form_CustomValidate event
faccountdelete.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
faccountdelete.ValidateRequired = true;
<?php } else { ?>
faccountdelete.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php

// Load records for display
if ($account_delete->Recordset = $account_delete->LoadRecordset())
	$account_deleteTotalRecs = $account_delete->Recordset->RecordCount(); // Get record count
if ($account_deleteTotalRecs <= 0) { // No record found, exit
	if ($account_delete->Recordset)
		$account_delete->Recordset->Close();
	$account_delete->Page_Terminate("accountlist.php"); // Return to list
}
?>
<p><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("Delete") ?>&nbsp;<?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $account->TableCaption() ?></span></p>
<p class="phpmaker"><a href="<?php echo $account->getReturnUrl() ?>" id="a_GoBack" class="ewLink"><?php echo $Language->Phrase("GoBack") ?></a></p>
<?php $account_delete->ShowPageHeader(); ?>
<?php
$account_delete->ShowMessage();
?>
<form name="faccountdelete" id="faccountdelete" class="ewForm" action="<?php echo ew_CurrentPage() ?>" method="post">
<br>
<input type="hidden" name="t" value="account">
<input type="hidden" name="a_delete" id="a_delete" value="D">
<?php foreach ($account_delete->RecKeys as $key) { ?>
<?php $keyvalue = is_array($key) ? implode($EW_COMPOSITE_KEY_SEPARATOR, $key) : $key; ?>
<input type="hidden" name="key_m[]" value="<?php echo ew_HtmlEncode($keyvalue) ?>">
<?php } ?>
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<div class="ewGridMiddlePanel">
<table id="tbl_accountdelete" class="ewTable ewTableSeparate">
<?php echo $account->TableCustomInnerHtml ?>
	<thead>
	<tr class="ewTableHeader">
		<td><span id="elh_account_id" class="account_id"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->id->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_username" class="account_username"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->username->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account__email" class="account__email"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->_email->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_reg_mail" class="account_reg_mail"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->reg_mail->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_joindate" class="account_joindate"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->joindate->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_last_ip" class="account_last_ip"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->last_ip->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_online" class="account_online"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->online->FldCaption() ?></td></tr></table></span></td>
		<td><span id="elh_account_expansion" class="account_expansion"><table class="ewTableHeaderBtn"><tr><td><?php echo $account->expansion->FldCaption() ?></td></tr></table></span></td>
	</tr>
	</thead>
	<tbody>
<?php
$account_delete->RecCnt = 0;
$i = 0;
while (!$account_delete->Recordset->EOF) {
	$account_delete->RecCnt++;
	$account_delete->RowCnt++;

	// Set row properties
	$account->ResetAttrs();
	$account->RowType = EW_ROWTYPE_VIEW; // View

	// Get the field contents
	$account_delete->LoadRowValues($account_delete->Recordset);

	// Render row
	$account_delete->RenderRow();
?>
	<tr<?php echo $account->RowAttributes() ?>>
		<td<?php echo $account->id->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_id" class="account_id">
<span<?php echo $account->id->ViewAttributes() ?>>
<?php echo $account->id->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->username->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_username" class="account_username">
<span<?php echo $account->username->ViewAttributes() ?>>
<?php echo $account->username->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->_email->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account__email" class="account__email">
<span<?php echo $account->_email->ViewAttributes() ?>>
<?php echo $account->_email->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->reg_mail->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_reg_mail" class="account_reg_mail">
<span<?php echo $account->reg_mail->ViewAttributes() ?>>
<?php echo $account->reg_mail->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->joindate->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_joindate" class="account_joindate">
<span<?php echo $account->joindate->ViewAttributes() ?>>
<?php echo $account->joindate->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->last_ip->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_last_ip" class="account_last_ip">
<span<?php echo $account->last_ip->ViewAttributes() ?>>
<?php echo $account->last_ip->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->online->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_online" class="account_online">
<span<?php echo $account->online->ViewAttributes() ?>>
<?php echo $account->online->ListViewValue() ?></span>
</span></td>
		<td<?php echo $account->expansion->CellAttributes() ?>><span id="el<?php echo $account_delete->RowCnt ?>_account_expansion" class="account_expansion">
<span<?php echo $account->expansion->ViewAttributes() ?>>
<?php echo $account->expansion->ListViewValue() ?></span>
</span></td>
	</tr>
<?php
	$account_delete->Recordset->MoveNext();
}
$account_delete->Recordset->Close();
?>
</tbody>
</table>
</div>
</td></tr></table>
<br>
<input type="submit" name="Action" value="<?php echo ew_BtnCaption($Language->Phrase("DeleteBtn")) ?>">
</form>
<script type="text/javascript">
faccountdelete.Init();
</script>
<?php
$account_delete->ShowPageFooter();
if (EW_DEBUG_ENABLED)
	echo ew_DebugMsg();
?>
<script type="text/javascript">

// Write your table-specific startup script here
// document.write("page loaded");

</script>
<?php include_once "footer.php" ?>
<?php
$account_delete->Page_Terminate();
?>
