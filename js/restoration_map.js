/**
 * ---------------------------------------------------------
 * constants
 *
 * ---------------------------------------------------------
 */
google.load("earth", "1");
var ge = null;
var gex = null;
var mapShape = null;
var mapLine = null;
var measureTool = null;
var tree = null;
var selected = null;
var lastSelected = null;
var TimeToFade = 300.0;
var panelList = ['savePanel', 'editPanel', 'trailPanel', 'measurePanel', 'borderPanel', 'loginPanel', 'userPanel', 'aboutPanel', 'landmarkPanel', 'downloadPanel' ];

/**
 * ---------------------------------------------------------
 * initialize functions
 *
 * ---------------------------------------------------------
 */
 
$(document).ready(function() {
	checkBrowser();
	google.earth.createInstance('map3d', initCB, failureCB);
	$('#nav_menu').dropmenu();
});

function initCB(instance) {
	ge = instance;
	ge.getWindow().setVisibility(true);
	// show navigation controls 
	ge.getNavigationControl().setVisibility(ge.VISIBILITY_AUTO);
	gex = new GEarthExtensions(ge);
	// check plugin version
	if ( parseFloat(ge.getApiVersion()) < 1.005)
	{
		alert("Restoration Map requires a newer version of the Google Earth Plugin. Please upgrade and try again.");
		return;
	}
	// check whether user is already logged in
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	var url = "php/check_login.php";
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			$('#activity_loading').activity(false);
			var response = ajaxRequest.responseText;
			if (response == "not logged in") {
				// load tree with no user logged in
				loadKmlTree(-1);
			} else 
				// load tree with user id
				loadKmlTree(response);
		}
	}
	// send the new request 			
	ajaxRequest.open("POST", url, true);
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send('');

	// upon load make buttons disabled
	document.getElementById('deleteButton').disabled = true;
	document.getElementById('downloadButton').disabled = true; 
	document.getElementById('editButton').disabled = true; 
	// set login/logout and admin button
	loggedIn('setMenuOnLoad(true)', 'setMenuOnLoad(false)');
	
	// Create a new LookAt
	var lookAt = ge.createLookAt('');
	
	// Set the position values
	lookAt.setLatitude(42.10);
	lookAt.setLongitude(-88.00);
	lookAt.setRange(200000.0); //default is 0.0
	
	// Update the view in Google Earth
	ge.getView().setAbstractView(lookAt);
}

function loadKmlTree(user_id) {
	if (tree) {
		// reload existing tree with new url
		var newUrl = 'php/tree.php?user_id=' + user_id;
		tree.reload(newUrl);
	} else {
		// build a new kml tree
		tree = kmltree({
			url: 'php/tree.php?user_id=' + user_id,
			bustCache: true,
			gex: gex,
			element: $('#tree'),
			mapElement: $('#map3d'),
			setExtent: true,
			selectable: function(kmlObject){
				return kmlObject.getType() === 'KmlPlacemark';
			}
		});
		tree.load();
		enableGoogleLayersControl(tree, ge);
		// make shapes selectable 
		$(tree).bind('select', function(event, selectData){
			if (selectData[0]) {
				selected = selectData[0].kmlObject;
				document.getElementById('deleteButton').disabled = false;  
				document.getElementById('downloadButton').disabled = false; 
				document.getElementById('editButton').disabled = false;  
			} else {
				document.getElementById('deleteButton').disabled = true;
				document.getElementById('downloadButton').disabled = true; 
				document.getElementById('editButton').disabled = true;
			}
		}); 
		initMonitoringLayers();
	}
}

// upon load, sets login/logout and admin menus depending on whether user has valid session
function setMenuOnLoad( logged_in ) {
	if ( logged_in ) {
		document.getElementById("loginMenu").innerHTML = "Logout";
		if ( isAdmin() )
			document.getElementById("adminMenu").style.visibility = "visible";
	} else {
		document.getElementById("loginMenu").innerHTML = "Login";
		document.getElementById("adminMenu").style.visibility = "hidden";
	}		    
}

function failureCB(errorCode) {
	// alert('Failed to load Google Earth plugin. Please try again.');
}

/**
 * ---------------------------------------------------------
 * utility functions
 *
 * ---------------------------------------------------------
 */
 
 function checkBrowser() {
	var check = false;
	// check if internet explore, must be above 8.0
	if ($.browser.msie) {
		var ver = getInternetExplorerVersion();
		if (ver = -1 || ver > 8.0)
			check = true;
	}
	// if safari/chrome good
	if ($.browser.webkit) {
	   check = true;
	}
	// if unsupported, hide divs and show message
	if (check == false) {
		document.getElementById('map3d').style.visibility = 'hidden';
		document.getElementById('tree').style.visibility = 'hidden';
		document.getElementById('treeborder').style.visibility = 'hidden';
		document.getElementById('nav_menu').style.visibility = 'hidden';
		document.getElementById('buttonpanel').style.visibility = 'hidden';
		document.getElementById('unsupported_browser').style.visibility = 'visible';
	}
}

function getInternetExplorerVersion() {
    var rv = -1; // Return value assumes failure.
    if (navigator.appName == 'Microsoft Internet Explorer') {
        var ua = navigator.userAgent;
        var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
        if (re.exec(ua) != null)
            rv = parseFloat(RegExp.$1);
    }
    return rv;
}
 
function refreshMap() {
	tree.refresh();
}

function resize() {  

	var windowHeight = $(window).height();  
	var windowWidth = $(window).width();
	
	// var windowHeight = document.documentElement.clientHeight;
	// var windowWidth = document.documentElement.clientWidth;

	$('#treeborder').height(windowHeight - 36 - 36);
	$('#treeborder').width(10);

	$('#tree').height(windowHeight - 36 - 36);
	$('#tree').width(290);
	
	$('#map3d').height(windowHeight - 36);
	$('#map3d').width(windowWidth - 300);
	
	var i = 0;
	while (i < panelList.length)
	{
		$('#'+panelList[i]).height(windowHeight - 36);
		i++;
	}
	resizeMonitorPanels(windowHeight);
} 

function closeAllPanels() {
	var i = 0;
	closeMonitoringPanels();
	while (i < panelList.length)
	{
		if ( document.getElementById( panelList[i] ).style.visibility == "visible")
		{
			switch( panelList[i] )
			{
				case 'savePanel':
					if (sl_show_new_shape_div) {
						fade("sl_new_shape_div");	
						fade("sl_authorized_users_list");
						fade("sl_new_shape_user_selector");
						sl_show_new_shape_div = false;
					}
					cancelNewShape();
					break;
				case 'editPanel':
					if (sl_show_edited_shape_div) {
						fade("sl_edited_shape_div");	
						fade("sl_edited_shape_authorized_users_list");
						fade("sl_edited_shape_user_selector");
						sl_show_edited_shape_div = false;
					}	
					cancelEditShape();
					break;
				case 'trailPanel':
					cancelTrail();
					break;
				case 'measurePanel':
					doneMeasuring();
					break;
				case 'landmarkPanel':
					if (sl_show_new_landmark_div) {
						fade("sl_new_landmark_div");
						fade("sl_landmark_authorized_users_list");
						fade("sl_new_landmark_user_selector");
						sl_show_new_landmark_div = false;
					}
					cancelLandmark();
					break;
				default:
					fade(panelList[i]);
					break;
			}
		}
		i++;
	}
}

