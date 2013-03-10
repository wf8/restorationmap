<?php
session_start();

require('restorationmap_config.php');

//if the user is not logged in
if(!$_SESSION['valid'])
{
    echo "not logged in";
    die();
} 
 
$opacity = $_SESSION['opacity'];

/**
<select id="user_polygon_opacity">
	<option value="100">100% (solid fill)</option>
	<option value="90">90%</option>
	<option value="80">80%</option>
	<option value="70">70%</option>
	<option value="60">60%</option>
	<option value="50">50%</option>
	<option value="40">40%</option>
	<option value="30">30%</option>
	<option value="20">20%</option>
	<option value="10">10%</option>
	<option value="0" selected="selected">0% (outline only)</option>
</select>
*/

echo '<select id="user_polygon_opacity">';
if ($opacity == 100)
	echo '<option value="100" selected="selected">100% (solid fill)</option>';
else
	echo '<option value="100">100% (solid fill)</option>';
for ($i = 90; $i > 0; $i = $i - 10) {
	if ($opacity == $i)
		echo '<option value="'.$i.'" selected="selected">'.$i.'%</option>';
	else
		echo '<option value="'.$i.'">'.$i.'%</option>';
}
if ($opacity == 0)
	echo '<option value="0" selected="selected">0% (outline only)</option>';
else
	echo '<option value="0">0% (outline only)</option>';
echo '</select>';

?>