<?php

/** Edit fields ending with "_path" by <input type="file"> and link to the uploaded files from select
 * @link https://www.adminer.org/plugins/#use
 * @author Jakub Vrana, https://www.vrana.cz/
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 *
 * @author Marcelo Gennari, https://gren.com.br
 * Modifications: Keep the original filename and create sub-folders with the same structure of the database: $uploadPath / db / table / field / $filename
 * @version 1.1.0
*/

class AdminerFileUpload {

	/** @access protected */
	var $uploadPath, $displayPath, $extensions;

	/**
	* @param string prefix for uploading data
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string regular expression with allowed file extensions
	* @param string fields terminated with this regex activates this plugin
	*/

	function __construct($uploadPath = "../static/data/", $displayPath = null
			, $extensions = "[a-zA-Z0-9]+", $fieldSufix = '_path') {
		$this->uploadPath = rtrim($uploadPath, '/');
		$this->displayPath =  $displayPath ? rtrim($displayPath, '/') : $this->uploadPath;
		$this->extensions = '~\.' . $extensions . '$~';
		$this->fieldsufix = '~(.*)' . $fieldSufix . '$~';
	}

	function editInput($table, $field, $attrs, $value) {
		if (preg_match($this->fieldsufix, $field["field"])) {
			$return = '';
			if (!empty($value)) { // link to the attached file
				$return .= "<a href='$this->displayPath/$_GET[db]/$table/$field[field]/$value'><div id='attachedfile'>$value</div></a>";
			}
			$return .= "<input type='file'$attrs>";
			return $return;
		}
	}

	function processInput($field, $value, $function = "") {
		if (preg_match($this->fieldsufix, $field["field"])) {
			if ($_GET["edit"] != "") {
				$table = ($_GET["edit"] ? $_GET["edit"] : $_GET["select"]);
				$filename = $_FILES['fields']['name']["$field[field]"];
				if ($filename) { // file uploaded?
					$uploadfullpath = "$this->uploadPath/$_GET[db]/$table/$field[field]";
					// upload error or file extension not allowed
					if ($_FILES['fields']['error']["$field[field]"] || !preg_match($this->extensions, $filename)) {
						return false;
					}
					// create sub-directory if needed
					if (!file_exists($uploadfullpath)) {
						if (!mkdir($uploadfullpath, 0770, true)) {
							return false;
						}
					}
					// if uploaded file already exists, add timestamp on it before move_uploaded_file
					if (file_exists("$uploadfullpath/$filename")) {
						if (!rename("$uploadfullpath/$filename", ("$uploadfullpath/$filename" . date("_ymd_His")))) {
							return false;
						}
					}
					// move file to its final location
					if (!move_uploaded_file($_FILES['fields']['tmp_name']["$field[field]"], "$uploadfullpath/$filename")) {
						return false;
					}
				} else {
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
			$link = "$this->displayPath/$_GET[db]/$_GET[select]/$field[field]/$val";
		}
	}

}