function showAboutPanel() {
	closeAllPanels();
	fade('aboutPanel');
}

function validDate(month, day, year) {
	if (day > 0 && month == 0)
		return false;
	if (0 <= month && month < 13)
		if (0 <= day && day < 32)
			if (999 < year && year < 9000)
				return true;
	return false;
}

function pad2(number) {
     return (number < 10 ? '0' : '') + number;
}

String.prototype.trim = function () {
    return this.replace(/^\s*/, "").replace(/\s*$/, "");
}

// accepts the div to insert the pulldown menu, and a string that can reference a callback function
function getStewardshipSites(pulldownDiv, callback) {	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			document.getElementById( pulldownDiv ).innerHTML = ajaxRequest.responseText;
			eval(callback);
		}
	}
	// construct URL				
	var url = "php/get_stewardship_sites.php";	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

function validSteward( siteId ) {	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();	
	// construct URL and GET params
	var params = "?site_id=" + siteId;						
	var url = "php/check_valid_steward.php" + params;	
	// send the new request		
	ajaxRequest.open("GET", url, false);
	ajaxRequest.send(null);
	if ( ajaxRequest.responseText == 'success' )
		return true;
	else
		return false;
}

function validAssistant( table, shapeId ) {	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();	
	// construct URL and GET params
	var params = "?table=" + table + "&shape_id=" + shapeId;						
	var url = "php/check_valid_assistant.php" + params;
	// send the new request		
	ajaxRequest.open("GET", url, false);
	ajaxRequest.send(null);
	if ( ajaxRequest.responseText == 'success' )
		return true;
	else
		return false;
}

/**
 * ---------------------------------------------------------
 * color converter functions
 * The order of the channels in KML is Alpha-Blue-Green-Red (ABGR) 
 * as opposed to the standard Alpha-Red-Green-Blue (ARGB).
 *
 * ---------------------------------------------------------
 */
 
function kmlColorToHtml(kmlColor) {
	var b = kmlColor.slice(2, 4);
	var g = kmlColor.slice(4, 6);
	var r = kmlColor.slice(6, 8);
	return r + g + b;
}

function htmlColorToKml(htmlColor) {
	var r = htmlColor.slice(0, 2);
	var g = htmlColor.slice(2, 4);
	var b = htmlColor.slice(4, 6);
	return "FF" + b + g + r; 
}

/**
 * ---------------------------------------------------------
 * "selected" functions - editing and deleting selected lines and shapes
 *
 * ---------------------------------------------------------
 */
 
function editSelected() {
	// show activity monitor and close any open panels
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	// first get the selected shapes database table and site id
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);
	var dbId = theId.slice(dash + 1, theId.indexOf("site"));
	// check to see if this user is allowed to edit this site
	if ( !validSteward(site_id) ) {
		if ( !validAssistant(table, dbId) ) {
			alert("Sorry you must be site steward to edit this map shape.");
			$('#activity_loading').activity(false);	
			return;	
		}
	}
	// we are editing a trail
	if (table == 'trails')
		getStewardshipSites('trailSiteSelector', 'finishEditSelectedTrail()');
	
	// we are editing a geographic feature and landmark	
	else if (table == 'landmark') {
		getUsersList('sl_new_landmark_user_selector');
		setupAuthorizedUsersListForEditedLandmark('sl_landmark_authorized_users_list', 'sl_new_landmark', 'sl_new_landmark_div');
		getStewardshipSites('landmarkSiteSelector', 'finishEditSelectedLandmark()');
	}
	// we are editing a border	
	else if (table == 'border') {
		// we are editing a border
		fade("borderPanel");
		mapShape = new MapShape(ge, gex, null);
		mapShape.targetShape = selected;
		mapShape.table = table;
		mapShape.site = site_id;
		selected.setVisibility(false);
		mapShape.edit();
	
	//we are editing a shape
	} else { 
		getStewardshipSites('editShapeSiteSelector', 'finishEditSelectedShape()');
		setupAuthorizedUsersListForEditedShape('sl_edited_shape_authorized_users_list', 'sl_edited_shape', 'sl_edited_shape_div');
		getUsersList('sl_edited_shape_user_selector');
	}
}		

function finishEditSelectedShape() {	
	// get site_id from selected shape
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);
	// turn color picker off in case its not an 'other' shape
	document.getElementById("editColorLabel").innerHTML = "";
	document.getElementById("editShapeColor").style.visibility = "hidden";
	// create copy the selected shape to create a new mapShape 
	mapShape = new MapShape(ge, gex, null);
	mapShape.targetShape = selected;
	mapShape.table = table;
	selected.setVisibility(false);
	mapShape.edit();
	// if shape is 'other' need to set the color in the color picker 
	// check for 'other'
	if (mapShape.table == 'other') {
		document. getElementById("editColorLabel").innerHTML = "Color:";
		// get the color of the selected shape and set it in the mapShape object
		if ( mapShape.targetShape.getGeometry().getType() == 'KmlPoint' )
			mapShape.color = mapShape.targetShape.getStyleSelector().getIconStyle().getColor().get() + '';
		else
			mapShape.color = mapShape.targetShape.getStyleSelector().getLineStyle().getColor().get() + '';
		// convert the mapShape color from kml color to html color
		var htmlColor = kmlColorToHtml(mapShape.color);
		// set the color in the color picker box
		document.getElementById('editShapeColor').color.fromString(htmlColor);
		document.getElementById("editShapeColor").style.visibility = "visible";
	}
	// get shapes date and title. possible formats are:
	// 2008 title
	// 12/2008 title
	// 12/31/2008 title
	var shapeMonth = '';
	var shapeDay = '';
	var shapeYear = '';
	var shapeTitle = '';
	var shapeName = selected.getName();
	var slash1 = shapeName.indexOf("/");
	if (slash1 != -1) {
		shapeMonth = shapeName.slice(0, slash1);
		shapeName = shapeName.slice(slash1 + 1);
	}
	var slash2 = shapeName.indexOf("/");
	if (slash2 != -1) {
		shapeDay = shapeName.slice(0, slash2);
		shapeName = shapeName.slice(slash2 + 1);
	}
	// check to see if there is a 'title'
	var space = shapeName.indexOf(" ");
	if (space != -1) {
		shapeYear = shapeName.slice(0, space);
		shapeTitle = shapeName.slice(space + 1);
	} else 
		shapeYear = shapeName;
	
	// populate form
	document.getElementById("editDateDay").value = shapeDay;
	document.getElementById("editDateMonth").value = shapeMonth;
	document.getElementById("editDateYear").value = shapeYear;
	document.getElementById("editTitle").value = shapeTitle;
	document.getElementById("editDescription").value = selected.getDescription();
	document.getElementById('editShapeError').value = "";
	document.getElementById("siteList").value = site_id;
	fade("editPanel");
	$('#activity_loading').activity(false);	
}

