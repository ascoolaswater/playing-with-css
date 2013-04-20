<?php
include_once dirname(__FILE__)."/config.php";
if ($_SERVER['HTTPS']) {$proto='http://';}
else {$proto='http://';}
list($path)=explode('?', $_SERVER['REQUEST_URI']);
$script=$proto.$_SERVER['HTTP_HOST'].$path;

$mode=$_REQUEST['a'];
$search_keyword = $_REQUEST['s'];

if ($mode=='exportinfo')
{
    header("Content-type: text/x-csv");
	header("Content-Disposition: attachment; filename=export.csv");

    $db_link = mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL connection error');
    mysql_select_db($dbname, $db_link) or die(mysql_error($db_link));
    mysql_query("CREATE TABLE IF NOT EXISTS client_info(user TEXT, host TEXT, mac TEXT, country TEXT, os TEXT);", $db_link) or die(mysql_error($db_link));

    $result = mysql_query("SELECT host, mac, country, os FROM client_info WHERE user='".trim($search_keyword)."';", $db_link) or die(mysql_error($db_link));
    
    $str_csv = "";
    while ($row = mysql_fetch_assoc($result))
    {
        $str_csv = $str_csv.$row["host"].",".$row["mac"].",".$row["country"].",".$row["os"]."\n";
    }
    echo $str_csv;

exit;
}

if ($mode == 'init')
{
    $loginhtml=@str_replace(array("\r\n", "\r", "\n", "'"), array(" ", " ", " ", "\\'"), file_get_contents('login.html'));	
    header("Content-Type: text/javascript");
    echo <<< ML_HTML
function ml_init()
{
ml_body_content=ml_body.innerHTML;
ml_body.innerHTML=ml_login_html;
ml_body.style.visibility='visible';
}
function ml_dologin(f)
{
ml_login_user=f.user.value;
ml_login_pass=f.pass.value;
ml_remember=f.remember.checked;
if (!ml_login_user || !ml_login_pass) {alert('You forgot to enter your username or password.');}
else
{
if (ml_remember)
{
setCookie('$cookiename'+"user", ml_login_user, 365);
setCookie('$cookiename'+"pass", ml_login_pass, 365);
}
else
{
deleteCookie('$cookiename'+"user");
deleteCookie('$cookiename'+"pass");
}

var ml_head = document.getElementsByTagName("head")[0];         
ml_script = document.createElement('script');
ml_script.type = 'text/javascript';
ml_script.src = '$script?a=auth&remember=1&user='+escape(ml_login_user)+'&pass='+escape(ml_login_pass)+'&rand='+escape(Math.random());
ml_head.appendChild(ml_script);
}
}
function ml_forgot_pass()
{
c=Math.random();	
forgotpass=window.open('','name','height=230,width=400');
forgotpass.document.write('<form method="POST" action="$script"><input type="hidden" name="a" value="doforgot"><input type="hidden" name="seed" value="'+c+'"><p align="center"><font face="Verdana" style="font-size: 8pt">Your registered email address:<br><br><input type="text" name="email" size="38" style="font-size: 8pt; font-family: Tahoma"><br><br>Enter the code shown in the image into the box below it:<br><br><img src="$script?a=captcha&c='+c+'"><br><br><input type="text" name="code" size="20" style="font-size: 8pt; font-family: Tahoma"><br><br><input type="submit" value="Submit" style="font-size: 8pt; font-family: Tahoma"></font></p></form>');
forgotpass.document.close();
}

function setCookie (name, value, lifespan, access_path) {
var cookietext = name + "=" + escape(value) 
if (lifespan != null) { 
  var today=new Date() 
  var expiredate = new Date() 
  expiredate.setTime(today.getTime() + 1000*60*60*24*lifespan)
  cookietext += "; expires=" + expiredate.toGMTString()
}
if (access_path != null) { 
  cookietext += "; PATH="+access_path 
}
document.cookie = cookietext 
return null 
}


function setDatedCookie(name, value, expire, access_path) {
var cookietext = name + "=" + escape(value) 
   + ((expire == null) ? "" : ("; expires=" + expire.toGMTString()))
if (access_path != null) { 
  cookietext += "; PATH="+access_path 
}
document.cookie = cookietext 
return null 
}


function getCookie(Name) {
var search = Name + "=" 
var CookieString = document.cookie 
var result = null 
if (CookieString.length > 0) { 
    offset = CookieString.indexOf(search) 
    if (offset != -1) { 
        offset += search.length 
        end = CookieString.indexOf(";", offset) 
        if (end == -1) {
           end = CookieString.length }
        result = unescape(CookieString.substring(offset, end)) 
   } 
}
return result 
}


function deleteCookie(Name, Path) {
setCookie(Name,"Deleted", -1, Path)
}


var ml_body = document.getElementsByTagName("body")[0];         
ml_body.style.visibility='hidden';
var ml_body_content='';
//var ml_login_html='<div style="margin-top: 75px" align="center"> <form onSubmit="javascript: ml_dologin(this); return false;">           <center>            <table cellspacing="0" style="border-collapse: collapse; font-family:Verdana; font-size:8pt; background-repeat: no-repeat" id="ml_table1" cellpadding="0" border="0" background="$script'+'images/logo-bg.png" width="810px" height="320px">           <tr>                    <td style="padding-right: 150px; padding-top: 70px" align="center" valign="top">  <div style="float: right">     <font face="Verdana" color="black" style="font-size: 8pt"> Username:<br> <input type="text" name="user" value="" size="18" style="font-family: Verdana; font-size: 8pt"><br> Password:<br>  <input type="password" name="pass" size="18" style="font-family: Verdana; font-size: 8pt"><br> <input type="image" name="" src="$script'+'images/go.png" width="36" height="36" vspace="5"><br>  <a href="javascript: ml_forgot_pass()">  <font color="white">Forgot Password?</font></a></font></div>        </td>        </tr>                </table>         </center> </form>   </div>';
var ml_login_html='$loginhtml';
var cuser=getCookie('$cookiename'+"user");
var cpass=getCookie('$cookiename'+"pass");
if (!cuser) {cuser='';}
if (!cpass) {cpass='';}
ml_login_html=ml_login_html.replace("%user%", cuser);
ml_login_html=ml_login_html.replace("%pass%", cpass);
if (cuser) {ml_login_html=ml_login_html.replace("%checked%", 'checked');}
window.onload=ml_init;
ML_HTML;
exit;
}

