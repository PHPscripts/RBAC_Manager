<?php require "configs/config.php" ?>
<?php 

/**
 * PHPMaker 9 configuration file
 */

// Show SQL for debug
define("EW_DEBUG_ENABLED", FALSE, TRUE); // TRUE to debug
if (EW_DEBUG_ENABLED) {
	@ini_set("display_errors", "1"); // Display errors
	error_reporting(E_ALL ^ E_NOTICE); // Report all errors except E_NOTICE
}

// General
define("EW_IS_WINDOWS", (strtolower(substr(PHP_OS, 0, 3)) === 'win'), TRUE); // Is Windows OS
define("EW_IS_PHP5", (phpversion() >= "5.0.0"), TRUE); // Is PHP 5 or later
if (!EW_IS_PHP5) die("This script requires PHP 5. You are running " . phpversion() . ".");
define("EW_PATH_DELIMITER", ((EW_IS_WINDOWS) ? "\\" : "/"), TRUE); // Physical path delimiter
define("EW_ROOT_RELATIVE_PATH", ".", TRUE); // Relative path of app root
define("EW_DEFAULT_DATE_FORMAT", "yyyy/mm/dd", TRUE); // Default date format
define("EW_DEFAULT_DATE_FORMAT_ID", "9", TRUE); // Default date format
define("EW_DATE_SEPARATOR", "/", TRUE); // Date separator
define("EW_UNFORMAT_YEAR", 50, TRUE); // Unformat year
define("EW_PROJECT_NAME", "RBAC", TRUE); // Project name
define("EW_CONFIG_FILE_FOLDER", EW_PROJECT_NAME . "", TRUE); // Config file name
define("EW_PROJECT_ID", "{94C0E450-F9A8-47EE-A905-551040DB9277}", TRUE); // Project ID (GUID)
$EW_RELATED_PROJECT_ID = "";
$EW_RELATED_LANGUAGE_FOLDER = "";
define("EW_RANDOM_KEY", '9SE7QlwBN5tTnjVx', TRUE); // Random key for encryption
define("EW_PROJECT_STYLESHEET_FILENAME", "phpcss/RBAC.css", TRUE); // Project stylesheet file name
define("EW_CHARSET", "utf-8", TRUE); // Project charset
define("EW_EMAIL_CHARSET", EW_CHARSET, TRUE); // Email charset
define("EW_EMAIL_KEYWORD_SEPARATOR", "", TRUE); // Email keyword separator
$EW_COMPOSITE_KEY_SEPARATOR = ","; // Composite key separator
define("EW_HIGHLIGHT_COMPARE", TRUE, TRUE); // Highlight compare mode, TRUE(case-insensitive)|FALSE(case-sensitive)
define("EW_RECORD_DELIMITER", "\r", TRUE); // Record delimiter for Ajax
define("EW_FIELD_DELIMITER", "|", TRUE); // Field delimiter for Ajax
if (!function_exists('xml_parser_create') && !class_exists("DOMDocument")) die("This script requires PHP XML Parser or DOM.");
define('EW_USE_DOM_XML', ((!function_exists('xml_parser_create') && class_exists("DOMDocument")) || FALSE), TRUE);
if (!isset($ADODB_OUTP)) $ADODB_OUTP = 'ew_SetDebugMsg';
  
// ADODB (Access/SQL Server)
define("EW_CODEPAGE", 65001, TRUE); // Code page

/**
 * Character encoding
 * Note: If you use non English languages, you need to set character encoding
 * for some features. Make sure either iconv functions or multibyte string
 * functions are enabled and your encoding is supported. See PHP manual for
 * details.
 */
define("EW_ENCODING", "UTF-8", TRUE); // Character encoding
define("EW_IS_DOUBLE_BYTE", in_array(EW_ENCODING, array("GBK", "BIG5", "SHIFT_JIS")), TRUE); // Double-byte character encoding
define("EW_FILE_SYSTEM_ENCODING", "", TRUE); // File system encoding

