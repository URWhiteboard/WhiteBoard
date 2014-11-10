$(document).ready(function() {
	
	//GET BROWSER WINDOW SIZE
	var currWidth = $(window).width() - 150;
	var currHeight = $(window).height() - 40;
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
	// NavBarUserContainer shown on click and hide navBarSearchResultsContainer
	$('.navBarUserContainer').click(function(e) {
        $('.navBarUserContainer').toggleClass('navBarUserContainerExpanded');
        $('.navBarSearchResultsContainer').hide();
		e.stopPropagation();
	});
	// Hide navbarUserContainer and navBarSearchResultsContainer on mousedown
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
	// Function to retrieve and show the search results live
	function searchCourses(value){
		$.get("../../ajax/search.php",
		{ 
			s: value
		},
		function(data) {
			$('.navBarSearchResultsContainer').html(data);
		}

	)};
	$(".navBarSearchBar").focus(function(){
		searchCourses($('.navBarSearchBar').val())
	});
	$(".navBarSearchBar").keyup(function(){
		searchCourses($('.navBarSearchBar').val())
	});
	$("#courseNavBarEnrollStatus").click(function(){
		changeSection()
	});
	// Function to add and remove a section
	function changeSection(){
		var section = $('#courseNavBarEnrollStatus').data("sid");
		var action = $('#courseNavBarEnrollStatus').data("action");
		$('#courseNavBarEnrollStatus').css('cursor', 'default');

		$.get("../../ajax/changeSection.php",
		{ 
			s: section,
			a: action
			
		},
		function(data) {
			$('#courseNavBarEnrollStatus').html(data);
			setTimeout(function(){window.location.reload(true)}, 1000);
		}
	)};
});