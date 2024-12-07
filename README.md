# Adminer File Upload Gren

Alternative version of the plugin with some customizations


## Preserve filenames

The filename is stored with the same name as uploaded by the user


## Folder structure mimic database structure

Example:

Database: DBNAME.TABLENAME.FIELDNAME_path

File: `../static/data`/ DBNAME / TABLENAME / FIELDNAME_path / `FILE`

Plugin automatically create subdirs as needed


## File's URL in edit interface

So the user can access the file in the registry form.


## Block filename by regex

Files that are blocked by default:

- .php or .exe extension
- starts with dot ` . `
- files without extension

The regex is parametrized.


## Preserve replaced files

If the user upload in the same  DBNAME / TABLENAME / FIELDNAME / FILENAME, the old file is renamed as FILENAME_ymd_His before the new takes it's place.

The user is warned by javascript popup.


## Automatically refresh the page if the file was Ajax submitted

When a file is uploaded by ajax in `edit` interface, it's contents only appear in field's link when the page is reloaded, so a small javascript does it.

<br>

# Using:

## index.php

Create an `index.php` with the following content:

```php
<?php
function adminer_object() {
	$pluginsfolder = (basename(__FILE__) == 'index.php') ? '.' : '..';
	include_once "./plugins/plugin.php"; // required to run any plugin
	foreach (glob("./plugins/*.php") as $filename) { include_once $filename; } // autoloader
	$plugins = array(
		// specify plugins here.
		new AdminerFileUploadGren()
	);
	return new AdminerPlugin($plugins);
}

include "./your-compiled-or-downloaded-adminer.php";
```

## Folder structure

A typical deploy looks like this:

```ini
📂 webserver_root
├── 📂 adminer
│   ├── 📄 index.php
│   ├── 📄 your-compiled-or-downloaded-adminer.php
│   ├── 📄 adminer.css
│   ├── 📄 favicon.ico
│   └── 📂 plugins
│       ├── 📄 plugin.php
│       └── 📄 file-upload-gren.php # plugin goes here
└── 📂 static
    └── 📂 data # this folder must be writeable by webserver
        └── 📂 UPLOAD FILE STRUCTURE
```