// Database
define("EW_IS_MSACCESS", FALSE, TRUE); // Access
define("EW_IS_MSSQL", FALSE, TRUE); // SQL Server
define("EW_IS_MYSQL", TRUE, TRUE); // MySQL
define("EW_IS_POSTGRESQL", FALSE, TRUE); // PostgreSQL
define("EW_IS_ORACLE", FALSE, TRUE); // Oracle
if (!EW_IS_WINDOWS && (EW_IS_MSACCESS || EW_IS_MSSQL))
	die("Microsoft Access or SQL Server is supported on Windows server only.");
define("EW_DB_QUOTE_START", "`", TRUE);
define("EW_DB_QUOTE_END", "`", TRUE);
define("EW_SELECT_LIMIT", (EW_IS_MYSQL || EW_IS_POSTGRESQL), TRUE);

/**
 * MySQL charset (for SET NAMES statement, not used by default)
 * Note: Read http://dev.mysql.com/doc/refman/5.0/en/charset-connection.html
 * before using this setting.
 */
define("EW_MYSQL_CHARSET", "utf8", TRUE);

/**
 * Password (MD5 and case-sensitivity)
 * Note: If you enable MD5 password, make sure that the passwords in your
 * user table are stored as MD5 hash (32-character hexadecimal number) of the
 * clear text password. If you also use case-insensitive password, convert the
 * clear text passwords to lower case first before calculating MD5 hash.
 * Otherwise, existing users will not be able to login. MD5 hash is
 * irreversible, password will be reset during password recovery.
 */
define("EW_ENCRYPTED_PASSWORD", FALSE, TRUE); // Use encrypted password
define("EW_CASE_SENSITIVE_PASSWORD", FALSE, TRUE); // Case-sensitive password

/**
 * Remove XSS
 * Note: If you want to allow these keywords, remove them from the following EW_XSS_ARRAY at your own risks.
*/
define("EW_REMOVE_XSS", TRUE, TRUE);
$EW_XSS_ARRAY = array('javascript', 'vbscript', 'expression', '<applet', '<meta', '<xml', '<blink', '<link', '<style', '<script', '<embed', '<object', '<iframe', '<frame', '<frameset', '<ilayer', '<layer', '<bgsound', '<title', '<base',
'onabort', 'onactivate', 'onafterprint', 'onafterupdate', 'onbeforeactivate', 'onbeforecopy', 'onbeforecut', 'onbeforedeactivate', 'onbeforeeditfocus', 'onbeforepaste', 'onbeforeprint', 'onbeforeunload', 'onbeforeupdate', 'onblur', 'onbounce', 'oncellchange', 'onchange', 'onclick', 'oncontextmenu', 'oncontrolselect', 'oncopy', 'oncut', 'ondataavailable', 'ondatasetchanged', 'ondatasetcomplete', 'ondblclick', 'ondeactivate', 'ondrag', 'ondragend', 'ondragenter', 'ondragleave', 'ondragover', 'ondragstart', 'ondrop', 'onerror', 'onerrorupdate', 'onfilterchange', 'onfinish', 'onfocus', 'onfocusin', 'onfocusout', 'onhelp', 'onkeydown', 'onkeypress', 'onkeyup', 'onlayoutcomplete', 'onload', 'onlosecapture', 'onmousedown', 'onmouseenter', 'onmouseleave', 'onmousemove', 'onmouseout', 'onmouseover', 'onmouseup', 'onmousewheel', 'onmove', 'onmoveend', 'onmovestart', 'onpaste', 'onpropertychange', 'onreadystatechange', 'onreset', 'onresize', 'onresizeend', 'onresizestart', 'onrowenter', 'onrowexit', 'onrowsdelete', 'onrowsinserted', 'onscroll', 'onselect', 'onselectionchange', 'onselectstart', 'onstart', 'onstop', 'onsubmit', 'onunload');

