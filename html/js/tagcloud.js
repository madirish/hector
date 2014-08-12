$.fn.tagcloud.defaults = {
		size: {start: 12, end: 24, unit: 'pt'},
		color: {start: '#aaa', end: '#f52'}
		
};

$(function(){
	$("#tagcloud a").tagcloud();
})