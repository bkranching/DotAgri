<?php
include("functions.inc");
sec_session_start();

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
