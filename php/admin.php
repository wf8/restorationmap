<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if( !$_SESSION['valid'] )
{
    echo "not logged in";
    die();
} 

// is user registered as admin
if ( !$_SESSION['admin'] )
{
	echo 'you are not authorized';
	die();
}

 //creates a 3 character sequence
function createSalt()
{
	$string = md5(uniqid(rand(), true));
	return substr($string, 0, 3);
}

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());	


// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());
	
$function = $_GET['function'];	
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Restoration Map: Admin</title>
	</head>
	<body>
	<font face="verdana, helvetica" size="1">
	Back to the <a href="../">Map</a><br>
	<br>
	<br>
	<font face="arial, helvetica" size="8">
	Restoration Map: Admin
	</font>
	<font face="verdana, helvetica" size="1">
	<br><br><br>
	<table style='table-layout:fixed;width:100%'>
	<tr>
		<td valign='top' align='left' style='width:300px'>
			<a href="admin.php?function=">System Status</a><br>
			<a href="admin.php?function=generate_report">Generate Reports</a><br>
			<a href="admin.php?function=database_structure">View Database Structure</a><br>
			<a href="admin.php?function=upload_bcn">Upload BCN Data</a><br>
			<br>
			<a href="admin.php?function=get_user_info">Get User Info</a><br>
			<a href="admin.php?function=register_new_user">Register New User</a><br>
			<a href="admin.php?function=reset_password">Reset User Password</a><br>
			<br>
			<a href="admin.php?function=register_new_site">Register New Site</a><br>
			<a href="admin.php?function=add_user_to_site">Add User to Site</a><br>
			<a href="admin.php?function=remove_user_from_site">Remove User from Site</a><br>
			
			
		</td>
		<td valign='top' align='left'>
			
			<?php
			if ($function == '') {
			?>	
				<font face="verdana, helvetica" size="1">
				System Status:<br>
				
				<?php
				$query = "SELECT id FROM users";
				$result = mysql_query($query);
				echo "Number of users: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM stewardship_site";
				$result = mysql_query($query);
				echo "Number of stewardship sites: " . mysql_num_rows($result) . '<br>';
				
				$query = "SELECT id FROM trails";
				$result = mysql_query($query);
				echo "Number of trail layers: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM landmark";
				$result = mysql_query($query);
				echo "Number of geographic feature/landmark layers: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM brush";
				$result = mysql_query($query);
				echo "Number of brush and tree removal layers: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM burns";
				$result = mysql_query($query);
				echo "Number of prescribed burn layers: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM seed";
				$result = mysql_query($query);
				echo "Number of seed collection and planting layers: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM weed";
				$result = mysql_query($query);
				echo "Number of weed control layers: " . mysql_num_rows($result) . '<br>';
				$query = "SELECT id FROM other";
				$result = mysql_query($query);
				echo "Number of planning/other layers: " . mysql_num_rows($result) . '<br>';
				?>
				
			<?php
			}
			?>
				
						<?php
			if ($function == 'database_structure') {
			?>	
				<img src="../images/database_structure.png">
			<?php
			}
			?>
			
			
			<?php
			if ($function == 'upload_bcn') {
			?>	
			
				<font face="verdana, helvetica" size="1">
				Upload BCN Data:<br>
				<br>
				This will update Restoration Map's BCN database for a single year's worth of point count 
				data by parsing an uploaded 
tab-separated values (tsv/csv) file. The uploaded file should be a BCN file edited to include 
only the single year (and only point count data) that 
needs updating; otherwise the file will be too large to upload. This should be done once a year.<br><br>
<b>This will take a few minutes after 
clicking 'upload', please wait!</b><br>
				<form action="bcn_import.php" method="post" enctype="multipart/form-data">
			    	Year: <input type="text" name="year" maxlength="4" /><br>
					BCN tsv/csv file: <input type="file" name="file" id="file"><br>
					<br>
			    	<input type="submit" name="submit" value="Upload" />
				</form><br>
			<?php
			}
			?>
				
			<?php
			if ($function == 'register_new_user') {
			?>	
				<font face="verdana, helvetica" size="1">
				Register New User:<br>
				<form action="admin.php?function=register_new_user" method="post">
			    	Email: <input type="text" name="email" maxlength="50" /><br>
			    	First Name: <input type="text" name="first_name" maxlength="50" /><br>
			    	Last Name: <input type="text" name="last_name" maxlength="50" /><br>
			    	Password: <input type="password" name="pass1" /><br>
			    	Password Again: <input type="password" name="pass2" /><br>
			    	<input type="hidden" name="form" value="register_new_user" />
			    	<input type="submit" value="Register" />
				</form><br>
				<?php
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if ($_POST['form'] == 'register_new_user')
					{
						//retrieve our data from POST
						$email = $_POST['email'];
						$first_name = $_POST['first_name'];
						$last_name = $_POST['last_name'];
						$pass1 = $_POST['pass1'];
						$pass2 = $_POST['pass2'];
						
						// check that the 2 passwords are the same
						if($pass1 != $pass2) 
						    echo "Passwords are not the same. User not added.";
						else
						{
							// check that email is right length
							if(strlen($email) > 50) {
							    echo "Email is too long. User not added.";
							    exit;
							} else
							{
								// get salt and hash
								$salt = createSalt();
								$hash = hash('sha256', $pass1);
								$hash = hash('sha256', $salt . $hash);
								
																		
								//sanitize user input
								$email = mysql_real_escape_string($email);
								$first_name = mysql_real_escape_string($first_name);
								$last_name = mysql_real_escape_string($last_name);
								
								$query = "SELECT * FROM users WHERE email = '$email'";
								$result = mysql_query($query);
								if(mysql_num_rows($result) > 0) 
					    			echo "There is already a user registered with that email address.";
					    		else
					    		{
						    		$query = "SELECT * FROM users WHERE first_name = '$first_name' AND last_name = '$last_name'";
									$result = mysql_query($query);
									if(mysql_num_rows($result) > 0) 
						    			echo "There is already a user registered with that name.";
						    		else
						    		{		    		
										$query = "INSERT INTO users (email, first_name, last_name, password, salt) VALUES ('$email', '$first_name', '$last_name', '$hash', '$salt')";
										$result = mysql_query($query, $connection);
										if (!$result) 
									  		echo 'Error: ' . mysql_error();
									  	else 
									  	{
											// echo $query . '<br>';	
											echo "User successfully added.";
											
									  	}
						    		}
					    		}
							}
						}
					}
				}
			}
			?>
	
			<?php
			if ($function == 'reset_password') {
			?>
				
				<font face="verdana, helvetica" size="1">
				Reset User Password:<br>
				<form action="admin.php?function=reset_password" method="post">
			    	Email: <input type="text" name="email" maxlength="50" /><br>
			    	Password: <input type="password" name="pass1" /><br>
			    	Password Again: <input type="password" name="pass2" /><br>
			    	<input type="hidden" name="form" value="reset_password" />
			    	<input type="submit" value="Reset" />
				</form><br>
				<?php
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if ($_POST['form'] == 'reset_password')
					{
						//retrieve our data from POST
						$email = $_POST['email'];
						$pass1 = $_POST['pass1'];
						$pass2 = $_POST['pass2'];
						
						// check that the 2 passwords are the same
						if($pass1 != $pass2) 
						    echo "Passwords are not the same. Password not reset.";
						else
						{
							// get salt and hash
							$salt = createSalt();
							$hash = hash('sha256', $pass1);
							$hash = hash('sha256', $salt . $hash);
								
							//sanitize email
							$email = mysql_real_escape_string($email);
							$query = "SELECT * FROM users WHERE email = '$email'";
							$result = mysql_query($query);
							if(mysql_num_rows($result) < 1) //no such user exists
					    		echo "User does not exist.";
							else
							{
								$query = "UPDATE users SET password = '$hash', salt = '$salt' WHERE email = '$email'";
								$result = mysql_query($query, $connection);
								if (!$result) 
							  		echo 'Error: ' . mysql_error();
							  	else
							  	{
									// echo $query . '<br>';
									echo "Password reset.";
							  	}
							}
						}
					}
				}
			}
			?>

			<?php
			if ($function == 'get_user_info') {
			?>

				<font face="verdana, helvetica" size="1">
				Get User Info:<br>
				<form action="admin.php?function=get_user_info" method="post">
					<?php	
					$query_users = "SELECT * FROM users";
					$result_users = mysql_query($query_users);
					if (!$result_users) 
						die('Invalid query: ' . mysql_error());
						
					echo "<select name='user_list'><option>Select User</option>";
					// Iterates through each site, gets name of site from database
					while ($row = @mysql_fetch_assoc($result_users)) 
					{
						$userId = $row['id'];
						$userEmail = $row['email'];
						echo '<option value="' . $userId . '">' . $userEmail . '</option>';
					}
					echo "</select>";
					?>	
				
			    	<input type="submit" value="Submit" />
			    	<input type="hidden" name="form" value="get_user_info" />
				</form><br>
			
				<?php	
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if ($_POST['form'] == 'get_user_info')
					{
						//retrieve our data from POST
						$user_id = $_POST['user_list'];
						
						$query_users = "SELECT * FROM users WHERE id = '$user_id'";
						$result_users = mysql_query($query_users);
						if (!$result_users) 
							die('Invalid query: ' . mysql_error());
						$the_user = @mysql_fetch_assoc($result_users);
						// display email, name, and admin info
						echo 'User email is registered as ' . $the_user['email'] . '<br>';
						echo 'User name is ' . $the_user['first_name'] . ' ' . $the_user['last_name'] . '<br>';
						if ( $the_user['admin'] )
							echo 'User does have admin permissions.<br>';
						else
							echo 'User does not have admin permissions.<br>';
						
						
						 // Selects all the sites for which this user is steward
						$query1 = "SELECT * FROM site_steward WHERE user_id='$user_id'";
						$result1 = mysql_query($query1);
						if ($result1) 
						// user is steward 
						{	
							// Iterates through each site, displays name of site from database
							while ($row = @mysql_fetch_assoc($result1)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo 'User is registered as site steward of ' . $row2['name'] . '.<br>';
							}
						}
						
						// Selects all the sites for which this user is assistant
						$query1 = "SELECT * FROM site_assistant WHERE user_id='$user_id'";
						$result1 = mysql_query($query1);
						if ($result1) 
						// user is steward 
						{	
							// Iterates through each site, displays name of site from database
							while ($row = @mysql_fetch_assoc($result1)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo 'User is registered as site assistant of ' . $row2['name'] . '.<br>';
							}
						}
						
					}
				}
			}
			?>	
	
			<?php
			if ($function == 'add_user_to_site') {
			?>
	
				<font face="verdana, helvetica" size="1">
				Add User to Site:<br>
				<form action="admin.php?function=add_user_to_site" method="post">
					<?php	
					$query_users = "SELECT * FROM users";
					$result_users = mysql_query($query_users);
					if (!$result_users) 
						die('Invalid query: ' . mysql_error());
						
					echo "<select name='user_list'><option value='0'>Select User</option>";
					// Iterates through each site, gets name of site from database
					while ($row = @mysql_fetch_assoc($result_users)) 
					{
						$userId = $row['id'];
						$userEmail = $row['email'];
						echo '<option value="' . $userId . '">' . $userEmail . '</option>';
					}
					echo "</select><br>";
					
					$query_sites = "SELECT * FROM stewardship_site";
					$result_sites = mysql_query($query_sites);
					if (!$result_sites) 
						die('Invalid query: ' . mysql_error());
					echo "<select name='site_list'><option value='0'>Select Site</option>";
					echo "<option value='0.5'>All</option>";
					// Iterates through each site, gets name of site from database
					while ($row = @mysql_fetch_assoc($result_sites)) 
					{
						$siteId = $row['id'];
						$siteName = $row['name'];
						echo '<option value="' . $siteId . '">' . $siteName . '</option>';
					}
					echo "</select>";
					?>	
					<br>
					<select name='user_level'>
						<option value='0'>Select User Permission Level</option>
						<option value='1'>Site Steward</option>
						<option value='2'>Site Assistant</option>
					</select>
			    	<input type="submit" value="Submit" />
			    	<input type="hidden" name="form" value="add_user_to_site" />
				</form><br>
				<?php	
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if ($_POST['form'] == 'add_user_to_site')
					{
						//retrieve our data from POST
						$user_id = $_POST['user_list'];
						$site_id = $_POST['site_list'];
						$user_level = $_POST['user_level'];
						
						if ( $user_id == 0 || $site_id == 0 || $user_level == 0)
							echo "You need to select a site, a user, and a permission level.";
						else 
						{
							// check if we are adding the user to all sites
							if ($site_id == 0.5) {
								// delete any existing permissions for this user
								$query = "DELETE FROM site_steward WHERE user_id = '$user_id'";
								$result = mysql_query($query);
								$query = "DELETE FROM site_assistant WHERE user_id = '$user_id'";
								$result = mysql_query($query);
								
								// get all the site ids
								$query_sites = "SELECT * FROM stewardship_site";
								$result_sites = mysql_query($query_sites);
								if (!$result_sites) 
									die('Invalid query: ' . mysql_error());
								if ($user_level == 1)
									$table = "site_steward";
								else if ($user_level == 2)
									$table = "site_assistant";
								// Iterates through each site, and add user
								while ($row = @mysql_fetch_assoc($result_sites)) 
								{
									$site_id = $row['id'];
									$query = "INSERT INTO $table (user_id, stewardshipsite_id) VALUES ('$user_id', '$site_id')";
									$result = mysql_query($query, $connection);
									if (!$result) 
										die('Error: ' . mysql_error());
								}
								echo "User successfully added to all sites.";
							} else {
								// we are adding the user to a single site
								// check if user already is steward							
								$query = "SELECT * FROM site_steward WHERE user_id = '$user_id' AND stewardshipsite_id = '$site_id'";
								$result = mysql_query($query);
								if(mysql_num_rows($result) > 0) 
							    	echo "User already added to site as steward.";
							    else {	 
							    	// check if user is already assistant
							    	$query = "SELECT * FROM site_assistant WHERE user_id = '$user_id' AND stewardshipsite_id = '$site_id'";
									$result = mysql_query($query);
									if(mysql_num_rows($result) > 0) 
								    	echo "User already added to site as assistant.";
							    	else {
								    	if ($user_level == 1)
											$table = "site_steward";
										else if ($user_level == 2)
											$table = "site_assistant";
										$query = "INSERT INTO $table (user_id, stewardshipsite_id) VALUES ('$user_id', '$site_id')";
										$result = mysql_query($query, $connection);
										if (!$result) 
											echo 'Error: ' . mysql_error();
										else {
											// echo $query . '<br>';	
											echo "User successfully added to site.";
										}
							    	}
							    }
							}
						}
					}
				}
			}
			?>


			<?php
			if ($function == 'remove_user_from_site') {
			?>
	
				<font face="verdana, helvetica" size="1">
				Remove User from Site:<br>
				<form action="admin.php?function=remove_user_from_site" method="post">
					<?php	
					$query_users = "SELECT * FROM users";
					$result_users = mysql_query($query_users);
					if (!$result_users) 
						die('Invalid query: ' . mysql_error());
						
					echo "<select name='user_list'><option value='0'>Select User</option>";
					// Iterates through each site, gets name of site from database
					while ($row = @mysql_fetch_assoc($result_users)) 
					{
						$userId = $row['id'];
						$userEmail = $row['email'];
						echo '<option value="' . $userId . '">' . $userEmail . '</option>';
					}
					echo "</select><br>";
					
					$query_sites = "SELECT * FROM stewardship_site";
					$result_sites = mysql_query($query_sites);
					if (!$result_sites) 
						die('Invalid query: ' . mysql_error());
					echo "<select name='site_list'><option value='0'>Select Site</option>";
					// Iterates through each site, gets name of site from database
					while ($row = @mysql_fetch_assoc($result_sites)) 
					{
						$siteId = $row['id'];
						$siteName = $row['name'];
						echo '<option value="' . $siteId . '">' . $siteName . '</option>';
					}
					echo "</select>";
					?>	
			
			    	<input type="submit" value="Submit" />
			    	<input type="hidden" name="form" value="remove_user_from_site" />
				</form><br>
				<?php	
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if ($_POST['form'] == 'remove_user_from_site')
					{
						//retrieve our data from POST
						$user_id = $_POST['user_list'];
						$site_id = $_POST['site_list'];
						
						if ( $user_id == 0 || $site_id == 0)
							echo "You need to select a site and a user.";
						else 
						{
							$query = "SELECT * FROM site_steward WHERE user_id = '$user_id' AND stewardshipsite_id = '$site_id'";
							$result = mysql_query($query);
							if(mysql_num_rows($result) == 0) {
								$is_site_steward = false;
							} else {	   
								$is_site_steward = true;
								$query = "DELETE FROM site_steward WHERE user_id = '$user_id' AND stewardshipsite_id = '$site_id'";
								$result = mysql_query($query, $connection);
								if (!$result) 
									echo 'Error: ' . mysql_error();
								else {
									// echo $query . '<br>';	
									echo "User was successfully removed as site steward.<br>";
								}
						    }
						  
					    	$query = "SELECT * FROM site_assistant WHERE user_id = '$user_id' AND stewardshipsite_id = '$site_id'";
							$result = mysql_query($query);
							if(mysql_num_rows($result) == 0) {
								if (!$is_site_steward)
						    		echo "User is not assigned to this site.<br>";
							} else {	   
								$query = "DELETE FROM site_assistant WHERE user_id = '$user_id' AND stewardshipsite_id = '$site_id'";
								$result = mysql_query($query, $connection);
								if (!$result) 
									echo 'Error: ' . mysql_error();
								else {
									// echo $query . '<br>';	
									echo "User was successfully removed as site assistant.<br>";
								}
						    }
						    
						    
						}
					}
				}
			}
			?>


			<?php
			if ($function == 'register_new_site') {
			?>
		
				<font face="verdana, helvetica" size="1">
				Register New Site:<br>
				<form action="admin.php?function=register_new_site" method="post">
			    	Name: <input type="text" name="site_name" maxlength="50" /><br>
			    	Border coordinates: <input type="text" name="coordinates" length="100" /><br>
			    	County: 
			    	<select name="county">
			    		<option>Select county</option>
  						<option>Boone, IL</option>
  						<option>Cook, IL</option>
  						<option>DuPage, IL</option>
  						<option>Jasper, IN</option>
  						<option>Kane, IL</option>
  						<option>Kankakee, IL</option>
  						<option>Kendall, IL</option>
  						<option>Kenosha, WI</option>
  						<option>Lake, IL</option>
  						<option>Lake, IN</option>
  						<option>Lafayette, WI</option>
  						<option>LaPorte, IN</option>
  						<option>Lee, IL</option>
  						<option>McHenry, IL</option>
  						<option>Newton, IN</option>
  						<option>Porter, IN</option>
  						<option>Racine, WI</option>
  						<option>Will, IL</option>
  						<option>Winnebago, IL</option>
					</select><br>
			    	
			    	<input type="hidden" name="form" value="register_new_site" />
			    	<input type="submit" value="Register" />
				</form><br>
				<?php
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					if ($_POST['form'] == 'register_new_site')
					{
						//retrieve our data from POST
						$site_name = $_POST['site_name'];
						$coordinates = $_POST['coordinates'];
						$county = $_POST['county'];
						
						// check that a county has been selected
							if($county == "Select county") {
							    echo "Please select the site's county. Site not added.";
							    exit;
						}
						
						// check coordinates
						if(strlen($coordinates) < 2) 
						    echo "Enter site coordinates. Site not added.";
						else
						{
							// check site name
							if(strlen($site_name) < 2) {
							    echo "Enter site name. Site not added.";
							    exit;
							} else
							{			
								//check to make sure site doesnt already exist
								$site_name = mysql_real_escape_string($site_name);
								$query = "SELECT * FROM stewardship_site WHERE name = '$site_name'";
								$result = mysql_query($query);
								if(mysql_num_rows($result) > 0) 
					    			echo "Site already exists.";
					    		else
					    		{	    		
									$query = "INSERT INTO stewardship_site (name, county) VALUES ('$site_name', '$county')";
									$result = mysql_query($query, $connection);
									if (!$result) 
								  		echo 'Error: ' . mysql_error();
								  	else 
								  	{
								  		echo $query . '<br>';
								  		$query = "SELECT * FROM stewardship_site WHERE name = '$site_name'";
										$result = mysql_query($query);
										while ($row = @mysql_fetch_assoc($result))
										{
											$site_id = $row['id'];
									  		$query = "INSERT INTO border (stewardshipsite_id, coordinates) VALUES ('$site_id', '$coordinates')";
											$result = mysql_query($query, $connection);
											if (!$result) 
									  			echo 'Error: ' . mysql_error();
									  		else {
												// echo $query . '<br>';	
												echo "Site successfully added.";
									  		}
										}
								  	}
					    		}
							}
						}
					}
				}
			}
			?>
			
			
			
			<?php
			if ($function == 'generate_report') {
			?>

				<font face="verdana, helvetica" size="1">
				Generate User Report:<br>
				<form action="admin.php?function=generate_report" method="post">
					<?php	
					$query_users = "SELECT * FROM users ORDER BY last_name";
					$result_users = mysql_query($query_users);
					if (!$result_users) 
						die('Invalid query: ' . mysql_error());
						
					echo "<select name='user_list'><option value='-1'>Select User</option>";
					while ($row = @mysql_fetch_assoc($result_users)) 
					{
						$userId = $row['id'];
						$userEmail = $row['email'];
						
						$last_name = $row['last_name'];
						$first_name = $row['first_name'];
						$full_name = $last_name . ', ' . $first_name;
						if ($last_name !== 'guest')
							echo '<option value="' . $userId . '">' . $full_name . '</option>';
					}
					echo "</select>";
					?>	
			    	<input type="submit" value="Submit" />
			    	<input type="hidden" name="form" value="user_report" />
				</form><br>
				
				Generate Site Report:<br>
				<form action="admin.php?function=generate_report" method="post">
					<?php	
					$query_sites = "SELECT * FROM stewardship_site ORDER BY name";
					$result_sites = mysql_query($query_sites);
					if (!$result_sites) 
						die('Invalid query: ' . mysql_error());
					echo "<select name='site_list'><option value='-1'>Select Site</option>";
					// Iterates through each site, gets name of site from database
					while ($row = @mysql_fetch_assoc($result_sites)) 
					{
						$siteId = $row['id'];
						$siteName = $row['name'];
						echo '<option value="' . $siteId . '">' . $siteName . '</option>';
					}
					echo "</select>";
					?>	
			    	<input type="submit" value="Submit" />
			    	<input type="hidden" name="form" value="site_report" />
				</form><br>
				<!--
				Generate County Report:<br>
				<form action="admin.php?function=generate_report" method="post">
					<select name="county">
			    		<option>Select county</option>
  						<option>Boone, IL</option>
  						<option>Cook, IL</option>
  						<option>DuPage, IL</option>
  						<option>Jasper, IN</option>
  						<option>Kane, IL</option>
  						<option>Kendall, IL</option>
  						<option>Kenosha, WI</option>
  						<option>Lake, IL</option>
  						<option>Lake, IN</option>
  						<option>LaPorte, IN</option>
  						<option>Lee, IL</option>
  						<option>McHenry, IL</option>
  						<option>Newton, IN</option>
  						<option>Porter, IN</option>
  						<option>Racine, WI</option>
  						<option>Will, IL</option>
  						<option>Winnebago, IL</option>
					</select>
					<input type="submit" value="Submit" />
			    	<input type="hidden" name="form" value="county_report" />
				</form><br>
				-->
				<?php	
				if ($_SERVER['REQUEST_METHOD'] == 'POST')
				{
					//
					// generate user report
					//
					if ($_POST['form'] == 'user_report')
					{
						//retrieve our data from POST
						$user_id = $_POST['user_list'];
						
						echo 'Click <a href="download_user_report.php?user_id='.$user_id.'">here</a> to download this data as a spreadsheet file.<br><br>';
						
						echo '<table border="1"><tr><td><b>Stewardship Site</b></td><td><b>County</b></td><td><b>Type</b></td><td><b>Date</b></td><td><b>Name</b></td><td><b>Description</b></td><td><b>Acreage</b></td></tr>';
						
						
						$query = "SELECT * FROM brush WHERE user_id = '$user_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Bush and tree removal</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td></tr>';
							}
						}
						
						$query = "SELECT * FROM landmark WHERE user_id = '$user_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Geographic feature / Landmark</td><td>';
								echo 'N/A</td><td>' . $row['name'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td></tr>';
							}
						}
						
						
						$query = "SELECT * FROM other WHERE user_id = '$user_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Planning and other</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td></tr>';
							}
						}
						
						
						$query = "SELECT * FROM burns WHERE user_id = '$user_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Prescribed burn</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td></tr>';
							}
						}
						
						
						$query = "SELECT * FROM seed WHERE user_id = '$user_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Seed collection and planting</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td></tr>';
							}
						}
						
						
						$query = "SELECT * FROM weed WHERE user_id = '$user_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Weed control</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td></tr>';
							}
						}
					
						$query = "SELECT * FROM trails WHERE user_id = '$user_id'";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Trails</td><td>';
								echo 'N/A</td><td>' . $row['name'] . '</td><td>N/A</td><td>N/A</td></tr>';
							}
						}
						
						echo '</table>';					
					}
				
					//
					// generate site report
					//
					if ($_POST['form'] == 'site_report')
					{
						//retrieve our data from POST
						$site_id = $_POST['site_list'];
						
						// get user info
						$query_users = "SELECT * FROM users ORDER BY id";
						$result_users = mysql_query($query_users);
						if (!$result_users) 
							die('Invalid query: ' . mysql_error());
						
						$usersArray = array();
						while ($row = @mysql_fetch_assoc($result_users)) {
							$usersArray[$row['id']] = array($row['last_name'], $row['first_name'], $row['email']);
						}						
						
						echo 'Click <a href="download_site_report_admin.php?site_id='.$site_id.'">here</a> to download this data as a spreadsheet file.<br><br>';
						
						echo '<table border="1"><tr><td><b>Stewardship Site</b></td><td><b>County</b></td><td><b>Type</b></td><td><b>Date</b></td><td><b>Name</b></td><td><b>Description</b></td><td><b>Acreage</b></td><td><b>User Last Name</b></td><td><b>User First Name</b></td><td><b>User Email</b></td></tr>';
						
						
						$query = "SELECT * FROM brush WHERE stewardshipsite_id = '$site_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  {	
							while ($row = @mysql_fetch_assoc($results)) {								
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';				
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Bush and tree removal</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td>'.$userInfo.'</tr>';
							}
						}
						
						$query = "SELECT * FROM landmark WHERE stewardshipsite_id = '$site_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Geographic feature / Landmark</td><td>';
								echo 'N/A</td><td>' . $row['name'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td>'.$userInfo.'</tr>';
							}
						}
						
						
						$query = "SELECT * FROM other WHERE stewardshipsite_id = '$site_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';											
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Planning and other</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td>'.$userInfo.'</tr>';
							}
						}
						
						
						$query = "SELECT * FROM burns WHERE stewardshipsite_id = '$site_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';				
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Prescribed burn</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td>'.$userInfo.'</tr>';
							}
						}
						
						
						$query = "SELECT * FROM seed WHERE stewardshipsite_id = '$site_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';											
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Seed collection and planting</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td>'.$userInfo.'</tr>';
							}
						}
						
						
						$query = "SELECT * FROM weed WHERE stewardshipsite_id = '$site_id' ORDER BY date";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';				
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Weed control</td><td>';
								echo $row['date'] . '</td><td>' . $row['title'] . '</td><td>' . $row['description'] . '</td><td>'.calculate_acreage($row['coordinates']).'</td>'.$userInfo.'</tr>';
							}
						}
					
						$query = "SELECT * FROM trails WHERE stewardshipsite_id = '$site_id'";
						$results = mysql_query($query);
						if ($results)  
						{	
							while ($row = @mysql_fetch_assoc($results)) 
							{
								$user_id = $row['user_id'];
								$userInfo = '<td>' . $usersArray[$user_id][0] . '</td><td>' . $usersArray[$user_id][1] . '</td><td>' . $usersArray[$user_id][2] . '</td>';											
								$thisSiteId = $row['stewardshipsite_id'];
								$query2 = "SELECT * FROM stewardship_site WHERE id='$thisSiteId'";
								$result2 = mysql_query($query2);
								if (!$result2) 
									die('Invalid query: ' . mysql_error());
								$row2 = @mysql_fetch_assoc($result2);
								echo '<tr><td>' . $row2['name'] . '</td><td>' . $row2['county'] . '</td><td>Trails</td><td>';
								echo 'N/A</td><td>' . $row['name'] . '</td><td>N/A</td><td>N/A</td></tr>';
							}
						}
						
						echo '</table>';					
					}
				}

			}
			
			function lon2x($lon) { return deg2rad($lon * 6378137.0); }
			function lat2y($lat) { return log(tan(M_PI_4 + deg2rad($lat) / 2.0)) * 6378137.0; }
			function x2lon($x) { return rad2deg($x / 6378137.0); }
			function y2lat($y) { return rad2deg(2.0 * atan(exp($y / 6378137.0)) - M_PI_2); }
			
			function calculate_acreage($these_coordinates) {
				// first project longitude and latitude as x and y, then calculate area according to this formula:
				// A = (1/2)(x1*y2 - x2*y1 + x2*y3 - x3*y2 + x3y4 - x4y3 + x4*y5 - x5*y4 + x5*y1 - x1*y5)
				$points = explode(' ', trim($these_coordinates));
				$number_of_points = count($points);
				if ($number_of_points < 3)
					return 0;
				if (trim($points[0]) != trim(end($points)))
					return 0;
				if (trim($points[0]) == trim(end($points)) && $number_of_points == 3)
					return 0;
				// make the last point the same as the first point to ensure the polygon is closed
			        array_push($points, $points[0]);
				$number_of_points++;
				$counter = 0;
				$sum = 0;
				while ($counter < $number_of_points - 1) {		
					$thisCoordinate = explode(',', $points[$counter]);
					$thisX = lon2x($thisCoordinate[0]); 
					$thisY = lat2y($thisCoordinate[1]); 
					if ($counter > 0) {
						$sum += ($oldX * $thisY) - ($thisX * $oldY);
					}
					$oldX = $thisX;
					$oldY = $thisY;
					$counter++;
				}
				// convert square meters to acres, round and return   1.0913
				return abs(round($sum * 0.000247105381 / 4 * 1.118614, 2));
			}
			
			?>	
			
			
		</td>
	</tr>
	</table>
	
	<?php
	mysql_close($connection);
	?>
	
	</body>
</html>
