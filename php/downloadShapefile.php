<?php
header("Pragma: public"); 
header("Expires: 0");
header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
header("Cache-Control: private",false); 
header("Content-Type: application/zip");
header("Content-Disposition: attachment; filename=\"download-shapefile.zip\";" );
header("Content-Transfer-Encoding: binary");
header("Content-Length: ".filesize('shapefiles/download-shapefile.zip'));
ob_clean();
flush();
readfile("shapefiles/download-shapefile.zip");
exit();
?>