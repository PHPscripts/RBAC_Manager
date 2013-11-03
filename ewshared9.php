<?php

//
// Shared code for PHPMaker and PHP Report Maker
//

/**
 * Functions for converting encoding
 */

function ew_ConvertToUtf8($str) {
	return ew_Convert(EW_ENCODING, "UTF-8", $str);
}

function ew_ConvertFromUtf8($str) {
	return ew_Convert("UTF-8", EW_ENCODING, $str);
}

function ew_Convert($from, $to, $str) {
	if (is_string($str) && $from != "" && $to != "" && strtoupper($from) != strtoupper($to)) {
		if (function_exists("iconv")) {
			return iconv($from, $to, $str);
		} elseif (function_exists("mb_convert_encoding")) {
			return mb_convert_encoding($str, $to, $from);
		} else {
			return $str;
		}
	} else {
		return $str;
	}
}

// Convert rows (array) to JSON
function ew_ArrayToJson($ar, $offset = 0) {
	$arOut = array();
	$array = FALSE;
	if (count($ar) > 0) {
		$keys = array_keys($ar[0]);
		foreach ($keys as $key) {
			if (is_int($key)) {
				$array = TRUE;
				break;
			}
		}
	}
	foreach ($ar as $row) {
		$arwrk = array();
		foreach ($row as $key => $val) {
			if (($array && is_string($key)) || (!$array && is_int($key)))
				continue;
			$key = ($array) ? "" : "\"" . ew_JsEncode2($key) . "\":";
			if (is_null($val)) {
				$arwrk[] = $key . "null";
			} elseif (is_bool($val)) {
				$arwrk[] = $key . (($val) ? "true" : "false");
			} elseif (is_string($val)) {
				$arwrk[] = $key . "\"" . ew_JsEncode2($val) . "\"";
			} else {
				$arwrk[] = $key . $val;
			}
		}
		if ($array) { // array
			$arOut[] = "[" . implode(",", $arwrk) . "]";
		} else { // object
			$arOut[] = "{" . implode(",", $arwrk) . "}";
		}
	}
	if ($offset > 0)
		$arOut = array_slice($arOut, $offset);
	return "[" . implode(",", $arOut) . "]";
}

/**
 * Langauge class
 */

class cLanguage {
	var $LanguageId;
	var $Phrases = NULL;
	var $LanguageFolder = EW_LANGUAGE_FOLDER;

	// Constructor
	function __construct($langfolder = "", $langid = "") {
		global $gsLanguage;
		if ($langfolder <> "")
			$this->LanguageFolder = $langfolder;
		$this->LoadFileList(); // Set up file list
		if ($langid <> "") { // Set up language id
			$this->LanguageId = $langid;
			$_SESSION[EW_SESSION_LANGUAGE_ID] = $this->LanguageId;
		} elseif (@$_GET["language"] <> "") {
			$this->LanguageId = $_GET["language"];
			$_SESSION[EW_SESSION_LANGUAGE_ID] = $this->LanguageId;
		} elseif (@$_SESSION[EW_SESSION_LANGUAGE_ID] <> "") {
			$this->LanguageId = $_SESSION[EW_SESSION_LANGUAGE_ID];
		} else {
			$this->LanguageId = EW_LANGUAGE_DEFAULT_ID;
		}
		$gsLanguage = $this->LanguageId;
		$this->Load($this->LanguageId);
	}

	// Load language file list
	function LoadFileList() {
		global $EW_LANGUAGE_FILE;
		if (is_array($EW_LANGUAGE_FILE)) {
			$cnt = count($EW_LANGUAGE_FILE);
			for ($i = 0; $i < $cnt; $i++)
				$EW_LANGUAGE_FILE[$i][1] = $this->LoadFileDesc($this->LanguageFolder . $EW_LANGUAGE_FILE[$i][2]);
		}
	}

	// Load language file description
	function LoadFileDesc($File) {
		if (EW_USE_DOM_XML) {
			$this->Phrases = new cXMLDocument();
			if ($this->Phrases->Load($File))
				return $this->GetNodeAtt($this->Phrases->DocumentElement(), "desc");
		} else {
			$ar =	ew_Xml2Array(substr(file_get_contents($File), 0, 512)); // Just read the first part
			return (is_array($ar)) ? @$ar['ew-language']['attr']['desc'] : "";
		}
	}

