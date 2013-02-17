<?php
/**
 * iniファイルだとapacheの設定をいじらないとoauthのparamが見えてしまうため、
 * Setting.phpとして保持
 * 分かり易さを兼ねてmethodは用意しない
 */
class Setting
{
    public static $username = 'memocamera'; // ツイッターのユーザーID（例：memocamera）
    public static $hashtag  = '曇時々やゝ光'; // ツイッターのタグの名前（例：曇時々やゝ光）
    public static $count    = 300; // 最新何件のデータを残すか
    public static $sinceId  = 234864117591203840; // ここまでのtweetはとりこみたい（このtweetは入らない）

    public static $oauth = array( // OAuthの設定
        'consumer_key'        => '',
        'consumer_secret'     => '',
        'access_token'        => '',
        'access_token_secret' => '',
    );
}