// Session names
define("EW_SESSION_STATUS", EW_PROJECT_NAME . "_status", TRUE); // Login status
define("EW_SESSION_USER_NAME", EW_SESSION_STATUS . "_UserName", TRUE); // User name
define("EW_SESSION_USER_ID", EW_SESSION_STATUS . "_UserID", TRUE); // User ID
define("EW_SESSION_USER_PROFILE", EW_SESSION_STATUS . "_UserProfile", TRUE); // User profile
define("EW_SESSION_USER_PROFILE_USER_NAME", EW_SESSION_USER_PROFILE . "_UserName", TRUE);
define("EW_SESSION_USER_PROFILE_PASSWORD", EW_SESSION_USER_PROFILE . "_Password", TRUE);
define("EW_SESSION_USER_PROFILE_LOGIN_TYPE", EW_SESSION_USER_PROFILE . "_LoginType", TRUE);
define("EW_SESSION_USER_LEVEL_ID", EW_SESSION_STATUS . "_UserLevel", TRUE); // User Level ID
@define("EW_SESSION_USER_LEVEL", EW_SESSION_STATUS . "_UserLevelValue", TRUE); // User Level
define("EW_SESSION_PARENT_USER_ID", EW_SESSION_STATUS . "_ParentUserID", TRUE); // Parent User ID
define("EW_SESSION_SYS_ADMIN", EW_PROJECT_NAME . "_SysAdmin", TRUE); // System admin
define("EW_SESSION_PROJECT_ID", EW_PROJECT_NAME . "_ProjectID", TRUE); // User Level project ID
define("EW_SESSION_AR_USER_LEVEL", EW_PROJECT_NAME . "_arUserLevel", TRUE); // User Level array
define("EW_SESSION_AR_USER_LEVEL_PRIV", EW_PROJECT_NAME . "_arUserLevelPriv", TRUE); // User Level privilege array
define("EW_SESSION_USER_LEVEL_MSG", EW_PROJECT_NAME . "_UserLevelMessage", TRUE); // User Level Message
define("EW_SESSION_SECURITY", EW_PROJECT_NAME . "_Security", TRUE); // Security array
define("EW_SESSION_MESSAGE", EW_PROJECT_NAME . "_Message", TRUE); // System message
define("EW_SESSION_FAILURE_MESSAGE", EW_PROJECT_NAME . "_Failure_Message", TRUE); // System error message
define("EW_SESSION_SUCCESS_MESSAGE", EW_PROJECT_NAME . "_Success_Message", TRUE); // System message
define("EW_SESSION_WARNING_MESSAGE", EW_PROJECT_NAME . "_Warning_Message", TRUE); // Warning message
define("EW_SESSION_INLINE_MODE", EW_PROJECT_NAME . "_InlineMode", TRUE); // Inline mode

// Language settings
define("EW_LANGUAGE_FOLDER", "phplang/", TRUE);
$EW_LANGUAGE_FILE = array();
$EW_LANGUAGE_FILE[] = array("en", "", "english.xml");
define("EW_LANGUAGE_DEFAULT_ID", "en", TRUE);
define("EW_SESSION_LANGUAGE_ID", EW_PROJECT_NAME . "_LanguageId", TRUE); // Language ID

// Data types
define("EW_DATATYPE_NUMBER", 1, TRUE);
define("EW_DATATYPE_DATE", 2, TRUE);
define("EW_DATATYPE_STRING", 3, TRUE);
define("EW_DATATYPE_BOOLEAN", 4, TRUE);
define("EW_DATATYPE_MEMO", 5, TRUE);
define("EW_DATATYPE_BLOB", 6, TRUE);
define("EW_DATATYPE_TIME", 7, TRUE);
define("EW_DATATYPE_GUID", 8, TRUE);
define("EW_DATATYPE_XML", 9, TRUE);
define("EW_DATATYPE_OTHER", 10, TRUE);

