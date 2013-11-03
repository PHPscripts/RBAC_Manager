<?php
if (session_id() == "") session_start(); // Initialize Session data
ob_start(); // Turn on output buffering
?>
<?php include_once "ewcfg9.php" ?>
<?php include_once "ewmysql9.php" ?>
<?php include_once "phpfn9.php" ?>
<?php

// Get resize parameters
$resize = (@$_GET["resize"] <> "");
$width = (@$_GET["width"] <> "") ? $_GET["width"] : 0;
$height = (@$_GET["height"] <> "") ? $_GET["height"] : 0;
if (@$_GET["width"] == "" && @$_GET["height"] == "") {
	$width = EW_THUMBNAIL_DEFAULT_WIDTH;
	$height = EW_THUMBNAIL_DEFAULT_HEIGHT;
}
$quality = (@$_GET["quality"] <> "") ? $_GET["quality"] : EW_THUMBNAIL_DEFAULT_QUALITY;

// Resize image from physical file
if (@$_GET["fn"] <> "") {
	$fn = ew_StripSlashes($_GET["fn"]);
	$fn = str_replace("\0", "", $fn);
	$fn = ew_PathCombine(ew_AppRoot(), $fn, TRUE); // P7
	if (file_exists($fn) || fopen($fn, "rb") !== FALSE) { // Allow remote file
		$pathinfo = pathinfo($fn);
		$ext = strtolower($pathinfo['extension']);
		if (in_array($ext, explode(',', EW_IMAGE_ALLOWED_FILE_EXT))) {
			$size = getimagesize($fn);
			if ($size)
				header("Content-type: {$size['mime']}");
			echo ew_ResizeFileToBinary($fn, $width, $height, $quality);
		}
	}
	exit();
} else { // Display image from Session
	if (@$_GET["tbl"] <> "") {
		$tbl = $_GET["tbl"];
	} else {
		exit();
	}
	if (@$_GET["fld"] <> "") {
		$fld = $_GET["fld"];
	} else {
		exit();
	}
	if (@$_GET["idx"] <> "") {
		$idx = $_GET["idx"];
	} else {
		$idx = -1;
	}
	if (@$_GET["db"] <> "") {
		$restoreDb = TRUE;
	} else {
		$restoreDb = FALSE;
	}
	if (@$_GET["file"] <> "") {
		$restoreDbFile = TRUE;
	} else {
		$restoreDbFile = FALSE;
	}

	// Get session
	$obj = new cUpload($tbl, $fld);
	$obj->Index = $idx;
	if ($restoreDb) {
		$obj->RestoreDbFromSession();
		$obj->Value = $obj->DbValue;
	} else {
		$obj->RestoreFromSession();
	}
	if (is_null($obj->Value))
		exit();

	// Restore db file
	if ($restoreDbFile) {
		$fn = ew_UploadPathEx(TRUE, @$_GET["path"]) . $obj->Value;
		if (file_exists($fn)) {
			$pathinfo = pathinfo($fn);
			$ext = strtolower($pathinfo['extension']);
			if (in_array($ext, explode(',', EW_IMAGE_ALLOWED_FILE_EXT))) {
				$size = getimagesize($fn);
				if ($size)
					header("Content-type: {$size['mime']}");
				echo ew_ResizeFileToBinary($fn, $width, $height, $quality);
			}
		}
	} else {

		// If not IE, get the content type
		if (strpos(ew_ServerVar("HTTP_USER_AGENT"), "MSIE") === FALSE) {
			$tmpfname = tempnam(ew_TmpFolder(), 'tmp');
			$handle = fopen($tmpfname, "w");
			fwrite($handle, $obj->Value);
			fclose($handle);
			$size = getimagesize($tmpfname);
			if ($size)
				header("Content-type: {$size['mime']}");
			@unlink($tmpfname);
		}
		if ($resize)
			$obj->Resize($width, $height, $quality);
		echo $obj->Value;
		exit();
	}
}
?>
