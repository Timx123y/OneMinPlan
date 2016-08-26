$(document).ready(function() {
	$('input#faqsearch').val('');
	var navOffset = $('.navbar').height() + 6; 
	$('body').scrollspy({ target: '#faq-sidebar', offset:navOffset });

	
  	$('#faq-sidebar').stick_in_parent({offset_top:navOffset,parent:$('#faq')});



	// restore
	function restoreFAQ() {
    $('.qa').removeClass('noMatch').fadeIn(200);
		$('.faq-category').removeClass('noResults').fadeIn(200);
    $('p.noResults').remove();
	}

	// search
	$("input#faqsearch")
      .change( function () {
        //getting search value
        var searchtext = $(this).val();
        if(searchtext) {
          // strip extra spaces
          searchtext = searchtext.replace(/[\s]+/g, ' ');
          var keywords = searchtext.split(' ');
          console.log(keywords);
          var i;
          var matches = 0;
          $('.faq-category').addClass('noResults');
          $('.qa').removeClass('match');
          $('.qa').addClass('noMatch');
          $('#faq-contents dt').each(function() {
            var dttext = $(this).text();
            var match = true;
            $(keywords).each(function() {
              var keyword = this;
              if (dttext.indexOf(keyword) < 0) {
                match = false;
              }
            });
            if (match) {
              matches++;
              $(this).parent().addClass('match');
              $(this).parent().removeClass('noMatch');
              $(this).parent().parent().removeClass('noResults');
            }
          });

          //hiding non matching lists
          $('.faq-category.noResults').slideUp();
          $('.qa.noMatch').slideUp();
          //showing matching lists
          $('p.noResults').remove();
          if (matches == 0) {
          	$('#faq-contents').append("<p class='noResults'>No results.</p>")
          } else {
          	$('.qa.match').slideDown().parent().slideDown();
          	
          }

        } else {
          //if search keyword is empty then display all the lists
          restoreFAQ();
          
        }
        return false;
      })
    .keyup( function () {
        $(this).change();
    });


    $('#faq-sidebar nav a').click(function() {
    	$('input#faqsearch').val('');
    	restoreFAQ();
    	if (location.pathname.replace(/^\//,'') == this.pathname.replace(/^\//,'') && location.hostname == this.hostname) {
		  var target = $(this.hash);
		  target = target.length ? target : $('[name=' + this.hash.slice(1) +']');
		  if (target.length) {
		    $('html,body').animate({
		      scrollTop: target.offset().top - navOffset
		    }, 750);
		    return false;
		  }
		}
    });
});