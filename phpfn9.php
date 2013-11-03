<?php

/**
 * PHPMaker Common classes and functions
 * (C) 2002-2012 e.World Technology Limited. All rights reserved.
*/

// Auto load class
function ew_AutoLoad($class) {
	if (substr($class, 0, 1) == "c") {
		$fn = "%cls%info.php";
		$file = str_replace("%cls%", substr($class, 1), $fn);
		if (file_exists($file))
			include $file;
	}
}
spl_autoload_register("ew_AutoLoad");
if (!function_exists("G")) {

	function &G($name) {
		return $GLOBALS[$name];
	}
}

// Get current project ID
function CurrentProjectID() {
	if (isset($GLOBALS["Page"]))
		return $GLOBALS["Page"]->ProjectID;
	return "{94C0E450-F9A8-47EE-A905-551040DB9277}";
}

// Get current page object
function &CurrentPage() {
	return $GLOBALS["Page"];
}

// Get current main table object
function &CurrentTable() {
	return $GLOBALS["Table"];
}

// Get current master table object
function &CurrentMasterTable() {
	$res = NULL;
	$tbl = &CurrentTable();
	if ($tbl && method_exists($tbl, "getCurrentMasterTable") && $tbl->getCurrentMasterTable() <> "")
		$res = $GLOBALS[$tbl->getCurrentMasterTable()];
	return $res;
}

// Get PHP errors
function ew_ErrorHandler($errno, $errstr, $errfile, $errline) {
	switch ($errno) {
		case E_USER_ERROR:
		case E_RECOVERABLE_ERROR:
			ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $errstr . ", file: " . $errfile . ", line: " . $errline);
			break;
		case E_WARNING:
		case E_USER_WARNING:
			ew_AddMessage($_SESSION[EW_SESSION_WARNING_MESSAGE], $errstr . ", file: " . $errfile . ", line: " . $errline);
			break;

		//case E_NOTICE: // Skip
		case E_USER_NOTICE:
		case E_STRICT:
		case E_DEPRECATED:
		case E_USER_DEPRECATED:
			ew_AddMessage($_SESSION[EW_SESSION_MESSAGE], $errstr . ", file: " . $errfile . ", line: " . $errline);
			break;
		default:
			break;
	}
	return FALSE; // Restore standard PHP error handler
}

/**
 * Export document classes
 */

// Get export document object
function &ew_ExportDocument(&$tbl, $style) {
	global $EW_EXPORT;
	$inst = NULL;
	$type = strtolower($tbl->Export);
	$class = $EW_EXPORT[$type];
	if (class_exists($class))
		$inst = new $class($tbl, $style);
	return $inst;
}

//
// Base class for export
//
class cExportBase {
	var $Table;
	var $Text;
	var $Line = "";
	var $Header = "";
	var $Style = "h"; // "v"(Vertical) or "h"(Horizontal)
	var $Horizontal = TRUE; // Horizontal
	var $RowCnt = 0;
	var $FldCnt = 0;

	// Constructor
	function __construct(&$tbl = NULL, $style = "") {
		$this->Table = $tbl;
		$this->SetStyle($style);
	}

	// Style
	function SetStyle($style) {
		if (strtolower($style) == "v" || strtolower($style) == "h")
			$this->Style = strtolower($style);		
		$this->Horizontal = ($this->Style <> "v");
	}

	// Field caption
	function ExportCaption(&$fld) {
		$this->FldCnt++;
		$this->ExportValueEx($fld, $fld->ExportCaption());
	}

	// Field value
	function ExportValue(&$fld) {
		$this->ExportValueEx($fld, $fld->ExportValue());
	}

	// Field aggregate
	function ExportAggregate(&$fld, $type) {
		$this->FldCnt++;
		if ($this->Horizontal) {
			global $Language;
			$val = "";
			if (in_array($type, array("TOTAL", "COUNT", "AVERAGE")))
				$val = $Language->Phrase($type) . ": " . $fld->ExportValue();
			$this->ExportValueEx($fld, $val);
		}
	}

	// Get meta tag for charset
	function CharsetMetaTag() {
		return "<meta http-equiv=\"Content-Type\" content=\"text/html; charset=" . EW_CHARSET . "\"/>\r\n";
	}

	// Table header
	function ExportTableHeader() {
		$this->Text .= "<table cellspacing=\"0\" class=\"ewExportTable\">";
	}

	// Export a value (caption, field value, or aggregate)
	function ExportValueEx(&$fld, $val, $usestyle = TRUE) {
		$this->Text .= "<td" . (($usestyle && EW_EXPORT_CSS_STYLES) ? $fld->CellStyles() : "") . ">";
		$this->Text .= strval($val);
		$this->Text .= "</td>";
	}

	// Begin a row
	function BeginExportRow($rowcnt = 0, $usestyle = TRUE) {
		$this->RowCnt++;
		$this->FldCnt = 0;
		if ($this->Horizontal) {
			if ($rowcnt == -1) {
				$this->Table->CssClass = "ewExportTableFooter";
			} elseif ($rowcnt == 0) {
				$this->Table->CssClass = "ewExportTableHeader";
			} else {
				$this->Table->CssClass = (($rowcnt % 2) == 1) ? "ewExportTableRow" : "ewExportTableAltRow";
			}
			$this->Text .= "<tr" . (($usestyle && EW_EXPORT_CSS_STYLES) ? $this->Table->RowStyles() : "") . ">";
		}
	}

	// End a row
	function EndExportRow() {
		if ($this->Horizontal)
			$this->Text .= "</tr>";
	}

	// Empty row
	function ExportEmptyRow() {
		$this->RowCnt++;
		$this->Text .= "<br>";
	}

	// Page break
	function ExportPageBreak() {
	}

	// Export a field
	function ExportField(&$fld) {
		$this->FldCnt++;
		$wrkExportValue = "";
		if ($fld->HrefValue2 <> "") { // Upload field
			if (!empty($fld->Upload->DbValue))
				$wrkExportValue = ew_ConvertFullUrl($fld->HrefValue2);
			if ($wrkExportValue <> "") {
				$attrs = array("href" => $wrkExportValue);
				$wrkExportValue = ew_HtmlElement("a", $attrs, $fld->FldCaption());
			}
		} else {
			$wrkExportValue = $fld->ExportValue($this->Table->Export, $fld->ExportOriginalValue);
		}
		if ($this->Horizontal) {
			$this->ExportValueEx($fld, $wrkExportValue);
		} else { // Vertical, export as a row
			$this->RowCnt++;
			$this->Text .= "<tr class=\"" . (($this->FldCnt % 2 == 1) ? "ewExportTableRow" : "ewExportTableAltRow") . "\">" .
				"<td class=\"ewTableHeader\">" . $fld->ExportCaption() . "</td>";
			$this->Text .= "<td" . ((EW_EXPORT_CSS_STYLES) ? $fld->CellStyles() : "") . ">" . $wrkExportValue . "</td></tr>";
		}
	}

	// Table Footer
	function ExportTableFooter() {
		$this->Text .= "</table>";
	}

	// Add HTML tags
	function ExportHeaderAndFooter() {
		$header = "<html><head>\r\n";
		$header .= $this->CharsetMetaTag();
		if (EW_EXPORT_CSS_STYLES && EW_PROJECT_STYLESHEET_FILENAME <> "")
			$header .= "<style type=\"text/css\">" . file_get_contents(EW_PROJECT_STYLESHEET_FILENAME) . "</style>\r\n";
		$header .= "</" . "head>\r\n<body>\r\n";
		$this->Text = $header . $this->Text . "</body></html>";
	}

	// Export
	function Export() {
		if (strtolower(EW_CHARSET) == "utf-8")
			echo "\xEF\xBB\xBF";
		echo $this->Text;
	}
}

//
// Class for export to email
// 
class cExportEmail extends cExportBase {

	// Export a field
	function ExportField(&$fld) {
		$this->FldCnt++;
		$ExportValue = $fld->ExportValue();
		if ($fld->FldViewTag == "IMAGE") {
			if ($fld->ImageResize && function_exists("ew_GdVersion") && ew_GdVersion() > 0) {
				$imagefn = $fld->GetTempImage();
				if ($imagefn <> "")
					$ExportValue = "<img src=\"" . $imagefn . "\" alt=\"\" style=\"border: 0;\">";
			} elseif ($ExportValue <> "") {
				$ExportValue = ew_ConvertFullUrl($ExportValue);
				$attrs = array("href" => $ExportValue);
				$ExportValue = ew_HtmlElement("a", $attrs, $fld->FldCaption());
			}
		}
		if ($this->Horizontal) {
			$this->ExportValueEx($fld, $ExportValue);
		} else { // Vertical, export as a row
			$this->RowCnt++;
			$this->Text .= "<tr class=\"" . (($this->FldCnt % 2 == 1) ? "ewExportTableRow" : "ewExportTableAltRow") . "\">" .
				"<td class=\"ewTableHeader\">" . $fld->ExportCaption() . "</td>";
			$this->Text .= "<td" . ((EW_EXPORT_CSS_STYLES) ? $fld->CellStyles() : "") . ">" . $ExportValue . "</td></tr>";
		}
	}

	// Export
	function Export() {
		echo $this->Text;
	}

	// Destructor
	function __destruct() {
		ew_DeleteTmpImages();
	}
}

//
// Class for export to HTML
// 
class cExportHtml extends cExportBase {

	// Same as base class
}

//
// Class for export to Word
// 
class cExportWord extends cExportBase {

	// Export
	function Export() {
		global $gsExportFile;
		header('Content-Type: application/vnd.ms-word' . ((EW_CHARSET <> "") ? ";charset=" . EW_CHARSET : ""));
		header('Content-Disposition: attachment; filename=' . $gsExportFile . '.doc');
		if (strtolower(EW_CHARSET) == "utf-8")
			echo "\xEF\xBB\xBF";
		echo $this->Text;
	}
}

//
// Class for export to Excel
// 
class cExportExcel extends cExportBase {

	// Export a value (caption, field value, or aggregate)
	function ExportValueEx(&$fld, $val, $usestyle = TRUE) {
		if ($fld->FldDataType == EW_DATATYPE_STRING && is_numeric($val))
			$val = "=\"" . strval($val) . "\"";
		$this->Text .= parent::ExportValueEx($fld, $val, $usestyle);
	}

	// Export
	function Export() {
		global $gsExportFile;
		header('Content-Type: application/vnd.ms-excel' . ((EW_CHARSET <> "") ? ";charset=" . EW_CHARSET : ""));
		header('Content-Disposition: attachment; filename=' . $gsExportFile . '.xls');
		if (strtolower(EW_CHARSET) == "utf-8")
			echo "\xEF\xBB\xBF";
		echo $this->Text;
	}
}

//
// Class for export to CSV
// 
class cExportCsv extends cExportBase {
	var $QuoteChar = "\"";

	// Style
	function ChangeStyle($style) {
		$this->Horizontal = TRUE;
	}

	// Table header
	function ExportTableHeader() {

		// Skip
	}

	// Export a value (caption, field value, or aggregate)
	function ExportValueEx(&$fld, $val, $usestyle = TRUE) {
		if ($this->Line <> "")
			$this->Line .= ",";
		$this->Line .= $this->QuoteChar . str_replace($this->QuoteChar, $this->QuoteChar . $this->QuoteChar, strval($val)) . $this->QuoteChar;
	}

	// Begin a row
	function BeginExportRow($rowcnt = 0, $usestyle = TRUE) {
		$this->Line = "";
	}

	// End a row
	function EndExportRow() {
		$this->Line .= "\r\n";
		$this->Text .= $this->Line;
	}

	// Empty line
	function ExportEmptyLine() {

		// Skip
	}

	// Export a field
	function ExportField(&$fld) {
		$this->ExportValue($fld);
	}

	// Table Footer
	function ExportTableFooter() {

		// Skip
	}

	// Add HTML tags
	function ExportHeaderAndFooter() {

		// Skip
	}

	// Export
	function Export() {
		global $gsExportFile;
		header('Content-Type: text/csv');
		header('Content-Disposition: attachment; filename=' . $gsExportFile . '.csv');
		if (strtolower(EW_CHARSET) == "utf-8")
			echo "\xEF\xBB\xBF";
		echo $this->Text;
	}
}

//
// Class for export to XML
//
class cExportXml extends cExportBase {
	var $XmlDoc;
	var $HasParent;

	// Constructor
	function __construct(&$tbl = NULL, $style = "") {
		parent::__construct($tbl, $style);
		$this->XmlDoc = new cXMLDocument(EW_XML_ENCODING);
	}

	// Style
	function SetStyle($style) {}

	// Field caption
	function ExportCaption(&$fld) {}

	// Field value
	function ExportValue(&$fld) {}

	// Field aggregate
	function ExportAggregate(&$fld, $type) {}

	// Get meta tag for charset
	function CharsetMetaTag() {}

	// Table header
	function ExportTableHeader() {
		$this->HasParent = is_object($this->XmlDoc->DocumentElement());
		if (!$this->HasParent)
			$this->XmlDoc->AddRoot($this->Table->TableVar);
	}

	// Export a value (caption, field value, or aggregate)
	function ExportValueEx(&$fld, $val, $usestyle = TRUE) {}

	// Begin a row
	function BeginExportRow($rowcnt = 0, $usestyle = TRUE) {
		if ($rowcnt <= 0)
			return; 
		if ($this->HasParent)
			$this->XmlDoc->AddRow($this->Table->TableVar);
		else
			$this->XmlDoc->AddRow();
	}

	// End a row
	function EndExportRow() {}

	// Empty row
	function ExportEmptyRow() {}

	// Page break
	function ExportPageBreak() {}

	// Export a field
	function ExportField(&$fld) {
		if ($fld->FldDataType <> EW_DATATYPE_BLOB) {
			$ExportValue = $fld->ExportValue();
			if (is_null($ExportValue))
				$ExportValue = "<Null>";
			$this->XmlDoc->AddField(substr($fld->FldVar, 2), $ExportValue);
		}
	}

	// Table Footer
	function ExportTableFooter() {}

	// Add HTML tags
	function ExportHeaderAndFooter() {}

	// Export
	function Export() {
		global $gsExportFile;
		ob_end_clean();
		header('Content-Type: text/xml');

		//header('Content-Disposition: attachment; filename=' . $gsExportFile . '.xml');
		echo $this->XmlDoc->XML();
	}
}

//
// Class for export to PDF
//
class cExportPdf extends cExportBase {

	// Table header
	function ExportTableHeader() {
		$this->Text .= "<table cellspacing=\"0\" class=\"ewTablePdf ewTablePdfBorder\">\r\n";
	}

	// Export a value (caption, field value, or aggregate)
	function ExportValueEx(&$fld, $val, $usestyle = TRUE) {
		$wrkval = strval($val);
		$wrkval = "<td" . (($usestyle && EW_EXPORT_CSS_STYLES) ? $fld->CellStyles() : "") . ">" . $wrkval . "</td>\r\n";
		$this->Line .= $wrkval;
		$this->Text .= $wrkval;
	}

	// Begin a row
	function BeginExportRow($rowcnt = 0, $usestyle = TRUE) {
		$this->FldCnt = 0;
		if ($this->Horizontal) {
			if ($rowcnt == -1)
				$this->Table->CssClass = "ewTablePdfFooter";
			elseif ($rowcnt == 0)
				$this->Table->CssClass = "ewTablePdfHeader";
			else
				$this->Table->CssClass = (($rowcnt % 2) == 1) ? "ewTableRow" : "ewTableAltRow";
			$this->Line = "<tr" . (($usestyle && EW_EXPORT_CSS_STYLES) ? $this->Table->RowStyles() : "") . ">";
			$this->Text .= $this->Line;
		}
	}

	// End a row
	function EndExportRow() {
		if ($this->Horizontal) {
			$this->Line .= "</tr>";
			$this->Text .= "</tr>";
			$this->Header = $this->Line;
		}
	}

	// Page break
	function ExportPageBreak() {
		if ($this->Horizontal) {
			$this->Text .= "</table>\r\n"; // end current table
			$this->Text .= "<p style=\"page-break-after:always;\">\r\n"; // page break
			$this->Text .= "<table class=\"ewTablePdf ewTablePdfBorder\">\r\n"; // new page header
			$this->Text .= $this->Header;
		}
	}

	// Export a field
	function ExportField(&$fld) {
		$ExportValue = $fld->ExportValue();
		if ($fld->FldViewTag == "IMAGE")  {
			$imagefn = $fld->GetTempImage();
			if ($imagefn <> "")
				$ExportValue = "<img src=\"" . $imagefn . "\" alt=\"\" style=\"border: 0;\">";
		} else {
			$ExportValue = str_replace("<br>", "\r\n", $ExportValue);
			$ExportValue = strip_tags($ExportValue);
			$ExportValue = str_replace("\r\n", "<br>", $ExportValue);
		}
		if ($this->Horizontal) {
			$this->ExportValueEx($fld, $ExportValue);
		} else { // Vertical, export as a row
			$this->FldCnt++;
			$fld->CellCssClass = ($this->FldCnt % 2 == 1) ? "ewTableRow" : "ewTableAltRow";
			$this->Text .= "<tr><td class=\"ewTablePdfHeader\">" . $fld->ExportCaption() . "</td>";
			$this->Text .= "<td" . ((EW_EXPORT_CSS_STYLES) ? $fld->CellStyles() : "") . ">" .
				$ExportValue . "</td></tr>";
		}
	}

	// Add HTML tags
	function ExportHeaderAndFooter() {
		$header = "<html><head>\r\n";
		$header .= $this->CharsetMetaTag();
		if (EW_EXPORT_CSS_STYLES && EW_PDF_STYLESHEET_FILENAME <> "")
			$header .= "<style type=\"text/css\">" . file_get_contents(EW_PDF_STYLESHEET_FILENAME) . "</style>\r\n";
		$header .= "</" . "head>\r\n<body>\r\n";
		$this->Text = $header . $this->Text . "</body></html>";
	}

	// Export
	function Export() {
		global $gsExportFile;
		include_once "dompdf060b3/dompdf_config.inc.php";
		@ini_set("memory_limit", EW_PDF_MEMORY_LIMIT);
		set_time_limit(EW_PDF_TIME_LIMIT);
		$dompdf = new DOMPDF();
		$dompdf->load_html($this->Text);
		$dompdf->set_paper($this->Table->ExportPageSize, $this->Table->ExportPageOrientation);
		$dompdf->render();
		ob_end_clean();
		$dompdf->stream($gsExportFile . ".pdf", array("Attachment" => 1)); // 0 to open in browser, 1 to download
	}

	// Destructor
	function __destruct() {
		ew_DeleteTmpImages();
	}
}

/**
 * Email class
 */

class cEmail {

	// Class properties
	var $Sender = ""; // Sender
	var $Recipient = ""; // Recipient
	var $Cc = ""; // Cc
	var $Bcc = ""; // Bcc
	var $Subject = ""; // Subject
	var $Format = ""; // Format
	var $Content = ""; // Content
	var $AttachmentContent = ""; // Attachement content
	var $AttachmentFileName = ""; // Attachment file name
	var $EmbeddedImages = array(); // Embedded image
	var $Charset = ""; // Charset
	var $SendErrDescription; // Send error description
	var $SmtpSecure = EW_SMTP_SECURE_OPTION; // Send secure option

	// Method to load email from template
	function Load($fn) {
		$fn = ew_ScriptFolder() . EW_PATH_DELIMITER . $fn;
		$sWrk = file_get_contents($fn); // Load text file content
		if (substr($sWrk, 0, 3) == "\xEF\xBB\xBF") // utf-8 BOM
			$sWrk = substr($sWrk, 3);
		if ($sWrk <> "") {

			// Locate Header & Mail Content
			if (EW_IS_WINDOWS) {
				$i = strpos($sWrk, "\r\n\r\n");
			} else {
				$i = strpos($sWrk, "\n\n");
				if ($i === FALSE) $i = strpos($sWrk, "\r\n\r\n");
			}
			if ($i > 0) {
				$sHeader = substr($sWrk, 0, $i);
				$this->Content = trim(substr($sWrk, $i, strlen($sWrk)));
				if (EW_IS_WINDOWS) {
					$arrHeader = explode("\r\n", $sHeader);
				} else {
					$arrHeader = explode("\n", $sHeader);
				}
				$cnt = count($arrHeader);
				for ($j = 0; $j < $cnt; $j++) {
					$i = strpos($arrHeader[$j], ":");
					if ($i > 0) {
						$sName = trim(substr($arrHeader[$j], 0, $i));
						$sValue = trim(substr($arrHeader[$j], $i+1, strlen($arrHeader[$j])));
						switch (strtolower($sName))
						{
							case "subject":
								$this->Subject = $sValue;
								break;
							case "from":
								$this->Sender = $sValue;
								break;
							case "to":
								$this->Recipient = $sValue;
								break;
							case "cc":
								$this->Cc = $sValue;
								break;
							case "bcc":
								$this->Bcc = $sValue;
								break;
							case "format":
								$this->Format = $sValue;
								break;
						}
					}
				}
			}
		}
	}

	// Method to replace sender
	function ReplaceSender($ASender) {
		$this->Sender = str_replace('<!--$From-->', $ASender, $this->Sender);
	}

	// Method to replace recipient
	function ReplaceRecipient($ARecipient) {
		$this->Recipient = str_replace('<!--$To-->', $ARecipient, $this->Recipient);
	}

	// Method to add Cc email
	function AddCc($ACc) {
		if ($ACc <> "") {
			if ($this->Cc <> "") $this->Cc .= ";";
			$this->Cc .= $ACc;
		}
	}

	// Method to add Bcc email
	function AddBcc($ABcc) {
		if ($ABcc <> "")  {
			if ($this->Bcc <> "") $this->Bcc .= ";";
			$this->Bcc .= $ABcc;
		}
	}

	// Method to replace subject
	function ReplaceSubject($ASubject) {
		$this->Subject = str_replace('<!--$Subject-->', $ASubject, $this->Subject);
	}

	// Method to replace content
	function ReplaceContent($Find, $ReplaceWith) {
		$this->Content = str_replace($Find, $ReplaceWith, $this->Content);
	}

	// Method to add embedded image
	function AddEmbeddedImage($image) {
		if ($image <> "")
			$this->EmbeddedImages[] = $image;
	}

	// Method to send email
	function Send() {
		global $gsEmailErrDesc;
		$result = ew_SendEmail($this->Sender, $this->Recipient, $this->Cc, $this->Bcc,
			$this->Subject, $this->Content, $this->Format, $this->Charset, $this->SmtpSecure,
			$this->AttachmentFileName, $this->AttachmentContent, $this->EmbeddedImages);
		$this->SendErrDescription = $gsEmailErrDesc;
		return $result;
	}
}

/**
 * Pager item class
 */

class cPagerItem {
	var $Start;
	var $Text;
	var $Enabled;
}

/**
 * Numeric pager class
 */

class cNumericPager {
	var $Items = array();
	var $Count, $FromIndex, $ToIndex, $RecordCount, $PageSize, $Range;
	var $FirstButton, $PrevButton, $NextButton, $LastButton;
	var $ButtonCount = 0;
	var $Visible = TRUE;

