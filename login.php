<?php
/*
   MUD4TLD - Martin's User and Domain system for Top Level Domains.
   Written 2012-2014 By Martin COLEMAN.
   This software is hereby dedicated to the public domain.
   Made for the OpenNIC Project.
   http://www.mchomenet.info/mud4tld.html
*/
include("regnum.cfg");
include("functions.inc");
sec_session_start();

function login($username, $password)
{
	if ( ! valid_username($username) )
	{
		display_error("Incorrect username or password or account not verified. Please try again.");
	}
	$hash = crypt_pass($password);
	$base = database_open();
	$stmt = $dbh->prepare("SELECT * FROM users WHERE username=':username' AND verified=1 LIMIT 1");
	$stmt->bindParam(':username', $username);
	$stmt->execute();
	$results = $stmt->fetch();

	if ( password_verify($password, $results['password']) )
	{
		$_SESSION['username'] = $username;
		$_SESSION['userid'] = $results['id'];
		$user_browser = $_SERVER['HTTP_USER_AGENT'];
		$_SESSION['login_string'] = hash('sha512', $hash . $user_browser);
		header("location: user.php");
	} 
	else 
	{
		display_error("Incorrect username or password or account not verified. Please try again.");
	}
}

function send_password($user, $email) {

	if ( ! filter_var($email, FILTER_VALIDATE_EMAIL) )
	{
		display_error("Invalid email account. Please try again.");
	}
	if ( ! valid_username($username) )
	{
		display_error("Usernames must be alphanumeric characters only<br>");
	}
	$base = database_open();
	$stmt = $dbh->prepare(
		"SELECT id, username, admin_contact FROM users WHERE username=':user' AND verified=1 LIMIT 1";
	);
	$stmt->bindParam(':user', $user);
	$stmt->execute();
	$user_entry = $stmt->fetch();

	if ( $user_entry['username'] == $user )
	{
		$stmt = $dbh->prepare(
			"SELECT * FROM contacts WHERE id=':contact' AND verified=1 LIMIT 1";
		);
		$stmt->bindParam(':user', $user);
		$stmt->execute();
		$results = $stmt->fetch();
		/* TODO */
		$newPass=generatePassword(8);
		$real_password=hash('sha256',$newPass);
		$query = "UPDATE users set password='".$real_password."' WHERE username='".$user."' AND email='".$email."'";
		$results = database_query_now($base, $query);
		if (!empty($results)) {
			$to      = $email;
			$subject = $server.' new password';
			$message = 'You have requested a new password'."\r\n".'Your new password is: '.$newPass."\r\n\r\nThank you for use ".$server." services\r\n\r\n";
			$headers = 'From: Opennic <no-reply@'.$server. ">\r\n";
			mail($to, $subject, $message, $headers);
			header("location: index.php");
		}
	} 
	else 
	{
		display_error("Incorrect username or email or account not verified. Please try again.");
	}
}

function register($username, $email, $password)
{
	global $TLD, $tld_db;

	show_header();
	
	/* prepare clean data */
	$username=htmlspecialchars(stripslashes($username));
	$password=htmlspecialchars(stripslashes($password));
	#$name=htmlspecialchars(stripslashes($name));
	$email=htmlspecialchars(stripslashes($email));
	
	/* perform validation checks */
	if(filter_var($email, FILTER_VALIDATE_EMAIL) == FALSE)
	{
		echo "Not a valid email address";
		die();
	}
	if(!validateUsername($username))
	{
		echo "Usernames must be alphanumeric characters only<br>";
		die();
	}
	$username=clean_up_input($username); /* just in case */
	$username=strtolower($username);
	if(username_taken($username))
	{
		echo "That username is already taken. Please try using another, different username.";
		die();
	}
	
	/* let the user know */
	echo "Creating new account for $username<BR>\n";

	/* generate user verification key */
	/* TODO: store in DB */
	$userkeyfile="tmp/".$username.".ukf";	// some environments does not allow execuion outside its boundaries even /tmp
	$fh=fopen($userkeyfile, 'w') or die("Can't create user key verification file. Please report this to the admin.");
	$userkey=substr(md5(uniqid(mt_rand(), true)), 0, 16);

	fwrite($fh, $userkey);
	fclose($fh);

	/* prepare account */
	$base=database_open_now($tld_db, 0666);
	$real_password=hash('sha256',$password);
	date_default_timezone_set('Etc/UTC');
	$registered=strftime('%Y-%m-%d');
	#$query = "INSERT INTO users (username, password, email, registered, verified)
	#		VALUES('".$username."', '".$real_password."', '".$email."', '".$registered."', 0)";
	#$results = database_query_now($base, $query);
	$results = database_pdo_query("INSERT INTO users (username, password, email, registered, verified) VALUES('".$username."', '".$real_password."', '".$email."', '".$registered."', 0)");
	
	/* construct email */
	$msg_FROM = "FROM: hostmaster@opennic.".$TLD;
	$msg_subject = "OpenNIC ".$TLD." User Registration.";
	$msg = "Welcome ".$username." to OpenNIC.".$TLD."!\n\n";
	$msg .= "Your details are:\n";
	$msg .= "Username: ".$username."\n";
	$msg .= "Password: (The one you specified during sign up. Remember, this is encrypted and cannot be retrieved.)\n\n";
	$msg .= "Always ensure your contact details are up to date.\n\n";
	$msg .= "To confirm this email and activate your account, please visit https://www.opennic.".$TLD."/register/confirm.php?username=".$username."&userkey=".$userkey."\nYou have 24 hours to activate your account, otherwise it will be deleted.\n\n";
	$msg .= "Thank you for your patronage.\nOpenNIC".$TLD." Administration.\n";
	mail($email, $msg_subject, $msg, $msg_FROM);
	echo "If registration was successful, you should receive an email shortly. Please contact hostmaster@opennic.".$TLD." if you do not receive one within 24 hours. Please ensure that email address is on your email whitelist.";
	// echo "DEBUG: [".$msg."]";
}

