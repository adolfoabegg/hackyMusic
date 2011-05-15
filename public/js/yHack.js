yHack = {
	init : function() {
		if (navigator.geolocation) {
			$('#search').val('Searching your location');

			navigator.geolocation.getCurrentPosition(
				function(position) {
					$.ajax({
						url : '/ajax/geolocation/?latitude=' + position.coords.latitude + '&longitude=' + position.coords.longitude,
						success : function(response) {
							$('#search').val(response);

							yHack.search();
						}
					});
				},
				function() {
					$('#search').val('');	
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
