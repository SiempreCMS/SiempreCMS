<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
// Allows user to login.
require_once('../../admin-includes/admin.base.inc.php');
require_once('../../admin-includes/admin.security.inc.php');
// require_once('../../admin-includes/admin.vercheck.inc.php');

if (isset($_GET['perform'])) {
	$upgrade = true;
	$vercheck = new vercheck();
	$upgradeResult = $vercheck->performUpgrade();
}
else {
	$upgrade = false;
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Siempre CMS - Upgrade</title>
	<meta http-equiv="Content-Type" content="application/xhtml+xml; charset=utf-8" />
	<meta name="description" content="Siempre CMS" />
	<meta name="keywords" content="Siempre CMS" />
	<meta name="robots" content="noindex, nofollow" />
	<link rel="icon" type="image/png" href="images/favicon.ico" />
	<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
	<link rel="stylesheet" href="css/jquery-ui.min.css" />
	<link rel="stylesheet" href="css/jquery-ui.theme.min.css" />
	<link rel="stylesheet" href="css/jstree/style.min.css" />
	<link rel="stylesheet" href="css/main.css" />

</head>
<body>
	
	<div class="login">
		<div class="login-banner" id="title-panel-header">
			<div class="login-logo">
				<img src="images/logo.png"/>
			</div>
			<h1>Upgrade to Siempre CMS Admin Required</h1>
			<h4>Please note: by attempting to access this system your IP will be logged: <?php 	echo getenv("REMOTE_ADDR"); ?></h4>
		</div>
		<div>
			
		</div>
		<?php
		if($upgrade)
		{
			echo '<div class="form-row-centered clearfix">';
			if($upgradeResult)
			{
				echo '<h3>Upgrade successful</h3>';
				echo '<h4><a href="dashboard.php">Click here to return home</a></h4>';
			}
			else
			{
				echo '<h3>Upgrade FAILED </h3>';
				echo '<h4>Something went wrong with the upgrade</h4>';
				echo '<p>' . $vercheck->message . '</p>';
				echo '<p> . here</p>';
			}
			echo '</div>';
		}
		else {
		?>
		<div>
			<form name="login-form" id="login-form" class="login-form" method="post" action="upgrade.php?perform=true">
				<fieldset>
					<legend>Upgrade</legend>
					<h3>WARNING - ensure you've taken a backup of your files and Database BEFORE running the upgrade</h3>
					
					<div class="form-row-centered clearfix">
						<input type="submit" id="upgrade-submit" class="button green" value="Upgrade" />
					</div>
					
				</fieldset>
			</form>
		</div>
		<?php
		}
		?>
	</div>
</div>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script> 
	<script type="text/javascript" src="js/jquery.blockUI.js"></script>
	<script type="text/javascript" src="js/login.js"></script>
</body>
</html>