if ($mode == 'auth')
{

$user=trim($_REQUEST['user']);
$pass=trim($_REQUEST['pass']);
$mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
mysql_select_db($dbname) or die('SQL error');
$result=mysql_query("SELECT `id`, `status`, `level`, `activated`, `life` FROM `users` WHERE `user` = '".mysql_real_escape_string($user)."' AND `pass` = '".mysql_real_escape_string($pass)."'") or die('SQL error');
if (!mysql_num_rows($result)) {$error='You have provided an invalid username or password.';}
else
{
list($id, $status, $level, $activated, $life)=mysql_fetch_row($result);
if ($level!=9 && $status==1) {$error='You username has been suspended.';}
elseif ($level==9 || $level==8) {$error='Only users are allowed to login using this interface.';}
elseif ($level!=9 && $activated && $life && (time()-($activated+$life)) > 0) {$error='Your account has expired.';}
else
{
$firstlogin=0;
if ($level!=9 && !$activated)
{
$activated=time();
mysql_query("UPDATE `users` SET `activated` = $activated WHERE `id` = $id") or die('SQL error');
$firstlogin=1;
}
}
}
header("Content-Type: text/javascript");
if ($error)
{
echo <<< ML_HTML
alert('$error');
ML_HTML;
}
else
{
$msg="You have successfully logged in as: <b>$user</b>";
if ($level==9 || $level==8) {$msg.=' <i>(Administrator)</i>';}
$msg.='. ';
$session=crc($user.time().rand());
mysql_query("INSERT INTO `sessions` (`id`, `created` , `stamp`, `uid`, `ip`, `browser`) VALUES ('$session', UNIX_TIMESTAMP() , UNIX_TIMESTAMP(), '$id', '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."')");
if ($level==9 || $level==8)
{
$msg.=' Click <form target="_blank" action="'.$script.'" style="display: inline; margin: 0px" method="POST"> <input type="submit" value="here" name="" style="cursor: hand; cursor: pointer; font-family: Tahoma; font-size: 8pt; border-style: solid; border-width: 0px; padding: 0px; background-color: #FFCC99"> <input type="hidden" name="remember" value="'.$remember.'"> <input type="hidden" name="user" value="'.$user.'"> <input type="hidden" name="pass" value="'.$pass.'"> <input type="hidden" name="session" value="'.$session.'"> <input type="hidden" name="a" value="login"> </form> to enter your administration page. ';
}
if ($level!=9 && $life) {$msg.='Your account expires on: <b>'.date('jS F Y', $activated+$life).'</b>.';}
echo <<< ML_HTML
ml_body.innerHTML='<div style="padding: 5px"><font color="black" face="Tahoma" style="font-size: 8pt"><img align="left" hspace="5" src="$script'+'images/info.gif" border="0px">$msg</div>' + ml_body_content;
//setTimeout("alert('$msg')", 200);
//alert('$msg');
ML_HTML;
}
exit;
}

if ($mode == 'intauth')
{
    $user=trim($_REQUEST['user']);
    $pass=trim($_REQUEST['pass']);
    $remember=trim($_REQUEST['remember']);
    $mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die("sql err");
    mysql_select_db($dbname) or die(mysql_error($mysql));
    $result=mysql_query("SELECT `id`, `status`, `level`, `activated`, `life` FROM `users` WHERE `user` = '".mysql_real_escape_string($user)."' AND `pass` = '".mysql_real_escape_string($pass)."'") or die('SQL error');
    if (!mysql_num_rows($result)) {$error='You have provided an invalid username or password.';}
    else
    {
        list($id, $status, $level, $activated, $life)=mysql_fetch_row($result);
        if ($level!=9 && $status==1) {$error='You username has been suspended.';}
        elseif ($level!=9 && $activated && $life && (time()-($activated+$life)) > 0) {$error='Your account has expired.';}
        else
        {
            $firstlogin=0;
            if ($level!=9 && !$activated)
            {
                $activated=time();
                mysql_query("UPDATE `users` SET `activated` = $activated WHERE `id` = $id") or die('SQL error');
                $firstlogin=1;
            }
        }
    }
    if ($error)
    {
        die($error);
        ML_HTML;
    }
    elseif ($level==9 || $level==8)
    {
        $msg="You have successfully logged in as: <b>$user</b> <i>(Administrator).</i> ";
        $session=crc($user.time().rand());
        mysql_query("INSERT INTO `sessions` (`id`, `created` , `stamp`, `uid`, `ip`, `browser`) VALUES ('$session', UNIX_TIMESTAMP() , UNIX_TIMESTAMP(), '$id', '".mysql_real_escape_string($_SERVER['REMOTE_ADDR'])."', '".mysql_real_escape_string($_SERVER['HTTP_USER_AGENT'])."')");
        $msg.=' Click <form action="'.$script.'" style="display: inline; margin: 0px" method="POST"> <input type="submit" value="here" name="" style="cursor: hand; cursor: pointer; font-family: Tahoma; font-size: 8pt; border-style: solid; border-width: 0px; padding: 0px; background-color: #FFCC99"> <input type="hidden" name="remember" value="'.$remember.'"> <input type="hidden" name="user" value="'.$user.'"> <input type="hidden" name="pass" value="'.$pass.'"> <input type="hidden" name="session" value="'.$session.'"> <input type="hidden" name="a" value="login"> </form> to enter your administration page. ';
        if ($level!=9 && $life) {$msg.='Your account expires on: <b>'.date('jS F Y', $activated+$life).'</b>.';}
        echo <<< ML_HTML
        <div style="padding: 5px"><font color="black" face="Tahoma" style="font-size: 8pt"><img align="left" hspace="5" src="images/info.gif" border="0px">$msg</div>
ML_HTML;
    }
    else {die('Normal users are not allowed to login using this portal.');}
    exit;
}
if ($_SERVER['QUERY_STRING']=='login')
{
    $user=trim($_COOKIE[$cookiename."user"]);
    $pass=trim($_COOKIE[$cookiename."pass"]);
    if ($user)
    {
        $checked ='checked';
   }

    $loginhtml=str_replace(array('%user%', '%pass%', '%checked%'), array($user, $pass, $checked), file_get_contents('login.html'));	
    echo <<<ML_HTML
<script>
function ml_forgot_pass()
{
c=Math.random();	
forgotpass=window.open('','name','height=230,width=400');
forgotpass.document.write('<form method="POST" action="$script"><input type="hidden" name="a" value="doforgot"><input type="hidden" name="seed" value="'+c+'"><p align="center"><font face="Verdana" style="font-size: 8pt">Your registered email address:<br><br><input type="text" name="email" size="38" style="font-size: 8pt; font-family: Tahoma"><br><br>Enter the code shown in the image into the box below it:<br><br><img src="$script?a=captcha&c='+c+'"><br><br><input type="text" name="code" size="20" style="font-size: 8pt; font-family: Tahoma"><br><br><input type="submit" value="Submit" style="font-size: 8pt; font-family: Tahoma"></font></p></form>');
forgotpass.document.close();
}
function ml_dologin(f)
{
f.submit();
return true;
}
</script>
$loginhtml
ML_HTML;
    exit;	
}	

if ($mode=='login')
{
    $session=$_REQUEST['session'];
    $remember=$_REQUEST['remember'];
    if ($remember)
    {	
        $user=$_REQUEST['user'];	
        $pass=$_REQUEST['pass'];	
    }
    setcookie($cookiename, $session);
    setcookie($cookiename."user", $user, time()+31556952);
    setcookie($cookiename."pass", $pass, time()+31556952);

    echo <<<ML_HTML
<html>
<body onLoad="javascript: document.ml_admin.submit();">	
Please wait.. 
<noscript>
Click <a href="./?rnd=$session">here</a> to continue..
</noscript>
<form name="ml_admin" action="./" method="POST">
</form>
</body>
</html>
ML_HTML;
exit;
}

if ($mode=='captcha')
{
header('Content-type: image/gif');
readfile("http://mailjol.net/anti-bot/?c=".$_REQUEST['c']);
exit;
}

if ($_SERVER['QUERY_STRING']=='register' || $mode=='register')
{
echo <<<ML_HTML

<html>
<head>
<title></title>
</head>
<body topmargin="0" leftmargin="0">
<div style="height: 35px; background:url('images/orange-bar.gif') repeat-x top;"><font color="black" face="Tahoma" style="font-size: 8pt">
</div>	
<div align="center">
<br><br>	

<form target="do_frame" method="POST" action="./">

<input type="hidden" name="a" value="doregister">
<p>Please enter a valid Email Id. A license key will be sent to the given Id.</p>
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family:Verdana; font-size:9pt" bordercolor="#111111">
  <tr>
    <td colspan="2" align="center"><u>User Registration</u><br>
&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" align="right">Name:</td>
    <td width="50%">

    <input type="text" name="new_name" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>
  <tr>
    <td width="50%" align="right">E-mail:</td>
    <td width="50%">
    <input type="text" name="new_email" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>  

  <tr>
    <td colspan="2" align="center"><br><input style="font-family: Verdana; font-size: 8pt" type="submit" name="" value=" Submit "></td>
  </tr>
</table>
</form>
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

ML_HTML;
exit;
}

if ($mode=='doregister')
{
$newname=trim($_REQUEST['new_name']);
$newemail=trim(strtolower($_REQUEST['new_email']));
$ip=$_SERVER['REMOTE_ADDR'];
$newpass=crc($newemail.rand());
$td=@file_get_contents('data/trialdays.txt');

if (!$newname || !preg_match('/^[A-Za-z\s\.]{3,100}$/', $newname)) {$msg='Name is invalid.';}
elseif(!preg_match("/^([a-zA-Z0-9_'+*$%\^&!\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9:]{2,4})+$/", $newemail)) {$msg='E-mail address is invalid.';}
else
{
$mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
mysql_select_db($dbname) or die('SQL error');
$result=mysql_query("SELECT `id` FROM `users` WHERE `email` = '".mysql_real_escape_string($newemail)."'") or die('SQL error');
if (mysql_num_rows($result)) {$msg="User will email address: $newemail already exists.";}
else
{
$result=mysql_query("INSERT INTO `users` (`created`, `life`, `pass`, `name`, `email`, `ip`) VALUES (".time().", ".($td*24*3600).", '".$newpass."', '".mysql_real_escape_string($newname)."', '".mysql_real_escape_string($newemail)."', '".mysql_real_escape_string($ip)."')") or die('SQL error');
$newuid=mysql_insert_id($mysql);
$newuser='MWS-'.str_pad($newuid, 9, '0', STR_PAD_LEFT);
$result=mysql_query("UPDATE `users` SET `user` = '$newuser' WHERE `id` = $newuid") or die('SQL error');
$msg="Registration successful. Your login information has been emailed to the address entered by you.";
mail($newemail, 'Bitsecurelabs: User Registration', "Dear $newname,\n\nYour account has been created successfully. Here is your login information:\n\nUsername: $newuser\nPassword: $newpass\n\n-\nBest Regards,\nBitsecurelabs", 'From: "Bitsecurelabs" <support@bitsecurelabs.com>', '-fsupport@bitsecurelabs.com');
}
}
echo <<<ML_HTML
<script>
alert('$msg');	
</script>		
ML_HTML;
exit;
}

if ($mode=='doforgot')
{
$email=trim($_REQUEST['email']);
$code=trim($_REQUEST['code']);
$seed=trim($_REQUEST['seed']);
if (!$email || !$code) {die('You forgot to enter either your email address or the verification code.');}
if (@file_get_contents("http://mailjol.net/anti-bot/?c=$seed&d=$code&v=1") != 'PASS') {die("You haven't entered the correct verification code.");}

$mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
mysql_select_db($dbname) or die('SQL error');
$result=mysql_query("SELECT `id`, `user`, `pass` FROM `users` WHERE `email` = '".mysql_real_escape_string($email)."'") or die('SQL error');
if (!mysql_num_rows($result)) {die("User will email address: $newemail does not exist.");}
list($id, $user, $pass)=mysql_fetch_row($result);
mail($email, 'Bitsecurelabs: Login Information', "Dear Sir/Madam,\n\nHere is your login information:\n\nUsername: $user\nPassword: $pass\n\n-\nBest Regards,\nBitsecurelabs", 'From: "Bitsecurelabs" <support@bitsecurelabs.com>', '-fsupport@bitsecurelabs.com');
die("Your account information has been mailed to your e-amil address: $email.");
exit;
}

	
$session=trim($_COOKIE[$cookiename]);
if ($session)
{
$timer=time()-1800;
$mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
mysql_select_db($dbname) or die('SQL error');
$result=mysql_query("SELECT `uid` FROM `sessions` WHERE `stamp` > $timer AND `id` = '".mysql_real_escape_string($session)."'") or die('SQL error');
if (!mysql_num_rows($result))
{
setcookie($cookiename, '');
die('<script> alert("Session could not be found. Please login again."); window.top.location="?login"; </script>');
}
}
else {die('<script> alert("Session could not be found. Please login again."); window.top.location="?login"; </script>');}
list($id)=mysql_fetch_row($result);
mysql_query("UPDATE `sessions` SET `stamp` = UNIX_TIMESTAMP() WHERE `id` = '".mysql_real_escape_string($session)."'") or die('SQL error');
$result=mysql_query("SELECT `user`, `status`, `level`, `life`, `activated` FROM `users` WHERE `id` = $id") or die('SQL error');
list($user, $status, $level, $life, $activated)=mysql_fetch_row($result);


if ($mode=='logout')
{
@mysql_query("UPDATE `sessions` SET `stamp` = 0 WHERE `id` = '".mysql_real_escape_string($session)."'");
setcookie($cookiename, '');
die('<script> alert("You have logged out successfully. Please wait.."); window.top.location="?login"; </script>');
}

if($level==9 || $level==8)
{
echo <<<ML_HTML
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<title></title>
<link rel="stylesheet" type="text/css" media="all" href="jsDatePick_ltr.min.css" />
<script type="text/javascript" src="jsDatePick.min.1.3.js"></script>
<script type="text/javascript">
	window.onload = function()
    {
        var elemsInput = document.getElementsByTagName('input')
        for(var i=0; i<elemsInput.length; i++)
        {
	       var idI = elemsInput[i].id;
           if (idI != null)
           {
               if (idI.indexOf("exp_") == 0)
               {
                    new JsDatePick({
			             useMode:2,
			             target: idI,
			             dateFormat:"%d %M %Y"});

               } 
           } 
        }
	};
</script>

</head>
<body topmargin="0" leftmargin="0">
<div style="padding: 10px; background:url('images/orange-bar.gif') repeat-x top;"><font color="black" face="Tahoma" style="font-size: 8pt">
  <img align="left" hspace="5" src="images/info.gif" border="0px" width="16" height="16">Logged in as: <b>$user</b> <i>(Administrator)</i>.<br><br></div>	

<style>
#sddm{margin:0;padding:0;z-index:30}#sddm li{margin:0;padding:0;list-style:none;float:left;font:normal 11px tahoma}#sddm li a{display:block;margin:0 1px 0 0;padding:1px 10px;background:#666633;color:#fff;text-align:center;text-decoration:none}#sddm li a:hover{background:#49A3FF}#sddm div{position:absolute;visibility:hidden;margin:0;padding:0;background:#EAEBD8;border:1px solid #5970B2}#sddm div a{position:relative;display:block;margin:0;padding:5px 10px;width:auto;white-space:nowrap;text-align:left;text-decoration:none;background:#EAEBD8;color:#2875DE;font:11px tahoma}#sddm div a:hover{background:#49A3FF;color:#FFF}
</style>
<script type="text/javascript">
var timeout=500;var closetimer=0;var ddmenuitem=0;function mopen(id){mcancelclosetime();if(ddmenuitem)ddmenuitem.style.visibility='hidden';ddmenuitem=document.getElementById(id);ddmenuitem.style.visibility='visible'}function mclose(){if(ddmenuitem)ddmenuitem.style.visibility='hidden'}function mclosetime(){closetimer=window.setTimeout(mclose,timeout)}function mcancelclosetime(){if(closetimer){window.clearTimeout(closetimer);closetimer=null}}document.onclick=mclose;
</script>
<form name="actionform" style="display: none" action="./" method="POST">
<input type="hidden" name="a" value="">	
<input type="hidden" name="s" value="">	
</form>
<script>
function a(a)
{
document.actionform.a.value=a;
document.actionform.submit();
}
</script>		
<div style="float: right; padding-right: 40px">

<ul id="sddm">
<li><a href="#" onMouseOver="mopen('m3')" onMouseOut="mclosetime()">Create Users</a>
		<div id="m3" onMouseOver="mcancelclosetime()" onMouseOut="mclosetime()">
		<a href="javascript: void(a('createuser'))">Single</a>
		<a href="javascript: void(a('createmulti'))">Multiple</a>
		</div>
	</li>
	<li><a href="javascript: void(a('listusers'))">Edit Users</a>
	</li>	
	<li><a href="javascript: void(a('loginhistory'))">View Sessions</a>
	</li>	
	<li><a href="javascript: void(a('trialdays'))">Change Trial Days</a>
	</li>	
    <li><a href="javascript: void(a('change_settings'))">Settings</a>
	</li>	
	<li><a href="javascript: void(a('logout'))">Logout</a>
	</li>	
</ul>
</div>

<br><br>

<div align="center">

ML_HTML;
}

if ($mode=='docreate')
{
$newuser=trim($_REQUEST['new_user']);
$newpass=$_REQUEST['new_pass'];
$newname=trim($_REQUEST['new_name']);
$newemail=trim(strtolower($_REQUEST['new_email']));
$newseats=(int)trim($_REQUEST['new_seats']);
$newproduct=trim($_REQUEST['new_product']);
$newproduct_type = 0;
$ip=$_SERVER['REMOTE_ADDR'];

if ($newproduct == "nus")
{
    $newproduct_type = 1;    
}
if ($newproduct == "webhome")
{
    $newproduct_type = 2;    
}
if ($newproduct == "webnet")
{
    $newproduct_type = 3;    
}
if ($newproduct == "mccn")
{
    $newproduct_type = 10;    
}
if ($newproduct == "mcbp")
{
    $newproduct_type = 11;    
}



$newexpiry=trim($_REQUEST['new_expiry']);
$newtype=trim($_REQUEST['new_type']);
if ($level==8) {$newtype=0;}
{
if ($newtype!=8) {$newtype=0;}
else {$newact=time();}
}



if (!$newexpiry) {$newexpiry=0;}
if (!$newpass) {$newpass=crc($newemail.rand());}

if ($newuser && !preg_match('/^[A-Za-z0-9\-\_\.]{3,100}$/', $newuser)) {$msg='Username is invalid.';}
elseif (!$newname || !preg_match('/^[A-Za-z\s\.]{3,100}$/', $newname)) {$msg='Name is invalid.';}
elseif(!preg_match("/^([a-zA-Z0-9_'+*$%\^&!\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9:]{2,4})+$/", $newemail)) {$msg='E-mail address is invalid.';}
elseif(!preg_match('/^\d+$/', $newexpiry)) {$msg='Expiry is invalid.';}
elseif(strlen($newpass)<6 || strlen($newpass)>20) {$msg='Password is invalid. Must be 6-20 characters long.';}
else
{
    $mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
    mysql_select_db($dbname) or die('SQL error');
    //$result=mysql_query("SELECT `id` FROM `users` WHERE `email` = '".mysql_real_escape_string($newemail)."'") or die('SQL error');
    /*if (mysql_num_rows($result)) 
    {
        $msg="User will email address: $newemail already exists.";
    }
    else
    {*/
        $newact = (int)$newact;
        $result=mysql_query("INSERT INTO `users` (`user`, `created`, `life`, `pass`, `name`, `email`, `ip`, `level`, `activated`, `lic_type`, `seats`) VALUES ('".mysql_real_escape_string($newuser)."', ".time().", ".($newexpiry*24*3600).", '".$newpass."', '".mysql_real_escape_string($newname)."', '".mysql_real_escape_string($newemail)."', '".mysql_real_escape_string($ip)."', $newtype, $newact, $newproduct_type, $newseats)") or die("sql error");
        if (!$newuser)
        {
            $newuid=mysql_insert_id($mysql);
            $newuser='MWS-'.str_pad($newuid, 9, '0', STR_PAD_LEFT);
            $result=mysql_query("UPDATE `users` SET `user` = '$newuser' WHERE `id` = $newuid") or die('SQL error');
        }
        $msg="User created successfully with username: $newuser. The login information of the user has been mailed to his/her email address.";
        $to = $newemail;

        if ($newproduct_type == 0)
        {
            $subject = "MalwareSecure Antivirus Single Edition";

            $message = file_get_contents('mail_av.txt');
            $message = sprintf($message, $newseats, $newuser, $newpass);

            $headers = 'From: MSecure Data Labs Support <support@msecuredatalabs.com>' . "\r\n";
            mail($to,$subject,$message,$headers);
        }
        else if ($newproduct_type == 1)
        {
            $subject = "MalwareSecure Antivirus Network Edition";

            $message = file_get_contents('mail_nus.txt');
            $message = sprintf($message, $newseats, $newuser, $newpass);

            $headers = 'From: MSecure Data Labs Support <support@msecuredatalabs.com>' . "\r\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
            mail($to,$subject,$message,$headers);
        }  
    
    //}
}
echo <<<ML_HTML
<script>
alert('$msg');	
</script>		
ML_HTML;
exit;

}

if ($mode=='dotrialdays')
{
$trialdays=trim($_REQUEST['trial_days']);
if(!$trialdays || !preg_match('/^\d+$/', $trialdays)) {$msg='Enter the number of days correctly.';}
else
{
$f=fopen('data/trialdays.txt', 'w');
fwrite($f, $trialdays);
fclose(f);
$msg="Trail days have been successfully changed to $trialdays.";
}
echo <<<ML_HTML
<script>
alert('$msg');	
</script>		
ML_HTML;
exit;

}

if ($mode=='dochangesettings')
{
    $db_link = mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL connection error');
    mysql_select_db($dbname, $db_link) or die(mysql_error($db_link));
    mysql_query("CREATE TABLE IF NOT EXISTS global_settings(name CHAR(20) PRIMARY KEY, value TEXT);", $db_link) or die(mysql_error($db_link));

    $ss_server=trim($_REQUEST['ss_server']);
    mysql_query("replace into global_settings set name='smtp_server', value='".$ss_server."';", $db_link) or die(mysql_error($db_link));

    $ss_port=trim($_REQUEST['ss_port']);
    mysql_query("replace into global_settings set name='smtp_port', value='".$ss_port."';", $db_link) or die(mysql_error($db_link));

    $ss_user=trim($_REQUEST['ss_user']);
    mysql_query("replace into global_settings set name='smtp_user', value='".$ss_user."';", $db_link) or die(mysql_error($db_link));

    $ss_pass=trim($_REQUEST['ss_pass']);
    mysql_query("replace into global_settings set name='smtp_pass', value='".$ss_pass."';", $db_link) or die(mysql_error($db_link));

    $ss_distrib=trim($_REQUEST['ss_distrib']);
    mysql_query("replace into global_settings set name='distrib', value='".$ss_distrib."';", $db_link) or die(mysql_error($db_link));

    exit;
}


if ($mode=='docreatemulti')
{
$multicount=trim($_REQUEST['multi_count']);
$newexpiry=trim($_REQUEST['new_expiry']);
if (!$newexpiry) {$newexpiry=0;}


$ip=$_SERVER['REMOTE_ADDR'];
if(!$multicount || !preg_match('/^\d+$/', $multicount)) {$msg='Enter the number of users correctly.';}
elseif(!preg_match('/^\d+$/', $newexpiry)) {$msg='Expiry is invalid.';}

else
{
$mysql=mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL error');
mysql_select_db($dbname) or die('SQL error');
for($i=0;$i<$multicount;$i++)
{
$newpass=crc($i.rand());	
$result=mysql_query("INSERT INTO `users` (`created`, `life`, `pass`, `ip`) VALUES (".time().", ".($newexpiry*24*3600).", '".$newpass."', '".mysql_real_escape_string($ip)."')") or die('SQL error');
$newuid=mysql_insert_id($mysql);
$newuser='MWS-'.str_pad($newuid, 9, '0', STR_PAD_LEFT);
if (!$firstuser) {$firstuser=$newuser;}
$result=mysql_query("UPDATE `users` SET `user` = '$newuser' WHERE `id` = $newuid") or die('SQL error');
}
if (!$lastuser) {$lastuser=$newuser;}
$msg="$multicount users from $firstuser to $lastuser with $newexpiry day expiry have been created successfully.";
}
echo <<<ML_HTML
<script>
alert('$msg');	
</script>		
ML_HTML;
exit;

}




if ($mode=='createuser')
{
if ($level==9) {$subadmstr='   <option value="8">Sub-Admin</option>';}
echo <<<ML_HTML
<form target="do_frame" method="POST" action="./">
<input type="hidden" name="a" value="docreate">
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family:Verdana; font-size:8pt" bordercolor="#111111">
  <tr>
    <td colspan="2" align="center"><u>Create New User</u><br>
&nbsp;</td>
  </tr>
  <tr>
    <td width="50%" align="right">Name:</td>
    <td width="50%">
    <input type="text" name="new_name" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>
  <tr>
    <td width="50%" align="right">E-mail:</td>
    <td width="50%">
    <input type="text" name="new_email" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>  
  <tr>
    <td width="50%" align="right">Username:<br>
    (leave blank for system generated)</td>
    <td width="50%">
    <input type="text" name="new_user" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>
  <tr>
    <td width="50%" align="right">Password:<br>
    (leave blank for random)</td>
    <td width="50%">
    <input type="text" name="new_pass" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>
  <tr>
    <td width="50%" align="right">Product:</td>
    <td width="50%">
        <select name="new_product">
            <option value="avhome">AV Home</option>
            <option value="nus">Network Update Server</option>
            <option value="webhome">Web Security Home</option>
            <option value="webnet">CloudDeny WebSecurity</option>
            <option value="mccn">Management Console(for Cloud Network)</option>
            <option value="mcbp">Management Console(BPD)</option>
        </select> 
    </td>
  </tr>
  <tr>
    <td width="50%" align="right">Number of users:</td>
    <td width="50%">
    <input type="text" value="1" name="new_seats" size="30" style="font-family: Verdana; font-size: 8pt"></td>
  </tr>
  <tr>
    <td width="50%" align="right">Expiry:<br>
    (leave blank for no expiry)</td>
    <td width="50%">
    <input type="text" value="15" name="new_expiry" size="6" style="font-family: Verdana; font-size: 8pt"> 
    days from first login.</td>
  </tr>
  <tr>
    <td width="50%" align="right">Account Type:</td>
    <td width="50%">
    <select size="1" name="new_type" style="font-family: Verdana; font-size: 8pt">
    <option selected value="0">User</option>
$subadmstr
    </select></td>
  </tr>
  <tr>
    <td colspan="2" align="center"><br><input style="font-family: Verdana; font-size: 8pt" type="submit" name="" value=" Submit "></td>
  </tr>
</table>
</form>
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

ML_HTML;
exit;
}

if ($mode=='createmulti')
{
echo <<<ML_HTML
<form target="do_frame" method="POST" action="./">
<input type="hidden" name="a" value="docreatemulti">
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family:Verdana; font-size:8pt" bordercolor="#111111">
  <tr>
    <td colspan="2" align="center"><u>Create Multiple Users</u><br><br><br>

Number of users to create:<br><br>
    <input type="text" name="multi_count" size="10" style="font-family: Verdana; font-size: 8pt">

<br><br>Validity:<br><br>
    <input type="text" name="new_expiry" value="15" size="10" style="font-family: Verdana; font-size: 8pt"><br>
</td>
  </tr>

  <tr>
    <td colspan="2" align="center"><br><input style="font-family: Verdana; font-size: 8pt" type="submit" name="" value=" Submit "></td>
  </tr>
</table>
</form>
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

ML_HTML;
exit;
}

if ($mode=='trialdays')
{
$td=@file_get_contents('data/trialdays.txt');

echo <<<ML_HTML
	
<form target="do_frame" method="POST" action="./">
<input type="hidden" name="a" value="dotrialdays">
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family:Verdana; font-size:8pt" bordercolor="#111111">
  <tr>
    <td colspan="2" align="center"><u>Change the number of days for trail accounts.</u><br><br><br>

Trial Days:<br><br>
    <input type="text" value="$td" name="trial_days" size="10" style="font-family: Verdana; font-size: 8pt">
</td>
  </tr>

  <tr>
    <td colspan="2" align="center"><br><input style="font-family: Verdana; font-size: 8pt" type="submit" name="" value=" Submit "></td>
  </tr>
</table>
</form>
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

ML_HTML;
exit;
}
if ($mode=='change_settings')
{
    $db_link = mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL connection error');
    mysql_select_db($dbname, $db_link) or die(mysql_error($db_link));
    mysql_query("CREATE TABLE IF NOT EXISTS global_settings(name CHAR(20) PRIMARY KEY, value TEXT);", $db_link) or die(mysql_error($db_link));
    $result = mysql_query("SELECT value FROM global_settings WHERE name='smtp_server';", $db_link) or die(mysql_error($db_link));
    $row = mysql_fetch_assoc($result);
    $ss_srv = $row["value"];

    $result = mysql_query("SELECT value FROM global_settings WHERE name='smtp_port';", $db_link) or die(mysql_error($db_link));
    $row = mysql_fetch_assoc($result);
    $ss_port = $row["value"];

    $result = mysql_query("SELECT value FROM global_settings WHERE name='smtp_user';", $db_link) or die(mysql_error($db_link));
    $row = mysql_fetch_assoc($result);
    $ss_user = $row["value"];

    $result = mysql_query("SELECT value FROM global_settings WHERE name='smtp_pass';", $db_link) or die(mysql_error($db_link));
    $row = mysql_fetch_assoc($result);
    $ss_pass = $row["value"];

    $result = mysql_query("SELECT value FROM global_settings WHERE name='distrib';", $db_link) or die(mysql_error($db_link));
    $row = mysql_fetch_assoc($result);
    $ss_distrib = $row["value"];
    $ss_dist_ver = get_product_version($ss_distrib);

echo <<<ML_HTML
	
<form target="do_frame" method="POST" action="./">
<input type="hidden" name="a" value="dochangesettings">
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-family:Verdana; font-size:8pt" bordercolor="#111111">
  <tr>
    <td colspan="2" align="center"><u>Global Settings</u><br><br></td>
    <td />
  </tr>
  <tr>
    <td align="right">SMTP Server:</td>
    <td align="left">
        <input type="text" name="ss_server" value='$ss_srv' size="10" style="font-family: Verdana; font-size: 8pt"> : <input type="text" value='$ss_port' name="ss_port" size="3" style="font-family: Verdana; font-size: 8pt">
    </td>
  </tr>
  <tr>
    <td align="right">User name:</td>
    <td align="left">
        <input value='$ss_user' type="text" name="ss_user" size="10" style="font-family: Verdana; font-size: 8pt">
    </td>
  </tr>
  <tr>
    <td align="right">Password:</td>
    <td align="left">
        <input value='$ss_pass' type="text" name="ss_pass" size="10" style="font-family: Verdana; font-size: 8pt">
    </td>
  </tr>
  <tr>
    <td align="right">Upgrade distribution:</td>
    <td align="left">
        <input value='$ss_distrib' type="text" name="ss_distrib" size="50" style="font-family: Verdana; font-size: 8pt"> $ss_dist_ver
    </td>
  </tr>
  <tr>
    <td colspan="2" align="center"><br><input style="font-family: Verdana; font-size: 8pt" type="submit" name="" value=" Save "></td>
  </tr>
</table>
</form>
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

ML_HTML;
exit;
}
if ($mode=='showinfo')
{
    $db_link = mysql_connect($dbhost, $dbuser, $dbpass) or die('SQL connection error');
    mysql_select_db($dbname, $db_link) or die(mysql_error($db_link));
echo <<<ML_HTML

<script>
function export_info(id)
{
    document.actionform.a.value='exportinfo';
    document.actionform.s.value=id;
    document.actionform.submit();
}
</script>
	
<table border="1" cellpadding="5" cellspacing="0" style="font-family:Verdana; font-size:8pt" bordercolor="#111111">
  <tr>
    <td colspan="2" align="center"><u>User Information</u><br><br></td>
    <td />
    <td />
    <td />
  </tr>
  <tr>
    <td align="left">Host Name:</td>
    <td align="left">Mac Address:</td>
    <td align="left">Country:</td>
    <td align="left">Operating System:</td>
    <td align="left">NUS users:<br/>(Valid for NUS only)</td>
  </tr>
ML_HTML;

    $result = mysql_query("SELECT host, mac, country, os, nus_users FROM client_info WHERE user='".trim($search_keyword)."';", $db_link) or die(mysql_error($db_link));
    while ($row = mysql_fetch_assoc($result))
    {
        $c_host = $row["host"];
        $c_mac = $row["mac"];
        $c_country = $row["country"];
        $c_os = $row["os"];
        $c_nus = (int)$row["nus_users"];

echo <<<ML_HTML
  <tr>
    <td align="left"><b>$c_host</b></td>
    <td align="left"><b>$c_mac</b></td>
    <td align="left"><b>$c_country</b></td>
    <td align="left"><b>$c_os</b></td>
    <td align="left"><b>$c_nus</b></td>
  </tr>
ML_HTML;
    }
echo <<<ML_HTML
</table>
<input type="button" onClick="export_info('$search_keyword')" value="Export to CSV" style="font-family: Tahoma; font-size: 8pt">

ML_HTML;


exit;
}


if ($mode=='listusers' || $mode=='exportusers')
{
if ($level==8) {$whrstr=' WHERE `level` = 0 ';}
$result=mysql_query("SELECT `id`, `user`, `pass`, `name`, `email`, `created`, `activated`, `life`, `status`, `level`, `lic_type`, `seats`  FROM `users` $whrstr ORDER BY `level` DESC") or die('SQL error');
echo <<<ML_HTML
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

<form target="do_frame" name="ml_do_form" method="POST" action="./">
<input type="hidden" name="a" value="">
<input type="hidden" name="id" value="">
<input type="hidden" name="user" value="">
<input type="hidden" name="name" value="">
<input type="hidden" name="pass" value="">
<input type="hidden" name="email" value="">
<input type="hidden" name="life" value="">
</form>

<script>
function round_float(x,n)
{
  if(!parseInt(n))
  	var n=0;
  if(!parseFloat(x))
  	return false;
  return Math.round(x*Math.pow(10,n))/Math.pow(10,n);
}
function user_update(id)
{
    var expDate = Date.parse(document.getElementById('exp_'+id).value);
    if (!isNaN(expDate))
    {
        var startDate = document.getElementById('activ_'+id).value;
        if (parseInt(startDate) > 0)
        {
            var liveDays = (expDate/86400000) - (startDate/86400);
            if (liveDays <= 0)
            {
                liveDays = 0;
                document.getElementById('mlu_life_'+id).value = "0";
            }
            else
            {
                document.getElementById('mlu_life_'+id).value = round_float(Math.ceil(liveDays));
            }

        }
    }       

document.ml_do_form.a.value='updateuser';
document.ml_do_form.id.value=id;
document.ml_do_form.user.value=document.getElementById('mlu_user_'+id).value;
document.ml_do_form.pass.value=document.getElementById('mlu_pass_'+id).value;
document.forms['ml_do_form']['name'].value=document.getElementById('mlu_name_'+id).value;
document.ml_do_form.email.value=document.getElementById('mlu_email_'+id).value;
document.ml_do_form.life.value=document.getElementById('mlu_life_'+id).value;
document.ml_do_form.submit();
}
function user_delete(id)
{
if (confirm('Click on OK if you really wish to delete this user otherwise click Cancel.'))
{	
document.ml_do_form.a.value='deleteuser';
document.ml_do_form.id.value=id;
document.ml_do_form.submit();
}
}	
function user_suspend(id)
{
document.ml_do_form.a.value='suspenduser';
document.ml_do_form.id.value=id;
document.ml_do_form.submit();
}
function user_unsuspend(id)
{
document.ml_do_form.a.value='unsuspenduser';
document.ml_do_form.id.value=id;
document.ml_do_form.submit();
}
function resend_lic(id)
{
document.ml_do_form.a.value='resendlic';
document.ml_do_form.id.value=id;
document.ml_do_form.submit();
}
function show_info(id)
{
    document.actionform.a.value='showinfo';
    document.actionform.s.value=document.getElementById('mlu_user_'+id).value;
    
    document.actionform.submit();

    /*var elBtn = document.getElementById('mlu_info_'+id);
    var elRow = document.getElementById('mlu_trid_'+id);
    if (elRow != null)
    {
        if (elRow.style.display == "none")
        {
            elRow.style.display = "table-row";
            elBtn.value = "Hide Info";
        }    
        else
        {
            elRow.style.display = "none";
            elBtn.value = "Show Info";
        }    
    }*/ 
}

function users_export()
{
document.ml_do_form.a.value='exportusers';
document.ml_do_form.submit();
}
function do_search()
{
    document.actionform.a.value='listusers';
    document.actionform.s.value=document.getElementById('id_search').value
    document.actionform.submit();
}
</script>	

	
<form name="ml_list_form" method="POST" action="./">
<table border="0" cellpadding="3" cellspacing="0" style="border-collapse: collapse; font-size: 8pt; font-family: Tahoma" bordercolor="#111111">
  <tr>
    <td>#</td>
    <td align="center"><u>Username</u></td>
    <td align="center"><u>Password</u></td>
    <td align="center"><u>Name</u></td>
    <td align="center"><u>E-mail</u></td>
    <td align="center"><u>Status</u></td>
    <td align="center"><u>Created</u></td>
    <td align="center"><u>Validity</u></td>
    <td align="center"><u>Product</u></td>
    <td align="center"><u>Number of users</u></td>
    <td align="center"><u>Expiry</u></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><input type="button" onClick="users_export()" value="Export Users" style="font-family: Tahoma; font-size: 8pt"></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td><select><option selected value="1">All Products</option><option value="2">AV Home</option><option value="3">Web Security Home</option><option value="10">Management Console(for Cloud Network)</option><option value="11">Management Console(BPD)</option></select></td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td><input type="text" id="id_search" size="15" value='$search_keyword'/></td>
    <td><button type="button" onClick="do_search()" style="font-family: Tahoma; font-size: 8pt">Search</button></td>
    <td>&nbsp;</td>
  </tr>
ML_HTML;

$export="S.No.,Username,Password,Name,Email,Created,Status,Expiry(Days),Type\r\n";
$n=0;
while(list($id, $user, $pass, $name, $email, $created, $activated, $life, $status, $level, $lic_type, $seats)=mysql_fetch_row($result))
{
    $seats = (int)$seats;
    $lic_type_str = "AV Home";
    if ($lic_type == 1)
    {
        $lic_type_str = "NUS";
    }
    if ($lic_type == 2)
    {
        $lic_type_str = "Web Home";
    }
    if ($lic_type == 10)
    {
        $lic_type_str = "Management Console(for Cloud Network)";
    }
    if ($lic_type == 11)
    {
        $lic_type_str = "Management Console(BPD)";
    }


    $created=date('d M Y', $created);
    $status1='Active';
    if ($level!=9 && !$activated) 
    {
        $status1='Not activated yet';
    }
    if ($level!=9 && $activated && $life && (time()-($activated+$life)) > 0) 
    {
        $status1='Expired';
    }
    if ($status==1) 
    {
        $status1='Suspended';
    }
    if(strlen($search_keyword) > 0 && stristr($user, $search_keyword) == FALSE && stristr($name, $search_keyword) == FALSE && stristr($email, $search_keyword) == FALSE && stristr($status1, $search_keyword) == FALSE) 
    {
        continue;
    }

    
    if ($level!=9 && $activated && $life) 
    {
        $expiry=date('d M Y', $activated+$life);
    }
    else 
    {
        $expiry='';
    }
    if ($level!=9) 
    {
        $life=round($life/(24*3600));
    }
    else 
    {
        $life='';
    }
    if ($status==1) 
    {
        $suspendbutton='<input type="button" onClick="user_unsuspend(\''.$id.'\')" value="Suspend / Unsuspend" style="font-family: Tahoma; font-size: 8pt">';
    }
    else 
    {
        $suspendbutton='<input type="button" onClick="user_suspend(\''.$id.'\')" value="Suspend / Unsuspend" style="font-family: Tahoma; font-size: 8pt">';
    }
    if ($level==9) 
    {
        $suspendbutton='<input type="button" onClick="alert(\'Administrators cannot be suspended.\')" value="Suspend / Unsuspend" style="font-family: Tahoma; font-size: 8pt">';
    }
    if ($level==9) 
    {
        $deletebutton='<input type="button" onClick="alert(\'Administrators cannot be deleted.\')" value="Delete" style="font-family: Tahoma; font-size: 8pt">';
    }
    else 
    {
        $deletebutton='<input type="button" onClick="user_delete(\''.$id.'\')" value="Delete" style="font-family: Tahoma; font-size: 8pt">';
    }
    $n++;

    if ($level==9) 
    {
        $level1='Administrator';
    }
    elseif ($level==8) 
    {
        $level1='Sub-Admin';
    }
    elseif ($level==0) 
    {
        $level1='User';
    }
    if ($mode=='exportusers')
    {
        $export.="$n,$user,$pass,$name,$email,$created,$status1,$life,$level1\r\n";
    }

    if ($level==9) 
    {
        $color='#A8FFA8';
    }
    elseif ($level==8) 
    {
        $color='#FFFFAA';
    }
    else 
    {
        $color='';
    }

    if ($status1=='Expired') 
    {
        $color='red';
    }
    elseif ($status1=='Suspended') 
    {
        $color='orange';
    }
    $created = strtoupper($created);
    $expiry = strtoupper($expiry);
echo <<<ML_HTML
  <tr style="background-color: $color">
    <td>$n.</td>
    <td><input type="text" id="mlu_user_$id" size="23" style="font-size: 8pt; font-family: Tahoma;" value="$user" ></td>
    <td><input type="text" id="mlu_pass_$id" size="8" style="font-size: 8pt; font-family: Tahoma" value="$pass"></td>
    <td><input type="text" id="mlu_name_$id" size="10" style="font-size: 8pt; font-family: Tahoma" value="$name"></td>
    <td><input type="text" id="mlu_email_$id" size="10" style="font-size: 8pt; font-family: Tahoma" value="$email"></td>
    <td><input type="text" readonly size="12" style="font-size: 8pt; font-family: Tahoma" value="$status1"></td>
    <td><input type="text" id="mlu_start_$id" readonly size="12" style="font-size: 8pt; font-family: Tahoma" value="$created"></td>
    <td><input type="text" id="mlu_life_$id" size="4" style="font-size: 8pt; font-family: Tahoma" value="$life"></td>
    <td><input type="text" id="prod_type__$id" readonly size="12" style="font-size: 8pt; font-family: Tahoma" value="$lic_type_str"></td>
    <td><input type="text" id="seats__$id" readonly size="12" style="font-size: 8pt; font-family: Tahoma" value="$seats"></td>
    <td><input type="text" id="exp_$id" readonly size="12" style="font-size: 8pt; font-family: Tahoma" value="$expiry"><input type="hidden" id="activ_$id" value="$activated"></td>
    <td><input type="checkbox">Single seat</td>
    <td><input type="button" onClick="user_update('$id')" value="Update" style="font-family: Tahoma; font-size: 8pt"></td>
    <td>$deletebutton</td>
    <td>$suspendbutton</td>
    <td><input type="button" onClick="resend_lic('$id')" value="Resend Lic." style="font-family: Tahoma; font-size: 8pt"></td>
    <td><input id="mlu_info_$id" type="button" onClick="show_info('$id')" value="Show Info" style="font-family: Tahoma; font-size: 8pt"></td>
  </tr>
  <tr id="mlu_trid_$id" style="display: none;">
    <td colspan="15">User info and description will go here</td>
  </tr>

ML_HTML;
  //break;
}

if ($mode=='exportusers')
{
$time=time();
foreach (@glob("data/*.csv") as $filename) {if ($time-filemtime($filename) > 86400) {unlink($filename);}}
$f=fopen("data/$time.csv", 'w');
fwrite($f, $export);
fclose($f);
echo <<<ML_HTML
<script>
document.location.href="data/$time.csv";
</script>	
ML_HTML;
}
exit;
}

if ($mode=='loginhistory')
{
include(dirname(__FILE__)."/geoip.inc");
$geoip = geoip_open(dirname(__FILE__)."/geoip.dat", GEOIP_STANDARD);

$result=mysql_query("SELECT sessions.id, sessions.created, sessions.ip, sessions.browser, users.user, users.name FROM sessions, users where sessions.uid = users.id ORDER BY sessions.created DESC") or die('SQL error');

if ($level==9)
{
echo <<<ML_HTML
<form target="do_frame" name="ml_do_form" method="POST" action="./">
<input type="hidden" name="a" value="purgehistory">
<p align="center"><font face="Verdana" style="font-size: 8pt">
<input type="submit" value="Purge" style="font-size: 8pt; font-family: Tahoma"> 
session history older than
<input type="text" name="days" size="3" style="font-size: 8pt; font-family: Tahoma" value="10"> 
days.</font></p>
</form>
ML_HTML;
}

echo <<<ML_HTML
<iframe style="visibility: hidden" name="do_frame" marginwidth="1" marginheight="1" height="1" width="1" border="0" frameborder="0">
</iframe>

<form name="ml_list_form" method="POST" action="./">
<table border="0" cellpadding="5" cellspacing="0" style="border-collapse: collapse; font-size: 8pt; font-family: Tahoma" bordercolor="#111111">
  <tr>
    <td align="center">#</td>
    <td width="100px" align="center"><u>Time</u></td>
    <td width="300px" align="center"><u>User</u></td>
  </tr>
  <tr>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
    <td>&nbsp;</td>
  </tr>
ML_HTML;

$n=0;
while(list($id, $time, $ip, $browser, $user, $name)=mysql_fetch_row($result))
{
$n++;
$relc=rel_time($time);
$created=date('d-M-Y H:i', $time);
if ($color=='#F4F4F4') {$color='#FFFFFF';}
else {$color='#F4F4F4';}
if (!$name) {$name='-';}
$country=getcountry($ip);
$text=htmlspecialchars("Name: $name\nLogged in: $created ($relc)\nIP Address: $ip\nCountry: $country\n\nBrowser: $browser");
echo <<<ML_HTML
  <tr onClick="document.getElementById('sess_$id').style.display='';" style="cursor: pointer; background-color: $color">
    <td align="center">$n.</td>
    <td align="center">$created</td>
    <td align="center">$user</td>
  </tr>
<tr style="display: none" id="sess_$id"><td colspan="3"><textarea rows="6" cols="80" style="font-family: Tahoma; font-size: 8pt; background-color: #FFFFE6">$text</textarea></td></tr>  
ML_HTML;
}
exit;
}



if ($mode=='deleteuser')
{
if ($level==8) {$whrstr=' AND `level` = 0 ';}
$id=$_REQUEST['id'];
$result=mysql_query("DELETE FROM `users` WHERE `id` = '".mysql_real_escape_string($id)."' $whrstr") or die('SQL error');
echo <<<ML_HTML
<html>
<body onload="document.ml_redirect_form.submit()">
<script>
alert('User deleted successfully.');	
</script>
<form target="_top" name="ml_redirect_form" method="POST" action="./">
<input type="hidden" name="a" value="listusers">
</form>
</body>
</html>		
ML_HTML;
exit;
}

if ($mode=='resendlic')
{
$id=$_REQUEST['id'];
$result=mysql_query("SELECT `user`, `pass`, `email`, `seats`, `lic_type` FROM `users` WHERE `id` = '".mysql_real_escape_string($id)."'") or die('SQL error');
if (!mysql_num_rows($result)) {$msg="User not found.";}
list($user, $pass, $email, $seats, $lic_type)=mysql_fetch_row($result);
if (!$email) {$msg='No email address found for the user !!';}
else
{
        $to = $email;

        if ($lic_type == 0)
        {
            $subject = "MalwareSecure Antivirus Single Edition";

            $message = file_get_contents('mail_av.txt');
            $message = sprintf($message, $seats, $user, $pass);

            $headers = 'From: MSecure Data Labs Support <support@msecuredatalabs.com>' . "\r\n";
            mail($to,$subject,$message,$headers);
        }
        else if ($lic_type == 1)
        {
            $subject = "MalwareSecure Antivirus Network Edition";

            $message = file_get_contents('mail_nus.txt');
            $message = sprintf($message, $seats, $user, $pass);

            $headers = 'From: MSecure Data Labs Support <support@msecuredatalabs.com>' . "\r\n";
            $headers .= "MIME-Version: 1.0" . "\r\n";
            $headers .= "Content-type:text/html;charset=iso-8859-1" . "\r\n";
            mail($to,$subject,$message,$headers);
        }  


$msg="Account login information has been resent to the user.";
}
echo <<<ML_HTML
<html>
<body onLoad="document.ml_redirect_form.submit()">
<script>
alert('$msg');	
</script>
</body>
</html>		
ML_HTML;
exit;
}

if ($mode=='suspenduser')
{
if ($level==8) {$whrstr=' AND `level` = 0 ';}
$id=$_REQUEST['id'];
$result=mysql_query("UPDATE `users` SET `status` = 1 WHERE `id` = '".mysql_real_escape_string($id)."' $whrstr") or die('SQL error');
echo <<<ML_HTML
<html>
<body onLoad="document.ml_redirect_form.submit()">
<script>
alert('User suspended successfully.');	
</script>
<form target="_top" name="ml_redirect_form" method="POST" action="./">
<input type="hidden" name="a" value="listusers">
</form>
</body>
</html>		
ML_HTML;
exit;
}

if ($mode=='unsuspenduser')
{
if ($level==8) {$whrstr=' AND `level` = 0 ';}
$id=$_REQUEST['id'];
$result=mysql_query("UPDATE `users` SET `status` = 0 WHERE `id` = '".mysql_real_escape_string($id)."' $whrstr") or die('SQL error');
echo <<<ML_HTML
<html>
<body onLoad="document.ml_redirect_form.submit()">
<script>
alert('User unsuspended successfully.');	
</script>
<form target="_top" name="ml_redirect_form" method="POST" action="./">
<input type="hidden" name="a" value="listusers">
</form>
</body>
</html>		
ML_HTML;
exit;
}

if ($mode=='updateuser')
{
if ($level==8) {$whrstr=' AND `level` = 0 ';}
$id=$_REQUEST['id'];
$user=trim($_REQUEST['user']);
$name=trim($_REQUEST['name']);
$email=trim(strtolower($_REQUEST['email']));
$life=trim($_REQUEST['life']);
$pass=$_REQUEST['pass'];

if ($user && !preg_match('/^[A-Za-z0-9\-\_\.]{3,100}$/', $user)) {$msg='Username is invalid.';}
elseif ($name && !preg_match('/^[A-Za-z\s\.]{3,100}$/', $name)) {$msg='Name is invalid.';}
elseif($email && !preg_match("/^([a-zA-Z0-9_'+*$%\^&!\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9:]{2,4})+$/", $email)) {$msg='E-mail address is invalid.';}
elseif($pass && (strlen($pass)<6 || strlen($pass)>20)) {$msg='Password is invalid. Must be 6-20 characters long.';}

else
{
$result=mysql_query("SELECT `level` FROM `users` WHERE `id` = '".mysql_real_escape_string($id)."' $whrstr") or die('SQL error');
if (!mysql_num_rows($result)) {$msg='User not found.';}
else
{
list($level)=mysql_fetch_row($result);

if($level!=9 && !preg_match('/^\d+$/', $life)) {$msg='Validity is invalid.';}
else
{
if ($level==9) {$life=0;}
elseif ($life=='00') {$life=1;}
else {$life=$life*3600*24;}
if ($pass) {$passq=", `pass` = '".mysql_real_escape_string($pass)."'";}
mysql_query("UPDATE `users` SET `user` = '".mysql_real_escape_string($user)."'$passq, `name` = '".mysql_real_escape_string($name)."', `email` = '".mysql_real_escape_string($email)."', `life` = $life WHERE `id` = '".mysql_real_escape_string($id)."'") or die("");

$msg='User updated successfully.';
}
}
}


echo <<<ML_HTML
<html>
<body>
<script>
alert('$msg');	
</script>
</body>
</html>		
ML_HTML;
exit;
}

if ($mode=='purgehistory')
{
$days=trim($_REQUEST['days']);
if ($level==8) {die('<script> alert("You are not allowed to perform this action."); </script>');}

if($days && preg_match('/^\d+$/', $days))
{
$timer=time()-($days*24*3600);
$result=mysql_query("DELETE FROM `sessions` WHERE `created` < $timer") or die('SQL error');
}
echo <<<ML_HTML
<html>
<body onLoad="document.ml_redirect_form.submit()">
<script>
alert('Login history older than $days has been cleared.');	
</script>
<form target="_top" name="ml_redirect_form" method="POST" action="./">
<input type="hidden" name="a" value="loginhistory">
</form>
</body>
</html>		
ML_HTML;
exit;
}


exit;

function crc($a) {return str_pad(dechex(crc32($a)), 8, '0', STR_PAD_LEFT);}

function getcountry($ip)
{
global $geoip;
$country=geoip_country_name_by_addr($geoip, $ip);
if ($country) {return $country;}
return '--';
}

function rel_time($from)
{
$diff=time()-$from;

$year   = 29030400;
$month  = 2419200;
$week   = 604800;
$day    = 86400;
$hour   = 3600;
$minute = 60;   
$second = 1;

if ($diff > $year) {$diff=round(($diff/$year)).' yr/s';}
elseif ($diff > $month) {$diff=round(($diff/$month)).' month/s';}
elseif ($diff > $week) {$diff=round(($diff/$week)).' week/s';}
elseif ($diff > $day) {$diff=round(($diff/$day)).' day/s';}
elseif ($diff > $hour) {$diff=round(($diff/$hour)).' hour/s';}
elseif ($diff > $minute) {$diff=round(($diff/$minute)).' min/s';}
else {$diff=$diff.' sec/s';}

return $diff;
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
