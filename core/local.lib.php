<?php

function normalize_name($name, $default)
{
	$fallback = md5($name);
	$name = strtolower($name);
	$name = preg_replace('#[^a-z0-9_]#i', '_', $name);
	$name = preg_replace('#__+#i', '_', $name);
	$name = preg_replace('#(^_|_$)#i', '', $name);
	if ($name) return $name;
	return $fallback;
}