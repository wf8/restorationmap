/**
 * Creates a new map shape.
 * @constructor
 * @param {GEPlugin} ge, the Google Earth Plugin object  
 * @param {GEarthExtensions} gex, the Google Earth Extensions object
 * @param {String} color, color of shape
 */
MapShape = function(ge, gex, color) {
    this.id = null;
    this.ge = ge;
    this.gex = gex;
    this.color = color;
    this.targetShape = null;
    this.name = null;
    this.description = null;
    this.table = null;
    this.site = null;
    this.authorized_users = null;
};

MapShape.prototype.setID = function(id) {
    this.id = id;
};

MapShape.prototype.getID = function() {
    return this.id;
};

/**
 * Change the color of the shape
 * @param {String} newColor, the new color
 */
MapShape.prototype.changeColor = function( newColor ) {
	
	this.color = newColor;
	// get the existing style for the placemark
	var style = this.targetShape.getStyleSelector();
	// get the color of the style
	style.getLineStyle().getColor().set(newColor);
	style.getIconStyle().getColor().set(newColor);
	// set the modified style back to the placemark
	this.targetShape.setStyleSelector(style);
}

/**
 * Start accepting user input for shape-draw. Sets callbacks to keep measures updated and to switch to edit mode on completion.
 * @param {Function} finishedCallback, function to be called when the user indicates the drawing is complete.
 */
MapShape.prototype.draw = function( finishedCallback ) {
    this.clear();
    this.id = null;

    this.targetShape = this.gex.dom.addPlacemark({
        visibility: true,
        polygon: [],
        style: {
            line: { width: 4, color: this.color },
            poly: { color: '00000000' },
            icon: { color: this.color }
        }
    });

    var drawLineStringOptions = {
        bounce: false,
        finishCallback: finishedCallback
    };

    this.gex.edit.drawLineString( this.targetShape.getGeometry().getOuterBoundary(), drawLineStringOptions );
    
    // finishedCallback.call();
};

/**
 * Begin editing process
 */
MapShape.prototype.edit = function() {
    this.targetShape.setVisibility(true);
    if ( this.targetShape.getGeometry().getType() == 'KmlPoint' )
    		this.gex.edit.makeDraggable( this.targetShape, { bounce: false } );
    	else
		    this.gex.edit.editLineString( this.targetShape.getGeometry().getOuterBoundary() );
};

/**
 * Stops the editing process.
 */
MapShape.prototype.endEdit = function() {
   	 if ( this.targetShape.getGeometry().getType() == 'KmlPoint' )
    		this.gex.edit.endDraggable( this.targetShape );
    	else
		    this.gex.edit.endEditLineString(this.targetShape.getGeometry().getOuterBoundary());
};

/**
 * Displays target shape (user drawn shape) on the map
 */
MapShape.prototype.displayTarget = function() {
    this.gex.util.displayKmlString(this.targetShape);
};



/**
 * Displays given shape on the map
 * @param {String} shape, kml version of shape
 */
MapShape.prototype.display = function(shape) {
    return this.gex.util.displayKmlString(shape);
};

/**
 * Removes given shape from the map
 * @param {String} shape, kml version of shape
 */
MapShape.prototype.remove = function(shape) {
    this.gex.dom.removeObject(shape);
};


/**
 * Remove the shape that was being drawn.
 */
MapShape.prototype.clear = function() {
    if ( this.targetShape ) {
    	if ( this.targetShape.getGeometry().getType() == 'KmlPoint' )
    		this.gex.edit.endDraggable( this.targetShape );
    	else
		    this.gex.edit.endEditLineString(this.targetShape.getGeometry().getOuterBoundary());
        this.gex.dom.removeObject( this.targetShape );
        this.targetShape = null;
    }
};

/**
 * Remove all shapes from map
 * Remove references to target shape
 */
MapShape.prototype.clearAll = function() {
    if ( this.targetShape ) {
	    if ( this.targetShape.getGeometry().getType() == 'KmlPoint' )
    		this.gex.edit.endDraggable( this.targetShape );
    	else
		    this.gex.edit.endEditLineString(this.targetShape.getGeometry().getOuterBoundary());
        this.id = null;
        this.targetShape = null;
    }
    //the following line also removes the study region from the map (problem)
    this.gex.dom.clearFeatures();
    //the following line isn't much of a solution to this problem (as it takes many seconds to load)
    //this.gex.util.displayKml(studyregionKML);
};

