<?php 
/*
 * This file is part of Siempre CMS
 *
 * (c) 2015 Steve Morgan http://siempresolutions.co.uk/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 
if (!isset($menu))
{
	$menu = '';
}
?>
<div id="menu-container" class="menu-container">
	<nav>
		<ul class="menu-icons">
			<li<?php if($menu=='home') echo ' class="active"'; ?>>
				<a href="dashboard.php" class="menu-button" title="Home">
					<img src="images/icons/home.png" alt="Home" />

				</a>	
			</li>
			<li<?php if($menu=='content') echo ' class="active"'; ?>>
				<a href="content.php" class="menu-button" title="Content section">
					<img src="images/icons/content.png" alt="Content" />
					<span>Content</span>
				</a>	
			</li>
			<li<?php if($menu=='templates') echo ' class="active"'; ?>>
				<a href="templates.php" class="menu-button">
					<img src="images/icons/templates.png" width="20" height="20" alt="Templates" title="Templates section"/>
					<span>Templates</span>
				</a>
			</li>
		<?php /*	<li<?php if($menu=='setting') echo ' class="active"'; ?>>
				<a href="#" id="menu-settings">
					<img src="images/icons/settings.png" width="20" height="20" alt="Settings" title="Settings - a forthcoming feature"/>
					<span>Settings</span>
				</a> </li>  */  ?>
			<li<?php if($menu=='user') echo ' class="active"'; ?>>
				<a href="user.php" class="menu-button">
					<img src="images/icons/users.png" width="20" height="20" alt="Users" title="Users section"/>
					<span>Users</span>
				</a>
			</li>
			<li>
				<a href="http://siempresolutions.co.uk/siemprecms/help" target="_blank" id="menu-help" title="Opens the Siempre CMS help website">
					<img src="images/icons/help.png" width="20" height="20" alt="Help"/>
					<span>Help</span>
				</a>
			</li>
			<li>
				<a href="logout.php" class="menu-button">
					<img src="images/icons/logout.png" width="20" height="20" alt="Log Out" title="Log out of Siempre CMS"/>
					<span>Log Out</span>
				</a>
			</li>
		</ul>
		
		<span class="mobile">Menu</span>
		<select> 
			<option value="" selected="selected">.. go to</option> 

			<option value="dashboard.php">Home</option> 
			<option value="content.php">Content</option> 
			<option value="templates.php">Templates</option> 
			<option value="user.php">Users</option> 
			<option value="http://siempresolutions.co.uk/siemprecms/help">Help</option> 
			<option value="user.php">Users</option> 
		</select> 
				
	</nav>
	
	
	
	<div class="menulogo">
		<a href="http://siempresolutions.co.uk">
			<img src="images/logo.png" alt="Siempre CMS Logo"/>
			<span>Siempre CMS v1.3.4</span>
		</a>
	</div>
</div>


