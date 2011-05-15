yHack = {
	init : function() {
		if (navigator.geolocation) {
			$('input[name=searchbox]').val('Searching your location');

			navigator.geolocation.getCurrentPosition(
				function(position) {
					$.ajax({
						url : '/ajax/geolocation/?latitude=' + position.coords.latitude + '&longitude=' + position.coords.longitude,
						success : function(response) {
							$('input[name=searchbox]').val(response);

							yHack.search();
						}
					});
				},
				function() {
					$('input[name=searchbox]').val('');	
				}
			);	
		}
	},

	search : function() {
	}
};


$(function() {
	yHack.init();
});
