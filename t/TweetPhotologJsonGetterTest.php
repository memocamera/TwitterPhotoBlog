<?php
require 'TweetPhotologJsonGetter.php';

class TweetPhotologJsonGetterTest extends PHPUnit_Framework_TestCase
{
    protected $_oauthSetting;

    public function setUp()
    {
        include_once dirname(__FILE__) . '/../cache/Setting.php';

        foreach (Setting::$oauth as $k => $v) {
            if (empty($v)) {
                $this->fail('OAuth setting error');
            }
        }

        $this->_oauthSetting = Setting::$oauth;
    }

    public function test_あるユーザの最新のtweetsを取得してくる()
    {
        $username = 'memocamera';

        $getter = new TweetPhotologJsonGetter($username, $this->_oauthSetting);
        $tweets = $getter->get();

        $this->assertSame(200, count($tweets), '件数が正しいか');
        $this->assertSame($username, $tweets[0]['user']['screen_name'], 'usernameが正しいか');
    }

    public function test_sinceからmaxまでのtweetsを取得してくる()
    {
        $username = 'memocamera';
        $sinceId = '276842829144260609';  // 入らない
        $maxId = '277637784909533185';    // 入る

        $getter = new TweetPhotologJsonGetter($username, $this->_oauthSetting);
        $tweets = $getter->get($sinceId, $maxId);

        $this->assertSame(4, count($tweets), '指定したtweetsが取得できているか');
    }

    public function test_sinceまでのtweetsを取得してくる()
    {
        $username = 'memocamera';
        $sinceId = '235551214954237952';  // 入らない

        $getter = new TweetPhotologJsonGetter($username, $this->_oauthSetting);
        $defaultTweets = $getter->get();
        $tweets = $getter->get($sinceId);

        $this->assertNotSame(count($defaultTweets), count($tweets), '繰り返しtweetsが取得できているか');

        foreach ($tweets as $tweet) {
            if (!isset($preIdStr)) {
                $preIdStr = $tweet['id_str'];
                continue;
            }

            $this->assertTrue($preIdStr > $tweet['id_str'], 'id_strが重複なく、かつ降順になっているか');
            $preIdStr = $tweet['id_str'];
        }

        $countOfIdStr = array();
        foreach ($tweets as $tweet) {
            if (!isset($countOfIdStr[$tweet['id_str']])) {
                $countOfIdStr[$tweet['id_str']] = 0;
            }
            ++$countOfIdStr[$tweet['id_str']];
        }
        foreach ($countOfIdStr as $idStr => $count) {
            $this->assertSame(1, $count, 'id_strの重複はないか');
        }
    }
}
