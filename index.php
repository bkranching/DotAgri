<?php
/*
   MUD4TLD - Martin's User and Domain system for Top Level Domains.
   Written 2012-2014 By Martin COLEMAN.
   This software is hereby dedicated to the public domain.
   Made for the OpenNIC Project.
   http://www.mchomenet.info/mud4tld.html
*/
/* Sample index page. Do what you want with it. Public domain. */
include("regnum.cfg");
include("functions.inc");
show_header();
?>

<table width="600" align="center">
	<tr><td align="center">
		<p>dot CHAN is the TLD (Top Level Domain) custom made for shitposters.</p>
		<p>Registering a dot CHAN is completely free.</p>
		<p align="center">
			<form action="domain.php" method="post">
				Check domain 
				<input type="text" name="domain">.CHAN&nbsp;
				<input type="hidden" name="action" value="check_domain">
				<input type="submit" value="Check!">
			</form>
		</p>
		<p>&nbsp;</p>
		<p>Is someone abusing a dot CHAN? Report spam or illegal material coming from a dot CHAN via <a href="mailto:abuse@opennic.chan">email</a>.</p>
	</td></tr>
</table>

<?php
show_footer();
?>
