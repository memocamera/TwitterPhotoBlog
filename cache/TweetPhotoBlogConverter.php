<?php
/**
 * Tweet Photolog用のconverter
 * tweetからhashtagと、その前の括弧にあるカメラ名を取り出して新しいtextにする
 *
 *       $hashtag = '曇時々やゝ光';
 *       $conveter = new TweetPhotoBLogConverter($hashtag);
 *       $res = $converter->convert($tweets);
 *
 */
class TweetPhotoBLogConverter
{
    protected $_hashtag;

    /**
     * @param string filterしたいhashtag
     */
    public function __construct($hashtag)
    {
        $this->_hashtag = $hashtag;
    }

    public function convert($tweets)
    {
        $convertedTweets = array();
        foreach ($tweets as $tweet) {
            if (!preg_match("/^([^\(]+)\s(?:\(([^\)]+)\)\s)?#$this->_hashtag\s/", $tweet['text'], $m)) {
                continue;
            }

            unset($tweet['text']);            // remove original tweet text
            $tweet['converted'] = array(
                'comment' => $m[1],
                'camera'  => '',
            );
            if (isset($m[2])) {   # with camera
                $tweet['converted']['camera'] = $m[2];
            }
            $convertedTweets[] = $tweet;
        }

        return $convertedTweets;
    }
}
