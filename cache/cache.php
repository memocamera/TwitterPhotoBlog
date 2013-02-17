<?php
require_once 'TweetPhotolog.php';
require_once 'Setting.php';

$model = new TweetPhotolog(getSetting());
if (!$model->hasFreshTweets()) {
    $isCached = $model->cacheTweets();
}

header("Content-Type: text/javascript; charset=utf-8"); 
echo $model->getJsonizedTweets();

function getSetting()
{
    $setting = array(
        'username' => Setting::$username,
        'hashtag'  => Setting::$hashtag,
        'count'    => Setting::$count,

        'oauth'    => Setting::$oauth,
    );

    if (isset(Setting::$sinceId)) {
        $setting['since_id'] = Setting::$sinceId;
    }

    return $setting;
}