// Row types
define("EW_ROWTYPE_VIEW", 1, TRUE); // Row type view
define("EW_ROWTYPE_ADD", 2, TRUE); // Row type add
define("EW_ROWTYPE_EDIT", 3, TRUE); // Row type edit
define("EW_ROWTYPE_SEARCH", 4, TRUE); // Row type search
define("EW_ROWTYPE_MASTER", 5, TRUE);  // Row type master record
define("EW_ROWTYPE_AGGREGATEINIT", 6, TRUE); // Row type aggregate init
define("EW_ROWTYPE_AGGREGATE", 7, TRUE); // Row type aggregate

// Table parameters
define("EW_TABLE_PREFIX", "||PHPReportMaker||", TRUE);
define("EW_TABLE_REC_PER_PAGE", "recperpage", TRUE); // Records per page
define("EW_TABLE_START_REC", "start", TRUE); // Start record
define("EW_TABLE_PAGE_NO", "pageno", TRUE); // Page number
define("EW_TABLE_BASIC_SEARCH", "psearch", TRUE); // Basic search keyword
define("EW_TABLE_BASIC_SEARCH_TYPE","psearchtype", TRUE); // Basic search type
define("EW_TABLE_ADVANCED_SEARCH", "advsrch", TRUE); // Advanced search
define("EW_TABLE_SEARCH_WHERE", "searchwhere", TRUE); // Search where clause
define("EW_TABLE_WHERE", "where", TRUE); // Table where
define("EW_TABLE_WHERE_LIST", "where_list", TRUE); // Table where (list page)
define("EW_TABLE_ORDER_BY", "orderby", TRUE); // Table order by
define("EW_TABLE_ORDER_BY_LIST", "orderby_list", TRUE); // Table order by (list page)
define("EW_TABLE_SORT", "sort", TRUE); // Table sort
define("EW_TABLE_KEY", "key", TRUE); // Table key
define("EW_TABLE_SHOW_MASTER", "showmaster", TRUE); // Table show master
define("EW_TABLE_SHOW_DETAIL", "showdetail", TRUE); // Table show detail
define("EW_TABLE_MASTER_TABLE", "mastertable", TRUE); // Master table
define("EW_TABLE_DETAIL_TABLE", "detailtable", TRUE); // Detail table
define("EW_TABLE_RETURN_URL", "return", TRUE); // Return URL
define("EW_TABLE_EXPORT_RETURN_URL", "exportreturn", TRUE); // Export return URL
define("EW_TABLE_GRID_ADD_ROW_COUNT", "gridaddcnt", TRUE); // Grid add row count

// Audit Trail
define("EW_AUDIT_TRAIL_TO_DATABASE", FALSE, TRUE); // Write audit trail to DB
define("EW_AUDIT_TRAIL_TABLE_NAME", "", TRUE); // Audit trail table name
define("EW_AUDIT_TRAIL_FIELD_NAME_DATETIME", "", TRUE); // Audit trail DateTime field name
define("EW_AUDIT_TRAIL_FIELD_NAME_SCRIPT", "", TRUE); // Audit trail Script field name
define("EW_AUDIT_TRAIL_FIELD_NAME_USER", "", TRUE); // Audit trail User field name
define("EW_AUDIT_TRAIL_FIELD_NAME_ACTION", "", TRUE); // Audit trail Action field name
define("EW_AUDIT_TRAIL_FIELD_NAME_TABLE", "", TRUE); // Audit trail Table field name
define("EW_AUDIT_TRAIL_FIELD_NAME_FIELD", "", TRUE); // Audit trail Field field name
define("EW_AUDIT_TRAIL_FIELD_NAME_KEYVALUE", "", TRUE); // Audit trail Key Value field name
define("EW_AUDIT_TRAIL_FIELD_NAME_OLDVALUE", "", TRUE); // Audit trail Old Value field name
define("EW_AUDIT_TRAIL_FIELD_NAME_NEWVALUE", "", TRUE); // Audit trail New Value field name