function finishEditSelectedTrail() {
	// get site_id from selected shape
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);
	// set form values
	document.getElementById("trailName").value = selected.getName();
	document.getElementById("uploadTrailStuff").style.visibility = "hidden";
	mapLine = new MapLine(ge, gex, "ffffff00");
	mapLine.table = "trails";
	mapLine.targetShape = selected;
	selected.setVisibility(false);
	mapLine.edit();
	document.getElementById("siteList").value = site_id;
	fade("trailPanel");
	$('#activity_loading').activity(false);
}

function finishEditSelectedLandmark() {
	// get site_id from selected shape
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);
	// set form values
	document.getElementById('landmarkError').value = "";
	document.getElementById("landmarkTitle").value = selected.getName();
	document.getElementById("landmarkDescription").value = selected.getDescription();	
	mapShape = new MapShape(ge, gex, null);
	mapShape.table = "landmark";				
	// check if 'selected' is a point or a multigeometry
	if ( selected.getGeometry().getType() == 'KmlPoint' )
		var thePolygon = selected.getGeometry();
	else
		// we have a multigeometry, so get the polygon
		var thePolygon = selected.getGeometry().getGeometries().getLastChild();
	// get the style
	var theStyle = selected.getStyleSelector();
	// create a new placemark
	var thePlace = ge.createPlacemark('');
	// set the geometry and style
	thePlace.setGeometry( thePolygon );
	thePlace.setStyleSelector( theStyle );
	// add new placemark to ge
	ge.getFeatures().appendChild( thePlace );
	// set the new placemark to the mapShape and edit it	
	mapShape.targetShape = thePlace;
	mapShape.setID( selected.getId() );
	selected.setVisibility(false);
	mapShape.edit();
	// get the color of the selected shape and set it in the mapShape object
	if ( mapShape.targetShape.getGeometry().getType() == 'KmlPoint' )
			mapShape.color = mapShape.targetShape.getStyleSelector().getIconStyle().getColor().get() + '';
		else
			mapShape.color = mapShape.targetShape.getStyleSelector().getLineStyle().getColor().get() + '';
	// convert the mapShape color from kml color to html color
	var htmlColor = kmlColorToHtml(mapShape.color);
	// set the color in the color picker box
	document.getElementById('landmarkColor').color.fromString(htmlColor);
	document.getElementById("landmarkColor").style.visibility = "visible";
	// set site
	document.getElementById("siteList").value = site_id;
	fade("landmarkPanel");
	$('#activity_loading').activity(false);
}

function deleteSelected(prompt) {

  	//  get the shapes database table
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	// check to see if it is a border
	if ( table == 'border' ) {
		document.getElementById('deleteButton').disabled = true;
		alert("You cannot delete a stewardship site border.");
		return;
	}
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);	
	var dbId = theId.slice(dash + 1, theId.indexOf("site"));
	// check to see if this user is allowed to edit this site
	if ( !validSteward(site_id) ) {
		if ( !validAssistant(table, dbId) ) {
			alert("Sorry you must be site steward to edit this map shape.");
			return;	
		}
	}
	// check to whether or not to prompt to the user 
  	if (prompt==false) {
     	 var check=confirm("Are you sure you want to delete this layer?");
	 	 if (check==false)
	  		return false;
  	}
	if (table == 'trails')
	{
		// if we are deleting a trail
		mapLine = new MapLine(ge, gex, null);
  		mapLine.targetShape = selected;
  		mapLine.deleteLine();
	} else
	{
		// or we are deleting a shape
  		mapShape = new MapShape(ge, gex, null);
  		mapShape.targetShape = selected;
  		mapShape.deleteShape();
	}
  	// clear all shapes			
	gex.dom.clearFeatures();
	// reload all shapes
	tree.refresh();
}

/**
 * ---------------------------------------------------------
 * download KML or SHP/SHX functions
 *
 * ---------------------------------------------------------
 */
 
function downloadSelected() {
	fade("downloadPanel");
}

function startKmlDownload() {
	fade("downloadPanel"); 
	document.getElementById('downloadString').value = selected.getKml();
	// fade does not work while submitting form, so delay form submission
	setTimeout("document.getElementById('downloadKmlForm').submit()", 300);
}

function startShpDownload() {
	document.getElementById('downloadError').value = "Generating SHP and SHX files...";
	$('#downloading_monitor').activity({segments: 12, align: 'left', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});

	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	var params = "kmlstring=" + selected.getKml();
	var url = "php/generateShapefile.php";	
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			$('#downloading_monitor').activity(false);
			document.getElementById('downloadError').value = "";
			fade("downloadPanel");
			// fade does not work while submitting form, so delay form submission
			setTimeout("endShpDownload()", 300);
		}
	}		
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	// send the new request 
	ajaxRequest.send(params);
}

function endShpDownload() {
	document.getElementById('downloadShpForm').submit();
}

/**
 * ---------------------------------------------------------
 * upload functions
 *
 * ---------------------------------------------------------
 */

function startKmlUpload() {
	document.getElementById('uploadError').value = "Uploading...";
	return true;
}
function stopKmlUpload( msg ){	
	if ( msg.indexOf("Error:") != -1 )
	{
		document.getElementById('uploadError').value = msg;
	} else
	{
		mapShape.endEdit();
		document.getElementById('uploadError').value = "";
		document.getElementById('editUploadedButton').disabled = false;
		// create a new placemark from the kml
		var thePlace = ge.parseKml(msg);
		thePlace.getGeometry().setAltitudeMode(ge.ALTITUDE_CLAMP_TO_GROUND);
		// get the style from selected and set it to the new placemark
		var theStyle = mapShape.targetShape.getStyleSelector();
		thePlace.setStyleSelector( theStyle );
		// clear the old targetShape and set the new placemark to the mapShape
		mapShape.clear();	
		mapShape.targetShape = thePlace;
		// add new placemark to ge
		ge.getFeatures().appendChild( mapShape.targetShape );
	} 
}
function startKmlTrailUpload() {
	document.getElementById('uploadTrailError').value = "Uploading...";
	return true;
}
function stopKmlTrailUpload( msg ){	
	if ( msg.indexOf("Error:") != -1 )
	{
		document.getElementById('uploadTrailError').value = msg;
	} else
	{
		mapLine.endEdit();
		document.getElementById('uploadTrailError').value = "";
		document.getElementById('editUploadedTrailButton').disabled = false;
		// create a new placemark from the kml
		var thePlace = ge.parseKml(msg);
		thePlace.getGeometry().setAltitudeMode(ge.ALTITUDE_CLAMP_TO_GROUND);
		// get the style from selected and set it to the new placemark
		var theStyle = mapLine.targetShape.getStyleSelector();
		thePlace.setStyleSelector( theStyle );
		// clear the old targetShape and set the new placemark to the mapShape
		mapLine.clear();	
		mapLine.targetShape = thePlace;
		// add new placemark to ge
		ge.getFeatures().appendChild( mapLine.targetShape );
	} 
}
function startKmlLandmarkUpload() {
	document.getElementById('uploadLandmarkError').value = "Uploading...";
	return true;
}
function stopKmlLandmarkUpload( msg ){	
	if ( msg.indexOf("Error:") != -1 )
	{
		document.getElementById('uploadLandmarkError').value = msg;
	} else
	{
		mapShape.endEdit();
		document.getElementById('uploadLandmarkError').value = "";
		document.getElementById('editUploadedLandmarkButton').disabled = false;
		// create a new placemark from the kml
		var thePlace = ge.parseKml(msg);
		thePlace.getGeometry().setAltitudeMode(ge.ALTITUDE_CLAMP_TO_GROUND);
		// get the style from selected and set it to the new placemark
		var theStyle = mapShape.targetShape.getStyleSelector();
		thePlace.setStyleSelector( theStyle );
		// clear the old targetShape and set the new placemark to the mapShape
		mapShape.clear();	
		mapShape.targetShape = thePlace;
		// add new placemark to ge
		ge.getFeatures().appendChild( mapShape.targetShape );
	} 
}

