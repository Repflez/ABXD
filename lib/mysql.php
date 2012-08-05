<?php
// AcmlmBoard XD support - MySQL database wrapper functions

include("database.php");

$queries = 0;

$dblink = new mysqli($dbserv, $dbuser, $dbpass, $dbname);
unset($dbpass);


function SqlEscape($text)
{
	global $dblink;
	return $dblink->real_escape_string($text);
}

function Query_ExpandFieldLists($match)
{
	$ret = array();
	$prefix = $match[1];
	$fields = preg_split('@\s*,\s*@', $match[2]);
	
	foreach ($fields as $f)
		$ret[] = $prefix.'.'.$f.' AS '.$prefix.'_'.$f;
		
	return implode(',', $ret);
}

function Query_AddUserInput($match)
{
	global $args;
	$var = $args[$match[1]+1];

	if ($var === NULL) return 'NULL';

	$var = (string) $var;
	
	if (ctype_digit($var)) 
		return $var;

	return '\''.SqlEscape($var).'\'';
}

/*
 * Function for prepared queries
 *
 * Example usage: Query("SELECT t1.(foo,bar), t2.(*) FROM {table1} t1 LEFT JOIN {table2} t2 ON t2.id=t1.crapo WHERE t1.id={0} AND t1.crapo={1}", 1337, "Robert'; DROP TABLE students; --");
 * assuming a database prefix of 'abxd_', final query is:
 * SELECT t1.foo AS t1_foo,t1.bar AS t1_bar, t2.* FROM abxd_table1 t1 LEFT JOIN abxd_table2 t2 ON t2.id=t1.crapo WHERE t1.id='1337' AND t1.crapo='Robert\'; DROP TABLE students; --'
 *
 * compacted fieldlists allow for defining certain widely-used field lists as global variables or defines (namely, the fields for usernames)
 * {table} syntax allows for flexible manipulation of table names (namely, adding a DB prefix)
 *
 */
function Query()
{
	global $dbpref, $args, $fieldLists;
	$args = func_get_args();
	if (is_array($args[0])) $args = $args[0];
	
	$query = $args[0];

	// expand compacted field lists
	$query = preg_replace("@(\w+)\.\(\*\)@s", '$1.*', $query);
	$query = str_replace(".(_userfields)", ".(".$fieldLists["userfields"].")", $query);
	$query = preg_replace_callback("@(\w+)\.\(([\w,\s]+)\)@s", 'Query_ExpandFieldLists', $query);

	// add table prefixes
	$query = preg_replace("@\{([a-z]\w*)\}@si", $dbpref.'$1', $query);

	// add the user input
	$query = preg_replace_callback("@\{(\d+)\}@s", 'Query_AddUserInput', $query);

	return RawQuery($query);
}

function RawQuery($query)
{
	global $queries, $querytext, $loguser, $dblink, $debugMode;

//	if($debugMode)
//		$queryStart = usectime();

	$res = @$dblink->query($query);

	if(!$res)
	{
		if($debugMode)
			die(nl2br(backTrace())."<br>".$dblink->error."<br />Query was: <code>".$query."</code><br />This could have been caused by a database layout change in a recent git revision. Try running the installer again to fix it. <form action=\"install/doinstall.php\" method=\"POST\"><br />
			<input type=\"hidden\" name=\"action\" value=\"Install\" />
			<input type=\"hidden\" name=\"existingSettings\" value=\"true\" />
			<input type=\"submit\" value=\"Click here to re-run the installation script\" /></form>");
		else
		{
			trigger_error("MySQL Error.", E_USER_ERROR);
			die("MySQL Error.");
		}
	}
	
	$queries++;
	
	if($debugMode)
	{
		$querytext .= "<tr class=\"cell0\">";
		$querytext .= "<td>".nl2br(htmlspecialchars($query))."</td>";
		
//derp, timing queries this way doesn't return accurate results since it's async
//		$querytext .= "<td>".sprintf("%1.3f",usectime()-$queryStart)."</td>";
		$querytext .= "<td><div class=\"spoiler\"><button class=\"spoilerbutton named\">Backtrace</button><div class=\"spoiled hidden\">".nl2br(backTrace())."</div></div></td>";

		$querytext .= "</tr>";
	}
	
	return $res;
}

function Fetch($result)
{
	return $result->fetch_array();
}

function FetchRow($result)
{
	return $result->fetch_row();
}

function FetchResult()
{
	$res = Query(func_get_args());
	if($res->num_rows == 0) return -1;
	return Result($res, 0, 0);
}

// based on http://stackoverflow.com/a/3779460/736054
function Result($res, $row = 0, $field = 0)
{
	$res->data_seek($row);
	$ceva = array_values($res->fetch_assoc());
	$rasp = $ceva[$field];
	return $rasp;
}

function NumRows($result)
{
	return $result->num_rows;
}

function InsertId()
{
	global $dblink;
	return $dblink->insert_id;
}

function getDataPrefix($data, $pref)
{
	$res = array();

	foreach($data as $key=>$val)
		if(substr($key, 0, strlen($pref)) == $pref)
			$res[substr($key, strlen($pref))] = $val;

	return $res;
}


$fieldLists = array(
	"userfields" => "id,name,displayname,powerlevel,sex,minipic"
);

function loadFieldLists()
{
	global $fieldLists;
	
	//Allow plugins to add their own!
	$bucket = "fieldLists"; include('lib/pluginloader.php');
}

?>
