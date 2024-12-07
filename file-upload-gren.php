<?php

/** Edit fields ending with "_path" by <input type="file"> and link to the uploaded files from select
 * @link https://www.adminer.org/plugins/#use
 * @author Jakub Vrana, https://www.vrana.cz/
 * @author Marcelo Gennari, https://gren.com.br
 * @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
 * @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
 * @version 2.0.0
*/

class AdminerFileUploadGren {

	/** @access protected */
	var $uploadPath, $displayPath, $extensions, $fileProhibitedRegex, $fieldSufixRegex;

	/**
	* @param string prefix for uploading data
	* @param string prefix for displaying data, null stands for $uploadPath
	* @param string regular expression with prohibited filename patterns
	* @param string fields terminated with this regex activates this plugin
	*/

	function __construct(
			$uploadPath = "../static/data",
			$displayPath = null,
			$fileProhibitedRegex = '~\.(exe|php)$|^\.|^[^.]+$~',
			$fieldSufixRegex = '_path'
		) {
			$this->uploadPath = rtrim($uploadPath, '/');
			$this->displayPath =  $displayPath ? rtrim($displayPath, '/') : $this->uploadPath;
			$this->fileProhibitedRegex = $fileProhibitedRegex;
			$this->fieldSufixRegex = '~(.*)' . $fieldSufixRegex . '$~';
	}

	function editInput($table, $field, $attrs, $value) {
		if (preg_match($this->fieldSufixRegex, $field["field"])) {
			$return = '';
			if (!empty($value)) { // link to the attached file
				$return .= "<a target='_blank' href='$this->displayPath/$_GET[db]/$table/$field[field]/$value'> <div id='attachedfile'>$value</div> </a>";
			}
			$return .= "<input type='file'$attrs>";
			return $return;
		}
	}

	function processInput($field, $value, $function = "") {
		if (preg_match($this->fieldSufixRegex, $field["field"])) {
			if ($_GET["edit"] != "") {
				$table = ($_GET["edit"] ? $_GET["edit"] : $_GET["select"]);
				$filename = $_FILES['fields']['name']["$field[field]"];
				if ($filename) { // file uploaded?
					$uploadfullpath = "$this->uploadPath/$_GET[db]/$table/$field[field]";
					// upload error or file extension not allowed
					if ($_FILES['fields']['error']["$field[field]"] || preg_match($this->fileProhibitedRegex, $filename)) {
						echo script( "alert('$filename: not uploaded. \\nRule fileProhibitedRegex: $this->fileProhibitedRegex')" );
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
						$filenameRenamedTo = $filename . date("_ymd_His");
						if ( !rename("$uploadfullpath/$filename", "$uploadfullpath/$filenameRenamedTo") ) {
							return false;
						}
					}
					// move file to its final location
					if (!move_uploaded_file($_FILES['fields']['tmp_name']["$field[field]"], "$uploadfullpath/$filename")) {
						return false;
					}
					// file uploaded sucess. check if it was replaced
					$alertReplace = isset($filenameRenamedTo) ? "alert('$filename was replaced. \\nOld file renamed to $filenameRenamedTo');" : '';
					// if ajax submitted (still in edit page after submit), reload the page to refresh file contents
					echo script( "$alertReplace if (new URLSearchParams(window.location.search).has('edit')) { window.location.replace(window.location.href) }" );
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
		if ($val != "" && preg_match($this->fieldSufixRegex, $field["field"], $regs)) {
			$link = "$this->displayPath/$_GET[db]/$_GET[select]/$field[field]/$val";
		}
	}

}
