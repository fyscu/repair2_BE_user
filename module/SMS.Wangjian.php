<?php
require_once("Util.php");

class SMS_Wangjian {
    private $_url = "http://utf8.api.smschinese.cn/?Uid={uid}&Key={ukey}&smsMob={cellnum}&smsText={smstext}";
    private $_uid;
    private $_ukey;
    
    /**
     * @param array $config apiconfig in config.php
     */
    public function __construct($config){
        $this->_uid = $config['sms_accessid'];
        $this->_ukey = $config['sms_access_secret'];
    }
    
    /**
     * @param int $cellnum 
     * @param string $text
     * @param array options(additional)
     * @param array status
     */
    public function send($cellnum, $text, $options=array()){
        $url = str_replace('{uid}', $this->_uid , $this->_url);
        $url = str_replace('{ukey}', $this->_ukey , $url);
        $url = str_replace('{cellnum}', $cellnum , $url);
        $url = str_replace('{smstext}', urlencode($text) , $url);
        
        $request = GET($url,array());
        if ($request == 1){
            return array('code'=>200);
        } else {
            return array('code'=>502, 'data'=>$request);
        }
    }
}