// User level constants
define("EW_ALLOW_ADD", 1, TRUE); // Add
define("EW_ALLOW_DELETE", 2, TRUE); // Delete
define("EW_ALLOW_EDIT", 4, TRUE); // Edit
@define("EW_ALLOW_LIST", 8, TRUE); // List
if (defined("EW_USER_LEVEL_COMPAT")) {
	define("EW_ALLOW_VIEW", 8, TRUE); // View
	define("EW_ALLOW_SEARCH", 8, TRUE); // Search
} else {
	define("EW_ALLOW_VIEW", 32, TRUE); // View
	define("EW_ALLOW_SEARCH", 64, TRUE); // Search
}
@define("EW_ALLOW_REPORT", 8, TRUE); // Report
@define("EW_ALLOW_ADMIN", 16, TRUE); // Admin

// Hierarchical User ID
@define("EW_USER_ID_IS_HIERARCHICAL", TRUE, TRUE); // Change to FALSE to show one level only

// Use subquery for master/detail
define("EW_USE_SUBQUERY_FOR_MASTER_USER_ID", FALSE, TRUE);

// User table filters
// User Profile Constants

define("EW_USER_PROFILE_KEY_SEPARATOR", "", TRUE);
define("EW_USER_PROFILE_FIELD_SEPARATOR", "", TRUE);
define("EW_USER_PROFILE_SESSION_ID", "SessionID", TRUE);
define("EW_USER_PROFILE_LAST_ACCESSED_DATE_TIME", "LastAccessedDateTime", TRUE);
define("EW_USER_PROFILE_SESSION_TIMEOUT", 20, TRUE);
define("EW_USER_PROFILE_LOGIN_RETRY_COUNT", "LoginRetryCount", TRUE);
define("EW_USER_PROFILE_LAST_BAD_LOGIN_DATE_TIME", "LastBadLoginDateTime", TRUE);
define("EW_USER_PROFILE_MAX_RETRY", 3, TRUE);
define("EW_USER_PROFILE_RETRY_LOCKOUT", 20, TRUE);
define("EW_USER_PROFILE_LAST_PASSWORD_CHANGED_DATE", "LastPasswordChangedDate", TRUE);
define("EW_USER_PROFILE_PASSWORD_EXPIRE", 90, TRUE);

// Email
define("EW_SMTP_SERVER", "localhost", TRUE); // SMTP server
define("EW_SMTP_SERVER_PORT", 25, TRUE); // SMTP server port
define("EW_SMTP_SECURE_OPTION", "", TRUE);
define("EW_SMTP_SERVER_USERNAME", "", TRUE); // SMTP server user name
define("EW_SMTP_SERVER_PASSWORD", "", TRUE); // SMTP server password
define("EW_SENDER_EMAIL", "", TRUE); // Sender email address
define("EW_RECIPIENT_EMAIL", "", TRUE); // Recipient email address
define("EW_MAX_EMAIL_RECIPIENT", 3, TRUE);
define("EW_MAX_EMAIL_SENT_COUNT", 3, TRUE);
define("EW_EXPORT_EMAIL_COUNTER", EW_SESSION_STATUS . "_EmailCounter", TRUE);

// File upload
define("EW_UPLOAD_DEST_PATH", "", TRUE); // Upload destination path (relative to app root)
define("EW_UPLOAD_ALLOWED_FILE_EXT", "gif,jpg,jpeg,bmp,png,doc,xls,pdf,zip", TRUE); // Allowed file extensions
define("EW_IMAGE_ALLOWED_FILE_EXT", "gif,jpg,png,bmp", TRUE); // Allowed file extensions for images
define("EW_MAX_FILE_SIZE", 2000000, TRUE); // Max file size
define("EW_THUMBNAIL_DEFAULT_WIDTH", 0, TRUE); // Thumbnail default width
define("EW_THUMBNAIL_DEFAULT_HEIGHT", 0, TRUE); // Thumbnail default height
define("EW_THUMBNAIL_DEFAULT_QUALITY", 75, TRUE); // Thumbnail default qualtity (JPEG)
define("EW_UPLOADED_FILE_MODE", 0666, TRUE); // Uploaded file mode
define("EW_UPLOAD_TMP_PATH", "", TRUE); // User upload temp path (relative to app root) e.g. "tmp/"

