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
	if(isset($_REQUEST['userkey']))
	{
		$userkey=$_REQUEST['userkey'];
	} 
	else 
	{
		display_error("Userkey required.");
	}
	$clean_username=clean_up_input($username);
	if(username_taken($clean_username)==0)
	{
		display_error("Sorry, that username does not exist.");
	}

	$myFile = "tmp/".$clean_username.".ukf";
	$fh = fopen($myFile, 'r') or die("Can't open user key verification.");
	$theData=fread($fh,filesize($myFile));
	fclose($fh);
	if($theData != $userkey)
	{
		display_error("Invalid user key.");
	}
	unlink($myFile);

	$base=database_open_now($tld_db, 0666);
	$query = "UPDATE users SET verified=1 WHERE username='".$clean_username."'";
	database_query_now($base, $query);

	show_header();
	echo "Your account for ".$clean_username." is now confirmed. You may now login using the link above to start registering domains.";
	show_footer();
} 
else 
{
	display_error("Data error.");
};
?>
