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
        $('.navBarSearchResultsContainer').hide();
		e.stopPropagation();
	});

	$(document.body).click(function() {
		$('.navBarUserContainer').removeClass('navBarUserContainerExpanded');
		$('.navBarSearchResultsContainer').hide();
	});
	$('.navBarUserOptionsContainer').click(function(e) {
		e.stopPropagation();
	});

	$('.navBarSearchBar').focus(function(e) {
		$('.navBarSearchResultsContainer').show();
		e.stopPropagation();
	})
	$('.navBarSearchContainer').click(function(e) {
		e.stopPropagation();
	});
	function searchCourses(value){
		$.get("../../search.php",
		{ 
			s: value
			
		},
		function(data) {
			$('.navBarSearchResultsContainer').html(data);
		}

	)};
	$(".navBarSearchBar").click(function(){
		searchCourses($('.navBarSearchBar').val())
	});
	$(".navBarSearchBar").keyup(function(){
		searchCourses($('.navBarSearchBar').val())
	});
});