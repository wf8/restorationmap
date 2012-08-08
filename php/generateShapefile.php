<?php
require_once('lib/ShapeFile.lib.php');
require_once('lib/Zip.php');
//@apache_setenv('no-gzip', 1);
//@ini_set('zlib.output_compression', 0);

$kml_string = stripslashes($_POST[kmlstring]);
$begin = strpos($kml_string, "<coordinates>") + 13;
$end = strpos($kml_string, "</coordinates>");
$coordinates_list = trim(substr($kml_string, $begin, $end - $begin));	

$coordinates = explode(" ", $coordinates_list);

$shp = new ShapeFile(5);
$record0 = new ShapeRecord(5);
$i = 0;
while ($i < count($coordinates)) {
	$point_coordinates = $coordinates[$i];
	$point = explode(",", trim($point_coordinates));
	$record0->addPoint(array("x" => $point[0], "y" => $point[1]));
	$i++;
}

$shp->addRecord($record0);

$shp->saveSimpleShapefile('shapefiles/download-shapefile.*');

$zip_file = new Archive_Zip('shapefiles/download-shapefile.zip');

$files_to_zip = array(
  'shapefiles/download-shapefile.shp',
  'shapefiles/download-shapefile.prj',
  'shapefiles/download-shapefile.dbf',
  'shapefiles/download-shapefile.shx'
);
$zip_file->create($files_to_zip);
exit();
?>