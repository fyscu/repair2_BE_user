<?php
require_once("Util.php");


// 2018.8 Not Use
class WeChat {
    private $_url = "https://api.weixin.qq.com/sns/jscode2session?appid={appid}&secret={appsecret}&js_code={code}&grant_type=authorization_code";
    private $_db;
    private $_user_id;
    private $_wechat_table;
    private $_appid;
    private $_appsecret;

    /**
     * @param Database $db
     * @param array $sysconfig
     * @param array $apiconfig
     */
    public function __construct($db, $sysconfig, $apiconfig){
        $this->_db = $db;
        $this->_user_id = "user_id";
        $this->_wechat_table = $sysconfig['wechat_table'];
        $this->_appid = $apiconfig['wma_appid'];
        $this->_appsecret = $apiconfig['wma_appsecret'];
    }

    /**
     * @param string $code callback from app
     * @param string $uid
     * @return array result
     */
    public function AddWeChatInfo($code, $uid){
        $url = str_replace('{appid}', $this->_appid , $this->_url);
        $url = str_replace('{appsecret}', $this->_appsecret , $url);
        $url = str_replace('{code}', $code , $url);
        
        $request = GET($url,array());
        if (empty($request)) return array('code'=>500);
        $res = json_decode($request);
        
        if (isset($res->errcode)){
            return array('code'=>400);
        }
        
        $response = $this->_db->insert($this->_wechat_table, 
                                       $this->_user_id, 
                                       array(
                                        'openid'=>$res->openid,
                                        'session_key'=>$res->session_key
                                        ), $uid);
        if ($response == false){
            return array('code'=>406);
        }
        return array('code'=>200);
    }

}