	// Load language file
	function Load($id) {
		global $DEFAULT_DECIMAL_POINT, $DEFAULT_THOUSANDS_SEP, $DEFAULT_MON_DECIMAL_POINT, $DEFAULT_MON_THOUSANDS_SEP;
		global $DEFAULT_CURRENCY_SYMBOL, $DEFAULT_POSITIVE_SIGN, $DEFAULT_NEGATIVE_SIGN, $DEFAULT_FRAC_DIGITS;
		global $DEFAULT_P_CS_PRECEDES, $DEFAULT_P_SEP_BY_SPACE, $DEFAULT_N_CS_PRECEDES, $DEFAULT_N_SEP_BY_SPACE, $DEFAULT_P_SIGN_POSN, $DEFAULT_N_SIGN_POSN;
		global $DEFAULT_LOCALE;
		global $DEFAULT_TIME_ZONE;
		$sFileName = $this->GetFileName($id);
		if ($sFileName == "")
			$sFileName = $this->GetFileName(EW_LANGUAGE_DEFAULT_ID);
		if ($sFileName == "")
			return;
		if (EW_USE_DOM_XML) {
			$this->Phrases = new cXMLDocument();
			$this->Phrases->Load($sFileName);
		} else {
			if (is_array(@$_SESSION[EW_PROJECT_NAME . "_" . $sFileName])) {
				$this->Phrases = $_SESSION[EW_PROJECT_NAME . "_" . $sFileName];
			} else {
				$this->Phrases = ew_Xml2Array(file_get_contents($sFileName));
			}
		}

		// Set up locale / currency format for language
		$bUseSystemLocale = ($this->LocalePhrase("use_system_locale") == "1" ? TRUE : FALSE);
		$ar = array("p_cs_precedes", "p_sep_by_space", "n_cs_precedes", "n_sep_by_space");
		foreach ($DEFAULT_LOCALE as $key => $value) {
			if ($this->LocalePhrase($key) <> "")
				$DEFAULT_LOCALE[$key] = in_array($key, $ar) ? $this->LocalePhrase($key) == "1" : $this->LocalePhrase($key);
		}
		if ($bUseSystemLocale) {
			$langLocale = $this->LocalePhrase("locale");
			if ($langLocale <> "") {

				//$curLocale = @setlocale(LC_ALL, "0"); // Get current locale
				if (setlocale(LC_ALL, $langLocale) == TRUE) { // Set language locale
					extract(localeconv());
				}

				//@setlocale(LC_ALL, $curLocale); // Restore current locale
			} else {
				extract(localeconv());
			}
			if (!empty($decimal_point))
				$DEFAULT_DECIMAL_POINT = $decimal_point;
			$DEFAULT_THOUSANDS_SEP = $thousands_sep;
			if (!empty($mon_decimal_point))
				$DEFAULT_MON_DECIMAL_POINT = $mon_decimal_point;
			$DEFAULT_MON_THOUSANDS_SEP = $mon_thousands_sep;
			if (!empty($currency_symbol)) {
				if (EW_CHARSET == "utf-8") {
					if ($int_curr_symbol == "EUR" && ord($currency_symbol) == 128) {
						$currency_symbol = "\xe2\x82\xac";
					} elseif ($int_curr_symbol == "GBP" && ord($currency_symbol) == 163) {
						$currency_symbol = "\xc2\xa3";
					} elseif ($int_curr_symbol == "JPY" && ord($currency_symbol) == 92) {
						$currency_symbol = "\xc2\xa5";
					}
				}
				$DEFAULT_CURRENCY_SYMBOL = $currency_symbol;
			}
			if (!empty($positive_sign)) $DEFAULT_POSITIVE_SIGN = $positive_sign;
			if (!empty($negative_sign)) $DEFAULT_NEGATIVE_SIGN = $negative_sign;
			if (!empty($frac_digits) && $frac_digits <> CHAR_MAX) $DEFAULT_FRAC_DIGITS = $frac_digits;
			if (!empty($p_cs_precedes) && $p_cs_precedes <> CHAR_MAX) $DEFAULT_P_CS_PRECEDES = $p_cs_precedes;
			if (!empty($p_sep_by_space) && $p_sep_by_space <> CHAR_MAX) $DEFAULT_P_SEP_BY_SPACE = $p_sep_by_space;
			if (!empty($n_cs_precedes) && $n_cs_precedes <> CHAR_MAX) $DEFAULT_N_CS_PRECEDES = $n_cs_precedes;
			if (!empty($n_sep_by_space) && $n_sep_by_space <> CHAR_MAX) $DEFAULT_N_SEP_BY_SPACE = $n_sep_by_space;
			if (!empty($p_sign_posn) && $p_sign_posn <> CHAR_MAX) $DEFAULT_P_SIGN_POSN = $p_sign_posn;
			if (!empty($n_sign_posn) && $n_sign_posn <> CHAR_MAX) $DEFAULT_N_SIGN_POSN = $n_sign_posn;
		}

		/**
		 * Time zone (Note: Requires PHP 5 >= 5.1.0)
		 * Read http://www.php.net/date_default_timezone_set for details
		 * and http://www.php.net/timezones for supported time zones
		*/

		// Set up time zone from language file for multi-lanuage site
		if ($this->LocalePhrase("time_zone") <> "") $DEFAULT_TIME_ZONE = $this->LocalePhrase("time_zone");
		if (function_exists("date_default_timezone_set") && $DEFAULT_TIME_ZONE <> "")
			date_default_timezone_set($DEFAULT_TIME_ZONE);
	}

	// Get language file name
	function GetFileName($Id) {
		global $EW_LANGUAGE_FILE;
		if (is_array($EW_LANGUAGE_FILE)) {
			$cnt = count($EW_LANGUAGE_FILE);
			for ($i = 0; $i < $cnt; $i++)
				if ($EW_LANGUAGE_FILE[$i][0] == $Id) {
					return $this->LanguageFolder . $EW_LANGUAGE_FILE[$i][2];
				}
		}
		return "";
	}

	// Get node attribute
	function GetNodeAtt($Nodes, $Att) {
		$value = ($Nodes) ? $this->Phrases->GetAttribute($Nodes, $Att) : "";

		//return ew_ConvertFromUtf8($value);
		return $value;
	}

	// Set node attribute
	function SetNodeAtt($Nodes, $Att, $Value) {
		if ($Nodes)
			$this->Phrases->SetAttribute($Nodes, $Att, $Value);
	}

	// Get locale phrase
	function LocalePhrase($Id) {
		if (is_object($this->Phrases)) {
			return $this->GetNodeAtt($this->Phrases->SelectSingleNode("//locale/phrase[@id='" . strtolower($Id) . "']"), "value");
		} elseif (is_array($this->Phrases)) {
			return ew_ConvertFromUtf8(@$this->Phrases['ew-language']['locale']['phrase'][strtolower($Id)]['attr']['value']);
		}
	}

	// Set locale phrase
	function setLocalePhrase($Id, $Value) {
		if (is_object($this->Phrases)) {
			$this->SetNodeAtt($this->Phrases->SelectSingleNode("//locale/phrase[@id='" . strtolower($Id) . "']"), "value", $Value);
		} elseif (is_array($this->Phrases)) {
			$this->Phrases['ew-language']['locale']['phrase'][strtolower($Id)]['attr']['value'] = $Value;
		}
	}

	// Get phrase
	function Phrase($Id) {
		if (is_object($this->Phrases)) {
			return $this->GetNodeAtt($this->Phrases->SelectSingleNode("//global/phrase[@id='" . strtolower($Id) . "']"), "value");
		} elseif (is_array($this->Phrases)) {
			return ew_ConvertFromUtf8(@$this->Phrases['ew-language']['global']['phrase'][strtolower($Id)]['attr']['value']);
		}
	}

