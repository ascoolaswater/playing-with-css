<?php
$mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
mysql_select_db("bitsecurelabs") or die('SQL error');

$result=mysql_query("INSERT INTO `users` (`user`, `created`, `life`, `pass`, `name`, `email`, `ip`, `level`, `activated`, `lic_type`, `seats`) VALUES ('11', ".11.", ".33.", '".44."', '".1."', '".1."', '".1."', 1, '1', 23, 24);");
echo mysql_error($result);
?>