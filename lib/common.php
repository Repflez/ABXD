<?php
// AcmlmBoard XD support - Main hub

// I can't believe there are PRODUCTION servers that have E_NOTICE turned on. What are they THINKING? -- Kawa
error_reporting(E_ALL ^ E_NOTICE | E_STRICT);

if(!is_file(COMMONDIR . '/config/database.php'))
	die(header("Location: /install.php"));

define('LIBDIR', dirname(__FILE__));

$boardroot = preg_replace('{/[^/]*$}', '/', $_SERVER['SCRIPT_NAME']);

// Deslash GPC variables if we have magic quotes on
if (get_magic_quotes_gpc())
{
	function AutoDeslash($val)
	{
		if (is_array($val))
			return array_map('AutoDeslash', $val);
		else if (is_string($val))
			return stripslashes($val);
		else
			return $val;
	}

	$_REQUEST = array_map('AutoDeslash', $_REQUEST);
	$_GET = array_map('AutoDeslash', $_GET);
	$_POST = array_map('AutoDeslash', $_POST);
	$_COOKIE = array_map('AutoDeslash', $_COOKIE);
}

function usectime()
{
	$t = gettimeofday();
	return $t['sec'] + ($t['usec'] / 1000000);
}
$timeStart = usectime();


if (!function_exists('password_hash'))
	require_once(COMMONDIR . '/lib/password.php');

require_once(LIBDIR . '/version.php');
require_once(COMMONDIR . '/config/salt.php');
require_once(LIBDIR . '/dirs.php');
require_once(LIBDIR . '/settingsfile.php');
require_once(LIBDIR . '/debug.php');

require_once(LIBDIR . '/mysql.php');
require_once(COMMONDIR . '/config/database.php');
if(!sqlConnect())
	die("Can't connect to the board database. Check the installation settings");
if(!fetch(query("SHOW TABLES LIKE '{misc}'")))
	die(header("Location: install.php"));

require_once(LIBDIR . '/mysqlfunctions.php');
require_once(LIBDIR . '/settingssystem.php');
Settings::load();
Settings::checkPlugin("main");
require_once(LIBDIR . '/feedback.php');
require_once(LIBDIR . '/language.php');
require_once(LIBDIR . '/write.php');
require_once(LIBDIR . '/snippets.php');
require_once(LIBDIR . '/links.php');

class KillException extends Exception { }
date_default_timezone_set("GMT");

$title = "";

//WARNING: These things need to be kept in a certain order of execution.

require_once(LIBDIR . '/browsers.php');
require_once(LIBDIR . '/pluginsystem.php');
loadFieldLists();
require_once(LIBDIR . '/loguser.php');
require_once(LIBDIR . '/permissions.php');
require_once(LIBDIR . '/ranksets.php');
require_once(LIBDIR . '/post.php');
require_once(LIBDIR . '/logs.php');
require_once(LIBDIR . '/onlineusers.php');

require_once(LIBDIR . '/htmlfilter.php');
require_once(LIBDIR . '/smilies.php');

$theme = $loguser['theme'];

require_once(LIBDIR . '/layout.php');

//Classes
require_once(COMMONDIR . '/class/PipeMenuBuilder.php');

require_once(LIBDIR . '/lists.php');

$mainPage = "board";
$bucket = "init"; include(LIBDIR . '/pluginloader.php');

