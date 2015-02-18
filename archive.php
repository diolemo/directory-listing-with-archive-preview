<?php

require_once('core/init.php');
require_once('core/local/browse.php');
require_once('core/local/archive.php');

$__in_archive = true;
$base_directory = realpath(create_path(
	array(dirname(__FILE__), 'files')));

$local_node = null;
$archive_file = null;
$param_index = 1;

while (($param = Request::param($param_index++)) !== null)
{
	$local_node = create_path($local_node, $param);
	$test_node = create_path($base_directory, $local_node);
	$test_node = realpath($test_node);

	// directory does not exist or is out of scope
	if (!str_starts($test_node, $base_directory))
		show_404();

	if (is_file($test_node))
	{
		$archive_file = $test_node;
		$internal_node = create_path(array_slice(Request::param(), $param_index));
		break;
	}
}

if (!$archive_file)
{
	$local_node = create_path('browse.php', $local_node);
	Request::redirect($local_node, true);
	return;
}

$archive_contents = read_archive($archive_file, $internal_node);

if ($archive_contents->file_match)
{
	$basename = basename($internal_node);
	$mime = 'application/octet-stream';
	force_download($basename, $mime, $archive_contents->file_match->size);
	read_archive_file($archive_file, $internal_node);
	return;
}

Content::mime(Context::$conf['mime_type']);

// this is a virtual directory through the archive
$directory = create_path($archive_file, $internal_node);

// remove the last section of the path to obtain the parent directory
$parent_url = create_path(array_slice(extract_path(Request::$url->local), 0, -1));
$parent_directory = create_path(array_slice(extract_path($directory), 0, -1));
if (!str_starts($parent_directory, $base_directory))
	$parent_directory = null;

$directories = $archive_contents->directories;
$files = $archive_contents->files;
$nodes = array_merge($directories, $files);

$sort = (int) Get::data('sort');
$reverse = (bool) Get::data('reverse');

node_sort($nodes, $sort, $reverse);

require('html/browse.php');

?>