function showMultiGeo() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getStewardshipSites('multiGeoSiteSelector', 'finishShowMultiGeo()');
}

function finishShowMultiGeo() {
	clearNewShapeForm();


	mapShape = new MapShape(ge, gex, "FF7800F0");
	mapShape.table = "weed";
	mapShape.draw();
	fade("savePanel");
	$('#activity_loading').activity(false);
}

function start_multigeo_upload() {
	document.getElementById('uploadMultiGeoError').value = "Uploading...";
	return true;
}
function stop_multigeo_upload( msg ){	
	if ( msg.indexOf("Error:") != -1 )
	{
		document.getElementById('uploadMultiGeoError').value = msg;
	} else
	{
		mapLine.endEdit();
		document.getElementById('uploadMultiGeoError').value = "";
		// create a new placemark from the kml
		
		// will need to move through each geo and parseKml one by one
		var thePlace = ge.parseKml(msg);
		thePlace.getGeometry().setAltitudeMode(ge.ALTITUDE_CLAMP_TO_GROUND);
		// get the style from selected and set it to the new placemark
		var theStyle = mapLine.targetShape.getStyleSelector();
		thePlace.setStyleSelector( theStyle );
		// clear the old targetShape and set the new placemark to the mapShape
		mapLine.clear();	
		mapLine.targetShape = thePlace;
		// add new placemark to ge
		ge.getFeatures().appendChild( mapLine.targetShape );
	} 
}


/**
 * ---------------------------------------------------------
 * functions for brush, burn, seed, etc shapes
 *
 * ---------------------------------------------------------
 */
 
function newBrush() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getUsersList('sl_new_shape_user_selector');
	setupAuthorizedUsersList('sl_authorized_users_list');
	getStewardshipSites('newShapeSiteSelector', 'finishNewBrush()');
}

function finishNewBrush() {	
	clearNewShapeForm();
	document.getElementById("saveTitle").value = "New brush and tree removal";	
	document. getElementById("colorLabel").innerHTML = "";
	document.getElementById("shapeColor").style.visibility = "hidden";
	mapShape = new MapShape(ge, gex, "FF14F000");
	mapShape.table = "brush";
	mapShape.draw();
	fade("savePanel");
	$('#activity_loading').activity(false);
}

function newBurn() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getUsersList('sl_new_shape_user_selector');
	setupAuthorizedUsersList('sl_authorized_users_list');
	getStewardshipSites('newShapeSiteSelector', 'finishNewBurn()');
}

function finishNewBurn() {
	clearNewShapeForm();
	document.getElementById("saveTitle").value = "New presribed burn";
	document. getElementById("colorLabel").innerHTML = "";
	document.getElementById("shapeColor").style.visibility = "hidden";
	mapShape = new MapShape(ge, gex, "ff1f00ff");
	mapShape.table = "burns";
	mapShape.draw();
	fade("savePanel");
	$('#activity_loading').activity(false);
}

function newSeed() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getUsersList('sl_new_shape_user_selector');
	setupAuthorizedUsersList('sl_authorized_users_list');
	getStewardshipSites('newShapeSiteSelector', 'finishNewSeed()');
}

function finishNewSeed() {
	clearNewShapeForm();
	document.getElementById("saveTitle").value = "New seed collection and planting";
	document. getElementById("colorLabel").innerHTML = "";
	document.getElementById("shapeColor").style.visibility = "hidden";
	mapShape = new MapShape(ge, gex, "FF14F0FF");
	mapShape.table = "seed";
	mapShape.draw();
	fade("savePanel");	
	$('#activity_loading').activity(false);
}	
	
function newWeed() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getUsersList('sl_new_shape_user_selector');
	setupAuthorizedUsersList('sl_authorized_users_list');
	getStewardshipSites('newShapeSiteSelector', 'finishNewWeed()');
}

function finishNewWeed() {
	clearNewShapeForm();
	document.getElementById("saveTitle").value = "New weed control";
	document. getElementById("colorLabel").innerHTML = "";
	document.getElementById("shapeColor").style.visibility = "hidden";
	mapShape = new MapShape(ge, gex, "FF7800F0");
	mapShape.table = "weed";
	mapShape.draw();
	fade("savePanel");
	$('#activity_loading').activity(false);
}

function newOther() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	getUsersList('sl_new_shape_user_selector');
	setupAuthorizedUsersList('sl_authorized_users_list');
	getStewardshipSites('newShapeSiteSelector', 'finishNewOther()');
}

function finishNewOther() {
	clearNewShapeForm();
	document.getElementById("saveTitle").value = "New other";
	document. getElementById("colorLabel").innerHTML = "Color:";
	document.getElementById("shapeColor").style.visibility = "visible";
	mapShape = new MapShape(ge, gex, "FFF0A014");
	mapShape.table = "other";
	mapShape.draw();
	fade("savePanel");
	$('#activity_loading').activity(false);
}				