	function __construct($StartRec, $DisplayRecs, $TotalRecs, $RecRange)
	{
		$this->FirstButton = new cPagerItem;
		$this->PrevButton = new cPagerItem;
		$this->NextButton = new cPagerItem;
		$this->LastButton = new cPagerItem;
		$this->FromIndex = intval($StartRec);
		$this->PageSize = intval($DisplayRecs);
		$this->RecordCount = intval($TotalRecs);
		$this->Range = intval($RecRange);
		if ($this->PageSize == 0) return;
		if ($this->FromIndex > $this->RecordCount)
			$this->FromIndex = $this->RecordCount;
		$this->ToIndex = $this->FromIndex + $this->PageSize - 1;
		if ($this->ToIndex > $this->RecordCount)
			$this->ToIndex = $this->RecordCount;

		// setup
		$this->SetupNumericPager();

		// update button count
		if ($this->FirstButton->Enabled) $this->ButtonCount++;
		if ($this->PrevButton->Enabled) $this->ButtonCount++;
		if ($this->NextButton->Enabled) $this->ButtonCount++;
		if ($this->LastButton->Enabled) $this->ButtonCount++;
		$this->ButtonCount += count($this->Items);
  }

	// Add pager item
	function AddPagerItem($StartIndex, $Text, $Enabled)
	{
		$Item = new cPagerItem;
		$Item->Start = $StartIndex;
		$Item->Text = $Text;
		$Item->Enabled = $Enabled;
		$this->Items[] = $Item;
	}

	// Setup pager items
	function SetupNumericPager()
	{
		if ($this->RecordCount > $this->PageSize) {
			$Eof = ($this->RecordCount < ($this->FromIndex + $this->PageSize));
			$HasPrev = ($this->FromIndex > 1);

			// First Button
			$TempIndex = 1;
			$this->FirstButton->Start = $TempIndex;
			$this->FirstButton->Enabled = ($this->FromIndex > $TempIndex);

			// Prev Button
			$TempIndex = $this->FromIndex - $this->PageSize;
			if ($TempIndex < 1) $TempIndex = 1;
			$this->PrevButton->Start = $TempIndex;
			$this->PrevButton->Enabled = $HasPrev;

			// Page links
			if ($HasPrev || !$Eof) {
				$x = 1;
				$y = 1;
				$dx1 = intval(($this->FromIndex-1)/($this->PageSize*$this->Range))*$this->PageSize*$this->Range + 1;
				$dy1 = intval(($this->FromIndex-1)/($this->PageSize*$this->Range))*$this->Range + 1;
				if (($dx1+$this->PageSize*$this->Range-1) > $this->RecordCount) {
					$dx2 = intval($this->RecordCount/$this->PageSize)*$this->PageSize + 1;
					$dy2 = intval($this->RecordCount/$this->PageSize) + 1;
				} else {
					$dx2 = $dx1 + $this->PageSize*$this->Range - 1;
					$dy2 = $dy1 + $this->Range - 1;
				}
				while ($x <= $this->RecordCount) {
					if ($x >= $dx1 && $x <= $dx2) {
						$this->AddPagerItem($x, $y, $this->FromIndex<>$x);
						$x += $this->PageSize;
						$y++;
					} elseif ($x >= ($dx1-$this->PageSize*$this->Range) && $x <= ($dx2+$this->PageSize*$this->Range)) {
						if ($x+$this->Range*$this->PageSize < $this->RecordCount) {
							$this->AddPagerItem($x, $y . "-" . ($y+$this->Range-1), TRUE);
						} else {
							$ny = intval(($this->RecordCount-1)/$this->PageSize) + 1;
							if ($ny == $y) {
								$this->AddPagerItem($x, $y, TRUE);
							} else {
								$this->AddPagerItem($x, $y . "-" . $ny, TRUE);
							}
						}
						$x += $this->Range*$this->PageSize;
						$y += $this->Range;
					} else {
						$x += $this->Range*$this->PageSize;
						$y += $this->Range;
					}
				}
			}

			// Next Button
			$TempIndex = $this->FromIndex + $this->PageSize;
			$this->NextButton->Start = $TempIndex;
			$this->NextButton->Enabled = !$Eof;

			// Last Button
			$TempIndex = intval(($this->RecordCount-1)/$this->PageSize)*$this->PageSize + 1;
			$this->LastButton->Start = $TempIndex;
			$this->LastButton->Enabled = ($this->FromIndex < $TempIndex);
		}
	}
}

/**
 * PrevNext pager class
 */

class cPrevNextPager {
	var $FirstButton, $PrevButton, $NextButton, $LastButton;
	var $CurrentPage, $PageCount, $FromIndex, $ToIndex, $RecordCount;
	var $Visible = TRUE;

	function __construct($StartRec, $DisplayRecs, $TotalRecs)
	{
		$this->FirstButton = new cPagerItem;
		$this->PrevButton = new cPagerItem;
		$this->NextButton = new cPagerItem;
		$this->LastButton = new cPagerItem;
		$this->FromIndex = intval($StartRec);
		$this->PageSize = intval($DisplayRecs);
		$this->RecordCount = intval($TotalRecs);
		if ($this->PageSize == 0) return;
		$this->CurrentPage = intval(($this->FromIndex-1)/$this->PageSize) + 1;
		$this->PageCount = intval(($this->RecordCount-1)/$this->PageSize) + 1;
		if ($this->FromIndex > $this->RecordCount)
			$this->FromIndex = $this->RecordCount;
		$this->ToIndex = $this->FromIndex + $this->PageSize - 1;
		if ($this->ToIndex > $this->RecordCount)
			$this->ToIndex = $this->RecordCount;

		// First Button
		$TempIndex = 1;
		$this->FirstButton->Start = $TempIndex;
		$this->FirstButton->Enabled = ($TempIndex <> $this->FromIndex);

		// Prev Button
		$TempIndex = $this->FromIndex - $this->PageSize;
		if ($TempIndex < 1) $TempIndex = 1;
		$this->PrevButton->Start = $TempIndex;
		$this->PrevButton->Enabled = ($TempIndex <> $this->FromIndex);

		// Next Button
		$TempIndex = $this->FromIndex + $this->PageSize;
		if ($TempIndex > $this->RecordCount)
			$TempIndex = $this->FromIndex;
		$this->NextButton->Start = $TempIndex;
		$this->NextButton->Enabled = ($TempIndex <> $this->FromIndex);

		// Last Button
		$TempIndex = intval(($this->RecordCount-1)/$this->PageSize)*$this->PageSize + 1;
		$this->LastButton->Start = $TempIndex;
		$this->LastButton->Enabled = ($TempIndex <> $this->FromIndex);
  }
}

/**
 * Table classes
 */

// Common class for table and report
class cTableBase {
	var $TableVar;
	var $TableName;
	var $TableType;
	var $Visible = TRUE;
	var $fields = array();
	var $UseTokenInUrl = EW_USE_TOKEN_IN_URL;
	var $Export; // Export
	var $ExportAll;
	var $ExportPageBreakCount; // Page break per every n record (PDF only)
	var $ExportPageOrientation; // Page orientation (PDF only)
	var $ExportPageSize; // Page size (PDF only)
	var $SendEmail; // Send email
	var $TableCustomInnerHtml; // Custom inner HTML
	var $BasicSearch; // Basic search
	var $CurrentFilter; // Current filter
	var $CurrentOrder; // Current order
	var $CurrentOrderType; // Current order type
	var $RowType; // Row type
	var $CssClass; // CSS class
	var $CssStyle; // CSS style
	var $RowAttrs = array(); // Row custom attributes
	var $CurrentAction; // Current action
	var $LastAction; // Last action
	var $UserIDAllowSecurity = 0; // User ID Allow

	// Reset attributes for table object
	function ResetAttrs() {
		$this->CssClass = "";
		$this->CssStyle = "";
    	$this->RowAttrs = array();
		foreach ($this->fields as $fld) {
			$fld->ResetAttrs();
		}
	}

	// Setup field titles
	function SetupFieldTitles() {
		foreach ($this->fields as &$fld) {
			if (strval($fld->FldTitle()) <> "") {
				$fld->EditAttrs["onmouseover"] = "ew_ShowTitle(this, '" . ew_JsEncode3($fld->FldTitle()) . "');";
				$fld->EditAttrs["onmouseout"] = "ew_HideTooltip();";
			}
		}
	}
	var $TableFilter = "";

	// Get field values
	function GetFieldValues($propertyname) {
		$values = array();
		foreach ($this->fields as $fldname => $fld)
			$values[$fldname] = &$fld->$propertyname;
		return $values;
	}
	var $TableCaption = "";

	// Set table caption
	function setTableCaption($v) {
		$this->TableCaption = $v;
	}

	// Table caption
	function TableCaption() {
		global $Language;
		if ($this->TableCaption <> "")
			return $this->TableCaption;
		else
			return $Language->TablePhrase($this->TableVar, "TblCaption");
	}
	var $PgCaption = array();

	// Set page caption
	function setPageCaption($Page, $v) {
		$this->PgCaption[$Page] = $v;
	}

	// Page caption
	function PageCaption($Page) {
		global $Language;
		$Caption = @$this->PgCaption[$Page];
		if ($Caption <> "") {
			return $Caption;
		} else {
			$Caption = $Language->TablePhrase($this->TableVar, "TblPageCaption" . $Page);
			if ($Caption == "") $Caption = "Page " . $Page;
			return $Caption;
		}
	}

	// Add URL parameter
	function UrlParm($parm = "") {
		$UrlParm = ($this->UseTokenInUrl) ? "t=" . $this->TablVar : "";
		if ($parm <> "") {
			if ($UrlParm <> "")
				$UrlParm .= "&";
			$UrlParm .= $parm;
		}
		return $UrlParm;
	}

	// Row styles
	function RowStyles() {
		$sAtt = "";
		$sStyle = trim($this->CssStyle);
		if (@$this->RowAttrs["style"] <> "")
			$sStyle .= " " . $this->RowAttrs["style"];
		$sClass = trim($this->CssClass);
		if (@$this->RowAttrs["class"] <> "")
			$sClass .= " " . $this->RowAttrs["class"];
		if (trim($sStyle) <> "")
			$sAtt .= " style=\"" . trim($sStyle) . "\"";
		if (trim($sClass) <> "")
			$sAtt .= " class=\"" . trim($sClass) . "\"";
		return $sAtt;
	}

	// Row attributes
	function RowAttributes() {
		$sAtt = $this->RowStyles();
		if ($this->Export == "") {
			foreach ($this->RowAttrs as $k => $v) {
				if ($k <> "class" && $k <> "style" && trim($v) <> "")
					$sAtt .= " " . $k . "=\"" . trim($v) . "\"";
			}
		}
		return $sAtt;
	}

	// Field object by name
	function fields($fldname) {
		return $this->fields[$fldname];
	}
}

// class for table
class cTable extends cTableBase {
	var $CurrentMode = ""; // Current mode
	var $UpdateConflict; // Update conflict
	var $EventName; // Event name
	var $EventCancelled; // Event cancelled
	var $CancelMessage; // Cancel message
	var $AllowAddDeleteRow = TRUE; // Allow add/delete row
	var $ValidateKey = TRUE; // Validate key
	var $DetailAdd; // Allow detail add
	var $DetailEdit; // Allow detail edit
	var $GridAddRowCount;

	// Check current action
	// - Add
	function IsAdd() {
		return $this->CurrentAction == "add";
	}

	// - Copy
	function IsCopy() {
		return $this->CurrentAction == "copy" || $this->CurrentAction == "C";
	}

	// - Edit
	function IsEdit() {
		return $this->CurrentAction == "edit";
	}

	// - Delete
	function IsDelete() {
		return $this->CurrentAction == "D";
	}

	// - Confirm
	function IsConfirm() {
		return $this->CurrentAction == "F";
	}

	// - Overwrite
	function IsOverwrite() {
		return $this->CurrentAction == "overwrite";
	}

	// - Cancel
	function IsCancel() {
		return $this->CurrentAction == "cancel";
	}

	// - Grid add
	function IsGridAdd() {
		return $this->CurrentAction == "gridadd";
	}

	// - Grid edit
	function IsGridEdit() {
		return $this->CurrentAction == "gridedit";
	}

	// - Add/Copy/Edit/GridAdd/GridEdit
	function IsAddOrEdit() {
		return $this->IsAdd() || $this->IsCopy() || $this->IsEdit() || $this->IsGridAdd() || $this->IsGridEdit();
	}

	// - Insert
	function IsInsert() {
		return $this->CurrentAction == "insert" || $this->CurrentAction == "A";
	}

	// - Update
	function IsUpdate() {
		return $this->CurrentAction == "update" || $this->CurrentAction == "U";
	}

	// - Grid update
	function IsGridUpdate() {
		return $this->CurrentAction == "gridupdate";
	}

	// - Grid insert
	function IsGridInsert() {
		return $this->CurrentAction == "gridinsert";
	}

	// - Grid overwrite
	function IsGridOverwrite() {
		return $this->CurrentAction == "gridoverwrite";
	}

	// Check last action
	// - Cancelled
	function IsCanceled() {
		return $this->LastAction == "cancel" && $this->CurrentAction == "";
	}

	// - Inline inserted
	function IsInlineInserted() {
		return $this->LastAction == "insert" && $this->CurrentAction == "";
	}

	// - Inline updated
	function IsInlineUpdated() {
		return $this->LastAction == "update" && $this->CurrentAction == "";
	}

	// - Grid updated
	function IsGridUpdated() {
		return $this->LastAction == "gridupdate" && $this->CurrentAction == "";
	}

	// - Grid inserted
	function IsGridInserted() {
		return $this->LastAction == "gridinsert" && $this->CurrentAction == "";
	}

	// Export return page
	function ExportReturnUrl() {
		$url = @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_EXPORT_RETURN_URL];
		return ($url <> "") ? $url : ew_CurrentPage();
	}

	function setExportReturnUrl($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_EXPORT_RETURN_URL] = $v;
	}

	// Records per page
	function getRecordsPerPage() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_REC_PER_PAGE];
	}

	function setRecordsPerPage($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_REC_PER_PAGE] = $v;
	}

	// Start record number
	function getStartRecordNumber() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_START_REC];
	}

	function setStartRecordNumber($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_START_REC] = $v;
	}

	// Search highlight name
	function HighlightName() {
		return $this->TableVar . "_Highlight";
	}

	// Search WHERE clause
	function getSearchWhere() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_SEARCH_WHERE];
	}

	function setSearchWhere($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_SEARCH_WHERE] = $v;
	}

	// Session WHERE clause
	function getSessionWhere() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_WHERE];
	}

	function setSessionWhere($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_WHERE] = $v;
	}

	// Session ORDER BY
	function getSessionOrderBy() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ORDER_BY];
	}

	function setSessionOrderBy($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_ORDER_BY] = $v;
	}

	// Session key
	function getKey($fld) {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_KEY . "_" . $fld];
	}

	function setKey($fld, $v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TableVar . "_" . EW_TABLE_KEY . "_" . $fld] = $v;
	}

	// URL encode
	function UrlEncode($str) {
		return urlencode($str);
	}
}

/**
 * Field class
 */

class cField {
	var $TblName; // Table name
	var $TblVar; // Table variable name
	var $FldName; // Field name
	var $FldVar; // Field variable name
	var $FldExpression; // Field expression (used in SQL)
	var $FldBasicSearchExpression; // Field expression (used in basic search SQL)
	var $FldIsVirtual; // Virtual field
	var $FldVirtualExpression; // Virtual field expression (used in ListSQL)
	var $FldForceSelection; // Autosuggest force selection
	var $FldVirtualSearch; // Search as virtual field
	var $FldDefaultErrMsg; // Default error message
	var $VirtualValue; // Virtual field value
	var $TooltipValue; // Field tooltip value
	var $TooltipWidth = 0; // Field tooltip width
	var $FldType; // Field type
	var $FldDataType; // PHPMaker Field type
	var $FldBlobType; // For Oracle only
	var $FldViewTag; // View Tag
	var $FldIsDetailKey = FALSE; // Field is detail key
	var $AdvancedSearch; // AdvancedSearch Object
	var $Upload; // Upload Object
	var $FldDateTimeFormat; // Date time format
	var $CssStyle; // CSS style
	var $CssClass; // CSS class
	var $ImageAlt; // Image alt
	var $ImageWidth = 0; // Image width
	var $ImageHeight = 0; // Image height
	var $ImageResize = FALSE; // Image resize
	var $ResizeQuality = 100; // Resize quality
	var $ViewCustomAttributes; // View custom attributes
	var $EditCustomAttributes; // Edit custom attributes
	var $LinkCustomAttributes; // Link custom attributes
	var $Count; // Count
	var $Total; // Total
	var $TrueValue = '1';
	var $FalseValue = '0';
	var $Visible = TRUE; // Visible
	var $Disabled; // Disabled
	var $ReadOnly = FALSE; // Read only
	var $TruncateMemoRemoveHtml; // Remove HTML from memo field
	var $CustomMsg = ""; // Custom message
	var $CellCssClass = ""; // Cell CSS class
	var $CellCssStyle = ""; // Cell CSS style
	var $CellCustomAttributes = ""; // Cell custom attributes
	var $MultiUpdate; // Multi update
	var $OldValue; // Old Value
	var $ConfirmValue; // Confirm value
	var $CurrentValue; // Current value
	var $ViewValue; // View value
	var $EditValue; // Edit value
	var $EditValue2; // Edit value 2 (search)
	var $HrefValue; // Href value
	var $HrefValue2; // Href value 2 (confirm page upload control)
	var $FormValue; // Form value
	var $QueryStringValue; // QueryString value
	var $DbValue; // Database value
	var $Sortable = TRUE; // Sortable
	var $UploadPath = EW_UPLOAD_DEST_PATH; // Upload path
	var $CellAttrs = array(); // Cell custom attributes
	var $EditAttrs = array(); // Edit custom attributes
	var $ViewAttrs = array(); // View custom attributes
	var $LinkAttrs = array(); // Link custom attributes
	var $LookupFn = ""; // Lookup table function(&$sql) for modifying SQL
	var $DisplayValueSeparator = ", ";

	// Constructor
	function __construct($tblvar, $tblname, $fldvar, $fldname, $fldexp, $fldbsexp, $fldtype, $flddtfmt, $upload, $fldvirtualexp, $fldvirtual, $forceselect, $fldvirtualsrch, $fldviewtag="") {
		$this->TblVar = $tblvar;
		$this->TblName = $tblname;
		$this->FldVar = $fldvar;
		$this->FldName = $fldname;
		$this->FldExpression = $fldexp;
		$this->FldBasicSearchExpression = $fldbsexp;
		$this->FldType = $fldtype;
		$this->FldDataType = ew_FieldDataType($fldtype);
		$this->FldDateTimeFormat = $flddtfmt;
		$this->AdvancedSearch = new cAdvancedSearch($this->TblVar, $this->FldVar);
		if ($upload)
			$this->Upload = new cUpload($this->TblVar, $this->FldVar);
		$this->FldVirtualExpression = $fldvirtualexp;
		$this->FldIsVirtual = $fldvirtual;
		$this->FldForceSelection = $forceselect;
		$this->FldVirtualSearch = $fldvirtualsrch;
		$this->FldViewTag = $fldviewtag;
	}
	var $Caption = "";

	// Set field caption
	function setFldCaption($v) {
		$this->Caption = $v;
	}

	// Field caption
	function FldCaption() {
		global $Language;
		if ($this->Caption <> "")
			return $this->Caption;
		else
			return $Language->FieldPhrase($this->TblVar, substr($this->FldVar, 2), "FldCaption");
	}

	// Field title
	function FldTitle() {
		global $Language;
		return $Language->FieldPhrase($this->TblVar, substr($this->FldVar, 2), "FldTitle");
	}

	// Field image alt
	function FldAlt() {
		global $Language;
		return $Language->FieldPhrase($this->TblVar, substr($this->FldVar, 2), "FldAlt");
	}

	// Field error message
	function FldErrMsg() {
		global $Language;
		$err = $Language->FieldPhrase($this->TblVar, substr($this->FldVar, 2), "FldErrMsg");
		if ($err == "") $err = $this->FldDefaultErrMsg . " - " . $this->FldCaption();
		return $err;
	}

	// Field tag value
	function FldTagValue($i) {
		global $Language;
		return $Language->FieldPhrase($this->TblVar, substr($this->FldVar, 2), "FldTagValue" . $i);
	}

	// Field tag caption
	function FldTagCaption($i) {
		global $Language;
		return $Language->FieldPhrase($this->TblVar, substr($this->FldVar, 2), "FldTagCaption" . $i);
	}

	// Reset attributes for field object
	function ResetAttrs() {
		$this->CssStyle = "";
		$this->CssClass = "";
		$this->CellCssStyle = "";
		$this->CellCssClass = "";
		$this->CellAttrs = array();
		$this->EditAttrs = array();
		$this->ViewAttrs = array();
		$this->LinkAttrs = array();
	}

	// View Attributes
	function ViewAttributes() {
		$sAtt = "";
		$sStyle = trim($this->CssStyle);
		if (@$this->ViewAttrs["style"] <> "")
			$sStyle .= " " . $this->ViewAttrs["style"];
		$sClass = trim($this->CssClass);
		if (@$this->ViewAttrs["class"] <> "")
			$sClass .= " " . $this->ViewAttrs["class"];
		if (trim($sStyle) <> "")
			$sAtt .= " style=\"" . trim($sStyle) . "\"";
		if (trim($sClass) <> "")
			$sAtt .= " class=\"" . trim($sClass) . "\"";
		if (trim($this->ImageAlt) <> "")
			$this->ViewAttrs["alt"] = trim($this->ImageAlt);
		if (intval($this->ImageWidth) > 0 && (!$this->ImageResize || ($this->ImageResize && intval($this->ImageHeight) <= 0)))
			$this->ViewAttrs["width"] = intval($this->ImageWidth);
		if (intval($this->ImageHeight) > 0 && (!$this->ImageResize || ($this->ImageResize && intval($this->ImageWidth) <= 0)))
			$this->ViewAttrs["height"] = intval($this->ImageHeight);
		foreach ($this->ViewAttrs as $k => $v) {
			if ($k <> "style" && $k <> "class" && trim($v) <> "")
				$sAtt .= " " . $k . "=\"" . trim($v) . "\"";
		}
		if (trim($this->ViewCustomAttributes) <> "")
			$sAtt .= " " . trim($this->ViewCustomAttributes);
		return $sAtt;
	}

	// Edit attributes
	function EditAttributes() {
		$sAtt = "";
		$sStyle = trim($this->CssStyle);
		if (@$this->EditAttrs["style"] <> "")
			$sStyle .= " " . $this->EditAttrs["style"];
		$sClass = trim($this->CssClass);
		if (@$this->EditAttrs["class"] <> "")
			$sClass .= " " . $this->EditAttrs["class"];
		if (trim($sStyle) <> "")
			$sAtt .= " style=\"" . trim($sStyle) . "\"";
		if ($sClass <> "")
			$sAtt .= " class=\"" . trim($sClass) . "\"";
		if ($this->Disabled)
			$this->EditAttrs["disabled"] = "disabled";
		if ($this->ReadOnly)
			$this->EditAttrs["readonly"] = "readonly";
		foreach ($this->EditAttrs as $k => $v) {
			if ($k <> "style" && $k <> "class" && trim($v) <> "")
				$sAtt .= " " . $k . "=\"" . trim($v) . "\"";
		}
		if (trim($this->EditCustomAttributes) <> "")
			$sAtt .= " " . trim($this->EditCustomAttributes);
		return $sAtt;
	}

	// Cell styles
	function CellStyles() {
		$sAtt = "";
		$sStyle = trim($this->CellCssStyle);
		if (@$this->CellAttrs["style"] <> "")
			$sStyle .= " " . $this->CellAttrs["style"];
		$sClass = trim($this->CellCssClass);
		if (@$this->CellAttrs["class"] <> "")
			$sClass .= " " . $this->CellAttrs["class"];
		if (trim($sStyle) <> "")
			$sAtt .= " style=\"" . trim($sStyle) . "\"";
		if (trim($sClass) <> "")
			$sAtt .= " class=\"" . trim($sClass) . "\"";
		return $sAtt;
	}

