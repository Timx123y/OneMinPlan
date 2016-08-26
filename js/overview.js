var is_touch_device = 
  ("ontouchstart" in window) 
  || window.DocumentTouch 
  && document instanceof DocumentTouch;
function attractionOverlay(details) {
  $('#legend-overlay').hide();
  $ao = $('#attraction-overlay');
  $ao.find('.details .marker img').attr('src',details.marker);
  $ao.find('.details h5').text(details.name);
  var $address = $ao.find('.details .address').contents();
  $address[$address.length - 1].nodeValue = ' ' + details.address;
  $ao.find('.details .label').attr('class','label ' + details.labelClass);
  $ao.find('.details .label').text(details.label);
  $ao.find('.details .description').text(details.description);

  if (details.tip != '') {
    $ao.find('.details .tip').text(details.tip);
    $ao.find('.details .tip').parent().show();
  } else {
    $ao.find('.details .tip').parent().hide();
  }
  var duration = details.duration;
  var durationstr = '';
  if (duration < 1) {
    durationstr = String(duration * 60);
    durationstr += ' mins';
  } else {
    durationstr = duration + ' hour';
    if (duration != 1) {
      durationstr += 's';
    }
  }
  $ao.find('.details .duration').text(durationstr);
  var photoURL = details.photo;
  if (photoURL != '') {
    $ao.find('.photo').hide();
    $ao.find('.photo img').attr('src',photoURL);
    $ao.find('.photo img').load(function() {
      $ao.find('.photo').fadeIn();
    });
    
  } else {
    $ao.find('.photo').hide();
  }
  $ao.fadeIn();
}

function initializeMap() {
	var mapOptions = {
		center: { lat: 22.3228865, lng: 114.1740887},
		zoom: 12,
		mapTypeControl: false,
		panControl: false
	};
	var map = new google.maps.Map(document.getElementById('map-canvas'),
    mapOptions);
	
	// Place markers
    $('#attractions .attraction').each(function() {
    	var myTitle = $(this).attr('data-title');
    	var lat = $(this).attr('data-latitude');
    	var lng = $(this).attr('data-longitude');
    	var myLatlng = new google.maps.LatLng(lat,lng);
      var visitday = $(this).attr('data-visitday');
    	var iconImg = 'img/marker-day' + visitday + '.png';
      var myLabel = $(this).attr('data-label');
      var myLabelClass = 'label-';
    	if ($(this).hasClass('artsculture')) {
        myLabelClass += 'artsculture';
    	} else if ($(this).hasClass('food')) {
        myLabelClass += 'food';
    	} else if ($(this).hasClass('fun')) {
        myLabelClass += 'fun';
    	} else if ($(this).hasClass('shopping')) {
        myLabelClass += 'shopping';
    	} else if ($(this).hasClass('nature')) {
        myLabelClass += 'nature';
    	} else if ($(this).hasClass('sights')) {
        myLabelClass += 'sights';
    	}
    	
    	var marker = new google.maps.Marker({
      		position: myLatlng,
      		map: map,
      		title: myTitle,
      		icon: iconImg
  		});
    	$(this).find('.marker-outer').append('<a></a>');
  		$(this).find('.marker-outer a').append('<img class="marker img-responsive" src="' + iconImg + '">');
  		
  		var details = 
      {
        name: myTitle,
        address: $(this).attr('data-address'),
        description: $(this).attr('data-description'),
        tip: $(this).attr('data-tip'),
        duration: $(this).attr('data-duration'),
        photo: $(this).attr('data-photo'),
        marker: iconImg,
        label: myLabel,
        labelClass: myLabelClass
      };

  		google.maps.event.addListener(marker, 'click', function() {
  			map.setZoom(15);
    		map.setCenter(marker.getPosition());
        attractionOverlay(details);
  		});
  		$(this).find('.marker-outer a').click(function() {
  			map.setZoom(15);
    		map.setCenter(marker.getPosition());
        attractionOverlay(details);
  		});
    });
}

function sortByCategory() {
  var $list = $('#attractions-list');
  var $nodes = $list.children('.attraction');
  $nodes.sort(function(a,b){
    var an = a.getAttribute('data-label'),
      bn = b.getAttribute('data-label');

    if(an > bn) {
      return 1;
    }
    if(an < bn) {
      return -1;
    }
    return 0;
  });
  $nodes.detach().appendTo($list);
}

$(document).ready(function() {
  // Add class to body for touch / no-touch
  var touchClass = is_touch_device ? 'touch' : 'no-touch';
  $('body').addClass(touchClass);
  
  $('#attraction-overlay').hide();
  $('#attraction-overlay button.close').click(function() {
    $(this).parent().hide();
    $('#legend-overlay').fadeIn();
  });
  initializeMap();
  sortByCategory();
  var navOffset = $('.navbar').height() + 6; 
  $('#map-outer').stick_in_parent({offset_top:navOffset});
  // var $mapouter = $("#map-outer");
  // var $attractions = $('#attractions');
  // var pos = $mapouter.position();   
  // var w = $mapouter.offset().left + $mapouter.width();
  //                 
  // $(window).scroll(function() {
  //     var windowpos = $(window).scrollTop();
  //     if (windowpos + navOffset >= pos.top) {
  //         $mapouter.css('position','fixed');
  //         $mapouter.css('top',navOffset+'px');
  //         //$mapouter.css('padding-right','10%');
  //         $attractions.addClass('col-sm-offset-8');
  //     } else {
  //         $mapouter.css('position','relative');
  //         $mapouter.css('top',0);
  //         //$mapouter.css('padding-right','15px');
  //         $attractions.removeClass('col-sm-offset-8');
  //     }
  // });
});