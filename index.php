<?php
//register_shutdown_function(function(){ var_dump(error_get_last()); });die();

// import flight framework
require_once 'lib/flight/Flight.php';

// load config
require_once 'config.php';

// database drivers
require_once __DIR__ .'/lib/Medoo.php';
require_once __DIR__ .'/module/MySQL.php';
//require_once __DIR__ .'/module/Elastic.REST.php';
Flight::register('db', Flight::get('dbconfig')['database_type'], 
                 array(Flight::get('dbconfig'))
                );
//Flight::register('cache', Flight::get('cacheconfig')['database_type'], 
//                 array(Flight::get('cacheconfig'))
//                );
                
// geetest captcha
require_once __DIR__.'/lib/geetest3/lib/class.geetestlib.php';
Flight::register('geetest', 'GeetestLib', array(Flight::get('apiconfig')['captcha_id'],
                                                Flight::get('apiconfig')['captcha_pkey']
                                                )
                );
session_start();

// sms
require_once __DIR__ .'/module/SMS.Wangjian.php';
require_once __DIR__ .'/lib/alisms-lite/SignatureHelper.php';
require_once __DIR__ .'/module/SMS.Aliyun.php';
Flight::register('sms', Flight::get('apiconfig')['sms_api'], array(Flight::get('apiconfig')));

// mobile detect
require_once __DIR__.'/lib/MobileDetect/Mobile_Detect.php';
Flight::register('mdetect', 'Mobile_Detect');

// models
require_once __DIR__ .'/module/Application.php';
Flight::register('application', 'Application', array(Flight::db(), Flight::get('sysconfig')));
require_once __DIR__ .'/module/Login.php';
Flight::register('login', 'Login', array(Flight::db(), Flight::get('sysconfig')));
require_once __DIR__ .'/module/Register.php';
Flight::register('reg', 'Register', array(Flight::db(), Flight::sms(), Flight::get('sysconfig')));
require_once __DIR__ .'/module/Profile.php';
Flight::register('profile', 'Profile', array(Flight::db(), Flight::get('sysconfig')));
require_once __DIR__ .'/module/Permission.php';
Flight::register('perm', 'Permission', array(Flight::db(), Flight::get('sysconfig')));
require_once __DIR__ .'/module/Vip.php';
Flight::register('vip', 'Vip', array(Flight::db(), Flight::get('sysconfig')));

// useragent info
if (Flight::mdetect()->isMobile()){
    $client_type = "h5";
} else {
    $client_type = "web";
}
Flight::set('uadata',array(
	"user_id" => "register",
	"client_type" => $client_type,
	"ip_address" => Flight::request()->ip
));

// templates
Flight::set('flight.views.path', 'template');

// routes
Flight::route('/logout', function(){
    unset($_SESSION['cur_tilltime']);
    unset($_SESSION['cur_uid']);
    return false;
});

Flight::route('/@appid', function($appid){
    /*if (isset($_SESSION['cur_tilltime']) && time()<$_SESSION['cur_tilltime']){
        $token = Flight::login()->genSession($_SESSION['cur_username']);
        if ($token['code'] == 201){
            Flight::redirect('/new/location');
        }
    }
    unset($_SESSION['cur_tilltime']);
    unset($_SESSION['cur_username']);*/
    
    $appdata = Flight::application()->getAppInfo($appid);
    if ($appdata['code']==200){
        Flight::view()->set('appid', $appid);
        Flight::view()->set('appname', $appdata['data']['name']);
        Flight::render('login');
    } else {
        Flight::notFound();
    }
    return false;
});


// Beginning of apis

Flight::route('/api/*', function(){
    //header('Content-type: application/json');
    return true;
});

Flight::route('/api/login', function(){
    $appid = filter_var(Flight::request()->data->appid, FILTER_SANITIZE_NUMBER_INT);
    $username = filter_var(Flight::request()->data->username, FILTER_SANITIZE_NUMBER_INT);
    $password = Flight::request()->data->password;
    if (empty($username) || empty($password) || empty($appid)){
        Flight::json(array("code"=>444));
        return false;
    }
    $appdata = Flight::application()->getAppInfo($appid);
    if ($appdata['code']!=200) {
        Flight::json(array("code"=>404));
        return false;
    }
    if ($appdata['data']['type']=='WEB'){
        $login = Flight::login()->login($username, $password, $appdata['data']['callback']);
    } else if ($appdata['data']['type']=='NATIVE') {
        $login = Flight::login()->login($username, $password, NULL);
    }
    
    if ($login['code'] == 200){
        $_SESSION['cur_uid'] = $login['uid'];
        $_SESSION['cur_tilltime'] = time() + Flight::get('sysconfig')['session_life'];
        $profile = Flight::profile()->needUpdate($login['uid']);
        $login['profile']=$profile['code'];
    }
    Flight::json($login);
    return false;
});