	// Set phrase
	function setPhrase($Id, $Value) {
		if (is_object($this->Phrases)) {
			$this->SetNodeAtt($this->Phrases->SelectSingleNode("//global/phrase[@id='" . strtolower($Id) . "']"), "value", $Value);
		} elseif (is_array($this->Phrases)) {
			$this->Phrases['ew-language']['global']['phrase'][strtolower($Id)]['attr']['value'] = $Value;
		}
	}

	// Get project phrase
	function ProjectPhrase($Id) {
		if (is_object($this->Phrases)) {
			return $this->GetNodeAtt($this->Phrases->SelectSingleNode("//project/phrase[@id='" . strtolower($Id) . "']"), "value");
		} elseif (is_array($this->Phrases)) {
			return ew_ConvertFromUtf8(@$this->Phrases['ew-language']['project']['phrase'][strtolower($Id)]['attr']['value']);
		}
	}

	// Set project phrase
	function setProjectPhrase($Id, $Value) {
		if (is_object($this->Phrases)) {
			$this->SetNodeAtt($this->Phrases->SelectSingleNode("//project/phrase[@id='" . strtolower($Id) . "']"), "value", $Value);
		} elseif (is_array($this->Phrases)) {
			$this->Phrases['ew-language']['project']['phrase'][strtolower($Id)]['attr']['value'] = $Value;
		}
	}

	// Get menu phrase
	function MenuPhrase($MenuId, $Id) {
		if (is_object($this->Phrases)) {
			return $this->GetNodeAtt($this->Phrases->SelectSingleNode("//project/menu[@id='" . $MenuId . "']/phrase[@id='" . strtolower($Id) . "']"), "value");
		} elseif (is_array($this->Phrases)) {
			return ew_ConvertFromUtf8(@$this->Phrases['ew-language']['project']['menu'][$MenuId]['phrase'][strtolower($Id)]['attr']['value']);
		}
	}

	// Set menu phrase
	function setMenuPhrase($MenuId, $Id, $Value) {
		if (is_object($this->Phrases)) {
			$this->SetNodeAtt($this->Phrases->SelectSingleNode("//project/menu[@id='" . $MenuId . "']/phrase[@id='" . strtolower($Id) . "']"), "value", $Value);
		} elseif (is_array($this->Phrases)) {
			$this->Phrases['ew-language']['project']['menu'][$MenuId]['phrase'][strtolower($Id)]['attr']['value'] = $Value;
		}
	}

	// Get table phrase
	function TablePhrase($TblVar, $Id) {
		if (is_object($this->Phrases)) {
			return $this->GetNodeAtt($this->Phrases->SelectSingleNode("//project/table[@id='" . strtolower($TblVar) . "']/phrase[@id='" . strtolower($Id) . "']"), "value");
		} elseif (is_array($this->Phrases)) {
			return ew_ConvertFromUtf8(@$this->Phrases['ew-language']['project']['table'][strtolower($TblVar)]['phrase'][strtolower($Id)]['attr']['value']);
		}
	}

	// Set table phrase
	function setTablePhrase($TblVar, $Id, $Value) {
		if (is_object($this->Phrases)) {
			$this->SetNodeAtt($this->Phrases->SelectSingleNode("//project/table[@id='" . strtolower($TblVar) . "']/phrase[@id='" . strtolower($Id) . "']"), "value", $Value);
		} elseif (is_array($this->Phrases)) {
			$this->Phrases['ew-language']['project']['table'][strtolower($TblVar)]['phrase'][strtolower($Id)]['attr']['value'] = $Value;
		}
	}

	// Get field phrase
	function FieldPhrase($TblVar, $FldVar, $Id) {
		if (is_object($this->Phrases)) {
			return $this->GetNodeAtt($this->Phrases->SelectSingleNode("//project/table[@id='" . strtolower($TblVar) . "']/field[@id='" . strtolower($FldVar) . "']/phrase[@id='" . strtolower($Id) . "']"), "value");
		} elseif (is_array($this->Phrases)) {
			return ew_ConvertFromUtf8(@$this->Phrases['ew-language']['project']['table'][strtolower($TblVar)]['field'][strtolower($FldVar)]['phrase'][strtolower($Id)]['attr']['value']);
		}
	}

	// Set field phrase
	function setFieldPhrase($TblVar, $FldVar, $Id, $Value) {
		if (is_object($this->Phrases)) {
			$this->SetNodeAtt($this->Phrases->SelectSingleNode("//project/table[@id='" . strtolower($TblVar) . "']/field[@id='" . strtolower($FldVar) . "']/phrase[@id='" . strtolower($Id) . "']"), "value", $Value);
		} elseif (is_array($this->Phrases)) {
			$this->Phrases['ew-language']['project']['table'][strtolower($TblVar)]['field'][strtolower($FldVar)]['phrase'][strtolower($Id)]['attr']['value'] = $Value;
		}
	}

	// Output XML as JSON
	function XmlToJSON($XPath) {
		$NodeList = $this->Phrases->SelectNodes($XPath);
		$Str = "{";
		foreach ($NodeList as $Node) {
			$Id = $this->GetNodeAtt($Node, "id");
			$Value = $this->GetNodeAtt($Node, "value");
			$Str .= "\"" . ew_JsEncode2($Id) . "\":\"" . ew_JsEncode2($Value) . "\",";
		}
		if (substr($Str, -1) == ",") $Str = substr($Str, 0, strlen($Str)-1);
		$Str .= "}";
		return $Str;
	}

	// Output array as JSON
	function ArrayToJSON($client) {
		$ar = @$this->Phrases['ew-language']['global']['phrase'];
		$Str = "{";
		if (is_array($ar)) {
			foreach ($ar as $id => $node) {
				$is_client = @$node['attr']['client'] == '1';
				$value = ew_ConvertFromUtf8(@$node['attr']['value']);
				if (!$client || ($client && $is_client))
					$Str .= "\"" . ew_JsEncode2($id) . "\":\"" . ew_JsEncode2($value) . "\",";
			}
		}
		if (substr($Str, -1) == ",") $Str = substr($Str, 0, strlen($Str)-1);
		$Str .= "}";
		return $Str;
	}

