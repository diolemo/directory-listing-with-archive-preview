<?php

function read_archive($rar_file, $internal_node)
{
	$list_command = sprintf('bin/7z l -slt %s %s', 
		escapeshellarg($rar_file),
		escapeshellarg($internal_node));

	$output = shell_exec($list_command);
	$output = substr($output, strpos($output, '----------'));
	$lines = explode(PHP_EOL, $output);

	$contents = new stdClass();
	$contents->directories = array();
	$contents->files = array();
	$contents->file_match = false;
	$build_node = new stdClass();
	$build_node->absolute = null;
	$build_node->relative = null;
	$build_node->modified = null;
	$build_node->size = 0;

	$internal_node_length = count(array_filter(extract_path($internal_node)));
	$implied_directories = array();

	foreach ($lines as $line)
	{
		if (preg_match('#^path\s+=\s+(.+)#i', $line, $match))
		{
			$build_node = new stdClass();
			$build_node->absolute = $match[1];
			$build_node->modified = null;
			$build_node->size = 0;

			$relative_path_parts = array_slice(extract_path($build_node->absolute), $internal_node_length);
			$build_node->relative = create_path($relative_path_parts);
			if (count($relative_path_parts) > 1)
				$implied_directories[] = $relative_path_parts[0];

			continue;
		}

		if (preg_match('#^size\s+=\s+(\d+)#i', $line, $match))
		{
			$build_node->size = (int) $match[1];
			continue;
		}

		if (preg_match('#^modified\s+=\s+(.+)#i', $line, $match))
		{
			$build_node->modified = strtotime($match[1]);
			continue;
		}

		// not at the current level so do not include in the results
		if (str_contains(substr($build_node->absolute, 
			strlen($internal_node) + 1), DIRECTORY_SEPARATOR))
			continue;

		// matches $build_node as a directory
		if (preg_match('#^folder\s+=\s+\+#i', $line))
		{
			$build_node->is_directory = true;
			$build_node->is_file = false;
			if ($build_node->absolute !== $internal_node)
				$contents->directories[] = $build_node;
			continue;
		}

		// matches $build_node as a file
		if (preg_match('#^folder\s+=\s+\-#i', $line))
		{
			$build_node->is_directory = false;
			$build_node->is_file = true;
			$build_node->extension = file_extension($build_node->relative);
			$contents->files[] = $build_node;
			// we found the exact file we were reading for so return true
			if ($internal_node === $build_node->absolute) 
				$contents->file_match = $build_node;
			continue;
		}
	}

	// remove all known directories from implied
	// so the directories left are new ones we must create
	$implied_directories = array_unique($implied_directories);
	foreach ($contents->directories as $directory_node)
	{
		if (($index = array_search($directory_node->relative, $implied_directories)) !== false)
			array_splice($implied_directories, $index, 1);
	}

	foreach ($implied_directories as $directory) 
	{
		$build_node = new stdClass();
		$build_node->is_directory = true;
		$build_node->is_file = false;
		$build_node->absolute = create_path($internal_node, $directory);
		$build_node->relative = $directory;
		$contents->directories[] = $build_node;
	}

	return $contents;
}

// force the user to download the file
function force_download($name, $type, $size = false)
{
	$expires_date = gmdate(DateTime::RFC1123, 0);

	header("Pragma: public");
	header("Expires: {$expires_date}");
	header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
	header("Cache-Control: private", false);
	header("Content-Disposition: attachment; filename=\"{$name}\"");
	header("Content-Transfer-Encoding: binary");
	if ($size !== false) 
		header("Content-Length: {$size}");
	header("Content-Type: {$type}");
	header("Connection: close");
}

function read_archive_file($rar_file, $internal_node)
{
	$read_command = sprintf('bin/7z e -so %s %s 2>/dev/null', 
		escapeshellarg($rar_file),
		escapeshellarg($internal_node));
	passthru($read_command);
}

function download_directory_as_archive($directory)
{
	// remove the last section of the path to obtain the parent directory
	$parent_directory = create_path(array_slice(extract_path($directory), 0, -1));
	$directory_name = basename($directory);
	$archive_command = sprintf('tar -cOC %s %s', 
		escapeshellarg($parent_directory),
		escapeshellarg($directory_name));
	passthru($archive_command);
}
