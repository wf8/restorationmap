/**
 * ---------------------------------------------------------
 * constants, etc
 *
 * ---------------------------------------------------------
 */
 
 var monitoringPanelList = [ 'sc2011Panel','sc2011invasivesPanel','bcnPointCountPanel','frogPanel', 'landAuditPanel','landAudit2001Panel','natural_communties_panel', 'ws_display_data_panel', 'ws_data_entry_panel' ];

/**
 * ---------------------------------------------------------
 * utility functions
 *
 * ---------------------------------------------------------
 */
 
function initMonitoringLayers() {
	// detect when certain nodes are clicked
	$(tree).bind('click', function(event, node, kmlObject){
		//if (tree.lookup(node).getName() == "Spring Creek 2011 Bird Blitz") 
			//open_sc_2011_panel();
	});	
	// detect double click
	$(tree).bind('dblclick', function(event, node, kmlObject){
		if (tree.lookup(node).getName() == "Spring Creek 2011 Bird Blitz") {
			zoom_to_sc_2011();
			open_sc_2011_panel();
		}
		if (tree.lookup(node).getName() == "Spring Creek 2011 Invasives Blitz") {
			zoom_to_sc_2011_invasives();
			open_sc_2011_invasives_panel();
		}
		if (tree.lookup(node).getName() == "BCN Point Count") {
			open_bcn_point_count_panel();
		}
		if (tree.lookup(node).getName() == "Calling Frog Survey") {
			open_frog_panel();
		}
		if (tree.lookup(node).getName() == "Cook County Land Audit 2007-2008") {
			open_land_audit_panel();
		}
		if (tree.lookup(node).getName() == "Cook County Land Audit 2001") {
			open_land_audit_2001_panel();
		}
		if (tree.lookup(node).getName() == "FPDCC Natural Communities") {
			open_nat_comm_panel();
		}
		if (tree.lookup(node).getName() == "Weed Scouts") {
			open_ws_panel();
		}
	});	
	// detect when networklinks are loaded
	$(tree).bind('networklinkload', function(event, node, kmlObject){
		// do nothing
	});
	// detect when items are toggled
	$(tree).bind('toggleItem', function(event, node, kmlObject){
		if (tree.lookup(node).getName() == "Spring Creek 2011 Bird Blitz" && tree.lookup(node).getVisibility()) {
			if (sc2011KmlObject != null) 
				ge.getFeatures().appendChild(sc2011KmlObject);
			open_sc_2011_panel();
		}
		if (tree.lookup(node).getName() == "Spring Creek 2011 Bird Blitz" && !tree.lookup(node).getVisibility()) {
			if (sc2011KmlObject != null) 
				ge.getFeatures().removeChild(sc2011KmlObject);
		}
		if (tree.lookup(node).getName() == "Spring Creek 2011 Invasives Blitz" && tree.lookup(node).getVisibility()) {
			if (sc2011invasivesKmlObject != null) 
				ge.getFeatures().appendChild(sc2011invasivesKmlObject);
			open_sc_2011_invasives_panel();
		}
		if (tree.lookup(node).getName() == "Spring Creek 2011 Invasives Blitz" && !tree.lookup(node).getVisibility()) {
			if (sc2011invasivesKmlObject != null) 
				ge.getFeatures().removeChild(sc2011invasivesKmlObject);
		}
		if (tree.lookup(node).getName() == "BCN Point Count" && tree.lookup(node).getVisibility()) {
			if (bcn_point_count_kml_object != null) 
				ge.getFeatures().appendChild(bcn_point_count_kml_object);
			open_bcn_point_count_panel();
		}
		if (tree.lookup(node).getName() == "BCN Point Count" && !tree.lookup(node).getVisibility()) {
			if (bcn_point_count_kml_object != null) 
				ge.getFeatures().removeChild(bcn_point_count_kml_object);
		}
		if (tree.lookup(node).getName() == "Calling Frog Survey" && tree.lookup(node).getVisibility()) {
			if (frog_kml_object != null) 
				ge.getFeatures().appendChild(frog_kml_object);
			open_frog_panel();
		}
		if (tree.lookup(node).getName() == "Calling Frog Survey" && !tree.lookup(node).getVisibility()) {
			if (frog_kml_object != null) 
				ge.getFeatures().removeChild(frog_kml_object);
		}
		if (tree.lookup(node).getName() == "Cook County Land Audit 2007-2008" && tree.lookup(node).getVisibility()) {
			if (land_audit_kml_object != null) 
				ge.getFeatures().appendChild(land_audit_kml_object);
			open_land_audit_panel();
		}
		if (tree.lookup(node).getName() == "Cook County Land Audit 2007-2008" && !tree.lookup(node).getVisibility()) {
			if (land_audit_kml_object != null) 
				ge.getFeatures().removeChild(land_audit_kml_object);
		}
		if (tree.lookup(node).getName() == "Cook County Land Audit 2001" && tree.lookup(node).getVisibility()) {
			if (land_audit_2001_kml_object != null) 
				ge.getFeatures().appendChild(land_audit_2001_kml_object);
			open_land_audit_2001_panel();
		}
		if (tree.lookup(node).getName() == "Cook County Land Audit 2001" && !tree.lookup(node).getVisibility()) {
			if (land_audit_2001_kml_object != null) 
				ge.getFeatures().removeChild(land_audit_2001_kml_object);
		}
		if (tree.lookup(node).getName() == "FPDCC Natural Communities" && tree.lookup(node).getVisibility()) {
			if (nat_comm_kml_object != null) 
				ge.getFeatures().appendChild(nat_comm_kml_object);
			open_nat_comm_panel();
		}
		if (tree.lookup(node).getName() == "FPDCC Natural Communities" && !tree.lookup(node).getVisibility()) {
			if (nat_comm_kml_object != null) 
				ge.getFeatures().removeChild(nat_comm_kml_object);
		}
		if (tree.lookup(node).getName() == "FPDCC Management Units" && !tree.lookup(node).getVisibility()) {
			if (management_units_kml_object != null) 
				ge.getFeatures().removeChild(management_units_kml_object);
		}
		if (tree.lookup(node).getName() == "FPDCC Management Units" && tree.lookup(node).getVisibility()) {
			if (management_units_kml_object != null) 
				ge.getFeatures().appendChild(management_units_kml_object);
			else {
				alert("This is a large data file and may take 1-2 minutes to download.");
				load_management_units();
			}
		}
		if (tree.lookup(node).getName() == "Weed Scouts" && tree.lookup(node).getVisibility()) {
			if (ws_kml_object != null) 
				ge.getFeatures().appendChild(ws_kml_object);
			open_ws_panel();
		}
		if (tree.lookup(node).getName() == "Weed Scouts" && !tree.lookup(node).getVisibility()) {
			if (ws_kml_object != null) 
				ge.getFeatures().removeChild(ws_kml_object);
		}
	});
	// make sure monitoring layers are null to start
	sc2011KmlObject = null;
	sc2011invasivesKmlObject = null; 
	bcn_point_count_kml_object = null;
	frog_kml_object = null;
	land_audit_kml_object = null;
	land_audit_2001_kml_object = null;
	nat_comm_kml_object = null;
	management_units_kml_object = null;
	ws_kml_object = null;
}

