<?php
require 'TweetPhotoBLogFilter.php';

class TweetPhotoBLogFilterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider_tweetをfilterできる
     */
    public function test_tweetをfilterできる($comment, $expected, $tweet)
    {
        $hashtag = '曇時々やゝ光';
        $filter = new TweetPhotoBLogFilter($hashtag);
        $res = $filter->filter(array($tweet));

        $this->assertSame($expected, !empty($res), $comment);
    }

    public function provider_tweetをfilterできる()
    {
        // expected, tweet
        return array(
            array(
                '正しいtweetはtrue',
                true,
                array(
                    'created_at' => "Sun Dec 09 02:16:07 +0000 2012",
                    'id_str'     => "277597458555609088",
                    'text'       => "グー。 (Rolleiflex 2.8F) #曇時々やゝ光 http://t.co/MDQKIF9V http://t.co/bZElVgEL",
                    'entities'   => array(
                        'hashtags' => array(
                            array(
                                'text' => "曇時々やゝ光",
                            ),
                        ),
                        'media'    => array(
                            array(
                                'media_url' => "http://pbs.twimg.com/media/A9o5avQCIAAoWgi.jpg",
                                'expanded_url' => "http://twitter.com/memocamera/status/277597458555609088/photo/1",
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'hashtagが違う場合はfalse',
                false,
                array(
                    'created_at' => "Sun Dec 09 02:16:07 +0000 2012",
                    'id_str'     => "277597458555609088",
                    'text'       => "グー。 (Rolleiflex 2.8F) #test http://t.co/MDQKIF9V http://t.co/bZElVgEL",
                    'entities'   => array(
                        'hashtags' => array(
                            array(
                                'text' => "test",
                            ),
                        ),
                        'media'    => array(
                            array(
                                'media_url' => "http://pbs.twimg.com/media/A9o5avQCIAAoWgi.jpg",
                                'expanded_url' => "http://twitter.com/memocamera/status/277597458555609088/photo/1",
                            ),
                        ),
                    ),
                ),
            ),
            array(
                'mediaがない場合はfalse',
                false,
                array(
                    'created_at' => "Sun Dec 09 02:16:07 +0000 2012",
                    'id_str'     => "277597458555609088",
                    'text'       => "グー。 (Rolleiflex 2.8F) #曇時々やゝ光 http://t.co/MDQKIF9V http://t.co/bZElVgEL",
                    'entities'   => array(
                        'hashtags' => array(
                            array(
                                'text' => "曇時々やゝ光",
                            ),
                        ),
                    ),
                ),
            ),
        );
    }
}
