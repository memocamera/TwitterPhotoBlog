$(function(){
	var dateObj = new Date;
	$.ajax({
		url : 'cache/cache.php',
		dataType : 'json',
		success : function(json){
			var userData = '';
			$.each(json, function(){
				if(!this.entities.media || !this.entities.hashtags){
					return;
				}
				var appendData = '';
				var camera = this.converted.camera || '';
				appendData += '<article class="item"><a href="' + this.entities.media[0].expanded_url + '/large" target="_blank">';
				appendData += '<p class="comment"><span>' + this.converted.comment + '</span></p>';
				appendData += '<img class="photo" src="' + this.entities.media[0].media_url + '"></a>';
				appendData += '<ul class="photoData">';
				appendData += '<li class="date">' + dateObj.format('Y.m.d', this.created_at) + '</li>';
				appendData += '<li class="camera">' + camera + '</li>';
				appendData += '</ul>';
				appendData += '<ul class="twitterData">';
				appendData += '<li class="reply"><a href="https://twitter.com/intent/tweet?in_reply_to=' + this.id_str + '" target="_blank">Reply</a></li>';
				appendData += '<li class="retweet"><a href="https://twitter.com/intent/retweet?tweet_id=' + this.id_str + '" target="_blank">Retweet</a></li>';
				appendData += '<li class="favorite"><a href="https://twitter.com/intent/favorite?tweet_id=' + this.id_str + '" target="_blank">Favorite</a></li>';
				appendData += '</ul></article>';
				userData = '<p class="user"><a href="https://twitter.com/' + this.user.screen_name + '" target="_blank"><img width="24" src="' + this.user.profile_image_url + '">' + this.user.screen_name +'</a></p>';
				$('footer').before(appendData);
			});
			
			//Floating Layout
			var $container = $('#container');
			$container.imagesLoaded( function(){
				$container.masonry({
					itemSelector : '.item'
				});
				
				//Hide Loading & Show ID
				$('hgroup').after(userData);
				$('p.load').remove();
				
				//Fadein
				$('article').hide().each(function (i) {
					$(this).delay(i * 30).fadeIn(500);
				});
				$('footer').hide().fadeIn(1000);
			});
			
			//Comment Appear
			$('.item > a').hover(
				function () {
					$(this).children('.comment').show();
				},
				function () {
					$(this).children('.comment').hide();
				}
			);
		}
	});
});