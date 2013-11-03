<?php

// Global variable for table object
$realmlist = NULL;

//
// Table class for realmlist
//
class crealmlist extends cTable {
	var $id;
	var $name;
	var $address;
	var $localAddress;
	var $localSubnetMask;
	var $port;
	var $icon;
	var $flag;
	var $timezone;
	var $allowedSecurityLevel;
	var $population;
	var $gamebuild;

	//
	// Table class constructor
	//
	function __construct() {
		global $Language;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();
		$this->TableVar = 'realmlist';
		$this->TableName = 'realmlist';
		$this->TableType = 'TABLE';
		$this->ExportAll = TRUE;
		$this->ExportPageBreakCount = 0; // Page break per every n record (PDF only)
		$this->ExportPageOrientation = "portrait"; // Page orientation (PDF only)
		$this->ExportPageSize = "a4"; // Page size (PDF only)
		$this->DetailAdd = FALSE; // Allow detail add
		$this->DetailEdit = FALSE; // Allow detail edit
		$this->GridAddRowCount = 5;
		$this->AllowAddDeleteRow = ew_AllowAddDeleteRow(); // Allow add/delete row
		$this->UserIDAllowSecurity = 0; // User ID Allow
		$this->BasicSearch = new cBasicSearch($this->TableVar);

		// id
		$this->id = new cField('realmlist', 'realmlist', 'x_id', 'id', '`id`', '`id`', 19, -1, FALSE, '`id`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->id->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['id'] = &$this->id;

		// name
		$this->name = new cField('realmlist', 'realmlist', 'x_name', 'name', '`name`', '`name`', 200, -1, FALSE, '`name`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['name'] = &$this->name;

		// address
		$this->address = new cField('realmlist', 'realmlist', 'x_address', 'address', '`address`', '`address`', 200, -1, FALSE, '`address`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['address'] = &$this->address;

		// localAddress
		$this->localAddress = new cField('realmlist', 'realmlist', 'x_localAddress', 'localAddress', '`localAddress`', '`localAddress`', 200, -1, FALSE, '`localAddress`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['localAddress'] = &$this->localAddress;

		// localSubnetMask
		$this->localSubnetMask = new cField('realmlist', 'realmlist', 'x_localSubnetMask', 'localSubnetMask', '`localSubnetMask`', '`localSubnetMask`', 200, -1, FALSE, '`localSubnetMask`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['localSubnetMask'] = &$this->localSubnetMask;

		// port
		$this->port = new cField('realmlist', 'realmlist', 'x_port', 'port', '`port`', '`port`', 18, -1, FALSE, '`port`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->port->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['port'] = &$this->port;

		// icon
		$this->icon = new cField('realmlist', 'realmlist', 'x_icon', 'icon', '`icon`', '`icon`', 17, -1, FALSE, '`icon`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->icon->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['icon'] = &$this->icon;

		// flag
		$this->flag = new cField('realmlist', 'realmlist', 'x_flag', 'flag', '`flag`', '`flag`', 17, -1, FALSE, '`flag`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->flag->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['flag'] = &$this->flag;

		// timezone
		$this->timezone = new cField('realmlist', 'realmlist', 'x_timezone', 'timezone', '`timezone`', '`timezone`', 17, -1, FALSE, '`timezone`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->timezone->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['timezone'] = &$this->timezone;

		// allowedSecurityLevel
		$this->allowedSecurityLevel = new cField('realmlist', 'realmlist', 'x_allowedSecurityLevel', 'allowedSecurityLevel', '`allowedSecurityLevel`', '`allowedSecurityLevel`', 17, -1, FALSE, '`allowedSecurityLevel`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->allowedSecurityLevel->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['allowedSecurityLevel'] = &$this->allowedSecurityLevel;

		// population
		$this->population = new cField('realmlist', 'realmlist', 'x_population', 'population', '`population`', '`population`', 4, -1, FALSE, '`population`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->population->FldDefaultErrMsg = $Language->Phrase("IncorrectFloat");
		$this->fields['population'] = &$this->population;

		// gamebuild
		$this->gamebuild = new cField('realmlist', 'realmlist', 'x_gamebuild', 'gamebuild', '`gamebuild`', '`gamebuild`', 19, -1, FALSE, '`gamebuild`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->gamebuild->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['gamebuild'] = &$this->gamebuild;
	}

	// Single column sort
	function UpdateSort(&$ofld) {
		if ($this->CurrentOrder == $ofld->FldName) {
			$sSortField = $ofld->FldExpression;
			$sLastSort = $ofld->getSort();
			if ($this->CurrentOrderType == "ASC" || $this->CurrentOrderType == "DESC") {
				$sThisSort = $this->CurrentOrderType;
			} else {
				$sThisSort = ($sLastSort == "ASC") ? "DESC" : "ASC";
			}
			$ofld->setSort($sThisSort);
			$this->setSessionOrderBy($sSortField . " " . $sThisSort); // Save to Session
		} else {
			$ofld->setSort("");
		}
	}

	// Table level SQL
	function SqlFrom() { // From
		return "`realmlist`";
	}

	function SqlSelect() { // Select
		return "SELECT * FROM " . $this->SqlFrom();
	}

	function SqlWhere() { // Where
		$sWhere = "";
		$this->TableFilter = "";
		ew_AddFilter($sWhere, $this->TableFilter);
		return $sWhere;
	}

	function SqlGroupBy() { // Group By
		return "";
	}

	function SqlHaving() { // Having
		return "";
	}

	function SqlOrderBy() { // Order By
		return "";
	}

	// Check if Anonymous User is allowed
	function AllowAnonymousUser() {
		switch (@$this->PageID) {
			case "add":
			case "register":
			case "addopt":
				return FALSE;
			case "edit":
			case "update":
			case "changepwd":
			case "forgotpwd":
				return FALSE;
			case "delete":
				return FALSE;
			case "view":
				return FALSE;
			case "search":
				return FALSE;
			default:
				return FALSE;
		}
	}

	// Apply User ID filters
	function ApplyUserIDFilters($sFilter) {
		return $sFilter;
	}

	// Check if User ID security allows view all
	function UserIDAllow($id = "") {
		return TRUE;
	}

	// Get SQL
	function GetSQL($where, $orderby) {
		return ew_BuildSelectSql($this->SqlSelect(), $this->SqlWhere(),
			$this->SqlGroupBy(), $this->SqlHaving(), $this->SqlOrderBy(),
			$where, $orderby);
	}

	// Table SQL
	function SQL() {
		$sFilter = $this->CurrentFilter;
		$sFilter = $this->ApplyUserIDFilters($sFilter);
		$sSort = $this->getSessionOrderBy();
		return ew_BuildSelectSql($this->SqlSelect(), $this->SqlWhere(),
			$this->SqlGroupBy(), $this->SqlHaving(), $this->SqlOrderBy(),
			$sFilter, $sSort);
	}

	// Table SQL with List page filter
	function SelectSQL() {
		$sFilter = $this->getSessionWhere();
		ew_AddFilter($sFilter, $this->CurrentFilter);
		$sFilter = $this->ApplyUserIDFilters($sFilter);
		$sSort = $this->getSessionOrderBy();
		return ew_BuildSelectSql($this->SqlSelect(), $this->SqlWhere(), $this->SqlGroupBy(),
			$this->SqlHaving(), $this->SqlOrderBy(), $sFilter, $sSort);
	}

	// Get ORDER BY clause
	function GetOrderBy() {
		$sSort = $this->getSessionOrderBy();
		return ew_BuildSelectSql("", "", "", "", $this->SqlOrderBy(), "", $sSort);
	}

	// Try to get record count
	function TryGetRecordCount($sSql) {
		global $conn;
		$cnt = -1;
		if ($this->TableType == 'TABLE' || $this->TableType == 'VIEW') {
			$sSql = "SELECT COUNT(*) FROM" . substr($sSql, 13);
			$sOrderBy = $this->GetOrderBy();
			if (substr($sSql, strlen($sOrderBy) * -1) == $sOrderBy)
				$sSql = substr($sSql, 0, strlen($sSql) - strlen($sOrderBy)); // Remove ORDER BY clause
		} else {
			$sSql = "SELECT COUNT(*) FROM (" . $sSql . ") EW_COUNT_TABLE";
		}
		if ($rs = $conn->Execute($sSql)) {
			if (!$rs->EOF && $rs->FieldCount() > 0) {
				$cnt = $rs->fields[0];
				$rs->Close();
			}
		}
		return intval($cnt);
	}

	// Get record count based on filter (for detail record count in master table pages)
	function LoadRecordCount($sFilter) {
		$origFilter = $this->CurrentFilter;
		$this->CurrentFilter = $sFilter;
		$this->Recordset_Selecting($this->CurrentFilter);

		//$sSql = $this->SQL();
		$sSql = $this->GetSQL($this->CurrentFilter, "");
		$cnt = $this->TryGetRecordCount($sSql);
		if ($cnt == -1) {
			if ($rs = $this->LoadRs($this->CurrentFilter)) {
				$cnt = $rs->RecordCount();
				$rs->Close();
			}
		}
		$this->CurrentFilter = $origFilter;
		return intval($cnt);
	}

	// Get record count (for current List page)
	function SelectRecordCount() {
		global $conn;
		$origFilter = $this->CurrentFilter;
		$this->Recordset_Selecting($this->CurrentFilter);
		$sSql = $this->SelectSQL();
		$cnt = $this->TryGetRecordCount($sSql);
		if ($cnt == -1) {
			if ($rs = $conn->Execute($sSql)) {
				$cnt = $rs->RecordCount();
				$rs->Close();
			}
		}
		$this->CurrentFilter = $origFilter;
		return intval($cnt);
	}

	// Update Table
	var $UpdateTable = "`realmlist`";

	// INSERT statement
	function InsertSQL(&$rs) {
		global $conn;
		$names = "";
		$values = "";
		foreach ($rs as $name => $value) {
			if (!isset($this->fields[$name]))
				continue;
			$names .= $this->fields[$name]->FldExpression . ",";
			$values .= ew_QuotedValue($value, $this->fields[$name]->FldDataType) . ",";
		}
		while (substr($names, -1) == ",")
			$names = substr($names, 0, -1);
		while (substr($values, -1) == ",")
			$values = substr($values, 0, -1);
		return "INSERT INTO " . $this->UpdateTable . " ($names) VALUES ($values)";
	}

	// Insert
	function Insert(&$rs) {
		global $conn;
		return $conn->Execute($this->InsertSQL($rs));
	}

	// UPDATE statement
	function UpdateSQL(&$rs, $where = "") {
		$sql = "UPDATE " . $this->UpdateTable . " SET ";
		foreach ($rs as $name => $value) {
			if (!isset($this->fields[$name]))
				continue;
			$sql .= $this->fields[$name]->FldExpression . "=";
			$sql .= ew_QuotedValue($value, $this->fields[$name]->FldDataType) . ",";
		}
		while (substr($sql, -1) == ",")
			$sql = substr($sql, 0, -1);
		$filter = $this->CurrentFilter;
		ew_AddFilter($filter, $where);
		if ($filter <> "")	$sql .= " WHERE " . $filter;
		return $sql;
	}

	// Update
	function Update(&$rs, $where = "") {
		global $conn;
		return $conn->Execute($this->UpdateSQL($rs, $where));
	}

	// DELETE statement
	function DeleteSQL(&$rs, $where = "") {
		$sql = "DELETE FROM " . $this->UpdateTable . " WHERE ";
		if ($rs) {
			$sql .= ew_QuotedName('id') . '=' . ew_QuotedValue($rs['id'], $this->id->FldDataType) . ' AND ';
		}
		if (substr($sql, -5) == " AND ") $sql = substr($sql, 0, -5);
		$filter = $this->CurrentFilter;
		ew_AddFilter($filter, $where);
		if ($filter <> "")	$sql .= " AND " . $filter;
		return $sql;
	}

	// Delete
	function Delete(&$rs, $where = "") {
		global $conn;
		return $conn->Execute($this->DeleteSQL($rs, $where));
	}

	// Key filter WHERE clause
	function SqlKeyFilter() {
		return "`id` = @id@";
	}

	// Key filter
	function KeyFilter() {
		$sKeyFilter = $this->SqlKeyFilter();
		if (!is_numeric($this->id->CurrentValue))
			$sKeyFilter = "0=1"; // Invalid key
		$sKeyFilter = str_replace("@id@", ew_AdjustSql($this->id->CurrentValue), $sKeyFilter); // Replace key value
		return $sKeyFilter;
	}

	// Return page URL
	function getReturnUrl() {
		$name = EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL;

		// Get referer URL automatically
		if (ew_ServerVar("HTTP_REFERER") <> "" && ew_ReferPage() <> ew_CurrentPage() && ew_ReferPage() <> "login.php") // Referer not same page or login page
			$_SESSION[$name] = ew_ServerVar("HTTP_REFERER"); // Save to Session
		if (@$_SESSION[$name] <> "") {
			return $_SESSION[$name];
		} else {
			return "realmlistlist.php";
		}
	}

	function setReturnUrl($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] = $v;
	}

	// List URL
	function GetListUrl() {
		return "realmlistlist.php";
	}

	// View URL
	function GetViewUrl() {
		return $this->KeyUrl("realmlistview.php", $this->UrlParm());
	}

	// Add URL
	function GetAddUrl() {
		return "realmlistadd.php";
	}

	// Edit URL
	function GetEditUrl($parm = "") {
		return $this->KeyUrl("realmlistedit.php", $this->UrlParm($parm));
	}

	// Inline edit URL
	function GetInlineEditUrl() {
		return $this->KeyUrl(ew_CurrentPage(), $this->UrlParm("a=edit"));
	}

	// Copy URL
	function GetCopyUrl($parm = "") {
		return $this->KeyUrl("realmlistadd.php", $this->UrlParm($parm));
	}

	// Inline copy URL
	function GetInlineCopyUrl() {
		return $this->KeyUrl(ew_CurrentPage(), $this->UrlParm("a=copy"));
	}

	// Delete URL
	function GetDeleteUrl() {
		return $this->KeyUrl("realmlistdelete.php", $this->UrlParm());
	}

	// Add key value to URL
	function KeyUrl($url, $parm = "") {
		$sUrl = $url . "?";
		if ($parm <> "") $sUrl .= $parm . "&";
		if (!is_null($this->id->CurrentValue)) {
			$sUrl .= "id=" . urlencode($this->id->CurrentValue);
		} else {
			return "javascript:alert(ewLanguage.Phrase('InvalidRecord'));";
		}
		return $sUrl;
	}

	// Sort URL
	function SortUrl(&$fld) {
		if ($this->CurrentAction <> "" || $this->Export <> "" ||
			in_array($fld->FldType, array(128, 204, 205))) { // Unsortable data type
				return "";
		} elseif ($fld->Sortable) {
			$sUrlParm = $this->UrlParm("order=" . urlencode($fld->FldName) . "&ordertype=" . $fld->ReverseSort());
			return ew_CurrentPage() . "?" . $sUrlParm;
		} else {
			return "";
		}
	}

	// Get record keys from $_POST/$_GET/$_SESSION
	function GetRecordKeys() {
		global $EW_COMPOSITE_KEY_SEPARATOR;
		$arKeys = array();
		$arKey = array();
		if (isset($_POST["key_m"])) {
			$arKeys = ew_StripSlashes($_POST["key_m"]);
			$cnt = count($arKeys);
		} elseif (isset($_GET["key_m"])) {
			$arKeys = ew_StripSlashes($_GET["key_m"]);
			$cnt = count($arKeys);
		} elseif (isset($_GET)) {
			$arKeys[] = @$_GET["id"]; // id

			//return $arKeys; // do not return yet, so the values will also be checked by the following code
		}

		// check keys
		$ar = array();
		foreach ($arKeys as $key) {
			if (!is_numeric($key))
				continue;
			$ar[] = $key;
		}
		return $ar;
	}

	// Get key filter
	function GetKeyFilter() {
		$arKeys = $this->GetRecordKeys();
		$sKeyFilter = "";
		foreach ($arKeys as $key) {
			if ($sKeyFilter <> "") $sKeyFilter .= " OR ";
			$this->id->CurrentValue = $key;
			$sKeyFilter .= "(" . $this->KeyFilter() . ")";
		}
		return $sKeyFilter;
	}

	// Load rows based on filter
	function &LoadRs($sFilter) {
		global $conn;

		// Set up filter (SQL WHERE clause) and get return SQL
		//$this->CurrentFilter = $sFilter;
		//$sSql = $this->SQL();

		$sSql = $this->GetSQL($sFilter, "");
		$rs = $conn->Execute($sSql);
		return $rs;
	}

	// Load row values from recordset
	function LoadListRowValues(&$rs) {
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

	// Render list row values
	function RenderListRow() {
		global $conn, $Security;

		// Call Row Rendering event
		$this->Row_Rendering();

   // Common render codes
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

		// flag
		$this->flag->ViewValue = $this->flag->CurrentValue;
		$this->flag->ViewCustomAttributes = "";

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

		// flag
		$this->flag->LinkCustomAttributes = "";
		$this->flag->HrefValue = "";
		$this->flag->TooltipValue = "";

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

		// Call Row Rendered event
		$this->Row_Rendered();
	}

	// Aggregate list row values
	function AggregateListRowValues() {
	}

	// Aggregate list row (for rendering)
	function AggregateListRow() {
	}

	// Export data in HTML/CSV/Word/Excel/Email/PDF format
	function ExportDocument(&$Doc, &$Recordset, $StartRec, $StopRec, $ExportPageType = "") {
		if (!$Recordset || !$Doc)
			return;

		// Write header
		$Doc->ExportTableHeader();
		if ($Doc->Horizontal) { // Horizontal format, write header
			$Doc->BeginExportRow();
			if ($ExportPageType == "view") {
				if ($this->id->Exportable) $Doc->ExportCaption($this->id);
				if ($this->name->Exportable) $Doc->ExportCaption($this->name);
				if ($this->address->Exportable) $Doc->ExportCaption($this->address);
				if ($this->localAddress->Exportable) $Doc->ExportCaption($this->localAddress);
				if ($this->localSubnetMask->Exportable) $Doc->ExportCaption($this->localSubnetMask);
				if ($this->port->Exportable) $Doc->ExportCaption($this->port);
				if ($this->icon->Exportable) $Doc->ExportCaption($this->icon);
				if ($this->timezone->Exportable) $Doc->ExportCaption($this->timezone);
				if ($this->allowedSecurityLevel->Exportable) $Doc->ExportCaption($this->allowedSecurityLevel);
				if ($this->population->Exportable) $Doc->ExportCaption($this->population);
				if ($this->gamebuild->Exportable) $Doc->ExportCaption($this->gamebuild);
			} else {
				if ($this->id->Exportable) $Doc->ExportCaption($this->id);
				if ($this->name->Exportable) $Doc->ExportCaption($this->name);
				if ($this->address->Exportable) $Doc->ExportCaption($this->address);
				if ($this->localAddress->Exportable) $Doc->ExportCaption($this->localAddress);
				if ($this->port->Exportable) $Doc->ExportCaption($this->port);
				if ($this->icon->Exportable) $Doc->ExportCaption($this->icon);
				if ($this->timezone->Exportable) $Doc->ExportCaption($this->timezone);
				if ($this->allowedSecurityLevel->Exportable) $Doc->ExportCaption($this->allowedSecurityLevel);
				if ($this->population->Exportable) $Doc->ExportCaption($this->population);
				if ($this->gamebuild->Exportable) $Doc->ExportCaption($this->gamebuild);
			}
			$Doc->EndExportRow();
		}

		// Move to first record
		$RecCnt = $StartRec - 1;
		if (!$Recordset->EOF) {
			$Recordset->MoveFirst();
			if ($StartRec > 1)
				$Recordset->Move($StartRec - 1);
		}
		while (!$Recordset->EOF && $RecCnt < $StopRec) {
			$RecCnt++;
			if (intval($RecCnt) >= intval($StartRec)) {
				$RowCnt = intval($RecCnt) - intval($StartRec) + 1;

				// Page break
				if ($this->ExportPageBreakCount > 0) {
					if ($RowCnt > 1 && ($RowCnt - 1) % $this->ExportPageBreakCount == 0)
						$Doc->ExportPageBreak();
				}
				$this->LoadListRowValues($Recordset);

				// Render row
				$this->RowType = EW_ROWTYPE_VIEW; // Render view
				$this->ResetAttrs();
				$this->RenderListRow();
				$Doc->BeginExportRow($RowCnt); // Allow CSS styles if enabled
				if ($ExportPageType == "view") {
					if ($this->id->Exportable) $Doc->ExportField($this->id);
					if ($this->name->Exportable) $Doc->ExportField($this->name);
					if ($this->address->Exportable) $Doc->ExportField($this->address);
					if ($this->localAddress->Exportable) $Doc->ExportField($this->localAddress);
					if ($this->localSubnetMask->Exportable) $Doc->ExportField($this->localSubnetMask);
					if ($this->port->Exportable) $Doc->ExportField($this->port);
					if ($this->icon->Exportable) $Doc->ExportField($this->icon);
					if ($this->timezone->Exportable) $Doc->ExportField($this->timezone);
					if ($this->allowedSecurityLevel->Exportable) $Doc->ExportField($this->allowedSecurityLevel);
					if ($this->population->Exportable) $Doc->ExportField($this->population);
					if ($this->gamebuild->Exportable) $Doc->ExportField($this->gamebuild);
				} else {
					if ($this->id->Exportable) $Doc->ExportField($this->id);
					if ($this->name->Exportable) $Doc->ExportField($this->name);
					if ($this->address->Exportable) $Doc->ExportField($this->address);
					if ($this->localAddress->Exportable) $Doc->ExportField($this->localAddress);
					if ($this->port->Exportable) $Doc->ExportField($this->port);
					if ($this->icon->Exportable) $Doc->ExportField($this->icon);
					if ($this->timezone->Exportable) $Doc->ExportField($this->timezone);
					if ($this->allowedSecurityLevel->Exportable) $Doc->ExportField($this->allowedSecurityLevel);
					if ($this->population->Exportable) $Doc->ExportField($this->population);
					if ($this->gamebuild->Exportable) $Doc->ExportField($this->gamebuild);
				}
				$Doc->EndExportRow();
			}
			$Recordset->MoveNext();
		}
		$Doc->ExportTableFooter();
	}

	// Table level events
	// Recordset Selecting event
	function Recordset_Selecting(&$filter) {

		// Enter your code here	
	}

	// Recordset Selected event
	function Recordset_Selected(&$rs) {

		//echo "Recordset Selected";
	}

	// Recordset Search Validated event
	function Recordset_SearchValidated() {

		// Example:
		//$this->MyField1->AdvancedSearch->SearchValue = "your search criteria"; // Search value

	}

	// Recordset Searching event
	function Recordset_Searching(&$filter) {

		// Enter your code here	
	}

	// Row_Selecting event
	function Row_Selecting(&$filter) {

		// Enter your code here	
	}

	// Row Selected event
	function Row_Selected(&$rs) {

		//echo "Row Selected";
	}

	// Row Inserting event
	function Row_Inserting($rsold, &$rsnew) {

		// Enter your code here
		// To cancel, set return value to FALSE

		return TRUE;
	}

	// Row Inserted event
	function Row_Inserted($rsold, &$rsnew) {

		//echo "Row Inserted"
	}

	// Row Updating event
	function Row_Updating($rsold, &$rsnew) {

		// Enter your code here
		// To cancel, set return value to FALSE

		return TRUE;
	}

	// Row Updated event
	function Row_Updated($rsold, &$rsnew) {

		//echo "Row Updated";
	}

	// Row Update Conflict event
	function Row_UpdateConflict($rsold, &$rsnew) {

		// Enter your code here
		// To ignore conflict, set return value to FALSE

		return TRUE;
	}

	// Row Deleting event
	function Row_Deleting(&$rs) {

		// Enter your code here
		// To cancel, set return value to False

		return TRUE;
	}

	// Row Deleted event
	function Row_Deleted(&$rs) {

		//echo "Row Deleted";
	}

	// Email Sending event
	function Email_Sending(&$Email, &$Args) {

		//var_dump($Email); var_dump($Args); exit();
		return TRUE;
	}

	// Row Rendering event
	function Row_Rendering() {

		// Enter your code here	
	}

	// Row Rendered event
	function Row_Rendered() {

		// To view properties of field class, use:
		//var_dump($this-><FieldName>); 

	}

	// User ID Filtering event
	function UserID_Filtering(&$filter) {

		// Enter your code here
	}
}
?>