function clearNewShapeForm() {
	document.getElementById('newShapeError').value = "";
	document.getElementById("shapeDateDay").value = "";
	document.getElementById("shapeDateMonth").value = "";
	document.getElementById("shapeDateYear").value = "";
	document.getElementById("shapeTitle").value = "";
	document.getElementById("shapeDescription").value = "";
	document.getElementById('uploadError').value = "";
	document.getElementById('editUploadedButton').disabled = true;
	document.getElementById("upload_file").value = "";
}
function changingColor(newColor) {
	// convert to String
	var newColorString = newColor + '';
	// convert from html color to kml
	newColorString = htmlColorToKml(newColorString);
	mapShape.changeColor(newColorString);
}
function saveNewShape() {
	// check if the shape has any geometry yet
	var theKml = mapShape.targetShape.getKml();
	var begin = theKml.indexOf("<coordinates>") + 13;
	var end = theKml.indexOf("</coordinates>");
	var coordinates = $.trim(theKml.slice(begin,end));	
	if (coordinates == '') {
		document.getElementById('newShapeError').value = "Click on the map to draw."; 
		return false;
	}
	// check if this is a public or private shape
	if ($('input:radio[name=sl_new_shape]:checked').val() == "public") {
		mapShape.authorized_users = null;
	} else {
		// private shape, so pass the list of authorized users to the mapShape
		mapShape.authorized_users = authorized_users;
	}
	// get date from form
	var shapeDateMonth = document.getElementById("shapeDateMonth").value.trim();
	var shapeDateDay = document.getElementById("shapeDateDay").value.trim();
	var shapeDateYear = document.getElementById("shapeDateYear").value.trim();
	// check if month or day is blank
	if (shapeDateMonth == '')
		shapeDateMonth = 0;
	if (shapeDateDay == '')
		shapeDateDay = 0;
	// check that the date is valid, display error if not
	if (!validDate(shapeDateMonth, shapeDateDay, shapeDateYear)) {
		document.getElementById('newShapeError').value = "Invalid date. Please correct."; 
		return false;
	}
	// make single digit month and date into double digit
	shapeDateMonth = pad2(parseInt(shapeDateMonth, 10));
	shapeDateDay = pad2(parseInt(shapeDateDay));
	// construct name string
	var shapeDate = shapeDateMonth + "/" + shapeDateDay + "/" + shapeDateYear;
	
	// check that site is selected
	var siteSelected = document.getElementById("siteList").value;
	if (siteSelected == "Select Site") {
		document.getElementById('newShapeError').value = "Please select a site."; 
		return false;
	}
	mapShape.site = siteSelected;
	// save shape name and description
	mapShape.name = shapeDate + " " + document.getElementById("shapeTitle").value;
	mapShape.description = document.getElementById("shapeDescription").value;	
	mapShape.save();
	// hide save shape panel
	fade("savePanel");
	if (sl_show_new_shape_div) {
		fade("sl_new_shape_div");	
		fade("sl_authorized_users_list");
		fade("sl_new_shape_user_selector");
		sl_show_new_shape_div = false;
	}
	document.getElementById("shapeColor").style.visibility = "hidden";
	setTimeout("document.getElementById('newShapeSiteSelector').innerHTML = ''", TimeToFade);			
	// clear any newly drawn shapes 
	mapShape.clear();
	// reload kmltree 
	tree.refresh(); 
}
function cancelNewShape() {
	mapShape.clear(); 
	fade("savePanel");
	document.getElementById("shapeColor").style.visibility = "hidden";
	setTimeout("document.getElementById('newShapeSiteSelector').innerHTML = ''", TimeToFade);
}
function cancelEditShape() {
	mapShape.clear(); 
	tree.refresh();
	fade("editPanel");
	document. getElementById("editColorLabel").innerHTML = "";
	document.getElementById("editShapeColor").style.visibility = "hidden";
	setTimeout("document.getElementById('editShapeSiteSelector').innerHTML = ''", TimeToFade);
}		
function cancelEditBorder() {
	mapShape.clear(); 
	tree.refresh();
	fade("borderPanel"); 
}	
function saveEditShape() {
	// get date from form
	var shapeDateMonth = document.getElementById("editDateMonth").value.trim();
	var shapeDateDay = document.getElementById("editDateDay").value.trim();
	var shapeDateYear = document.getElementById("editDateYear").value.trim();
	// check if month or day is blank
	if (shapeDateMonth == '')
		shapeDateMonth = 0;
	if (shapeDateDay == '')
		shapeDateDay = 0;
	// check that the date is valid, display error if not
	if (!validDate(shapeDateMonth, shapeDateDay, shapeDateYear)) {
		document.getElementById('editShapeError').value = "Invalid date. Please correct."; 
		return false;
	}
	// make single digit month and date into double digit
	shapeDateMonth = pad2(parseInt(shapeDateMonth, 10));
	shapeDateDay = pad2(parseInt(shapeDateDay));
	// construct name string
	var shapeDate = shapeDateMonth + "/" + shapeDateDay + "/" + shapeDateYear;
	
	// check that site is selected
	var siteSelected = document.getElementById("siteList").value;
	if (siteSelected == "Select Site") {
		document.getElementById('editShapeError').value = "Please select a site."; 
		return false;
	}
	if ($('input:radio[name=sl_edited_shape]:checked').val() == "public") {
		mapShape.authorized_users = null;
	} else {
		// private shape, so pass the list of authorized users to the mapShape
		mapShape.authorized_users = authorized_users;
	}
	mapShape.site = siteSelected;
	// save shape name and description
	mapShape.name = shapeDate + " " + document.getElementById("editTitle").value;	
	mapShape.description = document.getElementById("editDescription").value;

	mapShape.endEdit();
	// save the edited shape
	mapShape.save();
	// clear all shapes			
	gex.dom.clearFeatures();
	// reload all shapes
	tree.refresh();
	fade("editPanel");	
	if (sl_show_edited_shape_div) {
		fade("sl_edited_shape_div");	
		fade("sl_edited_shape_authorized_users_list");
		fade("sl_edited_shape_user_selector");
		sl_show_edited_shape_div = false;
	}
	document. getElementById("editColorLabel").innerHTML = "";
	document.getElementById("editShapeColor").style.visibility = "hidden";
	setTimeout("document.getElementById('editShapeSiteSelector').innerHTML = ''", TimeToFade);			
	// deleteSelected(true);
}
function saveEditBorder() {
	mapShape.endEdit();
	// save the edited shape
	mapShape.save();
	// clear all shapes			
	gex.dom.clearFeatures();
	// reload all shapes
	tree.refresh();
	fade("borderPanel");
	document.getElementById('borderPanel').style.visibility = 'hidden';	
}

/**
 * ---------------------------------------------------------
 * measuring functions
 *
 * ---------------------------------------------------------
 */
function startMeasuring() {
	closeAllPanels();
	document.getElementById('displayArea').innerHTML = '';
	document.getElementById('displayDistance').innerHTML = '';
	fade("measurePanel");
	measureTool = new MeasureTool();
	// measureTool.setUnits('english');
}
function doneMeasuring() {
	fade("measurePanel");
	measureTool.clear();
	measureTool = null;
}
function measureArea() {
	measureTool.measureArea(gex, 'displayArea');
}
function measureDistance() {
	measureTool.measureDistance(gex, 'displayDistance');
}
function measureChangeUnits() {
	if ( measureTool.areaTarget )
		switch( measureTool.units )
				{
					case 'metric':
						measureTool.setUnits('acre-english');
						break;
					case 'acre-english':
						measureTool.setUnits('english');
						break;
					case 'english':
						measureTool.setUnits('metric');
						break;
				}
	if ( measureTool.distTarget )	
		switch( measureTool.units )
			{
				case 'acre-english':
					measureTool.setUnits('metric');
						break;
				case 'metric':
					measureTool.setUnits('english');
					break;
				case 'english':
					measureTool.setUnits('metric');
					break;
			}		
}		


/**
 * ---------------------------------------------------------
 * trail functions
 *
 * ---------------------------------------------------------
 */
 
function newTrail() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	selected = null;
	getStewardshipSites('trailSiteSelector', 'finishNewTrail()');
}

