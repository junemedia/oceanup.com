(function ( $ ) {
	"use strict";

	$(function () {

		$('.section_title').click(function(){
		  
		  $(this).siblings('.section_content').toggleClass('active');
		});
		
		$('.search_user').click(function(event){

			event.preventDefault();

			var data = {
				action: 'search_users',
				user: $('input[name="user_name"]').val()
			};
		
			// since 2.8 ajaxurl is always defined in the admin header and points to admin-ajax.php
			$.post(ajaxurl, data, function(response) {
				$('#search_results').html(response);
			});
			
			
		});
        
	});

}(jQuery));