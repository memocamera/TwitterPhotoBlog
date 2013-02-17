<?php
/**
 * TweetPhotoBLogのロジッククラス
 */
require_once 'Lite.php';
require_once 'TweetPhotoBLogJsonGetter.php';
require_once 'TweetPhotoBLogFilter.php';
require_once 'TweetPhotoBLogConverter.php';

class TweetPhotoBLog {
    const CACHE_ID_OF_TWEETS  = 'twitter';
    const CACHE_ID_OF_LIFETIME  = 'lifetime';
    const LIFE_TIME = 300;

    protected $_username;
    protected $_hashtag;
    protected $_count;
    protected $_sinceId;
    protected $_oauth;

    protected $_tweets = array();

    protected $_cache;

    /**
     * @param $settingOrSettingPath array|string 設定もしくは設定が書いてあるファイルのpath
     */
    public function __construct($settingOrSettingPath)
    {
        if (is_string($settingOrSettingPath)) {
            $settingOrSettingPath = parse_ini_file($settingOrSettingPath, true);
        }

        $this->_username = $settingOrSettingPath['username'];
        $this->_hashtag = $settingOrSettingPath['hashtag'];
        $this->_count = (int)$settingOrSettingPath['count'];
        $this->_sinceId = (isset($settingOrSettingPath['since_id']))
            ? $settingOrSettingPath['since_id']
            : null;
        $this->_oauth = $settingOrSettingPath['oauth'];

        $cacheOptions = array (
            'cacheDir' => './',
            'lifeTime' => null,
        );
        $this->_cache = new Cache_Lite($cacheOptions);
    }

    /**
     * @return boolean should get tweets?
     */
    public function hasFreshTweets()
    {
        $lifetime = $this->_cache->get(self::CACHE_ID_OF_LIFETIME);
        if (!$lifetime) {
            return false;
        }

        if (time() - $lifetime <= self::LIFE_TIME) {
            return true;
        }

        $this->_tweets = json_decode($this->_cache->get(self::CACHE_ID_OF_TWEETS), true);
        return false;
    }

    /**
     * @return string json string of tweets
     */
    public function getJsonizedTweets($offset = 0, $length = null)
    {
        $tweets = $this->_cache->get(self::CACHE_ID_OF_TWEETS);
        if (empty($tweets)) {
            $tweets = '[]';
        }

        if ($length) {
            $tweets = json_encode(array_slice(json_decode($tweets, true), $offset, $length));
        }

        return $tweets;
    }

    /**
     * @return boolean キャッシュできればtrue、できなければfalse
     */
    public function cacheTweets()
    {
        if (!empty($this->_tweets)) {
            $lastestTweetOnCache = current($this->_tweets);
            $this->_sinceId = $lastestTweetOnCache['id_str'];
        }

        $convertedFilteredTweets = $this->_getConvertedFilteredTweets();
        if (!empty($convertedFilteredTweets)) {
            $this->_adjoinTweets($convertedFilteredTweets);
        }

        $isSaved = false;
        if (!empty($this->_tweets)) { // リクエストがあるたびにcacheは更新する
            $isSaved = $this->_cache->save(json_encode($this->_tweets), self::CACHE_ID_OF_TWEETS);
            $this->_cache->save(time(), self::CACHE_ID_OF_LIFETIME);
        }

        return $isSaved;
    }

    /**
     * Twitterから最新のtweetをDL + filter + convert
     * @return array converted filtered tweets
     */
    protected function _getConvertedFilteredTweets()
    {
        $getter = new TweetPhotoBLogJsonGetter($this->_username, $this->_oauth);
        $tweets = $getter->get($this->_sinceId);
        $filter = new TweetPhotoBLogFilter($this->_hashtag);
        $filteredTweets = $filter->filter($tweets);
        if (empty($filteredTweets)) {
            return array();
        }

        $converter = new TweetPhotoBLogConverter($this->_hashtag);

        return $converter->convert($filteredTweets);
    }

    /**
     * 設定した上限までtweetsを保持する
     */
    protected function _adjoinTweets($convertedFilteredTweets)
    {
        $tweets = array_merge($convertedFilteredTweets, $this->_tweets);
        if (count($tweets) > $this->_count) {
            $tweets = array_slice($tweets, 0, $this->_count);
        }

        $this->_tweets = $tweets;
    }
}