	// Output all phrases as JSON
	function AllToJSON() {
		if (is_object($this->Phrases)) {
			return "var ewLanguage = new ew_Language(" . $this->XmlToJSON("//global/phrase") . ");";
		} elseif (is_array($this->Phrases)) {
			return "var ewLanguage = new ew_Language(" . $this->ArrayToJSON(FALSE) . ");";
		}
	}

	// Output client phrases as JSON
	function ToJSON() {
		if (is_object($this->Phrases)) {
			return "var ewLanguage = new ew_Language(" . $this->XmlToJSON("//global/phrase[@client='1']") . ");";
		} elseif (is_array($this->Phrases)) {
			return "var ewLanguage = new ew_Language(" . $this->ArrayToJSON(TRUE) . ");";
		}
	}
}

/**
 * XML document class
 */

class cXMLDocument {
	var $Encoding = "utf-8";
	var $RootTagName;
	var $SubTblName = '';
	var $RowTagName;
	var $XmlDoc = FALSE;
	var $XmlTbl;
	var $XmlSubTbl;
	var $XmlRow;
	var $NullValue = 'NULL';

	function cXMLDocument($encoding = "") {
		if ($encoding <> "")
			$this->Encoding = $encoding;
		if ($this->Encoding <> "") {
			$this->XmlDoc = new DOMDocument("1.0", strval($this->Encoding));
		} else {
			$this->XmlDoc = new DOMDocument("1.0");
		}
	}

	function Load($filename) {
		$filepath = realpath($filename);
		return file_exists($filepath) ? $this->XmlDoc->load($filepath) : FALSE;
	}

	function &DocumentElement() {
		$de = $this->XmlDoc->documentElement;
		return $de;
	}

	function GetAttribute($element, $name) {
		return ($element) ? ew_ConvertFromUtf8($element->getAttribute($name)) : "";
	}

	function SetAttribute($element, $name, $value) {
		if ($element)
			$element->setAttribute($name, ew_ConvertToUtf8($value));
    }

	function SelectSingleNode($query) {
		$elements = $this->SelectNodes($query);
		return ($elements->length > 0) ? $elements->item(0) : NULL;
	}

	function SelectNodes($query) {
		$xpath = new DOMXPath($this->XmlDoc);
		return $xpath->query($query);
	}

	function AddRoot($roottagname = 'table') {
		$this->RootTagName = ew_XmlTagName($roottagname);
		$this->XmlTbl = $this->XmlDoc->createElement($this->RootTagName);
		$this->XmlDoc->appendChild($this->XmlTbl);
	}

	function AddRow($tabletagname = '', $rowtagname = 'row') {
		$this->RowTagName = ew_XmlTagName($rowtagname);
		$this->XmlRow = $this->XmlDoc->createElement($this->RowTagName);
		if ($tabletagname == '') {
			if ($this->XmlTbl)
				$this->XmlTbl->appendChild($this->XmlRow);
		} else {
			if ($this->SubTblName == '') {
				$this->SubTblName = ew_XmlTagName($tabletagname);
				$this->XmlSubTbl = $this->XmlDoc->createElement($this->SubTblName);
				$this->XmlTbl->appendChild($this->XmlSubTbl);
			}
			if ($this->XmlSubTbl)
				$this->XmlSubTbl->appendChild($this->XmlRow);
		}
	}

	function AddField($name, $value) {
		if (is_null($value)) $value = $this->NullValue;
		$value = ew_ConvertToUtf8($value); // Convert to UTF-8
		$xmlfld = $this->XmlDoc->createElement(ew_XmlTagName($name));
		$this->XmlRow->appendChild($xmlfld);
		$xmlfld->appendChild($this->XmlDoc->createTextNode($value));
	}

	function XML() {
		return $this->XmlDoc->saveXML();
	}
}

/**
 * Menu class
 */

class cMenu {
	var $Id;
	var $IsMobile = FALSE;
	var $IsRoot = FALSE;
	var $NoItem = NULL;
	var $ItemData = array();

	function __construct($id, $mobile = FALSE) {
		$this->Id = $id;
		$this->IsMobile = $mobile;
	}

	// Add a menu item
	function AddMenuItem($id, $text, $url, $parentid = -1, $src = "", $allowed = TRUE, $grouptitle = FALSE) {
		$item = new cMenuItem($id, $text, $url, $parentid, $src, $allowed, $grouptitle);

		// Fire MenuItem_Adding event
		if (function_exists("MenuItem_Adding") && !MenuItem_Adding($item))
			return;
		if ($item->ParentId < 0) {
			$this->AddItem($item);
		} else {
			if ($oParentMenu = &$this->FindItem($item->ParentId))
				$oParentMenu->AddItem($item, $this->IsMobile);
		}
	}

	// Add item to internal array
	function AddItem($item) {
		$this->ItemData[] = $item;
	}

	// Clear all menu items
	function Clear() {
		$this->ItemData = array();
	}

	// Find item
	function &FindItem($id) {
		$cnt = count($this->ItemData);
		for ($i = 0; $i < $cnt; $i++) {
			$item = &$this->ItemData[$i];
			if ($item->Id == $id) {
				return $item;
			} elseif (!is_null($item->SubMenu)) {
				if ($subitem = &$item->SubMenu->FindItem($id))
					return $subitem;
			}
		}
		$noitem = $this->NoItem;
		return $noitem;
	}

	// Get menu item count
	function Count() {
		return count($this->ItemData);
	}

	// Move item to position
	function MoveItem($Text, $Pos) {
		$cnt = count($this->ItemData);
		if ($Pos < 0) {
			$Pos = 0;
		} elseif ($Pos >= $cnt) {
			$Pos = $cnt - 1;
		}
		$item = NULL;
		$cnt = count($this->ItemData);
		for ($i = 0; $i < $cnt; $i++) {
			if ($this->ItemData[$i]->Text == $Text) {
				$item = $this->ItemData[$i];
				break;
			}
		}
		if ($item) {
			unset($this->ItemData[$i]);
			$this->ItemData = array_merge(array_slice($this->ItemData, 0, $Pos),
				array($item), array_slice($this->ItemData, $Pos));
		}
	}

