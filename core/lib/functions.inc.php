<?php

function terminate($status = 0) 
{ 
	define('ABLE_TERMINATED', true);
	exit($status);
}

function show_404()
{
	require('core/404.php');
	terminate();
}

