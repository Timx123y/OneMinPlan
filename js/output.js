var is_touch_device = 
	("ontouchstart" in window) 
	|| window.DocumentTouch 
	&& document instanceof DocumentTouch;
var imgModalsCreated = false;


function centerModals() {
	$('.modal').each(function(i){
	    var $clone = $(this).clone().css('display', 'block').appendTo('body');
	    var top = Math.round(($clone.height() - $clone.find('.modal-content').height()) / 2);
	    top = top > 0 ? top : 0;
	    $clone.remove();
	    $(this).find('.modal-content').css('margin-top', top);
  	});
}

function responsiveSetup() {

	// Set up or remove image modals
	if (($(window).width() >= 768) && (!imgModalsCreated)) {
		if (!imgModalsCreated) {
			// create
			$('#itin .photo img').each(function() {
				// Extract info from the img
				var imgURL = $(this).attr('src');
				var imgID = imgURL.slice(imgURL.lastIndexOf('/')+1,imgURL.lastIndexOf('.'));
				// Wrap the img with an <a>
				var $aWrap = $(this).wrap('<a class="img-clickable" data-toggle="modal"></a>').parent();
				$aWrap.attr('data-target','#'+imgID);
				// Create the modal div and append to body
				var $modaldiv = $('<img />')
					.attr('src',imgURL)
					.appendTo(document.body)
					.wrap('<div class="modal-body"></div>').parent()
					.wrap('<div class="modal-content"></div>').parent()
					.wrap('<div class="modal-dialog"></div>').parent()
					.wrap('<div class="modal fade"></div>').parent();
				$modaldiv.attr('id',imgID);
				imgModalsCreated = true;
			}); 
			// Add a modal-footer with close button to all modals
			$('.modal-body').append('<br /><button type="button" class="btn btn-default" data-dismiss="modal">Close</button>');
		
		} // if !imgModalsCreated
		centerModals();
	} else if (imgModalsCreated) {
		// Remove all modal divs
		$('.modal.fade').remove();
		// Unwrap all img-thumbnails
		$('#itin .photo img').unwrap();

		imgModalsCreated = false;
	}
	
}

/**
//
// Hotels Map
//
**/

var hkcenter = { lat: 22.3228865, lng: 114.1740887};
var hotelsMap;
var mapOptions = {
	center: hkcenter,
	zoom: 12,
	mapTypeControl: false,
	panControl: false
};

function initializeHotelsMap() {
	hotelsMap = new google.maps.Map(document.getElementById('hotel-map-canvas'),
    mapOptions);
}	

var hotelMarkers = [];

// Adds a marker to the map and push to the array.
function addHotelMarker(location) {
  var marker = new google.maps.Marker({
    position: location,
    map: hotelsMap
  });
  hotelMarkers.push(marker);
  return marker;
}


// Removes the markers from the map, but keeps them in the array.
function clearHotelMarkers() {
  for (var i = 0; i < hotelMarkers.length; i++) {
    hotelMarkers[i].setMap(null);
  }
}

// Deletes all markers in the array by removing references to them.
function deleteHotelMarkers() {
  clearHotelMarkers();
  hotelMarkers = [];
}

var hotelIWs = [];
function closeHotelIWs() {
	for (var i = 0; i < hotelIWs.length; i++) {
		hotelIWs[i].close();
	}
}


/**
//
// Address Map
//
**/

var addressMap;

function initializeAddressMap() {
	addressMap = new google.maps.Map(document.getElementById('address-map-canvas'),
    mapOptions);
}	

var addressMarkers = [];

// Adds a marker to the map and push to the array.
function addAddressMarker(location) {
  var marker = new google.maps.Marker({
    position: location,
    map: hotelsMap
  });
  addressMarkers.push(marker);
  return marker;
}

// Removes the markers from the map, but keeps them in the array.
function clearAddressMarkers() {
  for (var i = 0; i < addressMarkers.length; i++) {
    addressMarkers[i].setMap(null);
  }
}

