var is_touch_device = 
	("ontouchstart" in window) 
	|| window.DocumentTouch 
	&& document instanceof DocumentTouch;
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
	// Set up the select2 in the #basicform
	// $('#city').select2({
	// 	placeholder: 'Where are you going?'
	// });
	// $('#days').select2({
	// 	placeholder: 'No. of days'
	// });
	// $('#starttime').select2({
	// 	placeholder: 'Start my day at'
	// });
	// $('#pace').select2({
	// 	placeholder: 'Pace'
	// });
	// Change the layout of #finetune-form
	if ($(window).width() < 768) {
		$('#finetune-form .btn-group').removeClass('btn-group').addClass('btn-group-vertical');
	} else {
		$('#finetune-form .btn-group-vertical').removeClass('btn-group-vertical').addClass('btn-group');
	}
	// Center modals
	centerModals();
}

function validateBasicForm() {
	// $('#basicform .alert-warning').removeClass('in');
	// $('#basicform .alert-warning').html('');
	// var alertMsg = '';
	// if (($('#city').val() == null) || ($('#city').val() == '')) {
	// 	alertMsg += '<p>Please select a city</p>';
	// } 
	// if (($('#days').val() == null) || ($('#days').val() == '')) {
	// 	alertMsg += '<p>Please select number of days</p>';
	// } 
	// if (($('#starttime').val() == null) || ($('#starttime').val() == '')) {
	// 	alertMsg += '<p>Please select starting time</p>';
	// } 
	// if (($('#pace').val() == null) || ($('#pace').val() == '')) {
	// 	alertMsg += '<p>Please select a pace</p>';
	// }
	// if (alertMsg == '') {
	// 	return true;
	// } else {
	// 	$('#basicform .alert-warning').append(alertMsg);
	// 	$('#basicform .alert-warning').addClass('in');
	// 	return false;
	// }
	return true;
} // validateBasicForm()

function validateFinetune() {
	$('#finetune .alert-warning').removeClass('in');
	$('#finetune .alert-warning').html('');
	$('#finetune .warning').removeClass('warning');
	var alertMsg = '';
	


	if (alertMsg == '') {
		return true;
	} else {
		$('#finetune .alert-warning').append(alertMsg);
		$('#finetune .alert-warning').addClass('in');
		return false;
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

	// Functionality for #btn-next
	$('#btn-next').click( function() {
		if (validateBasicForm()) {
			$('#btn-next').hide();
			$("#finetune").collapse('show');	
		}
	});

	// Auto-validate form when alert is shown
	$('#basicform select').change(function() {
		if ($('#basicform .alert-warning').hasClass('in')) {
			validateBasicForm();
		}
	});

	$('form').submit(function(e) {
		if (validateBasicForm() && validateFinetune()) {
			return;
		}
		e.preventDefault();
	});

	// Auto-validate form when alert is shown
	$('#finetune input:radio').change(function() {
		if ($('#finetune .alert-warning').hasClass('in')) {
			validateFinetune();
		}
	});

	// Functionality for #btn-submit
	// $('.modal').on('show.bs.modal', centerModals);
	// $('#btn-submit').click( function() {
	// 	$("#modal-submit").modal("show");
	// 	var i = 0;
	// 	var setIn = setInterval(function() {
	// 		i += 10;
	// 		if (i <= 100) {
	// 			$(".progress-bar").attr('valuenow',i).css('width',i.toString()+'%');
	// 		} else {
	// 			clearTimeout(setIn);
	// 			window.location.href = 'output.html';
	// 		}

	// 	}, 250);

		
	// });

	// Smooth scroll
	$('a[href*=#]:not([href=#])').click(function() {
		if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
		  var target = $(this.hash);
		  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
		  if (target.length) {
		    $('html,body').animate({
		      scrollTop: target.offset().top
		    }, 750);
		    return false;
		  }
		}
	});
});