<?php
require 'TweetPhotoBLog.php';

class TestTweetPhotoBLog extends TweetPhotoBLog
{
    public function getSetting()
    {
        if (!$this->_oauth) {
            $oauth = null;
        }
        return array(
            'username' => $this->_username,
            'hashtag'  => $this->_hashtag,
            'count'    => $this->_count,
            'oauth'    => $oauth,
        );
    }
}

class TweetPhotoBLogTest extends PHPUnit_Framework_TestCase
{
    public function test_iniファイルのpathを渡すとsettingとして保持する()
    {
        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 300,
            'oauth' => null,
        );

        $settingPath = dirname(__FILE__) . '/dummy_setting.ini';
        file_put_contents($settingPath, sprintf("username=%s\nhashtag=%s\ncount=%s\n\n[oauth]\n",
            $setting['username'],
            $setting['hashtag'],
            $setting['count']
        ));

        $model = new TestTweetPhotoBLog($settingPath);
        $actual = $model->getSetting();

        $this->assertSame($setting, $actual, 'settingにデータが入っているか');

        unlink($settingPath);
    }

    public function test_twitterからデータを取得しcacheできる()
    {
        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 300,
            'oauth' => null,
        );
        $model = new TweetPhotoBLog($setting);
        $isCached = $model->cacheTweets();

        $this->assertTrue($isCached, 'cacheできたか');
    }

    public function test_count件数分しかcacheされない()
    {
        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 10,
            'oauth' => null,
        );
        $model = new TweetPhotoBLog($setting);
        $model->cacheTweets();
        $json = $model->getJsonizedTweets();

        $tweets = json_decode($json, true);
        $this->assertSame($setting['count'], count($tweets), 'cacheされている件数が正しいか');
    }

    public function test_sinceIdが指定されているときはそのidまで取得をする()
    {
        $expectedIdStr = '237508887245377536';

        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 300,
            'since_id' => '237361426266742786',
            'oauth' => null,
        );
        $model = new TweetPhotoBLog($setting);
        $model->cacheTweets();
        $json = $model->getJsonizedTweets();
        $tweets = json_decode($json, true);

        $lastTweet = end($tweets);
        $this->assertSame($expectedIdStr, $lastTweet['id_str'], 'since_idまで取得できたか');

        return $model;
    }

    /**
     * @depends test_sinceIdが指定されているときはそのidまで取得をする
     */
    public function test_pager用に取得数を決められる($model)
    {
        $expectedCount = 10;
        $tweets = $model->getJsonizedTweets(0, $expectedCount); // offset, length
        $this->assertSame($expectedCount, count(json_decode($tweets, true)), '数が正しいか');
    }

    public function test_空配列のときはcacheしない()
    {
        $cacheFile = 'cache_c21f969b5f03d33d43e04f8f136e7682_79f3a982fd2496a50c5ec8f88e9b608f';
        if (file_exists($cacheFile)) {
            unlink($cacheFile);
        }

        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 300,
            'since_id' => '937361426266742786', // あり得ないくらいでかいid
            'oauth' => null,
        );
        $model = new TweetPhotoBLog($setting);
        $isCached = $model->cacheTweets();

        $this->assertTrue(!$isCached, 'cacheされなかったか');
    }

    /**
     * @depends test_twitterからデータを取得しcacheできる
     */
    public function test_cacheがあるときはcacheを返す()
    {
        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 300,
            'oauth' => null,
        );
        $model = new TweetPhotoBLog($setting);
        $json = $model->getJsonizedTweets();

        $tweets = json_decode($json, true);
        $this->assertSame($setting['username'], $tweets[0]['user']['screen_name'], 'tweetが取得できているか');
    }

    /**
     * @dataProvider provider_tweetsの取得タイミングの判別ができる
     */
    public function test_tweetsの取得タイミングの判別ができる($comment, $expected, $cacheData)
    {
        $this->_setUpCacheData($cacheData);

        $setting = array(
            'username' => 'memocamera',
            'hashtag'  => '曇時々やゝ光',
            'count'    => 300,
            'oauth' => null,
        );
        $model = new TweetPhotoBLog($setting);
        $hasFreshTweets = $model->hasFreshTweets();

        $this->assertSame($expected, $hasFreshTweets, $comment);
    }

    public function provider_tweetsの取得タイミングの判別ができる()
    {
        // comment, expected, $cacheData
        return array(
            array(
                'cacheがなければfalseになっているか',
                false,
                null,
            ),
            array(
                '閾値を超えてない場合にはtrueになっているか',
                true,
                time(),
            ),
            array(
                '閾値を超えている場合にはfalseになっているか',
                false,
                time() - TweetPhotoBLog::LIFE_TIME - 1,
            ),
        );
    }

    protected function _setUpCacheData($data)
    {
        include_once('Lite.php');
        $cacheOptions = array (
            'cacheDir' => './',
            'lifeTime' => 10,
        );
        $cache = new Cache_Lite($cacheOptions);
        if (!is_null($data)) {
            $cache->save($data, TweetPhotoBLog::CACHE_ID_OF_LIFETIME);
        } else {
            if ($cache->get(TweetPhotoBLog::CACHE_ID_OF_LIFETIME)) {
                $cache->remove(TweetPhotoBLog::CACHE_ID_OF_LIFETIME);
            }
        }
    }
}
