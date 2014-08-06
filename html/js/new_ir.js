$(function(){
	var availableTags = [];
	var tags_json = $.parseJSON($("#availableTags").text());
	for (var tag in tags_json){
		availableTags.push(tag);
	}
	function split( val ) {
        return val.split( /,\s*/ );
        }
	function extractLast( term ) {
		return split( term ).pop();
		}
      
	$("#incidentTags").bind("keydown", function(event){
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
			console.log(ui.item.value);
			terms.pop();
			terms.push( ui.item.value );
			terms.push( "" );
			this.value = terms.join( ", " );
			var oldValue = $("#selectedTags").val();
			$("#selectedTags").val( oldValue + (oldValue ? ",":"") + tags_json[ui.item.value]);
			return false;
	        }
	})
})