Flight::route('/api/fastlogin', function(){
    $appid = Flight::request()->data->appid;
    $otpcode = filter_var(Flight::request()->data->otpcode, FILTER_SANITIZE_NUMBER_INT);
    $username = filter_var(Flight::request()->data->username, FILTER_SANITIZE_NUMBER_INT);
    if (empty($otpcode) || empty($username) || empty($appid)){
        Flight::json(array("code"=>444));
        return false;
    }
    // check app info
    $appdata = Flight::application()->getAppInfo($appid);
    if ($appdata['code']!=200) {
        Flight::json(array("code"=>404));
        return false;
    }
    // check otp num
    $otpstatus = Flight::reg()->checkOTPbySMS($username, $otpcode);
    if ($otpstatus === false) {Flight::json(array('code'=>401)); return false;}
    
    $login = Flight::login()->fastlogin($username);
    
    if ($login['code'] != 200){
        // user not exist
        //Flight::json(array("code"=>406));
        //return false;
        // temp TODO
        $reg = Flight::reg()->register($username,"");
        $login = Flight::login()->fastlogin($username);
        $login['newuser'] = true;
    } else {
        $login['newuser'] = false;
    }
    
    // user exist, continue login
    $_SESSION['cur_uid'] = $login['uid'];
    $_SESSION['cur_tilltime'] = time() + Flight::get('sysconfig')['session_life'];
    
    // vip update info
    $vipdata = Flight::vip()->getInfoByCell($username);
    if (count($vipdata)>0){
        Flight::profile()->updateInfo($_SESSION['cur_uid'], array("name"=>$vipdata[0]["name"]));
        $login['newuser'] = false;
    }
    
    $profile = Flight::profile()->needUpdate($login['uid']);
    $login['profile']=$profile['code'];
    
    Flight::json($login);
    return false;
});

Flight::route('/api/challenge',function(){
    $uid = Flight::request()->data->uid;
    $sessionid = Flight::request()->data->token;
    $appid = Flight::request()->data->appid;
    $appkey = Flight::request()->data->appkey;
    if (empty($uid) || empty($sessionid) || empty($appid) || empty($appkey)){
        Flight::json(array("code"=>444));
        return false;
    }
    if (Flight::application()->checkSecret($appid, $appkey)["code"] != 200){
        Flight::json(array("code"=>401));
        return false;
    }
    $check = Flight::login()->chkSession($sessionid, $uid);
    if ($check["code"] == 200){
        $check["data"] = Flight::profile()->getInfo($uid)[0];
        $check["data"]["permissions"] = Flight::perm()->getPerm($uid);
        $check["data"]["vip"] = Flight::vip()->getInfoByCell($check["data"]["cell"]);
    }
    Flight::json($check);
    return false;
});

Flight::route('/api/profile',function(){
    $uid = Flight::request()->data->uid;
    $appid = Flight::request()->data->appid;
    $appkey = Flight::request()->data->appkey;
    if (empty($uid) || empty($appid) || empty($appkey)){
        Flight::json(array("code"=>444));
        return false;
    }
    if (Flight::application()->checkSecret($appid, $appkey)["code"] != 200){
        Flight::json(array("code"=>401));
        return false;
    }
    $check["code"] = 200;
    $check["data"] = Flight::profile()->getInfo($uid)[0];
    $check["data"]["permissions"] = Flight::perm()->getPerm($uid);
    $check["data"]["vip"] = Flight::vip()->getInfoByCell($check["data"]["cell"]);

    Flight::json($check);
    return false;
});

Flight::route('/api/updateinfo',function(){
    $name = filter_var(Flight::request()->data->name, FILTER_SANITIZE_MAGIC_QUOTES);
    $email = filter_var(Flight::request()->data->email, FILTER_VALIDATE_EMAIL);
    if (!isset($_SESSION['cur_uid'])){
        Flight::json(array("code"=>401));
    } else if (strlen($name)<3) {
        Flight::json(array("code"=>444));
    } else {
        $req = Flight::profile()->updateInfo($_SESSION['cur_uid'], array("name"=>$name,"email"=>$email));
        Flight::json(array("code"=>$req['code']));
    }
    return false;
});

Flight::route('/api/updateinfo/app',function(){
    $uid = Flight::request()->data->uid;
    $appid = Flight::request()->data->appid;
    $appkey = Flight::request()->data->appkey;
    if (empty($uid) || empty($appid) || empty($appkey)){
        Flight::json(array("code"=>444));
        return false;
    }
    if (Flight::application()->checkSecret($appid, $appkey)["code"] != 200){
        Flight::json(array("code"=>401));
        return false;
    }
    $name = filter_var(Flight::request()->data->name, FILTER_SANITIZE_MAGIC_QUOTES);
    $email = filter_var(Flight::request()->data->email, FILTER_VALIDATE_EMAIL);
    $req = Flight::profile()->updateInfo($uid, array("name"=>$name,"email"=>$email));
    Flight::json(array("code"=>$req['code']));
    return false;
});

