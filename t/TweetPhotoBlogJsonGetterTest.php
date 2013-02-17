<?php
require 'TweetPhotoBLogJsonGetter.php';

class TweetPhotoBLogJsonGetterTest extends PHPUnit_Framework_TestCase
{
    public function test_あるユーザの最新のtweetsを取得してくる()
    {
        $username = 'memocamera';

        $getter = new TweetPhotoBLogJsonGetter($username);
        $tweets = $getter->get();

        $this->assertSame(200, count($tweets), '件数が正しいか');
        $this->assertSame($username, $tweets[0]['user']['screen_name'], 'usernameが正しいか');
    }

    public function test_sinceからmaxまでのtweetsを取得してくる()
    {
        $username = 'memocamera';
        $sinceId = '276842829144260609';  // 入らない
        $maxId = '277637784909533185';    // 入る

        $getter = new TweetPhotoBLogJsonGetter($username);
        $tweets = $getter->get($sinceId, $maxId);

        $this->assertSame(4, count($tweets), '指定したtweetsが取得できているか');
    }

    public function test_sinceまでのtweetsを取得してくる()
    {
        $username = 'memocamera';
        $sinceId = '235551214954237952';  // 入らない

        $getter = new TweetPhotoBLogJsonGetter($username);
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

    public function test_1_0と1_1で同じJSONが取得できる()
    {
        $setting = parse_ini_file(dirname(__FILE__) . '/../setting.ini', true);
        if (empty($setting['oauth'])) {
            $this->fail('skip 1.1 API test');
        }

        $username = 'memocamera';

        $getter1_0 = new TweetPhotoBLogJsonGetter($username);
        $getter1_1 = new TweetPhotoBLogJsonGetter($username, $setting['oauth']);
        $tweets1_0 = $getter1_0->get();
        $tweets1_1 = $getter1_1->get();

        $this->assertSame(count($tweets1_0), count($tweets1_1), '2つのtweet数が等しいか');
        foreach ($tweets1_0 as $i => $tweet) {
            $this->assertSame($tweet['id_str'], $tweets1_1[$i]['id_str'], '2つのtweetが等しいか');
        }
    }
}