function resizeMonitorPanels(windowHeight) {
	var i = 0;
	while (i < monitoringPanelList.length)
	{
		$('#'+monitoringPanelList[i]).height(windowHeight - 36);
		i++;
	}
}

function closeMonitoringPanels() {
	var i = 0;
	while (i < monitoringPanelList.length)
	{
		if ( document.getElementById( monitoringPanelList[i] ).style.visibility == "visible")
		{
			switch( monitoringPanelList[i] )
			{
				case 'bcnPointCountPanel':
					if (bcn_pc_polygon != null) {
						bcn_pc_polygon.clear();
						bcn_pc_polygon = null;
					}
					fade("bcnPointCountPanel");
					break;
					
				case 'natural_communties_panel':
					document.getElementById("nat_comm_opacity_div").style.visibility = "hidden";
					fade('natural_communties_panel');
					break;
				
				default:
					fade(monitoringPanelList[i]);
					break;
			}
		}
		i++;
	}
}

/**
 * ---------------------------------------------------------
 *
 * spring creek 2011 bird blitz monitoring functions
 *
 * ---------------------------------------------------------
 */
 
var sc2011KmlObject = null; 

function open_sc_2011_panel() {
	// show activity monitor
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getSC2011speciesList('sc2011speciesSelector');
}

function zoom_to_sc_2011() {
	if (sc2011KmlObject != null) {
		var sc2011view = sc2011KmlObject.getAbstractView();
		ge.getView().setAbstractView(sc2011view);
	}
}

function getSC2011speciesList( pulldownDiv ) {	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			document.getElementById( pulldownDiv ).innerHTML = ajaxRequest.responseText;
			fade("sc2011Panel");
			// turn off activity monitor
			$('#activity_loading').activity(false);
		}
	}
	// construct URL and GET params					
	var url = "php/monitoring/get_sc_2011_species_list.php?select_id=sc_2011_species";	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

