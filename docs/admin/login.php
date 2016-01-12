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

$user = '';
$password = '';
$jsDisabled = false;

// If a location is passed (e.g. the user has tried to deep link and hasn't logged in)
$loc = '';

if(isset($_POST['password'])) {
	$jsDisabled = true;
}

$safeRedirect = "";

if (isset($_GET['loc'])) {
	// Check page is in whitelist
	// Took all the white list stuff out - rely on the security in the pages themselves... 	
	$loc = urldecode($_GET['loc']);
	$redirectWhiteList = unserialize(REDIRECTWHITELIST);
	
	if(in_array($loc, $redirectWhiteList)) {
		$safeRedirect = $loc;
	}
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en-GB">
<head>
	<title>Siempre CMS - Login</title>
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
	<!-- Start of Login Response Dialog -->  
	<div id="login-response-dialog" title="Basic dialog" style="display: none; "> 
		<p>Login details are incorrect - please re-enter and try again.</p> 
	</div>  
	<!-- End of Login Response Dialog -->  

	<div class="login">
		<div class="login-banner" id="title-panel-header">
			<div class="login-logo">
				<img src="images/logo.png"/>
			</div>
			<h1>Welcome to <a href="http://siempresolutions.co.uk">Siempre CMS</a> Admin</h1>
			<div id="login-panel-header">
					<h4>Please note: by attempting to access this system your IP will be logged: <?php 	echo getenv("REMOTE_ADDR"); ?></h4>
			</div>
			<?php 
				if($jsDisabled) {
					echo "<h2 class=\"warning\">ERROR: Javascript MUST be enabled to use this system.</h2>";
				}
				if (DEMO === true) {
			?>			
			<div>
				<p>This is <a href="http://siempresolutions.co.uk">Siempre CMS</a> - a CMS build in PHP and MySQL</p>
				<p><b>For more details on Siempre CMS please visit <a href="http://siempresolutions.co.uk/siemprecms">the main site</a></b></p>
				<p><b><i>Please note use of this system is monitored</i></b></p>
				<p>To login into the back office CMS you can use the following details:</p>
				<p><b>User:</b> <i>demo</i></p>
				<p><b>Password:</b> <i>demo</i></p>
				<!-- <p>The demo site that the CMS back office powers can be found  <a href="http://siempresolutions.co.uk/siemprecms">Siempre CMS ALPHA</a></p> -->
				
				<p>If you have any difficulties, comments or questions please see the contact pages on <a href="http://siempresolutions.co.uk/siemprecms">the site</a></p>
				<?php 
					$user ='demo';
					$password = 'demo';
					}  
				?>
			</div>
		</div>
		<div>
			<form name="login-form" id="login-form" class="login-form" method="post" action="login.php">
				<fieldset>
					<legend>User Login</legend>
					<h3>Enter your details below and click "Login"</h3>
					<div class="form-row clearfix">
						<label title="Username">Username: </label>
						<input tabindex="1" accesskey="u" name="username" type="text" maxlength="100" id="username" value="<?php echo $user; ?>"/>
					</div>
					<div class="form-row clearfix">
						<label title="Password">Password: </label>
						<input tabindex="2" accesskey="p" name="password" type="password" maxlength="14" id="password" value="<?php echo $password; ?>"/>
					</div>
					<div class="form-row-centered clearfix">
						<input tabindex="3" accesskey="s" type="submit" id="login-submit" class="button green" value="Login" />
					</div>
					<input type="hidden" name="loc" id="loc" value="<?php echo $safeRedirect; ?>"/>
				</fieldset>
			</form>
		</div>
		<!--		<div class="descr" id="forgot" title="Login">If you've forgotten your login details click <a href="forgotlogin.php">here</a> to receive an email to reset your password.</div> -->
			<!-- Column 1 end -->
	</div>
</div>
	<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
	<script type="text/javascript" src="js/jquery-ui.min.js"></script> 
	<script type="text/javascript" src="js/jquery.blockUI.js"></script>
	<script type="text/javascript" src="js/login.js"></script>
</body>
</html>