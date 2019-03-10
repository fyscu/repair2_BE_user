<?php
date_default_timezone_set('Asia/Shanghai');

Flight::set('dbconfig', [
	// required
	'database_type' => 'MySQL',
	'database_name' => 'fy_user',
    'server' => 'localhost',
    'port' => 3306,
	'username' => '',
	'password' => '',
	'charset' => 'utf8',

	// driver_option for connection, read more from http://www.php.net/manual/en/pdo.setattribute.php
	'option' => [
		PDO::ATTR_CASE => PDO::CASE_NATURAL
	]
]);

// 2018.8 Discarded
Flight::set('cacheconfig', [
	// required
	'database_type' => 'Elastic_REST',
    'server' => 'localhost',
    'port' => 9200,
	'username' => '',
	'password' => '',
	'charset' => 'utf8',
]);

Flight::set('sysconfig',[
    'user_type' => 'username',
    'user_id' => 'user_id',
    'local_auth_table' => 'LocalAuth',
    'user_info_table' => 'UserInfo',
    'app_table' => 'App',
    'role_table' => 'Role',
    'perm_table' => 'Permission',
    'role_perm_table' => 'Role_Perm',
    'user_role_table' => 'User_Role',
    'otp_table' => 'OTP',
    'sid_table' => 'Session',
    'wechat_table' => 'WeChatAuth',
    'vip_table' => 'VIP',
    
    // lifetime(s)
    'session_life' => 259200,
    'token_life' => 86399,
    'otp_cell_life' => 599,
    'otp_cell_frequency' => 149,

]);

Flight::set('apiconfig',[
    // sms api
    'sms_api' => "SMS_Aliyun",
    'sms_accessid' => "",
    'sms_access_secret' => "",
    'sms_sign' => "飞扬俱乐部",
    'sms_template' => "",
    
    /*
    'sms_api' => "SMS_Wangjian",
    'sms_accessid' => "",
    'sms_access_secret' => "",
    'sms_sign' => "飞扬俱乐部",
    'sms_template' => "",
    */

    // geetest api
    'captcha_id' => "",
    'captcha_pkey' => "",
    
    // wechat_miniapp api
    'wma_appid' => '',
    'wma_appsecret' => ''
]);