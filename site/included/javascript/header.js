$(document).ready(function() {
	
	//GET BROWSER WINDOW SIZE
	var currWidth = $(window).width() - 150;
	var currHeight = $(window).height() - 40;
	$('#mainContentContainer').css('width', currWidth);
	$('#mainContentContainer').css('height', currHeight);
	if(window.location.search != "") {
		$('#mainContentContainerContent').css('height', currHeight-50);
	}
	//ON RESIZE OF WINDOW
	$(window).resize(function() {
		
		//GET NEW SIZE
		var currWidth = $(window).width() - 150;
		var currHeight = $(window).height() - 40;	
		//RESIZE BOTH ELEMENTS TO NEW HEIGHT
		$('#mainContentContainer').css('width', currWidth);
		$('#mainContentContainer').css('height', currHeight);
		if(window.location.search != "") {
			$('#mainContentContainerContent').css('height', currHeight-50);
		}
		
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
	// Handles the onclick listener for adding/dropping a course
	$("#courseNavBarEnrollStatus").click(function(){
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
	)});

	var containerId = '#mainContentContainerContent';
	var hash = window.location.hash;
	var tabsId;
	var url;
	if(window.location.search.indexOf('c') !== -1) {
		$('#courseNavBarInfo').addClass('selected');
		var course = $('#courseNavBarEnrollStatus').data("cid");
		$.get("../../ajax/courses/info.php",
		{ 
			c: course
		},
		function(data) {
			$(containerId).html(data);
		});
	} else if(window.location.search != "") {
		// set tabsId to requested tab
		if(hash == "#info") {
			changeSelected('#courseNavBarInfo', 'info');
		} else if(hash == "#announcements") {
			changeSelected('#courseNavBarAnnouncements', 'announcements');
		} else if(hash == "#assignments") {
			changeSelected('#courseNavBarAssignments', 'assignments');
		} else if(hash == "#grades") {
			changeSelected('#courseNavBarGrades', 'grades');
		} else if(hash == "#resources") {
			changeSelected('#courseNavBarResources', 'resources');
		} else {
			changeSelected('#courseNavBarInfo', 'info');
		}

		// Set up click listeners for the tabs
		$('#courseNavBarInfo').click(function(){
			changeSelected('#courseNavBarInfo', 'info');
		});
		$('#courseNavBarAnnouncements').click(function(){
			changeSelected('#courseNavBarAnnouncements', 'announcements');
		});
		$('#courseNavBarAssignments').click(function(){
			changeSelected('#courseNavBarAssignments', 'assignments');
		});
		$('#courseNavBarGrades').click(function(){
			changeSelected('#courseNavBarGrades', 'grades');
		});
		$('#courseNavBarResources').click(function(){
			changeSelected('#courseNavBarResources', 'resources');
		});

		// Function to handle tab changes and loading new content
		function changeSelected(selected, url) {
			$('#courseNavBarInfo').removeClass('selected');
			$('#courseNavBarAnnouncements').removeClass('selected');
			$('#courseNavBarAssignments').removeClass('selected');
			$('#courseNavBarGrades').removeClass('selected');
			$('#courseNavBarResources').removeClass('selected');
			$(selected).addClass('selected');
			if(history.pushState) {
			    history.pushState(null, 'WhiteBoard', '#'+url);
			}

			loadTab($(selected), url);
		}
		// Function to load the new tab content
		function loadTab(tabObj, url){
			var section = $('#courseNavBarEnrollStatus').data("sid");
			var action = $('#courseNavBarEnrollStatus').data("action");
			$.get("../../ajax/courses/"+ url +".php",
			{ 
				s: section,
				a: action
				
			},
			function(data) {
				if(data=="Permission Denied!") {
					// User does not have permission, redirect them back to info
					changeSelected('#courseNavBarInfo', 'info');
				} else {
					// User has permission, show them the page
					$(containerId).html(data);
				}
			}
			);
		}

	}
});