	// Cell attributes
	function CellAttributes() {
		$sAtt = $this->CellStyles();
		foreach ($this->CellAttrs as $k => $v) {
			if ($k <> "style" && $k <> "class" && trim($v) <> "")
				$sAtt .= " " . $k . "=\"" . trim($v) . "\"";
		}
		if (trim($this->CellCustomAttributes) <> "")
			$sAtt .= " " . trim($this->CellCustomAttributes);
		return $sAtt;
	}

	// Link attributes
	function LinkAttributes() {
		$sAtt = "";
		$sHref = trim($this->HrefValue);
		foreach ($this->LinkAttrs as $k => $v) {
			if (trim($v) <> "") {
				if ($k == "href")
					$sHref .= " " . $v;
				else
					$sAtt .= " " . $k . "=\"" . trim($v) . "\"";
			}
		}
		if ($sHref <> "")
			$sAtt .= " href=\"" . trim($sHref) . "\"";
		if (trim($this->LinkCustomAttributes) <> "")
			$sAtt .= " " . trim($this->LinkCustomAttributes);
		return $sAtt;
	}

	// Sort
	function getSort() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TblVar . "_" . EW_TABLE_SORT . "_" . $this->FldVar];
	}

	function setSort($v) {
		if (@$_SESSION[EW_PROJECT_NAME . "_" . $this->TblVar . "_" . EW_TABLE_SORT . "_" . $this->FldVar] <> $v) {
			$_SESSION[EW_PROJECT_NAME . "_" . $this->TblVar . "_" . EW_TABLE_SORT . "_" . $this->FldVar] = $v;
		}
	}

	function ReverseSort() {
		return ($this->getSort() == "ASC") ? "DESC" : "ASC";
	}

	// Advanced search
	function UrlParameterName($name) {
		$fldparm = substr($this->FldVar, 2);
		if (strcasecmp($name, "SearchValue") == 0) {
			$fldparm = "x_" . $fldparm;
		} elseif (strcasecmp($name, "SearchOperator") == 0) {
			$fldparm = "z_" . $fldparm;
		} elseif (strcasecmp($name, "SearchCondition") == 0) {
			$fldparm = "v_" . $fldparm;
		} elseif (strcasecmp($name, "SearchValue2") == 0) {
			$fldparm = "y_" . $fldparm;
		} elseif (strcasecmp($name, "SearchOperator2") == 0) {
			$fldparm = "w_" . $fldparm;
		}
		return $fldparm;
	}

	// List view value
	function ListViewValue() {
		if ($this->FldDataType == EW_DATATYPE_XML) {
			return $this->ViewValue . "&nbsp;";
		} else {
			$value = trim(strval($this->ViewValue));
			if ($value <> "") {
				$value2 = trim(preg_replace('/<[^img][^>]*>/i', '', strval($value)));
				return ($value2 <> "") ? $this->ViewValue : "&nbsp;";
			} else {
				return "&nbsp;";
			}
		}
	}
	var $Exportable = TRUE;

	// Export caption
	function ExportCaption() {
		return (EW_EXPORT_FIELD_CAPTION) ? $this->FldCaption() : $this->FldName;
	}
	var $ExportOriginalValue = EW_EXPORT_ORIGINAL_VALUE;

	// Export value
	function ExportValue() {
		return ($this->ExportOriginalValue) ? $this->CurrentValue : $this->ViewValue;
	}

	// Get temp image
	function GetTempImage() {
		if ($this->FldDataType == EW_DATATYPE_BLOB) {
			$wrkdata = $this->Upload->DbValue;
			if (!empty($wrkdata)) {
				if ($this->ImageResize) {
					$wrkwidth = $this->ImageWidth;
					$wrkheight = $this->ImageHeight;
					ew_ResizeBinary($wrkdata, $wrkwidth, $wrkheight, $this->ResizeQuality);
				}
				return ew_TmpImage($wrkdata);
			}
		} else {
			$wrkfile = $this->Upload->DbValue;
			if (empty($wrkfile)) $wrkfile = $this->CurrentValue;
			if (!empty($wrkfile)) {
				$imagefn = ew_UploadPathEx(TRUE, $this->UploadPath) . $wrkfile;
				if ($this->ImageResize) {
					$wrkwidth = $this->ImageWidth;
					$wrkheight = $this->ImageHeight;
					$wrkdata = ew_ResizeFileToBinary($imagefn, $wrkwidth, $wrkheight, $this->ResizeQuality);
					return ew_TmpImage($wrkdata);
				} else {
					return $imagefn;
				}
			}
		}
	}

	// Form value
	function setFormValue($v) {
		$this->FormValue = ew_StripSlashes($v);
		if (is_array($this->FormValue))
			$this->FormValue = implode(",", $this->FormValue);
		$this->CurrentValue = $this->FormValue;
	}

	// Old value (from $_POST)
	function setOldValue($v) {
		$this->OldValue = ew_StripSlashes($v);
		if (is_array($this->OldValue)) {
			$this->OldValue = implode(",", $this->OldValue);
		} else {
			$this->OldValue = $v;
		}
	}

	// QueryString value
	function setQueryStringValue($v) {
		$this->QueryStringValue = ew_StripSlashes($v);
		$this->CurrentValue = $this->QueryStringValue;
	}

	// Database value
	function setDbValue($v) {
		$this->DbValue = $v;
		$this->CurrentValue = $this->DbValue;
	}

	// Set database value with error default
	function SetDbValueDef(&$rs, $value, $default, $skip = FALSE) {
		if ($skip || !$this->Visible || $this->Disabled)
			return;
		switch ($this->FldType) {
			case 2:
			case 3:
			case 16:
			case 17:
			case 18:  // Integer
				$value = trim($value);
				$DbValue = (is_numeric($value)) ? intval($value) : $default;
				break;
			case 19:
			case 20:
			case 21: // Big integer
				$value = trim($value);
				$DbValue = (is_numeric($value)) ? $value : $default;
				break;
			case 5:
			case 6:
			case 14:
			case 131: // Double
			case 139:
			case 4: // Single
				$value = trim($value);
				$value = ew_StrToFloat($value);
				$DbValue = (is_numeric($value)) ? $value : $default;
				break;
			case 7:
			case 133:
			case 134:
			case 135: // Date
			case 141: // XML
			case 145: // Time
			case 146: // DateTiemOffset
			case 201:
			case 203:
			case 129:
			case 130:
			case 200:
			case 202: // String
				$value = trim($value);
				$DbValue = ($value == "") ? $default : $value;
				break;
			case 128:
			case 204:
			case 205: // Binary
				$DbValue = (is_null($value)) ? $default : $value;
				break;
			case 72: // GUID
				$value = trim($value);
				$DbValue = ($value <> "" && ew_CheckGUID($value)) ? $value : $default;
				break;
			case 11: // Boolean
				$DbValue = (is_bool($value) || is_numeric($value)) ? $value : $default;
				break;
			default:
				$DbValue = $value;
		}

		//$this->setDbValue($DbValue); // Do not override CurrentValue
		$this->DbValue = $DbValue;
		$rs[$this->FldName] = $this->DbValue;
	}

	// Session value
	function getSessionValue() {
		return @$_SESSION[EW_PROJECT_NAME . "_" . $this->TblVar . "_" . $this->FldVar . "_SessionValue"];
	}

	function setSessionValue($v) {
		$_SESSION[EW_PROJECT_NAME . "_" . $this->TblVar . "_" . $this->FldVar . "_SessionValue"] = $v;
	}
}

/**
 * List option collection class
 */

class cListOptions {
	var $Items = array();
	var $CustomItem = "";
	var $Tag = "td";
	var $TagClassName = "";
	var $TableVar = "";
	var $RowCnt = "";
	var $ScriptType = "block";
	var $ScriptId = "";
	var $ScriptClassName = "";
	var $JavaScript = "";
	var $RowSpan = 1;

	// Add and return a new option
	function &Add($Name) {
		$item = new cListOption($Name);
		$item->Parent = &$this;
		$this->Items[$Name] = $item;
		return $item;
	}

	// Load default settings
	function LoadDefault() {
		$this->CustomItem = "";
		foreach ($this->Items as $key => $item)
			$this->Items[$key]->Body = "";
	}

	// Hide all options
	function HideAllOptions() {
		foreach ($this->Items as $key => $item)
			$this->Items[$key]->Visible = FALSE;
	}

	// Show all options
	function ShowAllOptions() {
		foreach ($this->Items as $key => $item)
			$this->Items[$key]->Visible = TRUE;
	}

	// Get item by name
	// predefined names: view/edit/copy/delete/detail_<DetailTable>/userpermission/checkbox
	function &GetItem($Name) {
		$item = array_key_exists($Name, $this->Items) ? $this->Items[$Name] : NULL;
		return $item;
	}

	// Move item to position
	function MoveItem($Name, $Pos) {
		$cnt = count($this->Items);
		if ($Pos < 0)
			$Pos = 0;
		elseif ($Pos >= $cnt)
			$Pos = $cnt - 1;
		$item = $this->GetItem($Name);
		if ($item) {
			unset($this->Items[$Name]);
			$this->Items = array_merge(array_slice($this->Items, 0, $Pos),
				array($Name => $item), array_slice($this->Items, $Pos));
		}
	}

	// Render list options
	function Render($Part, $Pos="", $RowCnt="", $ScriptType="block", $ScriptId="", $ScriptClassName="") {
		if ($ScriptId <> "") {
			$this->RenderEx($Part, $Pos, $RowCnt, "block", $ScriptId, $ScriptClassName); // original block for ew_ShowTemplates
			$this->RenderEx($Part, $Pos, $RowCnt, "blocknotd", $ScriptId);
			$this->RenderEx($Part, $Pos, $RowCnt, "single", $ScriptId);
		} else {
			$this->RenderEx($Part, $Pos, $RowCnt, $ScriptType, $ScriptId, $ScriptClassName);
		}
	}

	function RenderEx($Part, $Pos="", $RowCnt="", $ScriptType="block", $ScriptId="", $ScriptClassName="") {
		$this->RowCnt = $RowCnt;
		$this->ScriptType = $ScriptType;
		$this->ScriptId = $ScriptId;
		$this->ScriptClassName = $ScriptClassName;
		$this->JavaScript = "";
		if ($ScriptId <> "") {
			$this->Tag = ($ScriptType == "block") ? "td" : "span";
			if ($ScriptType == "block") {
				if ($Part == "header")
					echo "<script id=\"tpoh_" . $ScriptId . "\" class=\"" . $ScriptClassName . "\" type=\"text/html\">";
				else if ($Part == "body")
					echo "<script id=\"tpob" . $RowCnt . "_" . $ScriptId . "\" class=\"" . $ScriptClassName . "\" type=\"text/html\">";
				else if ($Part == "footer")
					echo "<script id=\"tpof_" . $ScriptId . "\" class=\"" . $ScriptClassName . "\" type=\"text/html\">";
			} elseif ($ScriptType == "blocknotd") {
				if ($Part == "header")
					echo "<script id=\"tpo2h_" . $ScriptId . "\" class=\"" . $ScriptClassName . "\" type=\"text/html\">";
				else if ($Part == "body")
					echo "<script id=\"tpo2b" . $RowCnt . "_" . $ScriptId . "\" class=\"" . $ScriptClassName . "\" type=\"text/html\">";
				else if ($Part == "footer")
					echo "<script id=\"tpo2f_" . $ScriptId . "\" class=\"" . $ScriptClassName . "\" type=\"text/html\">";
				echo "<span>";
			}
		} else {
			$this->Tag = ($Pos <> "") ? "td" : "span";
		}
		if ($this->CustomItem <> "") {
			$cnt = 0;
			foreach ($this->Items as &$item) {
				if ($item->Visible && ($ScriptId <> "" || $this->ShowPos($item->OnLeft, $Pos)))
					$cnt++;
				if ($item->Name == $this->CustomItem)
					$opt = $item;
			}
			if (is_object($opt) && $cnt > 0) {
				if ($ScriptId <> "" || $this->ShowPos($opt->OnLeft, $Pos)) {
					echo $opt->Render($Part, $cnt);
				} else {
					echo $opt->Render("", $cnt);
				}
			}
		} else {
			foreach ($this->Items as &$item) {
				if ($item->Visible && ($ScriptId <> "" || $this->ShowPos($item->OnLeft, $Pos)))
					echo $item->Render($Part, 1);
			}
		}
		if (($ScriptType == "block" || $ScriptType == "blocknotd") && $ScriptId <> "") {
			if ($ScriptType == "blocknotd")
				echo "</span>";
			echo "</script>";
			if ($this->JavaScript <> "")
				echo $this->JavaScript;
		}
	}

	function ShowPos($OnLeft, $Pos) {
		return ($OnLeft && $Pos == "left") || (!$OnLeft && $Pos == "right") || ($Pos == "");
	}

	// Concat options and return concatenated HTML
	// pattern - regular expression pattern for matching the option names, e.g. '/^detail_/'
	function Concat($pattern, $separator = "") {
		$ar = array();
		$keys = array_keys($this->Items);
		foreach ($keys as $key) {
			if (preg_match($pattern, $key) && trim($this->Items[$key]->Body) <> "")
				$ar[] = $this->Items[$key]->Body;
		}
		return implode($separator, $ar);
	}

	// Merge options to the first option and return it
	// pattern - regular expression pattern for matching the option names, e.g. '/^detail_/'
	function &Merge($pattern, $separator = "") {
		$keys = array_keys($this->Items);
		$first = NULL;
		foreach ($keys as $key) {
			if (preg_match($pattern, $key)) {
				if (!$first) {
					$first = $this->Items[$key];
					$first->Body = $this->Concat($pattern, $separator);
				} else {
					$this->Items[$key]->Visible = FALSE;
				}
			}
		}
		return $first;
	}
}

/**
 * List option class
 */

class cListOption {
	var $Name;
	var $OnLeft;
	var $CssStyle;
	var $CssClass;
	var $Visible = TRUE;
	var $Header;
	var $Body;
	var $Footer;
	var $Parent;

	function __construct($Name) {
		$this->Name = $Name;
	}

	function MoveTo($Pos) {
		$this->Parent->MoveItem($this->Name, $Pos);
	}

	function Render($Part, $ColSpan = 1) {
		$tagclass = $this->Parent->TagClassName;
		if ($Part == "header") {
			if ($tagclass == "") $tagclass = "ewListOptionHeader";
			$value = $this->Header;
		} elseif ($Part == "body") {
			if ($tagclass == "") {
				if ($this->Parent->Tag == "td")
					$tagclass = "ewListOptionBody";
				else
					$tagclass = "ewListOptionBody2";
			}
			$value = $this->Body;
		} elseif ($Part == "footer") {
			if ($tagclass == "") $tagclass = "ewListOptionFooter";
			$value = $this->Footer;
		} else {
			$value = $Part;
		}
		if (strval($value) == "" && $this->Parent->Tag == "span" && $this->Parent->ScriptId == "")
			return "";
		$res = ($value <> "") ? $value : "&nbsp;";
		ew_AppendClass($tagclass, $this->CssClass);
		$attrs = array("class" => $tagclass,  "style" => $this->CssStyle);
		if (strtolower($this->Parent->Tag) == "td" && $this->Parent->RowSpan > 1)
			$attrs["rowspan"] = $this->Parent->RowSpan;
		if (strtolower($this->Parent->Tag) == "td" && $ColSpan > 1)
			$attrs["colspan"] = $ColSpan;
		$name = $this->Parent->TableVar . "_" . $this->Name;
		if ($Part == "header")
			$res = "<span id=\"elh_" . $name . "\" class=\"" . $name . "\">" . $res . "</span>";
		else if ($Part == "body")
			$res = "<span id=\"el" . $this->Parent->RowCnt . "_" . $name . "\" class=\"" . $name . "\">" . $res . "</span>";
		else if ($Part == "footer")
			$res = "<span id=\"elf_" . $name . "\" class=\"" . $name . "\">" . $res . "</span>";
		$res = ew_HtmlElement($this->Parent->Tag, $attrs, $res);
		if ($this->Parent->ScriptId <> "") {
			$js = ew_ExtractScript($res, $this->Parent->ScriptClassName . "_js");
			if ($this->Parent->ScriptType == "single") {
				if ($Part == "header")
					$res = "<script id=\"tpoh_" . $this->Parent->ScriptId . "_" . $this->Name . "\" type=\"text/html\">" . $res . "</script>";
				else if ($Part == "body")
					$res = "<script id=\"tpob" . $this->Parent->RowCnt . "_" . $this->Parent->ScriptId . "_" . $this->Name . "\" type=\"text/html\">" . $res . "</script>";
				else if ($Part == "footer")
					$res = "<script id=\"tpof_" . $this->Parent->ScriptId . "_" . $this->Name . "\" type=\"text/html\">" . $res . "</script>";
			}
			if ($js <> "")
				if ($this->Parent->ScriptType == "single")
					$res .= $js;
				else
					$this->Parent->JavaScript .= $js;
		}
		return $res;
	}
}
?>
<?php

//
// Basic Search class
//
class cBasicSearch {
	var $TblVar = "";
	var $Keyword = "";
	var $KeywordDefault = "";
	var $Type = "=";
	var $TypeDefault = "=";

	private $_Prefix = "";

	// Constructor
	function __construct($tblvar) {
		$this->TblVar = $tblvar;
		$this->_Prefix = EW_PROJECT_NAME . "_" . $tblvar . "_";
	}

	// Session variable name
	function GetSessionName($suffix) {
		return $this->_Prefix . $suffix;
	}

	// Load default
	function LoadDefault() {
		$this->Keyword = $this->KeywordDefault;
		$this->Type = $this->TypeDefault;
		$this->Save();
	}

	// Unset session
	function UnsetSession() {
		unset($_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH_TYPE)]);
		unset($_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH)]);
	}

	// Isset session
	function IssetSession() {
		return isset($_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH)]);
	}

	// Save to session
	function setKeyword($v) {
		$_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH)] = $v;
	}

	function setType($v) {
		$_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH_TYPE)] = $v;
	}

	function Save() {
		$_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH)] = $this->Keyword;
		$_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH_TYPE)] = $this->Type;
	}

	// Load from session
	function getKeyword() {
		return @$_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH)];
	}

	function getType() {
		return @$_SESSION[$this->GetSessionName(EW_TABLE_BASIC_SEARCH_TYPE)];
	}

	function Load() {
		$this->Keyword = $this->getKeyword();
		if ($this->getType() == "") $this->setType("=");
		$this->Type = $this->getType();
	}
}

/**
 * Advanced Search class
 */

class cAdvancedSearch {
	var $TblVar;
	var $FldVar;
	var $SearchValue; // Search value
	var $SearchOperator; // Search operator
	var $SearchCondition; // Search condition
	var $SearchValue2; // Search value 2
	var $SearchOperator2; // Search operator 2
	var $SearchValueDefault = ""; // Search value default
	var $SearchOperatorDefault = ""; // Search operator default
	var $SearchConditionDefault = ""; // Search condition default
	var $SearchValue2Default = ""; // Search value 2 default
	var $SearchOperator2Default = ""; // Search operator 2 default

	private $_Prefix = "";

	private $_Suffix = "";

	// Constructor
	function __construct($tblvar, $fldvar) {
		$this->TblVar = $tblvar;
		$this->FldVar = $fldvar;
		$this->_Prefix = EW_PROJECT_NAME . "_" . $tblvar . "_" . EW_TABLE_ADVANCED_SEARCH . "_";
		$this->_Suffix = "_" . substr($fldvar, 2);
	}

	// Session variable name
	function GetSessionName($infix) {
		return $this->_Prefix . $infix . $this->_Suffix;
	}

	// Unset session
	function UnsetSession() {
		unset($_SESSION[$this->GetSessionName("x")]);
		unset($_SESSION[$this->GetSessionName("z")]);
		unset($_SESSION[$this->GetSessionName("v")]);
		unset($_SESSION[$this->GetSessionName("y")]);
		unset($_SESSION[$this->GetSessionName("w")]);
	}

	// Isset session
	function IssetSession() {
		return isset($_SESSION[$this->GetSessionName("x")]) ||
			isset($_SESSION[$this->GetSessionName("y")]);
	}

	// Save to session
	function Save() {
		$FldVal = ew_StripSlashes($this->SearchValue);
		if (is_array($FldVal)) $FldVal = implode(",", $FldVal);
		$FldVal2 = ew_StripSlashes($this->SearchValue2);
		if (is_array($FldVal2)) $FldVal2 = implode(",", $FldVal2);
		if (@$_SESSION[$this->GetSessionName("x")] <> $FldVal)
			$_SESSION[$this->GetSessionName("x")] = $FldVal;
		if (@$_SESSION[$this->GetSessionName("y")] <> $FldVal2)
			$_SESSION[$this->GetSessionName("y")] = $FldVal2;
		if (@$_SESSION[$this->GetSessionName("z")] <> $this->SearchOperator)
			$_SESSION[$this->GetSessionName("z")] = $this->SearchOperator;
		if (@$_SESSION[$this->GetSessionName("v")] <> $this->SearchCondition)
			$_SESSION[$this->GetSessionName("v")] = $this->SearchCondition;
		if (@$_SESSION[$this->GetSessionName("w")] <> $this->SearchOperator2)
			$_SESSION[$this->GetSessionName("w")] = $this->SearchOperator2;
	}

	// Load from session
	function Load() {
		$this->SearchValue = @$_SESSION[$this->GetSessionName("x")];
		$this->SearchOperator = @$_SESSION[$this->GetSessionName("z")];
		$this->SearchCondition = @$_SESSION[$this->GetSessionName("v")];
		$this->SearchValue2 = @$_SESSION[$this->GetSessionName("y")];
		$this->SearchOperator2 = @$_SESSION[$this->GetSessionName("w")];
	}

	function getValue($infix) {
		return @$_SESSION[$this->GetSessionName($infix)];
	}

	// Load default values
	function LoadDefault() {
		if ($this->SearchValueDefault != "") $this->SearchValue = $this->SearchValueDefault;
		if ($this->SearchOperatorDefault != "") $this->SearchOperator = $this->SearchOperatorDefault;
		if ($this->SearchConditionDefault != "") $this->SearchCondition = $this->SearchConditionDefault;
		if ($this->SearchValue2Default != "") $this->SearchValue2 = $this->SearchValue2Default;
		if ($this->SearchOperator2Default != "") $this->SearchOperator2 = $this->SearchOperator2Default;
	}
}
?>
<?php

/**
 * Upload class
 */

class cUpload {
	var $Index = -1; // Index for multiple form elements
	var $TblVar; // Table variable
	var $FldVar; // Field variable
	var $Message; // Error message
	var $DbValue; // Value from database
	var $Value = NULL; // Upload value
	var $Action; // Upload action
	var $FileName; // Upload file name
	var $FileSize; // Upload file size
	var $ContentType; // File content type
	var $ImageWidth; // Image width
	var $ImageHeight; // Image height
	var $Error; // Upload error

	// Constructor
	function __construct($TblVar, $FldVar, $Binary = FALSE) {
		$this->TblVar = $TblVar;
		$this->FldVar = $FldVar;
	}

	function GetSessionID() {
		return EW_PROJECT_NAME . "_" . $this->TblVar . "_" . $this->FldVar . "_" . $this->Index;
	}

	// Save value to Session
	function SaveDbToSession() {
		$sSessionID = $this->GetSessionID();
		$_SESSION[$sSessionID . "_DbValue"] = $this->DbValue;
	}

	// Restore value from Session
	function RestoreDbFromSession() {
		$sSessionID = $this->GetSessionID();
		$this->DbValue = @$_SESSION[$sSessionID . "_DbValue"];
	}

