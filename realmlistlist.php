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

$realmlist_list = NULL; // Initialize page object first

class crealmlist_list extends crealmlist {

	// Page ID
	var $PageID = 'list';

	// Project ID
	var $ProjectID = "{94C0E450-F9A8-47EE-A905-551040DB9277}";

	// Table name
	var $TableName = 'realmlist';

	// Page object name
	var $PageObjName = 'realmlist_list';

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

		// Initialize URLs
		$this->ExportPrintUrl = $this->PageUrl() . "export=print";
		$this->ExportExcelUrl = $this->PageUrl() . "export=excel";
		$this->ExportWordUrl = $this->PageUrl() . "export=word";
		$this->ExportHtmlUrl = $this->PageUrl() . "export=html";
		$this->ExportXmlUrl = $this->PageUrl() . "export=xml";
		$this->ExportCsvUrl = $this->PageUrl() . "export=csv";
		$this->ExportPdfUrl = $this->PageUrl() . "export=pdf";
		$this->AddUrl = "realmlistadd.php";
		$this->InlineAddUrl = $this->PageUrl() . "a=add";
		$this->GridAddUrl = $this->PageUrl() . "a=gridadd";
		$this->GridEditUrl = $this->PageUrl() . "a=gridedit";
		$this->MultiDeleteUrl = "realmlistdelete.php";
		$this->MultiUpdateUrl = "realmlistupdate.php";

		// Page ID
		if (!defined("EW_PAGE_ID"))
			define("EW_PAGE_ID", 'list', TRUE);

		// Table name (for backward compatibility)
		if (!defined("EW_TABLE_NAME"))
			define("EW_TABLE_NAME", 'realmlist', TRUE);

		// Start timer
		if (!isset($GLOBALS["gTimer"])) $GLOBALS["gTimer"] = new cTimer();

		// Open connection
		if (!isset($conn)) $conn = ew_Connect();

		// List options
		$this->ListOptions = new cListOptions();
		$this->ListOptions->TableVar = $this->TableVar;

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

		// Get grid add count
		$gridaddcnt = @$_GET[EW_TABLE_GRID_ADD_ROW_COUNT];
		if (is_numeric($gridaddcnt) && $gridaddcnt > 0)
			$this->GridAddRowCount = $gridaddcnt;

		// Set up list options
		$this->SetupListOptions();

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

	// Class variables
	var $ListOptions; // List options
	var $ExportOptions; // Export options
	var $DisplayRecs = 30;
	var $StartRec;
	var $StopRec;
	var $TotalRecs = 0;
	var $RecRange = 10;
	var $Pager;
	var $SearchWhere = ""; // Search WHERE clause
	var $RecCnt = 0; // Record count
	var $EditRowCnt;
	var $StartRowCnt = 1;
	var $RowCnt = 0;
	var $Attrs = array(); // Row attributes and cell attributes
	var $RowIndex = 0; // Row index
	var $KeyCount = 0; // Key count
	var $RowAction = ""; // Row action
	var $RowOldKey = ""; // Row old key (for copy)
	var $RecPerRow = 0;
	var $ColCnt = 0;
	var $DbMasterFilter = ""; // Master filter
	var $DbDetailFilter = ""; // Detail filter
	var $MasterRecordExists;	
	var $MultiSelectKey;
	var $Command;
	var $Recordset;
	var $OldRecordset;

	//
	// Page main
	//
	function Page_Main() {
		global $objForm, $Language, $gsFormError, $gsSearchError, $Security;

		// Search filters
		$sSrchAdvanced = ""; // Advanced search filter
		$sSrchBasic = ""; // Basic search filter
		$sFilter = "";

		// Get command
		$this->Command = strtolower(@$_GET["cmd"]);
		if ($this->IsPageRequest()) { // Validate request

			// Set up records per page
			$this->SetUpDisplayRecs();

			// Handle reset command
			$this->ResetCmd();

			// Hide all options
			if ($this->Export <> "" ||
				$this->CurrentAction == "gridadd" ||
				$this->CurrentAction == "gridedit") {
				$this->ListOptions->HideAllOptions();
				$this->ExportOptions->HideAllOptions();
			}

			// Get basic search values
			$this->LoadBasicSearchValues();

			// Restore search parms from Session if not searching / reset
			if ($this->Command <> "search" && $this->Command <> "reset" && $this->Command <> "resetall")
				$this->RestoreSearchParms();

			// Call Recordset SearchValidated event
			$this->Recordset_SearchValidated();

			// Set up sorting order
			$this->SetUpSortOrder();

			// Get basic search criteria
			if ($gsSearchError == "")
				$sSrchBasic = $this->BasicSearchWhere();
		}

		// Restore display records
		if ($this->getRecordsPerPage() <> "") {
			$this->DisplayRecs = $this->getRecordsPerPage(); // Restore from Session
		} else {
			$this->DisplayRecs = 30; // Load default
		}

		// Load Sorting Order
		$this->LoadSortOrder();

		// Load search default if no existing search criteria
		if (!$this->CheckSearchParms()) {

			// Load basic search from default
			$this->BasicSearch->LoadDefault();
			if ($this->BasicSearch->Keyword != "")
				$sSrchBasic = $this->BasicSearchWhere();
		}

		// Build search criteria
		ew_AddFilter($this->SearchWhere, $sSrchAdvanced);
		ew_AddFilter($this->SearchWhere, $sSrchBasic);

		// Call Recordset_Searching event
		$this->Recordset_Searching($this->SearchWhere);

		// Save search criteria
		if ($this->Command == "search") {
			$this->setSearchWhere($this->SearchWhere); // Save to Session
			$this->StartRec = 1; // Reset start record counter
			$this->setStartRecordNumber($this->StartRec);
		} else {
			$this->SearchWhere = $this->getSearchWhere();
		}

		// Build filter
		$sFilter = "";
		ew_AddFilter($sFilter, $this->DbDetailFilter);
		ew_AddFilter($sFilter, $this->SearchWhere);

		// Set up filter in session
		$this->setSessionWhere($sFilter);
		$this->CurrentFilter = "";

		// Export data only
		if (in_array($this->Export, array("html","word","excel","xml","csv","email","pdf"))) {
			$this->ExportData();
			if ($this->Export == "email")
				$this->Page_Terminate($this->ExportReturnUrl());
			else
				$this->Page_Terminate(); // Terminate response
			exit();
		}
	}

