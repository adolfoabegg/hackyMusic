yHack = {
	init : function() {
        this.loadLocation();
	},

    loadLocation : function() {
		if ($.cookie('customLocation')) {
            $('#search').val($.cookie('customLocation'));
            this.search();
        } else if (navigator.geolocation) {
			navigator.geolocation.getCurrentPosition(
				function(position) {
					$.ajax({
						url : '/ajax/geolocation',
                        data : {
                            latitude: position.coords.latitude,
                            longitude: position.coords.longitude
                        },
						success : function(response) {
							$('#search').val(response);
							yHack.search();
						}
					});
				},
				function() {
				}
			);	
		}
    },

    showSpinner : function() {
        $('#spinner').show();
    },
    hideSpinner : function() {
        $('#spinner').hide();
    },

    highlightAutocompleteItem : function(s, t) {
        var matcher = new RegExp("(" + $.ui.autocomplete.escapeRegex(t) + ")", "ig");
        return s.replace(matcher, "<strong>$1</strong>");
    },

	search : function() {
        this.showSpinner();

        $.ajax({
            url : '/ajax/fetch-songs',
            data : {
                address: $('#search').val()
            },
            success : function(response) {
                yHack.hideSpinner();

                $('#playlist .overview').empty();

                for (var i in response['songs']) {
                    yHack.addSong(response['songs'][i]);
                }

                $('.song').click(function() {
                    yHack.play($(this));
                });

                $('#playlist').fadeIn();
                scrollport.tinyscrollbar_update();

                yHack.play($('#playlist .song').first());

                $('body')[0].className = response['weatherClass'];

                if (!response['isDaytime']) {
                    $('body').addClass('moon');
                }
            }
        });
	},

    addSong : function(e) {
        var song = $(
            '<div class="song" data-video="' + e['youtubeId'] + '">' +
                '<img width="34" src="' + e['albumImage'] + '" class="cover" />' +
                '<div class="title">' + e['track'] + '</div>' +
                '<div class="artist">' + e['artist'] + '</div>' +
            '</div>'
        );

        $('#playlist .overview').append(song);
    },

    play : function(nextSong) {
        var lastSong = $('.song.playing');
        lastSong.removeClass('playing');

        if (nextSong) {
            $('#youtube').show();

            if (nextSong.data('start')) {
                player.loadVideoById(nextSong.data('video'), nextSong.data('start'));
            } else {
                player.loadVideoById(nextSong.data('video'));
            }

            nextSong.addClass('playing');
        } else {
            $('#youtube').hide();
        }
    }
};


$(function() {
	yHack.init();
});