	// Check if a menu item should be shown
	function RenderItem($item) {
		if (!is_null($item->SubMenu)) {
			foreach ($item->SubMenu->ItemData as $subitem) {
				if ($item->SubMenu->RenderItem($subitem))
					return TRUE;
			}
		}
		return ($item->Allowed && $item->Url <> "");
	}

	// Check if this menu should be rendered
	function RenderMenu() {
		foreach ($this->ItemData as $item) {
			if ($this->RenderItem($item))
				return TRUE;
		}
		return FALSE;
	}

	// Render the menu
	function Render($ret = FALSE) {
		if (function_exists("Menu_Rendering") && $this->IsRoot) Menu_Rendering($this);
		if (!$this->RenderMenu())
			return;
		if (!$this->IsMobile) {
			$str = "<div";
			if ($this->Id <> "") {
				if (is_numeric($this->Id)) {
					$str .= " id=\"menu_" . $this->Id . "\"";
				} else {
					$str .= " id=\"" . $this->Id . "\"";
				}
			}
			$str .= " class=\"" . (($this->IsRoot) ? EW_MENUBAR_CLASSNAME : EW_MENU_CLASSNAME) . "\">";
			$str .= "<div class=\"bd" . (($this->IsRoot) ? " first-of-type": "") . "\">\n";
		} else {
			$str = "";
		}
		$gopen = FALSE; // Group open status
		$gcnt = 0; // Group count
		$i = 0; // Menu item count
		$classfot = " class=\"first-of-type\"";
		foreach ($this->ItemData as $item) {
			if ($this->RenderItem($item)) {
				$i++;

				// Begin a group
				if ($i == 1 && !$item->GroupTitle) {
					$gcnt++;
					if (!$this->IsMobile)
						$str .= "<ul " . $classfot . ">\n";
					$gopen = TRUE;
				}
				$aclass = ($this->IsRoot) ? EW_MENUBAR_ITEM_LABEL_CLASSNAME : EW_MENU_ITEM_LABEL_CLASSNAME;
				$liclass = ($this->IsRoot) ? EW_MENUBAR_ITEM_CLASSNAME : EW_MENU_ITEM_CLASSNAME;
				if ($item->GroupTitle && EW_MENU_ITEM_CLASSNAME <> "") { // Group title
					$gcnt++;
					if ($i > 1 && $gopen) {
						if (!$this->IsMobile)
							$str .= "</ul>\n"; // End last group
						$gopen = FALSE;
					}

					// Begin a new group with title
					if (strval($item->Text) <> "") {
						if ($this->IsMobile)
							$str .= "<li data-role=\"list-divider\">" . $item->Text . "</li>\n";
						else
							$str .= "<h6" . (($gcnt == 1) ? $classfot : "") . ">" . $item->Text . "</h6>\n";
					}
					if (!$this->IsMobile)
						$str .= "<ul" . (($gcnt == 1) ? $classfot : "") . ">\n";
					$gopen = TRUE;
					if (!is_null($item->SubMenu)) {
						foreach ($item->SubMenu->ItemData as $subitem) {
							if ($this->RenderItem($subitem))
								$str .= $subitem->Render($aclass, $liclass, $this->IsMobile) . "\n"; // Create <LI>
						}
					}
					if (!$this->IsMobile)
						$str .= "</ul>\n"; // End the group
					$gopen = FALSE;
				} else { // Menu item
					if (!$gopen) { // Begin a group if no opened group
						$gcnt++;
						if (!$this->IsMobile)
							$str .= "<ul" . (($gcnt == 1) ? $classfot : "") . ">\n";
						$gopen = TRUE;
					}
					if ($this->IsRoot && $i == 1) // For horizontal menu
						$liclass .= " first-of-type";
					$str .= $item->Render($aclass, $liclass, $this->IsMobile) . "\n"; // Create <LI>
				}
			}
		}
		if ($gopen)
			if (!$this->IsMobile)
				$str .= "</ul>\n"; // End last group
		if ($this->IsMobile)
			$str = "<ul data-role=\"listview\" data-filter=\"true\" class=\"first-of-type\">" . $str . "</ul>\n";
		else
			$str .= "</div></div>\n";
		if ($ret) // Return as string
			return $str;
		echo $str; // Output
	}
}

// Menu item class
class cMenuItem {
	var $Id;
	var $Text;
	var $Url;
	var $ParentId; 
	var $SubMenu = NULL; // Data type = cMenu
	var $Source;
	var $Allowed = TRUE;
	var $Target;
	var $GroupTitle;

	// Constructor
	function __construct($id, $text, $url, $parentid = -1, $src = "", $allowed = TRUE, $grouptitle = FALSE) {
		$this->Id = $id;
		$this->Text = $text;
		$this->Url = $url;
		$this->ParentId = $parentid;
		$this->Source = $src;
		$this->Allowed = $allowed;
		$this->GroupTitle = $grouptitle;
	}

	// Add submenu item
	function AddItem($item, $mobile = FALSE) {
		if (is_null($this->SubMenu))
			$this->SubMenu = new cMenu($this->Id, $mobile);
		$this->SubMenu->AddItem($item);
	}

	// Render
	function Render($aclass = "", $liclass = "", $mobile = FALSE) {

		// Create <A>
		if ($mobile)
			$attrs = array("class" => $aclass, "rel" => "external", "href" => str_replace("#","?chart=",$this->Url), "target" => $this->Target);
		else
			$attrs = array("class" => $aclass, "href" => $this->Url, "target" => $this->Target);
		$innerhtml = ew_HtmlElement("a", $attrs, $this->Text);
		if (!is_null($this->SubMenu)) {
			if ($mobile && $this->Url <> "")
				$innerhtml .= $innerhtml;
			$innerhtml .= $this->SubMenu->Render(TRUE);
		}

		// Create <LI>
		return ew_HtmlElement("li", array("class" => $liclass), $innerhtml);
	}
}

// Menu Rendering event
function Menu_Rendering(&$Menu) {

	// Change menu items here
}

// MenuItem Adding event
function MenuItem_Adding(&$Item) {

	//var_dump($Item);
	// Return FALSE if menu item not allowed

	return TRUE;
}

