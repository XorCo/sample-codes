/**
* @author 	XorCo
* @date		07.27.2012
* @description	Returns the margins, width and height of a picture and its draggable area
* @param 	link(String) 	- source of the image ex: http://www.example.com/image.jpg
* @param	result(JSON) 	- json object containing the results
* @use		pollCreation.js
*/
function getImageProperties(link,result)
{
	var img = new Image();
	img.onload = function() {
		var default_width 		= _defaultWidth;		// GLOBAL(config.js) - Width of the standard thumbnail
		var default_height 		= _defaultHeight;		// GLOBAL(config.js) - Height of the standard thumbnail
		var appropriate_width 	= _defaultAppropriateWidth;		// GLOBAL(config.js) - Width of appropriate draggable area
		var appropriate_height 	= _defaultAppropriateHeight;		// GLOBAL(config.js) - Height of appropriate draggable area
		var picture_width 		= this.width;			// Width of the input picture
		var picture_height 		= this.height;			// Height of the input picture

		// We give an already appropriate size to the image
		var horizontal_difference 	= picture_width  - appropriate_width;
		var vertical_difference 	= picture_height - appropriate_height;
		
		// If the image is too big, the picture is being resized according to the greatest side
		if ( horizontal_difference > 0 && vertical_difference > 0 )
		{
			var global_difference = horizontal_difference - vertical_difference;
			
			// If the picture is in landscape mode
			if ( global_difference > 0 )
			{
				picture_width  = (appropriate_height/picture_height)*picture_width;
				picture_height = appropriate_height;
			}
			else
			{
				picture_height = (appropriate_width/picture_width)*picture_height;
				picture_width  = appropriate_width;
			}
		}

		// The draggable area is being determined in function of the difference between 
		// the size of the picture and the thumbnail
		var horizontal_margin = (default_width - picture_width)/2;
		var vertical_margin   = (default_height - picture_height)/2;

		var draggable_width  = picture_width + Math.abs(default_width - picture_width);
		var draggable_height = picture_height + Math.abs(default_height - picture_height);
		
		// Placement of the draggable_area relative to the thumbnail
		var draggable_x = (default_width - draggable_width)/2;			// Horizontal
		var draggable_y = (default_height - draggable_height)/2;		// Vertical

		draggable_width  = draggable_width - Math.abs(horizontal_margin);
		draggable_height = draggable_height - Math.abs(vertical_margin);
		draggable_x = draggable_x + Math.abs(horizontal_margin);
		draggable_y = draggable_y + Math.abs(vertical_margin);

		// Output
		json = {
				"picture":
					{	
						"link": link,
						"marginTop": Math.round(vertical_margin),
						"marginLeft": Math.round(horizontal_margin),
						"width": picture_width,
						"height": picture_height
					},
				"draggable":
					{
						"width": draggable_width,
						"height": draggable_height,
						"marginLeft": Math.round(draggable_x),
						"marginTop": Math.round(draggable_y)						
					}
			};
		
		result(json);
	};
	
	// Must be placed after img.onload to properly work
	img.src = link;
}

