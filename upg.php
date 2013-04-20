<?php
    error_reporting(0);
    include_once dirname(__FILE__)."/config.php";
    $db_link = mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL connection error');
    mysql_select_db($dbname, $db_link) or die(mysql_error($db_link));
    mysql_query("CREATE TABLE IF NOT EXISTS global_settings(name CHAR(20) PRIMARY KEY, value TEXT);", $db_link) or die(mysql_error($db_link));

    $result = mysql_query("SELECT value FROM global_settings WHERE name='distrib';", $db_link) or die(mysql_error($db_link));
    $row = mysql_fetch_assoc($result);
    $ss_distrib = $row["value"];
    $ss_dist_ver = get_product_version($ss_distrib);
    
    $cl_ver = trim($_GET["v"]);
    if (strlen($ss_dist_ver) > 0 && strlen($cl_ver) > 0 && $ss_dist_ver > $cl_ver)
    {
        $ss_distrib = str_replace("/home/msecurel/public_html/licensing", "http://subupd.msecuredatalabs.com", $ss_distrib);
        header(sprintf("Location: %s", $ss_distrib));     
    }
    else
    {
        header("HTTP/1.0 404 Not Found");
    }


function get_product_version($file_name)
{
   try 
   {
      if (strlen(trim($file_name)) == 0)
   {
      return "";
   }
   $key = "P\x00r\x00o\x00d\x00u\x00c\x00t\x00V\x00e\x00r\x00s\x00i\x00o\x00n\x00\x00\x00";
   $fptr = fopen($file_name, "rb");
   if (!$fptr)
   {
      return "";
   }
   $data = "";
   while (!feof($fptr))
   {
      $data .= fread($fptr, 65536);
      if (strpos($data, $key)!==FALSE)
         break;
      $data = substr($data, strlen($data)-strlen($key));
   }
   fclose($fptr);
   if (strpos($data, $key)===FALSE)
      return "";
   $pos = strpos($data, $key)+strlen($key);
   $version = "";
   for ($i=$pos; $data[$i]!="\x00"; $i+=2)
      $version .= $data[$i];
   return trim($version);

   } 
   catch (Exception $e) 
   {
   }
   return "";
}

?>