// Create a DIV as container of encrypted SQL for synchronous query
function ew_CreateQuery($id, $sql) {
	echo ew_HtmlElement("div", array("id" => $id, "class" => "ewDisplayNone"), TEAencrypt($sql));
}

// Output SCRIPT tag
function ew_AddClientScript($src, $attrs = NULL) {
	$atts = array("type"=>"text/javascript", "src"=>$src);
	if (is_array($attrs))
		$atts = array_merge($atts, $attrs);
	echo ew_HtmlElement("script", $atts, "") . "\n";
}

// Output LINK tag
function ew_AddStylesheet($href, $attrs = NULL) {
	$atts = array("rel"=>"stylesheet", "type"=>"text/css", "href"=>$href);
	if (is_array($attrs))
		$atts = array_merge($atts, $attrs);
	echo ew_HtmlElement("link", $atts, "", FALSE) . "\n";
}

// Build HTML element
function ew_HtmlElement($tagname, $attrs, $innerhtml = "", $endtag = TRUE) {
	$html = "<" . $tagname;
	if (is_array($attrs)) {
		foreach ($attrs as $name => $attr) {
			if (strval($attr) <> "")
				$html .= " " . $name . "=\"" . ew_HtmlEncode($attr) . "\"";
		}
	}
	$html .= ">";
	if (strval($innerhtml) <> "")
		$html .= $innerhtml;
	if ($endtag)
		$html .= "</" . $tagname . ">";
	return $html;
}

// Encode html
function ew_HtmlEncode($exp) {
	return htmlspecialchars(strval($exp));
}

// XML tag name
function ew_XmlTagName($name) {
	if (!preg_match('/\A(?!XML)[a-z][\w0-9-]*/i', $name))
		$name = "_" . $name;
	return $name;
}

// Debug timer
class cTimer {
	var $StartTime;
	var $EndTime;

	function cTimer($start = TRUE) {
		if ($start)
			$this->Start();
	}

	function GetTime() {
		list($usec, $sec) = explode(" ", microtime());
		return ((float)$usec + (float)$sec);
	}

	// Get script start time
	function Start() {
		if (EW_DEBUG_ENABLED)
			$this->StartTime = $this->GetTime();
	}

	// display elapsed time (in seconds)
	function Stop() {
		if (EW_DEBUG_ENABLED)
			$this->EndTime = $this->GetTime();
		if (isset($this->EndTime) && isset($this->StartTime) &&
			$this->EndTime > $this->StartTime)
			echo '<p>Page processing time: ' . ($this->EndTime - $this->StartTime) . ' seconds</p>';
	}
}

// Convert XML to array
function ew_Xml2Array($contents) {
	if (!$contents) return array(); 
	if (!function_exists('xml_parser_create')) return FALSE;
	$get_attributes = 1; // Always get attributes. DO NOT CHANGE!

	// Get the XML Parser of PHP
	$parser = xml_parser_create();
	xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8"); // Always return in utf-8
	xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
	xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
	xml_parse_into_struct($parser, trim($contents), $xml_values);
	xml_parser_free($parser);
	if (!$xml_values) return;
	$xml_array = array();
	$parents = array();
	$opened_tags = array();
	$arr = array();
	$current = &$xml_array;
	$repeated_tag_index = array(); // Multiple tags with same name will be turned into an array
	foreach ($xml_values as $data) {
		unset($attributes, $value); // Remove existing values

		// Extract these variables into the foreach scope
		// tag(string), type(string), level(int), attributes(array)

		extract($data);
		$result = array();
		if (isset($value))
			$result['value'] = $value; // Put the value in a assoc array

		// Set the attributes
		if (isset($attributes) and $get_attributes) {
			foreach ($attributes as $attr => $val)
				$result['attr'][$attr] = $val; // Set all the attributes in a array called 'attr'
		} 

		// See tag status and do the needed
		if ($type == "open") { // The starting of the tag '<tag>'
			$parent[$level-1] = &$current;
			if (!is_array($current) || !in_array($tag, array_keys($current))) { // Insert New tag
				if ($tag <> 'ew-language' && @$result['attr']['id'] <> '') { // 
					$last_item_index = $result['attr']['id'];
					$current[$tag][$last_item_index] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 1;
					$current = &$current[$tag][$last_item_index];
				} else {
					$current[$tag] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 0;
					$current = &$current[$tag];
				}
			} else { // Another element with the same tag name
				if ($repeated_tag_index[$tag.'_'.$level] > 0) { // If there is a 0th element it is already an array
					if (@$result['attr']['id'] <> '') {
						$last_item_index = $result['attr']['id'];
					} else {
						$last_item_index = $repeated_tag_index[$tag.'_'.$level];
					}
					$current[$tag][$last_item_index] = $result;
					$repeated_tag_index[$tag.'_'.$level]++;
				} else { // Make the value an array if multiple tags with the same name appear together
					$temp = $current[$tag];
					$current[$tag] = array();
					if (@$temp['attr']['id'] <> '') {
						$current[$tag][$temp['attr']['id']] = $temp;
					} else {
						$current[$tag][] = $temp;
					}
					if (@$result['attr']['id'] <> '') {
						$last_item_index = $result['attr']['id'];
					} else {
						$last_item_index = 1;
					}
					$current[$tag][$last_item_index] = $result;
					$repeated_tag_index[$tag.'_'.$level] = 2;
				} 
				$current = &$current[$tag][$last_item_index];
			}
		} elseif ($type == "complete") { // Tags that ends in one line '<tag>'
			if (!isset($current[$tag])) { // New key
				$current[$tag] = array(); // Always use array for "complete" type
				if (@$result['attr']['id'] <> '') {
					$current[$tag][$result['attr']['id']] = $result;
				} else {
					$current[$tag][] = $result;
				}
				$repeated_tag_index[$tag.'_'.$level] = 1;
			} else { // Existing key
				if (@$result['attr']['id'] <> '') {
			  	$current[$tag][$result['attr']['id']] = $result;
				} else {
					$current[$tag][$repeated_tag_index[$tag.'_'.$level]] = $result;
				}
			  $repeated_tag_index[$tag.'_'.$level]++;
			}
		} elseif ($type == 'close') { // End of tag '</tag>'
			$current = &$parent[$level-1];
		}
	}
	return($xml_array);
}

