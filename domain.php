<?php
include("functions.inc");
if ( ! check_login() )
{
	header("Location: login.php");
	display_error("Not logged in!");
}

function register_domain($domain)
{
	show_header();
	global $TLD, $tld_svr, $user, $userkey, $domain_table;

	$userid=$_SESSION['userid'];
	$username=$_SESSION['username'];
	if(strlen($userid)<1)
	{
		display_error("Error validating user session.\n");
	}
	if(strlen($username)<3)
	{
		display_error("Error validating user name.\n");
	}
	if( (strlen($domain)<2) && (strlen($domain)>50) && (strlen($ns1)<5) && (strlen($ns2)<5) )
	{
		display_error("<font color='#ff0000'><b>Error</b></font> Domain details must adhere to standard lengths.\n"); 
	}
	if(domain_taken($domain))
	{
		display_error (
			"Sorry, this domain has already been submitted for processing. If you believe this to be ".
			"in error or you would like to dispute the previous registration, please contact us using ".
			"the domain <a href=\"abuse.php\">abuse</a> page</a>. Thank you."
		);
	}
	/* TODO: check against invalid domains */

	$nowp1 = (date("Y") + 1).date("-m-d");
	$now = date("Y-m-d");
	
	$ret_data = database_pdo_query ("
		INSERT INTO $domain_table (domain,name,email,ns1,ns2,ns1_ip,ns2_ip,registered,expires,updated,userid) 
		VALUES (
			'$domain', '$username', 'contact@example.chan', 'ns1.example.chan', 'ns2.example.chan', 
			'192.168.1.1', '192.168.1.2', '$now', '$nowp1', '$now', '$userid'
		)
	");

	if ($ret_data == 1)
	{
		echo "<font color=\"#008000\"><b>Complete</b></font><BR>Congratulations! Your new domain has been registered.  Please configure it now:\n<a href=\"domain.php?action=modify&domain=".$domain."\">Configure</a>";
	} 
	else 
	{
		echo "<font color=\"#800000\"><b>Error</b></font><BR>An error occured during registration. Please try again.";
	}
}

function delete_domain($domain)
{
	global $domain_table;
	$userid=$_SESSION['userid'];
	$results = database_pdo_query("SELECT * FROM $domain_table WHERE domain='$domain' LIMIT 1");
	print_r($results);
	$real_userid = $results['userid'];
	echo "<b>userid = $real_userid</b>";
	if($userid != $real_userid)
	{
		echo "<font color=\"#ff0000\"><b>Error: You do not have permission to modify this domain.</b></font>";
		die();
	}

	show_header();
	$ret_data = database_pdo_query("DELETE FROM $domain_table WHERE `domain`='$domain' AND `userid`='$userid'");
	if ($ret_data)
	{
    	echo "<center><b>Domain deleted</b>. Changes may take up to 24-72 hours to take effect.</center>";
	} else {
    	echo "<center><b>Error, domain not deleted</b>. Possibly an administrative glitch.</center>";
	}
}

function frm_delete_domain($domain)
{
	global $TLD;
	
	show_header();
	?>
	<center>
	<h2>Cancel <?php echo $domain.'.'.$TLD; ?> Registration</h2>
	<form action="domain.php" method="post">
	This means you will no longer be able to manage it and that someone else may register it instead.<BR>
	Are you sure you wish to delete <b><?php echo $domain.".".$TLD;?>?</b><BR>
	<input type="checkbox" name="delete">Yes <input type="submit" value="Confirm">
	<input type="hidden" name="domain" value="<?php echo $domain; ?>">
	<input type="hidden" name="action" value="confirm_delete_domain">
	</form>
	</center>
	<?php
}


function update_domain($domain, $ns1, $ns2, $ns1_ip, $ns2_ip, $mx1, $dskey, $txt, $isns)
{
	global $TLD, $tld_db, $domain_table;

	show_header();
	if((!validateIPAddress($ns1_ip)) || (!validateIPAddress($ns2_ip)))
	{
		echo "IP Addresses must be a valid IPv4 or IPv6 address\nIPv6 addresses must be in long form: do not use :: to omit 0'd hextets";
		die();
	}
	if((!validateFQDN($ns1, $domain)) || (!validateFQDN($ns2, $domain)) || (!validateFQDN($mx1, "")))
	{
		echo "All hostnames must be in FQDN form, including the .$TLD<br>The server hostname(s) must match the domain.";
		die();
	}
	if(!validateDSKEY($dskey))
	{
		echo "Your DSkey is invalid<br>";
		die();
	}
	if(!validateTXT($txt))
	{
		echo "Your site description contains invalid characters<br>";
		die();
	}
	if(($isns != 0 ) && ($isns != 1))
	{
		echo "something has gone badly wrong.  Please report to the admin that 'isns' is not a 1 or 0.";
		die();
	}
	$updated=strftime('%Y-%m-%d');
	$userid=$_SESSION['userid'];
	$arr = database_pdo_query("SELECT * FROM $domain_table WHERE userid='$userid' AND domain='$domain' LIMIT 1");
	$real_userid=$arr['userid'];
	if($userid != $real_userid)
	{
		echo "<font color=\"#ff0000\"><b>Error: You do not have permission to modify this domain.</b></font>";
		die();
	}
	echo "Updating ".$domain.'.'.$TLD."...";
	$ret_data = database_pdo_query("UPDATE domains SET ns1='$ns1', ns2='$ns2', updated='$updated', ns1_ip='$ns1_ip', ns2_ip='$ns2_ip', mx1='$mx1', dskey='$dskey', txt='$txt',  isns='$isns' WHERE domain='$domain' AND userid='$userid'");
	echo "Done. The changes should take effect within the hour. Please be aware some networks may not see the changes for up to 72 hours.<BR>";
}

// Main entry point
$userid=$_SESSION['userid'];
$arr = database_pdo_query("SELECT * FROM $domain_table WHERE userid='".$userid."' AND domain='".$domain."' LIMIT 1");
$real_userid=$arr['userid'];
if($userid != $real_userid)
{
	display_error("<font color=\"#ff0000\"><b>Error: You do not have permission to modify this domain.</b></font>");
}
show_header();

if(isset($_REQUEST['action']))
{
	$action=$_REQUEST['action'];
	switch($action)
	{
		case "frm_register_domain":
			if(!isset($_POST['domain']))
			{
				echo "Error. No domain specified."; die();
			} else {
				$domain=$_POST['domain'];
				frm_register_domain($domain);
			}
			break;
		case "register_domain":
			if(!isset($_POST['domain']))
			{
				echo "Error. No domain specified."; die();
			}
			$domain=$_POST['domain'];
			if(!isset($_POST['ns1_ip']))
			{
				$ns1_ip="NULL";
			}
			if(!isset($_POST['ns2_ip']))
			{
				$ns2_ip="NULL";
			}
			$ns2_ip="NULL";
			#register_domain($domain, $ns1, $ns2, $ns1_ip, $ns2_ip);
			register_domain($domain);
			break;
		case "confirm_delete_domain":
			if(!isset($_POST['domain']))
			{
				echo "Error. No domain specified."; die();
			}
			$domain=$_POST['domain'];
			if(!isset($_POST['delete']))
			{
				echo "Error. Deletion validation failed."; die();
			}
			delete_domain($domain);
			break;
		case "delete_domain":
			$domain=$_POST['domain'];
			frm_delete_domain($domain);
			break;
		case "modify":
			if(!isset($_SESSION['userid']))
			{
				die("Domain modification not allowed. You must be logged in.");
			}
			if(!isset($_REQUEST['domain']))
			{
				die("Invalid domain request");
			}
			$domain=$_REQUEST['domain'];
			frm_view_domain($domain);
			break;
		case "update":
			if(!isset($_SESSION['userid']))
			{
				die("Domain modification not allowed.");
			}
			if(!isset($_POST['domain']))
			{
				die("Invalid domain request");
			}
			$domain=$_POST['domain'];
			
			/* standard nameservers */
			if(!isset($_POST['ns1']))
			{
				die("Server 1 is required.");
			}
			$ns1=$_POST['ns1'];
			if($ns1=='')
			{
				die("Nameserver 1 is required.");
			}
			if(isset($_POST['ns2']))
			{
				$ns2=$_POST['ns2'];
			}
			/* deal with custom nameservers */
			if(isset($_POST['ns1_ip']))
			{
				$ns1_ip=$_POST['ns1_ip'];
			} else {
				$ns1_ip="NULL";
			}
			if(isset($_POST['ns2_ip']))
			{
				$ns2_ip=$_POST['ns2_ip'];
			} else {
				$ns2_ip="NULL";
			}
			if(isset($_POST['mx1']))
			{
				$mx1=$_POST['mx1'];
			} else {
				$mx1="NULL";
			}
			if(isset($_POST['dskey']))
			{
				$dskey=$_POST['dskey'];
			} else {
				$dskey="NULL";
			}
			if(isset($_POST['txt']))
			{
				$txt=$_POST['txt'];
			} else {
				$txt="NULL";
			}
			$isns=$_POST['isns'];

			update_domain($domain, $ns1, $ns2, $ns1_ip, $ns2_ip, $mx1, $dskey, $txt, $isns);
			break;
		default:
			echo "Invalid command.";
			die();
	}
} 
else 
{
	echo "Unspecified error.";
}

	echo "<center><h2>".$domain.'.'.$TLD." Modification</h2>\n";
	echo "Registered: ".$arr['registered']."<BR><BR>\n";
?>
<form action="domain.php" method="post">
	<table width="600" border=0 cellspacing=2 cellpadding=0>
		<tr><td colspan="3"><b>Name Settings</b></td></tr>
		<tr><td>Type</td><td><input type=radio name=isns value=0 <?php if(! $arr['type']) echo "checked"; ?>>A<br><input type=radio name=isns value=1 <?php if($arr['type']) echo "checked"; ?>>NS</td></tr>
		<tr><td>Server 1</td><td><input type="text" name="ns1" value="<?php echo $arr['ns1']; ?>"></td></tr>
		<tr><td>Server 2</td><td><input type="text" name="ns2" value="<?php echo $arr['ns2']; ?>"></td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td colspan="3">Address Settings</td></tr>
		<tr><td>Server 1</td><td><input type="text" name="ns1_ip" value="<?php echo $arr['ns1_ip']; ?>"><font size="-1">IPv4 only</font></td></tr>
		<tr><td>Server 2</td><td><input type="text" name="ns2_ip" value="<?php echo $arr['ns2_ip']; ?>"><font size="-1">IPv4 only</font></td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td>Mail Server</td><td colspan="2"><input type="text" name="mx1" value="<?php echo $arr['mx1']; ?>"></td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td>Description</td><td colspan="2"><input type="text" name="txt" value="<?php echo $arr['txt']; ?>" size="100" maxlength="255"></td></tr>
		<tr><td colspan="3">&nbsp;</td></tr>
		<tr><td>DSKEY</td><td colspan="2"><input type="text" name="dskey" value="<?php echo $arr['dskey']; ?>" size="100" maxlength="255"></td></tr>
		<tr><td colspan="2">&nbsp;</td></tr>
		<tr><td colspan="2" align="center">
			<input type="hidden" name="domain" value="<?php echo $domain; ?>">
			<input type="hidden" name="action" value="update">
			<input type="submit" name="submit" value="Update Domain">
		</td></tr>
	</table>
</form>
<p>&nbsp;</p>

<font color="#ff0000"><b>Careful!</b></font>
<form action="domain.php" method="post">
<input type="hidden" name="action" value="delete_domain">
<input type="hidden" name="domain" value="<?php echo $domain; ?>">
<input type="submit" value="Delete Domain">
</form>
<?php
}
show_footer();
?>

