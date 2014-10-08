$(document).ready(function() {
	
	//GET BROWSER WINDOW SIZE
	var currWidth = $(window).width() - 170;
	var currHeight = $(window).height() - 60;
	$('#mainContentContainer').css('width', currWidth);
	$('#mainContentContainer').css('height', currHeight);
	
	//ON RESIZE OF WINDOW
	$(window).resize(function() {
		
		//GET NEW SIZE
		var currWidth = $(window).width() - 170;
		var currHeight = $(window).height() - 60;	
		//RESIZE BOTH ELEMENTS TO NEW HEIGHT
		$('#mainContentContainer').css('width', currWidth);
		$('#mainContentContainer').css('height', currHeight);
		
	});
	// NavBarUserContainer pop up logout and other functions on click
	$('.navBarUserContainer').click(function(e) {
        $('.navBarUserContainer').toggleClass('navBarUserContainerExpanded');
		e.stopPropagation();
	});

	$(document.body).click(function() {
		$('.navBarUserContainer').removeClass('navBarUserContainerExpanded');
	});

	$('.navBarUserOptionsContainer').click(function(e) {
		e.stopPropagation();
	});
});