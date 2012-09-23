<?php
set_time_limit(0);
function checkFileType()
{
	if (($_FILES["upload_file"]["type"] == "application/vnd.google-earth.kml+xml")
	|| ($_FILES["upload_file"]["type"] == "application/xml")
	|| ($_FILES["upload_file"]["type"] == "text/xml")
	|| ($_FILES["upload_file"]["type"] == "application/gpx+xml"))
		return true;
	else
		return false;
}
function checkFileExtension()
{
	$filename = explode(".", $_FILES["upload_file"]["name"]);
	$file_extension = $filename[1];
	if ( $file_extension == "kml")
		return true;
	else
		return false;
}
if ($_FILES["upload_file"]["error"] == 4)
	$result = "Error: Please select a file.";
else if ($_FILES["upload_file"]["error"] > 0)
	$result = "Error: " . $_FILES["upload_file"]["error"];
else if ( checkFileType() || checkFileExtension() )
{
	if	($_FILES["upload_file"]["size"] > 100000000) // 100 kb restriction // made it 100000kb temporarily
		$result = "Error: File must be under 750 mb.";
	else
	{
   		// Creates an array of strings to hold the lines of the KML file.
		$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
		$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';

   		$uploaded_file = file_get_contents($_FILES['upload_file']['tmp_name']);

   		$filename = explode(".", $_FILES["upload_file"]["name"]);
		$file_extension = $filename[1];
   		$coordinates = '';


   		if ($file_extension == "kml")
   		{
   			
   			$begin_coordinates = strpos($uploaded_file, '<coordinates>') + 13;
   			if (!$begin_coordinates)
   				$result = "Error: No geometry found in KML file.";
   			else {
   				$old_begin_coordinates = -1;
				while($begin_coordinates > $old_begin_coordinates) {
					
					$end_coordinates = strpos($uploaded_file, '</coordinates>', $begin_coordinates);
					$coordinates = trim(substr($uploaded_file, $begin_coordinates, $end_coordinates - $begin_coordinates));				
					$points = explode(' ', $coordinates);
					$number_of_points = count($points);		
					
					// check if we are displyaing a polygon or a point
					if ($number_of_points < 3) {
						// display a point
						$kml[] = '<Placemark>';
						$kml[] = ' <Style><IconStyle><color>ffffffff</color>';
						$kml[] = ' <Icon><href>http://habitatproject.org/restorationmap/images/placemark_circle.png</href></Icon>';
						$kml[] = ' <scale>0.8</scale></IconStyle><LabelStyle><scale>0</scale></LabelStyle></Style><Point><coordinates>';
						$kml[] = $coordinates;
						$kml[] = ' </coordinates></Point></Placemark>';
					} else {
						// display a polygon
						$kml[] = '<Placemark>';
						$kml[] = '   <Style><LineStyle><width>4</width><color>ffffffff</color></LineStyle><PolyStyle><fill>0</fill></PolyStyle></Style>';
						$kml[] = '   <Polygon><tessellate>1</tessellate><outerBoundaryIs><LinearRing><coordinates>';		
						$kml[] = $coordinates;
						$kml[] = ' </coordinates></LinearRing></outerBoundaryIs></Polygon></Placemark>';
					}
					$old_begin_coordinates = $begin_coordinates;
					$begin_coordinates = strpos($uploaded_file, '<coordinates>', $old_begin_coordinates) + 13;
				}
				$kml[] = '</kml>';
		   		$result = join("\n", $kml);
   			}
   		}
	}
} else
	$result = "Error: Invalid file type.";
?>
<html>
<head>
<script language="javascript" type="text/javascript">
	var result = <?= json_encode($result); ?>;
	window.top.window.stopMultigeoUpload(result);
</script>
</head>
<body></body>
</html>