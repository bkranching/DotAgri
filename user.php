<?php
/*
   MUD4TLD - Martin's User and Domain system for Top Level Domains.
   Written 2012-2014 By Martin COLEMAN.
   This software is hereby dedicated to the public domain.
   Made for the OpenNIC Project.
   http://www.mchomenet.info/mud4tld.html
*/
include("functions.inc");
sec_session_start();
if ( ! check_login() )
{
	header("Location: login.php");
	display_error("Not logged in!");
}


function dashboard()
{
	global $TLD, $domain_expires, $tld_db, $user_table;
	show_header();
	
	$username=$_SESSION['username'];
	$userid=$_SESSION['userid'];

	// echo "<p align=\"right\"><a href=\"user.php?action=logout\">Logout</a></p>\n";
	echo "<center><H2>Welcome to ".$username."'s Dashboard for .".$TLD."</H2>\n";
	echo "<b>My .".$TLD." domains</b><BR><BR>";
	$base=database_open_now($tld_db, 0666);
	$query="SELECT domain, registered, expires FROM domains WHERE userid=".$userid."";
	$results = database_query_now($base, $query);
	if(dbNumRows($results))
	{
		echo "<table width=\"400\" align=\"center\" border=0 cellspacing=1 cellpadding=0>\n";
		echo "<tr><td>Domain Name</td><td>Created</td>";
		if($domain_expires==1)
		{
			echo "<td>Expires</td>";
		}
		echo "</tr>\n";

		while($arr=database_fetch_array_now($results))
		{
			echo "<tr><td><a href=\"domain.php?action=modify&domain=".$arr['domain']."\">".$arr['domain'].'.'.$TLD."</a></td><td>".$arr['registered']."</td>";
			if($domain_expires==1)
			{
				echo "<td>".$arr['expires']."</td>";
			}
			echo "</tr>\n";
		}
		echo "</table>\n";
	} else {
		echo "You do not have any domains registered.\n";
	}
	echo "You can register a new ".$TLD." <a href=\"domain.php?action=frm_check_domain\">here</a>.";

	$get_user_details="SELECT email, pgpkey FROM users WHERE userid='".$userid."' AND username='".$username."' LIMIT 1";
	$base=database_open_now($tld_db, 0666);
	$get_user_details_results = database_query_now($base, $get_user_details);
	$get_user_details_arr=database_fetch_array_now($get_user_details_results);
	#$name=$get_user_details_arr['name'];
	#$get_user_details_arr=databse_pdo_query("SELECT email, pgpkey FROM $user_table WHERE userid='$userid' AND username='$username' LIMIT 1";
	$email=$get_user_details_arr['email'];
	#$country=$get_user_details_arr['country'];
	$pgpkey=$get_user_details_arr['pgpkey'];
?>
<BR><BR>
<form action="user.php" method="post">
<table width="450" align="center">
<tr><td colspan="2" align="center"><b>.<?php echo $TLD; ?> User Details</b></td></tr>
<tr><td>Email</td><td><?php echo $email; ?><sup>*</sup></td></tr>
<tr><td>Current Password</td><td><input type="password" name="password"></td></tr>
<tr><td valign="top">Password</td><td><input type="password" name="password1"><BR><font size="-1">(Must be at least 5 characters long)</font></td></tr>
<tr><td>Password Confirm</td><td><input type="password" name="password2"></td></tr>
<tr><td>PGP Key*</td><td><textarea rows="40" cols="85" name="pgpkey" wrap="physical"><?php echo $pgpkey; ?></textarea></td></tr>
<tr><td colspan="2" align="center"><input type="submit" name="submit" value="Update"></td></tr>
<input type="hidden" name="action" value="update_account">
<tr><td colspan="2">
<font size="-1">
<sup>*</sup>Please contact support to change this.<BR>
<sup>**</sup>This is optional and for our statistics only.
</font></td></tr>
</table>
</form>
<?php
	echo "</center>";
}

function update_account($password, $pgpkey)
{
	global $user_table;
	show_header();
	$changed=0;
	if(!isset($_SESSION['userid']))
	{
		echo "No valid account."; die();
	}
	if(!validatePGP($pgpkey))
	{
		echo "Invalid PGP key<br>";
		die();
	}
	$userid=$_SESSION['userid'];
	if ( strlen($password) > 0 )
	{
		$password=htmlspecialchars(stripslashes($password));
		$real_password=hash('sha256',$password);
		$pass_ret = database_pdo_query("UPDATE $user_table SET password='$real_password' WHERE userid='$userid'");
		$changed=1;
	}
	if ( strlen($pgpkey) > 0 )
	{
		if(!validatePGP($pgpkey))
		{
			echo "Invalid PGP key<br>";
			die();
		}
		$pgp_ret = database_pdo_query("UPDATE $user_table SET pgpkey='$pgpkey' WHERE userid='$userid'");
		$changed = 1;
	}
	if ( $changed == 1 )
	{
		echo "Details updated.";
	} else {
		echo "Nothing was changed.";
	}
}

if(!isset($_REQUEST['action']))
{
	echo "Invalid command.";
	die();
} else {
	$action=$_REQUEST['action'];
	switch($action)
	{
		case "update_account":
			if(!isset($_POST['password']))
			{
				echo "Current password not specified.";
				die();
			}
			$password=$_POST['password'];
			$password=str_replace(" ", "", $password);
			if(strlen($password)<6)
			{
				echo "Password should be at least 6 characters long.\n"; die();
			}
			if(!isset($_POST['pgpkey']))
			{
				echo "Data error. Please retry.  PGPKEY not set.";
				die();
			} else {
				$pgpkey=$_POST['pgpkey'];
			}
			if(isset($_POST['password1']))
			{
				$password1=$_POST['password1'];
				$password2=$_POST['password2'];
				if($password1 != $password2)
				{
					echo "Sorry, passwords do not match. Please try again.";
					die();
				}
				$password=$password1;
			}
			if(strlen($password1)>0)
			{
				if(strlen($password1)<6)
				{
					echo "Remember, your new password needs to be at least 6 characters long.";
					die();
				}
				$password=$password1;
			}
			update_account($password, $pgpkey);
			break;
		case "view_account":
			dashboard();
			break;
		case "logout":
			session_destroy();
			header("location: index.php");
			break;
		default:
			echo "Invalid sub-command.";
			die();
	}
}
show_footer();
?>