function finishNewTrail() {
	document.getElementById("trailName").value = "";
	document.getElementById('trailError').value = "";	
	document.getElementById("uploadTrailStuff").style.visibility = "visible";
	document.getElementById('editUploadedTrailButton').disabled = true;
	document.getElementById('uploadTrailError').value = "";
	document.getElementById('uploadTrailFile').value = "";
	mapLine = new MapLine(ge, gex, "ffffff00");
	mapLine.table = "trails";
	mapLine.draw();
	fade("trailPanel");
	$('#activity_loading').activity(false);
}

function cancelTrail() {
	mapLine.clear();
	document.getElementById("trailName").value = "";
	document.getElementById('trailError').value = "";
	document.getElementById("uploadTrailStuff").style.visibility = "hidden";
	fade("trailPanel");
	setTimeout("document.getElementById('trailSiteSelector').innerHTML = ''", TimeToFade);
	if (selected)
		tree.refresh();
}

function saveTrail() {
	// check if the shape has any geometry yet
	var theKml = mapLine.targetShape.getKml();
	var begin = theKml.indexOf("<coordinates>") + 13;
	var end = theKml.indexOf("</coordinates>");
	var coordinates = $.trim(theKml.slice(begin,end));	
	if (coordinates == '') {
		document.getElementById('trailError').value = "Click on the map to draw."; 
		return false;
	}
	// check that site is selected
	var siteSelected = document.getElementById("siteList").value;
	if (siteSelected == "Select Site") {
		document.getElementById('trailError').value = "Please select a site."; 
		return false;
	}
	mapLine.site = siteSelected;
	// save trail name and insert trail into db
	mapLine.name = document.getElementById("trailName").value;
	mapLine.site = document.getElementById("siteList").value;		
	mapLine.save();
	// hide trail panel
	document.getElementById("trailName").value = "";
	document.getElementById("uploadTrailStuff").style.visibility = "hidden";
	fade("trailPanel");
	document.getElementById('trailError').value = "";	
	setTimeout("document.getElementById('trailSiteSelector').innerHTML = ''", TimeToFade);	
	// clear any newly drawn lines 
	mapLine.clear();
	// if we are saving an edited existing trail, delete the original unedited trail
	//if (selected)
	//	deleteSelected(true);
	// reload kmltree 
	tree.refresh(); 
}

/**
 * ---------------------------------------------------------
 * Geographic Features and Landmarks functions
 *
 * ---------------------------------------------------------
 */
 
function newLandmark() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	selected = null;
	getUsersList('sl_new_landmark_user_selector');
	setupAuthorizedUsersList('sl_landmark_authorized_users_list');
	getStewardshipSites('landmarkSiteSelector', 'finishNewLandmark()');
}

function finishNewLandmark() {
	document.getElementById("landmarkError").value = "";
	document.getElementById("landmarkTitle").value = "";
	document.getElementById("landmarkDescription").value = "";
	document.getElementById("landmarkColor").style.visibility = "visible";
	mapShape = new MapShape(ge, gex, "FF54B6F7");//F7B654
	mapShape.table = "landmark";
	mapShape.draw();
	fade("landmarkPanel");
	$('#activity_loading').activity(false);
}

function cancelLandmark() {
	mapShape.clear(); 
	fade("landmarkPanel");
	document.getElementById("landmarkColor").style.visibility = "hidden";
	setTimeout("document.getElementById('landmarkSiteSelector').innerHTML = ''", TimeToFade);
	if (selected)
		tree.refresh();
}

function saveLandmark() {
	// check if the shape has any geometry yet
	var theKml = mapShape.targetShape.getKml();
	var begin = theKml.indexOf("<coordinates>") + 13;
	var end = theKml.indexOf("</coordinates>");
	var coordinates = $.trim(theKml.slice(begin,end));	
	if (coordinates == '') {
		document.getElementById('landmarkError').value = "Click on the map to draw."; 
		return false;
	}
	// check that site is selected
	var siteSelected = document.getElementById("siteList").value;
	if (siteSelected == "Select Site") {
		document.getElementById('landmarkError').value = "Please select a site."; 
		return false;
	}
	// check if this is a public or private shape
	if ($('input:radio[name=sl_new_landmark]:checked').val() == "public") {
		mapShape.authorized_users = null;
	} else {
		// private shape, so pass the list of authorized users to the mapShape
		mapShape.authorized_users = authorized_users;
	}	
	mapShape.site = siteSelected;
	mapShape.name = document.getElementById("landmarkTitle").value;	
	mapShape.description = document.getElementById("landmarkDescription").value;
	mapShape.save();
	// hide landmark panel
	document.getElementById("landmarkTitle").value = "";
	document.getElementById("landmarkDescription").value = "";
	fade("landmarkPanel");
	if (sl_show_new_landmark_div) {
		fade("sl_new_landmark_div");
		fade("sl_landmark_authorized_users_list");
		fade("sl_new_landmark_user_selector");
		sl_show_new_landmark_div = false;
	}
	document.getElementById("landmarkPanel").style.visibility = "hidden";
	document.getElementById("landmarkColor").style.visibility = "hidden";
	document.getElementById('landmarkError').value = "";	
	setTimeout("document.getElementById('landmarkSiteSelector').innerHTML = ''", TimeToFade);
	// clear newly drawn shape
	mapShape.clear();
	tree.refresh(); 
}

/**
 * ---------------------------------------------------------
 * login/logout functions
 *
 * ---------------------------------------------------------
 */
function login_logout() {
	// show activity monitor
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	loggedIn('beginLogout()', 'beginLogin()');
}
function beginLogin() {
	closeAllPanels();
	document.getElementById("loginError").value = "";
	document.getElementById('loginEmail').value = "";
	document.getElementById('loginPassword').value = "";
	fade("loginPanel");
	// turn off activity monitor
	$('#activity_loading').activity(false);
}

function login() {	
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	// prepare POST parameters
	var theEmail = document.getElementById("loginEmail").value;
	var thePassword = document.getElementById("loginPassword").value;
	var params = "email=" + theEmail + "&password=" + thePassword;
	// send the new request 
	var url = "php/login_logout.php";
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			var response = ajaxRequest.responseText;
			if (response.indexOf("success login") != -1) {
				loadKmlTree(response.substring(14));
				document.getElementById("loginMenu").innerHTML = "Logout";
				if ( isAdmin() )
					document.getElementById("adminMenu").style.visibility = "visible";
				closeAllPanels()
			} else 
				document.getElementById("loginError").value = response;
			;
			// turn off activity monitor
			$('#activity_loading').activity(false);
		}
	}			
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
}

function beginLogout() {
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	var params = "";
	var url = "php/login_logout.php";	
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			var response = ajaxRequest.responseText;
			if (response == "success logout") {
				loadKmlTree(-1);
				document.getElementById("loginMenu").innerHTML = "Login";
				document.getElementById("adminMenu").style.visibility = "hidden";
				closeAllPanels();
				// turn off activity monitor
				$('#activity_loading').activity(false);
			}
		}
	}		
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	// send the new request 
	ajaxRequest.send(params);
}

