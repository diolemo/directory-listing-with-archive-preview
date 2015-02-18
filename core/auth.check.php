<?php

if (!Request::is_cli())
{
	Auth::__check_no_auth();
	Auth::__check_auth_conditions();
}

