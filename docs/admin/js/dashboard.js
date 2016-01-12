
// MAIN DOC READY JQUERY
$(document).ready(function() {  

	
	$("nav select").change(function() {
	  window.location = $(this).find("option:selected").val();
	});
	
	/* make the whole li button clickable */
	$('ul.menu-icons li').bind('click', function() {
		window.location = $(this).find('a').attr('href');
		return false;
	});

	/* stop bubble */
	$('ul.menu-icons li a').bind('click', function(event) {
		event.preventDefault ? event.preventDefault() : event.returnValue = false;
	});
});