	// Set up number of records displayed per page
	function SetUpDisplayRecs() {
		$sWrk = @$_GET[EW_TABLE_REC_PER_PAGE];
		if ($sWrk <> "") {
			if (is_numeric($sWrk)) {
				$this->DisplayRecs = intval($sWrk);
			} else {
				if (strtolower($sWrk) == "all") { // Display all records
					$this->DisplayRecs = -1;
				} else {
					$this->DisplayRecs = 30; // Non-numeric, load default
				}
			}
			$this->setRecordsPerPage($this->DisplayRecs); // Save to Session

			// Reset start position
			$this->StartRec = 1;
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Build filter for all keys
	function BuildKeyFilter() {
		global $objForm;
		$sWrkFilter = "";

		// Update row index and get row key
		$rowindex = 1;
		$objForm->Index = $rowindex;
		$sThisKey = strval($objForm->GetValue("k_key"));
		while ($sThisKey <> "") {
			if ($this->SetupKeyValues($sThisKey)) {
				$sFilter = $this->KeyFilter();
				if ($sWrkFilter <> "") $sWrkFilter .= " OR ";
				$sWrkFilter .= $sFilter;
			} else {
				$sWrkFilter = "0=1";
				break;
			}

			// Update row index and get row key
			$rowindex++; // next row
			$objForm->Index = $rowindex;
			$sThisKey = strval($objForm->GetValue("k_key"));
		}
		return $sWrkFilter;
	}

	// Set up key values
	function SetupKeyValues($key) {
		$arrKeyFlds = explode($GLOBALS["EW_COMPOSITE_KEY_SEPARATOR"], $key);
		if (count($arrKeyFlds) >= 1) {
			$this->id->setFormValue($arrKeyFlds[0]);
			if (!is_numeric($this->id->FormValue))
				return FALSE;
		}
		return TRUE;
	}

	// Return basic search SQL
	function BasicSearchSQL($Keyword) {
		$sKeyword = ew_AdjustSql($Keyword);
		$sWhere = "";
		$this->BuildBasicSearchSQL($sWhere, $this->name, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->address, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->localAddress, $Keyword);
		$this->BuildBasicSearchSQL($sWhere, $this->localSubnetMask, $Keyword);
		return $sWhere;
	}

	// Build basic search SQL
	function BuildBasicSearchSql(&$Where, &$Fld, $Keyword) {
		if ($Keyword == EW_NULL_VALUE) {
			$sWrk = $Fld->FldExpression . " IS NULL";
		} elseif ($Keyword == EW_NOT_NULL_VALUE) {
			$sWrk = $Fld->FldExpression . " IS NOT NULL";
		} else {
			$sFldExpression = ($Fld->FldVirtualExpression <> $Fld->FldExpression) ? $Fld->FldVirtualExpression : $Fld->FldBasicSearchExpression;
			$sWrk = $sFldExpression . ew_Like(ew_QuotedValue("%" . $Keyword . "%", EW_DATATYPE_STRING));
		}
		if ($Where <> "") $Where .= " OR ";
		$Where .= $sWrk;
	}

	// Return basic search WHERE clause based on search keyword and type
	function BasicSearchWhere() {
		global $Security;
		$sSearchStr = "";
		$sSearchKeyword = $this->BasicSearch->Keyword;
		$sSearchType = $this->BasicSearch->Type;
		if ($sSearchKeyword <> "") {
			$sSearch = trim($sSearchKeyword);
			if ($sSearchType <> "=") {
				while (strpos($sSearch, "  ") !== FALSE)
					$sSearch = str_replace("  ", " ", $sSearch);
				$arKeyword = explode(" ", trim($sSearch));
				foreach ($arKeyword as $sKeyword) {
					if ($sSearchStr <> "") $sSearchStr .= " " . $sSearchType . " ";
					$sSearchStr .= "(" . $this->BasicSearchSQL($sKeyword) . ")";
				}
			} else {
				$sSearchStr = $this->BasicSearchSQL($sSearch);
			}
			$this->Command = "search";
		}
		if ($this->Command == "search") {
			$this->BasicSearch->setKeyword($sSearchKeyword);
			$this->BasicSearch->setType($sSearchType);
		}
		return $sSearchStr;
	}

	// Check if search parm exists
	function CheckSearchParms() {

		// Check basic search
		if ($this->BasicSearch->IssetSession())
			return TRUE;
		return FALSE;
	}

	// Clear all search parameters
	function ResetSearchParms() {

		// Clear search WHERE clause
		$this->SearchWhere = "";
		$this->setSearchWhere($this->SearchWhere);

		// Clear basic search parameters
		$this->ResetBasicSearchParms();
	}

	// Load advanced search default values
	function LoadAdvancedSearchDefault() {
		return FALSE;
	}

	// Clear all basic search parameters
	function ResetBasicSearchParms() {
		$this->BasicSearch->UnsetSession();
	}

	// Restore all search parameters
	function RestoreSearchParms() {

		// Restore basic search values
		$this->BasicSearch->Load();
	}

	// Set up sort parameters
	function SetUpSortOrder() {

		// Check for "order" parameter
		if (@$_GET["order"] <> "") {
			$this->CurrentOrder = ew_StripSlashes(@$_GET["order"]);
			$this->CurrentOrderType = @$_GET["ordertype"];
			$this->UpdateSort($this->id); // id
			$this->UpdateSort($this->name); // name
			$this->UpdateSort($this->address); // address
			$this->UpdateSort($this->localAddress); // localAddress
			$this->UpdateSort($this->port); // port
			$this->UpdateSort($this->icon); // icon
			$this->UpdateSort($this->timezone); // timezone
			$this->UpdateSort($this->allowedSecurityLevel); // allowedSecurityLevel
			$this->UpdateSort($this->population); // population
			$this->UpdateSort($this->gamebuild); // gamebuild
			$this->setStartRecordNumber(1); // Reset start position
		}
	}

	// Load sort order parameters
	function LoadSortOrder() {
		$sOrderBy = $this->getSessionOrderBy(); // Get ORDER BY from Session
		if ($sOrderBy == "") {
			if ($this->SqlOrderBy() <> "") {
				$sOrderBy = $this->SqlOrderBy();
				$this->setSessionOrderBy($sOrderBy);
			}
		}
	}

	// Reset command
	// cmd=reset (Reset search parameters)
	// cmd=resetall (Reset search and master/detail parameters)
	// cmd=resetsort (Reset sort parameters)
	function ResetCmd() {

		// Check if reset command
		if (substr($this->Command,0,5) == "reset") {

			// Reset search criteria
			if ($this->Command == "reset" || $this->Command == "resetall")
				$this->ResetSearchParms();

			// Reset sorting order
			if ($this->Command == "resetsort") {
				$sOrderBy = "";
				$this->setSessionOrderBy($sOrderBy);
				$this->id->setSort("");
				$this->name->setSort("");
				$this->address->setSort("");
				$this->localAddress->setSort("");
				$this->port->setSort("");
				$this->icon->setSort("");
				$this->timezone->setSort("");
				$this->allowedSecurityLevel->setSort("");
				$this->population->setSort("");
				$this->gamebuild->setSort("");
			}

			// Reset start position
			$this->StartRec = 1;
			$this->setStartRecordNumber($this->StartRec);
		}
	}

	// Set up list options
	function SetupListOptions() {
		global $Security, $Language;

		// "view"
		$item = &$this->ListOptions->Add("view");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = TRUE;

		// "edit"
		$item = &$this->ListOptions->Add("edit");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = TRUE;

		// "copy"
		$item = &$this->ListOptions->Add("copy");
		$item->CssStyle = "white-space: nowrap;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = TRUE;

		// "checkbox"
		$item = &$this->ListOptions->Add("checkbox");
		$item->CssStyle = "white-space: nowrap; text-align: center; vertical-align: middle; margin: 0px;";
		$item->Visible = $Security->IsLoggedIn();
		$item->OnLeft = TRUE;
		$item->Header = "<input type=\"checkbox\" name=\"key\" id=\"key\" class=\"phpmaker\" onclick=\"ew_SelectAllKey(this);\">";
		$item->MoveTo(0);

		// Call ListOptions_Load event
		$this->ListOptions_Load();
	}

	// Render list options
	function RenderListOptions() {
		global $Security, $Language, $objForm;
		$this->ListOptions->LoadDefault();

		// "view"
		$oListOpt = &$this->ListOptions->Items["view"];
		if ($Security->IsLoggedIn())
			$oListOpt->Body = "<a class=\"ewRowLink\" href=\"" . $this->ViewUrl . "\">" . $Language->Phrase("ViewLink") . "</a>";

		// "edit"
		$oListOpt = &$this->ListOptions->Items["edit"];
		if ($Security->IsLoggedIn()) {
			$oListOpt->Body = "<a class=\"ewRowLink\" href=\"" . $this->EditUrl . "\">" . $Language->Phrase("EditLink") . "</a>";
		}

		// "copy"
		$oListOpt = &$this->ListOptions->Items["copy"];
		if ($Security->IsLoggedIn()) {
			$oListOpt->Body = "<a class=\"ewRowLink\" href=\"" . $this->CopyUrl . "\">" . $Language->Phrase("CopyLink") . "</a>";
		}

		// "checkbox"
		$oListOpt = &$this->ListOptions->Items["checkbox"];
		if ($Security->IsLoggedIn())
			$oListOpt->Body = "<input type=\"checkbox\" name=\"key_m[]\" value=\"" . ew_HtmlEncode($this->id->CurrentValue) . "\" class=\"phpmaker\" onclick='ew_ClickMultiCheckbox(event, this);'>";
		$this->RenderListOptionsExt();

		// Call ListOptions_Rendered event
		$this->ListOptions_Rendered();
	}

	function RenderListOptionsExt() {
		global $Security, $Language;
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

	// Load basic search values
	function LoadBasicSearchValues() {
		$this->BasicSearch->Keyword = @$_GET[EW_TABLE_BASIC_SEARCH];
		if ($this->BasicSearch->Keyword <> "") $this->Command = "search";
		$this->BasicSearch->Type = @$_GET[EW_TABLE_BASIC_SEARCH_TYPE];
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

	// Load old record
	function LoadOldRecord() {

		// Load key values from Session
		$bValidKey = TRUE;
		if (strval($this->getKey("id")) <> "")
			$this->id->CurrentValue = $this->getKey("id"); // id
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
		$this->ViewUrl = $this->GetViewUrl();
		$this->EditUrl = $this->GetEditUrl();
		$this->InlineEditUrl = $this->GetInlineEditUrl();
		$this->CopyUrl = $this->GetCopyUrl();
		$this->InlineCopyUrl = $this->GetInlineCopyUrl();
		$this->DeleteUrl = $this->GetDeleteUrl();

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
		$item->Visible = FALSE;

		// Export to Email
		$item = &$this->ExportOptions->Add("email");
		$item->Body = "<a id=\"emf_realmlist\" href=\"javascript:void(0);\" onclick=\"ew_EmailDialogShow({lnk:'emf_realmlist',hdr:ewLanguage.Phrase('ExportToEmail'),f:document.frealmlistlist,sel:false});\">" . $Language->Phrase("ExportToEmail") . "</a>";
		$item->Visible = FALSE;

		// Hide options for export/action
		if ($this->Export <> "" || $this->CurrentAction <> "")
			$this->ExportOptions->HideAllOptions();
	}

	// Export data in HTML/CSV/Word/Excel/XML/Email/PDF format
	function ExportData() {
		$utf8 = (strtolower(EW_CHARSET) == "utf-8");
		$bSelectLimit = EW_SELECT_LIMIT;

		// Load recordset
		if ($bSelectLimit) {
			$this->TotalRecs = $this->SelectRecordCount();
		} else {
			if ($rs = $this->LoadRecordset())
				$this->TotalRecs = $rs->RecordCount();
		}
		$this->StartRec = 1;

		// Export all
		if ($this->ExportAll) {
			set_time_limit(EW_EXPORT_ALL_TIME_LIMIT);
			$this->DisplayRecs = $this->TotalRecs;
			$this->StopRec = $this->TotalRecs;
		} else { // Export one page only
			$this->SetUpStartRec(); // Set up start record position

			// Set the last record to display
			if ($this->DisplayRecs <= 0) {
				$this->StopRec = $this->TotalRecs;
			} else {
				$this->StopRec = $this->StartRec + $this->DisplayRecs - 1;
			}
		}
		if ($bSelectLimit)
			$rs = $this->LoadRecordset($this->StartRec-1, $this->DisplayRecs <= 0 ? $this->TotalRecs : $this->DisplayRecs);
		if (!$rs) {
			header("Content-Type:"); // Remove header
			header("Content-Disposition:");
			$this->ShowMessage();
			return;
		}
		$ExportDoc = ew_ExportDocument($this, "h");
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
		$this->ExportDocument($ExportDoc, $rs, $StartRec, $StopRec, "");
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

	// Form Custom Validate event
	function Form_CustomValidate(&$CustomError) {

		// Return error message in CustomError
		return TRUE;
	}

	// ListOptions Load event
	function ListOptions_Load() {

		// Example:
		//$opt = &$this->ListOptions->Add("new");
		//$opt->Header = "xxx";
		//$opt->OnLeft = TRUE; // Link on left
		//$opt->MoveTo(0); // Move to first column

	}

	// ListOptions Rendered event
	function ListOptions_Rendered() {

		// Example: 
		//$this->ListOptions->Items["new"]->Body = "xxx";

	}
}
?>
<?php ew_Header(FALSE) ?>
<?php

// Create page object
if (!isset($realmlist_list)) $realmlist_list = new crealmlist_list();

// Page init
$realmlist_list->Page_Init();

// Page main
$realmlist_list->Page_Main();
?>
<?php include_once "header.php" ?>
<?php if ($realmlist->Export == "") { ?>
<script type="text/javascript">

// Page object
var realmlist_list = new ew_Page("realmlist_list");
realmlist_list.PageID = "list"; // Page ID
var EW_PAGE_ID = realmlist_list.PageID; // For backward compatibility

// Form object
var frealmlistlist = new ew_Form("frealmlistlist");

// Form_CustomValidate event
frealmlistlist.Form_CustomValidate = 
 function(fobj) { // DO NOT CHANGE THIS LINE!

 	// Your custom validation code here, return false if invalid. 
 	return true;
 }

// Use JavaScript validation or not
<?php if (EW_CLIENT_VALIDATE) { ?>
frealmlistlist.ValidateRequired = true;
<?php } else { ?>
frealmlistlist.ValidateRequired = false; 
<?php } ?>

// Dynamic selection lists
// Form object for search

var frealmlistlistsrch = new ew_Form("frealmlistlistsrch");
</script>
<script type="text/javascript">

// Write your client script here, no need to add script tags.
</script>
<?php } ?>
<?php
	$bSelectLimit = EW_SELECT_LIMIT;
	if ($bSelectLimit) {
		$realmlist_list->TotalRecs = $realmlist->SelectRecordCount();
	} else {
		if ($realmlist_list->Recordset = $realmlist_list->LoadRecordset())
			$realmlist_list->TotalRecs = $realmlist_list->Recordset->RecordCount();
	}
	$realmlist_list->StartRec = 1;
	if ($realmlist_list->DisplayRecs <= 0 || ($realmlist->Export <> "" && $realmlist->ExportAll)) // Display all records
		$realmlist_list->DisplayRecs = $realmlist_list->TotalRecs;
	if (!($realmlist->Export <> "" && $realmlist->ExportAll))
		$realmlist_list->SetUpStartRec(); // Set up start record position
	if ($bSelectLimit)
		$realmlist_list->Recordset = $realmlist_list->LoadRecordset($realmlist_list->StartRec-1, $realmlist_list->DisplayRecs);
?>
<p style="white-space: nowrap;"><span id="ewPageCaption" class="ewTitle ewTableTitle"><?php echo $Language->Phrase("TblTypeTABLE") ?><?php echo $realmlist->TableCaption() ?>&nbsp;&nbsp;</span>
<?php $realmlist_list->ExportOptions->Render("body"); ?>
</p>
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($realmlist->Export == "" && $realmlist->CurrentAction == "") { ?>
<form name="frealmlistlistsrch" id="frealmlistlistsrch" class="ewForm" action="<?php echo ew_CurrentPage() ?>">
<a href="javascript:frealmlistlistsrch.ToggleSearchPanel();" style="text-decoration: none;"><img id="frealmlistlistsrch_SearchImage" src="images/collapse.gif" alt="" width="9" height="9" style="border: 0;"></a><span class="phpmaker">&nbsp;<?php echo $Language->Phrase("Search") ?></span><br>
<div id="frealmlistlistsrch_SearchPanel">
<input type="hidden" name="cmd" value="search">
<input type="hidden" name="t" value="realmlist">
<div class="ewBasicSearch">
<div id="xsr_1" class="ewRow">
	<input type="text" name="<?php echo EW_TABLE_BASIC_SEARCH ?>" id="<?php echo EW_TABLE_BASIC_SEARCH ?>" size="20" value="<?php echo ew_HtmlEncode($realmlist_list->BasicSearch->getKeyword()) ?>">
	<input type="submit" name="btnsubmit" id="btnsubmit" value="<?php echo ew_BtnCaption($Language->Phrase("QuickSearchBtn")) ?>">&nbsp;
	<a href="<?php echo $realmlist_list->PageUrl() ?>cmd=reset" id="a_ShowAll" class="ewLink"><?php echo $Language->Phrase("ShowAll") ?></a>&nbsp;
</div>
<div id="xsr_2" class="ewRow">
	<label><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="="<?php if ($realmlist_list->BasicSearch->getType() == "=") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("ExactPhrase") ?></label>&nbsp;&nbsp;<label><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="AND"<?php if ($realmlist_list->BasicSearch->getType() == "AND") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("AllWord") ?></label>&nbsp;&nbsp;<label><input type="radio" name="<?php echo EW_TABLE_BASIC_SEARCH_TYPE ?>" value="OR"<?php if ($realmlist_list->BasicSearch->getType() == "OR") { ?> checked="checked"<?php } ?>><?php echo $Language->Phrase("AnyWord") ?></label>
</div>
</div>
</div>
</form>
<?php } ?>
<?php } ?>
<?php $realmlist_list->ShowPageHeader(); ?>
<?php
$realmlist_list->ShowMessage();
?>
<br>
<table cellspacing="0" class="ewGrid"><tr><td class="ewGridContent">
<form name="frealmlistlist" id="frealmlistlist" class="ewForm" action="" method="post">
<input type="hidden" name="t" value="realmlist">
<div id="gmp_realmlist" class="ewGridMiddlePanel">
<?php if ($realmlist_list->TotalRecs > 0) { ?>
<table id="tbl_realmlistlist" class="ewTable ewTableSeparate">
<?php echo $realmlist->TableCustomInnerHtml ?>
<thead><!-- Table header -->
	<tr class="ewTableHeader">
<?php

// Render list options
$realmlist_list->RenderListOptions();

// Render list options (header, left)
$realmlist_list->ListOptions->Render("header", "left");
?>
<?php if ($realmlist->id->Visible) { // id ?>
	<?php if ($realmlist->SortUrl($realmlist->id) == "") { ?>
		<td><span id="elh_realmlist_id" class="realmlist_id"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->id->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->id) ?>',1);"><span id="elh_realmlist_id" class="realmlist_id">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->id->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->id->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->id->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->name->Visible) { // name ?>
	<?php if ($realmlist->SortUrl($realmlist->name) == "") { ?>
		<td><span id="elh_realmlist_name" class="realmlist_name"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->name->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->name) ?>',1);"><span id="elh_realmlist_name" class="realmlist_name">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->name->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->name->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->name->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->address->Visible) { // address ?>
	<?php if ($realmlist->SortUrl($realmlist->address) == "") { ?>
		<td><span id="elh_realmlist_address" class="realmlist_address"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->address->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->address) ?>',1);"><span id="elh_realmlist_address" class="realmlist_address">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->address->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->address->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->address->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->localAddress->Visible) { // localAddress ?>
	<?php if ($realmlist->SortUrl($realmlist->localAddress) == "") { ?>
		<td><span id="elh_realmlist_localAddress" class="realmlist_localAddress"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->localAddress->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->localAddress) ?>',1);"><span id="elh_realmlist_localAddress" class="realmlist_localAddress">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->localAddress->FldCaption() ?><?php echo $Language->Phrase("SrchLegend") ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->localAddress->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->localAddress->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->port->Visible) { // port ?>
	<?php if ($realmlist->SortUrl($realmlist->port) == "") { ?>
		<td><span id="elh_realmlist_port" class="realmlist_port"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->port->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->port) ?>',1);"><span id="elh_realmlist_port" class="realmlist_port">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->port->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->port->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->port->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->icon->Visible) { // icon ?>
	<?php if ($realmlist->SortUrl($realmlist->icon) == "") { ?>
		<td><span id="elh_realmlist_icon" class="realmlist_icon"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->icon->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->icon) ?>',1);"><span id="elh_realmlist_icon" class="realmlist_icon">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->icon->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->icon->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->icon->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->timezone->Visible) { // timezone ?>
	<?php if ($realmlist->SortUrl($realmlist->timezone) == "") { ?>
		<td><span id="elh_realmlist_timezone" class="realmlist_timezone"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->timezone->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->timezone) ?>',1);"><span id="elh_realmlist_timezone" class="realmlist_timezone">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->timezone->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->timezone->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->timezone->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->allowedSecurityLevel->Visible) { // allowedSecurityLevel ?>
	<?php if ($realmlist->SortUrl($realmlist->allowedSecurityLevel) == "") { ?>
		<td><span id="elh_realmlist_allowedSecurityLevel" class="realmlist_allowedSecurityLevel"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->allowedSecurityLevel->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->allowedSecurityLevel) ?>',1);"><span id="elh_realmlist_allowedSecurityLevel" class="realmlist_allowedSecurityLevel">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->allowedSecurityLevel->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->allowedSecurityLevel->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->allowedSecurityLevel->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->population->Visible) { // population ?>
	<?php if ($realmlist->SortUrl($realmlist->population) == "") { ?>
		<td><span id="elh_realmlist_population" class="realmlist_population"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->population->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->population) ?>',1);"><span id="elh_realmlist_population" class="realmlist_population">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->population->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->population->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->population->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php if ($realmlist->gamebuild->Visible) { // gamebuild ?>
	<?php if ($realmlist->SortUrl($realmlist->gamebuild) == "") { ?>
		<td><span id="elh_realmlist_gamebuild" class="realmlist_gamebuild"><table class="ewTableHeaderBtn"><thead><tr><td><?php echo $realmlist->gamebuild->FldCaption() ?></td></tr></thead></table></span></td>
	<?php } else { ?>
		<td><div onmousedown="ew_Sort(event,'<?php echo $realmlist->SortUrl($realmlist->gamebuild) ?>',1);"><span id="elh_realmlist_gamebuild" class="realmlist_gamebuild">
			<table class="ewTableHeaderBtn"><thead><tr><td class="ewTableHeaderCaption"><?php echo $realmlist->gamebuild->FldCaption() ?></td><td class="ewTableHeaderSort"><?php if ($realmlist->gamebuild->getSort() == "ASC") { ?><img src="images/sortup.gif" width="10" height="9" alt="" style="border: 0;"><?php } elseif ($realmlist->gamebuild->getSort() == "DESC") { ?><img src="images/sortdown.gif" width="10" height="9" alt="" style="border: 0;"><?php } ?></td></tr></thead></table>
		</span></div></td>		
	<?php } ?>
<?php } ?>		
<?php

// Render list options (header, right)
$realmlist_list->ListOptions->Render("header", "right");
?>
	</tr>
</thead>
<tbody>
<?php
if ($realmlist->ExportAll && $realmlist->Export <> "") {
	$realmlist_list->StopRec = $realmlist_list->TotalRecs;
} else {

	// Set the last record to display
	if ($realmlist_list->TotalRecs > $realmlist_list->StartRec + $realmlist_list->DisplayRecs - 1)
		$realmlist_list->StopRec = $realmlist_list->StartRec + $realmlist_list->DisplayRecs - 1;
	else
		$realmlist_list->StopRec = $realmlist_list->TotalRecs;
}
$realmlist_list->RecCnt = $realmlist_list->StartRec - 1;
if ($realmlist_list->Recordset && !$realmlist_list->Recordset->EOF) {
	$realmlist_list->Recordset->MoveFirst();
	if (!$bSelectLimit && $realmlist_list->StartRec > 1)
		$realmlist_list->Recordset->Move($realmlist_list->StartRec - 1);
} elseif (!$realmlist->AllowAddDeleteRow && $realmlist_list->StopRec == 0) {
	$realmlist_list->StopRec = $realmlist->GridAddRowCount;
}

// Initialize aggregate
$realmlist->RowType = EW_ROWTYPE_AGGREGATEINIT;
$realmlist->ResetAttrs();
$realmlist_list->RenderRow();
while ($realmlist_list->RecCnt < $realmlist_list->StopRec) {
	$realmlist_list->RecCnt++;
	if (intval($realmlist_list->RecCnt) >= intval($realmlist_list->StartRec)) {
		$realmlist_list->RowCnt++;

		// Set up key count
		$realmlist_list->KeyCount = $realmlist_list->RowIndex;

		// Init row class and style
		$realmlist->ResetAttrs();
		$realmlist->CssClass = "";
		if ($realmlist->CurrentAction == "gridadd") {
		} else {
			$realmlist_list->LoadRowValues($realmlist_list->Recordset); // Load row values
		}
		$realmlist->RowType = EW_ROWTYPE_VIEW; // Render view

		// Set up row id / data-rowindex
		$realmlist->RowAttrs = array_merge($realmlist->RowAttrs, array('data-rowindex'=>$realmlist_list->RowCnt, 'id'=>'r' . $realmlist_list->RowCnt . '_realmlist', 'data-rowtype'=>$realmlist->RowType));

		// Render row
		$realmlist_list->RenderRow();

		// Render list options
		$realmlist_list->RenderListOptions();
?>
	<tr<?php echo $realmlist->RowAttributes() ?>>
<?php

// Render list options (body, left)
$realmlist_list->ListOptions->Render("body", "left", $realmlist_list->RowCnt);
?>
	<?php if ($realmlist->id->Visible) { // id ?>
		<td<?php echo $realmlist->id->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_id" class="realmlist_id">
<span<?php echo $realmlist->id->ViewAttributes() ?>>
<?php echo $realmlist->id->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->name->Visible) { // name ?>
		<td<?php echo $realmlist->name->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_name" class="realmlist_name">
<span<?php echo $realmlist->name->ViewAttributes() ?>>
<?php echo $realmlist->name->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->address->Visible) { // address ?>
		<td<?php echo $realmlist->address->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_address" class="realmlist_address">
<span<?php echo $realmlist->address->ViewAttributes() ?>>
<?php echo $realmlist->address->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->localAddress->Visible) { // localAddress ?>
		<td<?php echo $realmlist->localAddress->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_localAddress" class="realmlist_localAddress">
<span<?php echo $realmlist->localAddress->ViewAttributes() ?>>
<?php echo $realmlist->localAddress->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->port->Visible) { // port ?>
		<td<?php echo $realmlist->port->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_port" class="realmlist_port">
<span<?php echo $realmlist->port->ViewAttributes() ?>>
<?php echo $realmlist->port->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->icon->Visible) { // icon ?>
		<td<?php echo $realmlist->icon->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_icon" class="realmlist_icon">
<span<?php echo $realmlist->icon->ViewAttributes() ?>>
<?php echo $realmlist->icon->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->timezone->Visible) { // timezone ?>
		<td<?php echo $realmlist->timezone->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_timezone" class="realmlist_timezone">
<span<?php echo $realmlist->timezone->ViewAttributes() ?>>
<?php echo $realmlist->timezone->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->allowedSecurityLevel->Visible) { // allowedSecurityLevel ?>
		<td<?php echo $realmlist->allowedSecurityLevel->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_allowedSecurityLevel" class="realmlist_allowedSecurityLevel">
<span<?php echo $realmlist->allowedSecurityLevel->ViewAttributes() ?>>
<?php echo $realmlist->allowedSecurityLevel->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->population->Visible) { // population ?>
		<td<?php echo $realmlist->population->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_population" class="realmlist_population">
<span<?php echo $realmlist->population->ViewAttributes() ?>>
<?php echo $realmlist->population->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
	<?php if ($realmlist->gamebuild->Visible) { // gamebuild ?>
		<td<?php echo $realmlist->gamebuild->CellAttributes() ?>><span id="el<?php echo $realmlist_list->RowCnt ?>_realmlist_gamebuild" class="realmlist_gamebuild">
<span<?php echo $realmlist->gamebuild->ViewAttributes() ?>>
<?php echo $realmlist->gamebuild->ListViewValue() ?></span>
</span><a id="<?php echo $realmlist_list->PageObjName . "_row_" . $realmlist_list->RowCnt ?>"></a></td>
	<?php } ?>
<?php

// Render list options (body, right)
$realmlist_list->ListOptions->Render("body", "right", $realmlist_list->RowCnt);
?>
	</tr>
<?php
	}
	if ($realmlist->CurrentAction <> "gridadd")
		$realmlist_list->Recordset->MoveNext();
}
?>
</tbody>
</table>
<?php } ?>
<?php if ($realmlist->CurrentAction == "") { ?>
<input type="hidden" name="a_list" id="a_list" value="">
<?php } ?>
</div>
</form>
<?php

// Close recordset
if ($realmlist_list->Recordset)
	$realmlist_list->Recordset->Close();
?>
<?php if ($realmlist->Export == "") { ?>
<div class="ewGridLowerPanel">
<?php if ($realmlist->CurrentAction <> "gridadd" && $realmlist->CurrentAction <> "gridedit") { ?>
<form name="ewpagerform" id="ewpagerform" class="ewForm" action="<?php echo ew_CurrentPage() ?>">
<table class="ewPager"><tr><td>
<?php if (!isset($realmlist_list->Pager)) $realmlist_list->Pager = new cPrevNextPager($realmlist_list->StartRec, $realmlist_list->DisplayRecs, $realmlist_list->TotalRecs) ?>
<?php if ($realmlist_list->Pager->RecordCount > 0) { ?>
	<table cellspacing="0" class="ewStdTable"><tbody><tr><td><span class="phpmaker"><?php echo $Language->Phrase("Page") ?>&nbsp;</span></td>
<!--first page button-->
	<?php if ($realmlist_list->Pager->FirstButton->Enabled) { ?>
	<td><a href="<?php echo $realmlist_list->PageUrl() ?>start=<?php echo $realmlist_list->Pager->FirstButton->Start ?>"><img src="images/first.gif" alt="<?php echo $Language->Phrase("PagerFirst") ?>" width="16" height="16" style="border: 0;"></a></td>
	<?php } else { ?>
	<td><img src="images/firstdisab.gif" alt="<?php echo $Language->Phrase("PagerFirst") ?>" width="16" height="16" style="border: 0;"></td>
	<?php } ?>
<!--previous page button-->
	<?php if ($realmlist_list->Pager->PrevButton->Enabled) { ?>
	<td><a href="<?php echo $realmlist_list->PageUrl() ?>start=<?php echo $realmlist_list->Pager->PrevButton->Start ?>"><img src="images/prev.gif" alt="<?php echo $Language->Phrase("PagerPrevious") ?>" width="16" height="16" style="border: 0;"></a></td>
	<?php } else { ?>
	<td><img src="images/prevdisab.gif" alt="<?php echo $Language->Phrase("PagerPrevious") ?>" width="16" height="16" style="border: 0;"></td>
	<?php } ?>
<!--current page number-->
	<td><input type="text" name="<?php echo EW_TABLE_PAGE_NO ?>" id="<?php echo EW_TABLE_PAGE_NO ?>" value="<?php echo $realmlist_list->Pager->CurrentPage ?>" size="4"></td>
<!--next page button-->
	<?php if ($realmlist_list->Pager->NextButton->Enabled) { ?>
	<td><a href="<?php echo $realmlist_list->PageUrl() ?>start=<?php echo $realmlist_list->Pager->NextButton->Start ?>"><img src="images/next.gif" alt="<?php echo $Language->Phrase("PagerNext") ?>" width="16" height="16" style="border: 0;"></a></td>	
	<?php } else { ?>
	<td><img src="images/nextdisab.gif" alt="<?php echo $Language->Phrase("PagerNext") ?>" width="16" height="16" style="border: 0;"></td>
	<?php } ?>
<!--last page button-->
	<?php if ($realmlist_list->Pager->LastButton->Enabled) { ?>
	<td><a href="<?php echo $realmlist_list->PageUrl() ?>start=<?php echo $realmlist_list->Pager->LastButton->Start ?>"><img src="images/last.gif" alt="<?php echo $Language->Phrase("PagerLast") ?>" width="16" height="16" style="border: 0;"></a></td>	
	<?php } else { ?>
	<td><img src="images/lastdisab.gif" alt="<?php echo $Language->Phrase("PagerLast") ?>" width="16" height="16" style="border: 0;"></td>
	<?php } ?>
	<td><span class="phpmaker">&nbsp;<?php echo $Language->Phrase("of") ?>&nbsp;<?php echo $realmlist_list->Pager->PageCount ?></span></td>
	</tr></tbody></table>
	</td>	
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td>
	<span class="phpmaker"><?php echo $Language->Phrase("Record") ?>&nbsp;<?php echo $realmlist_list->Pager->FromIndex ?>&nbsp;<?php echo $Language->Phrase("To") ?>&nbsp;<?php echo $realmlist_list->Pager->ToIndex ?>&nbsp;<?php echo $Language->Phrase("Of") ?>&nbsp;<?php echo $realmlist_list->Pager->RecordCount ?></span>
<?php } else { ?>
	<?php if ($realmlist_list->SearchWhere == "0=101") { ?>
	<span class="phpmaker"><?php echo $Language->Phrase("EnterSearchCriteria") ?></span>
	<?php } else { ?>
	<span class="phpmaker"><?php echo $Language->Phrase("NoRecord") ?></span>
	<?php } ?>
<?php } ?>
	</td>
<?php if ($realmlist_list->TotalRecs > 0) { ?>
	<td>&nbsp;&nbsp;&nbsp;&nbsp;</td>
	<td><table cellspacing="0" class="ewStdTable"><tbody><tr><td><?php echo $Language->Phrase("RecordsPerPage") ?>&nbsp;</td><td>
<input type="hidden" name="t" value="realmlist">
<select name="<?php echo EW_TABLE_REC_PER_PAGE ?>" id="<?php echo EW_TABLE_REC_PER_PAGE ?>" onchange="this.form.submit();">
<option value="10"<?php if ($realmlist_list->DisplayRecs == 10) { ?> selected="selected"<?php } ?>>10</option>
<option value="20"<?php if ($realmlist_list->DisplayRecs == 20) { ?> selected="selected"<?php } ?>>20</option>
<option value="30"<?php if ($realmlist_list->DisplayRecs == 30) { ?> selected="selected"<?php } ?>>30</option>
<option value="40"<?php if ($realmlist_list->DisplayRecs == 40) { ?> selected="selected"<?php } ?>>40</option>
<option value="50"<?php if ($realmlist_list->DisplayRecs == 50) { ?> selected="selected"<?php } ?>>50</option>
<option value="ALL"<?php if ($realmlist->getRecordsPerPage() == -1) { ?> selected="selected"<?php } ?>><?php echo $Language->Phrase("AllRecords") ?></option>
</select></td></tr></tbody></table>
	</td>
<?php } ?>
</tr></table>
</form>
<?php } ?>
<span class="phpmaker">
<?php if ($Security->IsLoggedIn()) { ?>
<?php if ($realmlist_list->AddUrl <> "") { ?>
<a class="ewGridLink" href="<?php echo $realmlist_list->AddUrl ?>"><?php echo $Language->Phrase("AddLink") ?></a>&nbsp;&nbsp;
<?php } ?>
<?php } ?>
<?php if ($realmlist_list->TotalRecs > 0) { ?>
<?php if ($Security->IsLoggedIn()) { ?>
<a class="ewGridLink" href="" onclick="ew_SubmitSelected(document.frealmlistlist, '<?php echo $realmlist_list->MultiDeleteUrl ?>');return false;"><?php echo $Language->Phrase("DeleteSelectedLink") ?></a>&nbsp;&nbsp;
<?php } ?>
<?php } ?>
</span>
</div>
<?php } ?>
</td></tr></table>
<?php if ($realmlist->Export == "") { ?>
<script type="text/javascript">
frealmlistlistsrch.Init();
frealmlistlist.Init();
</script>
<?php } ?>
<?php
$realmlist_list->ShowPageFooter();
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
$realmlist_list->Page_Terminate();
?>
