/**
 * Creates a new map line.
 * @constructor
 * @param {GEPlugin} ge, the Google Earth Plugin object  
 * @param {GEarthExtensions} gex, the Google Earth Extensions object
 * @param {String} color, color of shape
 */
 MapLine = function(ge, gex, color) {
    this.id = null;
    this.site = null;
    this.ge = ge;
    this.gex = gex;
    this.color = color;
    this.targetShape = null;
    this.name = null;
    this.description = null;
    this.table = null;
};
MapLine.prototype.setID = function(id) {
    this.id = id;
};

MapLine.prototype.getID = function() {
    return this.id;
};

/**
 * Start accepting user input for line draw. 
 */
MapLine.prototype.draw = function() {
    this.clear();
    this.id = null;
    
    this.targetShape = this.gex.dom.addPlacemark({
        lineString: [],
        style: {
            line: { width: 3, color: this.color }
        }
    });
    
    var drawLineStringOptions = {
        bounce: false,
    };

    this.gex.edit.drawLineString( this.targetShape.getGeometry(), drawLineStringOptions );   
};

/**
 * Begin editing process
 */
MapLine.prototype.edit = function() {
    this.targetShape.setVisibility(true);
    this.gex.edit.editLineString( this.targetShape.getGeometry() );
};

/**
 * Stops the editing process.
 */
MapLine.prototype.endEdit = function() {
    this.gex.edit.endEditLineString( this.targetShape.getGeometry() );
};

/**
 * Remove the line that was being drawn.
 */
MapLine.prototype.clear = function() {
    if ( this.targetShape ) {
        this.gex.edit.endEditLineString( this.targetShape.getGeometry() );
        this.gex.dom.removeObject( this.targetShape );
        this.targetShape = null;
    }
};

/**
 * save the line that was being drawn.
 */
MapLine.prototype.save = function() {
	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();	
	// check if shape has an id, we are updating an existing shape
	var shapeId = this.targetShape.getId();
	var dbId = '';
	if (shapeId != null) {
		// we need to get the db id of the shape
		var dash = shapeId.indexOf("-");
		var siteBegin = shapeId.indexOf("site");
		dbId = shapeId.slice(dash + 1, siteBegin);
	}
	
	// get shapes date and title
	var shapeName = this.name;
	
	// get coordinates to insert in db 
	var theKml = this.targetShape.getKml();
	var begin = theKml.indexOf("<coordinates>") + 13;
	var end = theKml.indexOf("</coordinates>");
	var coordinates = theKml.slice(begin,end);	

	// construct POST params
	var params = "table=" + this.table + "&name=" + shapeName + "&coordinates=" + coordinates + "&site=" + this.site + "&id=" + dbId;						
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
MapLine.prototype.deleteLine = function() {
	
	//setup new AJAX request 
	var ajaxRequest  = new XMLHttpRequest();
	// prepare line to delete
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
