<?php
header('Content-Description: File Download');
header("Cache-Control: no-store, no-cache");
header("Content-type: text/csv");
header("Content-Disposition: attachment; filename=weed_scout.csv");
header("Pragma: no-cache");
header("Expires: 0");
header('Content-Length: ' . strlen(stripslashes($_POST[string])));
echo stripslashes($_POST[string]);
exit;
?>