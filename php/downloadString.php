<?php
//$file = fopen("test.txt", 'w');
//fwrite($file, stripslashes($_GET[string]));
//fclose($fh);
header('Content-Description: File Download');
header("Cache-Control: no-store, no-cache");
header('Content-type: application/vnd.google-earth.kml+xml');
header('Content-disposition: attachment; filename="download.kml"');
header('Content-Transfer-Encoding: binary');
header('Expires: 0');
header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
header('Pragma: public');
header('Content-Length: ' . strlen(stripslashes($_POST[string])));
echo stripslashes($_POST[string]);
exit;
?>