<?php
	define('USERNAME', '');
	define('PASSWORD', '');
	define('PATH_TO_SVN', '');
	define('MAX_COMMITS', 1000);
	define('MAX_ISSUES', 1000);
	define('MAX_TIME_ENTRIES', 1000);
	define('USER_ID', '');
	define('PROJECT_ID', '');
	define('NAME', '');
	define('WIKI_NAME', '');
	define('PUBLISH', false);
	// list of parent tasks that isn't useful (so the real parent would be a child)
	$parentToIgnore = array();
	// list of parent issues you want to group (= creation of parent issue)
	$parentToCreate = array();
