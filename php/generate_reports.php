<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if( !$_SESSION['valid'] )
{
    echo "not logged in";
    die();
} 

 // Opens a connection to a MySQL server.
$connection = mysql_connect ($db_server, $db_username, $db_password);
if (!$connection) 
	die('Not connected : ' . mysql_error());	


// Sets the active MySQL database.
$db_selected = mysql_select_db($db_database, $connection);
if (!$db_selected) 
	die ('Can\'t use db : ' . mysql_error());
	
$report = $_GET['report'];	
	
?>
<!DOCTYPE html>
<html>
	<head>
		<title>Restoration Map: Reports</title>
	</head>
	<body>
	
	<font face="arial, helvetica" size="8">
	Restoration Map: Reports
	</font>
	<font face="verdana, helvetica" size="1">
	<br><br><br>
	<?php	

		//
		// generate user report
		//
		if ($report == 'user')
		{
			//retrieve our data from POST
			$user_id = $_SESSION['user_id'];
			
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
		} else {
	
		//
		// generate site report
		//

			//retrieve our data from POST
			$site_id = $report;
			
			echo 'Click <a href="download_site_report.php?site_id='.$site_id.'">here</a> to download this data as a spreadsheet file.<br><br>';
			
			echo '<table border="1"><tr><td><b>Stewardship Site</b></td><td><b>County</b></td><td><b>Type</b></td><td><b>Date</b></td><td><b>Name</b></td><td><b>Description</b></td><td><b>Acreage</b></td></tr>';
			
			
			$query = "SELECT * FROM brush WHERE stewardshipsite_id = '$site_id' ORDER BY date";
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
			
			$query = "SELECT * FROM landmark WHERE stewardshipsite_id = '$site_id' ORDER BY date";
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
			
			
			$query = "SELECT * FROM other WHERE stewardshipsite_id = '$site_id' ORDER BY date";
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
			
			
			$query = "SELECT * FROM burns WHERE stewardshipsite_id = '$site_id' ORDER BY date";
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
			
			
			$query = "SELECT * FROM seed WHERE stewardshipsite_id = '$site_id' ORDER BY date";
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
			
			
			$query = "SELECT * FROM weed WHERE stewardshipsite_id = '$site_id' ORDER BY date";
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
		
			$query = "SELECT * FROM trails WHERE stewardshipsite_id = '$site_id'";
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
	
		mysql_close($connection);
	?>
	
	</body>
</html>
