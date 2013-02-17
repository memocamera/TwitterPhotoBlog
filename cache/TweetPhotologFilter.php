<?php
/**
 * Tweet Photolog用のfilter
 * 決まったhashtagとmediaデータがあるtweetだけを返す
 *
 *       $hashtag = '曇時々やゝ光';
 *       $filter = new TweetPhotologFilter($hashtag);
 *       $res = $filter->filter($tweets);
 *
 */
class TweetPhotologFilter
{
    protected $_hashtag;

    /**
     * @param string filterしたいhashtag
     */
    public function __construct($hashtag)
    {
        $this->_hashtag = $hashtag;
    }

    public function filter($tweets)
    {
        $filteredTweets = array();
        foreach ($tweets as $tweet) {
            if (empty($tweet['entities'])
                || empty($tweet['entities']['media'])
                || empty($tweet['entities']['hashtags'])) {
                continue;
            }

            if ($tweet['entities']['hashtags'][0]['text'] !== $this->_hashtag) {
                continue;
            }

            $filteredTweets[] = $tweet;
        }

        return $filteredTweets;
    }
}
