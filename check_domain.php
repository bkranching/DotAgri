<?php
include("regnum.cfg");
include("functions.inc");
sec_session_start();

function check_domain($domain)
{
	global $TLD, $specialNamesRFC6761, $specialNamesIANA;
	$name=strtolower($name); /* all lower case to remove confusion */
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