	// Remove value from Session
	function RemoveDbFromSession() {
		$sSessionID = $this->GetSessionID();
		unset($_SESSION[$sSessionID . "_DbValue"]);
	}

	// Save upload values to Session
	function SaveToSession() {
		$sSessionID = $this->GetSessionID();
		$_SESSION[$sSessionID . "_upload"] = serialize($this);
	}

	// Restore upload values from Session
	function RestoreFromSession() {
		$sSessionID = $this->GetSessionID();
		$obj = @unserialize($_SESSION[$sSessionID . "_upload"]);
		if (is_object($obj)) {
			$this->Action = $obj->Action;
			$this->FileSize = $obj->FileSize;
			$this->FileName = $obj->FileName;
			$this->ContentType = $obj->ContentType;
			$this->ImageWidth = $obj->ImageWidth;
			$this->ImageHeight = $obj->ImageHeight;
			$this->Value = $obj->Value;
			$this->Error = $obj->Error;
		}
	}

	// Remove upload values from Session
	function RemoveFromSession() {
		$sSessionID = $this->GetSessionID();
		unset($_SESSION[$sSessionID . "_upload"]);
		$this->RemoveDbFromSession();
	}

	// Check file type of the uploaded file
	function UploadAllowedFileExt($filename) {
		return ew_CheckFileType($filename);
	}

	// Get upload file
	function UploadFile() {
		global $objForm;
		$this->Value = NULL; // Reset first
		$gsFldVar = $this->FldVar;
		$gsFldVarAction = "a" . substr($gsFldVar, 1);

		// Get action
		$this->Action = $objForm->GetValue($gsFldVarAction);

		// Get and check the upload file size
		$this->FileSize = $objForm->GetUploadFileSize($gsFldVar);

		// Get and check the upload file type
		$this->FileName = $objForm->GetUploadFileName($gsFldVar);

		// Get upload file content type
		$this->ContentType = $objForm->GetUploadFileContentType($gsFldVar);

		// Get upload value
		$this->Value = $objForm->GetUploadFileData($gsFldVar);

		// Get upload error
		$this->Error = $objForm->GetUploadFileError($gsFldVar);

		// Get image width and height
		$sizes = $objForm->GetUploadImageSize($gsFldVar);
		$this->ImageWidth = @$sizes[0];
		$this->ImageHeight = @$sizes[1];
		return TRUE; // Normal return
	}

	// Resize image
	function Resize($width, $height, $quality) {
		if (!ew_Empty($this->Value)) {
			$wrkwidth = $width;
			$wrkheight = $height;
			if (ew_ResizeBinary($this->Value, $wrkwidth, $wrkheight, $quality)) {
				if ($wrkwidth > 0 && $wrkheight > 0) {
					$this->ImageWidth = $wrkwidth;
					$this->ImageHeight = $wrkheight;
				}
				$this->FileSize = strlen($this->Value);
			}
		}
	}

	// Save uploaded data to file (Path relative to application root)
	function SaveToFile($Path, $NewFileName, $OverWrite) {
		if (!ew_Empty($this->Value)) {
			$Path = ew_UploadPathEx(TRUE, $Path);
			if (trim(strval($NewFileName)) == "") $NewFileName = $this->FileName;
			if ($OverWrite) {
				return ew_SaveFile($Path, $NewFileName, $this->Value);
			} else {
				return ew_SaveFile($Path, ew_UploadFileNameEx($Path, $NewFileName), $this->Value);
			}
		}
		return FALSE;
	}

	// Resize and save uploaded data to file (Path relative to application root)
	function ResizeAndSaveToFile($Width, $Height, $Quality, $Path, $NewFileName, $OverWrite) {
		$bResult = FALSE;
		if (!ew_Empty($this->Value)) {
			$OldValue = $this->Value;
			$this->Resize($Width, $Height, $Quality);
			$bResult = $this->SaveToFile($Path, $NewFileName, $OverWrite);
			$this->Value = $OldValue;
		}
		return $bResult;
	}
}
?>
<?php

/**
 * Advanced Security class
 */

class cAdvancedSecurity {
	var $UserLevel = array(); // All User Levels
	var $UserLevelPriv = array(); // All User Level permissions
	var $UserLevelID = array(); // User Level ID array
	var $UserID = array(); // User ID array
	var $CurrentUserLevelID;
	var $CurrentUserLevel; // Permissions
	var $CurrentUserID;
	var $CurrentParentUserID;

	// Constructor
	function __construct() {

		// Init User Level
		$this->CurrentUserLevelID = $this->SessionUserLevelID();
		if (is_numeric($this->CurrentUserLevelID) && intval($this->CurrentUserLevelID) >= -1) {
			$this->UserLevelID[] = $this->CurrentUserLevelID;
		}

		// Init User ID
		$this->CurrentUserID = $this->SessionUserID();
		$this->CurrentParentUserID = $this->SessionParentUserID();

		// Load user level (for TablePermission_Loading event)
		$this->LoadUserLevel();
	}

	// Session User ID
	function SessionUserID() {
		return strval(@$_SESSION[EW_SESSION_USER_ID]);
	}

	function setSessionUserID($v) {
		$_SESSION[EW_SESSION_USER_ID] = trim(strval($v));
		$this->CurrentUserID = trim(strval($v));
	}

	// Session Parent User ID
	function SessionParentUserID() {
		return strval(@$_SESSION[EW_SESSION_PARENT_USER_ID]);
	}

	function setSessionParentUserID($v) {
		$_SESSION[EW_SESSION_PARENT_USER_ID] = trim(strval($v));
		$this->CurrentParentUserID = trim(strval($v));
	}

	// Session User Level ID
	function SessionUserLevelID() {
		return @$_SESSION[EW_SESSION_USER_LEVEL_ID];
	}

	function setSessionUserLevelID($v) {
		$_SESSION[EW_SESSION_USER_LEVEL_ID] = $v;
		$this->CurrentUserLevelID = $v;
		if (is_numeric($v) && $v >= -1)
			$this->UserLevelID = array($v);
	}

	// Session User Level value
	function SessionUserLevel() {
		return @$_SESSION[EW_SESSION_USER_LEVEL];
	}

	function setSessionUserLevel($v) {
		$_SESSION[EW_SESSION_USER_LEVEL] = $v;
		$this->CurrentUserLevel = $v;
	}

	// Current user name
	function getCurrentUserName() {
		return strval(@$_SESSION[EW_SESSION_USER_NAME]);
	}

	function setCurrentUserName($v) {
		$_SESSION[EW_SESSION_USER_NAME] = $v;
	}

	function CurrentUserName() {
		return $this->getCurrentUserName();
	}

	// Current User ID
	function CurrentUserID() {
		return $this->CurrentUserID;
	}

	// Current Parent User ID
	function CurrentParentUserID() {
		return $this->CurrentParentUserID;
	}

	// Current User Level ID
	function CurrentUserLevelID() {
		return $this->CurrentUserLevelID;
	}

	// Current User Level value
	function CurrentUserLevel() {
		return $this->CurrentUserLevel;
	}

	// Can add
	function CanAdd() {
		return (($this->CurrentUserLevel & EW_ALLOW_ADD) == EW_ALLOW_ADD);
	}

	function setCanAdd($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_ADD);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_ADD));
		}
	}

	// Can delete
	function CanDelete() {
		return (($this->CurrentUserLevel & EW_ALLOW_DELETE) == EW_ALLOW_DELETE);
	}

	function setCanDelete($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_DELETE);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_DELETE));
		}
	}

	// Can edit
	function CanEdit() {
		return (($this->CurrentUserLevel & EW_ALLOW_EDIT) == EW_ALLOW_EDIT);
	}

	function setCanEdit($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_EDIT);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_EDIT));
		}
	}

	// Can view
	function CanView() {
		return (($this->CurrentUserLevel & EW_ALLOW_VIEW) == EW_ALLOW_VIEW);
	}

	function setCanView($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_VIEW);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_VIEW));
		}
	}

	// Can list
	function CanList() {
		return (($this->CurrentUserLevel & EW_ALLOW_LIST) == EW_ALLOW_LIST);
	}

	function setCanList($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_LIST);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_LIST));
		}
	}

	// Can report
	function CanReport() {
		return (($this->CurrentUserLevel & EW_ALLOW_REPORT) == EW_ALLOW_REPORT);
	}

	function setCanReport($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_REPORT);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_REPORT));
		}
	}

	// Can search
	function CanSearch() {
		return (($this->CurrentUserLevel & EW_ALLOW_SEARCH) == EW_ALLOW_SEARCH);
	}

	function setCanSearch($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_SEARCH);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_SEARCH));
		}
	}

	// Can admin
	function CanAdmin() {
		return (($this->CurrentUserLevel & EW_ALLOW_ADMIN) == EW_ALLOW_ADMIN);
	}

	function setCanAdmin($b) {
		if ($b) {
			$this->CurrentUserLevel = ($this->CurrentUserLevel | EW_ALLOW_ADMIN);
		} else {
			$this->CurrentUserLevel = ($this->CurrentUserLevel & (~ EW_ALLOW_ADMIN));
		}
	}

	// Last URL
	function LastUrl() {
		return @$_COOKIE[EW_PROJECT_NAME]['LastUrl'];
	}

	// Save last URL
	function SaveLastUrl() {
		$s = ew_ServerVar("SCRIPT_NAME");
		$q = ew_ServerVar("QUERY_STRING");
		if ($q <> "") $s .= "?" . $q;
		if ($this->LastUrl() == $s) $s = "";
		@setcookie(EW_PROJECT_NAME . '[LastUrl]', $s);
	}

	// Auto login
	function AutoLogin() {
		if (@$_COOKIE[EW_PROJECT_NAME]['AutoLogin'] == "autologin") {
			$usr = TEAdecrypt(@$_COOKIE[EW_PROJECT_NAME]['Username'], EW_RANDOM_KEY);
			$pwd = TEAdecrypt(@$_COOKIE[EW_PROJECT_NAME]['Password'], EW_RANDOM_KEY);
			$AutoLogin = $this->ValidateUser($usr, $pwd, TRUE);
		} else {
			$AutoLogin = FALSE;
		}
		return $AutoLogin;
	}

	// Validate user
	function ValidateUser($usr, $pwd, $autologin) {
		global $conn, $Language;
		$ValidateUser = FALSE;
		$CustomValidateUser = FALSE;

		// Call User Custom Validate event
		if (EW_USE_CUSTOM_LOGIN) {
			$CustomValidateUser = $this->User_CustomValidate($usr, $pwd);
			if ($CustomValidateUser) {
				$_SESSION[EW_SESSION_STATUS] = "login";
				$this->setCurrentUserName($usr); // Load user name
			}
		}

		// Check hard coded admin first
		if (!$ValidateUser) {
			if (EW_CASE_SENSITIVE_PASSWORD) {
				$ValidateUser = (!$CustomValidateUser && EW_ADMIN_USER_NAME == $usr && EW_ADMIN_PASSWORD == $pwd) ||
								($CustomValidateUser && EW_ADMIN_USER_NAME == $usr);
			} else {
				$ValidateUser = (!$CustomValidateUser && strtolower(EW_ADMIN_USER_NAME) == strtolower($usr)
								&& strtolower(EW_ADMIN_PASSWORD) == strtolower($pwd)) ||
								($CustomValidateUser && strtolower(EW_ADMIN_USER_NAME) == strtolower($usr));
			}
			if ($ValidateUser) {
				$_SESSION[EW_SESSION_STATUS] = "login";
				$_SESSION[EW_SESSION_SYS_ADMIN] = 1; // System Administrator
				$this->setCurrentUserName("Administrator"); // Load user name
			}
		}
		if ($CustomValidateUser)
			return $CustomValidateUser;
		if (!$ValidateUser && !IsPasswordExpired())
			$_SESSION[EW_SESSION_STATUS] = ""; // Clear login status
		return $ValidateUser;
	}

	// No User Level security
	function SetUpUserLevel() {}

	// Add user permission
	function AddUserPermission($UserLevelName, $TableName, $UserPermission) {

		// Get User Level ID from user name
		$UserLevelID = "";
		if (is_array($this->UserLevel)) {
			foreach ($this->UserLevel as $row) {
				list($levelid, $name) = $row;
				if (strval($UserLevelName) == strval($name)) {
					$UserLevelID = $levelid;
					break;
				}
			}
		}
		if (is_array($this->UserLevelPriv) && $UserLevelID <> "") {
			$cnt = count($this->UserLevelPriv);
			for ($i = 0; $i < $cnt; $i++) {
				list($table, $levelid, $priv) = $this->UserLevelPriv[$i];
				if (strtolower($table) == strtolower(EW_PROJECT_ID . $TableName) && strval($levelid) == strval($UserLevelID)) {
					$this->UserLevelPriv[$i][2] = $priv | $UserPermission; // Add permission
					break;
				}
			}
		}
	}

	// Delete user permission
	function DeleteUserPermission($UserLevelName, $TableName, $UserPermission) {

		// Get User Level ID from user name
		$UserLevelID = "";
		if (is_array($this->UserLevel)) {
			foreach ($this->UserLevel as $row) {
				list($levelid, $name) = $row;
				if (strval($UserLevelName) == strval($name)) {
					$UserLevelID = $levelid;
					break;
				}
			}
		}
		if (is_array($this->UserLevelPriv) && $UserLevelID <> "") {
			$cnt = count($this->UserLevelPriv);
			for ($i = 0; $i < $cnt; $i++) {
				list($table, $levelid, $priv) = $this->UserLevelPriv[$i];
				if (strtolower($table) == strtolower(EW_PROJECT_ID . $TableName) && strval($levelid) == strval($UserLevelID)) {
					$this->UserLevelPriv[$i][2] = $priv & (127 - $UserPermission); // Remove permission
					break;
				}
			}
		}
	}

	// Load current User Level
	function LoadCurrentUserLevel($Table) {
		$this->LoadUserLevel();
		$this->setSessionUserLevel($this->CurrentUserLevelPriv($Table));
	}

	// Get current user privilege
	function CurrentUserLevelPriv($TableName) {
		if ($this->IsLoggedIn()) {
			$Priv= 0;
			foreach ($this->UserLevelID as $UserLevelID)
				$Priv |= $this->GetUserLevelPrivEx($TableName, $UserLevelID);
			return $Priv;
		} else {
			return 0;
		}
	}

	// Get User Level ID by User Level name
	function GetUserLevelID($UserLevelName) {
		if (strval($UserLevelName) == "Administrator") {
			return -1;
		} elseif ($UserLevelName <> "") {
			if (is_array($this->UserLevel)) {
				foreach ($this->UserLevel as $row) {
					list($levelid, $name) = $row;
					if (strval($name) == strval($UserLevelName))
						return $levelid;
				}
			}
		}
		return -2;
	}

	// Add User Level by name (for use with TablePermission_Loading)
	function AddUserLevel($UserLevelName) {
		if (strval($UserLevelName) == "") return;
		$UserLevelID = $this->GetUserLevelID($UserLevelName);
		$this->AddUserLevelID($UserLevelID);
	}

	// Add User Level by ID (for use with TablePermission_Loading)
	function AddUserLevelID($UserLevelID) {
		if (!is_numeric($UserLevelID)) return;
		if ($UserLevelID < -1) return;
		if (!in_array($UserLevelID, $this->UserLevelID))
			$this->UserLevelID[] = $UserLevelID;
	}

	// Delete User Level by name (for use with TablePermission_Loading)
	function DeleteUserLevel($UserLevelName) {
		if (strval($UserLevelName) == "") return;
		$UserLevelID = $this->GetUserLevelID($UserLevelName);
		$this->DeleteUserLevelID($UserLevelID);
	}

	// Delete User Level by ID (for use with TablePermission_Loading)
	function DeleteUserLevelID($UserLevelID) {
		if (!is_numeric($UserLevelID)) return;
		if ($UserLevelID < -1) return;
		$cnt = count($this->UserLevelID);
		for ($i = 0; $i < $cnt; $i++) {
			if ($this->UserLevelID[$i] == $UserLevelID) {
				unset($this->UserLevelID[$i]);
				break;
			}
		}
	}

	// User Level list
	function UserLevelList() {
		return implode(", ", $this->UserLevelID);
	}

	// User Level name list
	function UserLevelNameList() {
		$list = "";
		foreach ($this->UserLevelID as $UserLevelID) {
			if ($list <> "") $list .= ", ";
			$list .= ew_QuotedValue($this->GetUserLevelName($UserLevelID), EW_DATATYPE_STRING);
		}
		return $list;
	}

	// Get user privilege based on table name and User Level
	function GetUserLevelPrivEx($TableName, $UserLevelID) {
		if (strval($UserLevelID) == "-1") { // System Administrator
			if (defined("EW_USER_LEVEL_COMPAT")) {
				return 31; // Use old User Level values
			} else {
				return 127; // Use new User Level values (separate View/Search)
			}
		} elseif ($UserLevelID >= 0) {
			if (is_array($this->UserLevelPriv)) {
				foreach ($this->UserLevelPriv as $row) {
					list($table, $levelid, $priv) = $row;
					if (strtolower($table) == strtolower($TableName) && strval($levelid) == strval($UserLevelID)) {
						if (is_null($priv) || !is_numeric($priv)) return 0;
						return intval($priv);
					}
				}
			}
		}
		return 0;
	}

	// Get current User Level name
	function CurrentUserLevelName() {
		return $this->GetUserLevelName($this->CurrentUserLevelID());
	}

	// Get User Level name based on User Level
	function GetUserLevelName($UserLevelID) {
		if (strval($UserLevelID) == "-1") {
			return "Administrator";
		} elseif ($UserLevelID >= 0) {
			if (is_array($this->UserLevel)) {
				foreach ($this->UserLevel as $row) {
					list($levelid, $name) = $row;
					if (strval($levelid) == strval($UserLevelID))
						return $name;
				}
			}
		}
		return "";
	}

	// Display all the User Level settings (for debug only)
	function ShowUserLevelInfo() {
		echo "<pre class=\"phpmaker\">";
		print_r($this->UserLevel);
		print_r($this->UserLevelPriv);
		echo "</pre>";
		echo "<p>Current User Level ID = " . $this->CurrentUserLevelID() . "</p>";
		echo "<p>Current User Level ID List = " . $this->UserLevelList() . "</p>";
	}

	// Check privilege for List page (for menu items)
	function AllowList($TableName) {
		return ($this->CurrentUserLevelPriv($TableName) & EW_ALLOW_LIST);
	}

	// Check privilege for Add page (for Allow-Add / Detail-Add)
	function AllowAdd($TableName) {
		return ($this->CurrentUserLevelPriv($TableName) & EW_ALLOW_ADD);
	}

	// Check privilege for Edit page (for Detail-Edit)
	function AllowEdit($TableName) {
		return ($this->CurrentUserLevelPriv($TableName) & EW_ALLOW_EDIT);
	}

	// Check if user password expired
	function IsPasswordExpired() {
		return (@$_SESSION[EW_SESSION_STATUS] == "passwordexpired");
	}

	// Check if user is logging in (after changing password)
	function IsLoggingIn() {
		return (@$_SESSION[EW_SESSION_STATUS] == "loggingin");
	}

	// Check if user is logged in
	function IsLoggedIn() {
		return (@$_SESSION[EW_SESSION_STATUS] == "login");
	}

	// Check if user is system administrator
	function IsSysAdmin() {
		return (@$_SESSION[EW_SESSION_SYS_ADMIN] == 1);
	}

	// Check if user is administrator
	function IsAdmin() {
		$IsAdmin = $this->IsSysAdmin();
		return $IsAdmin;
	}

	// Save User Level to Session
	function SaveUserLevel() {

		//$_SESSION[EW_SESSION_PROJECT_ID] = CurrentProjectID(); // Save project id
		$_SESSION[EW_SESSION_AR_USER_LEVEL] = $this->UserLevel;
		$_SESSION[EW_SESSION_AR_USER_LEVEL_PRIV] = $this->UserLevelPriv;
	}

	// Load User Level from Session
	function LoadUserLevel() {
		$ProjectID = CurrentProjectID();

		//if (!is_array(@$_SESSION[EW_SESSION_AR_USER_LEVEL]) || !is_array(@$_SESSION[EW_SESSION_AR_USER_LEVEL_PRIV]) || $ProjectID <> @$_SESSION[EW_SESSION_PROJECT_ID]) { // Reload if different project
		if (!is_array(@$_SESSION[EW_SESSION_AR_USER_LEVEL]) || !is_array(@$_SESSION[EW_SESSION_AR_USER_LEVEL_PRIV])) {
			$this->SetupUserLevel();
			$this->SaveUserLevel();
		} else {
			$this->UserLevel = $_SESSION[EW_SESSION_AR_USER_LEVEL];
			$this->UserLevelPriv = $_SESSION[EW_SESSION_AR_USER_LEVEL_PRIV];
		}
	}

	// Get current user info
	function CurrentUserInfo($fldname) {
		$info = NULL;
		if (defined("EW_USER_TABLE") && !$this->IsSysAdmin()) {
			$user = $this->CurrentUserName();
			if (strval($user) <> "")
				return ew_ExecuteScalar("SELECT " . ew_QuotedName($fldname) . " FROM " . EW_USER_TABLE . " WHERE " .
					str_replace("%u", ew_AdjustSql($user), EW_USER_NAME_FILTER));
		}
		return $info;
	}

	// UserID Loading event
	function UserID_Loading() {

		//echo "UserID Loading: " . $this->CurrentUserID() . "<br>";
	}

	// UserID Loaded event
	function UserID_Loaded() {

		//echo "UserID Loaded: " . $this->UserIDList() . "<br>";
	}

	// User Level Loaded event
	function UserLevel_Loaded() {

		//$this->AddUserPermission(<UserLevelName>, <TableName>, <UserPermission>);
		//$this->DeleteUserPermission(<UserLevelName>, <TableName>, <UserPermission>);

	}

	// Table Permission Loading event
	function TablePermission_Loading() {

		//echo "Table Permission Loading: " . $this->CurrentUserLevelID() . "<br>";
	}

	// Table Permission Loaded event
	function TablePermission_Loaded() {

		//echo "Table Permission Loaded: " . $this->CurrentUserLevel . "<br>";
	}

	// User Custom Validate event
	function User_CustomValidate(&$usr, &$pwd) {

		// Enter your custom code to validate user, return TRUE if valid.
		return FALSE;
	}

	// User Validated event
	function User_Validated(&$rs) {

		// Example:
		//$_SESSION['UserEmail'] = $rs['Email'];

	}

	// User PasswordExpired event
	function User_PasswordExpired(&$rs) {

	  //echo "User_PasswordExpired";
	}
}
?>
<?php

/**
 * Common functions
 */

// Connection/Query error handler
function ew_ErrorFn($DbType, $ErrorType, $ErrorNo, $ErrorMsg, $Param1, $Param2, $Object) {
	if ($ErrorType == 'CONNECT') {
		$msg = "Failed to connect to $Param2 at $Param1. Error: " . $ErrorMsg;
	} elseif ($ErrorType == 'EXECUTE') {
		if (EW_DEBUG_ENABLED) {
			$msg = "Failed to execute SQL: $Param1. Error: " . $ErrorMsg;
		} else {
			$msg = "Failed to execute SQL. Error: " . $ErrorMsg;
		}
	} 
	ew_AddMessage($_SESSION[EW_SESSION_FAILURE_MESSAGE], $msg);
}

