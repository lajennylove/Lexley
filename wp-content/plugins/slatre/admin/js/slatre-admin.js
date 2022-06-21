(function( $ ) {
	'use strict';

	$( window ).load(function() {
		// alert(" Hello , How are you Doing?");
		$(".slatreTrelloBoard").change(function () {

			var selected = $(this) ;
			var trello_board_id =  $(this).val();

			var ajaxData = {
				'action': 'slatre_ajax_response',
				'boardID': trello_board_id,
				'security': slatre_data.security
			};

			jQuery.post( slatre_data.slatreAjaxURL, ajaxData, function ( trello_board_list ) {
				console.log(trello_board_list);
				var list = JSON.parse(trello_board_list);
				console.log(list );

				if (list[0]) {
					
					$(selected).next('.slatreTrelloList').empty();
					$(selected).next('.slatreTrelloList').append(
						'<option value=""> Select a List	</option>'
					);
					jQuery.each(list[1], function (key, value) {
						$(selected).next('.slatreTrelloList').append(
							'<option value="' + key + '">' + value + "</option>"
						);
					});

				} else {
					alert("ERROR : " + list[1]);
				}

			});

		});


	});
	

})( jQuery );