// accepts 2 strings as input, 1 to be evaluated if user logged in, one if the user is logged out
function loggedIn(user_logged_in, user_logged_out) {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	var url = "php/check_login.php";
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			$('#activity_loading').activity(false);
			var response = ajaxRequest.responseText;
			if (response == "not logged in") {
				eval(user_logged_out);
			} else 
				eval(user_logged_in);
		}
	}
	// send the new request 			
	ajaxRequest.open("POST", url, true);
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send('');
}

function isAdmin() {	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();	
	// send the new request 
	var url = "php/check_admin.php";			
	ajaxRequest.open("POST", url, false);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send('');
	var response = ajaxRequest.responseText;
	if (response == "true") {
		return true;
	} else 
		return false;
}

function notLoggedInMessage() {	
	alert('Sorry, you must login first.\n\nTo demo Restoration Map you may login with email "guest" and password "guest".');
}

/**
 * ---------------------------------------------------------
 * user info functions
 *
 * ---------------------------------------------------------
 */
 
function beginChangePassword() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	closeAllPanels();
	document.getElementById("userError").value = "";
	document.getElementById('oldPassword').value = "";
	document.getElementById('newPassword1').value = "";
	document.getElementById('newPassword2').value = "";
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			document.getElementById("userInfo").innerHTML = ajaxRequest.responseText;
			fade("userPanel");
			// turn off activity monitor
			$('#activity_loading').activity(false);
		}
	}
	// send the new request 
	var url = "php/get_user_info.php";			
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send('');
}

function changePassword() {
	$('#activity_loading').activity({segments: 12, align: 'right', valign: 'top', steps: 3, width:2, space: 1, length: 3, color: '#ffffff', speed: 1.5});
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			var response = ajaxRequest.responseText;
			if (response == "success") {
				closeAllPanels();
			} else {
				document.getElementById('oldPassword').value = "";
				document.getElementById('newPassword1').value = "";
				document.getElementById('newPassword2').value = "";
				document.getElementById("userError").value = response;
			}
			// turn off activity monitor
			$('#activity_loading').activity(false);
		}
	}
	// prepare POST parameters
	var oldPass = document.getElementById('oldPassword').value;
	var newPass1 = document.getElementById('newPassword1').value;
	var newPass2 = document.getElementById('newPassword2').value;
	var params = "old=" + oldPass + "&new1=" + newPass1 + "&new2=" + newPass2;
	// send the new request 
	var url = "php/change_password.php";			
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
	
}

/**
 * ---------------------------------------------------------
 *
 * fading functions
 *
 * ---------------------------------------------------------
 */
 
function fade(eid) {
	var element = document.getElementById(eid);
  	if (element == null)
    	return;
  
  	element.style.visibility='visible';
  	element.style.zIndex = 10;
   
  	if (element.FadeState == null) {
    	if (element.style.opacity == null || element.style.opacity == '' || element.style.opacity == '1') {
      		element.FadeState = -2;
    	} else {
      		element.FadeState = 2;
    	}
	}
    
  	if (element.FadeState == 1 || element.FadeState == -1) {
    	element.FadeState = element.FadeState == 1 ? -1 : 1;
    	element.FadeTimeLeft = TimeToFade - element.FadeTimeLeft;
  	} else {
    	element.FadeState = element.FadeState == 2 ? -1 : 1;
    	element.FadeTimeLeft = TimeToFade;
    	setTimeout("animateFade(" + new Date().getTime() + ",'" + eid + "')", 33);
  	}  
}

function animateFade(lastTick, eid) {  
  	var curTick = new Date().getTime();
  	var elapsedTicks = curTick - lastTick;
  
  	var element = document.getElementById(eid);
 
  	if (element.FadeTimeLeft <= elapsedTicks) {
	    element.style.opacity = element.FadeState == 1 ? '0.95' : '0';
	    element.style.filter = 'alpha(opacity = ' + (element.FadeState == 1 ? '95' : '0') + ')';
	    element.FadeState = element.FadeState == 1 ? 2 : -2;
	    if (element.FadeState == -2) {
	    	element.style.visibility='hidden';
	    	element.style.zIndex = -10;
	    }
	    return;
  	}
 
  	element.FadeTimeLeft -= elapsedTicks;
  	var newOpVal = element.FadeTimeLeft/TimeToFade;
  	if (element.FadeState == 1)
    	newOpVal = 1 - newOpVal;

  	element.style.opacity = newOpVal;
  	element.style.filter = 'alpha(opacity = ' + (newOpVal*100) + ')';
  
  	setTimeout("animateFade(" + curTick + ",'" + eid + "')", 33);
}

/**
 * ---------------------------------------------------------
 *
 * private/public layers and authorizing users to view private layers functions
 *
 * ---------------------------------------------------------
 */
var sl_show_new_shape_div = false;
var sl_show_edited_shape_div = false;
var sl_show_new_landmark_div = false;
var authorized_users = new Array();

// fades the user authorization div on and off in the new shape panel 
function sl_new_shape_show_div() {
	old_sl_show_new_shape_div = sl_show_new_shape_div;
	if ($('input:radio[name=sl_new_shape]:checked').val() == "public") {
		sl_show_new_shape_div = false;
	}
	if ($('input:radio[name=sl_new_shape]:checked').val() == "private") {
		sl_show_new_shape_div = true;
	}
	if (old_sl_show_new_shape_div != sl_show_new_shape_div) {
		fade("sl_new_shape_div");	
		fade("sl_authorized_users_list");
		fade("sl_new_shape_user_selector");
	}
}

// fades the user authorization div on and off in the new landmark panel 
function sl_edited_shape_show_div() {
	old_sl_show_edited_shape_div = sl_show_edited_shape_div;
	if ($('input:radio[name=sl_edited_shape]:checked').val() == "public") {
		sl_show_edited_shape_div = false;
	}
	if ($('input:radio[name=sl_edited_shape]:checked').val() == "private") {
		sl_show_edited_shape_div = true;
	}
	if (old_sl_show_edited_shape_div != sl_show_edited_shape_div) {
		fade("sl_edited_shape_div");	
		fade("sl_edited_shape_authorized_users_list");
		fade("sl_edited_shape_user_selector");
	}
}

// fades the user authorization div on and off in the new landmark panel 
function sl_new_landmark_show_div() {
	old_sl_show_new_landmark_div = sl_show_new_landmark_div;
	if ($('input:radio[name=sl_new_landmark]:checked').val() == "public") {
		sl_show_new_landmark_div = false;
	}
	if ($('input:radio[name=sl_new_landmark]:checked').val() == "private") {
		sl_show_new_landmark_div = true;
	}
	if (old_sl_show_new_landmark_div != sl_show_new_landmark_div) {
		fade("sl_new_landmark_div");
		fade("sl_landmark_authorized_users_list");
		fade("sl_new_landmark_user_selector");	
	}
}