// Write HTTP header
function ew_Header($cache, $charset = EW_CHARSET) {
	header("Expires: Mon, 26 Jul 1997 05:00:00 GMT"); // Date in the past
	header("Last-Modified: " . gmdate("D, d M Y H:i:s") . " GMT"); // Always modified
	$export = @$_GET["export"];
	if ($cache || ew_IsHttps() && $export <> "" && $export <> "print") { // Allow cache
		header("Cache-Control: private, must-revalidate");
		header("Pragma: public");
	} else { // No cache
		header("Cache-Control: private, no-store, no-cache, must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
	}
	if ($charset <> "")
		header("Content-Type: text/html; charset=" . $charset); // Charset
}

// Get content type
function ew_ContentType($data) {
	if (substr($data, 0, 6) == "\x47\x49\x46\x38\x37\x61" || substr($data, 0, 6) == "\x47\x49\x46\x38\x39\x61") { // gif
		return "image/gif";
	} elseif (substr($data, 0, 4) == "\xFF\xD8\xFF\xE0" && substr($data, 6, 5) == "\x4A\x46\x49\x46\x00") { // jpg
		return "image/jpeg";
	} elseif (substr($data, 0, 8) == "\x89\x50\x4E\x47\x0D\x0A\x1A\x0A") { // png
		return "image/png";
	} elseif (substr($data, 0, 2) == "\x42\x4D") { // bmp
		return "image/bmp";
	} elseif (substr($data, 0, 4) == "\x25\x50\x44\x46") { // pdf
		return "application/pdf";
	} else {
		return "images";
	}
}

// Connect to database
function &ew_Connect($info = NULL) {
	$GLOBALS["ADODB_FETCH_MODE"] = ADODB_FETCH_BOTH;
	$conn = new mysqlt_driver_ADOConnection();
	$conn->debug = EW_DEBUG_ENABLED;
	$conn->debug_echo = FALSE;
	if (!$info) {
		$info = array("host" => EW_CONN_HOST, "port" => EW_CONN_PORT,
			"user" => EW_CONN_USER, "pass" => EW_CONN_PASS, "db" => EW_CONN_DB);
	}

	// Database connecting event
	Database_Connecting($info);
	$conn->port = intval($info["port"]);
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$conn->Connect($info["host"], $info["user"], $info["pass"], $info["db"]);
	if (EW_MYSQL_CHARSET <> "")
		$conn->Execute("SET NAMES '" . EW_MYSQL_CHARSET . "'");
	$conn->raiseErrorFn = '';

	// Database connected event
	Database_Connected($conn);
	return $conn;
}

// Database Connecting event
function Database_Connecting(&$info) {

	// Example:
	//var_dump($info);
	//if (ew_CurrentUserIP() == "127.0.0.1") { // testing on local PC
	//	$info["host"] = "locahost";
	//	$info["user"] = "root";
	//	$info["pass"] = "";
	//}

}

// Database Connected event
function Database_Connected(&$conn) {

	// Example:
	//$conn->Execute("Your SQL");

}

// check if allow add/delete row
function ew_AllowAddDeleteRow() {
	$ua = ew_UserAgent();
	if (count($ua) >= 4)
		return ($ua[1] != "MSIE" || $ua[3] > 5);
	else
		return TRUE;
}

// Check if HTTP POST
function ew_IsHttpPost() {
	$ct = ew_ServerVar("CONTENT_TYPE");
	if (empty($ct)) $ct = ew_ServerVar("HTTP_CONTENT_TYPE");
	return ($ct == "application/x-www-form-urlencoded");
}

// Append like operator
function ew_Like($pat) {
	if (EW_LIKE_COLLATION_FOR_MYSQL <> "") {
		return " LIKE " . $pat . " COLLATE " . EW_LIKE_COLLATION_FOR_MYSQL;
	} else {
		return " LIKE " . $pat;
	}
}

// Return multi-value search SQL
function ew_GetMultiSearchSql(&$Fld, $FldOpr, $FldVal) {
	if ($FldOpr == "IS NULL" || $FldOpr == "IS NOT NULL") {
		return $Fld->FldExpression . " " . $FldOpr;
	} else {
		$sWrk = "";
		$arVal = explode(",", $FldVal);
		foreach ($arVal as $sVal) {
			$sVal = trim($sVal);
			if ($sVal == EW_NULL_VALUE) {
				$sSql = $Fld->FldExpression . " IS NULL";
			} elseif ($sVal == EW_NOT_NULL_VALUE) {
				$sSql = $Fld->FldExpression . " IS NOT NULL";
			} elseif (EW_IS_MYSQL) {
				$sSql = "FIND_IN_SET('" . ew_AdjustSql($sVal) . "', " . $Fld->FldExpression . ")";
			} else {
				if (count($arVal) == 1 || EW_SEARCH_MULTI_VALUE_OPTION == 3) {
					$sSql = $Fld->FldExpression . " = '" . ew_AdjustSql($sVal) . "' OR " . ew_GetMultiSearchSqlPart($Fld, $sVal);
				} else {
					$sSql = ew_GetMultiSearchSqlPart($Fld, $sVal);
				}
			}
			if ($sWrk <> "") {
				if (EW_SEARCH_MULTI_VALUE_OPTION == 2) {
					$sWrk .= " AND ";
				} elseif (EW_SEARCH_MULTI_VALUE_OPTION == 3) {
					$sWrk .= " OR ";
				}
			}
			$sWrk .= "($sSql)";
		}
		return $sWrk;
	}
}

// Get multi search SQL part
function ew_GetMultiSearchSqlPart(&$Fld, $FldVal) {
	return $Fld->FldExpression . ew_Like("'" . ew_AdjustSql($FldVal) . ",%'") . " OR " .
		$Fld->FldExpression . ew_Like("'%," . ew_AdjustSql($FldVal) . ",%'") . " OR " .
		$Fld->FldExpression . ew_Like("'%," . ew_AdjustSql($FldVal) . "'");
}

// Check if float format
function ew_IsFloatFormat($FldType) {
	return ($FldType == 4 || $FldType == 5 || $FldType == 131 || $FldType == 6);
}

// Get search SQL
function ew_GetSearchSql(&$Fld, $FldVal, $FldOpr, $FldCond, $FldVal2, $FldOpr2) {
	$sSql = "";
	$virtual = ($Fld->FldIsVirtual && $Fld->FldVirtualSearch);
	$sFldExpression = ($virtual) ? $Fld->FldVirtualExpression : $Fld->FldExpression;
	$FldDataType = $Fld->FldDataType;
	if (ew_IsFloatFormat($Fld->FldType)) {
		$FldVal = ew_StrToFloat($FldVal);
		$FldVal2 = ew_StrToFloat($FldVal2);
	}
	if ($virtual)
		$FldDataType = EW_DATATYPE_STRING;
	if ($FldDataType == EW_DATATYPE_NUMBER) { // Fix wrong operator
		if ($FldOpr == "LIKE" || $FldOpr == "STARTS WITH") {
			$FldOpr = "=";
		} elseif ($FldOpr == "NOT LIKE") {
			$FldOpr = "<>";
		}
		if ($FldOpr2 == "LIKE" || $FldOpr2 == "STARTS WITH") {
			$FldOpr2 = "=";
		} elseif ($FldOpr2 == "NOT LIKE") {
			$FldOpr2 = "<>";
		}
	}
	if ($FldOpr == "BETWEEN") {
		$IsValidValue = ($FldDataType <> EW_DATATYPE_NUMBER) ||
			($FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal) && is_numeric($FldVal2));
		if ($FldVal <> "" && $FldVal2 <> "" && $IsValidValue)
			$sSql = $sFldExpression . " BETWEEN " . ew_QuotedValue($FldVal, $FldDataType) .
				" AND " . ew_QuotedValue($FldVal2, $FldDataType);
	} else {

		// Handle first value
		if ($FldVal == EW_NULL_VALUE || $FldOpr == "IS NULL") {
			$sSql = $Fld->FldExpression . " IS NULL";
		} elseif ($FldVal == EW_NOT_NULL_VALUE || $FldOpr == "IS NOT NULL") {
			$sSql = $Fld->FldExpression . " IS NOT NULL";
		} else {
			$IsValidValue = ($FldDataType <> EW_DATATYPE_NUMBER) ||
				($FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal));
			if ($FldVal <> "" && $IsValidValue && ew_IsValidOpr($FldOpr, $FldDataType)) {
				$sSql = $sFldExpression . ew_SearchString($FldOpr, $FldVal, $FldDataType);
				if ($Fld->FldDataType == EW_DATATYPE_BOOLEAN && $FldVal == $Fld->FalseValue && $FldOpr == "=")
					$sSql = "(" . $sSql . " OR " . $sFldExpression . " IS NULL)";
			}
		}

		// Handle second value
		$sSql2 = "";
		if ($FldVal2 == EW_NULL_VALUE || $FldOpr2 == "IS NULL") {
			$sSql2 = $Fld->FldExpression . " IS NULL";
		} elseif ($FldVal2 == EW_NOT_NULL_VALUE || $FldOpr2 == "IS NOT NULL") {
			$sSql2 = $Fld->FldExpression . " IS NOT NULL";
		} else {
			$IsValidValue = ($FldDataType <> EW_DATATYPE_NUMBER) ||
				($FldDataType == EW_DATATYPE_NUMBER && is_numeric($FldVal2));
			if ($FldVal2 <> "" && $IsValidValue && ew_IsValidOpr($FldOpr2, $FldDataType)) {
				$sSql2 = $sFldExpression . ew_SearchString($FldOpr2, $FldVal2, $FldDataType);
				if ($Fld->FldDataType == EW_DATATYPE_BOOLEAN && $FldVal2 == $Fld->FalseValue && $FldOpr2 == "=")
					$sSql2 = "(" . $sSql2 . " OR " . $sFldExpression . " IS NULL)";
			}
		}

		// Combine SQL
		if ($sSql2 <> "") {
			if ($sSql <> "")
				$sSql = "(" . $sSql . " " . (($FldCond == "OR") ? "OR" : "AND") . " " . $sSql2 . ")";
			else
				$sSql = $sSql2;
		}
	}
	return $sSql;
}

// Return search string
function ew_SearchString($FldOpr, $FldVal, $FldType) {
	if ($FldVal == EW_NULL_VALUE || $FldOpr == "IS NULL") {
		return " IS NULL";
	} elseif ($FldVal == EW_NOT_NULL_VALUE || $FldOpr == "IS NOT NULL") {
		return " IS NOT NULL";
	} elseif ($FldOpr == "LIKE") {
		return ew_Like(ew_QuotedValue("%$FldVal%", $FldType));
	} elseif ($FldOpr == "NOT LIKE") {
		return " NOT " . ew_Like(ew_QuotedValue("%$FldVal%", $FldType));
	} elseif ($FldOpr == "STARTS WITH") {
		return ew_Like(ew_QuotedValue("$FldVal%", $FldType));
	} else {
		return " $FldOpr " . ew_QuotedValue($FldVal, $FldType);
	}
}

// Check if valid operator
function ew_IsValidOpr($Opr, $FldType) {
	$Valid = ($Opr == "=" || $Opr == "<" || $Opr == "<=" ||
		$Opr == ">" || $Opr == ">=" || $Opr == "<>");
	if ($FldType == EW_DATATYPE_STRING || $FldType == EW_DATATYPE_MEMO || $FldType == EW_DATATYPE_XML)
		$Valid = ($Valid || $Opr == "LIKE" || $Opr == "NOT LIKE" ||	$Opr == "STARTS WITH");
	return $Valid; 
}

// Quote table/field name
function ew_QuotedName($Name) {
	$Name = str_replace(EW_DB_QUOTE_END, EW_DB_QUOTE_END . EW_DB_QUOTE_END, $Name);
	return EW_DB_QUOTE_START . $Name . EW_DB_QUOTE_END;
}

// Quote field value
function ew_QuotedValue($Value, $FldType) {
	if (is_null($Value)) return "NULL";
	switch ($FldType) {
	case EW_DATATYPE_STRING:
	case EW_DATATYPE_MEMO:
	case EW_DATATYPE_TIME:
		if (EW_REMOVE_XSS) {
			return "'" . ew_AdjustSql(ew_RemoveXSS($Value)) . "'";
		} else {
			return "'" . ew_AdjustSql($Value) . "'";
		}
	case EW_DATATYPE_XML:
		return "'" . ew_AdjustSql($Value) . "'";
	case EW_DATATYPE_BLOB:
		return "'" . addslashes($Value) . "'";
	case EW_DATATYPE_DATE:
		return "'" . ew_AdjustSql($Value) . "'";
	case EW_DATATYPE_GUID:
		return "'" . $Value . "'";
	case EW_DATATYPE_BOOLEAN:
		return "'" . $Value . "'"; // 'Y'|'N' or 'y'|'n' or '1'|'0' or 't'|'f'
	default:
		return $Value;
	}
}

// Convert different data type value
function ew_Conv($v, $t) {
	switch ($t) {
	case 2:
	case 3:
	case 16:
	case 17:
	case 18:
	case 19: // adSmallInt/adInteger/adTinyInt/adUnsignedTinyInt/adUnsignedSmallInt
		return (is_null($v)) ? NULL : intval($v);
	case 4:
	Case 5:
	case 6:
	case 131:
	case 139: // adSingle/adDouble/adCurrency/adNumeric/adVarNumeric
		return (is_null($v)) ? NULL : (float)$v;
	default:
		return (is_null($v)) ? NULL : $v;
	}
}

// Convert string to float
function ew_StrToFloat($v) {
	global $DEFAULT_THOUSANDS_SEP, $DEFAULT_DECIMAL_POINT;
	$v = str_replace(" ", "", $v);
	$v = str_replace(array($DEFAULT_THOUSANDS_SEP, $DEFAULT_DECIMAL_POINT), array("", "."), $v);
	return $v;
}

// Write message to debug file
function ew_Trace($msg) {
	$filename = "debug.txt";
	if (!$handle = fopen($filename, 'a')) exit;
	if (is_writable($filename)) fwrite($handle, $msg . "\n");
	fclose($handle);
}

// Compare values with special handling for null values
function ew_CompareValue($v1, $v2) {
	if (is_null($v1) && is_null($v2)) {
		return TRUE;
	} elseif (is_null($v1) || is_null($v2)) {
		return FALSE;

//	} elseif (is_float($v1) || is_float($v2)) {
//		return (float)$v1 == (float)$v2;

	} else {
		return ($v1 == $v2);
	}
}

// Check if boolean value is TRUE
function ew_ConvertToBool($value) {
	return ($value === TRUE || strval($value) == "1" ||
		strtolower(strval($value)) == "y" || strtolower(strval($value)) == "t");
}

// Strip slashes
function ew_StripSlashes($value) {
	if (!get_magic_quotes_gpc()) return $value;
	if (is_array($value)) { 
		return array_map('ew_StripSlashes', $value);
	} else {
		return stripslashes($value);
	}
}

// Prepend CSS class name
function ew_PrependClass(&$attr, $classname) {
	$classname = trim($classname);
	if ($classname <> "") {
		$attr = trim($attr);
		if ($attr <> "")
			$attr = " " . $attr;
		$attr = $classname . $attr;
	}
}

// Append CSS class name
function ew_AppendClass(&$attr, $classname) {
	$classname = trim($classname);
	if ($classname <> "") {
		$attr = trim($attr);
		if ($attr <> "")
			$attr .= " ";
		$attr .= $classname;
	}
}

// Add message
function ew_AddMessage(&$msg, $msgtoadd, $sep = "<br>") {
	if (strval($msgtoadd) <> "") {
		if (strval($msg) <> "")
			$msg .= $sep;
		$msg .= $msgtoadd;
	}
}

// Add filter
function ew_AddFilter(&$filter, $newfilter) {
	if (trim($newfilter) == "") return;
	if (trim($filter) <> "") {
		$filter = "(" . $filter . ") AND (" . $newfilter . ")";
	} else {
		$filter = $newfilter;
	}
}

// Add slashes for SQL
function ew_AdjustSql($val) {
	$val = addslashes(trim($val));
	return $val;
}

// Build SELECT SQL based on different sql part
function ew_BuildSelectSql($sSelect, $sWhere, $sGroupBy, $sHaving, $sOrderBy, $sFilter, $sSort) {
	$sDbWhere = $sWhere;
	ew_AddFilter($sDbWhere, $sFilter);
	$sDbOrderBy = $sOrderBy;
	if ($sSort <> "") $sDbOrderBy = $sSort;
	$sSql = $sSelect;
	if ($sDbWhere <> "") $sSql .= " WHERE " . $sDbWhere;
	if ($sGroupBy <> "") $sSql .= " GROUP BY " . $sGroupBy;
	if ($sHaving <> "") $sSql .= " HAVING " . $sHaving;
	if ($sDbOrderBy <> "") $sSql .= " ORDER BY " . $sDbOrderBy;
	return $sSql;
}

// Load recordset
function &ew_LoadRecordset($SQL) {
	global $conn;
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$rs = $conn->Execute($SQL);
	$conn->raiseErrorFn = '';
	return $rs;
}

// Execute UPDATE, INSERT, or DELETE statements
function ew_Execute($SQL) {
	global $conn;
	$conn->raiseErrorFn = 'ew_ErrorFn';
	$res = $conn->Execute($SQL);
	$conn->raiseErrorFn = '';
	return $res;
}

// Executes the query, and returns the first column of the first row
function ew_ExecuteScalar($SQL) {
	$res = FALSE;
	$rs = ew_LoadRecordset($SQL);
	if ($rs && !$rs->EOF && $rs->FieldCount() > 0) {
		$res = $rs->fields[0];
		$rs->Close();
	}
	return $res;
}

// Executes the query, and returns the first row
function ew_ExecuteRow($SQL) {
	$res = FALSE;
	$rs = ew_LoadRecordset($SQL);
	if ($rs && !$rs->EOF) {
		$res = $rs->fields;
		$rs->Close();
	}
	return $res;
}

