var TweetPhotoBLog = {
	limit: 10,
	currentPage: 1,
	userData: '',

	$footer: null
};
TweetPhotoBLog.init = function() {
	this.$footer = $('footer');
};
TweetPhotoBLog.getCache = function(page, selector) {
	if (!page) {
		page = 1;
	}
	TweetPhotoBLog.currentPage = page;

	$.ajax({
		url : 'cache/cache.php?page=' + page,
		dataType : 'json',
		success : function(json){
			var article = '',
				$container,
				$newImages;

			$.each(json, function(){
				if(!this.entities.media || !this.entities.hashtags){
					return;
				}

				if (!TweetPhotoBLog.userData) {
					TweetPhotoBLog.userData = TweetPhotoBLog._createUserData(this);
				}

				article += TweetPhotoBLog._createArticle(this);
			});

			if (!TweetPhotoBLog._isLoadedAllTweets(json)) {
				article += '<article class="item" id="more" data-next-page="' + (page + 1) + '"><p><a>+ View More</a></p></article>';
			}
			$(selector).after(article);
			
			TweetPhotoBLog._layoutElements();
			TweetPhotoBLog._showComment();
		}
	});
};
TweetPhotoBLog._createUserData = function(tweet) {
	return '<p class="user">'
		+ '<a href="https://twitter.com/' + tweet.user.screen_name + '" target="_blank">'
		+ '<img width="24" src="' + tweet.user.profile_image_url + '">'
		+ tweet.user.screen_name
		+ '</a></p>';
};
TweetPhotoBLog._createArticle = function(tweet) {
	console.log(this);
	var dateObj = new Date,
		camera = tweet.converted.camera || '',
		article = '<article class="item page' + TweetPhotoBLog.currentPage + '">'
				+ '<a href="' + tweet.entities.media[0].expanded_url + '/large" target="_blank">'
				+ '<p class="comment"><span>' + tweet.converted.comment + '</span></p>'
				+ '<img class="photo" src="' + tweet.entities.media[0].media_url + '"></a>'
				+ '<ul class="photoData">'
				+ '<li class="date">' + dateObj.format('Y.m.d', tweet.created_at) + '</li>'
				+ '<li class="camera">' + camera + '</li>'
				+ '</ul>'
				+ '<ul class="twitterData">'
				+ '<li class="reply"><a href="https://twitter.com/intent/tweet?in_reply_to=' + tweet.id_str + '" target="_blank">Reply</a></li>'
				+ '<li class="retweet"><a href="https://twitter.com/intent/retweet?tweet_id=' + tweet.id_str + '" target="_blank">Retweet</a></li>'
				+ '<li class="favorite"><a href="https://twitter.com/intent/favorite?tweet_id=' + tweet.id_str + '" target="_blank">Favorite</a></li>'
				+ '</ul></article>';

	return article;
};
TweetPhotoBLog._isLoadedAllTweets = function(tweets) {
	return tweets.length < this.limit;
}
TweetPhotoBLog._layoutElements = function() {
	//Floating Layout
	var $container = $('#container'),
		$newImages;

	if (TweetPhotoBLog.currentPage === 1) {
		$container.imagesLoaded( function(){
			$container.masonry({
				itemSelector : '.item'
			});
			
			//Hide Loading & Show ID
			$('hgroup').after(TweetPhotoBLog.userData);
			$('p.load').remove();
			
			//Fadein
			$('article.page' + TweetPhotoBLog.currentPage).hide().each(function (i) {
				$(this).delay(i * 30).fadeIn(500);
			});
			$('#more').hide().fadeIn(1000);
			$('footer').hide().fadeIn(1000);
		});
	} else {
		$newImages = $('.item.page' + TweetPhotoBLog.currentPage);
		$newImages.imagesLoaded(function(){
			$('#more').after(TweetPhotoBLog.$footer);

			$container.masonry('appended', $newImages.add('#more').add(TweetPhotoBLog.$footer), true);

			//Fadein
			$newImages.hide().each(function (i) {
				$(this).delay(i * 30).fadeIn(500);
			});
			$('#more').hide().fadeIn(1000);
			$('footer').hide().fadeIn(1000);
		});
	}
};
TweetPhotoBLog._showComment = function() {
	//Comment Appear
	$('.item > a').hover(
		function () {
			$(this).children('.comment').show();
		},
		function () {
			$(this).children('.comment').hide();
		}
	);
};

$(function(){
	$(document).on('mousedown', '#more', function() {
		var $this = $(this),
			nextPage = $this.data('next-page');

		$('footer').remove();
		$this.remove();
		$('#container').masonry('reload');
		TweetPhotoBLog.getCache(nextPage, '.item.page' + (nextPage - 1) + ':last');
	});

	TweetPhotoBLog.init();
	TweetPhotoBLog.getCache(1, 'header');
});
