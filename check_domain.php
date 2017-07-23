<?php
include("regnum.cfg");
include("functions.inc");

function check_domain($domain)
{
	global $TLD, $specialNamesRFC6761, $specialNamesIANA;
	/* sanity check the domain */
	$name=htmlspecialchars(stripslashes($domain));
	$name=preg_replace("/[^a-zA-Z0-9\-]/","", $name); /* replace characters we do not want */
	$name=preg_replace('/^[\-]+/','',$name); /* remove starting hyphens */
	$name=preg_replace('/[\-]+$/','',$name); /* remove ending hyphens */
	$name=str_replace(" ", "", $name); /* remove spaces */
	$name=str_replace("--", "-", $name); /* remove double hyphens */
	$name=strtolower($name); /* all lower case to remove confusion */
	if( (strlen($name)<=0) || (strlen($name)>63))	/* Domain name labels limit=63 and uri limit=253 octects see RFC1035 */
	{
		echo "Sorry, domain names must contain at least 1 character and be no longer than 253 characters.";	// >2 ISO 3166 reserved for country codes
		echo "Please go back and try again.";
		return;
	}
	if(preg_match('/^[a-zA-Z]{2}$/', $name)) 
	{
		echo "ISO 3166 country codes are reserved at this time.";
		echo "Please go back and try again.";
		return;
	}
	if(preg_match($specialNamesRFC6761, $name)) 
	{
		echo "Sorry, domain names must not contain special DNS names as specified in RFC6761.";
		echo "Please go back and try again.";
		return;
	}
	if(preg_match($specialNamesIANA, $name)) 
	{
		echo "Sorry, we do not want to mess with ICANN or IANA special DNS names.<br>\n"; 
		echo "See: http://www.icann.org/en/about/agreements/registries/unsponsored/registry-agmt-appk-26apr01-en.htm<br>\n";
		echo "Please go back and try again.\n";
		return;
	}
	if(strlen($name)>0)
	{
		echo "Checking <b>".$name.".".$TLD."</b> for you...";
		if(domain_taken($name))
		{
			echo "<font color=\"#ff0000\"><b>Taken</b></font><BR><BR>Sorry, that name is already taken.";
		} else {
			echo "<font color=\"#008000\"><b>Available!</b></font>\n";
			echo "<BR><BR>Congratulations! <b>".$name.".".$TLD."</b> is available.\n";
			echo "Would you like to register it now?\n";
			echo '<form action="domain.php" method="post">'."\n";
			echo '<input type="hidden" name="domain" value="'.$name.'">'."\n";
			echo '<input type="hidden" name="action" value="register_domain">'."\n";
			echo '<input type="submit" name="submit" value="Yes!">'."\n".'</form>'."\n";
		}
		echo "You can use the form below to search for another domain if you like.";
		return;
	}
}

show_header();

if(isset($_REQUEST['action']))
{
	$action=$_REQUEST['action'];
	if ( $action == "check_domain" )
	{
		if(!isset($_POST['domain']))
		{
			$domain=$_POST['domain'];
			check_domain($domain);
		}
		else
		{
			echo "Error. No domain specified.";
		}
	}
	else
	{
		echo "Invalid command.";
	}
}

echo '<p>'."\n";
echo '<form action="check_domain.php" method="post">'."\n";
echo 'Domain name <input type="text" name="domain">'.$TLD.'&nbsp;<input type="submit" name="check" value="Check">'."\n";
echo '<input type="hidden" name="action" value="check_domain">'."\n";
echo '</form>'."\n";
echo '</p>'."\n";

show_footer();
?>
