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
	if ($file_extension == "gpx" || $file_extension == "kml")
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
	if	($_FILES["upload_file"]["size"] > 100000000) // 100 kb restriction // temporarily lifted
		$result = "Error: File must be under 750 mb.";
	else
	{
   		// Creates an array of strings to hold the lines of the KML file.
		$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
		$kml[] = '<kml xmlns="http://earth.google.com/kml/2.1">';
		$kml[] = '<Placemark>';

   		$uploaded_file = file_get_contents($_FILES['upload_file']['tmp_name']);

   		$filename = explode(".", $_FILES["upload_file"]["name"]);
		$file_extension = $filename[1];
   		$coordinates = '';

   		if ($file_extension == "gpx")
   		{
   			$pos_lat = 0;
   			$pos_lon = 0;
   			$pos_lat_old = 0;

   			$finished = false;
   			while ( ! $finished )
   			{
				$pos_lat = strpos($uploaded_file, '<trkpt lat="', $pos_lat_old);
				if ($pos_lat === false)
				{
					$finished = true;
					break;
				}
				$pos_lat_end = strpos($uploaded_file, '" lon="', $pos_lat);
				$lat = substr($uploaded_file, $pos_lat + 12, $pos_lat_end - $pos_lat - 12);

				$pos_lon = strpos($uploaded_file, '" lon="', $pos_lat);
				$pos_lon_end = strpos($uploaded_file, '">', $pos_lat);
				$lon = substr($uploaded_file, $pos_lon + 7, $pos_lon_end - $pos_lon - 7);

				$pos_lat_old = $pos_lat_end;

				$coordinates = $coordinates . $lon . ',' . $lat . ',0 ';
   			}
   			$kml[] = '<LineString><tessellate>1</tessellate><coordinates>';
   			$kml[] = $coordinates;
   			$kml[] = '</coordinates></LineString></Placemark></kml>';
   		}
   		if ($file_extension == "kml")
   		{
   			$begin_coordinates = strpos($uploaded_file, '<coordinates>') + 13;
   			$end_coordinates = strpos($uploaded_file, '</coordinates>');
   			// check if we are uploading a path
   			if (strpos($uploaded_file, '<coordinates>') === false) {
   				$pos = 0;
   				while (strpos($uploaded_file, '<gx:coord>', $pos) !== false) {
   					$begin_coordinate = strpos($uploaded_file, '<gx:coord>', $pos) + 10;
   					$end_coordinate = strpos($uploaded_file, '</gx:coord>', $pos);
   					$coordinate = trim(substr($uploaded_file, $begin_coordinate, $end_coordinate - $begin_coordinate));
   					$coordinate = str_replace(' ', ',', $coordinate);
   					$coordinates = $coordinates . $coordinate . ' ';
   					$pos = $end_coordinate + 10;
   				}
   				$kml[] = '<LineString><tessellate>1</tessellate><coordinates>';
				$kml[] = trim($coordinates);
   				$kml[] = '</coordinates></LineString></Placemark></kml>';
   			} else {
				$coordinates = substr($uploaded_file, $begin_coordinates, $end_coordinates - $begin_coordinates);
				// check if we have uploaded a point or a polygon
				if (count(explode(" ", trim($coordinates))) == 1) {
					$kml[] = '<Point><coordinates>';
					$kml[] = trim($coordinates);
					$kml[] = '</coordinates></Point></Placemark></kml>';
				} else {
					$kml[] = '<LineString><tessellate>1</tessellate><coordinates>';
					$kml[] = trim($coordinates);
					$kml[] = '</coordinates></LineString></Placemark></kml>';
				}
		   	}
   		}
		$result = join("\n", $kml);
		//header('Content-type: application/vnd.google-earth.kml+xml');
	}
} else
	$result = "Error: Invalid file type.";
?>
<html>
<head>
<script language="javascript" type="text/javascript">
	var result = <?= json_encode($result); ?>;
	window.top.window.stopKmlTrailUpload(result);
</script>
</head>
<body></body>
</html>