Flight::route('/api/smsotp',function(){
    $cellnum = filter_var(Flight::request()->data->username, FILTER_SANITIZE_NUMBER_INT);
    if (empty($cellnum)){
        Flight::json(array("code"=>444));
        return false;
    }
    
    if ($_SESSION['gtserver'] == 1){
        $captcha_res = Flight::geetest()->success_validate(Flight::request()->data->geetest_challenge,
                                                          Flight::request()->data->geetest_validate,
                                                          Flight::request()->data->geetest_seccode,
                                                          Flight::get('uadata'));
    } else {
        $captcha_res = Flight::geetest()->fail_validate(Flight::request()->data->geetest_challenge,
                                                      Flight::request()->data->geetest_validate,
                                                      Flight::request()->data->geetest_seccode);
    }
    
    if ($captcha_res) {
        Flight::json(Flight::reg()->sendOTPbySMS($cellnum));
    } else {
        Flight::json(array("code"=>401));
    }
    return false;
});

Flight::route('/api/appotp',function(){
    $cellnum = filter_var(Flight::request()->data->username, FILTER_SANITIZE_NUMBER_INT);
    $appid = Flight::request()->data->appid;
    $appkey = Flight::request()->data->appkey;
    if (empty($cellnum) || empty($appid) || empty($appkey)){
        Flight::json(array("code"=>444));
        return false;
    }
    if (Flight::application()->checkSecret($appid, $appkey)["code"] != 200){
        Flight::json(array("code"=>401));
        return false;
    }
    Flight::json(Flight::reg()->sendOTPbySMS($cellnum));
    return false;
});

Flight::route('/api/register',function(){
    $appid = filter_var(Flight::request()->data->appid, FILTER_SANITIZE_NUMBER_INT);
    $type = Flight::request()->data->type;
    $cellnum = filter_var(Flight::request()->data->username, FILTER_SANITIZE_NUMBER_INT);
    $otpcode = filter_var(Flight::request()->data->otpcode, FILTER_SANITIZE_NUMBER_INT);
    $password = Flight::request()->data->password;
    
    if (empty($cellnum) || empty($otpcode) || empty($password)){
        Flight::json(array("code"=>444));
        return false;
    }
    $appdata = Flight::application()->getAppInfo($appid);
    if ($appdata['code']!=200) {
        Flight::json(array("code"=>404));
        return false;
    }
    $otpstatus = Flight::reg()->checkOTPbySMS($cellnum, $otpcode);
    if ($otpstatus === false) {Flight::json(array('code'=>401)); return false;}
    switch ($type) {
        case 0:
            Flight::json(Flight::reg()->passwordReset($cellnum, $password));
            break;
        case 1:
            $reg = Flight::reg()->register($cellnum, $password);
            if ($reg['code']==200){
                $log = Flight::login()->login($cellnum, $password, $appdata['data']['callback']);
                $_SESSION['cur_uid'] = $log['uid'];
                $_SESSION['cur_tilltime'] = time() + Flight::get('sysconfig')['session_life'];
                Flight::json(array('code'=>200, 'login'=>$log));
            } else {
                Flight::json($reg);
            }
            break;
        default:
            Flight::json(array("code"=>444));
    }
    return false;
});

Flight::route('/api/perm/@uid',function($uid){
    $req = Flight::perm()->getPerm($uid);
    Flight::json(array("code"=>200, "data"=>$req));
    return false;
});

Flight::route('/api/vip/@cell',function($cell){
    $req = Flight::vip()->getInfoByCell($cell);
    Flight::json(array("code"=>200, "data"=>$req));
    return false;
});

Flight::route('/api/captcha',function(){
    /*$cellnum = filter_var(Flight::request()->data->username, FILTER_SANITIZE_NUMBER_INT);
    if (empty($cellnum)){
        return false;
    }*/

    $status = Flight::geetest()->pre_process(Flight::get('uadata'), 1);
    $_SESSION['gtserver'] = $status;
    $_SESSION['user_id'] = Flight::get('uadata')['user_id'];
    echo Flight::geetest()->get_response_str();
    return false;
});

Flight::route('/api/ip',function(){
    echo Flight::request()->ip;
    return false;
});

Flight::route('/api/test',function(){
    echo Flight::request()->data->uid;
    echo "\n";
    echo Flight::request()->data->token;
    echo "\n";
    echo Flight::request()->data->appid;
    echo "\n";
    echo Flight::request()->data->appkey;
});

Flight::route('/api/sys/clearotp',function(){
    Flight::json(Flight::reg()->clearOutDatedOTP());
    return false;
});

Flight::route('/api/sys/clearsid',function(){
    Flight::json(Flight::login()->clearOutDatedSID());
    return false;
});

Flight::start();

?>
