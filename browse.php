<?php

require_once('core/init.php');
require_once('core/local/browse.php');

Content::mime(Context::$conf['mime_type']);

$__in_archive = false;
$base_directory = realpath(create_path(dirname(__FILE__), 'files'));
$local_directory = create_path(array_slice(Request::param(), 1));
$directory = create_path($base_directory, $local_directory);
$directory = realpath($directory);

// directory does not exist or is out of scope
if (!str_starts($directory, $base_directory))
	show_404();

// remove the last section of the path to obtain the parent directory
$parent_url = create_path(array_slice(extract_path(Request::$url->local), 0, -1));
$parent_directory = create_path(array_slice(extract_path($directory), 0, -1));
if (!str_starts($parent_directory, $base_directory))
	$parent_directory = null;

// the url relative to browse (not including files)
$files_local_url = create_path(array_slice(extract_path(Request::$url->local), 1));

// actually file, mistake?
if (is_file($directory))
{
	$file_url = create_path('files', $files_local_url);
	Request::redirect($file_url, true);
	return;
}

// scan the directory and exclude current/parent nodes
$node_strs = array_diff(scandir($directory), array('..', '.'));
$nodes = array();

foreach ($node_strs as $node_str)
{
	$absolute_node = realpath(create_path($directory, $node_str));
	$node_data = new stdClass();
	$node_data->absolute = $absolute_node;
	$node_data->relative = $node_str;
	$node_data->modified = filemtime($absolute_node);

	$parts = explode('.', $node_str);
	$extension = end($parts);
	$extension = strtolower($extension);

	if (is_dir($absolute_node))
	{
		$node_data->contains = count_directory_children($absolute_node);
		$node_data->is_directory = true;
		$node_data->is_file = false;
		$nodes[] = $node_data;
		continue;
	}

	if (is_file($absolute_node))
	{
		$node_data->is_directory = false;
		$node_data->is_file = true;
		$node_data->extension = file_extension($node_str);
		$node_data->size = filesize($absolute_node);
		$nodes[] = $node_data;
		continue;
	}
}

$sort = (int) Get::data('sort');
$reverse = (bool) Get::data('reverse');

node_sort($nodes, $sort, $reverse);

require('html/browse.php');