// Write audit trail
function ew_WriteAuditTrail($pfx, $dt, $script, $usr, $action, $table, $field, $keyvalue, $oldvalue, $newvalue) {
	$usrwrk = $usr;
	if ($usrwrk == "") $usrwrk = "-1"; // Assume Administrator if no user
	if (EW_AUDIT_TRAIL_TO_DATABASE) {
		global $conn;
		$sAuditSql = "INSERT INTO " . EW_AUDIT_TRAIL_TABLE_NAME .
			" (" . ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_DATETIME) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_SCRIPT) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_USER) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_ACTION) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_TABLE) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_FIELD) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_KEYVALUE) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_OLDVALUE) . ", " .
			ew_QuotedName(EW_AUDIT_TRAIL_FIELD_NAME_NEWVALUE) . ") VALUES (" .
			ew_QuotedValue($dt, EW_DATATYPE_DATE) . ", " .
			ew_QuotedValue($script, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($usrwrk, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($action, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($table, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($field, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($keyvalue, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($oldvalue, EW_DATATYPE_STRING) . ", " .
			ew_QuotedValue($newvalue, EW_DATATYPE_STRING) . ")";
		$conn->Execute($sAuditSql);
	} else {
		$sTab = "\t";
		$sHeader = "date/time" . $sTab . "script" . $sTab .	"user" . $sTab .
			"action" . $sTab . "table" . $sTab . "field" . $sTab .
			"key value" . $sTab . "old value" . $sTab . "new value";
		$sMsg = $dt . $sTab . $script . $sTab . $usrwrk . $sTab . 
				$action . $sTab . $table . $sTab . $field . $sTab .
				$keyvalue . $sTab . $oldvalue . $sTab . $newvalue;
		$sFolder = EW_AUDIT_TRAIL_PATH;
		$sFn = $pfx . "_" . date("Ymd") . ".txt";
		$filename = ew_UploadPathEx(TRUE, $sFolder) . $sFn;
		if (file_exists($filename)) {
			$fileHandler = fopen($filename, "a+b");
		} else {
			$fileHandler = fopen($filename, "a+b");
			fwrite($fileHandler,$sHeader."\r\n");
		}
		fwrite($fileHandler, $sMsg."\r\n");
		fclose($fileHandler);
	}
}

// Unformat date time based on format type
function ew_UnFormatDateTime($dt, $namedformat) {
	if (preg_match('/^([0-9]{4})-([0][1-9]|[1][0-2])-([0][1-9]|[1|2][0-9]|[3][0|1])( (0[0-9]|1[0-9]|2[0-3]):([0-5][0-9]):([0-5][0-9]))?$/', $dt))
		return $dt;
	$dt = trim($dt);
	while (strpos($dt, "  ") !== FALSE) $dt = str_replace("  ", " ", $dt);
	$arDateTime = explode(" ", $dt);
	if (count($arDateTime) == 0) return $dt;
	if ($namedformat == 0 || $namedformat == 1 || $namedformat == 2 || $namedformat == 8) {
		$arDefFmt = explode(EW_DATE_SEPARATOR, EW_DEFAULT_DATE_FORMAT);
		if ($arDefFmt[0] == "yyyy") {
			$namedformat = 9;
		} elseif ($arDefFmt[0] == "mm") {
			$namedformat = 10;
		} elseif ($arDefFmt[0] == "dd") {
			$namedformat = 11;
		}
	}
	$arDatePt = explode(EW_DATE_SEPARATOR, $arDateTime[0]);
	if (count($arDatePt) == 3) {
		switch ($namedformat) {
		case 5:
		case 9: //yyyymmdd
			if (ew_CheckDate($arDateTime[0])) {
				list($year, $month, $day) = $arDatePt;
				break;
			} else {
				return $dt;
			}
		case 6:
		case 10: //mmddyyyy
			if (ew_CheckUSDate($arDateTime[0])) {
				list($month, $day, $year) = $arDatePt;
				break;
			} else {
				return $dt;
			}
		case 7:
		case 11: //ddmmyyyy
			if (ew_CheckEuroDate($arDateTime[0])) {
				list($day, $month, $year) = $arDatePt;
				break;
			} else {
				return $dt;
			}
		case 12:
		case 15: //yymmdd
			if (ew_CheckShortDate($arDateTime[0])) {
				list($year, $month, $day) = $arDatePt;
				$year = ew_UnformatYear($year);
				break;
			} else {
				return $dt;
			}
		case 13:
		case 16: //mmddyy
			if (ew_CheckShortUSDate($arDateTime[0])) {
				list($month, $day, $year) = $arDatePt;
				$year = ew_UnformatYear($year);
				break;
			} else {
				return $dt;
			}
		case 14:
		case 17: //ddmmyy
			if (ew_CheckShortEuroDate($arDateTime[0])) {
				list($day, $month, $year) = $arDatePt;
				$year = ew_UnformatYear($year);
				break;
			} else {
				return $dt;
			}
		default:
			return $dt;
		}
		return $year . "-" . str_pad($month, 2, "0", STR_PAD_LEFT) . "-" .
			str_pad($day, 2, "0", STR_PAD_LEFT) .
			((count($arDateTime) > 1) ? " " . $arDateTime[1] : "");
	} else {
		return $dt;
	}
}

// Format a timestamp, datetime, date or time field from MySQL
// $namedformat:
// 0 - General Date
// 1 - Long Date
// 2 - Short Date (Default)
// 3 - Long Time
// 4 - Short Time (hh:mm:ss)
// 5 - Short Date (yyyy/mm/dd)
// 6 - Short Date (mm/dd/yyyy)
// 7 - Short Date (dd/mm/yyyy)
// 8 - Short Date (Default) + Short Time (if not 00:00:00)
// 9 - Short Date (yyyy/mm/dd) + Short Time (hh:mm:ss)
// 10 - Short Date (mm/dd/yyyy) + Short Time (hh:mm:ss)
// 11 - Short Date (dd/mm/yyyy) + Short Time (hh:mm:ss)
// 12 - Short Date - 2 digit year (yy/mm/dd)
// 13 - Short Date - 2 digit year (mm/dd/yy)
// 14 - Short Date - 2 digit year (dd/mm/yy)
// 15 - Short Date - 2 digit year (yy/mm/dd) + Short Time (hh:mm:ss)
// 16 - Short Date (mm/dd/yyyy) + Short Time (hh:mm:ss)
// 17 - Short Date (dd/mm/yyyy) + Short Time (hh:mm:ss)
function ew_FormatDateTime($ts, $namedformat) {
	if (is_numeric($ts)) // timestamp
	{
		switch (strlen($ts)) {
			case 14:
				$patt = '/(\d{4})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/';
				break;
			case 12:
				$patt = '/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/';
				break;
			case 10:
				$patt = '/(\d{2})(\d{2})(\d{2})(\d{2})(\d{2})/';
				break;
			case 8:
				$patt = '/(\d{4})(\d{2})(\d{2})/';
				break;
			case 6:
				$patt = '/(\d{2})(\d{2})(\d{2})/';
				break;
			case 4:
				$patt = '/(\d{2})(\d{2})/';
				break;
			case 2:
				$patt = '/(\d{2})/';
				break;
			default:
				return $ts;
		}
		if ((isset($patt))&&(preg_match($patt, $ts, $matches)))
		{
			$year = $matches[1];
			$month = @$matches[2];
			$day = @$matches[3];
			$hour = @$matches[4];
			$min = @$matches[5];
			$sec = @$matches[6];
		}
		if (($namedformat==0)&&(strlen($ts)<10)) $namedformat = 2;
	}
	elseif (is_string($ts))
	{
		if (preg_match('/(\d{4})-(\d{2})-(\d{2}) (\d{2}):(\d{2}):(\d{2})/', $ts, $matches)) // datetime
		{
			$year = $matches[1];
			$month = $matches[2];
			$day = $matches[3];
			$hour = $matches[4];
			$min = $matches[5];
			$sec = $matches[6];
		}
		elseif (preg_match('/(\d{4})-(\d{2})-(\d{2})/', $ts, $matches)) // date
		{
			$year = $matches[1];
			$month = $matches[2];
			$day = $matches[3];
			if ($namedformat==0) $namedformat = 2;
		}
		elseif (preg_match('/(^|\s)(\d{2}):(\d{2}):(\d{2})/', $ts, $matches)) // time
		{
			$hour = $matches[2];
			$min = $matches[3];
			$sec = $matches[4];
			if (($namedformat==0)||($namedformat==1)) $namedformat = 3;
			if ($namedformat==2) $namedformat = 4;
		}
		else
		{
			return $ts;
		}
	}
	else
	{
		return $ts;
	}
	if (!isset($year)) $year = 0; // dummy value for times
	if (!isset($month)) $month = 1;
	if (!isset($day)) $day = 1;
	if (!isset($hour)) $hour = 0;
	if (!isset($min)) $min = 0;
	if (!isset($sec)) $sec = 0;
	$uts = @mktime($hour, $min, $sec, $month, $day, $year);
	if ($uts < 0 || $uts == FALSE || // failed to convert
		(intval($year) == 0 && intval($month) == 0 && intval($day) == 0)) {
		$year = substr_replace("0000", $year, -1 * strlen($year));
		$month = substr_replace("00", $month, -1 * strlen($month));
		$day = substr_replace("00", $day, -1 * strlen($day));
		$hour = substr_replace("00", $hour, -1 * strlen($hour));
		$min = substr_replace("00", $min, -1 * strlen($min));
		$sec = substr_replace("00", $sec, -1 * strlen($sec));
		$DefDateFormat = str_replace("yyyy", $year, EW_DEFAULT_DATE_FORMAT);
		$DefDateFormat = str_replace("mm", $month, $DefDateFormat);
		$DefDateFormat = str_replace("dd", $day, $DefDateFormat);
		switch ($namedformat) {
			case 0:
				return $DefDateFormat." $hour:$min:$sec";
				break;
			case 1://unsupported, return general date
				return $DefDateFormat." $hour:$min:$sec";
				break;
			case 2:
				return $DefDateFormat;
				break;
			case 3:
				if (intval($hour)==0)
					return "12:$min:$sec AM";
				elseif (intval($hour)>0 && intval($hour)<12)
					return "$hour:$min:$sec AM";
				elseif (intval($hour)==12)
					return "$hour:$min:$sec PM";
				elseif (intval($hour)>12 && intval($hour)<=23)
					return (intval($hour)-12).":$min:$sec PM";
				else
					return "$hour:$min:$sec";
				break;
			case 4:
				return "$hour:$min:$sec";
				break;
			case 5:
				return "$year". EW_DATE_SEPARATOR . "$month" . EW_DATE_SEPARATOR . "$day";
				break;
			case 6:
				return "$month". EW_DATE_SEPARATOR ."$day" . EW_DATE_SEPARATOR . "$year";
				break;
			case 7:
				return "$day" . EW_DATE_SEPARATOR ."$month" . EW_DATE_SEPARATOR . "$year";
				break;
			case 8:
				return $DefDateFormat . (($hour == 0 && $min == 0 && $sec == 0) ? "" : " $hour:$min:$sec");
				break;
			case 9:
				return "$year". EW_DATE_SEPARATOR . "$month" . EW_DATE_SEPARATOR . "$day $hour:$min:$sec";
				break;
			case 10:
				return "$month". EW_DATE_SEPARATOR ."$day" . EW_DATE_SEPARATOR . "$year $hour:$min:$sec";
				break;
			case 11:
				return "$day" . EW_DATE_SEPARATOR ."$month" . EW_DATE_SEPARATOR . "$year $hour:$min:$sec";
				break;
			case 12:
				return substr($year,-2) . EW_DATE_SEPARATOR . $month . EW_DATE_SEPARATOR . $day;
				break;
			case 13:
				return $month . EW_DATE_SEPARATOR . $day . EW_DATE_SEPARATOR . substr($year,-2);
				break;
			case 14:
				return $day . EW_DATE_SEPARATOR . $month . EW_DATE_SEPARATOR . substr($year,-2);
				break;
		}
	} else {
		$DefDateFormat = str_replace("yyyy", $year, EW_DEFAULT_DATE_FORMAT);
		$DefDateFormat = str_replace("mm", $month, $DefDateFormat);
		$DefDateFormat = str_replace("dd", $day, $DefDateFormat);
		switch ($namedformat) {
			case 0:
				return strftime($DefDateFormat." %H:%M:%S", $uts);
				break;
			case 1:
				return strftime("%A, %B %d, %Y", $uts);
				break;
			case 2:
				return strftime($DefDateFormat, $uts);
				break;
			case 3:
				return strftime("%I:%M:%S %p", $uts);
				break;
			case 4:
				return strftime("%H:%M:%S", $uts);
				break;
			case 5:
				return strftime("%Y" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%d", $uts);
				break;
			case 6:
				return strftime("%m" . EW_DATE_SEPARATOR . "%d" . EW_DATE_SEPARATOR . "%Y", $uts);
				break;
			case 7:
				return strftime("%d" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%Y", $uts);
				break;
			case 8:
				return strftime($DefDateFormat . (($hour == 0 && $min == 0 && $sec == 0) ? "" : " %H:%M:%S"), $uts);
				break;
			case 9:
				return strftime("%Y" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%d %H:%M:%S", $uts);
				break;
			case 10:
				return strftime("%m" . EW_DATE_SEPARATOR . "%d" . EW_DATE_SEPARATOR . "%Y %H:%M:%S", $uts);
				break;
			case 11:
				return strftime("%d" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%Y %H:%M:%S", $uts);
				break;
			case 12:
				return strftime("%y" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%d", $uts);
				break;
			case 13:
				return strftime("%m" . EW_DATE_SEPARATOR . "%d" . EW_DATE_SEPARATOR . "%y", $uts);
				break;
			case 14:
				return strftime("%d" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%y", $uts);
				break;
			case 15:
				return strftime("%y" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%d %H:%M:%S", $uts);
				break;
			case 16:
				return strftime("%m" . EW_DATE_SEPARATOR . "%d" . EW_DATE_SEPARATOR . "%y %H:%M:%S", $uts);
				break;
			case 17:
				return strftime("%d" . EW_DATE_SEPARATOR . "%m" . EW_DATE_SEPARATOR . "%y %H:%M:%S", $uts);
				break;
		}
	}
}

// Format currency
// Arguments: Expression [,NumDigitsAfterDecimal [,IncludeLeadingDigit [,UseParensForNegativeNumbers [,GroupDigits]]]])
// NumDigitsAfterDecimal is the numeric value indicating how many places to the
// right of the decimal are displayed
// -1 Use Default
// -2 Retain all values after decimal place
// The IncludeLeadingDigit, UseParensForNegativeNumbers, and GroupDigits
// arguments have the following settings:
// -1 True
// 0 False
// -2 Use Default
function ew_FormatCurrency($amount, $NumDigitsAfterDecimal, $IncludeLeadingDigit = -2, $UseParensForNegativeNumbers = -2, $GroupDigits = -2) {
	extract($GLOBALS["DEFAULT_LOCALE"]);

	// check $NumDigitsAfterDecimal
	if ($NumDigitsAfterDecimal == -2) { // Use all values after decimal point
		$stramt = strval($amount);
		if (strrpos($stramt, '.') >= 0)
			$frac_digits = strlen($stramt) - strrpos($stramt, '.') - 1;
		else
			$frac_digits = 0;
	} elseif ($NumDigitsAfterDecimal > -1) {
		$frac_digits = $NumDigitsAfterDecimal;
	}

	// check $UseParensForNegativeNumbers
	if ($UseParensForNegativeNumbers == -1) {
		$n_sign_posn = 0;
		if ($p_sign_posn == 0) {
			$p_sign_posn = 3;
		}
	} elseif ($UseParensForNegativeNumbers == 0) {
		if ($n_sign_posn == 0)
			$n_sign_posn = 3;
	}

	// check $GroupDigits
	if ($GroupDigits == -1) {
	} elseif ($GroupDigits == 0) {
		$mon_thousands_sep = "";
	}

	// start by formatting the unsigned number
	$number = number_format(abs($amount),
							$frac_digits,
							$mon_decimal_point,
							$mon_thousands_sep);

	// check $IncludeLeadingDigit
	if ($IncludeLeadingDigit == 0) {
		if (substr($number, 0, 2) == "0.")
			$number = substr($number, 1, strlen($number)-1);
	}
	if ($amount < 0) {
		$sign = $negative_sign;

		// "extracts" the boolean value as an integer
		$n_cs_precedes  = intval($n_cs_precedes  == true);
		$n_sep_by_space = intval($n_sep_by_space == true);
		$key = $n_cs_precedes . $n_sep_by_space . $n_sign_posn;
	} else {
		$sign = $positive_sign;
		$p_cs_precedes  = intval($p_cs_precedes  == true);
		$p_sep_by_space = intval($p_sep_by_space == true);
		$key = $p_cs_precedes . $p_sep_by_space . $p_sign_posn;
	}
	$formats = array(

	  // currency symbol is after amount
	  // no space between amount and sign

	  '000' => '(%s' . $currency_symbol . ')',
	  '001' => $sign . '%s ' . $currency_symbol,
	  '002' => '%s' . $currency_symbol . $sign,
	  '003' => '%s' . $sign . $currency_symbol,
	  '004' => '%s' . $sign . $currency_symbol,

	  // one space between amount and sign
	  '010' => '(%s ' . $currency_symbol . ')',
	  '011' => $sign . '%s ' . $currency_symbol,
	  '012' => '%s ' . $currency_symbol . $sign,
	  '013' => '%s ' . $sign . $currency_symbol,
	  '014' => '%s ' . $sign . $currency_symbol,

	  // currency symbol is before amount
	  // no space between amount and sign

	  '100' => '(' . $currency_symbol . '%s)',
	  '101' => $sign . $currency_symbol . '%s',
	  '102' => $currency_symbol . '%s' . $sign,
	  '103' => $sign . $currency_symbol . '%s',
	  '104' => $currency_symbol . $sign . '%s',

	  // one space between amount and sign
	  '110' => '(' . $currency_symbol . ' %s)',
	  '111' => $sign . $currency_symbol . ' %s',
	  '112' => $currency_symbol . ' %s' . $sign,
	  '113' => $sign . $currency_symbol . ' %s',
	  '114' => $currency_symbol . ' ' . $sign . '%s');

  // lookup the key in the above array
	return sprintf($formats[$key], $number);
}

// Format number
// Arguments: Expression [,NumDigitsAfterDecimal [,IncludeLeadingDigit [,UseParensForNegativeNumbers [,GroupDigits]]]])
// NumDigitsAfterDecimal is the numeric value indicating how many places to the
// right of the decimal are displayed
// -1 Use Default
// -2 Retain all values after decimal place
// The IncludeLeadingDigit, UseParensForNegativeNumbers, and GroupDigits
// arguments have the following settings:
// -1 True
// 0 False
// -2 Use Default
function ew_FormatNumber($amount, $NumDigitsAfterDecimal, $IncludeLeadingDigit = -2, $UseParensForNegativeNumbers = -2, $GroupDigits = -2) {
	extract($GLOBALS["DEFAULT_LOCALE"]);

	// check $NumDigitsAfterDecimal
	if ($NumDigitsAfterDecimal == -2) { // Use all values after decimal point
		$stramt = strval($amount);
		if (strrpos($stramt, '.') === FALSE)
			$frac_digits = 0;
		else
			$frac_digits = strlen($stramt) - strrpos($stramt, '.') - 1;
	} elseif ($NumDigitsAfterDecimal > -1) {
		$frac_digits = $NumDigitsAfterDecimal;
	}

	// check $UseParensForNegativeNumbers
	if ($UseParensForNegativeNumbers == -1) {
		$n_sign_posn = 0;
		if ($p_sign_posn == 0) {
			$p_sign_posn = 3;
		}
	} elseif ($UseParensForNegativeNumbers == 0) {
		if ($n_sign_posn == 0)
			$n_sign_posn = 3;
	}

	// check $GroupDigits
	if ($GroupDigits == -1) {
	} elseif ($GroupDigits == 0) {
		$thousands_sep = "";
	}

	// start by formatting the unsigned number
	$number = number_format(abs($amount),
						  $frac_digits,
						  $decimal_point,
						  $thousands_sep);

	// check $IncludeLeadingDigit
	if ($IncludeLeadingDigit == 0) {
		if (substr($number, 0, 2) == "0.")
			$number = substr($number, 1, strlen($number)-1);
	}
	if ($amount < 0) {
		$sign = $negative_sign;
		$key = $n_sign_posn;
	} else {
		$sign = $positive_sign;
		$key = $p_sign_posn;
	}
	$formats = array(
		'0' => '(%s)',
		'1' => $sign . '%s',
		'2' => $sign . '%s',
		'3' => $sign . '%s',
		'4' => $sign . '%s');

	// lookup the key in the above array
	return sprintf($formats[$key], $number);
}

// Format percent
// Arguments: Expression [,NumDigitsAfterDecimal [,IncludeLeadingDigit	[,UseParensForNegativeNumbers [,GroupDigits]]]])
// NumDigitsAfterDecimal is the numeric value indicating how many places to the
// right of the decimal are displayed
// -1 Use Default
// The IncludeLeadingDigit, UseParensForNegativeNumbers, and GroupDigits
// arguments have the following settings:
// -1 True
// 0 False
// -2 Use Default
function ew_FormatPercent($amount, $NumDigitsAfterDecimal, $IncludeLeadingDigit = -2, $UseParensForNegativeNumbers = -2, $GroupDigits = -2) {
	extract($GLOBALS["DEFAULT_LOCALE"]);

	// check $NumDigitsAfterDecimal
	if ($NumDigitsAfterDecimal > -1)
		$frac_digits = $NumDigitsAfterDecimal;

	// check $UseParensForNegativeNumbers
	if ($UseParensForNegativeNumbers == -1) {
		$n_sign_posn = 0;
		if ($p_sign_posn == 0) {
			$p_sign_posn = 3;
		}
	} elseif ($UseParensForNegativeNumbers == 0) {
		if ($n_sign_posn == 0)
			$n_sign_posn = 3;
	}

	// check $GroupDigits
	if ($GroupDigits == -1) {
	} elseif ($GroupDigits == 0) {
		$thousands_sep = "";
	}

	// start by formatting the unsigned number
	$number = number_format(abs($amount)*100,
							$frac_digits,
							$decimal_point,
							$thousands_sep);

	// check $IncludeLeadingDigit
	if ($IncludeLeadingDigit == 0) {
		if (substr($number, 0, 2) == "0.")
			$number = substr($number, 1, strlen($number)-1);
	}
	if ($amount < 0) {
		$sign = $negative_sign;
		$key = $n_sign_posn;
	} else {
		$sign = $positive_sign;
		$key = $p_sign_posn;
	}
	$formats = array(
		'0' => '(%s%%)',
		'1' => $sign . '%s%%',
		'2' => $sign . '%s%%',
		'3' => $sign . '%s%%',
		'4' => $sign . '%s%%');

	// lookup the key in the above array
	return sprintf($formats[$key], $number);
}

// Encode value for single-quoted JavaScript string
function ew_JsEncode($val) {
	$val = strval($val);
	if (EW_IS_DOUBLE_BYTE)
		$val = ew_ConvertToUtf8($val);
	$val = str_replace("\\", "\\\\", $val);
	$val = str_replace("'", "\\'", $val);
	$val = str_replace("\r\n", "<br>", $val);
	$val = str_replace("\r", "<br>", $val);
	$val = str_replace("\n", "<br>", $val);
	if (EW_IS_DOUBLE_BYTE)
		$val = ew_ConvertFromUtf8($val);
	return $val;
}

// Display field value separator
// idx - display field index (1 or 2 or 3)
// fld - field object
function ew_ValueSeparator($idx, &$fld) {
	$sep = $fld->DisplayValueSeparator;
	return (is_array($sep)) ? $sep[$idx - 1] : $sep;
}

// Delimited values separator (for select-multiple or checkbox)
// idx - zero based value index
function ew_ViewOptionSeparator($idx = -1) {
	return ", ";
}

// Move uploaded file
function ew_MoveUploadFile($srcfile, $destfile) {
	$res = move_uploaded_file($srcfile, $destfile);
	if ($res) chmod($destfile, EW_UPLOADED_FILE_MODE);
	return $res;
}

// Render repeat column table
// $rowcnt - zero based row count
function ew_RepeatColumnTable($totcnt, $rowcnt, $repeatcnt, $rendertype) {
	$sWrk = "";
	if ($rendertype == 1) { // Render control start
		if ($rowcnt == 0) $sWrk .= "<table class=\"" . EW_ITEM_TABLE_CLASSNAME . "\">";
		if ($rowcnt % $repeatcnt == 0) $sWrk .= "<tr>";
		$sWrk .= "<td>";
	} elseif ($rendertype == 2) { // Render control end
		$sWrk .= "</td>";
		if ($rowcnt % $repeatcnt == $repeatcnt - 1) {
			$sWrk .= "</tr>";
		} elseif ($rowcnt == $totcnt - 1) {
			for ($i = ($rowcnt % $repeatcnt) + 1; $i < $repeatcnt; $i++) {
				$sWrk .= "<td>&nbsp;</td>";
			}
			$sWrk .= "</tr>";
		}
		if ($rowcnt == $totcnt - 1) $sWrk .= "</table>";
	}
	return $sWrk;
}

// Truncate Memo Field based on specified length, string truncated to nearest space or CrLf
function ew_TruncateMemo($memostr, $ln, $removehtml) {
	$str = ($removehtml) ? ew_RemoveHtml($memostr) : $memostr;
	if (strlen($str) > 0 && strlen($str) > $ln) {
		$k = 0;
		while ($k >= 0 && $k < strlen($str)) {
			$i = strpos($str, " ", $k);
			$j = strpos($str, chr(10), $k);
			if ($i === FALSE && $j === FALSE) { // Not able to truncate
				return $str;
			} else {

				// Get nearest space or CrLf
				if ($i > 0 && $j > 0) {
					if ($i < $j) {
						$k = $i;
					} else {
						$k = $j;
					}
				} elseif ($i > 0) {
					$k = $i;
				} elseif ($j > 0) {
					$k = $j;
				}

				// Get truncated text
				if ($k >= $ln) {
					return substr($str, 0, $k) . "...";
				} else {
					$k++;
				}
			}
		}
	} else {
		return $str;
	}
}

// Remove HTML tags from text
function ew_RemoveHtml($str) {
	return preg_replace('/<[^>]*>/', '', strval($str));
}

// Extract JavaScript from HTML and return converted script
function ew_ExtractScript(&$html, $class = "") {
	if (!preg_match_all('/<script([^>]*)>([\s\S]*?)<\/script\s*>/i', $html, $matches, PREG_SET_ORDER))
		return "";
	$scripts = "";
	foreach ($matches as $match) {
		if (preg_match('/(\s+type\s*=\s*[\'"]*(text|application)\/(java|ecma)script[\'"]*)|^((?!\s+type\s*=).)*$/i', $match[1])) { // JavaScript
			$html = str_replace($match[0], "", $html); // Remove the script from HTML
			$scripts .= ew_HtmlElement("script", array("type" => "text/html", "class" => $class), $match[2]); // Convert script type and add CSS class, if specified
		}
	}
	return $scripts; // Return converted scripts
}

// Include PHPMailer class
include_once("includes/phpmailer521/class.phpmailer.php");

// Function to send email
function ew_SendEmail($sFrEmail, $sToEmail, $sCcEmail, $sBccEmail, $sSubject, $sMail, $sFormat, $sCharset, $sSmtpSecure = "", $sAttachmentFileName = "", $sAttachmentContent = "", $arImages = array()) {
	global $Language, $gsEmailErrDesc;
	$res = FALSE;
	$mail = new PHPMailer();
	$mail->IsSMTP(); 
	$mail->Host = EW_SMTP_SERVER;
	$mail->SMTPAuth = (EW_SMTP_SERVER_USERNAME <> "" && EW_SMTP_SERVER_PASSWORD <> "");
	$mail->Username = EW_SMTP_SERVER_USERNAME;
	$mail->Password = EW_SMTP_SERVER_PASSWORD;
	$mail->Port = EW_SMTP_SERVER_PORT;
	if ($sSmtpSecure <> "") $mail->SMTPSecure = $sSmtpSecure;
	if (preg_match('/^(.+)<([\w.%+-]+@[\w.-]+\.[A-Z]{2,6})>$/i', trim($sFrEmail), $m)) {
		$mail->From = $m[2];
		$mail->FromName = trim($m[1]);
	} else {
		$mail->From = $sFrEmail;
		$mail->FromName = $sFrEmail;
	}
	$mail->Subject = $sSubject;
	$mail->Body = $sMail;
	if ($sCharset <> "" && strtolower($sCharset) <> "iso-8859-1")
		$mail->CharSet = $sCharset;
	$sToEmail = str_replace(";", ",", $sToEmail);
	$arrTo = explode(",", $sToEmail);
	foreach ($arrTo as $sTo) {
		$mail->AddAddress(trim($sTo));
	}
	if ($sCcEmail <> "") {
		$sCcEmail = str_replace(";", ",", $sCcEmail);
		$arrCc = explode(",", $sCcEmail);
		foreach ($arrCc as $sCc) {
			$mail->AddCC(trim($sCc));
		}
	}
	if ($sBccEmail <> "") {
		$sBccEmail = str_replace(";", ",", $sBccEmail);
		$arrBcc = explode(",", $sBccEmail);
		foreach ($arrBcc as $sBcc) {
			$mail->AddBCC(trim($sBcc));
		}
	}
	if (strtolower($sFormat) == "html") {
		$mail->ContentType = "text/html";
	} else {
		$mail->ContentType = "text/plain";
	}
	if ($sAttachmentContent <> "" && $sAttachmentFileName <> "") {
		$mail->AddStringAttachment($sAttachmentContent, $sAttachmentFileName);
	} else if ($sAttachmentFileName <> "") {
		$mail->AddAttachment($sAttachmentFileName);
	}
	if (is_array($arImages)) {
		foreach ($arImages as $tmpimage) {
			$file = ew_UploadPathEx(TRUE, EW_UPLOAD_DEST_PATH) . $tmpimage;
			$cid = ew_TmpImageLnk($tmpimage, "cid");
			$mail->AddEmbeddedImage($file, $cid, $tmpimage);
		}
	}
	$res = $mail->Send();
	$gsEmailErrDesc = $mail->ErrorInfo;

	// Uncomment to debug
//		var_dump($mail); exit();

	return $res;
}

// Field data type
function ew_FieldDataType($fldtype) {
	switch ($fldtype) {
		case 20:
		case 3:
		case 2:
		case 16:
		case 4:
		case 5:
		case 131:
		case 139:
		case 6:
		case 17:
		case 18:
		case 19:
		case 21: // Numeric
			return EW_DATATYPE_NUMBER;
		case 7:
		case 133:
		case 135: // Date
		case 146: // DateTiemOffset
			return EW_DATATYPE_DATE;
		case 134: // Time
		case 145: // Time
			return EW_DATATYPE_TIME;
		case 201:
		case 203: // Memo
			return EW_DATATYPE_MEMO;
		case 129:
		case 130:
		case 200:
		case 202: // String
			return EW_DATATYPE_STRING;
		case 11: // Boolean
			return EW_DATATYPE_BOOLEAN;
		case 72: // GUID
			return EW_DATATYPE_GUID;
		case 128:
		case 204:
		case 205: // Binary
			return EW_DATATYPE_BLOB;
		case 141: // XML
			return EW_DATATYPE_XML;
		default:
			return EW_DATATYPE_OTHER;
	}
}

// Application root
function ew_AppRoot() {

	// 1. use root relative path
	if (EW_ROOT_RELATIVE_PATH <> "") {
		$Path = realpath(EW_ROOT_RELATIVE_PATH);
		$Path = str_replace("\\\\", EW_PATH_DELIMITER, $Path);
	}

	// 2. if empty, use the document root if available
	if (empty($Path))
		$Path = ew_ServerVar("APPL_PHYSICAL_PATH"); // IIS
	if (empty($Path))
		$Path = ew_ServerVar("DOCUMENT_ROOT");

	// 3. if empty, use current folder
	if (empty($Path))
		$Path = realpath(".");

	// 4. use custom path, uncomment the following line and enter your path
	// e.g. $Path = 'C:\Inetpub\wwwroot\MyWebRoot'; // Windows
	//$Path = 'enter your path here';

	if (empty($Path))
		die("Path of website root unknown.");
	return ew_IncludeTrailingDelimiter($Path, TRUE);
}

// Get path relative to application root
function ew_ServerMapPath($Path) {
	return ew_PathCombine(ew_AppRoot(), $Path, TRUE);
}

// Get path relative to a base path
function ew_PathCombine($BasePath, $RelPath, $PhyPath) {
	if (preg_match('/^(http|ftp)s?\:\/\//i', $RelPath)) // Allow remote file
		return $RelPath;
	$BasePath = ew_RemoveTrailingDelimiter($BasePath, $PhyPath);
	if ($PhyPath) {
		$Delimiter = EW_PATH_DELIMITER;
		$RelPath = str_replace('/', EW_PATH_DELIMITER, $RelPath);
		$RelPath = str_replace('\\', EW_PATH_DELIMITER, $RelPath);
	} else {
		$Delimiter = '/';
		$RelPath = str_replace('\\', '/', $RelPath);
	}
	if ($RelPath == '.' || $RelPath == '..') $RelPath .= $Delimiter;
	$p1 = strpos($RelPath, $Delimiter);
	$Path2 = "";
	while ($p1 !== FALSE) {
		$Path = substr($RelPath, 0, $p1 + 1);
		if ($Path == $Delimiter || $Path == ".$Delimiter") {

			// Skip
		} elseif ($Path == "..$Delimiter") {
			$p2 = strrpos($BasePath, $Delimiter);
			if ($p2 !== FALSE) $BasePath = substr($BasePath, 0, $p2);
		} else {
			$Path2 .= $Path;
		}
		$RelPath = substr($RelPath, $p1+1);
		if ($RelPath === FALSE) {
			$RelPath = "";
		} elseif ($RelPath == '.' || $RelPath == '..') {
			$RelPath .= $Delimiter;
		}
		$p1 = strpos($RelPath, $Delimiter);
	}
	return ew_IncludeTrailingDelimiter($BasePath, $PhyPath) . $Path2 . $RelPath;
}

// Remove the last delimiter for a path
function ew_RemoveTrailingDelimiter($Path, $PhyPath) {
	$Delimiter = ($PhyPath) ? EW_PATH_DELIMITER : '/';
	while (substr($Path, -1) == $Delimiter)
		$Path = substr($Path, 0, strlen($Path)-1);
	return $Path;
}

// Include the last delimiter for a path
function ew_IncludeTrailingDelimiter($Path, $PhyPath) {
	$Path = ew_RemoveTrailingDelimiter($Path, $PhyPath);
	$Delimiter = ($PhyPath) ? EW_PATH_DELIMITER : '/';
	return $Path . $Delimiter;
}

// Write the paths for config/debug only
function ew_WritePaths() {
	echo 'DOCUMENT_ROOT=' . ew_ServerVar("DOCUMENT_ROOT") . "<br>";
	echo 'EW_ROOT_RELATIVE_PATH=' . EW_ROOT_RELATIVE_PATH . "<br>";
	echo 'ew_AppRoot()=' . ew_AppRoot() . "<br>";
	echo 'realpath(".")=' . realpath(".") . "<br>";
	echo '__FILE__=' . __FILE__ . "<br>";
}

// Upload path
// If PhyPath is TRUE(1), return physical path on the server
// If PhyPath is FALSE(0), return relative URL
function ew_UploadPathEx($PhyPath, $DestPath) {
	if ($PhyPath) {
		$Path = ew_PathCombine(ew_AppRoot(), str_replace("/", EW_PATH_DELIMITER, $DestPath), TRUE);
	} else {
		$Path = ew_ScriptName();
		$Path = substr($Path, 0, strrpos($Path, "/"));
		$Path = ew_PathCombine($Path, EW_ROOT_RELATIVE_PATH, FALSE);
		$Path = ew_PathCombine(ew_IncludeTrailingDelimiter($Path, FALSE), $DestPath, FALSE);
	}
	return ew_IncludeTrailingDelimiter($Path, $PhyPath);
}

// Global upload path
// If PhyPath is TRUE(1), return physical path on the server
// If PhyPath is FALSE(0), return relative URL
function ew_UploadPath($PhyPath) {
	return ew_UploadPathEx($PhyPath, EW_UPLOAD_DEST_PATH);
}

// Upload file name
function ew_UploadFileNameEx($folder, $sFileName) {

	// By default, ew_UniqueFileName() is used to get an unique file name,
	// you can change the logic here

	$sOutFileName = ew_UniqueFilename($folder, $sFileName);

	// Return computed output file name
	return $sOutFileName;
}

// Generate an unique file name (filename(n).ext)
function ew_UniqueFilename($folder, $orifn) {
	if ($orifn == "") $orifn = ew_DefaultFileName();
	$orifn = str_replace(" ", "_", $orifn);
	$orifn = strtolower(basename($orifn));
	$destpath = $folder . $orifn;
	$newfn = $orifn;
	$i = 1;
	if (!file_exists($folder)) {
		if (!ew_CreateFolder($folder)) {
			die("Folder does not exist: " . $folder);
		}
	}
	while (file_exists(ew_Convert(EW_ENCODING, EW_FILE_SYSTEM_ENCODING, $destpath))) {
		$file_extension = strtolower(strrchr($orifn, "."));
		$file_name = basename($orifn, $file_extension);
		$newfn = $file_name . "($i)" . $file_extension;
		$destpath = $folder . $newfn;
		$i++;
	}
	return $newfn;
}

// Create a default file name(yyyymmddhhmmss.bin)
function ew_DefaultFileName() {
	return date("YmdHis") . ".bin";
}

// Get refer page name
function ew_ReferPage() {
	return ew_GetPageName(ew_ServerVar("HTTP_REFERER"));
}

// Get script physical folder
function ew_ScriptFolder() {
	$folder = "";
	$path = ew_ServerVar("SCRIPT_FILENAME");
	$p = strrpos($path, EW_PATH_DELIMITER);
	if ($p !== FALSE)
		$folder = substr($path, 0, $p);
	return ($folder <> "") ? $folder : realpath(".");
}

// Get a temp folder for temp file
function ew_TmpFolder() {
	$tmpfolder = NULL;
	$folders = array();
	if (EW_IS_WINDOWS) {
		$folders[] = ew_ServerVar("TEMP");
		$folders[] = ew_ServerVar("TMP");
	} else {
		if (EW_UPLOAD_TMP_PATH <> "") $folders[] = ew_AppRoot() . str_replace("/", EW_PATH_DELIMITER, EW_UPLOAD_TMP_PATH);
		$folders[] = '/tmp';
	}
	if (ini_get('upload_tmp_dir')) {
		$folders[] = ini_get('upload_tmp_dir');
	}
	foreach ($folders as $folder) {
		if (!$tmpfolder && is_dir($folder)) {
			$tmpfolder = $folder;
		}
	}

	//if ($tmpfolder) $tmpfolder = ew_IncludeTrailingDelimiter($tmpfolder, TRUE);
	return $tmpfolder;
}

// Create folder
function ew_CreateFolder($dir, $mode = 0777) {
  if (is_dir($dir) || @mkdir($dir, $mode))
		return TRUE;
  if (!ew_CreateFolder(dirname($dir), $mode))
		return FALSE;
  return @mkdir($dir, $mode);
}

// Save file
function ew_SaveFile($folder, $fn, $filedata) {
	$fn = ew_Convert(EW_ENCODING, EW_FILE_SYSTEM_ENCODING, $fn);
	$res = FALSE;
	if (ew_CreateFolder($folder)) {
		if ($handle = fopen($folder . $fn, 'w')) { // P6
			$res = fwrite($handle, $filedata);
    	fclose($handle);
		}
		if ($res)
			chmod($folder . $fn, EW_UPLOADED_FILE_MODE);
	}
	return $res;
}

// function to generate random number
function ew_Random() {
	return mt_rand();
}

// function to remove CR and LF
function ew_RemoveCrLf($s) {
	if (strlen($s) > 0) {
		$s = str_replace("\n", " ", $s);
		$s = str_replace("\r", " ", $s);
		$s = str_replace("\l", " ", $s);
	}
	return $s;
}

// Calculate field hash
function ew_GetFldHash($value) {
	return md5(ew_GetFldValueAsString($value));
}

// Get field value as string
function ew_GetFldValueAsString($value) {
	if (is_null($value)) {
		return "";
	} else {
		if (strlen($value) > 65535) { // BLOB/TEXT
			if (EW_BLOB_FIELD_BYTE_COUNT > 0) {
				return substr($value, 0, EW_BLOB_FIELD_BYTE_COUNT);
			} else {
				return $value;
			}
		} else {
			return strval($value);
		}
	}
}

// Convert byte array to binary string
function ew_BytesToStr($bytes) {
	$str = "";
	foreach ($bytes as $byte)
		$str .= chr($byte);
	return $str;
}

// Convert binary string to byte array
function ew_StrToBytes($str) {
	$cnt = strlen($str);
	$bytes = array();
	for ($i = 0; $i < $cnt; $i++)
		$bytes[] = ord($str[$i]);
	return $bytes;
}

// Create temp image file from binary data
function ew_TmpImage(&$filedata) {
	global $gTmpImages;
	$export = "";
	if (@$_GET["export"] <> "")
		$export = $_GET["export"];
	elseif (@$_POST["exporttype"] <> "")
		$export = $_POST["exporttype"];

//  $f = tempnam(ew_TmpFolder(), "tmp");
	$folder = ew_AppRoot() . EW_UPLOAD_DEST_PATH;
	$f = tempnam($folder, "tmp");
	$handle = fopen($f, 'w+');
	fwrite($handle, $filedata);
	fclose($handle);
	$info = getimagesize($f);
	switch ($info[2]) {
	case 1:
		rename($f, $f .= '.gif'); break;
	case 2:
		rename($f, $f .= '.jpg'); break;
	case 3:
		rename($f, $f .= '.png'); break;
	default:
		return "";
	}
	$tmpimage = basename($f);
	$gTmpImages[] = $tmpimage;

	//return EW_UPLOAD_DEST_PATH . $tmpimage;
	return ew_TmpImageLnk($tmpimage, $export);
}

// Delete temp images
function ew_DeleteTmpImages() {
	global $gTmpImages;
	foreach ($gTmpImages as $tmpimage)
		@unlink(ew_AppRoot() . EW_UPLOAD_DEST_PATH . $tmpimage);
}

// Get temp image link
function ew_TmpImageLnk($file, $lnktype = "") {
	if ($file == "") return "";
	if ($lnktype == "email" || $lnktype == "cid") {
		$ar = explode('.', $file);
		$lnk = implode(".", array_slice($ar, 0, count($ar)-1));
		if ($lnktype == "email") $lnk = "cid:" . $lnk;
		return $lnk;
	} else {
		return EW_UPLOAD_DEST_PATH . $file;
	}
}

// Get Hash Url
function ew_GetHashUrl($url, $hash) {
	$wrkurl = $url;
	if (ew_IsMobile()) {
		if (strpos($wrkurl, "?") !== FALSE)
			$wrkurl .= "&";
		else
			$wrkurl .= "?";
		$wrkurl .= "_row=" . $hash;
	} else {
		$wrkurl .= "#" . $hash;
	}
	return $wrkurl;
}
?>
<?php

/**
 * Form class
 */

class cFormObj {
	var $Index;

	// Constructor
	function __construct() {
		$this->Index = -1;
	}

	// Get form element name based on index
	function GetIndexedName($name) {
		if ($this->Index < 0) {
			return $name;
		} else {
			return substr($name, 0, 1) . $this->Index . substr($name, 1);
		}
	}

	// Has value for form element
	function HasValue($name) {
		$wrkname = $this->GetIndexedName($name);
		return isset($_POST[$wrkname]);
	}

	// Get value for form element
	function GetValue($name) {
		$wrkname = $this->GetIndexedName($name);
		return @$_POST[$wrkname];
	}

	// Get upload file size
	function GetUploadFileSize($name) {
		$wrkname = $this->GetIndexedName($name);
		return @$_FILES[$wrkname]['size'];
	}

	// Get upload file name
	function GetUploadFileName($name) {
		$wrkname = $this->GetIndexedName($name);
		return @$_FILES[$wrkname]['name'];
	}

	// Get file content type
	function GetUploadFileContentType($name) {
		$wrkname = $this->GetIndexedName($name);
		return @$_FILES[$wrkname]['type'];
	}

	// Get file error
	function GetUploadFileError($name) {
		$wrkname = $this->GetIndexedName($name);
		return @$_FILES[$wrkname]['error'];
	}

	// Get file temp name
	function GetUploadFileTmpName($name) {
		$wrkname = $this->GetIndexedName($name);
		return @$_FILES[$wrkname]['tmp_name'];
	}

	// Check if is upload file
	function IsUploadedFile($name) {
		$wrkname = $this->GetIndexedName($name);
		return is_uploaded_file(@$_FILES[$wrkname]["tmp_name"]);
	}

	// Get upload file data
	function GetUploadFileData($name) {
		if ($this->IsUploadedFile($name)) {
			$wrkname = $this->GetIndexedName($name);
			return file_get_contents($_FILES[$wrkname]["tmp_name"]);
		} else {
			return NULL;
		}
	}

	// Get upload image size
	function GetUploadImageSize($name) {
		$wrkname = $this->GetIndexedName($name);
		$file = @$_FILES[$wrkname]['tmp_name'];
		return (file_exists($file)) ? @getimagesize($file) : array(NULL, NULL);
	}
}
?>
<?php

/**
 * Functions for image resize
 */

// Resize binary to thumbnail
function ew_ResizeBinary($filedata, &$width, &$height, $quality) {
	return TRUE; // No resize
}

// Resize file to thumbnail file
function ew_ResizeFile($fn, $tn, &$width, &$height, $quality) {
	if (file_exists($fn)) { // Copy only
		return ($fn <> $tn) ? copy($fn, $tn) : TRUE;
	} else {
		return FALSE;
	}
}

// Resize file to binary
function ew_ResizeFileToBinary($fn, &$width, &$height, $quality) {
	return file_get_contents($fn); // Return original file content only
}
?>
<?php

/**
 * Functions for search
 */

// Highlight value based on basic search / advanced search keywords
function ew_Highlight($name, $src, $bkw, $bkwtype, $akw) {
	$outstr = "";
	if (strlen($src) > 0 && (strlen($bkw) > 0 || strlen($akw) > 0)) {
		$xx = 0;
		$yy = strpos($src, "<", $xx);
		if ($yy === FALSE) $yy = strlen($src);
		while ($yy >= 0) {
			if ($yy > $xx) {
				$wrksrc = substr($src, $xx, $yy - $xx);
				$kwstr = trim($bkw);
				if (strlen($bkw) > 0 && strlen($bkwtype) == 0) { // check for exact phase
        	$kwlist = array($kwstr); // use single array element
        } else {
					$kwlist = explode(" ", $kwstr);
				}
				if (strlen($akw) > 0)
					$kwlist[] = $akw;
				$x = 0;
				ew_GetKeyword($wrksrc, $kwlist, $x, $y, $kw);
				while ($y >= 0) {
					$outstr .= substr($wrksrc, $x, $y-$x) .
						"<span name=\"$name\" id=\"$name\" class=\"ewHighlightSearch\">" .
						substr($wrksrc, $y, strlen($kw)) . "</span>";
					$x = $y + strlen($kw);
					ew_GetKeyword($wrksrc, $kwlist, $x, $y, $kw);
				}
				$outstr .= substr($wrksrc, $x);
				$xx += strlen($wrksrc);
			}
			if ($xx < strlen($src)) {
				$yy = strpos($src, ">", $xx);
				if ($yy !== FALSE) {
					$outstr .= substr($src, $xx, $yy - $xx + 1);
					$xx = $yy + 1;
					$yy = strpos($src, "<", $xx);
					if ($yy === FALSE) $yy = strlen($src);
				} else {
					$outstr .= substr($src, $xx);
					$yy = -1;
				}
			} else {
				$yy = -1;
			}
		}	
	} else {
		$outstr = $src;
	}
	return $outstr;
}

// Get keyword
function ew_GetKeyword(&$src, &$kwlist, &$x, &$y, &$kw) {
	$thisy = -1;
	$thiskw = "";
	foreach ($kwlist as $wrkkw) {
		$wrkkw = trim($wrkkw);
		if ($wrkkw <> "") {
			if (EW_HIGHLIGHT_COMPARE) { // Case-insensitive
				$wrky = stripos($src, $wrkkw, $x);
			} else {
				$wrky = strpos($src, $wrkkw, $x);
			}
			if ($wrky !== FALSE) {
				if ($thisy == -1) {
					$thisy = $wrky;
					$thiskw = $wrkkw;
				} elseif ($wrky < $thisy) {
					$thisy = $wrky;
					$thiskw = $wrkkw;
				}
			}
		}
	}
	$y = $thisy;
	$kw = $thiskw;
}
?>
<?php

/**
 * Functions for Auto-Update fields
 */

// Get user IP
function ew_CurrentUserIP() {
	return ew_ServerVar("REMOTE_ADDR");
}

// Get current host name, e.g. "www.mycompany.com"
function ew_CurrentHost() {
	return ew_ServerVar("HTTP_HOST");
}

// Get current date in default date format
// $namedformat = -1|5|6|7 (see comment for ew_FormatDateTime)
function ew_CurrentDate($namedformat = -1) {
	if (in_array($namedformat, array(5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17))) {
		if ($namedformat == 5 || $namedformat == 9 || $namedformat == 12 || $namedformat == 15) {
			$DT = ew_FormatDateTime(date('Y-m-d'), 5);
		} elseif ($namedformat == 6 || $namedformat == 10 || $namedformat == 13 || $namedformat == 16) {
			$DT = ew_FormatDateTime(date('Y-m-d'), 6);
		} else {
			$DT = ew_FormatDateTime(date('Y-m-d'), 7);
		}
		return $DT;
	} else {
		return date('Y-m-d');
	}
}

// Get current time in hh:mm:ss format
function ew_CurrentTime() {
	return date("H:i:s");
}

// Get current date in default date format with time in hh:mm:ss format
// $namedformat = -1, 5-7, 9-11 (see comment for ew_FormatDateTime)
function ew_CurrentDateTime($namedformat = -1) {
	if (in_array($namedformat, array(5, 6, 7, 9, 10, 11, 12, 13, 14, 15, 16, 17))) {
		if ($namedformat == 5 || $namedformat == 9 || $namedformat == 12 || $namedformat == 15) {
			$DT = ew_FormatDateTime(date('Y-m-d H:i:s'), 9);
		} elseif ($namedformat == 6 || $namedformat == 10 || $namedformat == 13 || $namedformat == 16) {
			$DT = ew_FormatDateTime(date('Y-m-d H:i:s'), 10);
		} else {
			$DT = ew_FormatDateTime(date('Y-m-d H:i:s'), 11);
		}
		return $DT;
	} else {
		return date('Y-m-d H:i:s');
	}
}

// Get current date in standard format (yyyy/mm/dd)
function ew_StdCurrentDate() {
	return date('Y/m/d');
}

// Get date in standard format (yyyy/mm/dd)
function ew_StdDate($ts) {
	return date('Y/m/d', $ts);
}

// Get current date and time in standard format (yyyy/mm/dd hh:mm:ss)
function ew_StdCurrentDateTime() {
	return date('Y/m/d H:i:s');
}

// Get date/time in standard format (yyyy/mm/dd hh:mm:ss)
function ew_StdDateTime($ts) {
	return date('Y/m/d H:i:s', $ts);
}

// Encrypt password
function ew_EncryptPassword($input, $salt = '') {
	return (strval($salt) <> "") ? md5($input . $salt) . ":" . $salt : md5($input);
}

// Compare password
// Note: If salted, password must be stored in '<hashedstring>:<salt>' format
function ew_ComparePassword($pwd, $input) {
	@list($crypt, $salt) = explode(":", $pwd, 2);
	if (EW_CASE_SENSITIVE_PASSWORD) {
		if (EW_ENCRYPTED_PASSWORD) {
			return ($pwd == ew_EncryptPassword($input, @$salt));
		} else {
			return ($pwd == $input);
		}
	} else {
		if (EW_ENCRYPTED_PASSWORD) {
			return ($pwd == ew_EncryptPassword(strtolower($input), @$salt));
		} else {
			return (strtolower($pwd) == strtolower($input));
		}
	}
}

// Get connection object
function &Conn() {
	return $GLOBALS["conn"];
}

// Get security object
function &Security() {
	return $GLOBALS["Security"];
}

// Get language object
function &Language() {
	return $GLOBALS["Language"];
}

/**
 * Functions for backward compatibilty
 */

// Get current user name
function CurrentUserName() {
	global $Security;
	return (isset($Security)) ? $Security->CurrentUserName() : strval(@$_SESSION[EW_SESSION_USER_NAME]);
}

// Get current user ID
function CurrentUserID() {
	global $Security;
	return (isset($Security)) ? $Security->CurrentUserID() : strval(@$_SESSION[EW_SESSION_USER_ID]);
}

// Get current parent user ID
function CurrentParentUserID() {
	global $Security;
	return (isset($Security)) ? $Security->CurrentParentUserID() : strval(@$_SESSION[EW_SESSION_PARENT_USER_ID]);
}

// Get current user level
function CurrentUserLevel() {
	global $Security;
	return (isset($Security)) ? $Security->CurrentUserLevelID() : @$_SESSION[EW_SESSION_USER_LEVEL_ID];
}

// Get current user level list
function CurrentUserLevelList() {
	global $Security;
	return (isset($Security)) ? $Security->UserLevelList() : strval(@$_SESSION[EW_SESSION_USER_LEVEL_ID]);
}

// Get Current user info
function CurrentUserInfo($fldname) {
	global $Security;
	if (isset($Security)) {
		return $Security->CurrentUserInfo($fldname);
	} elseif (defined("EW_USER_TABLE") && !IsSysAdmin()) {
		$user = CurrentUserName();
		if (strval($user) <> "")
			return ew_ExecuteScalar("SELECT " . ew_QuotedName($fldname) . " FROM " . EW_USER_TABLE . " WHERE " .
				str_replace("%u", ew_AdjustSql($user), EW_USER_NAME_FILTER));
	}
	return NULL;
}

// Get current page ID
function CurrentPageID() {
	if (isset($GLOBALS["Page"])) {
		return $GLOBALS["Page"]->PageID;
	} elseif (defined("EW_PAGE_ID")) {
		return EW_PAGE_ID;
	}
	return "";
}

// Allow list
function AllowList($TableName) {
	global $Security;
	return $Security->AllowList($TableName);
}

// Allow add
function AllowAdd($TableName) {
	global $Security;
	return $Security->AllowAdd($TableName);
}

// Is password expired
function IsPasswordExpired() {
	global $Security;
	return (isset($Security)) ? $Security->IsPasswordExpired() : ($_SESSION[EW_SESSION_STATUS] == "passwordexpired");
}

// Is logging in
function IsLoggingIn() {
	global $Security;
	return (isset($Security)) ? $Security->IsLoggingIn() : ($_SESSION[EW_SESSION_STATUS] == "loggingin");
}

// Is logged in
function IsLoggedIn() {
	global $Security;
	return (isset($Security)) ? $Security->IsLoggedIn() : ($_SESSION[EW_SESSION_STATUS] == "login");
}

// Is system admin
function IsSysAdmin() {
	global $Security;
	return (isset($Security)) ? $Security->IsSysAdmin() : ($_SESSION[EW_SESSION_SYS_ADMIN] == 1);
}

/**
 * Functions for TEA encryption/decryption
 */

function long2str($v, $w) {
	$len = count($v);
	$s = array();
	for ($i = 0; $i < $len; $i++)
	{
		$s[$i] = pack("V", $v[$i]);
	}
	if ($w) {
		return substr(join('', $s), 0, $v[$len - 1]);
	}	else {
		return join('', $s);
	}
}

function str2long($s, $w) {
	$v = unpack("V*", $s. str_repeat("\0", (4 - strlen($s) % 4) & 3));
	$v = array_values($v);
	if ($w) {
		$v[count($v)] = strlen($s);
	}
	return $v;
}

// encrypt
function TEAencrypt($str, $key = EW_RANDOM_KEY) {
	if ($str == "") {
		return "";
	}
	$v = str2long($str, true);
	$k = str2long($key, false);
	$cntk = count($k);
	if ($cntk < 4) {
		for ($i = $cntk; $i < 4; $i++) {
			$k[$i] = 0;
		}
	}
	$n = count($v) - 1;
	$z = $v[$n];
	$y = $v[0];
	$delta = 0x9E3779B9;
	$q = floor(6 + 52 / ($n + 1));
	$sum = 0;
	while (0 < $q--) {
		$sum = int32($sum + $delta);
		$e = $sum >> 2 & 3;
		for ($p = 0; $p < $n; $p++) {
			$y = $v[$p + 1];
			$mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$z = $v[$p] = int32($v[$p] + $mx);
		}
		$y = $v[0];
		$mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
		$z = $v[$n] = int32($v[$n] + $mx);
	}
	return ew_UrlEncode(long2str($v, false));
}

// decrypt
function TEAdecrypt($str, $key = EW_RANDOM_KEY) {
	$str = ew_UrlDecode($str);
	if ($str == "") {
		return "";
	}
	$v = str2long($str, false);
	$k = str2long($key, false);
	$cntk = count($k);
	if ($cntk < 4) {
		for ($i = $cntk; $i < 4; $i++) {
			$k[$i] = 0;
		}
	}
	$n = count($v) - 1;
	$z = $v[$n];
	$y = $v[0];
	$delta = 0x9E3779B9;
	$q = floor(6 + 52 / ($n + 1));
	$sum = int32($q * $delta);
	while ($sum != 0) {
		$e = $sum >> 2 & 3;
		for ($p = $n; $p > 0; $p--) {
			$z = $v[$p - 1];
			$mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
			$y = $v[$p] = int32($v[$p] - $mx);
		}
		$z = $v[$n];
		$mx = int32((($z >> 5 & 0x07ffffff) ^ $y << 2) + (($y >> 3 & 0x1fffffff) ^ $z << 4)) ^ int32(($sum ^ $y) + ($k[$p & 3 ^ $e] ^ $z));
		$y = $v[0] = int32($v[0] - $mx);
		$sum = int32($sum - $delta);
	}
	return long2str($v, true);
}

function int32($n) {
	while ($n >= 2147483648) $n -= 4294967296;
	while ($n <= -2147483649) $n += 4294967296;
	return (int)$n;
}

function ew_UrlEncode($string) {
	$data = base64_encode($string);
	return str_replace(array('+','/','='), array('-','_','.'), $data);
}

function ew_UrlDecode($string) {
	$data = str_replace(array('-','_','.'), array('+','/','='), $string);
	return base64_decode($data);
}

// Remove XSS
function ew_RemoveXSS($val) {

	// remove all non-printable characters. CR(0a) and LF(0b) and TAB(9) are allowed 
	// this prevents some character re-spacing such as <java\0script> 
	// note that you have to handle splits with \n, \r, and \t later since they *are* allowed in some inputs 

	$val = preg_replace('/([\x00-\x08][\x0b-\x0c][\x0e-\x20])/', '', $val); 

	// straight replacements, the user should never need these since they're normal characters 
	// this prevents like <IMG SRC=&#X40&#X61&#X76&#X61&#X73&#X63&#X72&#X69&#X70&#X74&#X3A&#X61&#X6C&#X65&#X72&#X74&#X28&#X27&#X58&#X53&#X53&#X27&#X29> 

	$search = 'abcdefghijklmnopqrstuvwxyz'; 
	$search .= 'ABCDEFGHIJKLMNOPQRSTUVWXYZ'; 
	$search .= '1234567890!@#$%^&*()'; 
	$search .= '~`";:?+/={}[]-_|\'\\'; 
	for ($i = 0; $i < strlen($search); $i++) { 

	   // ;? matches the ;, which is optional 
	   // 0{0,7} matches any padded zeros, which are optional and go up to 8 chars 
	   // &#x0040 @ search for the hex values 

	   $val = preg_replace('/(&#[x|X]0{0,8}'.dechex(ord($search[$i])).';?)/i', $search[$i], $val); // with a ; 

	   // &#00064 @ 0{0,7} matches '0' zero to seven times 
	   $val = preg_replace('/(&#0{0,8}'.ord($search[$i]).';?)/', $search[$i], $val); // with a ; 
	} 

	// now the only remaining whitespace attacks are \t, \n, and \r 
	$ra = $GLOBALS["EW_XSS_ARRAY"]; // Note: Customize $EW_XSS_ARRAY in ewcfg*.php
	$found = true; // keep replacing as long as the previous round replaced something 
	while ($found == true) { 
	   $val_before = $val; 
	   for ($i = 0; $i < sizeof($ra); $i++) { 
	      $pattern = '/'; 
	      for ($j = 0; $j < strlen($ra[$i]); $j++) { 
	         if ($j > 0) { 
	            $pattern .= '('; 
	            $pattern .= '(&#[x|X]0{0,8}([9][a][b]);?)?'; 
	            $pattern .= '|(&#0{0,8}([9][10][13]);?)?'; 
	            $pattern .= ')?'; 
	         } 
	         $pattern .= $ra[$i][$j]; 
	      } 
	      $pattern .= '/i'; 
	      $replacement = substr($ra[$i], 0, 2).'<x>'.substr($ra[$i], 2); // add in <> to nerf the tag 
	      $val = preg_replace($pattern, $replacement, $val); // filter out the hex tags 
	      if ($val_before == $val) { 

	         // no replacements were made, so exit the loop 
	         $found = false; 
	      } 
	   } 
	} 
	return $val; 
}

// HTTP request by cURL
// Note: cURL must be enabled in PHP
function ew_ClientUrl($url, $postdata = "", $method = "GET") {
	if (!function_exists("curl_init"))
		die("cURL not installed.");
	$ch = curl_init();
	$method = strtoupper($method);
	if ($method == "POST") {
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $postdata);
	} elseif ($method == "GET") {
		curl_setopt($ch, CURLOPT_URL, $url . "?" . $postdata);
	}
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$res = curl_exec($ch);
	curl_close($ch);
	return $res;
}

// Calculate date difference
function ew_DateDiff($dateTimeBegin, $dateTimeEnd, $interval = "d") {
	$dateTimeBegin = strtotime($dateTimeBegin);
	if ($dateTimeBegin === -1 || $dateTimeBegin === FALSE)
		return FALSE;
	$dateTimeEnd = strtotime($dateTimeEnd);
	if($dateTimeEnd === -1 || $dateTimeEnd === FALSE)
		return FALSE;
	$dif = $dateTimeEnd - $dateTimeBegin;	
	$arBegin = getdate($dateTimeBegin);
	$dateBegin = mktime(0, 0, 0, $arBegin["mon"], $arBegin["mday"], $arBegin["year"]);
	$arEnd = getdate($dateTimeEnd);
	$dateEnd = mktime(0, 0, 0, $arEnd["mon"], $arEnd["mday"], $arEnd["year"]);
	$difDate = $dateEnd - $dateBegin;
	switch ($interval) {
		case "s": // seconds
			return $dif;
		case "n": // minutes
			return ($dif > 0) ? floor($dif/60) : ceil($dif/60);
		case "h": // hours
			return ($dif > 0) ? floor($dif/3600) : ceil($dif/3600);
		case "d": // days
			return ($difDate > 0) ? floor($difDate/86400) : ceil($difDate/86400);
		case "w": // weeks
			return ($difDate > 0) ? floor($difDate/604800) : ceil($difDate/604800);
		case "ww": // calendar weeks
			$difWeek = (($dateEnd - $arEnd["wday"]*86400) - ($dateBegin - $arBegin["wday"]*86400))/604800;
			return ($difWeek > 0) ? floor($difWeek) : ceil($difWeek);
		case "m": // months
			return (($arEnd["year"]*12 + $arEnd["mon"]) -	($arBegin["year"]*12 + $arBegin["mon"]));
		case "yyyy": // years
			return ($arEnd["year"] - $arBegin["year"]);
	}
}

// Write global debug message
function ew_DebugMsg() {
	global $gsDebugMsg;
	$msg = $gsDebugMsg;
	$gsDebugMsg = "";
	return ($msg <> "") ? "<p>" . $msg . "</p>" : "";
}

// Write global debug message
function ew_SetDebugMsg($v, $newline = TRUE) {
	global $gsDebugMsg;
	if ($newline && $gsDebugMsg <> "")
		$gsDebugMsg .= "<br>";
	$gsDebugMsg .=  $v;
}

// Init array
function &ew_InitArray($len, $value) {
	if ($len > 0)
		$ar = array_fill(0, $len, $value);
	else
		$ar = array();
	return $ar;
}

// Init 2D array
function &ew_Init2DArray($len1, $len2, $value) {
	return ew_InitArray($len1, ew_InitArray($len2, $value));
}

// Remove elements from array by an array of keys and return the removed elements as array (PHP 5 >= 5.2.0)
function ew_Splice(&$ar, $keys) {
	$arkeys = array_fill_keys($keys, 0);
	$res = array_intersect_key($ar, $arkeys);
	$ar = array_diff_key($ar, $arkeys);
	return $res;
}

// Extract elements from array by an array of keys (PHP 5 >= 5.2.0)
function ew_Slice(&$ar, $keys) {
	$arkeys = array_fill_keys($keys, 0);
	return array_intersect_key($ar, $arkeys);
}

/**
 * Validation functions
 */

// Check date format
// format: std/stdshort/us/usshort/euro/euroshort
function ew_CheckDateEx($value, $format, $sep) {
	if (strval($value) == "") return TRUE;
	while (strpos($value, "  ") !== FALSE)
		$value = str_replace("  ", " ", $value);
	$value = trim($value);
	$arDT = explode(" ", $value);
	if (count($arDT) > 0) {
		if (preg_match('/^([0-9]{4})-([0][1-9]|[1][0-2])-([0][1-9]|[1|2][0-9]|[3][0|1])$/', $arDT[0], $matches)) { // accept yyyy-mm-dd
			$sYear = $matches[1];
			$sMonth = $matches[2];
			$sDay = $matches[3];
		} else {
			$wrksep = "\\$sep";
			switch ($format) {
				case "std":
					$pattern = '/^([0-9]{4})' . $wrksep . '([0]?[1-9]|[1][0-2])' . $wrksep . '([0]?[1-9]|[1|2][0-9]|[3][0|1])$/';
					break;
				case "stdshort":
					$pattern = '/^([0-9]{2})' . $wrksep . '([0]?[1-9]|[1][0-2])' . $wrksep . '([0]?[1-9]|[1|2][0-9]|[3][0|1])$/';
					break;
				case "us":
					$pattern = '/^([0]?[1-9]|[1][0-2])' . $wrksep . '([0]?[1-9]|[1|2][0-9]|[3][0|1])' . $wrksep . '([0-9]{4})$/';
					break;
				case "usshort":
					$pattern = '/^([0]?[1-9]|[1][0-2])' . $wrksep . '([0]?[1-9]|[1|2][0-9]|[3][0|1])' . $wrksep . '([0-9]{2})$/';
					break;
				case "euro":
					$pattern = '/^([0]?[1-9]|[1|2][0-9]|[3][0|1])' . $wrksep . '([0]?[1-9]|[1][0-2])' . $wrksep . '([0-9]{4})$/';
					break;
				case "euroshort":
					$pattern = '/^([0]?[1-9]|[1|2][0-9]|[3][0|1])' . $wrksep . '([0]?[1-9]|[1][0-2])' . $wrksep . '([0-9]{2})$/';
					break;
			}
			if (!preg_match($pattern, $arDT[0])) return FALSE;
			$arD = explode($sep, $arDT[0]); // change EW_DATE_SEPARATOR to $sep
			switch ($format) {
				case "std":
				case "stdshort":
					$sYear = ew_UnformatYear($arD[0]);
					$sMonth = $arD[1];
					$sDay = $arD[2];
					break;
				case "us":
				case "usshort":
					$sYear = ew_UnformatYear($arD[2]);
					$sMonth = $arD[0];
					$sDay = $arD[1];
					break;
				case "euro":
				case "euroshort":
					$sYear = ew_UnformatYear($arD[2]);
					$sMonth = $arD[1];
					$sDay = $arD[0];
					break;
			}
		}
		if (!ew_CheckDay($sYear, $sMonth, $sDay)) return FALSE;
	}
	if (count($arDT) > 1 && !ew_CheckTime($arDT[1])) return FALSE;
	return TRUE;
}

// Unformat 2 digit year to 4 digit year
function ew_UnformatYear($yr) {
	if (strlen($yr) == 2) {
		if ($yr > EW_UNFORMAT_YEAR)
			return "19" . $yr;
		else
			return "20" . $yr;
	} else {
		return $yr;
	}
}

// Check Date format (yyyy/mm/dd)
function ew_CheckDate($value) {
	return ew_CheckDateEx($value, "std", EW_DATE_SEPARATOR);
}

// Check Date format (yy/mm/dd)
function ew_CheckShortDate($value) {
	return ew_CheckDateEx($value, "stdshort", EW_DATE_SEPARATOR);
}

// Check US Date format (mm/dd/yyyy)
function ew_CheckUSDate($value) {
	return ew_CheckDateEx($value, "us", EW_DATE_SEPARATOR);
}

// Check US Date format (mm/dd/yy)
function ew_CheckShortUSDate($value) {
	return ew_CheckDateEx($value, "usshort", EW_DATE_SEPARATOR);
}

// Check Euro Date format (dd/mm/yyyy)
function ew_CheckEuroDate($value) {
	return ew_CheckDateEx($value, "euro", EW_DATE_SEPARATOR);
}

// Check Euro Date format (dd/mm/yy)
function ew_CheckShortEuroDate($value) {
	return ew_CheckDateEx($value, "euroshort", EW_DATE_SEPARATOR);
}

// Check day
function ew_CheckDay($checkYear, $checkMonth, $checkDay) {
	$maxDay = 31;
	if ($checkMonth == 4 || $checkMonth == 6 ||	$checkMonth == 9 || $checkMonth == 11) {
		$maxDay = 30;
	} elseif ($checkMonth == 2)	{
		if ($checkYear % 4 > 0) {
			$maxDay = 28;
		} elseif ($checkYear % 100 == 0 && $checkYear % 400 > 0) {
			$maxDay = 28;
		} else {
			$maxDay = 29;
		}
	}
	return ew_CheckRange($checkDay, 1, $maxDay);
}

// Check integer
function ew_CheckInteger($value) {
	global $DEFAULT_DECIMAL_POINT;
	if (strval($value) == "") return TRUE;
	if (strpos($value, $DEFAULT_DECIMAL_POINT) !== FALSE)
		return FALSE;
	return ew_CheckNumber($value);
}

// Check number
function ew_CheckNumber($value) {
	global $DEFAULT_THOUSANDS_SEP, $DEFAULT_DECIMAL_POINT;
	if (strval($value) == "") return TRUE;
	$pat = '/^[+-]?(\d{1,3}(' . (($DEFAULT_THOUSANDS_SEP) ? '\\' . $DEFAULT_THOUSANDS_SEP . '?' : '') . '\d{3})*(\\' .
		$DEFAULT_DECIMAL_POINT . '\d+)?|\\' . $DEFAULT_DECIMAL_POINT . '\d+)$/';
	return preg_match($pat, $value);
}

// Check range
function ew_CheckRange($value, $min, $max) {
	if (strval($value) == "") return TRUE;
	if (is_int($min) || is_float($min) || is_int($max) || is_float($max)) { // Number
		if (ew_CheckNumber($value))
			$value = floatval(ew_StrToFloat($value));
	}
	if ((!is_null($min) && $value < $min) || (!is_null($max) && $value > $max))
		return FALSE;
	return TRUE;
}

// Check time
function ew_CheckTime($value) {
	if (strval($value) == "") return TRUE;
	return preg_match('/^(0[0-9]|1[0-9]|2[0-3]):[0-5][0-9](:[0-5][0-9])?$/', $value);
}

// Check US phone number
function ew_CheckPhone($value) {
	if (strval($value) == "") return TRUE;
	return preg_match('/^\(\d{3}\) ?\d{3}( |-)?\d{4}|^\d{3}( |-)?\d{3}( |-)?\d{4}$/', $value);
}

// Check US zip code
function ew_CheckZip($value) {
	if (strval($value) == "") return TRUE;
	return preg_match('/^\d{5}$|^\d{5}-\d{4}$/', $value);
}

// Check credit card
function ew_CheckCreditCard($value, $type="") {
	if (strval($value) == "") return TRUE;
	$creditcard = array("visa" => "/^4\d{3}[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
		"mastercard" => "/^5[1-5]\d{2}[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
		"discover" => "/^6011[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
		"amex" => "/^3[4,7]\d{13}$/",
		"diners" => "/^3[0,6,8]\d{12}$/",
		"bankcard" => "/^5610[ -]?\d{4}[ -]?\d{4}[ -]?\d{4}$/",
		"jcb" => "/^[3088|3096|3112|3158|3337|3528]\d{12}$/",
		"enroute" => "/^[2014|2149]\d{11}$/",
		"switch" => "/^[4903|4911|4936|5641|6333|6759|6334|6767]\d{12}$/");
	if (empty($type))	{
		$match = FALSE;
		foreach ($creditcard as $type => $pattern) {
			if (@preg_match($pattern, $value) == 1) {
				$match = TRUE;
				break;
			}
		}
		return ($match) ? ew_CheckSum($value) : FALSE;
	}	else {
		if (!preg_match($creditcard[strtolower(trim($type))], $value)) return FALSE;
		return ew_CheckSum($value);
	}
}

// Check sum
function ew_CheckSum($value) {
	$value = str_replace(array('-',' '), array('',''), $value);
	$checksum = 0;
	for ($i=(2-(strlen($value) % 2)); $i<=strlen($value); $i+=2)
		$checksum += (int)($value[$i-1]);
  for ($i=(strlen($value)%2)+1; $i <strlen($value); $i+=2) {
	  $digit = (int)($value[$i-1]) * 2;
		$checksum += ($digit < 10) ? $digit : ($digit-9);
  }
	return ($checksum % 10 == 0);
}

// Check US social security number
function ew_CheckSSC($value) {
	if (strval($value) == "") return TRUE;
	return preg_match('/^(?!000)([0-6]\d{2}|7([0-6]\d|7[012]))([ -]?)(?!00)\d\d\3(?!0000)\d{4}$/', $value);
}

// Check emails
function ew_CheckEmailList($value, $email_cnt) {
	if (strval($value) == "") return TRUE;
	$emailList = str_replace(",", ";", $value);
	$arEmails = explode(";", $emailList);
	$cnt = count($arEmails);
	if ($cnt > $email_cnt && $email_cnt > 0)
		return FALSE;
	foreach ($arEmails as $email) {
		if (!ew_CheckEmail($email))
			return FALSE;
	}
	return TRUE;
}

// Check email
function ew_CheckEmail($value) {
	if (strval($value) == "") return TRUE;
	return preg_match('/^[\w.%+-]+@[\w.-]+\.[A-Z]{2,6}$/i', trim($value));
}

// Check GUID
function ew_CheckGUID($value) {
	if (strval($value) == "") return TRUE;
	$p1 = '/^\{\w{8}-\w{4}-\w{4}-\w{4}-\w{12}\}$/';
	$p2 = '/^\w{8}-\w{4}-\w{4}-\w{4}-\w{12}$/';
	return preg_match($p1, $value) || preg_match($p2, $value);
}

// Check file extension
function ew_CheckFileType($value) {
	if (strval($value) == "") return TRUE;
	$extension = substr(strtolower(strrchr($value, ".")), 1);
	$allowExt = explode(",", strtolower(EW_UPLOAD_ALLOWED_FILE_EXT));
	return (in_array($extension, $allowExt) || trim(EW_UPLOAD_ALLOWED_FILE_EXT) == "");
}

// Check empty string
function ew_EmptyStr($value) {
	$str = strval($value);
	$str = str_replace("&nbsp;", "", $str);
	return (trim($str) == "");
}

// Check empty file
function ew_Empty($value) {
	return is_null($value);
}

// Check by preg
function ew_CheckByRegEx($value, $pattern) {
	if (strval($value) == "") return TRUE;
	return preg_match($pattern, $value);
}

// include shared code
include_once "includes/framework/ewshared9.php";
?>
