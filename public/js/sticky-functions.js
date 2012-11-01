var edited = function(note) {
	//alert("Edited note with id " + note.id + ", new text is: " + note.text);
	$.get('notes/edit',{
		  id	: note.id,	
		  text  : note.text,
		  x		: note.pos_x,
		  y		: note.pos_y,
		  w		: note.width,
		  h		: note.height
	});		
	
}
var created = function(note) {
	//alert("Created note with id " + note.id + ", text is: " + note.text);
	$.get('notes/create',{
		  id	: note.id,	
		  text  : note.text,
		  x		: note.pos_x,
		  y		: note.pos_y,
		  w		: note.width,
		  h		: note.height
	});		
}

var deleted = function(note) {
	//alert("Deleted note with id " + note.id + ", text is: " + note.text);
	$.get('notes/delete',{
		  id	: note.id
	});		
	
}

/*
 * key "id" has value "1"
key "text" has value "asdfasdfas fsadf asdf "
key "pos_x" has value "277"
key "pos_y" has value "59"
key "width" has value "160"
key "height" has value "162"
 */

			