/**
 * Hide the shape that was being drawn.
 */
MapShape.prototype.hide = function() {
    if ( this.targetShape ) {
        if ( this.targetShape.getGeometry().getType() == 'KmlLineString' )
	    	this.gex.edit.endEditLineString(this.targetShape.getGeometry().getOuterBoundary());
	    else
	   		this.gex.edit.endDraggable( this.targetShape );
        this.targetShape.setVisibility(false);
    }
};

/**
 * save the shape that was being drawn.
 */
MapShape.prototype.save = function() {
	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();	
	
	// check if shape has an id, we are updating an existing shape
	var shapeId = this.targetShape.getId();
	
	// hack for landmarks
	if (shapeId == '')
		shapeId = this.id;
		
	var dbId = '';
	if (shapeId != null) {
		// we need to get the db id of the shape
		var dash = shapeId.indexOf("-");
		var siteBegin = shapeId.indexOf("site");
		dbId = shapeId.slice(dash + 1, siteBegin);
	}
	// get coordinates to insert in db 
	var theKml = this.targetShape.getKml();
	var begin = theKml.indexOf("<coordinates>") + 13;
	var end = theKml.indexOf("</coordinates>");
	var coordinates = theKml.slice(begin,end);	
	
	// if this is a private shape, send the list of authorized users as a string in
	// the format "last_name1, first_name1:last_name2, first_name2:last_name3, first_name3" 
	// otherwise send an empty string
	if (this.authorized_users == null)
		var auth_users_param = "&authorized_users="; // send empty string
	else
		var auth_users_param = "&authorized_users=" + this.authorized_users.join(":");
		
	if (this.table == 'border') {		
		// construct POST params for border
		var params = "table=" + this.table + "&id=" + dbId + "&site=" + this.site + "&coordinates=" + coordinates;
	
	} else if (this.table == 'landmark') {
		// construct POST params for landmarks
		var params = "table=" + this.table + "&name=" + this.name + "&id=" + dbId + "&color=" + this.color;
		params = params + "&description=" + this.description + "&site=" + this.site + auth_users_param + "&coordinates=" + coordinates;
		
	} else if (this.table == 'trails') {
		// construct POST params for trails
		// hack for saving multi-shape geo
		var params = "table=" + this.table + "&name=" + this.name + "&id=" + dbId;
		params = params + "&site=" + this.site + auth_users_param + "&coordinates=" + coordinates;
	} else {
		// get shapes date and title
		var shapeName = this.name;
		var shapeMonth = shapeName.slice(0, 2); 
		var shapeDay = shapeName.slice(3, 5); 
		var shapeYear = shapeName.slice(6, 10);
		var shapeTitle = shapeName.slice(11);
		// put date in sql date format
		var sqlDate = shapeYear + "-" + shapeMonth + "-" + shapeDay;	
				
		// construct POST params
		var params = "table=" + this.table + "&date=" + sqlDate + "&title=" + shapeTitle + "&id=" + dbId;
		params = params + "&description=" + this.description + "&site=" + this.site + auth_users_param + "&coordinates=" + coordinates;		
		// if it an 'other' shape, save the color to the other database table
		if (this.table = 'other')
			params = params + "&color=" + this.color;	
	}				
	// send the new request 
	var url = "php/insertkml.php";			
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);		
}

/**
 * delete the shape from database
 */
MapShape.prototype.deleteShape = function() {
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	// prepare shape to delete
	var theId = this.targetShape.getId();
	var begin = theId.indexOf(".") + 1;
	var dash = theId.indexOf("-");
	this.table = theId.slice(begin, dash);
	var siteBegin = theId.indexOf("site");
	var id = theId.slice(dash + 1, siteBegin);	
	var params = "table=" + this.table + "&id=" + id;
	// send the new request 
	var url = "php/deletekml.php";			
	ajaxRequest.open("POST", url, true);				
	// Send the proper header information along with the request 
	ajaxRequest.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	ajaxRequest.send(params);
	
}