// Deletes all markers in the array by removing references to them.
function deleteAddressMarkers() {
  clearAddressMarkers();
  addressMarkers = [];
}

var addressIWs = [];
function closeAddressIWs() {
	for (var i = 0; i < hotelIWs.length; i++) {
		addressIWs[i].close();
	}
}


$(document).ready(function() {
	responsiveSetup();
	$(window).resize(function() {
		responsiveSetup();
	});
	// Add class to body for touch / no-touch
	var touchClass = is_touch_device ? 'touch' : 'no-touch';
	$('body').addClass(touchClass);

	

	// Hotel search function
	function hotelSearch() {
		var search_str = $('#hotelModal input#search').val();
		// Do search
		if(search_str !== '') {
	        $.ajax({
	            type: "POST",
	            url: "hotelsearch.php",
	            data: { query: search_str },
	            cache: false,
	            success: function(html){
	                $("#hotels").html(html);
	                $('#hotels .hotel').each(function() {
	                 	var $hotel = $(this);
			    		var myTitle = $hotel.attr('data-title');
			    		
			    		var lat = $hotel.attr('data-latitude');
						var lng = $hotel.attr('data-longitude');
						var myLatlng = new google.maps.LatLng(lat,lng);
						var marker = addHotelMarker(myLatlng);

						function highlightHotel() {
							closeHotelIWs()
							$('#hotels .hotel').removeClass('highlight');
				  			infowindow.open(hotelsMap,marker);
							hotelsMap.setZoom(15);
					    	hotelsMap.setCenter(myLatlng);
							$hotel.addClass('highlight');
							$('#hotels').animate({ 
								scrollTop: $hotel.position().top 
							}, 800);
						}

						function dehighlightHotel() {
							$hotel.removeClass('highlight');
						}

						google.maps.event.addListener(marker,"click",highlightHotel);
			  			var infowindow = new google.maps.InfoWindow({
			  				content: myTitle
			  			});
			  			hotelIWs.push(infowindow);
				  		
						google.maps.event.addListener(infowindow, "closeclick", function()
						{					    
						    dehighlightHotel(this);
						});
						$hotel.find('.marker').click(highlightHotel);
			    	});	// $('#hotels .hotel').each()
	            } // success function
	        }); // ajax()

	    } // if searchstr !== ''
	    
	    return false;
	}

	// Back to top button
	$('body').append($('<a href="#" role="button" class="back-to-top"><i class="fa fa-arrow-up fa-2x"></i></div>'));
	var btt_offset = 300,
		btt_opacity_offset = 900,
		$btt = $('.back-to-top')

	//hide or show the "back to top" link
	$(window).scroll(function(){
		($(this).scrollTop() > btt_offset) ? $btt.addClass('visible') : $btt.removeClass('visible fade-out');
		if($(this).scrollTop() > btt_opacity_offset) { 
			$btt.blur();
			$btt.addClass('fade-out');
		}
	});
	
	$btt.click(function(e) {
		$btt.blur();
		e.preventDefault();
		$('html, body').animate({
			scrollTop: 0
		}, 700);
		return false;
	});

	// Itin AJAX Interactivity
	function showLoadingOverlay() {
		$('body').append($("<div class='loading-overlay'><div class='spinner'><i class='fa fa-circle-o-notch fa-5x fa-spin'></i></div></div>"));
	}

	function removeLoadingOverlay() {
		$('.loading-overlay').remove();
		setupListeners();
	}

	function goUp(id) {
		showLoadingOverlay();
		$.ajax({
			type: "GET",
			url: "print_itin.php",
			data: {UP: id},
			cache: false,
			success: function(html) {
				$('#full_itin').html(html);
				removeLoadingOverlay();
			}
		});
	}
	
	function goDown(id) {
		showLoadingOverlay();
		$.ajax({
			type: "GET",
			url: "print_itin.php",
			data: {DOWN: id},
			cache: false,
			success: function(html) {
				$('#full_itin').html(html);
				removeLoadingOverlay();
			}
		});
	}
	
	function changeTime(id,time) {
		showLoadingOverlay();
		$.ajax({
			type: "GET",
			url: "print_itin.php",
			data: {CHANGENODE: id, CHANGETIME: time},
			cache: false,
			success: function(html) {
				$('#full_itin').html(html);
				removeLoadingOverlay();
			}
		});
	}

	function setupListeners() {
		// Setup tooltips for icons
	
		$('[data-toggle="tooltip"]').tooltip({
			placement: 'top',
			trigger: is_touch_device ? 'click' : 'hover'
		});
		

		// Set up popovers for .trans directions
		$('[data-toggle="popover"]').popover({
			container: '#itin',
			placement: ($(window).width() < 768) ? 'top' : 'right', 
			trigger: is_touch_device ? 'click' : 'hover',
			html: 'true'
		});

		// Collapse other panels when one starts showing
		$('#suggestions .panel-collapse').on('show.bs.collapse', function() {
			var showID = $(this).attr('id');
			$('#suggestions .panel-collapse').not('#'+showID).collapse('hide');	
		});	

		// Scroll to panel heading when finishes being shown
		$('#suggestions .panel-collapse').on('shown.bs.collapse', function() {
			var navOffset = $('.navbar').height() + 6;
			var headingSelector = '#' + $(this).attr('aria-labelledby');
			$('html, body').animate({
				scrollTop: $(headingSelector).offset().top - navOffset
			}, 800);
		});

		// Set up label-suggestions interaction
		$('a.label').click(function() {
			// Get the appropriate ID
			var collapseID = $(this).attr('class');
			collapseID = collapseID.slice(collapseID.indexOf('-')+1);
			collapseID = 'collapse-' + collapseID;
			$('#'+collapseID).collapse('show');		
		});

		// Hotel Modal shown
		$('#hotelModal').on('shown.bs.modal',function(e) {
			initializeHotelsMap();
			$('#hotelModal input#search').focus();
		});

		// search box keyup function (act as buffer)
		$('#hotelModal input#search').on('keyup',function(e) {
			
			// Set Timeout
		    clearTimeout($.data(this, 'timer'));

		    // Set Search String
		    var search_string = $(this).val();

		    // Do Search
		    if (search_string == '') {
		        $('#hotels').fadeOut();
		        

		    } else {
		    	deleteHotelMarkers();
		    	$('#hotels').fadeIn();

		        $(this).data('timer', setTimeout(hotelSearch, 20));

		    };
		    
		});

		// Address Modal shown
		$('#addressModal').on('shown.bs.modal',function(e) {
			initializeAddressMap();

		});

		$('.time .up').click(function() {
			var id = $(this).data('nodeid');
			goUp(id);
			return false;
		});
		$('.time .down').click(function() {
			var id = $(this).data('nodeid');
			goDown(id);
			return false;
		});
		$('input[name=touchspin]').TouchSpin({
			min: 0.25,
			max: 4,
			step: 0.25,
			decimals: 2,
			postfix: 'h',
			verticalbuttons: true,
			verticalupclass: 'glyphicon glyphicon-plus',
	      	verticaldownclass: 'glyphicon glyphicon-minus'
		});
		$('input[name=touchspin]').blur(function() {
			var id = $(this).data('nodeid');
			var time = $(this).val() * 60;
			changeTime(id,time);
		});
		$('input[name=touchspin]').keyup(function(e){
		    if(e.keyCode == 13) // enter key
		    {
		        $(this).blur();
		    }
		});
		$('input[name=touchspin]').change(function() {
			$(this).focus();
		});
		$('.bootstrap-touchspin-up').click(function() {
			$(this).parent().siblings('input[name=touchspin]').focus();
		});
		$('.bootstrap-touchspin-down').click(function() {
			$(this).parent().siblings('input[name=touchspin]').focus();
		});
	} // setupListeners()
	setupListeners();


	

});