// Function to load the new tab content, use this to reload any page on the courses tab
function loadTab(tabObj, url){
	var section = $('#courseNavBarEnrollStatus').data("sid");
	$.get("../../ajax/courses/"+ url +".php",
	{ 
		s: section
	},
	function(data) {
		if(data=="Permission Denied!") {
			// User does not have permission, redirect them back to info
			window.location.hash = '#info';
		} else if(data=="You are logged out!") {
			window.location.replace(window.location.protocol +'//'+ window.location.host);
		} else {
			// User has permission, show them the page
			$('#mainContentContainerContent').html(data);
		}
	});
}

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
		$('.navBarUserContainer').addClass('navBarUserContainerExpanded');
		$('.navBarSearchResultsContainer').hide();
		e.stopPropagation();
	});

	// Hide navBarUserContainer and show navBarSearchResultsContainer
	$('.navBarSearchBar').click(function(e) {
		$('.navBarUserContainer').removeClass('navBarUserContainerExpanded');
		$('.navBarSearchResultsContainer').show();
		e.stopPropagation();
	});

	// Hide navbarUserContainer and navBarSearchResultsContainer on click
	$(document.body).click(function() {
		$('.navBarUserContainer').removeClass('navBarUserContainerExpanded');
		$('.navBarSearchResultsContainer').hide();
	});

	// Prevent clicking on the drop down menus from closing them
	$('.navBarUserOptionsContainer').click(function(e) {
		e.stopPropagation();
	});

	$('.navBarSearchResultsContainer').click(function(e) {
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

	// Search courses on focus or on key up
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
	
	// Check to see if the user is browsing the courses page
	if(window.location.search.indexOf('c') !== -1) {
		$('#courseNavBarInfo').addClass('selected');
		var course = $('#courseNavBarEnrollInfo').data("cid");
		$.get("../../ajax/courses/info.php",
		{ 
			c: course
		},
		function(data) {
			$('#mainContentContainerContent').html(data);
		});
	// Check to see if the user is browsing their course list or a section
	} else if(window.location.search != "") {
		// handles changing the hash to the correct tab and changing the highlighting
		function changeSelected(hashValue) {
			var div;
			var url;
			if(hashValue == "#info") {
				div = '#courseNavBarInfo';
				url = 'info';
			} else if(hashValue == "#announcements") {
				div = '#courseNavBarAnnouncements';
				url = 'announcements';
			} else if(hashValue == "#assignments") {
				div = '#courseNavBarAssignments';
				url = 'assignments';
			} else if(hashValue == "#grades") {
				div = '#courseNavBarGrades';
				url = 'grades';
			} else if(hashValue == "#resources") {
				div = '#courseNavBarResources';
				url = 'resources';
			} else {
				// Hash doesn't correlate to a page, change it to the default info, which will load the default info page
				window.location.hash = '#info';
			}
			// Changes the highlighting of the tabs
			$('#courseNavBarInfo').removeClass('selected');
			$('#courseNavBarAnnouncements').removeClass('selected');
			$('#courseNavBarAssignments').removeClass('selected');
			$('#courseNavBarGrades').removeClass('selected');
			$('#courseNavBarResources').removeClass('selected');
			$(div).addClass('selected');
			// Finally send a request to load the tab if the div is not null
			if(div!=null) {
				loadTab($(div), url);
			}
		}
		// Load initial page in which the hash correlates to
		changeSelected(window.location.hash);

		// Bind hashchange so the page will change when the hash changes
		$(window).bind('hashchange', function() {
			changeSelected(window.location.hash);
		});
		
		// Set up click listeners for the tabs
		$('#courseNavBarInfo').click(function(){
			if(window.location.hash == '#info') {
				loadTab($('#info'), 'info');
			}else {
				window.location.hash = '#info';
			}
		});
		$('#courseNavBarAnnouncements').click(function(){
			if(window.location.hash == '#announcements') {
				loadTab($('#announcements'), 'announcements');
			}else {
				window.location.hash = '#announcements';
			}
		});
		$('#courseNavBarAssignments').click(function(){
			if(window.location.hash == '#assignments') {
				loadTab($('#assignments'), 'assignments');
			}else {
				window.location.hash = '#assignments';
			}
		});
		$('#courseNavBarGrades').click(function(){
			window.location.hash = '#grades';
			if(window.location.hash == '#grades') {
				loadTab($('#grades'), 'grades');
			}else {
				window.location.hash = '#grades';
			}
		});
		$('#courseNavBarResources').click(function(){
			window.location.hash = '#resources';
			if(window.location.hash == '#resources') {
				loadTab($('#resources'), 'resources');
			}else {
				window.location.hash = '#resources';
			}
		});
	}

	// Handles the new assignment
	// Attach a submit handler to the form
	//callback handler for form submit
	$("#newAssignment").submit(function(e){
	    e.preventDefault();
	});
});
