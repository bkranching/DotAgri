<?php
/*
   MUD4TLD - Martin's User and Domain system for Top Level Domains.
   Written 2012-2014 By Martin COLEMAN.
   This software is hereby dedicated to the public domain.
   Made for the OpenNIC Project.
   http://www.mchomenet.info/mud4tld.html
*/
include("regnum.cfg");
include("funtions.inc");

if(isset($_REQUEST['username']))
{
	$username=$_REQUEST['username'];
	isset($_REQUEST['email']) ? $email = $_REQUEST['email'] : display_error("email required.");
	isset($_REQUEST['userkey']) ? $userkey=$_REQUEST['userkey'] : display_error("Userkey required.");

	if ( ! valid_username($username)
	{
		display_error("Invalid username");
	}

	$dbh = database_open();
	$stmt = $dbh->prepare("SELECT * FROM users WHERE username = :user LIMIT 1");
	$stmt->bindParam(':user', $username);
	$stmt->execute();
	$user = $stmt->fetch(PDO::FETCH_ASSOC);

	if ( ! defined($user['id'] )
	{
		display_error("No such user: $username");
	}

	$stmt = $dbh->prepare("SELECT * FROM contacts WHERE user_id = :user LIMIT 1");
	$stmt->bindParam(':user', $user['id']);
	$stmt->execute();
	$contact_arr = $stmt->fetchALL();

	$contact = '';
	foreach ($contact_arr as $contact_e)
	{
		if ( $contact_e['email'] == $email )
		{
			$contact = $contact_e;
			break;
		}
	}
	if ( $contact == '' )
	{
		display_error("Error: no matching email address ($email) for user $username was found");
	}

	if ( $contact['verification_token'] == $userkey )
	{
		$stmt = $dbh->prepare("UPDATE contacts SET verified=1 WHERE id = :id");
		$stmt->bindParam(':id', $contact['id']);
		$stmt->execute();

		show_header();
		echo "Your email for ".$email." is now confirmed.";

		/* Verify user account if this is their admin contact */
		if ( $user['admin_contact'] == $contact['id'] )
		{
			$stmt = $dbh->prepare("UPDATE users SET verified=1 WHERE id = :id");
			$stmt->bindParam(':id', $user['id']);
			$stmt->execute();
			echo "Your account for $username is now verified.  ";
			echo "You may now login using the link above to start registering domains.";
		} 
		show_footer();
	}
	else
	{
		display_error("Invalid user key.");
	}
} 
else 
{
	display_error("Data error.");
};
?>
