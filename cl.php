<?php
include_once dirname(__FILE__)."/config.php";
if (strlen(trim($_GET["u"])) == 0)
{
	die("0");
}
$cprod_type = (int)trim($_GET["pt"]);
$db_link = mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL connection error');
mysql_select_db($dbname) or die(mysql_error($db_link));
$result = mysql_query("SELECT status FROM users WHERE user = '".trim($_GET["u"])."';") or die(mysql_error($db_link));
$row = mysql_fetch_assoc($result);
if ((int)$row['status'] == 1)
{
    die("1");
}
$result = mysql_query("SELECT activated, life, lic_type FROM users WHERE user = '".trim($_GET["u"])."';") or die(mysql_error($db_link));
$row = mysql_fetch_assoc($result);
$act_date = (int)$row['activated']; 
$life_count = (int)$row['life']; 
if ($cprod_type != (int)$row['lic_type'])
{
    die("0");
}
if ($act_date == 0)
{
    $act_date = time();
    mysql_query("UPDATE users SET activated='".$act_date."' WHERE user = '".trim($_GET["u"])."';", $db_link);    
}

$ret_v = $act_date + $life_count;

mysql_query("CREATE TABLE IF NOT EXISTS client_info(user TEXT, host TEXT, mac TEXT, country TEXT, os TEXT);", $db_link) or die(mysql_error($db_link));
mysql_query("DELETE FROM client_info WHERE user = '".trim($_GET["u"])."' AND mac = '".trim($_GET["m"])."';") or die(mysql_error($db_link));


$csv_c = @file_get_contents(sprintf("http://freegeoip.net/csv/%s", $_SERVER["REMOTE_ADDR"]));
$csv_a = explode(",", $csv_c);

$nus_cl = (int)$_GET["nus"];

mysql_query("INSERT INTO client_info VALUES('".trim($_GET["u"])."', '".trim($_GET["h"])."', '".trim($_GET["m"])."', '".$csv_a[2]."', '".trim($_GET["o"])."',".$nus_cl.");") or die(mysql_error($db_link));
echo $ret_v;
?>