$(document).ready(function(){
	function split( val ) {
        return val.split( /,\s*/ );
        }
	function extractLast( term ) {
		return split( term ).pop();
		}
	
	var availableTags;
	if ($("#availableTags").text() != '') {
		availableTags = $.parseJSON($("#availableTags").text());	
		$('[name="tags"').bind("keydown", function(event){
			if ( event.keyCode === $.ui.keyCode.TAB &&
		            $( this ).autocomplete( "instance" ).menu.active ) {
		          event.preventDefault();
		        }
		}).autocomplete({
			minLength:0,
			source: function( request, response ) {
		          response( $.ui.autocomplete.filter(
		            availableTags, extractLast( request.term ) ) );
		        },
			focus: function() {
				return false;
			},
			select: function( event, ui ) {
				var terms = split( this.value );
				terms.pop();
				terms.push( ui.item.value );
				terms.push( "" );
				this.value = terms.join( ", " );
				return false;
		        }
		})
	}
});