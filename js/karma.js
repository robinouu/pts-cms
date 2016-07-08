$(document).ready(function (){

	$('.kscrollto').each(function (){
		var obj = $(this);
		var hash = obj.attr('id');
		location.hash = hash;
	});

	$('.kselector').change(function (){
		var obj = $(this);
		var url = obj.attr('data-url');
		if( url && obj.val() ){
			window.location.href = url+obj.val();
		}
	});

	$('.kcamera').each(function (){

		var self = $(this);
		var audio = $.trim(self.attr('data-audio')) == "true";
		var autostart = $.trim(self.attr('data-auto')) == "true";
		var currentStream = null;

		$(this).on('start', function (){
			window.AudioContext = window.AudioContext || window.webkitAudioContext;

			var context = new AudioContext();
			navigator.getUserMedia = navigator.getUserMedia || navigator.webkitGetUserMedia || navigator.mozGetUserMedia || navigator.msGetUserMedia || navigator.oGetUserMedia;
			navigator.getUserMedia({audio: audio != '', video: true}, function(stream) {
				
				currentStream = stream;
				if( audio != '' ){
					var microphone = context.createMediaStreamSource(stream);
					var filter = context.createBiquadFilter();

					// microphone -> filter -> destination.
					microphone.connect(filter);
					filter.connect(context.destination);
				}

				self.attr('src', window.URL.createObjectURL(stream));
				
			}, function () {
				// error
			});
		});

		$(this).on('stop', function (){
			console.log('stoping camera.');
			
			var MediaStream = window.MediaStream;

			if (typeof MediaStream === 'undefined' && typeof webkitMediaStream !== 'undefined') {
			    MediaStream = webkitMediaStream;
			}

			/*global MediaStream:true */
			if (typeof MediaStream !== 'undefined' && !('stop' in MediaStream.prototype)) {
			    MediaStream.prototype.stop = function() {
			        this.getAudioTracks().forEach(function(track) {
			            track.stop();
			        });

			        this.getVideoTracks().forEach(function(track) {
			            track.stop();
			        });
			    };
			}
			currentStream.stop();

		});

		if( autostart ){
			$(this).trigger('start');
		}
	});
	
	$('.kdraggable').each(function (){
		var obj = $(this);
		obj.attr('draggable', true);
		var target = $.trim(obj.attr('data-drag'));
		if( target && $(target).size() ){
			target = $(target);
			obj.on('dragstart', function (e){
				target.addClass('kdragged');
			}).on('dragover', function (e){

			}).on('dragend', function (){
				target.removeClass('kdragged');
			});
		}
	});

	$('.kwindow').each(function (){
		var obj = $(this);
		$('.act-close', obj).click(function (){
			obj.remove();
		});
		$('.act-minimize', obj).click(function (){
			obj.removeClass('maximized');
			obj.addClass('minimized');
		});
		$('.act-maximize', obj).click(function (){
			obj.removeClass('minimized');
			obj.addClass('maximized');
		})
	});
	
	var kdata_table = function (obj, data) {
		$('.ktable tbody', obj).html('');
		for( i = 0; i < data.length; ++i ){
			var c = '';
			$.each(data[i], function (k, el) {
				if( k == 'id' ){
					c += '<td><input type="checkbox" name="ids[]" value="'+el+'" /></td>';
				}else{
					c += '<td>'+el+'</td>';
				}
			});
			$('.ktable tbody', obj).append($('<tr>'+c+'</tr>'));
		}
	};

	$('.kdata').each(function (){
		var obj = $(this);
		var model = $.trim(obj.attr('data-model'));
		if( model == '' )
			return;
		// Chargement des données en AJAX
		// On a besoin du type de donnée en JSON et ses champs
		// On a besoin des données enregistrées en base

		
		$.getJSON('/karma?model='+model+'&method=get', function (json) {
			kdata_table(obj, json.data);
		});

		$('.act-add', obj).click(function (){
			$.getJSON('/karma?model='+model+'&method=add', function (json) {
				$('.kform', obj).replaceWith(json.fields);
				kdata_table(obj, json.data);
			});
		});
		$('.act-edit', obj).click(function (){
			var id = $('.ktable input[type=checkbox]:checked:first', obj).attr('value');
			if( typeof id != 'undefined' ){
				$.getJSON('/karma?model='+model+'&method=edit&id='+id, function (json) {
					$('.kform', obj).replaceWith(json.fields);
					kdata_table(obj, json.data);
				});
			}
		});
		$('.act-delete', obj).click(function (){
			var ids = $('.ktable input[type=checkbox]:checked', obj).map(function() { return this.value; }).get();
			$.getJSON('/karma?model='+model+'&method=delete&ids[]='+ids.join(','), function (json) {
				$('.kform', obj).hide();
				kdata_table(obj, json.data);
			});
		});
		
	});

	$(document).on('submit', '.kform', function (e){
		var obj = $(e.target);
		e.preventDefault();
		data = obj.serialize();
		var sBtn = $('input[type=submit]', obj);
		data += '&' + sBtn.attr('name') + '=' + sBtn.attr('value');
		$.ajax({
			url: obj.attr('action'), // Le nom du fichier indiqué dans le formulaire
			type: obj.attr('method'), // La méthode indiquée dans le formulaire (get ou post)
			data: data, // Je sérialise les données (j'envoie toutes les valeurs présentes dans le formulaire)
			dataType: 'json',
			success: function(json) { // Je récupère la réponse du fichier PHP

				if( typeof json.data != 'undefined' && obj.parents('.kdata').size() ){
					kdata_table(obj.parents('.kdata'), json.data);
				}
				if( typeof json.fields != 'undefined' )
					obj.replaceWith(json.fields);
				if( typeof json.callback != 'undefined' ){
					var cb = eval(json.callback);
					cb(obj);
				}
			}
        });
	});

	function ktime_interval(){
		$('.ktime').each(function(){

		var now = new Date();
		 
		/*var year   = now.getFullYear();
		var month    = ('0'+now.getMonth()+1).slice(-2);
		var day    = ('0'+now.getDate()   ).slice(-2);*/
		var hour   = ('0'+now.getHours()  ).slice(-2);
		var minute  = ('0'+now.getMinutes()).slice(-2);
		var second = ('0'+now.getSeconds()).slice(-2);
			$(this).html(hour + ':' + minute + ':' + second);
		});
	}

	setInterval(ktime_interval, 1000);

	if( typeof(SC) != 'undefined' ){
		SC.initialize({
			client_id: '1ff10471bedfc09d73961efeb9ca0a52',
			redirect_uri: 'http://crazyhearts.ddns.net/ia/callback'
		});
	}

	// initiate auth popup
	function ktrack_request(player, str){
		SC.connect().then(function (){
			SC.get('/tracks', {
				q: str,
			}).then(function (tracks){
				if( !tracks.length ){
					SC.get('/tracks', {tags:str}).then(function (tag_tracks){
						if( !tag_tracks.length ){
							SC.get('/tracks', {genres: str}).then(function (genre_tracks){
								if( !genre_tracks.length ){
									console.log('tracks with str ' + str + ' not found. Try using the video service.');
								}else{
									ktrack_load(player, genre_tracks);
								}
							});
						}else{
							ktrack_load(player, tag_tracks);
						}
					});
				}else{
					ktrack_load(player, tracks);
				}
			});
		});
	}


	function ktrack_load(player, tracks){
		
		var nxt = $.data(player, 'scCurrentTrack');
		if( !nxt ) nxt = 0;

		if( !tracks ) tracks = $.data(player, 'scTracks');
		
		player.src = 'http://w.soundcloud.com/player/?url='+tracks[nxt].uri;

		var loadNext = function() {
			scPlayer.play();
			scPlayer.bind(SC.Widget.Events.FINISH, function() {
				$.data(player, 'scCurrentTrack', $.data(player, 'scCurrentTrack') + 1);
				ktrack_load(player, null);
			});
		};

		scPlayer = $.data(player, 'scPlayer');
		if( !scPlayer ) {
			scPlayer = SC.Widget(player);
			scPlayer.bind(SC.Widget.Events.READY, loadNext);
		}
			
		$.data(player, 'scPlayer', scPlayer);
		$.data(player, 'scTracks', tracks);
		$.data(player, 'scCurrentTrack', 0);
	}
});


function onLoadGAPI(){
	gapi.client.setApiKey('AIzaSyBtXOkRYkuUKZL_BqhwaWj0WtiiK4fRVcc');
	gapi.client.load('youtube', 'v3', function() {
		
	});
}