// Encode value for double-quoted Javascript string
function ew_JsEncode2($val) {
	$val = strval($val);
	if (EW_IS_DOUBLE_BYTE)
		$val = ew_ConvertToUtf8($val);
	$val = str_replace("\\", "\\\\", $val);
	$val = str_replace("\"", "\\\"", $val);
	$val = str_replace("\t", "\\t", $val);
	$val = str_replace("\r", "\\r", $val);
	$val = str_replace("\n", "\\n", $val);
	if (EW_IS_DOUBLE_BYTE)
		$val = ew_ConvertFromUtf8($val);
	return $val;
}

// Encode value to single-quoted Javascript string for HTML attributes
function ew_JsEncode3($val) {
	$val = strval($val);
	if (EW_IS_DOUBLE_BYTE)
		$val = ew_ConvertToUtf8($val);
	$val = str_replace("\\", "\\\\", $val);
	$val = str_replace("'", "\\'", $val);
	$val = str_replace("\"", "&quot;", $val);
	if (EW_IS_DOUBLE_BYTE)
		$val = ew_ConvertFromUtf8($val);
	return $val;
}

// Convert array to JSON for HTML attributes
function ew_ArrayToJsonAttr($ar) {
	$Str = "{";
	foreach ($ar as $key => $val)
		$Str .= $key . ":'" . ew_JsEncode3($val) . "',";
	if (substr($Str, -1) == ",") $Str = substr($Str, 0, strlen($Str)-1);
	$Str .= "}";
	return $Str;
}

// Get current page name
function ew_CurrentPage() {
	return ew_GetPageName(ew_ScriptName());
}

// Get page name
function ew_GetPageName($url) {
	$PageName = "";
	if ($url <> "") {
		$PageName = $url;
		$p = strpos($PageName, "?");
		if ($p !== FALSE)
			$PageName = substr($PageName, 0, $p); // Remove QueryString
		$p = strrpos($PageName, "/");
		if ($p !== FALSE)
			$PageName = substr($PageName, $p+1); // Remove path
	}
	return $PageName;
}

// Adjust text for caption
function ew_BtnCaption($Caption) {
	$Min = 10;
	if (strlen($Caption) < $Min) {
		$Pad = abs(intval(($Min - strlen($Caption))/2*-1));
		$Caption = str_repeat(" ", $Pad) . $Caption . str_repeat(" ", $Pad);
	}
	return $Caption;
}

// Get current user levels as array of user level IDs
function CurrentUserLevels() {
	global $Security;
	if (isset($Security)) {
		return $Security->UserLevelID;
	} else {
		if (isset($_SESSION[EW_SESSION_USER_LEVEL_ID])) {
			return array($_SESSION[EW_SESSION_USER_LEVEL_ID]);
		} else {
			return array();
		}
	}
}

// Check if menu item is allowed for current user level
function AllowListMenu($TableName) {
	$userlevels = CurrentUserLevels(); // Get user level ID list as array
	if (IsLoggedIn()) {
		if (in_array("-1", $userlevels)) {
			return TRUE;
		} else {
			$priv = 0;
			if (is_array(@$_SESSION[EW_SESSION_AR_USER_LEVEL_PRIV])) {
				foreach ($_SESSION[EW_SESSION_AR_USER_LEVEL_PRIV] as $row) {
					if (strval($row[0]) == strval($TableName) &&
						in_array($row[1], $userlevels)) {
						$thispriv = $row[2];
						if (is_null($thispriv))
							$thispriv = 0;
						$thispriv = intval($thispriv);
						$priv = $priv | $thispriv;
					}
				}
			}
			return ($priv & EW_ALLOW_LIST);
		}
	} else {
		return FALSE;
	}
}

// Get script name
if (!function_exists("ew_ScriptName")) {

	function ew_ScriptName() {
		$sn = ew_ServerVar("PHP_SELF");
		if (empty($sn)) $sn = ew_ServerVar("SCRIPT_NAME");
		if (empty($sn)) $sn = ew_ServerVar("ORIG_PATH_INFO");
		if (empty($sn)) $sn = ew_ServerVar("ORIG_SCRIPT_NAME");
		if (empty($sn)) $sn = ew_ServerVar("REQUEST_URI");
		if (empty($sn)) $sn = ew_ServerVar("URL");
		if (empty($sn)) $sn = "UNKNOWN";
		return $sn;
	}
}

// Get server variable by name
function ew_ServerVar($Name) {
	$str = @$_SERVER[$Name];
	if (empty($str)) $str = @$_ENV[$Name];
	return $str;
}

// YUI files host
function ew_YuiHost() {

	// Use files online
	if (ew_IsHttps()) {
		return "https://ajax.googleapis.com/ajax/libs/yui/2.9.0/";
	} else {
		return "http://yui.yahooapis.com/2.9.0/";
	}
}

// jQuery files host
function ew_jQueryHost($mobile) {

	// Use files online
	if ($mobile)
		return "http" . (ew_IsHttps() ? "s" : "") . "://ajax.aspnetcdn.com/ajax/jquery.mobile/%v/";
	else
		return "http" . (ew_IsHttps() ? "s" : "") . "://ajax.aspnetcdn.com/ajax/jQuery/";
}

// jQuery version
function ew_jQueryFile($f) {
	$ver = "1.8.3"; // jQuery version
	$mver = "1.2.0"; // jquery.mobile version
	$m = strpos($f, "mobile") !== FALSE;
	$v = $m ? $mver : $ver;
	return str_replace("%v", $v, ew_jQueryHost($m) . $f);
}

// Check if HTTPS
function ew_IsHttps() {
	return (ew_ServerVar("HTTPS") <> "" && ew_ServerVar("HTTPS") <> "off");
}

// Get domain URL
function ew_DomainUrl() {
	$sUrl = "http";
	$bSSL = ew_IsHttps();
	$sPort = strval(ew_ServerVar("SERVER_PORT"));
	$defPort = ($bSSL) ? "443" : "80";
	$sPort = ($sPort == $defPort) ? "" : ":$sPort";
	$sUrl .= ($bSSL) ? "s" : "";
	$sUrl .= "://";
	$sUrl .= ew_ServerVar("SERVER_NAME") . $sPort;
	return $sUrl;
}