// Audit trail
define("EW_AUDIT_TRAIL_PATH", "", TRUE); // Audit trail path (relative to app root)

// Export records
define("EW_EXPORT_ALL", TRUE, TRUE); // Export all records
define("EW_EXPORT_ALL_TIME_LIMIT", 120, TRUE); // Export all records time limit
define("EW_XML_ENCODING", "utf-8", TRUE); // Encoding for Export to XML
define("EW_EXPORT_ORIGINAL_VALUE", FALSE, TRUE);
define("EW_EXPORT_FIELD_CAPTION", FALSE, TRUE); // TRUE to export field caption
define("EW_EXPORT_CSS_STYLES", TRUE, TRUE); // TRUE to export CSS styles
define("EW_EXPORT_MASTER_RECORD", TRUE, TRUE); // TRUE to export master record
define("EW_EXPORT_MASTER_RECORD_FOR_CSV", FALSE, TRUE); // TRUE to export master record for CSV
$EW_EXPORT = array(
	"email" => "cExportEmail",
	"html" => "cExportHtml",
	"word" => "cExportWord",
	"excel" => "cExportExcel",
	"pdf" => "cExportPdf",
	"csv" => "cExportCsv",
	"xml" => "cExportXml"
);

// Export records for reports
$EW_EXPORT_REPORT = array(
	"print" => "ExportReportHtml",
	"html" => "ExportReportHtml",
	"word" => "ExportReportWord",
	"excel" => "ExportReportExcel"
);

// Use token in URL (reserved, not used, do NOT change!)
define("EW_USE_TOKEN_IN_URL", FALSE, TRUE);

// Use ILIKE for PostgreSql
define("EW_USE_ILIKE_FOR_POSTGRESQL", TRUE, TRUE);

// Use collation for MySQL
define("EW_LIKE_COLLATION_FOR_MYSQL", "", TRUE);

// Use collation for MsSQL
define("EW_LIKE_COLLATION_FOR_MSSQL", "", TRUE);

// Null / Not Null values
define("EW_NULL_VALUE", "##null##", TRUE);
define("EW_NOT_NULL_VALUE", "##notnull##", TRUE);

/**
 * Search multi value option
 * 1 - no multi value
 * 2 - AND all multi values
 * 3 - OR all multi values
*/
define("EW_SEARCH_MULTI_VALUE_OPTION", 3, TRUE);

// Validate option
define("EW_CLIENT_VALIDATE", TRUE, TRUE);
define("EW_SERVER_VALIDATE", FALSE, TRUE);

// Blob field byte count for hash value calculation
define("EW_BLOB_FIELD_BYTE_COUNT", 200, TRUE);

// Auto suggest max entries
define("EW_AUTO_SUGGEST_MAX_ENTRIES", 10, TRUE);

// Checkbox and radio button groups
define("EW_ITEM_TEMPLATE_CLASSNAME", "ewTemplate", TRUE);
define("EW_ITEM_TABLE_CLASSNAME", "ewItemTable", TRUE);

// Use mobile menu
$EW_USE_MOBILE_MENU = TRUE;

