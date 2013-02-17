<?php
require 'TweetPhotoBLogConverter.php';

class TweetPhotoBLogConverterTest extends PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provider_tweetをconvertできる
     */
    public function test_tweetをconvertできる($comment, $expected, $tweets)
    {
        $hashtag = '曇時々やゝ光';

        $converter = new TweetPhotoBLogConverter($hashtag);
        $res = $converter->convert($tweets);

        $this->assertSame($expected, $res, 'textが変換されているか');
    }

    public function provider_tweetをconvertできる()
    {
        // expected, tweet
        return array(
            array(
                '括弧もhashもあるtweetは両方使われる',
                array(
                    array(
                        'converted' => array(
                            'comment' => 'グー。',
                            'camera'  => 'Rolleiflex 2.8F',
                        ),
                    ),
                ),
                array(
                    array(
                        'text' => "グー。 (Rolleiflex 2.8F) #曇時々やゝ光 http://t.co/MDQKIF9V http://t.co/bZElVgEL",
                    ),
                ),
            ),
            array(
                '括弧がない場合にはtextは変換できてcameraが空になる',
                array(
                    array(
                        'converted' => array(
                            'comment' => 'グー。',
                            'camera'  => '',
                        ),
                    ),
                ),
                array(
                    array(
                        'text' => "グー。 #曇時々やゝ光 http://t.co/MDQKIF9V http://t.co/bZElVgEL",
                    ),
                ),
            ),
            array(
                '連続のtweetであとのtweetにhashtagがなくても1つめのtweetの情報を引き継ぐことはない',
                array(
                    array(
                        'converted' => array(
                            'comment' => 'グー。',
                            'camera'  => '',
                        ),
                    ),
                ),
                array(
                    array(
                        'text' => "グー。 #曇時々やゝ光 http://t.co/MDQKIF9V http://t.co/bZElVgEL",
                    ),
                    array(
                        'text' => "dummy",
                    ),
                ),
            ),
        );
    }
}
