<?php

// does $str end with $end
function str_ends($str, $end)
{
	return substr($str, -strlen($end)) === $end;
}
	
// does $str start with $start
function str_starts($str, $start)
{
	return substr($str, 0, strlen($start)) === $start;
}

function str_wild($value, $char = '%') 
{
	// take the value and replace all whitespace
	// with a wildcard character (%) inc. ends
	$value = implode(array($char, $value, $char));
	$value = str_replace(' ', $char, $value);
	return $value;      
}

function str_contains($haystack, $needle)
{
	return strpos($haystack, $needle) !== false;
}