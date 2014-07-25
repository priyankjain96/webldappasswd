<html>
<head>

<?php

// Warning:
//  exec() doesn't works when PHP safe_mode is enabled:
//    - STDERR redirection will be catched by automatic escapeshellcmd()
//    - running of external binaries is not allowed

error_reporting(E_ALL);

////////   Language-dependent strings   ////////

$textTitle               = 'Change password on '.$_SERVER['SERVER_NAME'];
$textHeader              = 'Change your system password on cloud server';
$msgEnterLogin           = 'Login';
$msgEnterCurrentPassword = 'Current password';
$msgEnterNewPassword     = 'New password';
$msgRepeatNewPassword    = 'Repeat new password';
$msgNewPasswordMismatch  = 'Passwords did not match. Please try again.';
$msgNewPasswordUnchanged = 'Old and new passwords are equal. Please try again.';
$msgPasswordChanged      = 'Password is successfully changed!';
$msgOk                   = 'Congratulations!';
$msgError                = 'Error';

$lang = $_SERVER["HTTP_ACCEPT_LANGUAGE"];  // 'ru', fixme: recode from locale to ContentType charset!!!
$langfile = "./lang/$lang.php";
echo "<!-- Debug: langfile = '$langfile' -->\n";
if (is_readable($langfile))
	include_once($langfile);

////////  HTTP request data  ///////////////////

function get_arg($data, $key) {
	if (array_key_exists($key, $data))
		return html_entity_decode($data[$key]);
	return "";
}

        $userLogin = get_arg($_REQUEST, 'login');
  $currentPassword = get_arg($_POST, 'currentPassword');
      $newPassword = get_arg($_POST, 'newPassword');
$repeatNewPassword = get_arg($_POST, 'repeatNewPassword');

$ldapPasswd       = '/usr/local/bin/ldappasswd';	// Doesn't works in safe mode!
$ldapFullUsername = "uid=$userLogin,dc=example,dc=com";	// Override this in ldap.inc!
require('./ldap.php');

?>

<title><?php echo $textTitle;?></title>
</head>
<body>
<center><h2><?php echo "Welcome to CapitalVia Cloud Server";?></h2></center>
<center><h2><?php echo $textHeader;?></h2></center>
<?php

function put_box($title, $titleForeColor, $titleBackColor, $msg, $msgForeColor, $msgBackColor) {
if (is_array($msg))
	$msg = implode('<br/>', $msg);
echo "
<table border='0' cellpadding='10' align='center' bgcolor='$titleBackColor'>
<tr><th align='left' valign='center' bgcolor='$titleBackColor'><font color='$titleForeColor'><big>$title</big></font></th></tr>
<tr><td align='left' valign='center' bgcolor='$msgBackColor'  ><font color='$msgForeColor'  >$msg</font></td></tr>
</table>
";
}

function    ok_box($text) { global $msgOk;    put_box($msgOk,    'White',  'Blue', $text, 'Green', 'Wheat'); }
function error_box($text) { global $msgError; put_box($msgError, 'Yellow', 'Red',  $text, 'Red',   'Wheat'); }

$runForm = TRUE;

if ($userLogin && $currentPassword && $newPassword && $repeatNewPassword) {
	if ($newPassword != $repeatNewPassword) {
		error_box($msgNewPasswordMismatch);
	} elseif ($newPassword == $currentPassword) {
		error_box($msgNewPasswordUnchanged);
	} else {
		$ldapOutput = array();
		$ldapCommand = "$ldapPasswd -v -x -D '$ldapFullUsername'"
			.(isset($ldapURI) ? " -h '$ldapURI'" : "")
			." -s '$newPassword' -w '$currentPassword'";
		exec(escapeshellcmd($ldapCommand).' 2>&1', $ldapOutput, $ldapResult);
	//	echo "<!-- Debug: ldapCommand = '$ldapCommand' -->\n";
	//	echo "<!-- Debug: ldapOutput  = [ ".implode(' :: ', $ldapOutput)." ] -->\n";
	//	echo "<!-- Debug: ldapResult  = '$ldapResult' -->\n";
		if ($ldapResult == 0) {
			ok_box($msgPasswordChanged);
			$runForm = FALSE;
		} else {
			error_box($ldapOutput);
		}
	}
}

if ($runForm) {

function put_line($title, $type, $name, $value) {
echo "
<tr>
	<td align='right'>$title:</td>
	<td align='left'><input type='$type' name='$name' value='$value' maxlength='255'/></td>
</tr>";
}

?>
<form action="<?php echo $_SERVER['PHP_SELF'];?>" method="POST">
<p>
<table align="center" border="0" cellpadding="4">
<?php
put_line($msgEnterLogin,           'text',     'login',             $userLogin);
put_line($msgEnterCurrentPassword, 'password', 'currentPassword',   $currentPassword);
put_line($msgEnterNewPassword,     'password', 'newPassword',       $newPassword);
put_line($msgRepeatNewPassword,    'password', 'repeatNewPassword', $repeatNewPassword);
?>
<tr><td colspan='2' align='center' valign='bottom'>
<br/>
<input type='submit'/>&nbsp;&nbsp;&nbsp;&nbsp;
<input type='reset' />
</td></tr>
</table>
</p>
<p align='right'>
<small>
<a href='http://ilya-evseev.narod.ru/posix/webldappasswd/'
>Powered by web-frontend for LDAP password ver.0.1.0</a>
</small>
</p>
</form>
<?php } ?>
</body>
</html>