function load_sc_2011_data() {
	// show activity monitor
	$('#sc2011_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("sc_2011_blitz");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	// check whether or not to show the area boundaries
	var sc_boundaries = "n";
	if ( $("#sc_2011_boundaries").is(':checked') )
		sc_boundaries = "y";
	
	// check species, habitat, status
	var sc_habitat = $('#sc_2011_habitat').val();
	var sc_status = $('#sc_2011_status').val();
	var sc_species = $('#sc_2011_species').val();
	
	//construct url
	var sc_url = "&species=" + sc_species + "&habitat=" + sc_habitat + "&status=" + sc_status;
	sc_url = "php/monitoring/spring_creek_2011_bird_blitz.php?boundaries=" + sc_boundaries + sc_url;

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (sc2011KmlObject != null) {
				ge.getFeatures().removeChild(sc2011KmlObject);
				sc2011KmlObject.release();
			}
			sc2011KmlObject = null;
			//get the new kmlObject from response
			sc2011KmlObject = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(sc2011KmlObject);
			// turn off activity monitor
			$('#sc2011_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", sc_url, true);
	ajaxRequest.send();
}

/**
 * ---------------------------------------------------------
 *
 * spring creek 2011 invasives blitz monitoring functions
 *
 * ---------------------------------------------------------
 */
 
var sc2011invasivesKmlObject = null; 

function open_sc_2011_invasives_panel() {
	closeAllPanels();
	fade("sc2011invasivesPanel");
}

function zoom_to_sc_2011_invasives() {
	if (sc2011invasivesKmlObject != null) {
		var sc2011view = sc2011invasivesKmlObject.getAbstractView();
		ge.getView().setAbstractView(sc2011view);
	}
}

function load_sc_2011_invasives_data() {
	// show activity monitor
	$('#sc2011_invasives_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("sc_2011_invasives_blitz");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	var sc_invasives_species = $('#sc_2011_invasive_species').val();
	
	//construct url
	var sc_url = "php/monitoring/spring_creek_2011_invasives_blitz.php?species=" + sc_invasives_species;

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (sc2011invasivesKmlObject != null) {
				ge.getFeatures().removeChild(sc2011invasivesKmlObject);
				sc2011invasivesKmlObject.release();
			}
			sc2011invasivesKmlObject = null;
			//get the new kmlObject from response
			sc2011invasivesKmlObject = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(sc2011invasivesKmlObject);
			// turn off activity monitor
			$('#sc2011_invasives_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", sc_url, true);
	ajaxRequest.send();
}



/**
 * ---------------------------------------------------------
 *
 * BCN Point Count data functions
 *
 * ---------------------------------------------------------
 */
 
var bcn_point_count_kml_object = null; 

function open_bcn_point_count_panel() {
	// show activity monitor
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	//fade("bcnPointCountPanel");
	load_bcn_point_count_pulldowns();
}

function load_bcn_point_count_pulldowns() {	
	
	// load year range options
	var d=new Date();
	var year = d.getFullYear();
	var year_options = "<option value='all'>All</option>";
	while (year >= 1932) {
		year_options = year_options + "<option>" + year + "</option>";
		year--;
	}
	year_options = year_options + "</select>";
	document.getElementById('bcn_point_count_begin_year_selector').innerHTML = "<select id='bcn_pc_begin_year'>" + year_options;
	document.getElementById('bcn_point_count_end_year_selector').innerHTML = "<select id='bcn_pc_end_year'>" + year_options;
	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			document.getElementById('bcn_point_count_species_selector').innerHTML = ajaxRequest.responseText;
			fade("bcnPointCountPanel");
			// turn off activity monitor
			$('#activity_loading').activity(false);
		}
	}
	// construct URL and GET params					
	var url = "php/monitoring/get_sc_2011_species_list.php?select_id=bcn_pc_species";	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

function load_bcn_point_count_data() {
	// show activity monitor
	$('#bcn_point_count_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("bcn_point_count");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	
	// check species, habitat, status
	var bcn_pc_habitat = $('#bcn_pc_habitat').val();
	var bcn_pc_status = $('#bcn_pc_status').val();
	var bcn_pc_species = $('#bcn_pc_species').val();
	
	// get date ranger
	var bcn_pc_begin_year = $('#bcn_pc_begin_year').val();
	var bcn_pc_begin_month = $('#bcn_pc_begin_month').val();
	var bcn_pc_end_year = $('#bcn_pc_end_year').val();
	var bcn_pc_end_month = $('#bcn_pc_end_month').val();
	
	//construct url
	var parameters = "&species=" + bcn_pc_species + "&habitat=" + bcn_pc_habitat + "&status=" + bcn_pc_status;
	parameters = parameters + "&year_begin=" + bcn_pc_begin_year + "&month_begin=" + bcn_pc_begin_month;
	parameters = parameters + "&year_end=" + bcn_pc_end_year + "&month_end=" + bcn_pc_end_month;
	var bbox = ge.getView().getViewportGlobeBounds();
	var coordinates = "west="+bbox.getWest()+"&east="+bbox.getEast()+"&south="+bbox.getSouth()+"&north="+bbox.getNorth();
	the_url = "php/monitoring/bcn_point_count.php?" + coordinates + parameters;
	// if we are using a polygon to select an area, we need to send the polygon coordinates
	if (bcn_pc_polygon_selected) {
		if (bcn_pc_polygon == null) {
			alert("Please click on the 'Draw new polygon' button, or choose to load data in the entire map area.");
			$('#bcn_point_count_loading').activity(false);
			return;
		}
		var theKml = bcn_pc_polygon.targetShape.getKml();
		var begin = theKml.indexOf("<coordinates>") + 13;
		var end = theKml.indexOf("</coordinates>");
		var coordinates = $.trim(theKml.slice(begin,end));
		// check if the user has drawn a shape
		if (coordinates.length == 0) {
			alert("Please click on the map to draw a polygon, or choose to load data in the entire map area.");
			$('#bcn_point_count_loading').activity(false);
			return;
		} else
			the_url = the_url + "&coordinates=" + coordinates;
	} else
		the_url = the_url + "&coordinates="
		
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (bcn_point_count_kml_object != null) {
				ge.getFeatures().removeChild(bcn_point_count_kml_object);
				bcn_point_count_kml_object.release();
			}
			bcn_point_count_kml_object = null;
			//get the new kmlObject from response
			bcn_point_count_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(bcn_point_count_kml_object);
			// remember if this data was from a polygon or the whole screen
			if (bcn_pc_polygon_selected) {
				bcn_pc_last_results_from_polygon = true;
				// if necessary, remove the selection polygon from the map
				if (bcn_pc_polygon != null) {
					bcn_pc_polygon.clear();
					bcn_pc_polygon = null;
				}
			} else 
				bcn_pc_last_results_from_polygon = false;
			// turn off activity monitor
			$('#bcn_point_count_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", the_url, true);
	ajaxRequest.send();
}

var bcn_pc_polygon_selected = false;
var bcn_pc_last_results_from_polygon = false;
var bcn_pc_polygon = null;

function bcn_pc_screen_view() {
	document.getElementById('bcn_pc_draw_button').disabled = true;
	bcn_pc_polygon_selected = false;
	if (!bcn_pc_last_results_from_polygon && bcn_point_count_kml_object != null) 
		ge.getFeatures().appendChild(bcn_point_count_kml_object);
	if (bcn_pc_last_results_from_polygon && bcn_point_count_kml_object != null) 
		ge.getFeatures().removeChild(bcn_point_count_kml_object);
		
	if (bcn_pc_polygon != null) {
		bcn_pc_polygon.clear();
		bcn_pc_polygon = null;
	}
}

function bcn_pc_polygon_view() {
	document.getElementById('bcn_pc_draw_button').disabled = false;
	bcn_pc_polygon_selected = true;
	if (bcn_pc_last_results_from_polygon && bcn_point_count_kml_object != null) 
		ge.getFeatures().appendChild(bcn_point_count_kml_object);
	if (!bcn_pc_last_results_from_polygon && bcn_point_count_kml_object != null) 
		ge.getFeatures().removeChild(bcn_point_count_kml_object);
	bcn_pc_polygon = new MapShape(ge, gex, "AA14B4FF");
	bcn_pc_polygon.draw();
}

function bcn_pc_draw_new() {
	if (bcn_pc_polygon != null) {
		bcn_pc_polygon.clear();
		bcn_pc_polygon = null;
	}
	bcn_pc_polygon = new MapShape(ge, gex, "AA14B4FF");
	bcn_pc_polygon.draw();
}



/**
 * ---------------------------------------------------------
 *
 * Calling Frog Survey data functions
 *
 * ---------------------------------------------------------
 */
 
var frog_kml_object = null; 

function open_frog_panel() {
	// show activity monitor
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	//fade("bcnPointCountPanel");
	load_frog_pulldowns();
}

function load_frog_pulldowns() {	
	
	// load year range options
	var d=new Date();
	var year = d.getFullYear();
	var year_options = "<option value='All'>All</option>";
	while (year >= 1999) {
		year_options = year_options + "<option>" + year + "</option>";
		year--;
	}
	year_options = year_options + "</select>";
	document.getElementById('frog_begin_year_selector').innerHTML = "<select id='frog_begin_year'>" + year_options;
	document.getElementById('frog_end_year_selector').innerHTML = "<select id='frog_end_year'>" + year_options;
	
	fade("frogPanel");
	// turn off activity monitor
	$('#activity_loading').activity(false);
}

function load_frog_data() {
	// show activity monitor
	$('#frog_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("frog_survey");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	
	// check species
	var frog_species = $('#frog_species').val();
	
	// get date ranger
	var frog_begin_year = $('#frog_begin_year').val();
	var frog_end_year = $('#frog_end_year').val();
	
	//construct url
	var parameters = "&species=" + frog_species;
	parameters = parameters + "&year_begin=" + frog_begin_year + "&year_end=" + frog_end_year;
	var bbox = ge.getView().getViewportGlobeBounds();
	var coordinates = "west="+bbox.getWest()+"&east="+bbox.getEast()+"&south="+bbox.getSouth()+"&north="+bbox.getNorth();
	the_url = "php/monitoring/frog_survey.php?" + coordinates + parameters;
	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (frog_kml_object != null) {
				ge.getFeatures().removeChild(frog_kml_object);
				frog_kml_object.release();
			}
			frog_kml_object = null;
			//get the new kmlObject from response
			frog_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(frog_kml_object);
			// turn off activity monitor
			$('#frog_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", the_url, true);
	ajaxRequest.send();
}

/**
 * ---------------------------------------------------------
 *
 * cook county land audit 2007-2008 functions
 *
 * ---------------------------------------------------------
 */
 
var land_audit_kml_object = null; 
var land_audit_2001_kml_object = null; 

function open_land_audit_panel() {
	closeAllPanels();
	fade("landAuditPanel");
	load_land_audit_data();
}

function open_land_audit_2001_panel() {
	closeAllPanels();
	fade("landAudit2001Panel");
	load_land_audit_2001_data();
}

function load_land_audit_data() {
	// show activity monitor
	$('#land_audit_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("land_audit_2007");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	//construct url
	var la_url = "php/monitoring/land_audit_2007.php";

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (land_audit_kml_object != null) {
				ge.getFeatures().removeChild(land_audit_kml_object);
				land_audit_kml_object.release();
			}
			land_audit_kml_object = null;
			//get the new kmlObject from response
			land_audit_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(land_audit_kml_object);
			// turn off activity monitor
			$('#land_audit_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();
}

function load_land_audit_2001_data() {
	// show activity monitor
	$('#land_audit_2001_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("land_audit_2001");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	//construct url
	var la_url = "php/monitoring/land_audit_2001.php";

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (land_audit_2001_kml_object != null) {
				ge.getFeatures().removeChild(land_audit_2001_kml_object);
				land_audit_2001_kml_object.release();
			}
			land_audit_2001_kml_object = null;
			//get the new kmlObject from response
			land_audit_2001_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(land_audit_2001_kml_object);
			// turn off activity monitor
			$('#land_audit_2001_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();
}


/**
 * ---------------------------------------------------------
 *
 * CCFPD Natural Communities functions
 *
 * ---------------------------------------------------------
 */
 
var nat_comm_kml_object = null; 
var nat_comm_show_opacity_div = false;

function open_nat_comm_panel() {
	closeAllPanels();
	fade("natural_communties_panel");	
	if (nat_comm_show_opacity_div)
		document.getElementById("nat_comm_opacity_div").style.visibility = "visible";
	else {
		document.getElementById("nat_comm_opacity_div").style.visibility = "hidden";
	}		
}

function nat_comm_show_div() {
	old_nat_comm_show_opacity_div = nat_comm_show_opacity_div;
	if ($('input:radio[name=nat_comm_how]:checked').val() == "outline") {
		nat_comm_show_opacity_div = false;
	}
	if ($('input:radio[name=nat_comm_how]:checked').val() == "now") {
		nat_comm_show_opacity_div = true;
	}
	if ($('input:radio[name=nat_comm_how]:checked').val() == "future") {
		nat_comm_show_opacity_div = true;
	}
	if (old_nat_comm_show_opacity_div != nat_comm_show_opacity_div)
		fade("nat_comm_opacity_div");	
}

function load_nat_comm_data() {
	// show activity monitor
	$('#nat_comm_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("fpdcc_nat_comm");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
	
	// get viewing options
	var nat_comm_how = $('input:radio[name=nat_comm_how]:checked').val();
	if (nat_comm_how == "outline")
		var nat_comm_opacity = 0;
	else
		var nat_comm_opacity = $('input:radio[name=nat_comm_opacity]:checked').val();
	
	//construct url
	parameters = "nat_comm_how=" + nat_comm_how + "&nat_comm_opacity=" + nat_comm_opacity;
	var la_url = "php/monitoring/natural_communities.php?" + parameters;

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (nat_comm_kml_object != null) {
				ge.getFeatures().removeChild(nat_comm_kml_object);
				nat_comm_kml_object.release();
			}
			nat_comm_kml_object = null;
			//get the new kmlObject from response
			nat_comm_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(nat_comm_kml_object);
			// turn off activity monitor
			$('#nat_comm_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();
}

/**
 * ---------------------------------------------------------
 *
 * CCFPD Management Units functions
 *
 * ---------------------------------------------------------
 */

function load_management_units() {
	// show activity monitor
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// toggle the node on if it is off
	var theNode = tree.getNodesById("fpdcc_management_units");
	if (!tree.lookup(theNode).getVisibility())
		tree.toggleItem(theNode, true);	
		
	//construct url
	var la_url = "php/monitoring/management_units.php";

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (management_units_kml_object != null) {
				ge.getFeatures().removeChild(management_units_kml_object);
				management_units_kml_object.release();
			}
			management_units_kml_object = null;
			//get the new kmlObject from response
			management_units_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(management_units_kml_object);
			// turn off activity monitor
			$('#activity_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();
}

/**
 * ---------------------------------------------------------
 *
 * Weed Scouts monitoring functions
 *
 * ---------------------------------------------------------
 */
 
var ws_kml_object = null; 

function open_ws_panel() {
	closeAllPanels();
	fade("ws_display_data_panel");
}

function zoom_to_ws_data() {
	if (ws_kml_object != null) {
		var bounds = gex.dom.computeBounds(ws_kml_object);
		gex.view.setToBoundsView(bounds, { aspectRatio: 1.0 });
	}
}

function load_ws_data() {
	// show activity monitor
	$('#ws_display_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// clear any error message
	document.getElementById('ws_display_error').value = ""; 
	// get date from form
	var wsBeginMonth = document.getElementById("ws_begin_month").value.trim();
	var wsBeginDay = document.getElementById("ws_begin_day").value.trim();
	var wsBeginYear = document.getElementById("ws_begin_year").value.trim();
	var wsEndMonth = document.getElementById("ws_end_month").value.trim();
	var wsEndDay = document.getElementById("ws_end_day").value.trim();
	var wsEndYear = document.getElementById("ws_end_year").value.trim();
	// if any dates are filled in we need to construct the date range
	if (wsBeginYear != '' || wsEndYear != '' || wsBeginMonth != '' || wsEndMonth != '' || wsBeginDay != '' || wsEndDay != '') {
		// check that the date is valid, display error if not
		if (!validDate(wsBeginMonth, wsBeginDay, wsBeginYear) || !validDate(wsEndMonth, wsEndDay, wsEndYear)) {
			document.getElementById('ws_display_error').value = "Invalid date. Please correct."; 
			$('#ws_display_loading').activity(false);
			return false;
		}
		// make single digit month and date into double digit
		wsBeginMonth = pad2(parseInt(wsBeginMonth));
		wsBeginDay = pad2(parseInt(wsBeginDay));
		wsEndMonth = pad2(parseInt(wsEndMonth));
		wsEndDay = pad2(parseInt(wsEndDay));
		// construct string
		var startDateRange = wsBeginYear + '-' + wsBeginMonth + '-' + wsBeginDay;
		var endDateRange = wsEndYear + '-' + wsEndMonth + '-' + wsEndDay;
	} else {
		var startDateRange = 'all';
		var endDateRange = 'all';
	}
	// get weed and abundance
	var weedToDisplay = document.getElementById("ws_weed_display").value;
	var abundance = document.getElementById("ws_abundance_display").value;
	
	//construct url
	parameters = "startDate=" + startDateRange + "&endDate=" + endDateRange + "&weed=" + weedToDisplay + "&abundance=" + abundance;
	var la_url = "php/monitoring/weed_scouts_display.php?" + parameters;

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// remove the old kmlObject
			if (ws_kml_object != null) {
				ge.getFeatures().removeChild(ws_kml_object);
				ws_kml_object.release();
			}
			ws_kml_object = null;
			//get the new kmlObject from response
			ws_kml_object = ge.parseKml(ajaxRequest.responseText);
			// add the new kmlObject to globe
			ge.getFeatures().appendChild(ws_kml_object);
			
			// turn off activity monitor
			$('#ws_display_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();
}

function download_ws_data_kml() {
	if (ws_kml_object == null)
		document.getElementById('ws_display_error').value = "No data is loaded in the map."; 
	else {
		document.getElementById('ws_display_error').value = ""; 
		document.getElementById('downloadString').value = ws_kml_object.getKml();
		document.getElementById('downloadKmlForm').submit();
	}
}

function download_ws_data_csv() {
		// show activity monitor
	$('#ws_display_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// clear any error message
	document.getElementById('ws_display_error').value = ""; 
	// get date from form
	var wsBeginMonth = document.getElementById("ws_begin_month").value.trim();
	var wsBeginDay = document.getElementById("ws_begin_day").value.trim();
	var wsBeginYear = document.getElementById("ws_begin_year").value.trim();
	var wsEndMonth = document.getElementById("ws_end_month").value.trim();
	var wsEndDay = document.getElementById("ws_end_day").value.trim();
	var wsEndYear = document.getElementById("ws_end_year").value.trim();
	// if any dates are filled in we need to construct the date range
	if (wsBeginYear != '' || wsEndYear != '' || wsBeginMonth != '' || wsEndMonth != '' || wsBeginDay != '' || wsEndDay != '') {
		// check that the date is valid, display error if not
		if (!validDate(wsBeginMonth, wsBeginDay, wsBeginYear) || !validDate(wsEndMonth, wsEndDay, wsEndYear)) {
			document.getElementById('ws_display_error').value = "Invalid date. Please correct."; 
			$('#ws_display_loading').activity(false);
			return false;
		}
		// make single digit month and date into double digit
		wsBeginMonth = pad2(parseInt(wsBeginMonth));
		wsBeginDay = pad2(parseInt(wsBeginDay));
		wsEndMonth = pad2(parseInt(wsEndMonth));
		wsEndDay = pad2(parseInt(wsEndDay));
		// construct string
		var startDateRange = wsBeginYear + '-' + wsBeginMonth + '-' + wsBeginDay;
		var endDateRange = wsEndYear + '-' + wsEndMonth + '-' + wsEndDay;
	} else {
		var startDateRange = 'all';
		var endDateRange = 'all';
	}
	// get weed and abundance
	var weedToDisplay = document.getElementById("ws_weed_display").value;
	var abundance = document.getElementById("ws_abundance_display").value;
	
	//construct url
	parameters = "startDate=" + startDateRange + "&endDate=" + endDateRange + "&weed=" + weedToDisplay + "&abundance=" + abundance;
	var la_url = "php/monitoring/weed_scouts_download.php?" + parameters;

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {		
			
			document.getElementById('downloadCsv').value = ajaxRequest.responseText;
			document.getElementById('downloadCsvForm').submit();			
			// turn off activity monitor
			$('#ws_display_loading').activity(false);
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();
}

function show_ws_data_entry_panel() {
	closeAllPanels();
	fade("ws_data_entry_panel");
}

function cancel_ws_data() {
	closeAllPanels();
	document.getElementById('ws_error').value = ""; 
	document.getElementById('ws_latitude').value = ""; 
	document.getElementById('ws_longitude').value = ""; 
	document.getElementById('ws_notes').value = "";
	if (temp_ws_placemark != null) {
		ge.getElementById('ws_temp_kml').setVisibility(false);
		ge.getFeatures().removeChild(ge.getElementById('ws_temp_kml'));
		temp_ws_placemark.release();
		temp_ws_placemark = null;
	}
}

function update_ws_latitude(new_coordinate) {
	if (temp_ws_placemark == null)
		create_temp_ws_placemark();
	temp_ws_placemark.getGeometry().setLatitude(parseFloat(new_coordinate));
	var lookAt = ge.createLookAt('');
	lookAt.setLatitude(temp_ws_placemark.getGeometry().getLatitude());
	lookAt.setLongitude(temp_ws_placemark.getGeometry().getLongitude());
	lookAt.setRange(5000.0); 
	ge.getView().setAbstractView(lookAt); 
}

function update_ws_longitude(new_coordinate) {
	if (new_coordinate > 0)
		new_coordinate = new_coordinate * -1;
	if (temp_ws_placemark == null)
		create_temp_ws_placemark();
	temp_ws_placemark.getGeometry().setLongitude(parseFloat(new_coordinate));
	var lookAt = ge.createLookAt('');
	lookAt.setLatitude(temp_ws_placemark.getGeometry().getLatitude());
	lookAt.setLongitude(temp_ws_placemark.getGeometry().getLongitude());
	lookAt.setRange(1000.0); 
	ge.getView().setAbstractView(lookAt);
}

var temp_ws_placemark = null;
function create_temp_ws_placemark() {
	// Create the placemark.
	temp_ws_placemark = ge.createPlacemark('ws_temp_kml');
	temp_ws_placemark.setName("New weed sighting");
	
	// Define a custom icon.
	var icon = ge.createIcon('');
	icon.setHref('http://maps.google.com/mapfiles/kml/paddle/red-circle.png');
	var style = ge.createStyle('');
	style.getIconStyle().setIcon(icon);
	style.getIconStyle().setScale(0.8);
	temp_ws_placemark.setStyleSelector(style);
	
	// Set the placemark's location.  
	var point = ge.createPoint('');
	point.setLatitude(41.89001);
	point.setLongitude(-87.6297);
	temp_ws_placemark.setGeometry(point);
	
	// Add the placemark to Earth.
	ge.getFeatures().appendChild(temp_ws_placemark);
}

function save_ws_data() {
		// show activity monitor
	$('#ws_loading').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	// clear any error message
	document.getElementById('ws_error').value = ""; 
	// get date from form
	var wsMonth = document.getElementById("ws_month").value.trim();
	var wsDay = document.getElementById("ws_day").value.trim();
	var wsYear = document.getElementById("ws_year").value.trim();
	if (wsMonth == '' || wsDay == '' || wsYear == '') {
		document.getElementById('ws_error').value = "Please enter a full date."; 
		$('#ws_loading').activity(false);
		return false;
	}
	// check that the date is valid, display error if not
	if (!validDate(wsMonth, wsDay, wsYear)) {
		document.getElementById('ws_error').value = "Invalid date. Please correct."; 
		$('#ws_loading').activity(false);
		return false;
	}
	// make single digit month and date into double digit
	wsMonth = pad2(parseInt(wsMonth));
	wsDay = pad2(parseInt(wsDay));

	// construct string
	var wsDate = wsYear + '-' + wsMonth + '-' + wsDay;

	// get weed and abundance
	var weed = document.getElementById("ws_weed").value;
	var abundance = document.getElementById("ws_abundance").value;
	if (weed == "0") {
		document.getElementById('ws_error').value = "Please select a weed."; 
		$('#ws_loading').activity(false);
		return false;
	}
	if (abundance == "0") {
		document.getElementById('ws_error').value = "Please select the abundance level."; 
		$('#ws_loading').activity(false);
		return false;
	}
	
	// lat and long
	var latitude = document.getElementById("ws_latitude").value;
	var longitude = document.getElementById("ws_longitude").value;
	// make sure latitude is between 41.0 and 43.0
	if (41.0 > latitude || latitude > 43.0) {
		document.getElementById('ws_error').value = "Enter a latitude in the Chicago area."; 
		$('#ws_loading').activity(false);
		return false;
	}
	// fix long 88.0 to -88.0
	if (longitude > 0)
		longitude = longitude * -1;
	// make sure longitude is between -86.0 and -89.0
	if (-89.0 > longitude || longitude > -86.0) {
		document.getElementById('ws_error').value = "Enter a longitude in the Chicago area."; 
		$('#ws_loading').activity(false);
		return false;
	}
	
	//construct url
	parameters = "date=" + wsDate + "&weed=" + weed + "&abundance=" + abundance + "&longitude=" + longitude + "&latitude=" + latitude;
	parameters = parameters + "&note=" + document.getElementById("ws_notes").value + "&name=" + document.getElementById("ws_name").value;
	var la_url = "php/monitoring/weed_scouts_save.php?" + parameters;

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			if (ajaxRequest.responseText == "success") {
				ge.getElementById('ws_temp_kml').setVisibility(false);
				ge.getFeatures().removeChild(ge.getElementById('ws_temp_kml'));
				temp_ws_placemark.release();
				temp_ws_placemark = null;
				document.getElementById('ws_error').value = "Last weed sighting successfully saved."; 
				document.getElementById('ws_latitude').value = ""; 
				document.getElementById('ws_longitude').value = ""; 
				document.getElementById('ws_notes').value = ""; 
				// turn off activity monitor
				$('#ws_loading').activity(false);
				return true;
			} else {
				document.getElementById('ws_error').value = "Error saving weed sighting.";
				return false
			}
		}
	}
	// send the new request		
	ajaxRequest.open("GET", la_url, true);
	ajaxRequest.send();	
}