/* Main entry point */
if(isset($_REQUEST['action']))
{
	$action=$_REQUEST['action'];
	switch($action)
	{
		case "Send_Password":
			if(isset($_POST['email']) && isset($_POST['username'])) {
				$user=$_POST['username'];
				$email=$_POST['email'];
				send_password($user, $email);
			} else {
				display_error("Data error. Please retry.");
			}
		case "login":
			if(isset($_POST['username']) && isset($_POST['password']))
			{
				$username=$_POST['username'];
				$password=$_POST['password'];
			} else {
				display_error("Data error. Please retry.");
			}
			login($username, $password);
			break;
		case "register":
			if ( isset($_POST['username']) && isset($_POST['password1']) && isset($_POST['password2']) && isset($_POST['email']) )
			{
				$username=$_POST['username'];
				$password1=$_POST['password1'];
				$password2=$_POST['password2'];
				$email=$_POST['email'];
			} else {
				display_error("Data error. Please retry.");
			}
			if ($password1 != $password2)
			{
				display_error("Sorry, passwords do not match. Please try again.");
			}
			if( (strlen($username)<2) && (strlen($password1)<5) && (strlen($email)<5) )
			{
				display_error("Invalid data. Please try again.");
			}
			register($username, $email, $password1);
			break;
		default:
			echo "Invalid sub-command.";
			die();
	}
}

show_header();
?>

<form action="login.php" method="post">
<table width="400" align="center">
<tr><td colspan="2" align="center"><h2><?php echo $TLD; ?> User Login</h2></td></tr>
<tr><td valign="top">Username:</td><td><input type="text" name="username"></td></tr>
<tr><td valign="top">Password:</td><td><input type="password" name="password"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Login"></td></tr>
<tr><td colspan="2" align="center"><br><a href="login.php?action=frm_send_password">Send new password</a></td></tr>
</table>
<input type="hidden" name="action" value="login">
</form>

<form action="user.php" method="post">
<table width="400" align="center">
<tr><td colspan="2" align="center"><h2><?php echo $TLD; ?> Send Password</h2></td></tr>
<tr><td valign="top">Username:</td><td><input type="text" name="username"></td></tr>
<tr><td valign="top">Email:</td><td><input type="text" name="email"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="action" value="Send_Password"></td></tr>
</table>
</form>

<form action="user.php" method="post">
<table width="400" align="center">
<tr><td colspan="2" align="center"><h2><?php echo $TLD; ?> User Registration</h2><font size="-1">All entries must be at least 5 characters long.</font></td></tr>
<tr><td>Email</td><td><input type="text" name="email1" maxlength="50"><sup>*</sup></td></tr>
<tr><td>Email confirmation</td><td><input type="text" name="email2" maxlength="50"><sup>*</sup></td></tr>
<tr><td>Username</td><td><input type="text" name="username" maxlength="20"></td></tr>
<tr><td valign="top">Password</td><td><input type="password" name="password1"><sup>**</sup></td></tr>
<tr><td valign="top">Password Confirm</td><td><input type="password" name="password2"></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Register"></td></tr>
<tr><td colspan="2"><font size="-1">
<sup>*</sup> Choose a reliable email as this can only be changed later by contacting support.<br>
<sup>**</sup> This is encrypted and cannot be retrieved.<br>
</font></td></tr>
</table>
<input type="hidden" name="action" value="register">
</form>

<?php
show_footer();
?>
