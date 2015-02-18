<?php

require_once('core/init.php');
require_once('core/local/browse.php');
require_once('core/local/archive.php');

Content::mime(Context::$conf['mime_type']);

$base_directory = realpath(create_path(dirname(__FILE__), 'files'));
$local_directory = create_path(array_slice(Request::param(), 1));
$directory = create_path($base_directory, $local_directory);
$directory = realpath($directory);

// directory does not exist or is out of scope
if (!str_starts($directory, $base_directory))
	show_404();

// actually file, mistake?
if (is_file($directory)) show_404();

// normalize directory name and add .tar 
$filename = normalize_name(Request::param(-1));
$filename = "{$filename}.tar";

// stream the tar file of the directory
$mime = 'application/octet-stream';
force_download($filename, $mime);
download_directory_as_archive($directory);
return;
