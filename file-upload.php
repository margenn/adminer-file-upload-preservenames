<?php

/** Edit fields ending with "_path" by <input type="file"> and link to the uploaded files from select
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, https://www.vrana.cz/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
* @version 1.0.0 beta
* Modified by Marcelo Gennari: Keep the original filename and store files in individual sub-folders. One for each table.
*/
class AdminerFileUpload {
	/** @access protected */
	var $uploadPath, $displayPath, $extensions;

	/**
	* @param string prefix for uploading data (create writable subdirectory for each table)
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string regular expression with allowed file extensions
	* @param string fields terminated with this regex activates this plugin
	*/
	function __construct($uploadPath = "../static/data/", $displayPath = null
			, $extensions = "[a-zA-Z0-9]+", $fieldSufix = '_path') {
		$this->uploadPath = $uploadPath;
		$this->displayPath = ($displayPath !== null ? $displayPath : $uploadPath);
		$this->extensions = '~\.' . $extensions . '$~';
		$this->fieldsufix = '~(.*)' . $fieldSufix . '$~';
	}

	function editInput($table, $field, $attrs, $value) {
		if (preg_match($this->fieldsufix, $field["field"])) {
			$rtn = '';
			if ( ! empty($value)) {
				$rtn .= "<div><a href=\"$this->displayPath$table/$value\">$value</a></div>";
			}
			$rtn .= "<input type='file'$attrs>";
			return $rtn;
		}
	}

	function processInput($field, $value, $function = "") {
		if (preg_match($this->fieldsufix, $field["field"], $regs)) {
			if ($_GET["edit"] != "") {
				$table = ($_GET["edit"] != "" ? $_GET["edit"] : $_GET["select"]);
				$filename = $_FILES['fields']['name']["$field[field]"];
				$uploadfullpath = "$this->uploadPath$table";
				if ($_FILES['fields']['error']["$field[field]"] || !preg_match($this->extensions, $filename, $regs2)) {
					return false;
				}
				if (!file_exists($uploadfullpath)) {
					$mkdirStatus = mkdir($uploadfullpath, 0770, true);
					if (!$mkdirStatus) return false;
				}
				if (!move_uploaded_file($_FILES['fields']['tmp_name']["$field[field]"], "$uploadfullpath/$filename")) {
					return false;
				}
			} else {
				$filename = $value;
			}
			return q($filename);
		}
	}

	function selectVal($val, &$link, $field, $original) {
		if ($val != "" && preg_match($this->fieldsufix, $field["field"], $regs)) {
			$link = "$this->displayPath$_GET[select]/$val";
		}
	}

}
