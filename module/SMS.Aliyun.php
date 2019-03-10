<?php
//ini_set("display_errors", "on"); // 显示错误提示，仅用于测试时排查问题
//error_reporting(E_ALL); // 显示所有错误提示，仅用于测试时排查问题
use Aliyun\DySDKLite\SignatureHelper;
class SMS_Aliyun {
    private $_accid;
    private $_acckey;
    private $_sign;
    private $_template;
    
    /**
     * @param array $config apiconfig in config.php
     */
    public function __construct($config){
        $this->_accid = $config['sms_accessid'];
        $this->_acckey = $config['sms_access_secret'];
        $this->_sign = $config['sms_sign'];
        $this->_template = $config['sms_template'];
    }
    
    /**
     * @param int $cellnum 
     * @param string $text
     * @param array options(additional)
     * @param array status
     */
    public function send($cellnum, $text, $options=array()){
        $params = array ();
        $accessKeyId = $this->_accid;
        $accessKeySecret = $this->_acckey;
        $params["PhoneNumbers"] = $cellnum;
        $params["SignName"] = $this->_sign;
        $params["TemplateCode"] = $this->_template;
        $params['TemplateParam'] = $options["data"];
        // fixme 可选: 设置发送短信流水号
        $params['OutId'] = "";
        // fixme 可选: 上行短信扩展码, 扩展码字段控制在7位或以下，无特殊需求用户请忽略此字段
        $params['SmsUpExtendCode'] = "";
        // *** 需用户填写部分结束, 以下代码若无必要无需更改 ***
        if(!empty($params["TemplateParam"]) && is_array($params["TemplateParam"])) {
            $params["TemplateParam"] = json_encode($params["TemplateParam"], JSON_UNESCAPED_UNICODE);
        }
        // 初始化SignatureHelper实例用于设置参数，签名以及发送请求
        $helper = new SignatureHelper();
        // 此处可能会抛出异常，注意catch
        $content = $helper->request(
            $accessKeyId,
            $accessKeySecret,
            "dysmsapi.aliyuncs.com",
            array_merge($params, array(
                "RegionId" => "cn-hangzhou",
                "Action" => "SendSms",
                "Version" => "2017-05-25",
            ))
            // fixme 选填: 启用https
            // ,true
        );
        if ($content->Code == "OK"){
            return array("code"=>200);
        } else {
            return array('code'=>502, 'data'=>$content->Message);
        }
    }
}