/**
 * Numeric and monetary formatting options
 * Note: DO NOT CHANGE THE FOLLOWING $DEFAULT_* VARIABLES!
 * If you want to use custom settings, customize the language file,
 * set "use_system_locale" to "0" to override localeconv and customize the
 * phrases under the <locale> node for ew_FormatCurrency/Number/Percent functions
 * Also read http://www.php.net/localeconv for description of the constants
*/
$DEFAULT_DECIMAL_POINT = ".";
$DEFAULT_THOUSANDS_SEP = ",";
$DEFAULT_CURRENCY_SYMBOL = "$";
$DEFAULT_MON_DECIMAL_POINT = ".";
$DEFAULT_MON_THOUSANDS_SEP = ",";
$DEFAULT_POSITIVE_SIGN = "";
$DEFAULT_NEGATIVE_SIGN = "-";
$DEFAULT_FRAC_DIGITS = 2;
$DEFAULT_P_CS_PRECEDES = TRUE;
$DEFAULT_P_SEP_BY_SPACE = FALSE;
$DEFAULT_N_CS_PRECEDES = TRUE;
$DEFAULT_N_SEP_BY_SPACE = FALSE;
$DEFAULT_P_SIGN_POSN = 3;
$DEFAULT_N_SIGN_POSN = 3;
$DEFAULT_LOCALE = array("decimal_point" => &$DEFAULT_DECIMAL_POINT,
	"thousands_sep" => &$DEFAULT_THOUSANDS_SEP,
	"currency_symbol" => &$DEFAULT_CURRENCY_SYMBOL,
	"mon_decimal_point" => &$DEFAULT_MON_DECIMAL_POINT,
	"mon_thousands_sep" => &$DEFAULT_MON_THOUSANDS_SEP,
	"positive_sign" => &$DEFAULT_POSITIVE_SIGN,
	"negative_sign" => &$DEFAULT_NEGATIVE_SIGN,
	"frac_digits" => &$DEFAULT_FRAC_DIGITS,
	"p_cs_precedes" => &$DEFAULT_P_CS_PRECEDES,
	"p_sep_by_space" => &$DEFAULT_P_SEP_BY_SPACE,
	"n_cs_precedes" => &$DEFAULT_N_CS_PRECEDES,
	"n_sep_by_space" => &$DEFAULT_N_SEP_BY_SPACE,
	"p_sign_posn" => &$DEFAULT_P_SIGN_POSN,
	"n_sign_posn" => &$DEFAULT_N_SIGN_POSN);
$DEFAULT_TIME_ZONE = "GMT";

// Cookies
define("EW_COOKIE_EXPIRY_TIME", time() + 365*24*60*60, TRUE); // Change cookie expiry time here

//
// Global variables
//

if (!isset($conn)) {

	// Common objects
	$conn = NULL; // Connection
	$Page = NULL; // Page
	$Table = NULL; // Main table
	$MasterPage = NULL; // Master page

//	$MasterTable = NULL; // Master table
	$Language = NULL; // Language
	$Security = NULL; // Security
	$UserProfile = NULL; // User profile
	$objForm = NULL; // Form

	// Current language
	$gsLanguage = "";

	// Used by ValidateForm/ValidateSearch
	$gsFormError = ""; // Form error message
	$gsSearchError = ""; // Search form error message

	// Used by *master.php
	$gsMasterReturnUrl = "";

	// Used by header.php, export checking
	$gsExport = "";
	$gsExportFile = "";

	// Used by header.php/footer.php, skip header/footer checking
	$gbSkipHeaderFooter = FALSE;
	$gbOldSkipHeaderFooter = $gbSkipHeaderFooter;

	// Email error message
	$gsEmailErrDesc = "";

	// Debug message
	$gsDebugMsg = "";

	// Debug timer
	$gTimer = NULL;

	// Keep temp images name for PDF export for delete
	$gTmpImages = array();
}

// User agent
$UserAgent = NULL;

// Mobile detect
$MobileDetect = NULL;
?>
<?php

// Menu
define("EW_MENUBAR_CLASSNAME", "ewMenuBarVertical", TRUE);
define("EW_MENUBAR_ITEM_CLASSNAME", "", TRUE);
define("EW_MENUBAR_ITEM_LABEL_CLASSNAME", "", TRUE);
define("EW_MENU_CLASSNAME", "ewMenuBarVertical", TRUE);
define("EW_MENU_ITEM_CLASSNAME", "", TRUE);
define("EW_MENU_ITEM_LABEL_CLASSNAME", "", TRUE);
?>
<?php
define("EW_PDF_STYLESHEET_FILENAME", "phpcss/ewpdf.css", TRUE); // export PDF CSS styles
define("EW_PDF_MEMORY_LIMIT", "128M", TRUE); // Memory limit
define("EW_PDF_TIME_LIMIT", 120, TRUE); // Time limit
?>
