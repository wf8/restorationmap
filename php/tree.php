<?php

// we can't use session variables here, so we must use URL parameter
// to pass the user_id to stewardship_sites.php so we only show the correct private layers
$user_id = $_GET[user_id];

$kml = array('<?xml version="1.0" encoding="UTF-8"?>');
$kml[] = '<kml xmlns="http://www.opengis.net/kml/2.2" xmlns:gx="http://www.google.com/kml/ext/2.2" xmlns:kml="http://www.opengis.net/kml/2.2" xmlns:atom="http://www.w3.org/2005/Atom">';
$kml[] = '<Document>';

$kml[] = '	<Folder>';
$kml[] = '		<name>Useful Layers</name>';
$kml[] = '		<visibility>0</visibility>';
		
$kml[] = '		<NetworkLink>';
$kml[] = '			<flyToView>0</flyToView>';
$kml[] = '			<name>NRCS Soil Data</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '			<Link>';
$kml[] = '				<href>soil_data.kml</href>';
$kml[] = '			</Link>';
$kml[] = '		</NetworkLink>';	
		
$kml[] = '		<NetworkLink>';
$kml[] = '			<flyToView>0</flyToView>';
$kml[] = '			<name>Wetlands Data (US FWS)</name>';
$kml[] = '			<open>0</open>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '			<Link>';
$kml[] = '				<href>wetlands.kml</href>';
$kml[] = '			</Link>';
$kml[] = '		</NetworkLink>';
		
$kml[] = '		<Folder id="LAYER_ROADS">';
$kml[] = '			<name>Roads</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '		</Folder>';
$kml[] = '		<Folder id="LAYER_BORDERS">';
$kml[] = '			<name>Borders</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '		</Folder>';
$kml[] = '		<Folder id="LAYER_TERRAIN">';
$kml[] = '			<name>Terrain</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '		</Folder>';
$kml[] = '		<Folder id="HISTORICAL_IMAGERY">';
$kml[] = '			<name>Historical Imagery</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '		</Folder>';
$kml[] = '		<Folder id="STATUS_BAR">';
$kml[] = '			<name>Status Bar</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '		</Folder>';
$kml[] = '		<Folder id="SCALE_LEGEND">';
$kml[] = '			<name>Scale Legend</name>';
$kml[] = '			<visibility>0</visibility>';
$kml[] = '		</Folder>';
$kml[] = '	</Folder>	';

	
$kml[] = '	<Folder>';
$kml[] = '		<flyToView>0</flyToView>';
$kml[] = '		<name>Monitoring Data</name>';
$kml[] = '		<open>1</open>';
$kml[] = '		<visibility>0</visibility>';
			
$kml[] = '			<GroundOverlay id="bcn_point_count">';
$kml[] = '				<flyToView>0</flyToView>';
$kml[] = '				<name>BCN Point Count</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';
			
$kml[] = '			<GroundOverlay id="frog_survey">';
$kml[] = '				<flyToView>0</flyToView>';
$kml[] = '				<name>Calling Frog Survey</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';

$kml[] = '			<GroundOverlay id="land_audit_2001">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Cook County Land Audit 2001</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';
			
$kml[] = '			<GroundOverlay id="land_audit_2007">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Cook County Land Audit 2007-2008</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';

$kml[] = '			<GroundOverlay id="fpdcc_management_units">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>FPDCC Management Units</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';
			
$kml[] = '			<GroundOverlay id="fpdcc_nat_comm">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>FPDCC Natural Communities</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';
			
$kml[] = '			<GroundOverlay id="shrub_survey">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Somme Shrub Survey</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';
			
$kml[] = '			<GroundOverlay id="sc_2011_blitz">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Spring Creek 2011 Bird Blitz</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';
			
$kml[] = '			<GroundOverlay id="sc_2011_invasives_blitz">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Spring Creek 2011 Invasives Blitz</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';

$kml[] = '			<GroundOverlay id="visual_report">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Visual Reports</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';

$kml[] = '			<GroundOverlay id="weed_scouts">';
$kml[] = '				<flyToView>0</flyToView>	';
$kml[] = '				<name>Weed Scouts</name>';
$kml[] = '				<open>0</open>';
$kml[] = '				<visibility>0</visibility>';
$kml[] = '			</GroundOverlay>';

$kml[] = '	</Folder>';

$kml[] = '	<NetworkLink>';
$kml[] = '		<flyToView>0</flyToView>';
$kml[] = '		<name>Habitat Restoration</name>';
$kml[] = '		<open>1</open>';
$kml[] = '		<visibility>0</visibility>';
$kml[] = '		<Link>';
$kml[] = '			<viewRefreshMode>onRequest</viewRefreshMode>';
$kml[] = '			<href>../php/stewardship_sites.php?user_id=' . $user_id . '</href>';
$kml[] = '		</Link>';
$kml[] = '	</NetworkLink>	';


$kml[] = ' </Document>';
$kml[] = '</kml>';
$kmlOutput = join("\n", $kml);
header('Content-type: application/vnd.google-earth.kml+xml');
echo $kmlOutput;
?>