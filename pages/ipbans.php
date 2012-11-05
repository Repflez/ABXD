<?php
//  AcmlmBoard XD - IP ban management tool
//  Access: administrators only

$title = __("IP bans");

AssertForbidden("editIPBans");

if($loguser['powerlevel'] < 3)
	Kill(__("Only administrators get to manage IP bans."));

MakeCrumbs(array(__("Admin") => actionLink("admin"), __("IP ban manager") => actionLink("ipbans")), "");

if(isset($_POST['actionadd']))
{
	//This doesn't allow you to ban IP ranges...
	//if(!filter_var($_POST['ip'], FILTER_VALIDATE_IP))
	//	Alert("Invalid IP");
	//else
	if(isIPBanned($_POST['ip']))
		Alert("Already banned IP!");
	else
	{
		$rIPBan = Query("insert into {ipbans} (ip, reason, date) values ({0}, {1}, {2})", $_POST['ip'], $_POST['reason'], ((int)$_POST['days'] > 0 ? time() + ((int)$_POST['days'] * 86400) : 0));
		Alert(__("Added."), __("Notice"));
	}
}
elseif($_GET['action'] == "delete")
{
	$rIPBan = Query("delete from {ipbans} where ip={0} limit 1", $_GET['ip']);
	Alert(__("Removed."), __("Notice"));
}

$rIPBan = Query("select * from {ipbans} order by date desc");

$banList = "";
while($ipban = Fetch($rIPBan))
{
	$cellClass = ($cellClass+1) % 2;
	if($ipban['date'])
		$date = formatdate($ipban['date'])." (".TimeUnits($ipban['date']-time())." left)";
	else
		$date = __("Permanent");
	$banList .= format("
	<tr class=\"cell{0}\">
		<td>
			{1}
		</td>
		<td>
			{2}
		</td>
		<td>
			{3}
		</td>
		<td>
			<a href=\"".actionLink("ipbans", "", "ip={1}&action=delete")."\">&#x2718;</a>
		</td>
	</tr>
", $cellClass, $ipban['ip'], $ipban['reason'], $date);
}

write("
<table class=\"outline margin width50\">
	<tr class=\"header1\">
		<th>".__("IP")."</th>
		<th>".__("Reason")."</th>
		<th>".__("Date")."</th>
		<th>&nbsp;</th>
	</tr>
	{0}
</table>

<form action=\"".actionLink("ipbans")."\" method=\"post\">
	<table class=\"outline margin width50\">
		<tr class=\"header1\">
			<th colspan=\"2\">
				".__("Add")."
			</th>
		</tr>
		<tr>
			<td class=\"cell2\">
				".__("IP")."
			</td>
			<td class=\"cell0\">
				<input type=\"text\" name=\"ip\" style=\"width: 98%;\" maxlength=\"45\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				".__("Reason")."
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"reason\" style=\"width: 98%;\" maxlength=\"100\" />
			</td>
		</tr>
		<tr>
			<td class=\"cell2\">
				".__("For")."
			</td>
			<td class=\"cell1\">
				<input type=\"text\" name=\"days\" size=\"13\" maxlength=\"13\" /> ".__("days")."
			</td>
		</tr>
		<tr class=\"cell2\">
			<td></td>
			<td>
				<input type=\"submit\" name=\"actionadd\" value=\"".__("Add")."\" />
			</td>
		</tr>
	</table>
</form>
", $banList);


?>