// creates the list of authorized users for new shapes and new landmarks
function setupAuthorizedUsersList( listDiv ) {
	// always check 'public' when new shape
	$('input:radio[name="sl_new_shape"]').filter('[value="public"]').attr('checked', true);
	$('input:radio[name="sl_new_landmark"]').filter('[value="public"]').attr('checked', true);
	// hide authorized user list if necessary
	if (sl_show_new_shape_div) {
		fade("sl_new_shape_div");	
		fade("sl_authorized_users_list");
		fade("sl_new_shape_user_selector");
		sl_show_new_shape_div = false;
	}
	if (sl_show_new_landmark_div) {
		fade("sl_new_landmark_div");
		fade("sl_landmark_authorized_users_list");
		fade("sl_new_landmark_user_selector");
		sl_show_new_landmark_div = false;
	}
	// reset authorized user list
	authorized_users = new Array();
	// first get the full name of the current user
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// add the users full name to the list of authorized users
			authorized_users.push(ajaxRequest.responseText);
			// now write the html list into the div
			var users_list_html = '';
			for (var i = 0; i < authorized_users.length; i++) {
				users_list_html = users_list_html + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- " + authorized_users[i] + "<br>";
			}
			document.getElementById( listDiv ).innerHTML = users_list_html;
		}
	}
	// construct URL				
	var url = "php/get_user_full_name.php";	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

// creates the list of authorized users for an edited landmark and selects private or public
// we shouldn't need to repeat this entire function
function setupAuthorizedUsersListForEditedLandmark(users_list_div, sl_radio, show_list_div) {
	//  get the shapes database table and id
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);	
	var dbId = theId.slice(dash + 1, theId.indexOf("site"));

	// reset authorized user list
	authorized_users = new Array();
	// first get the full name of the current user
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// response comes in form public:Lastname, firstname:Lastname, Firstname			
			var response = ajaxRequest.responseText;
			var responseArray = response.split(':');
			// check if shape is public or private	
			if (responseArray[0] == 'public') {
				// hide the list div
				if (sl_show_new_landmark_div) {
					fade("sl_new_landmark_div");
					fade("sl_landmark_authorized_users_list");
					fade("sl_new_landmark_user_selector");
					sl_show_new_landmark_div = false;
				}	
				// check public
				$('input:radio[name="' + sl_radio + '"]').filter('[value="public"]').attr('checked', true);
				// add only the current user
				authorized_users.push(responseArray[1]);	
			} else {
				// shape is private
				// so show the list div
				if (!sl_show_new_landmark_div) {
					fade("sl_new_landmark_div");
					fade("sl_landmark_authorized_users_list");
					fade("sl_new_landmark_user_selector");
					sl_show_new_landmark_div = true;
				}
				// check private
				$('input:radio[name="' + sl_radio + '"]').filter('[value="private"]').attr('checked', true);
				// add all the authorized users to the list
				for (var i = 1; i < responseArray.length; i++) {
					authorized_users.push(responseArray[i]);
				}	
			}			
			// now write the html list into the div
			var users_list_html = '';
			for (var i = 0; i < authorized_users.length; i++) {
				users_list_html = users_list_html + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- " + authorized_users[i] + "<br>";
			}
			document.getElementById( users_list_div ).innerHTML = users_list_html;
		}
	}
	// construct URL				
	var url = "php/get_authorized_user_list.php?table=" + table + "&shape_id=" + dbId;	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

// creates the list of authorized users for an edited shape (not landmark) and selects private or public
// we shouldn't need to repeat this entire function
function setupAuthorizedUsersListForEditedShape(users_list_div, sl_radio, show_list_div) {
	//  get the shapes database table and id
	var theId = selected.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	var table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site") + 4;
	var site_id = theId.slice(siteBegin);	
	var dbId = theId.slice(dash + 1, theId.indexOf("site"));

	// reset authorized user list
	authorized_users = new Array();
	// first check if the shape is private or public
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			// response comes in form public:Lastname, firstname:Lastname, Firstname
			var response = ajaxRequest.responseText;
			var responseArray = response.split(':');
			// check if shape is public or private	
			if (responseArray[0] == 'public') {
				// hide the list div
				if (sl_show_edited_shape_div) {
					fade("sl_edited_shape_div");	
					fade("sl_edited_shape_authorized_users_list");
					fade("sl_edited_shape_user_selector");
					sl_show_edited_shape_div = false;
				}	
				// check public
				$('input:radio[name="' + sl_radio + '"]').filter('[value="public"]').attr('checked', true);
				// add only the current user
				authorized_users.push(responseArray[1]);	
			} else {
				// shape is private
				// so show the list div
				if (!sl_show_edited_shape_div) {
					fade("sl_edited_shape_div");	
					fade("sl_edited_shape_authorized_users_list");
					fade("sl_edited_shape_user_selector");
					sl_show_edited_shape_div = true;
				}
				// check private
				$('input:radio[name="' + sl_radio + '"]').filter('[value="private"]').attr('checked', true);
				// add all the authorized users to the list
				for (var i = 1; i < responseArray.length; i++) {
					authorized_users.push(responseArray[i]);
				}	
			}			
			// now write the html list into the div
			var users_list_html = '';
			for (var i = 0; i < authorized_users.length; i++) {
				users_list_html = users_list_html + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- " + authorized_users[i] + "<br>";
			}
			document.getElementById( users_list_div ).innerHTML = users_list_html;
		}
	}
	// construct URL				
	var url = "php/get_authorized_user_list.php?table=" + table + "&shape_id=" + dbId;	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

// accepts the div to insert the pulldown menu, and a string that can reference a callback function
function getUsersList(pulldownDiv) {	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	ajaxRequest.onreadystatechange=function() {
		if (ajaxRequest.readyState==4 && ajaxRequest.status==200) {
			document.getElementById( pulldownDiv ).innerHTML = ajaxRequest.responseText;
		}
	}
	// construct URL				
	var url = "php/get_users_list.php";	
	// send the new request		
	ajaxRequest.open("GET", url, true);
	ajaxRequest.send(null);
}

// add user to list
function authorizeUser(listDiv) {
	var user_to_authorize = $('#userList').val();
	if (user_to_authorize != 'Select User') {
		var already_in_list = false;
		for (var i = 0; i < authorized_users.length; i++) {
			if (user_to_authorize == authorized_users[i])
				already_in_list = true;
		}
		if (!already_in_list) {
			authorized_users.push(user_to_authorize);
			i = 0;
			var users_list_html = '';
			while (i < authorized_users.length) {
				users_list_html = users_list_html + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- " + authorized_users[i] + "<br>";
				i++;
			}
			document.getElementById( listDiv ).innerHTML = users_list_html;		
		}
	}
}

// remove user from list
function deauthorizeUser(listDiv) {
	var user_to_deauthorize = $('#userList').val();
	var i = 0;
	var users_list_html = '';
	for (var i = 0; i < authorized_users.length; i++) {
		// dauthorize the selected user unless it is the current user (i == 0)
		if (user_to_deauthorize == authorized_users[i] && i != 0)
			authorized_users.splice(i, 1);
	}
	i = 0;
	for (var i = 0; i < authorized_users.length; i++) {
		users_list_html = users_list_html + "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;- " + authorized_users[i] + "<br>";
	}
	document.getElementById( listDiv ).innerHTML = users_list_html;
}