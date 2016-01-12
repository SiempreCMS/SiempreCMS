<?php
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
require_once('../../admin-includes/admin.base.inc.php');
require_once('../../admin-includes/admin.security.inc.php');
require_once('../../admin-includes/admin.vercheck.inc.php');

$justLoggedIn = false;
// Show the help dialog if they've just logged into the demo system for the first time
if (isset($_SESSION['justLoggedIn']) && $_SESSION['justLoggedIn'] == true)
{
	$justLoggedIn = true;
	$_SESSION['justLoggedIn'] = false;
}
?>
<!DOCTYPE html>
<html>
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
		<title>Siempre CMS - Dashboard</title>
		<link rel="icon" type="image/png" href="images/favicon.ico" />
		<meta name="viewport" content="width=device-width" />
		<link rel="stylesheet" href="css/jquery-ui.min.css" />
		<link rel="stylesheet" href="css/jquery-ui.theme.min.css" />
		<link rel="stylesheet" href="css/jstree/style.min.css" />
		<link rel="stylesheet" href="css/main.css?ver=1.3.3" />
		<script type="text/javascript">
		<?php 
			// for the initial login help dialog
			if ($justLoggedIn)
			{
				echo "var showHelpOnLoad = true; ";
			}
			else {
				echo "var showHelpOnLoad = false; ";
			}
		?>
		</script>
	</head>
	<body>
	
	<?php
		$menu = 'home';
		require_once('../../admin-includes/admin.menu.php');
		?>
		
		<!-- Start of Login Dialog -->  
		<div id="login-dialog" class="login-form" title="Please login" style="display: none;"> 
			<p>Your session has timed out - please re-enter your password to continue.</p> 
			<form id="login-form"  action="#">
				<div class="form-row clearfix">
					<label>User:</label>
					<input type="text" name="username" id="username" value="<?php echo $username; ?>" readonly="readonly"/>
				</div>
				<div class="form-row clearfix">
					<label>Password: </label>
				<input type="password" name="password" id="password" />
				</div>
				<div class="form-row clearfix">
					<span class="error" id="login-errors">&nbsp;</span>
				</div>
			</form>
		</div>  
		<!-- End of Login Dialog -->  
		
		<!-- Start of Booking Help Dialog -->  
		<div id="help-dialog" title="Siempre CMS Help - to close press the 'X' ->" style="display: none;" class="help"> 	
			<h2>Welcome to Siempre CMS Beta!</h2>
			<p>This is the beta test version...</p>
		</div>  
		<!-- End of Booking Help Dialog -->  
		
		<!-- Start of please wait div -->
		<div id="loading-dialog" title="Executing..." style="display: none;"> 
			<p><img src="images/ajax-loader.gif" alt="Please wait" /> Please Wait</p>
		</div> 
		<div id="menuspacer" class="menuspacer">
		</div>
		<div class="main">
			<?php 
				$host = gethostname();
				$src = "http://siemprecms.org/welcome?ver=" . FILEVERSION . "&host=" . $host . "&ip=" . gethostbyname($host);
			
				echo '<iframe src="' . $src . '"></iframe>';
			?>
		</div>
		<script src="js/jquery-1.11.2.min.js"></script>
		<script src="js/jquery-ui.min.js"></script> 
		<script type="text/javascript" src="js/jquery.blockUI.js"></script>
		<script src="js/jstree/jstree.min.js"></script>
		
 		<script type="text/javascript" src="js/jquery.ajaxq-sjm-0.0.7.js"></script> 
		<script type="text/javascript" src="js/tinymce/tinymce.min.js"></script>
		<script type="text/javascript" src="js/dashboard.js?v=133"></script>
	</body>
</html>