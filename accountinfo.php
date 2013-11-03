<?php

// Global variable for table object
$account = NULL;

//
// Table class for account
//
class caccount extends cTable {
	var $id;
	var $username;
	var $sha_pass_hash;
	var $sessionkey;
	var $v;
	var $s;
	var $token_key;
	var $_email;
	var $reg_mail;
	var $joindate;
	var $last_ip;
	var $failed_logins;
	var $locked;
	var $lock_country;
	var $last_login;
	var $online;
	var $expansion;
	var $mutetime;
	var $mutereason;
	var $muteby;
	var $locale;
	var $os;
	var $recruiter;

	//
	// Table class constructor
	//
	function __construct() {
		global $Language;

		// Language object
		if (!isset($Language)) $Language = new cLanguage();
		$this->TableVar = 'account';
		$this->TableName = 'account';
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
		$this->id = new cField('account', 'account', 'x_id', 'id', '`id`', '`id`', 19, -1, FALSE, '`id`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->id->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['id'] = &$this->id;

		// username
		$this->username = new cField('account', 'account', 'x_username', 'username', '`username`', '`username`', 200, -1, FALSE, '`username`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['username'] = &$this->username;

		// sha_pass_hash
		$this->sha_pass_hash = new cField('account', 'account', 'x_sha_pass_hash', 'sha_pass_hash', '`sha_pass_hash`', '`sha_pass_hash`', 200, -1, FALSE, '`sha_pass_hash`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['sha_pass_hash'] = &$this->sha_pass_hash;

		// sessionkey
		$this->sessionkey = new cField('account', 'account', 'x_sessionkey', 'sessionkey', '`sessionkey`', '`sessionkey`', 200, -1, FALSE, '`sessionkey`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['sessionkey'] = &$this->sessionkey;

		// v
		$this->v = new cField('account', 'account', 'x_v', 'v', '`v`', '`v`', 200, -1, FALSE, '`v`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['v'] = &$this->v;

		// s
		$this->s = new cField('account', 'account', 'x_s', 's', '`s`', '`s`', 200, -1, FALSE, '`s`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['s'] = &$this->s;

		// token_key
		$this->token_key = new cField('account', 'account', 'x_token_key', 'token_key', '`token_key`', '`token_key`', 200, -1, FALSE, '`token_key`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['token_key'] = &$this->token_key;

		// email
		$this->_email = new cField('account', 'account', 'x__email', 'email', '`email`', '`email`', 200, -1, FALSE, '`email`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['email'] = &$this->_email;

		// reg_mail
		$this->reg_mail = new cField('account', 'account', 'x_reg_mail', 'reg_mail', '`reg_mail`', '`reg_mail`', 200, -1, FALSE, '`reg_mail`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['reg_mail'] = &$this->reg_mail;

		// joindate
		$this->joindate = new cField('account', 'account', 'x_joindate', 'joindate', '`joindate`', 'DATE_FORMAT(`joindate`, \'%Y/%m/%d %H:%i:%s\')', 135, 9, FALSE, '`joindate`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->joindate->FldDefaultErrMsg = str_replace("%s", "/", $Language->Phrase("IncorrectDateYMD"));
		$this->fields['joindate'] = &$this->joindate;

		// last_ip
		$this->last_ip = new cField('account', 'account', 'x_last_ip', 'last_ip', '`last_ip`', '`last_ip`', 200, -1, FALSE, '`last_ip`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['last_ip'] = &$this->last_ip;

		// failed_logins
		$this->failed_logins = new cField('account', 'account', 'x_failed_logins', 'failed_logins', '`failed_logins`', '`failed_logins`', 19, -1, FALSE, '`failed_logins`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->failed_logins->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['failed_logins'] = &$this->failed_logins;

		// locked
		$this->locked = new cField('account', 'account', 'x_locked', 'locked', '`locked`', '`locked`', 17, -1, FALSE, '`locked`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->locked->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['locked'] = &$this->locked;

		// lock_country
		$this->lock_country = new cField('account', 'account', 'x_lock_country', 'lock_country', '`lock_country`', '`lock_country`', 200, -1, FALSE, '`lock_country`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['lock_country'] = &$this->lock_country;

		// last_login
		$this->last_login = new cField('account', 'account', 'x_last_login', 'last_login', '`last_login`', 'DATE_FORMAT(`last_login`, \'%Y/%m/%d %H:%i:%s\')', 135, 9, FALSE, '`last_login`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->last_login->FldDefaultErrMsg = str_replace("%s", "/", $Language->Phrase("IncorrectDateYMD"));
		$this->fields['last_login'] = &$this->last_login;

		// online
		$this->online = new cField('account', 'account', 'x_online', 'online', '`online`', '`online`', 17, -1, FALSE, '`online`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->online->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['online'] = &$this->online;

		// expansion
		$this->expansion = new cField('account', 'account', 'x_expansion', 'expansion', '`expansion`', '`expansion`', 17, -1, FALSE, '`expansion`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->expansion->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['expansion'] = &$this->expansion;

		// mutetime
		$this->mutetime = new cField('account', 'account', 'x_mutetime', 'mutetime', '`mutetime`', '`mutetime`', 20, -1, FALSE, '`mutetime`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->mutetime->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['mutetime'] = &$this->mutetime;

		// mutereason
		$this->mutereason = new cField('account', 'account', 'x_mutereason', 'mutereason', '`mutereason`', '`mutereason`', 200, -1, FALSE, '`mutereason`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['mutereason'] = &$this->mutereason;

		// muteby
		$this->muteby = new cField('account', 'account', 'x_muteby', 'muteby', '`muteby`', '`muteby`', 200, -1, FALSE, '`muteby`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['muteby'] = &$this->muteby;

		// locale
		$this->locale = new cField('account', 'account', 'x_locale', 'locale', '`locale`', '`locale`', 17, -1, FALSE, '`locale`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->locale->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['locale'] = &$this->locale;

		// os
		$this->os = new cField('account', 'account', 'x_os', 'os', '`os`', '`os`', 200, -1, FALSE, '`os`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->fields['os'] = &$this->os;

		// recruiter
		$this->recruiter = new cField('account', 'account', 'x_recruiter', 'recruiter', '`recruiter`', '`recruiter`', 19, -1, FALSE, '`recruiter`', FALSE, FALSE, FALSE, 'FORMATTED TEXT');
		$this->recruiter->FldDefaultErrMsg = $Language->Phrase("IncorrectInteger");
		$this->fields['recruiter'] = &$this->recruiter;
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
		return "`account`";
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
	var $UpdateTable = "`account`";

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
			return "accountlist.php";
		}
	}

	function setReturnUrl($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_RETURN_URL] = $v;
	}

	// List URL
	function GetListUrl() {
		return "accountlist.php";
	}

	// View URL
	function GetViewUrl() {
		return $this->KeyUrl("accountview.php", $this->UrlParm());
	}

	// Add URL
	function GetAddUrl() {
		return "accountadd.php";
	}

	// Edit URL
	function GetEditUrl($parm = "") {
		return $this->KeyUrl("accountedit.php", $this->UrlParm($parm));
	}

	// Inline edit URL
	function GetInlineEditUrl() {
		return $this->KeyUrl(ew_CurrentPage(), $this->UrlParm("a=edit"));
	}

	// Copy URL
	function GetCopyUrl($parm = "") {
		return $this->KeyUrl("accountadd.php", $this->UrlParm($parm));
	}

	// Inline copy URL
	function GetInlineCopyUrl() {
		return $this->KeyUrl(ew_CurrentPage(), $this->UrlParm("a=copy"));
	}

	// Delete URL
	function GetDeleteUrl() {
		return $this->KeyUrl("accountdelete.php", $this->UrlParm());
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

	// Render list row values
	function RenderListRow() {
		global $conn, $Security;

		// Call Row Rendering event
		$this->Row_Rendering();

   // Common render codes
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
		// id

		$this->id->ViewValue = $this->id->CurrentValue;
		$this->id->ViewCustomAttributes = "";

		// username
		$this->username->ViewValue = $this->username->CurrentValue;
		$this->username->ViewCustomAttributes = "";

		// sha_pass_hash
		$this->sha_pass_hash->ViewValue = $this->sha_pass_hash->CurrentValue;
		$this->sha_pass_hash->ViewCustomAttributes = "";

		// sessionkey
		$this->sessionkey->ViewValue = $this->sessionkey->CurrentValue;
		$this->sessionkey->ViewCustomAttributes = "";

		// v
		$this->v->ViewValue = $this->v->CurrentValue;
		$this->v->ViewCustomAttributes = "";

		// s
		$this->s->ViewValue = $this->s->CurrentValue;
		$this->s->ViewCustomAttributes = "";

		// token_key
		$this->token_key->ViewValue = $this->token_key->CurrentValue;
		$this->token_key->ViewCustomAttributes = "";

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

		// failed_logins
		$this->failed_logins->ViewValue = $this->failed_logins->CurrentValue;
		$this->failed_logins->ViewCustomAttributes = "";

		// locked
		$this->locked->ViewValue = $this->locked->CurrentValue;
		$this->locked->ViewCustomAttributes = "";

		// lock_country
		$this->lock_country->ViewValue = $this->lock_country->CurrentValue;
		$this->lock_country->ViewCustomAttributes = "";

		// last_login
		$this->last_login->ViewValue = $this->last_login->CurrentValue;
		$this->last_login->ViewValue = ew_FormatDateTime($this->last_login->ViewValue, 9);
		$this->last_login->ViewCustomAttributes = "";

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

		// mutetime
		$this->mutetime->ViewValue = $this->mutetime->CurrentValue;
		$this->mutetime->ViewCustomAttributes = "";

		// mutereason
		$this->mutereason->ViewValue = $this->mutereason->CurrentValue;
		$this->mutereason->ViewCustomAttributes = "";

		// muteby
		$this->muteby->ViewValue = $this->muteby->CurrentValue;
		$this->muteby->ViewCustomAttributes = "";

		// locale
		if (strval($this->locale->CurrentValue) <> "") {
			switch ($this->locale->CurrentValue) {
				case $this->locale->FldTagValue(1):
					$this->locale->ViewValue = $this->locale->FldTagCaption(1) <> "" ? $this->locale->FldTagCaption(1) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(2):
					$this->locale->ViewValue = $this->locale->FldTagCaption(2) <> "" ? $this->locale->FldTagCaption(2) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(3):
					$this->locale->ViewValue = $this->locale->FldTagCaption(3) <> "" ? $this->locale->FldTagCaption(3) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(4):
					$this->locale->ViewValue = $this->locale->FldTagCaption(4) <> "" ? $this->locale->FldTagCaption(4) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(5):
					$this->locale->ViewValue = $this->locale->FldTagCaption(5) <> "" ? $this->locale->FldTagCaption(5) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(6):
					$this->locale->ViewValue = $this->locale->FldTagCaption(6) <> "" ? $this->locale->FldTagCaption(6) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(7):
					$this->locale->ViewValue = $this->locale->FldTagCaption(7) <> "" ? $this->locale->FldTagCaption(7) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(8):
					$this->locale->ViewValue = $this->locale->FldTagCaption(8) <> "" ? $this->locale->FldTagCaption(8) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(9):
					$this->locale->ViewValue = $this->locale->FldTagCaption(9) <> "" ? $this->locale->FldTagCaption(9) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(10):
					$this->locale->ViewValue = $this->locale->FldTagCaption(10) <> "" ? $this->locale->FldTagCaption(10) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(11):
					$this->locale->ViewValue = $this->locale->FldTagCaption(11) <> "" ? $this->locale->FldTagCaption(11) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(12):
					$this->locale->ViewValue = $this->locale->FldTagCaption(12) <> "" ? $this->locale->FldTagCaption(12) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(13):
					$this->locale->ViewValue = $this->locale->FldTagCaption(13) <> "" ? $this->locale->FldTagCaption(13) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(14):
					$this->locale->ViewValue = $this->locale->FldTagCaption(14) <> "" ? $this->locale->FldTagCaption(14) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(15):
					$this->locale->ViewValue = $this->locale->FldTagCaption(15) <> "" ? $this->locale->FldTagCaption(15) : $this->locale->CurrentValue;
					break;
				case $this->locale->FldTagValue(16):
					$this->locale->ViewValue = $this->locale->FldTagCaption(16) <> "" ? $this->locale->FldTagCaption(16) : $this->locale->CurrentValue;
					break;
				default:
					$this->locale->ViewValue = $this->locale->CurrentValue;
			}
		} else {
			$this->locale->ViewValue = NULL;
		}
		$this->locale->ViewCustomAttributes = "";

		// os
		$this->os->ViewValue = $this->os->CurrentValue;
		$this->os->ViewCustomAttributes = "";

		// recruiter
		$this->recruiter->ViewValue = $this->recruiter->CurrentValue;
		$this->recruiter->ViewCustomAttributes = "";

		// id
		$this->id->LinkCustomAttributes = "";
		$this->id->HrefValue = "";
		$this->id->TooltipValue = "";

		// username
		$this->username->LinkCustomAttributes = "";
		$this->username->HrefValue = "";
		$this->username->TooltipValue = "";

		// sha_pass_hash
		$this->sha_pass_hash->LinkCustomAttributes = "";
		$this->sha_pass_hash->HrefValue = "";
		$this->sha_pass_hash->TooltipValue = "";

		// sessionkey
		$this->sessionkey->LinkCustomAttributes = "";
		$this->sessionkey->HrefValue = "";
		$this->sessionkey->TooltipValue = "";

		// v
		$this->v->LinkCustomAttributes = "";
		$this->v->HrefValue = "";
		$this->v->TooltipValue = "";

		// s
		$this->s->LinkCustomAttributes = "";
		$this->s->HrefValue = "";
		$this->s->TooltipValue = "";

		// token_key
		$this->token_key->LinkCustomAttributes = "";
		$this->token_key->HrefValue = "";
		$this->token_key->TooltipValue = "";

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

		// failed_logins
		$this->failed_logins->LinkCustomAttributes = "";
		$this->failed_logins->HrefValue = "";
		$this->failed_logins->TooltipValue = "";

		// locked
		$this->locked->LinkCustomAttributes = "";
		$this->locked->HrefValue = "";
		$this->locked->TooltipValue = "";

		// lock_country
		$this->lock_country->LinkCustomAttributes = "";
		$this->lock_country->HrefValue = "";
		$this->lock_country->TooltipValue = "";

		// last_login
		$this->last_login->LinkCustomAttributes = "";
		$this->last_login->HrefValue = "";
		$this->last_login->TooltipValue = "";

		// online
		$this->online->LinkCustomAttributes = "";
		$this->online->HrefValue = "";
		$this->online->TooltipValue = "";

		// expansion
		$this->expansion->LinkCustomAttributes = "";
		$this->expansion->HrefValue = "";
		$this->expansion->TooltipValue = "";

		// mutetime
		$this->mutetime->LinkCustomAttributes = "";
		$this->mutetime->HrefValue = "";
		$this->mutetime->TooltipValue = "";

		// mutereason
		$this->mutereason->LinkCustomAttributes = "";
		$this->mutereason->HrefValue = "";
		$this->mutereason->TooltipValue = "";

		// muteby
		$this->muteby->LinkCustomAttributes = "";
		$this->muteby->HrefValue = "";
		$this->muteby->TooltipValue = "";

		// locale
		$this->locale->LinkCustomAttributes = "";
		$this->locale->HrefValue = "";
		$this->locale->TooltipValue = "";

		// os
		$this->os->LinkCustomAttributes = "";
		$this->os->HrefValue = "";
		$this->os->TooltipValue = "";

		// recruiter
		$this->recruiter->LinkCustomAttributes = "";
		$this->recruiter->HrefValue = "";
		$this->recruiter->TooltipValue = "";

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
				if ($this->username->Exportable) $Doc->ExportCaption($this->username);
				if ($this->_email->Exportable) $Doc->ExportCaption($this->_email);
				if ($this->reg_mail->Exportable) $Doc->ExportCaption($this->reg_mail);
				if ($this->joindate->Exportable) $Doc->ExportCaption($this->joindate);
				if ($this->last_ip->Exportable) $Doc->ExportCaption($this->last_ip);
				if ($this->failed_logins->Exportable) $Doc->ExportCaption($this->failed_logins);
				if ($this->locked->Exportable) $Doc->ExportCaption($this->locked);
				if ($this->lock_country->Exportable) $Doc->ExportCaption($this->lock_country);
				if ($this->last_login->Exportable) $Doc->ExportCaption($this->last_login);
				if ($this->online->Exportable) $Doc->ExportCaption($this->online);
				if ($this->expansion->Exportable) $Doc->ExportCaption($this->expansion);
				if ($this->mutetime->Exportable) $Doc->ExportCaption($this->mutetime);
				if ($this->mutereason->Exportable) $Doc->ExportCaption($this->mutereason);
				if ($this->muteby->Exportable) $Doc->ExportCaption($this->muteby);
				if ($this->locale->Exportable) $Doc->ExportCaption($this->locale);
				if ($this->os->Exportable) $Doc->ExportCaption($this->os);
				if ($this->recruiter->Exportable) $Doc->ExportCaption($this->recruiter);
			} else {
				if ($this->id->Exportable) $Doc->ExportCaption($this->id);
				if ($this->username->Exportable) $Doc->ExportCaption($this->username);
				if ($this->_email->Exportable) $Doc->ExportCaption($this->_email);
				if ($this->reg_mail->Exportable) $Doc->ExportCaption($this->reg_mail);
				if ($this->joindate->Exportable) $Doc->ExportCaption($this->joindate);
				if ($this->last_ip->Exportable) $Doc->ExportCaption($this->last_ip);
				if ($this->online->Exportable) $Doc->ExportCaption($this->online);
				if ($this->expansion->Exportable) $Doc->ExportCaption($this->expansion);
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
					if ($this->username->Exportable) $Doc->ExportField($this->username);
					if ($this->_email->Exportable) $Doc->ExportField($this->_email);
					if ($this->reg_mail->Exportable) $Doc->ExportField($this->reg_mail);
					if ($this->joindate->Exportable) $Doc->ExportField($this->joindate);
					if ($this->last_ip->Exportable) $Doc->ExportField($this->last_ip);
					if ($this->failed_logins->Exportable) $Doc->ExportField($this->failed_logins);
					if ($this->locked->Exportable) $Doc->ExportField($this->locked);
					if ($this->lock_country->Exportable) $Doc->ExportField($this->lock_country);
					if ($this->last_login->Exportable) $Doc->ExportField($this->last_login);
					if ($this->online->Exportable) $Doc->ExportField($this->online);
					if ($this->expansion->Exportable) $Doc->ExportField($this->expansion);
					if ($this->mutetime->Exportable) $Doc->ExportField($this->mutetime);
					if ($this->mutereason->Exportable) $Doc->ExportField($this->mutereason);
					if ($this->muteby->Exportable) $Doc->ExportField($this->muteby);
					if ($this->locale->Exportable) $Doc->ExportField($this->locale);
					if ($this->os->Exportable) $Doc->ExportField($this->os);
					if ($this->recruiter->Exportable) $Doc->ExportField($this->recruiter);
				} else {
					if ($this->id->Exportable) $Doc->ExportField($this->id);
					if ($this->username->Exportable) $Doc->ExportField($this->username);
					if ($this->_email->Exportable) $Doc->ExportField($this->_email);
					if ($this->reg_mail->Exportable) $Doc->ExportField($this->reg_mail);
					if ($this->joindate->Exportable) $Doc->ExportField($this->joindate);
					if ($this->last_ip->Exportable) $Doc->ExportField($this->last_ip);
					if ($this->online->Exportable) $Doc->ExportField($this->online);
					if ($this->expansion->Exportable) $Doc->ExportField($this->expansion);
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
