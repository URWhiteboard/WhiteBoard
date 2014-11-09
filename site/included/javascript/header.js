$(document).ready(function() {
	
	//GET BROWSER WINDOW SIZE
	var currWidth = $(window).width() - 150;
	var currHeight = $(window).height() - 30;
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
	$(".sectionEnrollStatus").click(function(){
		var action;
		if($('.sectionEnrollStatus').html() == "Add section") {
			action = "a";
		}else if($('.sectionEnrollStatus').html() == "Drop section"){
			action = "r";
		}
		changeSection(action)
	});
	// Function to add and remove a section
	function changeSection(action){
		var section = $('.sectionEnrollStatus').data("sid");
		console.log(section);
		$.get("../../ajax/changeSection.php",
		{ 
			s: section,
			a: action
			
		},
		function(data) {
			$('.sectionEnrollStatus').html(data);
		}

	)};
});