// Get full URL
function ew_FullUrl() {
	return ew_DomainUrl() . ew_ScriptName();
}

// Get current URL
function ew_CurrentUrl() {
	$s = ew_ScriptName();
	$q = ew_ServerVar("QUERY_STRING");
	if ($q <> "") $s .= "?" . $q;
	return $s;
}

// Convert to full URL
function ew_ConvertFullUrl($url) {
	if ($url == "") return "";
	$sUrl = ew_FullUrl();
	return substr($sUrl, 0, strrpos($sUrl, "/")+1) . $url;
}

// Get user agent type and version
function ew_UserAgent() {
	$ua = ew_ServerVar("HTTP_USER_AGENT");

//echo "ua: " . $ua . "<br>";
	$os = "";
	$agent = "";
	$mobile = "";
	$version = 0;

	// Detect OS
	if (preg_match("/windows|win32/i", $ua)) {
		$os = "windows";
	} elseif (preg_match("/macintosh/i", $ua)) {
		$os = "macintosh";
	} elseif (preg_match("/linux/i", $ua)) {
		$os = "linux";
	} elseif (preg_match("/rhino/i", $ua)) {
		$os = 'rhino';
	}

	// Modern KHTML browsers should qualify as Safari X-Grade
	$webkit = 0;
	if (preg_match("/KHTML/", $ua)) {
		$webkit = 1;
	}

	// Modern WebKit browsers are at least X-Grade
	if (preg_match("/AppleWebKit\/([^\s]*)/i", $ua, $m)) { 
		$webkit = $m[1];

		// Mobile browser check
		if (preg_match("/ Mobile\//i", $ua)) {
			$mobile = "Apple"; // iPhone or iPod Touch
			if (preg_match("/OS ([^\s]*)/i", $ua, $m)) {
				$os = "iOS";
				$version = str_replace("_", ".", $m[1]); // ios version
			}
			if (preg_match("/iPad/i", $ua, $m))
				$agent = "iPad";
			elseif (preg_match("/iPod/i", $ua, $m))
				$agent = "iPod";
			elseif (preg_match("/iPhone/i", $ua, $m))
				$agent = "iPhone";
		} else {
			if (preg_match("/NokiaN[^\/]*\//i", $ua, $m)) {

				// Nokia N-series, ex: NokiaN95
				$os = "nokia";
				$mobile = $m[0];
				$agent = $mobile;
			}
			if (preg_match("/webOS/i", $ua, $m)) {
				$os = "WebOS";
				$mobile = $os;
				$agent = $mobile;
				if (preg_match("/webOS\/([^\s]*);/i", $ua, $m)) {
					$agent = substr($m[0],0,-1);
					$version = $m[1]; // webos
				}
			}
			if (preg_match("/ Android/i", $ua , $m)) {
				$mobile = "Android";
				if (preg_match("/Android ([^\s]*);/i", $ua, $m)) {
					$agent = substr($m[0],0,-1);
					$version = $m[1]; // android
				}
			}
			if (preg_match("/BlackBerry ([^\s]*);/i", $ua , $m)) {
				$os = "BlackBerry";
				$mobile = $os;
				$agent = substr($m[0],0,-1);
				$version = $m[1];
			}
			if (preg_match("/PlayBook/i", $ua)) {
				$os = "BackBerry";
				$mobile = $os;
				$agent = "PlayBook";
				if (preg_match("/ RIM Tablet OS ([^\s]*);/i", $ua, $m)) {
					$os = "RIM Tablet OS";
					$version = $m[1];
				}
			}
		}
		if (preg_match("/Chrome\/([^\s]*)/i", $ua, $m)) {
			$agent = "Chrome";
			$version = $m[1]; // Chrome
		} elseif ($mobile == "" && preg_match("/Safari\//", $ua) && preg_match("/Version\/([0-9\.]+)/", $ua, $m)) {
			$agent = "Safari";
			$version = $m[1]; // Safari
		} elseif (preg_match("/AdobeAIR\/([^\s]*)/i", $ua, $m)) {
			$agent = "AdobeAIR";
			$version = $m[0]; // Adobe AIR 1.0 or better
		}
	}
	if ($webkit == 0) { // not webkit
		if (preg_match("/Opera[\s\/]([^\s]*)/i", $ua, $m)) {
			$agent = "Opera";
			$version = $m[1];
			if (preg_match("/Version\/([^\s]*)/i", $ua, $m)) {
				$version = $m[1]; // opera 10+
			}
			if (preg_match("/Opera Mini[^;]* /i", $ua, $m)) {
				$mobile = $m[0]; // ex: Opera Mini/2.0.4509/1316
			}
		} else { // not opera or webkit
			if (preg_match("/MSIE\s([^;]*)/i", $ua, $m)) {
				$agent = "MSIE";
				$version = $m[1];
			} elseif (preg_match("/Firefox\/([^\s]*)/i", $ua, $m)) {
				$agent = "Firefox";
				$version = $m[1];
			} else { // not opera, webkit, ie, firefox
				if (preg_match("/Gecko\/([^\s]*)/i", $ua, $m)) {
					$agent = "Gecko"; // Gecko detected, look for revision
					if (preg_match("/rv:([^\s\)]*)/i", $ua, $m)) {
						$version = $m[1];
					}
				}
			}
		}
	}
	$a[] = $os;
	$a[] = $agent;
	$a[] = $mobile;
	$ver = explode(".", $version);
	for ($i = 0; $i < count($ver); $i++)
		$a[] = $ver[$i];
	return $a;
}

// Include Mobile_Detect.php
include_once("Mobile_Detect.php");

// Check if mobile device
function ew_IsMobile() {
	global $MobileDetect, $EW_USE_MOBILE_MENU;
	if (!isset($MobileDetect))
		$MobileDetect = new Mobile_Detect;
	return $EW_USE_MOBILE_MENU && $MobileDetect->isMobile();
}
?>
