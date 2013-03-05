<?php
require_once("twitteroauth.php");

class TweetPhotologJsonGetter
{
    const TWITTER_BASE_1_1 = 'https://api.twitter.com/1.1/';
    const TWITTER_API = 'statuses/user_timeline.json';
    const TWEET_COUNT = 200;
    const MAX_REQUEST_COUNT = 16;

    protected $_username;
    protected $_twitteroauth;

    public function __construct($username, $oauth)
    {
        $this->_username = $username;

        $this->_twitteroauth = new TwitterOAuth(
            $oauth['consumer_key'],
            $oauth['consumer_secret'],
            $oauth['access_token'],
            $oauth['access_token_secret']
        );
        $this->_twitteroauth->decode_json = false;
    }

    /**
     * @param $sinceId string ここで指定された値以降のtweetを取得（このidのtweetは入らない）
     * @param $maxId string|null ここで指定された値までのtweetを取得（このidのtweetは入る）
     * @return array tweets
     */
    public function get($sinceId = null, $maxId = null, $requestCount = self::MAX_REQUEST_COUNT)
    {
        $query = array(
            'screen_name'      => $this->_username,
            'count'            => self::TWEET_COUNT,
            'include_entities' => 1,
            'include_rts'      => 1,          // これがないとcount件来ない
        );
        if (!is_null($sinceId)) {
            $query['since_id'] = $sinceId;
        }
        if (!is_null($maxId)) {
            $query['max_id'] = $maxId;
        }

        $json = $this->_get($query);
        $tweets = json_decode($json, true);
        if (!$tweets || empty($tweets)) {
            return array();
        }

        if ($this->_hasToGetOldTweets($tweets, $sinceId, $maxId, $requestCount)) {
            $tweets = $this->_mergeOldTweets($tweets, $sinceId, $requestCount);
        }

        return $tweets;
    }

    protected function _hasToGetOldTweets($tweets, $sinceId, $maxId, $requestCount)
    {
        if (count($tweets) < self::TWEET_COUNT) {
            return false;
        }

        $oldestTweet = end($tweets);
        return !is_null($sinceId)
            && ($maxId !== $oldestTweet['id_str'])
            && ($requestCount > 0);
    }

    protected function _mergeOldTweets($tweets, $sinceId, $requestCount)
    {
        $oldestTweet = end($tweets);

        $oldTweets = $this->get($sinceId, $oldestTweet['id_str'], $requestCount - 1);
        if (!empty($oldTweets) && ($oldestTweet['id_str'] === $oldTweets[0]['id_str'])) {
            array_shift($oldTweets);
        }

        return array_merge($tweets, $oldTweets);
    }

    protected function _get($query)
    {
        return $this->_twitteroauth->oAuthRequest(
            self::TWITTER_BASE_1_1 . self::TWITTER_API,
            'GET',
            $query
        );
    }
}
