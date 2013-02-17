<?php
require_once 'TweetPhotoBLog.php';
require_once 'Setting.php';

$setting = getSetting();
$model = new TweetPhotoBLog($setting);

$page = getPage();
$limit = $setting['limit'];
$offset = ($page - 1) * $limit;

if ($page <= 1) {   // if nothing or page=1, check cache
    if (!$model->hasFreshTweets()) {
        $isCached = $model->cacheTweets();
    }
}

header("Content-Type: text/javascript; charset=utf-8");
echo $model->getJsonizedTweets($offset, $limit);

function getPage()
{
    if (!isset($_GET['page'])) {
        return;
    }

    return ($_GET['page']) ? $_GET['page'] : 1;
}

function getSetting()
{
    $setting = array(
        'username' => Setting::$username,
        'hashtag'  => Setting::$hashtag,
        'count'    => Setting::$count,
        'limit'    => (isset(Setting::$limit)) ? Setting::$limit : null,

        'oauth'    => Setting::$oauth,
    );

    if (isset(Setting::$sinceId)) {
        $setting['since_id'] = Setting::$sinceId;
    }

    return $setting;
}
