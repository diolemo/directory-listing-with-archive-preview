<?php

const SIZE_GIB = 1073741824;
const SIZE_MIB = 1048576;
const SIZE_KIB = 1024;

const SORT_DEFAULT = 0;
const SORT_NAME = 1;
const SORT_SIZE = 2;
const SORT_DATE = 3;

// icon css classes used in the listing (imported from json)
$icon_classes = json_decode(file_get_contents('core/local/icons.json'));

function icon_class($extension)
{
	global $icon_classes;
	if (!isset($icon_classes->{$extension}))
		return 'default';
	return $icon_classes->{$extension};
}

function create_path($parts)
{  
	if (!is_array($parts))
		$parts = func_get_args();
	$path = implode(DIRECTORY_SEPARATOR, $parts);
	$path = str_replace('//', '/', $path);
	return $path;
}

function extract_path($path)
{
	$parts = explode(DIRECTORY_SEPARATOR, $path);
	
	if ($count = count($parts))
	{
		$last = $parts[$count-1];
		if (strlen($last) === 0)
			$parts = array_slice($parts, 0, -1);
	}
	
	return $parts;
}

function file_extension($name)
{
	if (!preg_match('#\.([a-z0-9\-]{1,6})$#i', $name, $match))
		return null;
	return strtolower($match[1]);
}

function human_readable_size($size)
{
	if ($size > SIZE_GIB)
	{
		$size = ($size / SIZE_GIB);
		return sprintf('%.2f GiB', $size);
	}

	if ($size > SIZE_MIB)
	{
		$size = ($size / SIZE_MIB);
		return sprintf('%.2f MiB', $size);
	}

	if ($size > SIZE_KIB)
	{
		$size = ($size / SIZE_KIB);
		return sprintf('%.2f KiB', $size);
	}

	return sprintf('%d B', $size);
}

function node_modified_string($node)
{
	if (empty($node->modified)) return null;
	return date('Y-m-d H:i:s', $node->modified);
}

function node_size_string($node)
{
	if ($node->is_directory && !empty($node->contains))
	{
		return sprintf('%d folder(s) and %d file(s)',
			$node->contains->directories, 
			$node->contains->files);
	}

	if ($node->is_file && isset($node->size))
	{
		return human_readable_size($node->size);
	}

	return null;
}

function count_directory_children($node)
{
	$count = new stdClass();
	$count->directories = 0;
	$count->files = 0;

	$inner_nodes = array_diff(scandir($node), array('.', '..'));
	foreach ($inner_nodes as $inner_node)
	{
		$absolute_inner_node = realpath(create_path(array($node, $inner_node)));
		if (is_dir($absolute_inner_node)) $count->directories++;
		else if (is_file($absolute_inner_node)) $count->files++;
	}

	return $count;
}

function & node_sort(&$nodes, $sort, $reverse = false)
{
	if ($sort === SORT_DEFAULT)
	{
		usort($nodes, function($a, $b) {
			if ($a->is_directory && $b->is_file) return -1;
			if ($a->is_file && $b->is_directory) return 1;
			if ($a->relative < $b->relative) return -1;
			if ($a->relative > $b->relative) return 1;
			return 0;
		});
	}

	elseif ($sort === SORT_NAME)
	{
		usort($nodes, function($a, $b) {
			if ($a->relative < $b->relative) return -1;
			if ($a->relative > $b->relative) return 1;
			return 0;
		});
	}

	else if ($sort === SORT_SIZE)
	{
		usort($nodes, function($a, $b) {

			if ($a->is_directory && $b->is_file) return 1;
			if ($a->is_file && $b->is_directory) return -1;
			if ($a->is_directory /* || $b->is_directory */) 
			{
				if ($a->relative < $b->relative) return -1;
				if ($a->relative > $b->relative) return 1;
				return 0;
			}
			
			if ($a->size < $b->size) return -1;
			if ($a->size > $b->size) return 1;
			if ($a->relative < $b->relative) return -1;
			if ($a->relative > $b->relative) return 1;
			return 0;

		});
	}

	else if ($sort === SORT_DATE)
	{
		usort($nodes, function($a, $b) {			
			if ($a->modified < $b->modified) return -1;
			if ($a->modified > $b->modified) return 1;
			if ($a->relative < $b->relative) return -1;
			if ($a->relative > $b->relative) return 1;
			return 0;
		});
	}

	if ($reverse)
		$nodes = array_reverse($nodes);
	return $nodes;
}

function & isset_value(&$array, $key)
{
	return isset($array[$key]) ? $array[$key] : null;
}

function is_archive_extension($extension)
{
	if ($extension === "rar") return true;
	if ($extension === "zip") return true;
	return false;
}